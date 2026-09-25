<?php
/**
 * Growth-audit enquiry handling for /contact.
 *
 * Stateless CSRF (an HMAC-signed timestamp) so no session cookie is set and
 * the rest of the site stays fully cacheable. Every enquiry is appended to
 * storage/enquiries.log before mail is attempted, so nothing is ever lost if
 * the host's mail transport is down or unconfigured.
 *
 * The form works two ways from the same endpoint:
 *   - a plain HTML post (no JavaScript) re-renders the page with the errors,
 *     or redirects to ?sent=… on success;
 *   - the JavaScript in assets/js/main.js posts with `Accept: application/json`
 *     and gets a JSON verdict back, so the visitor sees the outcome in place.
 *
 * Mail goes through SMTP when storage/mail.php provides credentials (see
 * smtp_config()), and falls back to PHP's mail() otherwise.
 */
declare(strict_types=1);

const TOKEN_LIFETIME = 7200; // 2 hours
const TOKEN_MIN_AGE  = 2;    // a bot fills the form faster than this

/** Signing key, created on first use. */
function secret(): string
{
    $file = ROOT . '/storage/secret.key';
    if (is_file($file)) {
        return (string) file_get_contents($file);
    }

    $key = bin2hex(random_bytes(32));
    @mkdir(dirname($file), 0775, true);
    file_put_contents($file, $key, LOCK_EX);

    return $key;
}

function csrf_token(): string
{
    $ts = (string) time();

    return $ts . '.' . hash_hmac('sha256', $ts, secret());
}

function csrf_valid(string $token): bool
{
    [$ts, $mac] = array_pad(explode('.', $token, 2), 2, '');
    if ($ts === '' || !ctype_digit($ts)) {
        return false;
    }
    if (!hash_equals(hash_hmac('sha256', $ts, secret()), $mac)) {
        return false;
    }

    $age = time() - (int) $ts;

    return $age >= TOKEN_MIN_AGE && $age <= TOKEN_LIFETIME;
}

/** One-line-per-enquiry JSON log, so no request is dropped. */
function log_enquiry(array $data, bool $mailed, string $error = ''): void
{
    $file = ROOT . '/storage/enquiries.log';
    @mkdir(dirname($file), 0775, true);
    $row = $data + ['mailed' => $mailed, 'at' => date('c')];
    if ($error !== '') {
        $row['mail_error'] = $error;
    }
    file_put_contents(
        $file,
        json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n",
        FILE_APPEND | LOCK_EX
    );
}

/** Strip anything that could forge a mail header. */
function header_safe(string $v): string
{
    return trim(str_replace(["\r", "\n"], ' ', $v));
}

/** True when the request came from the form's JavaScript, which wants JSON back. */
function wants_json(): bool
{
    return str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')
        || ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'fetch';
}

function json_response(array $payload, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=UTF-8');
    header('Cache-Control: no-store');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/** The three outcome messages, shared by the JSON and the HTML paths. */
function enquiry_message(string $state): array
{
    return match ($state) {
        'sent' => [
            'tone' => 'ok',
            'text' => "Thanks — your request is with the team. We'll come back to you within one working day.",
        ],
        'logged' => [
            'tone' => 'warn',
            'text' => 'Your request has been recorded, but our email notification failed to send. '
                . 'To be safe, please also email ' . EMAIL . ' or call ' . PHONE . '.',
        ],
        default => ['tone' => '', 'text' => ''],
    };
}

/**
 * Validate a submission. Returns the cleaned values and any errors.
 *
 * @return array{errors: array<string,string>, values: array<string,string>}
 */
function validate_enquiry(): array
{
    $field = static fn (string $k): string => header_safe((string) ($_POST[$k] ?? ''));

    $values = [
        'name'     => $field('name'),
        'company'  => $field('company'),
        'email'    => $field('email'),
        'phone'    => $field('phone'),
        'interest' => $field('interest'),
        'revenue'  => $field('revenue'),
        'message'  => trim((string) ($_POST['message'] ?? '')),
    ];

    $errors = [];
    if (!csrf_valid((string) ($_POST['token'] ?? ''))) {
        $errors['form'] = 'This form has been open too long and needs a fresh start. Please reload the page and send it again.';
    }
    if ($values['name'] === '') {
        $errors['name'] = 'Please tell us your name.';
    } elseif (mb_strlen($values['name']) > 120) {
        $errors['name'] = 'Please keep your name under 120 characters.';
    }
    if ($values['email'] === '') {
        $errors['email'] = 'Please enter your work email so we can reply.';
    } elseif (!filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'That email address does not look right. Check for a typo, e.g. name@company.com.';
    }
    if ($values['phone'] !== '' && !preg_match('/^[+\d][\d\s().-]{5,24}$/', $values['phone'])) {
        $errors['phone'] = 'That phone number does not look right. Digits, spaces and + are fine.';
    }
    if ($values['interest'] !== '' && !in_array($values['interest'], INTERESTS, true)) {
        $errors['interest'] = 'Please choose one of the options.';
    }
    if ($values['revenue'] !== '' && !in_array($values['revenue'], REVENUE_BANDS, true)) {
        $errors['revenue'] = 'Please choose one of the options.';
    }
    if (mb_strlen($values['message']) > 5000) {
        $errors['message'] = 'Please keep the message under 5,000 characters.';
    }

    return ['errors' => $errors, 'values' => $values];
}

/**
 * Validate and deliver a submission.
 *
 * On failure returns the errors and submitted values for re-rendering (HTML)
 * or answers with JSON and exits. On success it never returns.
 *
 * @return array{errors: array<string,string>, values: array<string,string>}
 */
function handle_enquiry(): array
{
    // Honeypot: a real browser never fills a hidden field. Pretend it worked.
    if (($_POST['website'] ?? '') !== '') {
        if (wants_json()) {
            json_response(['ok' => true, 'state' => 'sent'] + enquiry_message('sent'));
        }
        redirect(url('contact') . '?sent=1#enquiry');
    }

    ['errors' => $errors, 'values' => $values] = validate_enquiry();

    if ($errors) {
        if (wants_json()) {
            json_response(['ok' => false, 'errors' => $errors], 422);
        }

        return ['errors' => $errors, 'values' => $values];
    }

    [$mailed, $error] = deliver_enquiry($values);
    log_enquiry($values, $mailed, $error);

    $state = $mailed ? 'sent' : 'logged';
    if (wants_json()) {
        json_response(['ok' => true, 'state' => $state] + enquiry_message($state));
    }
    redirect(url('contact') . '?sent=' . ($mailed ? '1' : 'logged') . '#enquiry');
}

/**
 * Email the enquiry to MAIL_TO. SMTP when configured, else PHP mail().
 *
 * @return array{0: bool, 1: string}  [delivered, error description]
 */
function deliver_enquiry(array $values): array
{
    $labels = [
        'name' => 'Name', 'company' => 'Company', 'email' => 'Email', 'phone' => 'Phone',
        'interest' => 'Interested in', 'revenue' => 'Monthly revenue', 'message' => 'Message',
    ];
    $body = '';
    foreach ($values as $key => $value) {
        $body .= $labels[$key] . ': ' . ($value === '' ? '—' : $value) . "\n";
    }
    $body .= "\nSent " . date('d M Y, H:i T') . ' from ' . SITE_URL . '/contact';
    if (!empty($_SERVER['REMOTE_ADDR'])) {
        $body .= ' (IP ' . header_safe((string) $_SERVER['REMOTE_ADDR']) . ')';
    }
    $body .= "\n";

    $subject = 'Growth audit request — ' . $values['name']
        . ($values['company'] !== '' ? ' (' . $values['company'] . ')' : '');

    $domain = (string) parse_url(SITE_URL, PHP_URL_HOST);
    $from   = 'no-reply@' . $domain;

    $smtp = smtp_config();
    if ($smtp !== null) {
        return smtp_send($smtp, $smtp['from'] ?: $from, MAIL_TO, $values['email'], $subject, $body);
    }

    $ok = @mail(
        MAIL_TO,
        encode_header($subject),
        $body,
        [
            'From'                      => SITE_NAME . ' <' . $from . '>',
            'Reply-To'                  => $values['email'],
            'MIME-Version'              => '1.0',
            'Content-Type'              => 'text/plain; charset=UTF-8',
            'Content-Transfer-Encoding' => '8bit',
        ]
    );

    if ($ok) {
        return [true, ''];
    }

    $last = error_get_last();

    return [false, 'mail(): ' . ($last['message'] ?? 'returned false')];
}

/** RFC 2047 encode a header value when it carries non-ASCII characters. */
function encode_header(string $value): string
{
    return preg_match('/[^\x20-\x7e]/', $value)
        ? '=?UTF-8?B?' . base64_encode($value) . '?='
        : $value;
}

/**
 * SMTP settings from storage/mail.php, which is outside version control:
 *
 *   <?php return [
 *     'host'   => 'smtp.hostinger.com',
 *     'port'   => 465,             // 465 = TLS from the start, 587 = STARTTLS
 *     'user'   => 'care@clientcarex.com',
 *     'pass'   => '…',
 *     'from'   => 'care@clientcarex.com', // must be a mailbox the login may send as
 *   ];
 *
 * Returns null when the file is missing, so mail() is used instead.
 */
function smtp_config(): ?array
{
    $file = ROOT . '/storage/mail.php';
    if (!is_file($file)) {
        return null;
    }
    $cfg = require $file;
    // Ignore the file until a real password has been pasted in.
    if (!is_array($cfg) || empty($cfg['host']) || empty($cfg['user'])
        || empty($cfg['pass']) || $cfg['pass'] === 'PASTE-MAILBOX-PASSWORD-HERE') {
        return null;
    }

    return $cfg + ['port' => 465, 'from' => '', 'timeout' => 15];
}

/**
 * Minimal SMTP client: implicit TLS on 465, STARTTLS otherwise, AUTH LOGIN.
 *
 * @return array{0: bool, 1: string}
 */
function smtp_send(array $cfg, string $from, string $to, string $replyTo, string $subject, string $body): array
{
    $port    = (int) $cfg['port'];
    $timeout = (int) $cfg['timeout'];
    $scheme  = $port === 465 ? 'ssl://' : 'tcp://';

    $sock = @stream_socket_client($scheme . $cfg['host'] . ':' . $port, $errno, $errstr, $timeout);
    if ($sock === false) {
        return [false, 'smtp connect: ' . $errstr . ' (' . $errno . ')'];
    }
    stream_set_timeout($sock, $timeout);

    $read = static function () use ($sock): array {
        $lines = '';
        while (($line = fgets($sock, 1024)) !== false) {
            $lines .= $line;
            if (strlen($line) < 4 || $line[3] !== '-') {
                break;
            }
        }

        return [(int) substr($lines, 0, 3), trim($lines)];
    };
    $say = static function (string $cmd, array $expect) use ($sock, $read): ?string {
        fwrite($sock, $cmd . "\r\n");
        [$code, $reply] = $read();

        return in_array($code, $expect, true) ? null : $reply;
    };

    [$code, $reply] = $read();
    if ($code !== 220) {
        fclose($sock);

        return [false, 'smtp greeting: ' . $reply];
    }

    $host = (string) parse_url(SITE_URL, PHP_URL_HOST);
    $fail = static function (string $step, string $reply) use ($sock): array {
        @fwrite($sock, "QUIT\r\n");
        fclose($sock);

        return [false, 'smtp ' . $step . ': ' . $reply];
    };

    if ($err = $say('EHLO ' . $host, [250])) {
        return $fail('ehlo', $err);
    }
    if ($scheme === 'tcp://') {
        if ($err = $say('STARTTLS', [220])) {
            return $fail('starttls', $err);
        }
        if (!stream_socket_enable_crypto($sock, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            return $fail('starttls', 'TLS negotiation failed');
        }
        if ($err = $say('EHLO ' . $host, [250])) {
            return $fail('ehlo', $err);
        }
    }
    if ($err = $say('AUTH LOGIN', [334])) {
        return $fail('auth', $err);
    }
    if ($err = $say(base64_encode((string) $cfg['user']), [334])) {
        return $fail('auth user', $err);
    }
    if ($err = $say(base64_encode((string) $cfg['pass']), [235])) {
        return $fail('auth pass', $err);
    }
    if ($err = $say('MAIL FROM:<' . $from . '>', [250])) {
        return $fail('mail from', $err);
    }
    if ($err = $say('RCPT TO:<' . $to . '>', [250, 251])) {
        return $fail('rcpt to', $err);
    }
    if ($err = $say('DATA', [354])) {
        return $fail('data', $err);
    }

    $headers = [
        'Date: ' . date('r'),
        'From: ' . encode_header(SITE_NAME) . ' <' . $from . '>',
        'To: <' . $to . '>',
        'Reply-To: <' . $replyTo . '>',
        'Subject: ' . encode_header($subject),
        'Message-ID: <' . bin2hex(random_bytes(12)) . '@' . $host . '>',
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8',
        'Content-Transfer-Encoding: 8bit',
    ];
    // Dot-stuffing: a line that is only "." would end the message early.
    $data = preg_replace('/^\./m', '..', str_replace(["\r\n", "\r"], "\n", $body));
    $data = str_replace("\n", "\r\n", (string) $data);

    if ($err = $say(implode("\r\n", $headers) . "\r\n\r\n" . $data . "\r\n.", [250])) {
        return $fail('send', $err);
    }
    $say('QUIT', [221]);
    fclose($sock);

    return [true, ''];
}

function redirect(string $to, int $status = 303): never
{
    header('Location: ' . $to, true, $status);
    exit;
}

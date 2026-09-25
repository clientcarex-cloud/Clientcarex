<?php
/**
 * Form handling for /contact (the short enquiry) and /growth-audit (the full
 * application, driven by GROWTH_AUDIT_FORM in app/content.php).
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

require_once ROOT . '/app/mail-template.php';

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
function log_enquiry(array $data, bool $mailed, string $error = '', string $via = ''): void
{
    $file = ROOT . '/storage/enquiries.log';
    @mkdir(dirname($file), 0775, true);
    $row = $data + ['mailed' => $mailed, 'via' => $via, 'to' => enquiry_recipient(), 'at' => date('c')];
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
 * Validate the growth-audit application against GROWTH_AUDIT_FORM.
 *
 * @return array{errors: array<string,string>, values: array<string,mixed>}
 */
function validate_growth_audit(): array
{
    $errors = [];
    if (!csrf_valid((string) ($_POST['token'] ?? ''))) {
        $errors['form'] = 'This form has been open too long and needs a fresh start. Please reload the page and send it again.';
    }

    $values = [];
    foreach (growth_audit_fields() as $f) {
        $id   = $f['id'];
        $req  = !empty($f['req']);
        $max  = (int) ($f['max'] ?? 0);
        $type = $f['type'];

        if ($type === 'checks') {
            $picked = array_values(array_filter(
                array_map('header_safe', array_map('strval', (array) ($_POST[$id] ?? []))),
                static fn (string $v): bool => $v !== ''
            ));
            $values[$id] = $picked;
            if ($req && $picked === []) {
                $errors[$id] = $f['msg'] ?? 'Please tick at least one option.';
            } elseif (array_diff($picked, $f['options'])) {
                $errors[$id] = 'Please choose from the options given.';
            }
            continue;
        }

        $raw   = (string) ($_POST[$id] ?? '');
        $value = $type === 'textarea' ? trim(str_replace("\r\n", "\n", $raw)) : header_safe($raw);
        $values[$id] = $value;

        if ($value === '') {
            if ($req) {
                $errors[$id] = $f['msg'] ?? ($type === 'select' ? 'Please choose an option.' : 'Please fill this in.');
            }
            continue;
        }
        if ($max > 0 && mb_strlen($value) > $max) {
            $errors[$id] = 'Please keep this under ' . number_format($max) . ' characters.';
            continue;
        }
        switch ($type) {
            case 'email':
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$id] = 'That email address does not look right. Check for a typo, e.g. name@company.com.';
                }
                break;
            case 'tel':
                if (!preg_match('/^[+\d][\d\s().-]{5,24}$/', $value)) {
                    $errors[$id] = 'That phone number does not look right. Digits, spaces and + are fine.';
                }
                break;
            case 'url':
                $url = preg_match('#^https?://#i', $value) ? $value : 'https://' . $value;
                if (!filter_var($url, FILTER_VALIDATE_URL) || !str_contains((string) parse_url($url, PHP_URL_HOST), '.')) {
                    $errors[$id] = 'That web address does not look right, e.g. https://yourcompany.com.';
                } else {
                    $values[$id] = $url;
                }
                break;
            case 'select':
                if (!in_array($value, $f['options'], true)) {
                    $errors[$id] = 'Please choose one of the options.';
                }
                break;
        }
    }

    return ['errors' => $errors, 'values' => $values];
}

/** Every field of GROWTH_AUDIT_FORM as a flat list, rows unpacked. */
function growth_audit_fields(): array
{
    $out = [];
    foreach (GROWTH_AUDIT_FORM as $step) {
        foreach ($step['fields'] as $item) {
            foreach (isset($item['id']) ? [$item] : $item as $f) {
                $out[] = $f;
            }
        }
    }

    return $out;
}

/** Tick-box arrays joined for the log row and the email. */
function flat_values(array $values): array
{
    return array_map(static fn ($v): string => is_array($v) ? implode(', ', $v) : (string) $v, $values);
}

/**
 * Validate and deliver a submission.
 *
 * On failure returns the errors and submitted values for re-rendering (HTML)
 * or answers with JSON and exits. On success it never returns.
 *
 * @return array{errors: array<string,string>, values: array<string,string>}
 */
function handle_enquiry(string $route = 'contact'): array
{
    $audit  = $route === 'growth-audit';
    $anchor = $audit ? '#audit' : '#enquiry';

    // Honeypot: a real browser never fills a hidden field. Pretend it worked.
    if (($_POST['website'] ?? '') !== '') {
        if (wants_json()) {
            json_response(['ok' => true, 'state' => 'sent'] + enquiry_message('sent'));
        }
        redirect(url($route) . '?sent=1' . $anchor);
    }

    ['errors' => $errors, 'values' => $values] = $audit ? validate_growth_audit() : validate_enquiry();

    if ($errors) {
        if (wants_json()) {
            json_response(['ok' => false, 'errors' => $errors], 422);
        }

        return ['errors' => $errors, 'values' => $values];
    }

    $flat = flat_values($values);
    $mail = $audit ? growth_audit_email($flat) : enquiry_email($flat);

    [$mailed, $error, $via] = deliver_enquiry($mail, $flat['email']);
    log_enquiry(['form' => $route] + $flat, $mailed, $error, $via);

    $state = $mailed ? 'sent' : 'logged';
    if (wants_json()) {
        json_response(['ok' => true, 'state' => $state] + enquiry_message($state));
    }
    redirect(url($route) . '?sent=' . ($mailed ? '1' : 'logged') . $anchor);
}

/**
 * Email a built message to MAIL_TO. SMTP when configured, else PHP mail().
 *
 * @param array{subject: string, text: string, html: string} $mail
 * @return array{0: bool, 1: string, 2: string}  [delivered, error description, transport used]
 */
function deliver_enquiry(array $mail, string $replyTo): array
{
    $domain = (string) parse_url(SITE_URL, PHP_URL_HOST);
    $from   = 'no-reply@' . $domain;

    $errors = [];
    $smtp   = smtp_config();
    $to     = enquiry_recipient();
    if ($smtp !== null) {
        // Try the configured port first, then the other common one, since
        // shared hosts often block one of them.
        $ports = array_unique([(int) $smtp['port'], (int) $smtp['port'] === 465 ? 587 : 465]);
        foreach ($ports as $port) {
            [$ok, $err] = smtp_send(['port' => $port] + $smtp, $smtp['from'] ?: $from, $to, $replyTo, $mail);
            if ($ok) {
                return [true, '', 'smtp:' . $port];
            }
            $errors[] = 'port ' . $port . ': ' . $err;
            if (!str_starts_with($err, 'smtp connect')) {
                break; // reached the server; the other port will not change a login or send error
            }
        }
    }

    $mime = mime_alternative($mail['text'], $mail['html']);
    $ok   = @mail(
        $to,
        encode_header($mail['subject']),
        $mime['body'],
        [
            'From'         => SITE_NAME . ' <' . $from . '>',
            'Reply-To'     => $replyTo,
            'MIME-Version' => '1.0',
            'Content-Type' => $mime['type'],
        ]
    );

    if ($ok) {
        // mail() only confirms the host's local queue took it, not delivery.
        return [true, $errors ? 'smtp skipped: ' . implode(' | ', $errors) : '', 'mail()'];
    }

    $last     = error_get_last();
    $errors[] = 'mail(): ' . ($last['message'] ?? 'returned false');

    return [false, implode(' | ', $errors), 'none'];
}

/** RFC 2047 encode a header value when it carries non-ASCII characters. */
function encode_header(string $value): string
{
    return preg_match('/[^\x20-\x7e]/', $value)
        ? '=?UTF-8?B?' . base64_encode($value) . '?='
        : $value;
}

/** Where enquiries are delivered: 'to' in the credentials file, else MAIL_TO. */
function enquiry_recipient(): string
{
    $smtp = smtp_config();

    return !empty($smtp['to']) && filter_var($smtp['to'], FILTER_VALIDATE_EMAIL) ? $smtp['to'] : MAIL_TO;
}

/**
 * Where the mailbox credentials live. The first file found wins:
 *
 *   1. clientcarex-mail.php in the folder ABOVE the web root — for the
 *      /homepage install that is the folder containing public_html, which
 *      no browser can ever reach;
 *   2. storage/mail.php inside the site, which .htaccess blocks from the web.
 *
 * storage/mail.example.php shows the format. Neither file is in git.
 */
function mail_config_files(): array
{
    return [
        dirname(ROOT, 2) . '/clientcarex-mail.php',
        ROOT . '/storage/mail.php',
    ];
}

/** Loaded SMTP settings, or null when no usable credentials file exists. */
function smtp_config(): ?array
{
    $file = null;
    foreach (mail_config_files() as $candidate) {
        if (is_file($candidate) && is_readable($candidate)) {
            $file = $candidate;
            break;
        }
    }
    if ($file === null) {
        return null;
    }
    $cfg = require $file;
    // Ignore the file until a real password has been pasted in.
    if (!is_array($cfg) || empty($cfg['host']) || empty($cfg['user'])
        || empty($cfg['pass']) || $cfg['pass'] === 'PASTE-MAILBOX-PASSWORD-HERE') {
        return null;
    }

    return $cfg + ['port' => 465, 'from' => '', 'to' => '', 'timeout' => 15];
}

/**
 * Minimal SMTP client: implicit TLS on 465, STARTTLS otherwise, AUTH LOGIN.
 *
 * @return array{0: bool, 1: string}
 */
function smtp_send(array $cfg, string $from, string $to, string $replyTo, array $mail): array
{
    $mime = mime_alternative($mail['text'], $mail['html']);

    $port    = (int) $cfg['port'];
    $timeout = (int) $cfg['timeout'];
    $scheme  = $port === 465 ? 'ssl://' : 'tcp://';

    $context = stream_context_create(['ssl' => [
        'peer_name'         => $cfg['host'],
        'SNI_enabled'       => true,
        'verify_peer'       => empty($cfg['insecure']),
        'verify_peer_name'  => empty($cfg['insecure']),
        'allow_self_signed' => !empty($cfg['insecure']),
    ]]);

    // A TLS failure reports errno 0 and an empty message; the reason is in
    // the PHP warning, so collect that instead.
    $warning = '';
    set_error_handler(static function (int $no, string $msg) use (&$warning): bool {
        $warning = $warning === '' ? $msg : $warning; // the first warning names the cause

        return true;
    });
    try {
        $sock = stream_socket_client(
            $scheme . $cfg['host'] . ':' . $port, $errno, $errstr, $timeout,
            STREAM_CLIENT_CONNECT, $context
        );
    } finally {
        restore_error_handler();
    }
    if ($sock === false) {
        $reason = trim((string) $errstr) !== '' ? $errstr . ' (' . $errno . ')' : ($warning !== '' ? $warning : 'no response (' . $errno . ')');

        return [false, 'smtp connect: ' . $reason];
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
        $warning = '';
        set_error_handler(static function (int $no, string $msg) use (&$warning): bool {
            $warning = $warning === '' ? $msg : $warning;

            return true;
        });
        try {
            $tls = stream_socket_enable_crypto($sock, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
        } finally {
            restore_error_handler();
        }
        if ($tls !== true) {
            return $fail('starttls', $warning !== '' ? $warning : 'TLS negotiation failed');
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
        'Subject: ' . encode_header($mail['subject']),
        'Message-ID: <' . bin2hex(random_bytes(12)) . '@' . $host . '>',
        'MIME-Version: 1.0',
        'Content-Type: ' . $mime['type'],
    ];
    // Dot-stuffing: a line that is only "." would end the message early.
    $data = preg_replace('/^\./m', '..', str_replace(["\r\n", "\r"], "\n", $mime['body']));
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

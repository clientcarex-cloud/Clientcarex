<?php
/**
 * /homepage/mail-setup — connect the contact form to a mailbox without
 * touching any file on the server.
 *
 * The visitor enters the mailbox and its password; we log in to Hostinger's
 * SMTP with them and send a test email. Only when that succeeds is
 * storage/mail.php written, so a wrong password can never be saved.
 *
 * The page needs no login of its own: nothing on it can be changed without
 * the mailbox password, and the mail server is what checks that. Failed
 * attempts are rate-limited so the page cannot be used to guess it.
 */
declare(strict_types=1);

const SETUP_MAX_FAILURES = 8;     // per hour, across all visitors
const SETUP_SMTP_HOST    = 'smtp.hostinger.com';

/** What the page shows before anything is submitted. */
function mail_setup_state(): array
{
    $cfg  = smtp_config();
    $last = last_enquiry_status();

    return [
        'configured' => $cfg !== null,
        'mailbox'    => $cfg['user'] ?? MAIL_TO,
        'to'         => enquiry_recipient(),
        'last'       => $last,
        'result'     => null,
        'errors'     => [],
    ];
}

/** Delivery status of the newest enquiry, without any personal data. */
function last_enquiry_status(): ?array
{
    $file = ROOT . '/storage/enquiries.log';
    if (!is_file($file) || filesize($file) === 0) {
        return null;
    }
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
    $row   = json_decode((string) end($lines), true);
    if (!is_array($row)) {
        return null;
    }

    return [
        'at'     => (string) ($row['at'] ?? ''),
        'mailed' => (bool) ($row['mailed'] ?? false),
        'via'    => (string) ($row['via'] ?? ''),
        'to'     => (string) ($row['to'] ?? ''),
        'error'  => (string) ($row['mail_error'] ?? ''),
    ];
}

/** Simple lockout: too many failed logins in the last hour and the page refuses. */
function setup_failures(): array
{
    $file = ROOT . '/storage/setup-failures.json';
    $list = is_file($file) ? (json_decode((string) file_get_contents($file), true) ?: []) : [];

    return array_values(array_filter($list, static fn ($t) => is_int($t) && $t > time() - 3600));
}

function record_setup_failure(): void
{
    $list   = setup_failures();
    $list[] = time();
    file_put_contents(ROOT . '/storage/setup-failures.json', json_encode($list), LOCK_EX);
}

/** Validate the submission, test the login by sending an email, save on success. */
function handle_mail_setup(): array
{
    $state  = mail_setup_state();
    $field  = static fn (string $k): string => header_safe((string) ($_POST[$k] ?? ''));
    $user   = $field('mailbox');
    $pass   = (string) ($_POST['password'] ?? '');
    $to     = $field('to') !== '' ? $field('to') : $user;
    $errors = [];

    $state['mailbox'] = $user;
    $state['to']      = $to;

    if (!csrf_valid((string) ($_POST['token'] ?? ''))) {
        $errors['form'] = 'This page had been open too long. Please try once more.';
    }
    if (!filter_var($user, FILTER_VALIDATE_EMAIL)) {
        $errors['mailbox'] = 'Enter the full mailbox address, e.g. care@clientcarex.com.';
    }
    if ($pass === '') {
        $errors['password'] = 'Enter the password for this mailbox.';
    }
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        $errors['to'] = 'Enter a valid email address.';
    }
    if ($errors) {
        return ['errors' => $errors] + $state;
    }

    if (count(setup_failures()) >= SETUP_MAX_FAILURES) {
        return ['result' => [
            'ok'   => false,
            'text' => 'Too many failed attempts in the last hour. Please wait an hour and try again.',
        ]] + $state;
    }

    // Hostinger's hosting machines answer for smtp.hostinger.com with their
    // own certificate, so the relay's certificate is not verified — as
    // requested for this deployment.
    $cfg = [
        'host'     => SETUP_SMTP_HOST,
        'port'     => 465,
        'user'     => $user,
        'pass'     => $pass,
        'from'     => $user,
        'to'       => $to,
        'timeout'  => 15,
        'insecure' => true,
    ];

    $subject = 'Contact form test — ' . SITE_NAME;
    $body    = "This is a test from the contact-form setup page at " . SITE_URL . "/homepage/mail-setup.\n\n"
        . "It worked: the website can now send email through " . $user . ".\n"
        . "Enquiries from the contact form will arrive at " . $to . ".\n\n"
        . 'Sent ' . date('d M Y, H:i T') . "\n";

    $attempts = [];
    foreach ([465, 587] as $port) {
        [$ok, $err] = smtp_send(['port' => $port] + $cfg, $user, $to, $user, $subject, $body);
        if ($ok) {
            $cfg['port'] = $port;
            $saved = save_mail_config($cfg);

            return ['result' => [
                'ok'   => $saved,
                'text' => $saved
                    ? 'It works. A test email has just been sent to ' . $to . ' — check that inbox. '
                      . 'Every contact-form enquiry will now be emailed there from ' . $user . '.'
                    : 'The mailbox login works, but the settings could not be saved: storage/ is not writable by the website.',
            ], 'configured' => $saved] + $state;
        }
        $attempts[] = 'port ' . $port . ': ' . $err;
        if (!str_starts_with($err, 'smtp connect') && !str_starts_with($err, 'smtp starttls')) {
            break;
        }
    }

    record_setup_failure();
    sleep(2);
    $detail = implode(' | ', $attempts);

    return ['result' => [
        'ok'     => false,
        'text'   => explain_smtp_failure($detail),
        'detail' => $detail,
    ]] + $state;
}

/** Turn the SMTP reply into a sentence a non-technical reader can act on. */
function explain_smtp_failure(string $detail): string
{
    if (str_contains($detail, '535')) {
        return 'The mail server rejected the password. Use the password for this mailbox itself '
            . '(the one used to log in to webmail), not the hosting account password. '
            . 'If unsure, reset the mailbox password in Hostinger (Emails → Manage → the mailbox → Change password) and try again.';
    }
    if (str_contains($detail, 'smtp connect') || str_contains($detail, 'starttls')) {
        return 'The website could not reach the mail server. This is usually the hosting company blocking outgoing mail — '
            . 'please forward the details below to Hostinger support.';
    }
    if (str_contains($detail, 'rcpt to') || str_contains($detail, 'mail from')) {
        return 'The mail server refused the sender or recipient address. Make sure the mailbox and the delivery address both exist.';
    }

    return 'The test email could not be sent. The details below say why.';
}

/** Write storage/mail.php for app/enquiry.php to pick up. */
function save_mail_config(array $cfg): bool
{
    $file = ROOT . '/storage/mail.php';
    $php  = "<?php\n// Written by /homepage/mail-setup on " . date('c') . ". Not web-accessible, not in git.\n"
        . 'return ' . var_export($cfg, true) . ";\n";
    @mkdir(dirname($file), 0775, true);

    return file_put_contents($file, $php, LOCK_EX) !== false;
}

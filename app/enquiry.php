<?php
/**
 * Growth-audit enquiry handling for /contact.
 *
 * Stateless CSRF (an HMAC-signed timestamp) so no session cookie is set and
 * the rest of the site stays fully cacheable. Every enquiry is appended to
 * storage/enquiries.log before mail is attempted, so nothing is ever lost if
 * the host's mail transport is down or unconfigured.
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
function log_enquiry(array $data, bool $mailed): void
{
    $file = ROOT . '/storage/enquiries.log';
    @mkdir(dirname($file), 0775, true);
    file_put_contents(
        $file,
        json_encode($data + ['mailed' => $mailed, 'at' => date('c')], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n",
        FILE_APPEND | LOCK_EX
    );
}

/** Strip anything that could forge a mail header. */
function header_safe(string $v): string
{
    return trim(str_replace(["\r", "\n"], ' ', $v));
}

/**
 * Validate and deliver a submission.
 *
 * @return array{errors: array<string,string>, values: array<string,string>}
 */
function handle_enquiry(): array
{
    $field = static fn (string $k): string => header_safe((string) ($_POST[$k] ?? ''));

    $values = [
        'name'    => $field('name'),
        'company' => $field('company'),
        'email'   => $field('email'),
        'phone'   => $field('phone'),
        'interest' => $field('interest'),
        'revenue'  => $field('revenue'),
        'message'  => trim((string) ($_POST['message'] ?? '')),
    ];

    // Honeypot: a real browser never fills a hidden field.
    if (($_POST['website'] ?? '') !== '') {
        redirect(url('contact') . '?sent=1');
    }

    $errors = [];
    if (!csrf_valid((string) ($_POST['token'] ?? ''))) {
        $errors['form'] = 'Your session expired. Please send the form again.';
    }
    if ($values['name'] === '') {
        $errors['name'] = 'Please tell us your name.';
    }
    if (!filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid work email.';
    }
    if (mb_strlen($values['message']) > 5000) {
        $errors['message'] = 'Please keep the message under 5,000 characters.';
    }

    if ($errors) {
        return ['errors' => $errors, 'values' => $values];
    }

    $mailed = send_enquiry_mail($values);

    log_enquiry($values, $mailed);
    redirect(url('contact') . '?sent=1');
}

/**
 * Deliver an enquiry through the host's own mail transport — no SMTP login.
 * PHPMailer in isMail() mode sets a same-domain From and an explicit envelope
 * sender (Return-Path), which shared hosts need before they will relay it.
 */
function send_enquiry_mail(array $values): bool
{
    require_once ROOT . '/app/PHPMailer/Exception.php';
    require_once ROOT . '/app/PHPMailer/PHPMailer.php';

    $subject = 'Growth audit request — ' . $values['name']
        . ($values['company'] !== '' ? ' (' . $values['company'] . ')' : '');

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    try {
        $mail->isMail();
        $mail->CharSet = 'UTF-8';
        $mail->setFrom(MAIL_FROM, SITE_NAME . ' Website');
        $mail->Sender = MAIL_FROM;
        $mail->addReplyTo($values['email'], $values['name']);
        foreach (MAIL_TO as $to) {
            $mail->addAddress($to);
        }

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = enquiry_mail_html($values);
        $mail->AltBody = enquiry_mail_text($values);

        return $mail->send();
    } catch (PHPMailer\PHPMailer\Exception) {
        return false;
    }
}

/** Label => value rows shown in the email, blank optional fields left out. */
function enquiry_rows(array $values): array
{
    $labels = [
        'name'     => 'Name',
        'company'  => 'Company',
        'email'    => 'Email',
        'phone'    => 'Phone',
        'interest' => 'Interested in',
        'revenue'  => 'Monthly revenue',
    ];

    $rows = [];
    foreach ($labels as $key => $label) {
        if ($values[$key] !== '') {
            $rows[$label] = $values[$key];
        }
    }

    return $rows;
}

function enquiry_mail_text(array $values): string
{
    $text = "Growth audit request — " . SITE_NAME . "\n" . str_repeat('-', 44) . "\n";
    foreach (enquiry_rows($values) as $label => $value) {
        $text .= "$label: $value\n";
    }
    $text .= str_repeat('-', 44) . "\nMessage:\n"
        . ($values['message'] !== '' ? $values['message'] : '— No message provided —') . "\n";

    return $text;
}

function enquiry_mail_html(array $values): string
{
    $h = static fn (string $s): string => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    $link = 'color:#065845;text-decoration:none;font-weight:600;';

    $rowsHtml = '';
    $i = 0;
    foreach (enquiry_rows($values) as $label => $value) {
        $cell = match ($label) {
            'Email' => '<a href="mailto:' . $h($value) . '" style="' . $link . '">' . $h($value) . '</a>',
            'Phone' => '<a href="tel:' . $h((string) preg_replace('/[^0-9+]/', '', $value)) . '" style="' . $link . '">' . $h($value) . '</a>',
            default => $h($value),
        };
        $bg = $i++ % 2 === 0 ? '#f4f7f2' : '#ffffff';
        $rowsHtml .= '<tr>'
            . '<td style="padding:13px 18px;background:' . $bg . ';border-bottom:1px solid #e2e9e4;font-size:13px;color:#5c6a65;font-weight:600;width:40%;vertical-align:top;">' . $label . '</td>'
            . '<td style="padding:13px 18px;background:' . $bg . ';border-bottom:1px solid #e2e9e4;font-size:14px;color:#101010;font-weight:500;vertical-align:top;">' . $cell . '</td>'
            . '</tr>';
    }

    $message = $values['message'] !== ''
        ? nl2br($h($values['message']))
        : '<span style="color:#7d8a85;">— No message provided —</span>';
    $email   = $h($values['email']);
    $name    = $h($values['name']);
    $date    = date('d M Y, g:i A');
    $logo    = SITE_URL . '/assets/img/ClientcareX-Logo.png';
    $site    = SITE_NAME;
    $company = COMPANY;
    $year    = date('Y');

    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;padding:0;background:#ebfef6;font-family:'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#ebfef6;padding:30px 12px;">
    <tr><td align="center">
      <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:16px;overflow:hidden;">
        <tr><td align="center" style="padding:30px 30px 22px;">
          <img src="$logo" alt="$site" width="200" style="display:block;border:0;width:200px;max-width:62%;height:auto;">
        </td></tr>
        <tr><td style="height:4px;background:#88e64a;font-size:0;line-height:0;">&nbsp;</td></tr>
        <tr><td style="padding:26px 30px;background:#06291f;">
          <div style="font-size:12px;letter-spacing:.09em;text-transform:uppercase;color:#a9ff9b;font-weight:700;">Growth audit request</div>
          <div style="font-size:21px;color:#ffffff;font-weight:700;margin-top:5px;">You've received a new enquiry</div>
          <div style="font-size:13px;color:rgba(255,255,255,.72);margin-top:7px;">Submitted on $date</div>
        </td></tr>
        <tr><td style="padding:26px 30px 10px;">
          <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #e2e9e4;border-radius:10px;overflow:hidden;">
            $rowsHtml
          </table>
        </td></tr>
        <tr><td style="padding:10px 30px 26px;">
          <div style="font-size:13px;color:#5c6a65;font-weight:600;margin-bottom:9px;">Message</div>
          <div style="background:#f4f7f2;border:1px solid #e2e9e4;border-left:4px solid #88e64a;border-radius:8px;padding:16px 18px;font-size:14px;color:#35403c;line-height:1.65;">$message</div>
        </td></tr>
        <tr><td align="center" style="padding:0 30px 32px;">
          <a href="mailto:$email" style="display:inline-block;background:#065845;color:#ffffff;text-decoration:none;font-weight:700;font-size:14px;padding:13px 30px;border-radius:999px;">Reply to $name</a>
        </td></tr>
        <tr><td style="padding:24px 30px;background:#052018;text-align:center;">
          <div style="font-size:15px;color:#ffffff;font-weight:700;">$site</div>
          <div style="font-size:12px;color:rgba(255,255,255,.55);margin-top:12px;line-height:1.6;">Sent automatically from the $site website.<br>&copy; $year $company. All rights reserved.</div>
        </td></tr>
      </table>
    </td></tr>
  </table>
</body>
</html>
HTML;
}

function redirect(string $to, int $status = 303): never
{
    header('Location: ' . $to, true, $status);
    exit;
}

<?php
/**
 * HTML email for the growth-audit enquiry, plus the MIME wrapper that sends
 * it with a plain-text alternative. Table layout and inline styles only, so
 * it renders the same in Gmail, Outlook and Apple Mail.
 */
declare(strict_types=1);

/** Brand colours, kept in step with assets/css/style.css. */
const MAIL_BRAND_DARK = '#06291f';
const MAIL_BRAND      = '#065845';
const MAIL_LIME       = '#88e64a';
const MAIL_INK        = '#101010';
const MAIL_MUTED      = '#5c6a65';
const MAIL_LINE       = '#e2e9e4';
const MAIL_PAPER      = '#f4f7f2';

/**
 * Multipart/alternative message body and the headers that describe it.
 *
 * @return array{type: string, body: string}
 */
function mime_alternative(string $text, string $html): array
{
    $boundary = '=_ccx_' . bin2hex(random_bytes(12));
    $part = static fn (string $type, string $content): string =>
        '--' . $boundary . "\r\n"
        . 'Content-Type: ' . $type . "; charset=UTF-8\r\n"
        . "Content-Transfer-Encoding: base64\r\n\r\n"
        . chunk_split(base64_encode($content), 76, "\r\n") . "\r\n";

    return [
        'type' => 'multipart/alternative; boundary="' . $boundary . '"',
        'body' => $part('text/plain', $text) . $part('text/html', $html) . '--' . $boundary . "--\r\n",
    ];
}

/** Shell shared by every email: logo band, white card, footer. */
function mail_shell(string $preheader, string $eyebrow, string $title, string $inner, string $footer): string
{
    $logo = SITE_URL . '/homepage/assets/img/ClientcareX-Logo.png';
    $font = "font-family:'DM Sans','Segoe UI',Helvetica,Arial,sans-serif;";

    return '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
        . '<meta name="color-scheme" content="light"><title>' . e($title) . '</title></head>'
        . '<body style="margin:0;padding:0;background:' . MAIL_PAPER . ';' . $font . '">'
        . '<div style="display:none;max-height:0;overflow:hidden;opacity:0;color:transparent">' . e($preheader) . '</div>'
        . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:' . MAIL_PAPER . '"><tr><td align="center" style="padding:32px 16px">'
        . '<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%">'
        // Logo band: white with a lime accent, so the logo keeps its own colours
        // (email clients strip CSS filters, so it cannot be recoloured).
        . '<tr><td style="background:#ffffff;border:1px solid ' . MAIL_LINE . ';border-bottom:0;border-top:5px solid ' . MAIL_LIME . ';border-radius:16px 16px 0 0;padding:24px 32px 20px">'
        . '<img src="' . e($logo) . '" width="150" height="30" alt="' . e(SITE_NAME) . '" style="display:block;border:0;width:150px;height:auto">'
        . '</td></tr>'
        // Card
        . '<tr><td style="background:#ffffff;padding:28px 32px 8px;border-left:1px solid ' . MAIL_LINE . ';border-right:1px solid ' . MAIL_LINE . '">'
        . '<p style="margin:0 0 8px;font-size:12px;letter-spacing:.12em;text-transform:uppercase;font-weight:700;color:' . MAIL_BRAND . '">' . e($eyebrow) . '</p>'
        . '<h1 style="margin:0 0 20px;font-size:24px;line-height:1.25;font-weight:700;color:' . MAIL_INK . '">' . e($title) . '</h1>'
        . $inner
        . '</td></tr>'
        // Footer
        . '<tr><td style="background:#ffffff;border:1px solid ' . MAIL_LINE . ';border-top:0;border-radius:0 0 16px 16px;padding:20px 32px 28px">'
        . '<p style="margin:0;font-size:12px;line-height:1.6;color:' . MAIL_MUTED . '">' . $footer . '</p>'
        . '</td></tr>'
        . '<tr><td style="padding:20px 8px 0;text-align:center;font-size:12px;color:' . MAIL_MUTED . '">'
        . e(COMPANY) . ' · <a href="' . e(SITE_URL) . '" style="color:' . MAIL_MUTED . '">' . e(preg_replace('#^https?://#', '', SITE_URL)) . '</a>'
        . '</td></tr>'
        . '</table></td></tr></table></body></html>';
}

/** Solid button that survives Outlook. */
function mail_button(string $href, string $label): string
{
    return '<table role="presentation" cellpadding="0" cellspacing="0" style="margin:8px 0 28px"><tr>'
        . '<td style="background:' . MAIL_BRAND . ';border-radius:999px">'
        . '<a href="' . e($href) . '" style="display:inline-block;padding:13px 26px;font-size:15px;font-weight:700;color:#ffffff;text-decoration:none;border-radius:999px">' . e($label) . '</a>'
        . '</td></tr></table>';
}

/**
 * The enquiry notification.
 *
 * @param array<string,string> $values  name, company, email, phone, interest, revenue, message
 * @return array{subject: string, text: string, html: string}
 */
function enquiry_email(array $values): array
{
    $labels = [
        'name' => 'Name', 'company' => 'Company', 'email' => 'Email', 'phone' => 'Phone',
        'interest' => 'Interested in', 'revenue' => 'Monthly revenue',
    ];
    $sentAt = date('D, d M Y \a\t H:i T');
    $ip     = header_safe((string) ($_SERVER['REMOTE_ADDR'] ?? ''));
    $who    = $values['name'] . ($values['company'] !== '' ? ' (' . $values['company'] . ')' : '');
    $subject = 'Growth audit request — ' . $who;

    // Plain-text alternative
    $text = "New growth audit request\n\n";
    foreach ($labels as $key => $label) {
        $text .= $label . ': ' . ($values[$key] === '' ? '—' : $values[$key]) . "\n";
    }
    $text .= "\nMessage:\n" . ($values['message'] === '' ? '—' : $values['message']) . "\n\n"
        . 'Reply to ' . $values['email'] . "\n"
        . 'Sent ' . $sentAt . ' from ' . SITE_URL . '/contact' . ($ip !== '' ? ' (IP ' . $ip . ')' : '') . "\n";

    // HTML rows
    $rows = '';
    foreach ($labels as $key => $label) {
        $value = $values[$key];
        $cell  = match (true) {
            $value === ''      => '<span style="color:' . MAIL_MUTED . '">—</span>',
            $key === 'email'   => '<a href="mailto:' . e($value) . '" style="color:' . MAIL_BRAND . ';font-weight:600">' . e($value) . '</a>',
            $key === 'phone'   => '<a href="tel:' . e(preg_replace('/[^\d+]/', '', $value)) . '" style="color:' . MAIL_BRAND . ';font-weight:600">' . e($value) . '</a>',
            default            => e($value),
        };
        $rows .= '<tr>'
            . '<td style="padding:11px 0;border-top:1px solid ' . MAIL_LINE . ';font-size:13px;color:' . MAIL_MUTED . ';width:38%;vertical-align:top">' . e($label) . '</td>'
            . '<td style="padding:11px 0;border-top:1px solid ' . MAIL_LINE . ';font-size:15px;color:' . MAIL_INK . ';vertical-align:top">' . $cell . '</td>'
            . '</tr>';
    }

    $message = $values['message'] === ''
        ? '<span style="color:' . MAIL_MUTED . '">No message left.</span>'
        : nl2br(e($values['message']));

    $inner = '<p style="margin:0 0 20px;font-size:15px;line-height:1.6;color:' . MAIL_MUTED . '">'
        . 'Someone just asked for a free growth audit through the website. Their details and message are below; replying to this email goes straight to them.</p>'
        . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px">' . $rows . '</table>'
        . '<p style="margin:0 0 8px;font-size:12px;letter-spacing:.12em;text-transform:uppercase;font-weight:700;color:' . MAIL_BRAND . '">Their message</p>'
        . '<div style="background:' . MAIL_PAPER . ';border-left:4px solid ' . MAIL_LIME . ';border-radius:0 10px 10px 0;padding:16px 18px;margin-bottom:24px;font-size:15px;line-height:1.65;color:' . MAIL_INK . '">' . $message . '</div>'
        . mail_button('mailto:' . rawurlencode($values['email']) . '?subject=' . rawurlencode('Re: your growth audit request — ' . SITE_NAME), 'Reply to ' . $values['name']);

    $footer = 'Sent ' . e($sentAt) . ' from the <a href="' . e(SITE_URL . '/contact') . '" style="color:' . MAIL_MUTED . '">contact form</a>'
        . ($ip !== '' ? ' · visitor IP ' . e($ip) : '')
        . '<br>Every enquiry is also kept on the server, so nothing is lost if this email goes astray.';

    $html = mail_shell(
        'New growth audit request from ' . $who,
        'New enquiry',
        'Growth audit request from ' . $values['name'],
        $inner,
        $footer
    );

    return ['subject' => $subject, 'text' => $text, 'html' => $html];
}

/** Small building blocks shared by the notification emails. */
function mail_eyebrow(string $text): string
{
    return '<p style="margin:0 0 8px;font-size:12px;letter-spacing:.12em;text-transform:uppercase;font-weight:700;color:' . MAIL_BRAND . '">' . e($text) . '</p>';
}

/** One label/value row for the details table. */
function mail_row(string $label, string $cell): string
{
    return '<tr>'
        . '<td style="padding:11px 0;border-top:1px solid ' . MAIL_LINE . ';font-size:13px;color:' . MAIL_MUTED . ';width:38%;vertical-align:top">' . e($label) . '</td>'
        . '<td style="padding:11px 0;border-top:1px solid ' . MAIL_LINE . ';font-size:15px;color:' . MAIL_INK . ';vertical-align:top">' . $cell . '</td>'
        . '</tr>';
}

/** A value cell: dash when empty, a link for email / phone / url, text otherwise. */
function mail_cell(string $type, string $value): string
{
    $link = 'style="color:' . MAIL_BRAND . ';font-weight:600"';

    return match (true) {
        $value === ''    => '<span style="color:' . MAIL_MUTED . '">—</span>',
        $type === 'email' => '<a href="mailto:' . e($value) . '" ' . $link . '>' . e($value) . '</a>',
        $type === 'tel'   => '<a href="tel:' . e(preg_replace('/[^\d+]/', '', $value)) . '" ' . $link . '>' . e($value) . '</a>',
        $type === 'url'   => '<a href="' . e($value) . '" ' . $link . '>' . e(preg_replace('#^https?://#', '', $value)) . '</a>',
        default           => e($value),
    };
}

/** A long answer, set off from the table. */
function mail_block(string $label, string $value): string
{
    $body = $value === ''
        ? '<span style="color:' . MAIL_MUTED . '">Not answered.</span>'
        : nl2br(e($value));

    return '<p style="margin:16px 0 6px;font-size:13px;font-weight:700;color:' . MAIL_INK . '">' . e($label) . '</p>'
        . '<div style="background:' . MAIL_PAPER . ';border-left:4px solid ' . MAIL_LIME . ';border-radius:0 10px 10px 0;padding:14px 16px;font-size:15px;line-height:1.65;color:' . MAIL_INK . '">' . $body . '</div>';
}

/**
 * The growth-audit application notification: one block per section of
 * GROWTH_AUDIT_FORM, short answers in a table and long answers as quotes.
 *
 * @param array<string,string> $values  every field id => answer (tick-box lists already joined)
 * @return array{subject: string, text: string, html: string}
 */
function growth_audit_email(array $values): array
{
    $sentAt  = date('D, d M Y \a\t H:i T');
    $ip      = header_safe((string) ($_SERVER['REMOTE_ADDR'] ?? ''));
    $founder = $values['founder_name'] ?? '';
    $company = $values['company'] ?? '';
    $who     = $founder . ($company !== '' ? ', ' . $company : '');
    $subject = 'Growth audit application — ' . $who;

    // The three answers that decide triage go in the summary line at the top.
    $summary = array_filter([
        $values['interest'] ?? '',
        $values['stage'] ?? '',
        ($values['revenue'] ?? '') !== '' ? 'Revenue: ' . $values['revenue'] : '',
        ($values['timeline'] ?? '') !== '' ? 'Start: ' . $values['timeline'] : '',
    ]);

    $text  = "New growth audit application\n" . implode(' · ', $summary) . "\n";
    $inner = '<p style="margin:0 0 6px;font-size:15px;line-height:1.6;color:' . MAIL_MUTED . '">'
        . 'A founder has applied for a growth audit through the website. Every section of the form is below; replying to this email goes straight to them.</p>'
        . '<p style="margin:0 0 22px;font-size:14px;line-height:1.6;font-weight:600;color:' . MAIL_INK . '">' . e(implode(' · ', $summary)) . '</p>';

    foreach (GROWTH_AUDIT_FORM as $step) {
        $rows   = '';
        $blocks = '';
        $text  .= "\n" . strtoupper($step['title']) . "\n" . str_repeat('-', mb_strlen($step['title'])) . "\n";

        foreach ($step['fields'] as $item) {
            foreach (isset($item['id']) ? [$item] : $item as $f) {
                $value = (string) ($values[$f['id']] ?? '');
                $text .= $f['label'] . ': ' . ($value === '' ? '—' : ($f['type'] === 'textarea' ? "\n" . $value . "\n" : $value)) . "\n";
                if ($f['type'] === 'textarea') {
                    $blocks .= mail_block($f['label'], $value);
                } else {
                    $rows .= mail_row($f['label'], mail_cell($f['type'], $value));
                }
            }
        }

        $inner .= mail_eyebrow($step['title'])
            . ($rows !== '' ? '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:8px">' . $rows . '</table>' : '')
            . $blocks
            . '<div style="height:28px;line-height:28px;font-size:0">&nbsp;</div>';
    }

    $text .= "\nReply to " . ($values['email'] ?? '') . "\n"
        . 'Sent ' . $sentAt . ' from ' . SITE_URL . '/growth-audit' . ($ip !== '' ? ' (IP ' . $ip . ')' : '') . "\n";

    $inner .= mail_button('mailto:' . rawurlencode((string) ($values['email'] ?? '')) . '?subject=' . rawurlencode('Re: your growth audit application — ' . SITE_NAME), 'Reply to ' . $founder);

    $footer = 'Sent ' . e($sentAt) . ' from the <a href="' . e(SITE_URL . '/growth-audit') . '" style="color:' . MAIL_MUTED . '">growth audit form</a>'
        . ($ip !== '' ? ' · visitor IP ' . e($ip) : '')
        . '<br>Every application is also kept on the server, so nothing is lost if this email goes astray.';

    $html = mail_shell(
        'New growth audit application from ' . $who,
        'New application',
        'Growth audit application from ' . $founder,
        $inner,
        $footer
    );

    return ['subject' => $subject, 'text' => $text, 'html' => $html];
}

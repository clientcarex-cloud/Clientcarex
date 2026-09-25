<?php
/**
 * TEMPORARY mail diagnostics — /mail-debug?key=…
 *
 * Shows which transport the enquiry form really uses, whether the SMTP host
 * is reachable, DNS (MX/SPF/DMARC) for the sending domain, the last few
 * enquiry log entries, and sends a test message with the full SMTP
 * conversation printed. Delete this file and its route in index.php once
 * mail is confirmed working.
 */
declare(strict_types=1);

const MAIL_DEBUG_KEY = '934a574bc62aad84';

if (!hash_equals(MAIL_DEBUG_KEY, (string) ($_GET['key'] ?? ''))) {
    http_response_code(404);
    exit('Not found');
}

header('Cache-Control: no-store');
header('X-Robots-Tag: noindex');
header('Content-Type: text/html; charset=UTF-8');

require ROOT . '/app/enquiry.php';
require_once ROOT . '/app/PHPMailer/Exception.php';
require_once ROOT . '/app/PHPMailer/PHPMailer.php';
require_once ROOT . '/app/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

$h    = static fn ($s): string => htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
$smtp = smtp_config();
$file = ROOT . '/storage/mail.php';

/** Hide AUTH credentials that PHPMailer echoes in the transcript. */
function redact_transcript(string $t): string
{
    $t = preg_replace('/(CLIENT -> SERVER: AUTH (?:PLAIN|XOAUTH2) )\S+/', '$1[redacted]', $t);
    // AUTH LOGIN sends base64 user and password on their own lines.
    return (string) preg_replace('/(CLIENT -> SERVER: )[A-Za-z0-9+\/]{8,}={0,2}\s*$/m', '$1[redacted]', $t);
}

/* ---- Test send -------------------------------------------------------- */
$result = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $to        = trim((string) ($_POST['to'] ?? ''));
    $recipients = $to !== '' && filter_var($to, FILTER_VALIDATE_EMAIL) ? [$to] : MAIL_TO;
    $transport = (string) ($_POST['transport'] ?? 'form');
    $transcript = '';
    $started = microtime(true);

    $mail = new PHPMailer(true);
    try {
        $from = $smtp['user'] ?? MAIL_FROM;
        $mail->CharSet = 'UTF-8';
        $mail->setFrom($from, SITE_NAME . ' Website');
        $mail->Sender = $from;
        foreach ($recipients as $r) {
            $mail->addAddress($r);
        }
        $mail->Subject = 'Mail debug test — ' . date('d M Y H:i:s');
        $mail->Body    = "Test message from /mail-debug on " . ($_SERVER['HTTP_HOST'] ?? '?')
            . "\nTransport: $transport\nSent: " . date('c') . "\n";

        if ($transport === 'phpmail') {
            $mail->isMail(); // PHP mail() / local sendmail
        } else {
            $mail->isSMTP();
            $mail->Timeout     = 15;
            $mail->SMTPDebug   = SMTP::DEBUG_CONNECTION;
            $mail->Debugoutput = static function (string $line) use (&$transcript): void {
                $transcript .= rtrim($line) . "\n";
            };
            // Same settings send_enquiry_mail() uses.
            if ($smtp) {
                $port = $transport === 'smtp587' ? 587 : (int) ($smtp['port'] ?? 465);
                $mail->Host       = (string) ($smtp['host'] ?? 'smtp.hostinger.com');
                $mail->Port       = $port;
                $mail->SMTPAuth   = true;
                $mail->Username   = (string) $smtp['user'];
                $mail->Password   = (string) $smtp['pass'];
                $mail->SMTPSecure = $port === 465 ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
            } else {
                $mail->Host        = 'localhost';
                $mail->Port        = 25;
                $mail->SMTPAuth    = false;
                $mail->SMTPAutoTLS = false;
            }
        }

        $ok = $mail->send();
        $result = ['ok' => $ok, 'error' => '', 'id' => $mail->getLastMessageID()];
    } catch (Throwable $e) {
        $result = ['ok' => false, 'error' => $mail->ErrorInfo ?: $e->getMessage(), 'id' => ''];
    }
    $result += [
        'to'         => $recipients,
        'transport'  => $transport,
        'ms'         => (int) ((microtime(true) - $started) * 1000),
        'transcript' => redact_transcript($transcript),
    ];
}

/* ---- Environment checks ----------------------------------------------- */
$host = $smtp['host'] ?? 'smtp.hostinger.com';
$ports = [];
foreach ([465, 587, 25] as $p) {
    $t0 = microtime(true);
    $fp = @fsockopen(($p === 465 ? 'ssl://' : '') . $host, $p, $errno, $errstr, 5);
    $ports[$p] = $fp
        ? 'open (' . (int) ((microtime(true) - $t0) * 1000) . ' ms)'
        : "blocked / failed — $errstr ($errno)";
    if ($fp) {
        fclose($fp);
    }
}

/* ---- TLS probes: why does 465 fail? ---------------------------------- */
function tls_errors(): string
{
    $out = [];
    while ($e = openssl_error_string()) {
        $out[] = $e;
    }
    $last = error_get_last()['message'] ?? '';

    return trim($last . ($out ? ' | OpenSSL: ' . implode(' ; ', $out) : ''));
}

function peer_cert($stream): string
{
    $cert = stream_context_get_params($stream)['options']['ssl']['peer_certificate'] ?? null;
    $info = $cert ? openssl_x509_parse($cert) : null;

    return $info
        ? 'CN=' . ($info['subject']['CN'] ?? '?') . ' · issuer=' . ($info['issuer']['O'] ?? $info['issuer']['CN'] ?? '?')
          . ' · expires ' . date('Y-m-d', $info['validTo_time_t'])
        : 'no certificate';
}

$ip = gethostbyname($host);
$probes = [];
foreach ([true, false] as $verify) {
    error_clear_last();
    $ctx = stream_context_create(['ssl' => [
        'verify_peer' => $verify, 'verify_peer_name' => $verify, 'allow_self_signed' => !$verify,
        'peer_name' => $host, 'SNI_enabled' => true, 'capture_peer_cert' => true,
    ]]);
    $fp = @stream_socket_client("ssl://$host:465", $errno, $errstr, 8, STREAM_CLIENT_CONNECT, $ctx);
    $label = 'Port 465 SSL, certificate check ' . ($verify ? 'ON' : 'OFF');
    if ($fp) {
        stream_set_timeout($fp, 8);
        $probes[$label] = 'OK · banner: ' . trim((string) fgets($fp, 512)) . ' · ' . peer_cert($fp);
        fclose($fp);
    } else {
        $probes[$label] = "FAILED ($errno) $errstr " . tls_errors();
    }
}

// Port 587: plain banner, then STARTTLS upgrade.
error_clear_last();
$fp = @stream_socket_client("tcp://$host:587", $errno, $errstr, 8);
if ($fp) {
    stream_set_timeout($fp, 8);
    $read = static function () use ($fp): string {
        $r = '';
        while (($l = fgets($fp, 512)) !== false) {
            $r .= $l;
            if (!isset($l[3]) || $l[3] === ' ') {
                break;
            }
        }
        return trim($r);
    };
    $banner = $read();
    fwrite($fp, "EHLO debug.local\r\n");
    $read();
    fwrite($fp, "STARTTLS\r\n");
    $st = $read();
    stream_context_set_option($fp, 'ssl', 'peer_name', $host);
    stream_context_set_option($fp, 'ssl', 'capture_peer_cert', true);
    $tls = str_starts_with($st, '220') && @stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
    $probes['Port 587 banner'] = $banner;
    $probes['Port 587 STARTTLS'] = $tls ? 'OK · ' . peer_cert($fp) : "FAILED · server said: $st " . tls_errors();
    fclose($fp);
} else {
    $probes['Port 587'] = "FAILED ($errno) $errstr";
}

$ca = openssl_get_cert_locations();
$probes['CA bundle'] = ($ca['default_cert_file'] ?? '?') . (is_file($ca['default_cert_file'] ?? '') ? ' (exists)' : ' (MISSING)')
    . ' · openssl.cafile=' . (ini_get('openssl.cafile') ?: '—');

$domain = substr((string) strrchr($smtp['user'] ?? MAIL_FROM, '@'), 1);
$mx = $txt = $dmarc = [];
if (function_exists('dns_get_record')) {
    foreach (@dns_get_record($domain, DNS_MX) ?: [] as $r) {
        $mx[] = $r['pri'] . ' ' . $r['target'];
    }
    foreach (@dns_get_record($domain, DNS_TXT) ?: [] as $r) {
        if (str_starts_with($r['txt'] ?? '', 'v=spf1')) {
            $txt[] = $r['txt'];
        }
    }
    foreach (@dns_get_record('_dmarc.' . $domain, DNS_TXT) ?: [] as $r) {
        $dmarc[] = $r['txt'] ?? '';
    }
}

$log = [];
$logFile = ROOT . '/storage/enquiries.log';
if (is_file($logFile)) {
    $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
    foreach (array_reverse(array_slice($lines, -15)) as $line) {
        $log[] = json_decode($line, true) ?: ['raw' => $line];
    }
}

$env = [
    'PHP version'             => PHP_VERSION,
    'OpenSSL'                 => extension_loaded('openssl') ? OPENSSL_VERSION_TEXT : 'MISSING — SMTPS/STARTTLS cannot work',
    'PHPMailer'               => PHPMailer::VERSION,
    'storage/mail.php'        => is_file($file) ? 'present' : 'MISSING — form falls back to localhost:25 (mail usually never leaves the server)',
    'SMTP config valid'       => $smtp ? 'yes' : 'no',
    'SMTP host'               => $smtp ? $host : 'localhost',
    'SMTP port'               => $smtp ? (string) ($smtp['port'] ?? 465) : '25',
    'SMTP user'               => $smtp['user'] ?? '—',
    'SMTP password'           => $smtp ? str_repeat('•', 6) . ' (' . strlen((string) $smtp['pass']) . ' chars)' : '—',
    'From / envelope sender'  => $smtp['user'] ?? MAIL_FROM,
    'MAIL_TO'                 => implode(', ', MAIL_TO),
    'mail() available'        => function_exists('mail') ? 'yes (sendmail_path: ' . (ini_get('sendmail_path') ?: '—') . ')' : 'disabled',
    'disable_functions'       => ini_get('disable_functions') ?: '—',
    'Server'                  => ($_SERVER['SERVER_SOFTWARE'] ?? '?') . ' · ' . php_uname('n'),
    'storage writable'        => is_writable(ROOT . '/storage') ? 'yes' : 'NO — enquiries.log cannot be written',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>Mail debug</title>
<style>
  body{font:14px/1.5 system-ui,-apple-system,Segoe UI,Roboto,sans-serif;margin:0;background:#f4f7f2;color:#101010}
  main{max-width:960px;margin:0 auto;padding:24px 16px 60px}
  h1{font-size:22px;margin:0 0 4px} h2{font-size:16px;margin:28px 0 8px}
  .warn{background:#fff4d6;border:1px solid #f0d27a;padding:10px 14px;border-radius:8px}
  table{width:100%;border-collapse:collapse;background:#fff;border:1px solid #e2e9e4;border-radius:8px;overflow:hidden}
  td,th{padding:8px 12px;border-bottom:1px solid #e2e9e4;text-align:left;vertical-align:top;word-break:break-word}
  th{width:32%;color:#5c6a65;font-weight:600}
  .ok{color:#067a4b;font-weight:600} .bad{color:#b3261e;font-weight:600}
  pre{background:#06291f;color:#d7ffe0;padding:14px;border-radius:8px;overflow:auto;font-size:12px;white-space:pre-wrap;word-break:break-all}
  form{background:#fff;border:1px solid #e2e9e4;border-radius:8px;padding:14px;display:flex;flex-wrap:wrap;gap:10px;align-items:end}
  label{display:flex;flex-direction:column;gap:4px;font-weight:600;font-size:13px;flex:1 1 220px}
  input,select{font:inherit;padding:8px 10px;border:1px solid #cfd8d3;border-radius:6px}
  button{font:inherit;font-weight:700;background:#065845;color:#fff;border:0;border-radius:999px;padding:10px 22px;cursor:pointer}
</style>
</head>
<body>
<main>
  <h1>Mail debug</h1>
  <p class="warn"><strong>Temporary page.</strong> Remove <code>app/mail-debug.php</code> and its route in <code>index.php</code> when finished.</p>

  <h2>Send a test</h2>
  <form method="post" action="?key=<?= $h(MAIL_DEBUG_KEY) ?>">
    <label>Recipient (blank = MAIL_TO)
      <input type="email" name="to" value="<?= $h($_POST['to'] ?? '') ?>" placeholder="<?= $h(implode(', ', MAIL_TO)) ?>">
    </label>
    <label>Transport
      <select name="transport">
        <option value="form" <?= ($_POST['transport'] ?? '') !== 'phpmail' ? 'selected' : '' ?>>Same as contact form (<?= $smtp ? 'SMTP ' . $h($host) : 'localhost:25' ?>)</option>
        <option value="smtp587" <?= ($_POST['transport'] ?? '') === 'smtp587' ? 'selected' : '' ?>>SMTP <?= $h($host) ?> port 587 + STARTTLS</option>
        <option value="phpmail" <?= ($_POST['transport'] ?? '') === 'phpmail' ? 'selected' : '' ?>>PHP mail() — for comparison</option>
      </select>
    </label>
    <button type="submit">Send test mail</button>
  </form>

  <?php if ($result): ?>
    <h2>Result</h2>
    <table>
      <tr><th>Status</th><td class="<?= $result['ok'] ? 'ok' : 'bad' ?>"><?= $result['ok'] ? 'Accepted by the mail server' : 'FAILED' ?></td></tr>
      <?php if ($result['error'] !== ''): ?><tr><th>Error</th><td class="bad"><?= $h($result['error']) ?></td></tr><?php endif ?>
      <tr><th>Transport</th><td><?= $h($result['transport']) ?></td></tr>
      <tr><th>Recipients</th><td><?= $h(implode(', ', $result['to'])) ?></td></tr>
      <tr><th>Message-ID</th><td><?= $h($result['id'] ?: '—') ?></td></tr>
      <tr><th>Time</th><td><?= $result['ms'] ?> ms</td></tr>
    </table>
    <?php if ($result['transcript'] !== ''): ?>
      <h2>SMTP conversation</h2>
      <pre><?= $h($result['transcript']) ?></pre>
    <?php endif ?>
    <p>"Accepted" means the server replied <code>250</code> to the message. If it still doesn't arrive, check spam, then search the Message-ID in the mailbox provider's logs / the Sent folder of <?= $h($smtp['user'] ?? MAIL_FROM) ?>.</p>
  <?php endif ?>

  <h2>Configuration</h2>
  <table>
    <?php foreach ($env as $k => $v): ?>
      <tr><th><?= $h($k) ?></th><td class="<?= str_contains($v, 'MISSING') || str_starts_with($v, 'NO') ? 'bad' : '' ?>"><?= $h($v) ?></td></tr>
    <?php endforeach ?>
  </table>

  <h2>Connectivity to <?= $h($host) ?></h2>
  <table>
    <?php foreach ($ports as $p => $v): ?>
      <tr><th>Port <?= $p ?></th><td class="<?= str_starts_with($v, 'open') ? 'ok' : 'bad' ?>"><?= $h($v) ?></td></tr>
    <?php endforeach ?>
  </table>

  <h2>TLS / SMTP probes (<?= $h($host) ?> → <?= $h($ip) ?>)</h2>
  <table>
    <?php foreach ($probes as $k => $v): ?>
      <tr><th><?= $h($k) ?></th><td class="<?= str_starts_with($v, 'FAILED') || str_contains($v, 'MISSING') ? 'bad' : (str_starts_with($v, 'OK') ? 'ok' : '') ?>"><?= $h($v) ?></td></tr>
    <?php endforeach ?>
  </table>

  <h2>DNS for <?= $h($domain) ?></h2>
  <table>
    <tr><th>MX</th><td class="<?= $mx ? '' : 'bad' ?>"><?= $h(implode("\n", $mx) ?: 'none found') ?></td></tr>
    <tr><th>SPF</th><td class="<?= $txt ? '' : 'bad' ?>"><?= $h(implode("\n", $txt) ?: 'none found') ?></td></tr>
    <tr><th>DMARC</th><td><?= $h(implode("\n", $dmarc) ?: 'none found') ?></td></tr>
  </table>

  <h2>Last enquiries (storage/enquiries.log, newest first)</h2>
  <?php if (!$log): ?>
    <p>No entries.</p>
  <?php else: ?>
    <table>
      <tr><th>When</th><td><strong>Name · email · mailed · error</strong></td></tr>
      <?php foreach ($log as $e): ?>
        <tr>
          <th><?= $h($e['at'] ?? '?') ?></th>
          <td>
            <?= $h(($e['name'] ?? '') . ' · ' . ($e['email'] ?? '')) ?> ·
            <span class="<?= !empty($e['mailed']) ? 'ok' : 'bad' ?>"><?= !empty($e['mailed']) ? 'mailed' : 'not mailed' ?></span>
            <?= isset($e['mail_error']) ? ' · ' . $h($e['mail_error']) : '' ?>
            <?= isset($e['raw']) ? $h($e['raw']) : '' ?>
          </td>
        </tr>
      <?php endforeach ?>
    </table>
  <?php endif ?>
</main>
</body>
</html>

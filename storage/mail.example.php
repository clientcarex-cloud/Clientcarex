<?php
/**
 * Mailbox credentials for the contact form — EXAMPLE. Copy this file to one
 * of the two places the site looks (the first one found wins):
 *
 *   1. RECOMMENDED: the folder that contains public_html, saved as
 *        clientcarex-mail.php
 *      That folder is outside the web root, so no browser can reach it.
 *
 *   2. Or inside the site:  homepage/storage/mail.php
 *      (.htaccess blocks the storage folder from the web.)
 *
 * Then set the file's permissions to 600 (owner read/write only) in the file
 * manager. Never put this file in git — both names are ignored already.
 */
return [
    // Outgoing mail server and port. On Hostinger use these as they are.
    'host' => 'smtp.hostinger.com',
    'port' => 465,                          // 465 = TLS from the start; 587 = STARTTLS

    // The mailbox that sends the email, and its own password
    // (the one used for webmail — not the hosting account password).
    'user' => 'care@clientcarex.com',
    'pass' => 'PASTE-MAILBOX-PASSWORD-HERE',

    // Appears as the sender. Must be a mailbox the login is allowed to send as.
    'from' => 'care@clientcarex.com',

    // Where enquiries are delivered. Any address; leave '' to use the sender.
    'to'   => 'digicarelynx@gmail.com',

    // Hostinger's servers answer for smtp.hostinger.com with their own
    // certificate, so certificate checks are skipped on that hop.
    'insecure' => true,

    'timeout' => 15,
];

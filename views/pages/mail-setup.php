<?php
/**
 * @var array $setup  from app/mail-setup.php:
 *   configured bool, mailbox string, to string, last ?array,
 *   result ?array{ok: bool, text: string, detail?: string}, errors array
 */
$errors = $setup['errors'];
$result = $setup['result'];
$last   = $setup['last'];
$action = BASE . '/mail-setup'; // stays under the install folder on purpose

part('page-hero', [
    'crumb' => 'Contact form',
    'title' => 'Email setup',
    'lede'  => 'Connect the contact form to the mailbox that should receive enquiries. Nothing else on the website changes.',
]);
?>

<section class="section">
  <div class="container">
    <div class="split" style="align-items:start">
      <div class="split__body" data-reveal>
        <span class="eyebrow">Status</span>
        <h2><?= $setup['configured'] ? 'Email is connected' : 'Email is not connected yet' ?></h2>
        <?php if ($setup['configured']): ?>
          <p>
            Enquiries are emailed to <strong><?= e($setup['to']) ?></strong>
            from the mailbox <strong><?= e($setup['mailbox']) ?></strong>.
            To change either, or after a password change, fill in the form again.
          </p>
        <?php else: ?>
          <p>
            Until this is done, enquiries are only saved on the server and no
            email goes out. Enter the mailbox details once and send a test.
          </p>
        <?php endif ?>

        <?php if ($last): ?>
          <div class="form__status <?= $last['mailed'] ? 'form__status--ok' : 'form__status--warn' ?>" style="margin-top:1.5rem">
            <span class="form__status-icon" aria-hidden="true"></span>
            <span>
              Latest enquiry (<?= e(date('d M Y, H:i', strtotime($last['at']) ?: time())) ?>):
              <?php if ($last['mailed']): ?>
                emailed to <?= e($last['to']) ?><?= $last['via'] !== '' ? ' via ' . e($last['via']) : '' ?>.
              <?php else: ?>
                saved but <strong>not emailed</strong>.
              <?php endif ?>
            </span>
          </div>
        <?php endif ?>

        <?php part('check-list', ['items' => [
            'Use the password of the mailbox itself, the one for webmail',
            'The test email goes to the delivery address below',
            'Settings are saved only when the test email succeeds',
        ]]) ?>
      </div>

      <form class="form" id="setup" method="post" action="<?= e($action) ?>#setup" novalidate data-reveal>
        <?php if ($result !== null || isset($errors['form'])): ?>
          <?php $ok = $result !== null && $result['ok']; ?>
          <div class="form__status form__status--<?= $ok ? 'ok' : 'error' ?>" role="<?= $ok ? 'status' : 'alert' ?>">
            <span class="form__status-icon" aria-hidden="true"></span>
            <span>
              <?= e($result['text'] ?? $errors['form']) ?>
              <?php if (!empty($result['detail'])): ?>
                <details style="margin-top:.5rem">
                  <summary style="cursor:pointer">Technical details</summary>
                  <code style="display:block;margin-top:.4rem;font-size:.8125rem;white-space:pre-wrap;word-break:break-word"><?= e($result['detail']) ?></code>
                </details>
              <?php endif ?>
            </span>
          </div>
        <?php endif ?>

        <?php part('field', [
            'id' => 'mailbox', 'label' => 'Mailbox that sends the email', 'type' => 'email',
            'auto' => 'username', 'req' => true, 'errors' => $errors, 'value' => e($setup['mailbox']),
        ]) ?>

        <div class="field<?= isset($errors['password']) ? ' field--invalid' : '' ?>">
          <label for="password">Password for that mailbox</label>
          <input type="password" id="password" name="password" autocomplete="current-password" required
                 aria-describedby="password-error"<?= isset($errors['password']) ? ' aria-invalid="true"' : '' ?>>
          <span class="field__hint">Not saved anywhere until the test email goes through.</span>
          <span class="field__error" id="password-error" role="alert"><?= isset($errors['password']) ? e($errors['password']) : '' ?></span>
        </div>

        <?php part('field', [
            'id' => 'to', 'label' => 'Deliver enquiries to', 'type' => 'email',
            'auto' => 'off', 'req' => true, 'errors' => $errors, 'value' => e($setup['to']),
        ]) ?>

        <input type="hidden" name="token" value="<?= e(csrf_token()) ?>">

        <button class="btn btn--lg btn--block" type="submit">Send a test email and save</button>
        <p class="field__hint form__foot">Takes a few seconds. The result appears at the top of this box.</p>
      </form>
    </div>
  </div>
</section>

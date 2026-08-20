<?php
/** @var array $form  ['errors' => [...], 'values' => [...], 'sent' => bool] */
$errors = $form['errors'];
$old    = static fn (string $k): string => e($form['values'][$k] ?? '');

$fields = [
    ['name', 'Full name', 'text', 'name', true],
    ['company', 'Company', 'text', 'organization', false],
    ['email', 'Work email', 'email', 'email', true],
    ['phone', 'Phone', 'tel', 'tel', false],
];

part('page-hero', [
    'crumb' => 'Contact',
    'title' => 'Request a demo',
    'lede'  => "Tell us where the manual work is. We'll show you what ClientcareX automates, what it costs, and what it saves — in about 30 minutes.",
]);
?>

<section class="section">
  <div class="container">
    <div class="split" style="align-items:start">
      <div class="split__body" data-reveal>
        <span class="eyebrow">Talk to us</span>
        <h2>Straight to a human</h2>
        <p>
          No call-centre queue. Reach the team directly by phone or email, or
          send the form and we'll come back within one working day.
        </p>

        <?php part('contact-strip', ['style' => 'grid-template-columns:1fr; margin-top:2rem']) ?>

        <p class="muted" style="margin-top:1.5rem;font-size:.9375rem">
          Already a customer?
          <a class="link-arrow" href="<?= APP_LOGIN ?>">Log in to your workspace</a>
        </p>
      </div>

      <form class="form" method="post" action="<?= url('contact') ?>" data-reveal>
        <?php if ($form['sent']): ?>
          <p class="field__hint" role="status" style="margin-bottom:1rem">
            Thanks — your request is with the team. We'll come back to you within one working day.
          </p>
        <?php elseif (isset($errors['form'])): ?>
          <p class="field__hint" role="alert" style="margin-bottom:1rem"><?= e($errors['form']) ?></p>
        <?php endif ?>

        <div class="form__row">
          <?php foreach (array_slice($fields, 0, 2) as [$id, $label, $type, $auto, $req]): ?>
            <?php part('field', compact('id', 'label', 'type', 'auto', 'req', 'errors') + ['value' => $old($id)]) ?>
          <?php endforeach ?>
        </div>

        <div class="form__row">
          <?php foreach (array_slice($fields, 2) as [$id, $label, $type, $auto, $req]): ?>
            <?php part('field', compact('id', 'label', 'type', 'auto', 'req', 'errors') + ['value' => $old($id)]) ?>
          <?php endforeach ?>
        </div>

        <div class="field">
          <label for="team">Team size</label>
          <select id="team" name="team">
            <option value="">Select…</option>
            <?php foreach (TEAM_SIZES as $size): ?>
              <option<?= ($form['values']['team'] ?? '') === $size ? ' selected' : '' ?>><?= e($size) ?></option>
            <?php endforeach ?>
          </select>
        </div>

        <div class="field">
          <label for="message">What would you like to automate?</label>
          <textarea id="message" name="message" rows="5"
                    placeholder="e.g. lead follow-up, payment reminders, attendance and payroll"><?= $old('message') ?></textarea>
          <span class="field__hint">
            <?= isset($errors['message']) ? e($errors['message']) : 'The more specific you are, the more useful the demo.' ?>
          </span>
        </div>

        <p style="position:absolute;left:-9999px" aria-hidden="true">
          <label for="website">Leave this empty</label>
          <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
        </p>
        <input type="hidden" name="token" value="<?= e(csrf_token()) ?>">

        <button class="btn btn--lg btn--block" type="submit">Request A Demo</button>
      </form>
    </div>
  </div>
</section>

<section class="section section--paper">
  <div class="container">
    <?php part('section-head', ['eyebrow' => 'FAQs', 'title' => 'Frequently asked questions']) ?>
    <?php part('faq') ?>
  </div>
</section>

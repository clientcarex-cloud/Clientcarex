<?php
/**
 * The growth-audit application: every section of GROWTH_AUDIT_FORM rendered
 * as one step. With JavaScript the steps are shown one at a time with a
 * progress bar; without it the whole form is on the page and posts normally.
 *
 * @var array $form  ['errors' => [...], 'values' => [...], 'state' => ''|'sent'|'logged']
 */
$errors = $form['errors'];
$values = $form['values'];
$status = enquiry_message($form['state']);
if (isset($errors['form'])) {
    $status = ['tone' => 'error', 'text' => $errors['form']];
}
$focus = array_key_first(array_diff_key($errors, ['form' => 1])) ?? (isset($errors['form']) ? 'founder_name' : null);
$steps = GROWTH_AUDIT_FORM;
$total = count($steps);

$render = static function (array $f) use ($values, $errors, $focus): void {
    $common = [
        'id'     => $f['id'],
        'label'  => $f['label'],
        'req'    => $f['req'] ?? false,
        'hint'   => $f['hint'] ?? '',
        'msg'    => $f['msg'] ?? '',
        'errors' => $errors,
        'focus'  => $focus === $f['id'],
    ];
    switch ($f['type']) {
        case 'select':
            part('select', $common + ['options' => $f['options'], 'value' => (string) ($values[$f['id']] ?? '')]);
            break;
        case 'textarea':
            part('textarea', $common + [
                'rows' => $f['rows'] ?? 4, 'max' => $f['max'] ?? 0,
                'placeholder' => $f['placeholder'] ?? '', 'value' => e((string) ($values[$f['id']] ?? '')),
            ]);
            break;
        case 'checks':
            part('checks', $common + ['options' => $f['options'], 'values' => (array) ($values[$f['id']] ?? [])]);
            break;
        default:
            part('field', $common + [
                'type' => $f['type'], 'auto' => $f['auto'] ?? 'off', 'max' => $f['max'] ?? 0,
                'placeholder' => $f['placeholder'] ?? '', 'value' => e((string) ($values[$f['id']] ?? '')),
            ]);
    }
};

part('page-hero', [
    'crumb' => 'Growth Audit',
    'title' => 'Apply for a growth audit',
    'lede'  => "Five short sections — you, your track record, the business, how it grows today, and what you want from us. It takes about ten minutes, and the team reads every one.",
]);
?>

<section class="section section--paper">
  <div class="container">
    <div class="form-layout">
      <aside class="form-aside" data-reveal>
        <span class="eyebrow">Before you start</span>
        <h2>What happens next</h2>
        <p>
          We fund every campaign we run, so we choose partners as carefully as
          you choose an agency. The more honest the numbers, the faster we can
          tell you whether the model fits.
        </p>

        <?php part('check-list', ['items' => [
            'Reviewed by a partner, not a sales desk',
            'A reply within one working day — yes, no, or a call',
            'Nothing you send is shared outside the team',
            'No fee, no commitment, nothing to sign',
        ]]) ?>

        <p class="muted" style="font-size:.9375rem">
          Just want to ask a quick question?
          <a class="link-arrow" href="<?= url('contact') ?>">Use the short contact form</a>
        </p>

        <?php part('contact-strip', ['style' => 'grid-template-columns:1fr; margin-top:1.5rem']) ?>
      </aside>

      <form class="form form--wide" id="audit" method="post" action="<?= url('growth-audit') ?>#audit" novalidate
            data-enquiry data-email="<?= e(EMAIL) ?>" data-phone="<?= e(PHONE) ?>" data-reveal>
        <div class="form__status<?= $status['tone'] !== '' ? ' form__status--' . $status['tone'] : '' ?>"
             data-status role="<?= $status['tone'] === 'ok' ? 'status' : 'alert' ?>" aria-live="polite"
             <?= $status['tone'] === '' ? 'hidden' : '' ?>>
          <span class="form__status-icon" aria-hidden="true"></span>
          <span data-status-text><?= e($status['text']) ?></span>
        </div>

        <ol class="form-progress" data-progress aria-label="Form sections">
          <?php foreach ($steps as $i => $step): ?>
            <li data-progress-item>
              <span class="form-progress__num" aria-hidden="true"><?= $i + 1 ?></span>
              <span class="form-progress__label"><?= e($step['title']) ?></span>
            </li>
          <?php endforeach ?>
        </ol>

        <?php foreach ($steps as $i => $step): ?>
          <fieldset class="form-step" data-step>
            <legend class="form-step__head">
              <span class="eyebrow">Section <?= $i + 1 ?> of <?= $total ?></span>
              <span class="form-step__title"><?= e($step['title']) ?></span>
              <?php if (!empty($step['lede'])): ?><span class="form-step__lede"><?= e($step['lede']) ?></span><?php endif ?>
            </legend>

            <?php foreach ($step['fields'] as $item): ?>
              <?php if (isset($item['id'])): ?>
                <?php $render($item) ?>
              <?php else: ?>
                <div class="form__row">
                  <?php foreach ($item as $f) { $render($f); } ?>
                </div>
              <?php endif ?>
            <?php endforeach ?>

            <?php if ($i < $total - 1): ?>
              <div class="form-step__actions">
                <?php if ($i > 0): ?>
                  <button class="btn btn--ghost" type="button" data-step-back>Back</button>
                <?php endif ?>
                <button class="btn" type="button" data-step-next>Continue<?= icon('arrow', 16) ?></button>
              </div>
            <?php endif ?>
          </fieldset>
        <?php endforeach ?>

        <p style="position:absolute;left:-9999px" aria-hidden="true">
          <label for="website">Leave this empty</label>
          <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
        </p>
        <input type="hidden" name="token" value="<?= e(csrf_token()) ?>">

        <div class="form-step__actions form-step__actions--final">
          <button class="btn btn--ghost" type="button" data-step-back>Back</button>
          <button class="btn btn--lg" type="submit" data-submit>
            <span data-submit-label>Send my application</span>
          </button>
        </div>
        <p class="field__hint form__foot">
          By sending this you agree to our <a href="<?= url('privacy') ?>">privacy policy</a>.
          Or email <a href="mailto:<?= e(EMAIL) ?>"><?= e(EMAIL) ?></a> · call <a href="tel:<?= PHONE_HREF ?>"><?= e(PHONE) ?></a>
        </p>
      </form>
    </div>
  </div>
</section>

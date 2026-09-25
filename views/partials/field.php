<?php
/**
 * One labelled input, with its validation message when there is one.
 * The error slot is always rendered (empty when there is nothing to say) so
 * the form's JavaScript can fill it in without touching the markup.
 * @var string      $id @var string $label @var string $type
 * @var string      $auto @var bool $req @var string $value @var array $errors
 * @var bool|null   $focus        put the cursor here after a failed no-JS post
 * @var int|null    $max          maxlength
 * @var string|null $hint         helper text under the input
 * @var string|null $placeholder
 * @var string|null $msg          message when a required field is left empty (used by the JS)
 */
$invalid = isset($errors[$id]);
$hint    = $hint ?? '';
$describe = ($hint !== '' ? $id . '-hint ' : '') . $id . '-error';
?>
<div class="field<?= $invalid ? ' field--invalid' : '' ?>">
  <label for="<?= $id ?>"><?= e($label) ?><?= $req ? '' : ' <span class="field__optional">(optional)</span>' ?></label>
  <input type="<?= $type ?>" id="<?= $id ?>" name="<?= $id ?>" autocomplete="<?= $auto ?? 'off' ?>"
         value="<?= $value ?>" aria-describedby="<?= $describe ?>"<?= $req ? ' required' : '' ?><?= !empty($max) ? ' maxlength="' . (int) $max . '"' : '' ?><?= !empty($placeholder) ? ' placeholder="' . e($placeholder) . '"' : '' ?><?= !empty($msg) ? ' data-required="' . e($msg) . '"' : '' ?><?= $invalid ? ' aria-invalid="true"' : '' ?><?= !empty($focus) ? ' autofocus' : '' ?>>
  <?php if ($hint !== ''): ?><span class="field__hint" id="<?= $id ?>-hint"><?= e($hint) ?></span><?php endif ?>
  <span class="field__error" id="<?= $id ?>-error" role="alert"><?= $invalid ? e($errors[$id]) : '' ?></span>
</div>

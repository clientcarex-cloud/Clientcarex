<?php
/**
 * One labelled input, with its validation message when there is one.
 * The error slot is always rendered (empty when there is nothing to say) so
 * the form's JavaScript can fill it in without touching the markup.
 * @var string $id @var string $label @var string $type
 * @var string $auto @var bool $req @var string $value @var array $errors
 * @var bool|null $focus  put the cursor here after a failed no-JS post
 */
$invalid = isset($errors[$id]);
?>
<div class="field<?= $invalid ? ' field--invalid' : '' ?>">
  <label for="<?= $id ?>"><?= e($label) ?><?= $req ? '' : ' <span class="field__optional">(optional)</span>' ?></label>
  <input type="<?= $type ?>" id="<?= $id ?>" name="<?= $id ?>" autocomplete="<?= $auto ?>"
         value="<?= $value ?>" aria-describedby="<?= $id ?>-error"<?= $req ? ' required' : '' ?><?= $invalid ? ' aria-invalid="true"' : '' ?><?= !empty($focus) ? ' autofocus' : '' ?>>
  <span class="field__error" id="<?= $id ?>-error" role="alert"><?= $invalid ? e($errors[$id]) : '' ?></span>
</div>

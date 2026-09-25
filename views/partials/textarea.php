<?php
/**
 * One labelled multi-line field.
 * @var string      $id @var string $label @var string $value @var array $errors
 * @var bool|null   $req @var bool|null $focus
 * @var int|null    $rows @var int|null $max
 * @var string|null $hint @var string|null $placeholder
 * @var string|null $msg   message when a required field is left empty (used by the JS)
 */
$invalid = isset($errors[$id]);
$req     = !empty($req);
$hint    = $hint ?? '';
$describe = ($hint !== '' ? $id . '-hint ' : '') . $id . '-error';
?>
<div class="field<?= $invalid ? ' field--invalid' : '' ?>">
  <label for="<?= $id ?>"><?= e($label) ?><?= $req ? '' : ' <span class="field__optional">(optional)</span>' ?></label>
  <textarea id="<?= $id ?>" name="<?= $id ?>" rows="<?= (int) ($rows ?? 4) ?>" aria-describedby="<?= $describe ?>"<?= $req ? ' required' : '' ?><?= !empty($max) ? ' maxlength="' . (int) $max . '"' : '' ?><?= !empty($placeholder) ? ' placeholder="' . e($placeholder) . '"' : '' ?><?= !empty($msg) ? ' data-required="' . e($msg) . '"' : '' ?><?= $invalid ? ' aria-invalid="true"' : '' ?><?= !empty($focus) ? ' autofocus' : '' ?>><?= $value ?></textarea>
  <?php if ($hint !== ''): ?><span class="field__hint" id="<?= $id ?>-hint"><?= e($hint) ?></span><?php endif ?>
  <span class="field__error" id="<?= $id ?>-error" role="alert"><?= $invalid ? e($errors[$id]) : '' ?></span>
</div>

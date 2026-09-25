<?php
/**
 * One labelled dropdown.
 * @var string      $id @var string $label @var array $options @var string $value
 * @var array|null  $errors @var bool|null $focus
 * @var bool|null   $req
 * @var string|null $hint
 * @var string|null $msg   message when a required choice is left empty (used by the JS)
 */
$invalid = isset($errors[$id]);
$req     = !empty($req);
$hint    = $hint ?? '';
$describe = ($hint !== '' ? $id . '-hint ' : '') . $id . '-error';
?>
<div class="field<?= $invalid ? ' field--invalid' : '' ?>">
  <label for="<?= $id ?>"><?= e($label) ?><?= $req ? '' : ' <span class="field__optional">(optional)</span>' ?></label>
  <select id="<?= $id ?>" name="<?= $id ?>" aria-describedby="<?= $describe ?>"<?= $req ? ' required' : '' ?><?= !empty($msg) ? ' data-required="' . e($msg) . '"' : '' ?><?= $invalid ? ' aria-invalid="true"' : '' ?><?= !empty($focus) ? ' autofocus' : '' ?>>
    <option value="">Select…</option>
    <?php foreach ($options as $option): ?>
      <option<?= $value === $option ? ' selected' : '' ?>><?= e($option) ?></option>
    <?php endforeach ?>
  </select>
  <?php if ($hint !== ''): ?><span class="field__hint" id="<?= $id ?>-hint"><?= e($hint) ?></span><?php endif ?>
  <span class="field__error" id="<?= $id ?>-error" role="alert"><?= $invalid ? e($errors[$id]) : '' ?></span>
</div>

<?php
/**
 * A group of tick boxes posted as one array (name="id[]").
 * @var string      $id @var string $label @var array $options @var array $values
 * @var array|null  $errors @var bool|null $req @var string|null $hint
 */
$invalid = isset($errors[$id]);
$req     = !empty($req);
$hint    = $hint ?? '';
?>
<fieldset class="field check-group<?= $invalid ? ' field--invalid' : '' ?>" aria-describedby="<?= $hint !== '' ? $id . '-hint ' : '' ?><?= $id ?>-error">
  <legend><?= e($label) ?><?= $req ? '' : ' <span class="field__optional">(optional)</span>' ?></legend>
  <div class="check-group__options">
    <?php foreach ($options as $i => $option): ?>
      <label class="check-group__option">
        <input type="checkbox" name="<?= $id ?>[]" value="<?= e($option) ?>"<?= in_array($option, $values, true) ? ' checked' : '' ?>>
        <span><?= e($option) ?></span>
      </label>
    <?php endforeach ?>
  </div>
  <?php if ($hint !== ''): ?><span class="field__hint" id="<?= $id ?>-hint"><?= e($hint) ?></span><?php endif ?>
  <span class="field__error" id="<?= $id ?>-error" role="alert"><?= $invalid ? e($errors[$id]) : '' ?></span>
</fieldset>

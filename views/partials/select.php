<?php
/**
 * One labelled dropdown.
 * @var string $id @var string $label @var array $options @var string $value
 * @var array|null $errors @var bool|null $focus
 */
$invalid = isset($errors[$id]);
?>
<div class="field<?= $invalid ? ' field--invalid' : '' ?>">
  <label for="<?= $id ?>"><?= e($label) ?></label>
  <select id="<?= $id ?>" name="<?= $id ?>" aria-describedby="<?= $id ?>-error"<?= $invalid ? ' aria-invalid="true"' : '' ?><?= !empty($focus) ? ' autofocus' : '' ?>>
    <option value="">Select…</option>
    <?php foreach ($options as $option): ?>
      <option<?= $value === $option ? ' selected' : '' ?>><?= e($option) ?></option>
    <?php endforeach ?>
  </select>
  <span class="field__error" id="<?= $id ?>-error" role="alert"><?= $invalid ? e($errors[$id]) : '' ?></span>
</div>

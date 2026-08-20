<?php /** Login / Sign Up pair — rendered twice in the header, defined once. */ ?>
<div class="<?= $class ?? 'nav__actions' ?>">
  <a class="btn btn--ghost btn--sm" href="<?= APP_LOGIN ?>">Login</a>
  <a class="btn btn--sm" href="<?= APP_REGISTER ?>">Sign Up</a>
</div>

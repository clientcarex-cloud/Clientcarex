<?php /** Pricing tables, driven by PLANS. */ ?>
<div class="plans" data-reveal>
  <?php foreach (PLANS as $plan): ?>
    <article class="plan<?= !empty($plan['featured']) ? ' plan--featured' : '' ?>">
      <span class="plan__badge"><?= e($plan['badge']) ?></span>
      <h3 class="plan__name"><?= e($plan['name']) ?></h3>
      <p class="plan__tagline">All the features you need</p>
      <p class="plan__price">
        <span class="plan__amount"><?= e($plan['price']) ?></span>
        <span class="plan__period">/user/month</span>
      </p>
      <p class="plan__summary"><?= e($plan['summary']) ?></p>
      <ul class="plan__features">
        <?php if (!empty($plan['inherits'])): ?>
          <li><?= icon('check', 15, '3') ?><strong>Everything in <?= e($plan['inherits']) ?></strong></li>
        <?php endif ?>
        <?php foreach ($plan['features'] as $feature): ?>
          <li><?= icon('check', 15, '3') ?><?= e($feature) ?></li>
        <?php endforeach ?>
      </ul>
      <div class="plan__cta">
        <a class="btn<?= empty($plan['featured']) ? ' btn--ghost' : '' ?> btn--block" href="<?= url('contact') ?>">Get Started</a>
        <p class="plan__terms"><?= e($plan['terms']) ?></p>
      </div>
    </article>
  <?php endforeach ?>
</div>

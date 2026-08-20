<?php part('page-hero', [
    'crumb' => 'Pricing',
    'title' => 'Find the right package',
    'lede'  => 'Transparent per-user pricing, billed yearly. Every plan includes onboarding, and every plan is covered by the 30-day money back guarantee.',
]) ?>

<section class="section">
  <div class="container">
    <?php part('plans') ?>

    <p class="pricing-note" style="margin-top:2.5rem">
      Prices are per user, per month, billed yearly in INR. Minimum seat counts
      apply per plan. Need a different mix of modules or more than 100 seats?
      <?php part('link-arrow', ['label' => 'Talk to us about Enterprise', 'href' => 'contact', 'arrow' => false]) ?>
    </p>
  </div>
</section>

<section class="section section--dark">
  <div class="container">
    <?php part('section-head', [
        'eyebrow' => "What's included everywhere",
        'title'   => "The parts we don't charge extra for",
    ]) ?>
    <?php part('stats', ['items' => STATS_PRICING]) ?>
  </div>
</section>

<section class="section section--paper">
  <div class="container">
    <?php part('section-head', ['eyebrow' => 'FAQs', 'title' => 'Pricing questions, answered']) ?>
    <?php part('faq') ?>
  </div>
</section>

<?php part('cta', [
    'title'   => 'Not sure which plan fits?',
    'lede'    => "Send us your team size and the processes you want automated. We'll come back with the plan that covers it — and the one that doesn't, so you can compare honestly.",
    'buttons' => [
        ['Request A Demo', 'contact', 'btn--lime btn--lg'],
        ['Compare features', 'features', 'btn--ghost btn--lg'],
    ],
]) ?>

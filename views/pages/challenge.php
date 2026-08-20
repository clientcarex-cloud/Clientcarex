<?php part('page-hero', [
    'crumb'   => '90 Day Challenge',
    'title'   => 'Go Big on Automation 🔥 The 90-Day Business Transformation Challenge 🚀',
    'lede'    => 'Our bold promise: automate, streamline and transform your business operations using AI and ERP — delivering real, measurable results in just 90 days.',
    'buttons' => [
        ['Join the Challenge', 'contact', 'btn--amber btn--lg'],
        ['See the 3-phase plan', 'how-it-works', 'btn--ghost btn--lg'],
    ],
]) ?>

<section class="section section--dark">
  <div class="container">
    <?php part('stats', ['items' => STATS_CHALLENGE]) ?>
  </div>
</section>

<section class="section">
  <div class="container">
    <?php part('section-head', [
        'eyebrow' => 'The programme',
        'title'   => 'What happens across the 13 weeks',
        'lede'    => 'Backed by KPIs, AI tools and ERP modules that evolve with your business.',
    ]) ?>
    <?php part('steps', ['items' => STEPS_CHALLENGE]) ?>
  </div>
</section>

<section class="section section--paper">
  <div class="container">
    <?php part('section-head', ['eyebrow' => 'Scope', 'title' => 'What gets automated']) ?>
    <?php part('modules', ['items' => CHALLENGE_SCOPE]) ?>
  </div>
</section>

<section class="section">
  <div class="container">
    <?php part('section-head', [
        'eyebrow' => 'The guarantee',
        'title'   => 'Risk-free, or your money back',
        'lede'    => "If we don't deliver any automation or process improvements within 90 days, we offer a risk-free money-back guarantee as per our terms.",
    ]) ?>
    <?php part('buttons', [
        'items' => [
            ['Join the Challenge', 'contact', 'btn--lg'],
            ['Read the refund policy', 'refund', 'btn--ghost btn--lg'],
        ],
        'class' => 'btn-row btn-row--center',
    ]) ?>
  </div>
</section>

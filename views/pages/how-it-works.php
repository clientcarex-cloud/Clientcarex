<?php part('page-hero', [
    'crumb' => 'How It Works',
    'title' => 'A proven 3-phase plan, backed by KPIs',
    'lede'  => "We don't hand you a login and wish you luck. Implementation is hands-on, staged over 13 weeks, and measured against numbers you agree up front.",
]) ?>

<section class="section">
  <div class="container">
    <?php part('steps', ['items' => STEPS_IMPLEMENTATION]) ?>
  </div>
</section>

<section class="section section--paper">
  <div class="container">
    <?php part('split', [
        'img'     => 'saas-afi.svg',
        'alt'     => 'Team collaborating on workflows',
        'eyebrow' => 'What you get',
        'title'   => 'Outcomes, not just tools',
        'body'    => 'We blend AI automation, ERP and business consulting into one engagement. Unlike generic platforms, the deliverable is a working process — not a subscription and a documentation link.',
        'list'    => [
            'A written workflow map of your business',
            'Configured ERP modules, not a blank tenant',
            'SOPs and training for the people who use it',
            'KPI reporting from week one',
        ],
        'button'  => ['See the 90-Day Challenge', 'challenge'],
    ]) ?>
  </div>
</section>

<?php part('riskfree', ['buttons' => [['Start Free Trial', 'contact', 'btn--lime btn--lg']]]) ?>

<?php part('cta', [
    'title'   => 'Start with a conversation about your workflows',
    'lede'    => "Thirty minutes is usually enough to tell whether we can help — and we'll say so if we can't.",
    'buttons' => [['Request A Demo', 'contact', 'btn--lime btn--lg']],
]) ?>

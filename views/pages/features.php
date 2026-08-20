<?php part('page-hero', [
    'crumb' => 'Features',
    'title' => 'Everything your business runs on, in one system',
    'lede'  => 'ClientcareX replaces the spreadsheets, WhatsApp threads and half-used tools with one AI-driven ERP — from the first lead to the final invoice.',
]) ?>

<section class="section">
  <div class="container">
    <?php part('section-head', [
        'eyebrow' => 'Core modules',
        'title'   => 'Built module by module, priced by what you actually use',
        'lede'    => 'Every module below ships inside one of the three plans. Nothing is bolted on later.',
    ]) ?>
    <?php part('modules', ['items' => MODULES]) ?>
  </div>
</section>

<section class="section section--paper">
  <div class="container">
    <?php part('split', [
        'img'     => 'saas-ai.svg',
        'alt'     => 'Automation dashboard',
        'eyebrow' => 'Automation',
        'title'   => 'Discover AI-driven marketing tools',
        'body'    => 'Eliminate repetitive customer service tasks with easy-to-configure automation, boosting efficiency across every team.',
        'list'    => ['Eliminate repetition', 'Boost efficiency'],
        'button'  => ['View Pricing', 'pricing'],
    ]) ?>
  </div>
</section>

<section class="section section--dark">
  <div class="container">
    <?php part('section-head', [
        'eyebrow' => 'Integrations',
        'title'   => 'Connects to the tools you already pay for',
        'lede'    => 'Slack, Zapier, HubSpot, Salesforce, Stripe, PayPal, Google Workspace and Google Analytics.',
    ]) ?>
    <?php part('buttons', [
        'items' => [
            ['Browse integrations', '#integrations', 'btn--lime btn--lg'],
            ['Ask about a custom one', 'contact', 'btn--ghost btn--lg'],
        ],
        'class' => 'btn-row btn-row--center',
    ]) ?>
  </div>
</section>

<?php part('cta', [
    'title'   => 'See the modules running on your own data',
    'lede'    => 'A 30-minute walkthrough, mapped to your workflows — not a generic demo reel.',
    'buttons' => [['Request A Demo', 'contact', 'btn--lime btn--lg']],
]) ?>

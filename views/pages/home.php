<section class="hero">
  <div class="container hero__inner">
    <div class="trust-pill">
      <img src="<?= asset('assets/img/users-1.webp') ?>" alt="" width="120" height="32">
      <span class="trust-pill__text">
        <strong class="trust-pill__title">Trusted by 2,000+ users</strong>
        <span class="trust-pill__sub">Satisfied clients globally</span>
      </span>
    </div>

    <h1>Automate Your Business &amp; Scale Fast, AI &amp; Data Driven ERP</h1>

    <p class="hero__lede">
      From leads to customer satisfaction, workflow automation and all your
      business operations in one place — designed to streamline operations and
      enhance productivity.
    </p>

    <?php part('buttons', ['items' => [
        ['Request A Demo', 'contact', 'btn--lg'],
        ['Explore More', 'features', 'btn--ghost btn--lg'],
    ]]) ?>

    <p class="hero__note"><?= icon('shield', 16) ?>30-day money back guarantee</p>
  </div>
</section>

<section class="section section--tight logo-cloud">
  <div class="container">
    <h2 class="logo-cloud__title" data-reveal>
      Trusted by 100+ Teams — They Grew Fast. You Can Too.
    </h2>
    <div class="logo-cloud__grid" data-reveal>
      <?php foreach (CLIENT_LOGOS as [$file, $alt, $w, $h]): ?>
        <img src="<?= asset('assets/img/' . $file) ?>" alt="<?= e($alt) ?>" width="<?= $w ?>" height="<?= $h ?>" loading="lazy">
      <?php endforeach ?>
    </div>
  </div>
</section>

<section class="section section--paper">
  <div class="container">
    <?php
    part('split', [
        'img'     => 'saas-ai.svg',
        'alt'     => 'AI-driven marketing dashboard',
        'eyebrow' => 'Automation',
        'title'   => 'Discover AI-driven marketing tools',
        'body'    => 'Eliminate repetitive customer service tasks with easy-to-configure automation, boosting efficiency across every team.',
        'list'    => ['Eliminate repetition', 'Boost efficiency'],
        'button'  => ['View Pricing', 'pricing'],
    ]);

    part('split', [
        'flip'    => true,
        'img'     => 'saas-afi.svg',
        'alt'     => 'Sharing tools with a team',
        'eyebrow' => 'Collaboration',
        'title'   => 'Share tools quickly and confidently in minutes',
        'body'    => 'This powerful toolset removes the need to leave your CRM to get things done — build a custom proposal with dynamic pricing tables, then customise your own dynamic versions.',
        'list'    => ['Eliminate repetition', 'Boost efficiency'],
        'button'  => ['View Pricing', 'pricing'],
    ]);
    ?>
  </div>
</section>

<section class="section">
  <div class="container">
    <?php part('section-head', [
        'eyebrow' => 'Why Choose Us',
        'title'   => 'Built to fit the way your team already works',
        'lede'    => 'Three capabilities that do most of the heavy lifting on day one.',
    ]) ?>

    <div class="grid grid--3" data-reveal>
      <?php foreach (PILLARS as [$img, $title, $body]): ?>
        <article class="card">
          <div class="card__icon"><img src="<?= asset('assets/img/' . $img) ?>" alt="" width="30" height="30" loading="lazy"></div>
          <h3><?= e($title) ?></h3>
          <p><?= e($body) ?></p>
        </article>
      <?php endforeach ?>
    </div>
  </div>
</section>

<?php part('riskfree', ['buttons' => [
    ['Start Free Trial', 'contact', 'btn--lime btn--lg'],
    ['See how it works', 'how-it-works', 'btn--ghost btn--lg'],
]]) ?>

<section class="section" id="integrations">
  <div class="container">
    <?php part('section-head', [
        'eyebrow' => 'Integrations',
        'title'   => 'Boost your efficiency with integrations',
        'lede'    => 'Connect every part of your business with integrations that simplify your workflow.',
    ]) ?>

    <div class="grid grid--3" data-reveal>
      <?php foreach (INTEGRATIONS as [$name, $tag, $logo, $body]): ?>
        <article class="card integration">
          <div class="integration__head">
            <span class="integration__logo"><img src="<?= asset('assets/img/' . $logo) ?>" alt="" width="26" height="26" loading="lazy"></span>
            <h3><?= e($name) ?><span class="integration__tag"><?= e($tag) ?></span></h3>
          </div>
          <p><?= e($body) ?></p>
          <?php part('link-arrow', ['label' => 'Learn More', 'href' => 'contact']) ?>
        </article>
      <?php endforeach ?>
    </div>
  </div>
</section>

<section class="section section--paper" id="pricing">
  <div class="container">
    <?php part('section-head', [
        'eyebrow' => 'Pricing plans',
        'title'   => 'Find the right package',
        'lede'    => "It's Now or Never ⚡ — unlock enterprise power 💼 and transform your business before time runs out 🕒",
    ]) ?>
    <?php part('plans') ?>
  </div>
</section>

<section class="section" id="reviews">
  <div class="container">
    <?php part('section-head', [
        'eyebrow' => 'Reviews',
        'title'   => 'What Our Customers Say',
        'lede'    => 'Witness firsthand the appreciation for our uncomplicated ticketing system in everyday work.',
    ]) ?>

    <div class="grid grid--4" data-reveal>
      <?php foreach (REVIEWS as [$name, $role, $quote]): ?>
        <article class="review">
          <div class="review__stars" aria-label="5 out of 5 stars"><?= str_repeat(icon('star', 15), 5) ?></div>
          <p><?= e($quote) ?></p>
          <div class="review__person">
            <span class="avatar" aria-hidden="true"><?= e(initials($name)) ?></span>
            <span>
              <span class="review__name"><?= e($name) ?></span>
              <span class="review__role"><?= e($role) ?></span>
            </span>
          </div>
        </article>
      <?php endforeach ?>
    </div>
  </div>
</section>

<section class="section section--paper" id="faq">
  <div class="container">
    <?php part('section-head', [
        'eyebrow' => 'FAQs',
        'title'   => 'Frequently asked questions',
        'lede'    => "Got questions? Whether you're scaling, streamlining or starting your 90-Day Business Transformation, here's where the doubts disappear.",
    ]) ?>
    <?php part('faq') ?>
    <?php part('buttons', [
        'items' => [['View All Questions', 'contact', 'btn--ghost']],
        'class' => 'btn-row btn-row--center',
        'style' => 'margin-top:2rem',
    ]) ?>
  </div>
</section>

<section class="section">
  <div class="container">
    <?php part('contact-strip', ['style' => 'margin-bottom:clamp(2.5rem,5vw,4rem)']) ?>

    <div class="cta" data-reveal>
      <h2>From professional business to enterprise — let's scale 10x growth</h2>
      <p>
        Tell us where the manual work is. We'll show you exactly what
        <?= SITE_NAME ?> automates, and what it saves you, in the first 90 days.
      </p>
      <?php part('buttons', ['items' => [
          ['Request A Demo', 'contact', 'btn--lime btn--lg'],
          ['See Pricing', 'pricing', 'btn--ghost btn--lg'],
      ], 'class' => 'btn-row btn-row--center']) ?>
    </div>
  </div>
</section>

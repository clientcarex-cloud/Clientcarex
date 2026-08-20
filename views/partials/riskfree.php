<?php /** @var array $buttons */ ?>
<section class="section section--dark">
  <div class="container riskfree">
    <div data-reveal>
      <span class="eyebrow">Risk-free</span>
      <h2>Try It Now Risk-Free</h2>
      <p>
        Discover how <?= SITE_NAME ?> can enhance your support team's efficiency,
        improve customer satisfaction and take your business to the next level.
      </p>
      <?php part('buttons', ['items' => $buttons, 'style' => 'margin-top:1.75rem']) ?>
    </div>

    <figure class="quote-card" data-reveal>
      <blockquote>&ldquo;<?= e(QUOTE['text']) ?>&rdquo;</blockquote>
      <figcaption>
        <span class="avatar" aria-hidden="true"><?= e(initials(QUOTE['name'])) ?></span>
        <span>
          <cite><?= e(QUOTE['name']) ?></cite>
          <span><?= e(QUOTE['role']) ?></span>
        </span>
      </figcaption>
    </figure>
  </div>
</section>

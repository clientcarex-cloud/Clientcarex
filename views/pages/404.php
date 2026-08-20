<?php
part('page-hero', [
    'eyebrow' => 'Error 404',
    'title'   => 'That page has moved on',
    'lede'    => "The link you followed doesn't exist any more. Here's the way back to the things people usually come here for.",
    'buttons' => [
        ['Back to home', '', 'btn--lg'],
        ['Contact us', 'contact', 'btn--ghost btn--lg'],
    ],
]);

$routes = [
    ['Features', 'Every module in the platform, from leads and CRM through to AI reporting.', 'Browse features', 'features'],
    ['Pricing', 'Professional, Business and Enterprise plans with transparent per-user pricing.', 'See pricing', 'pricing'],
    ['90-Day Challenge', 'Our transformation programme, and what happens across the 13 weeks.', 'See the programme', 'challenge'],
];
?>
<section class="section">
  <div class="container">
    <div class="grid grid--3">
      <?php foreach ($routes as [$title, $body, $label, $href]): ?>
        <article class="card">
          <h3><?= e($title) ?></h3>
          <p><?= e($body) ?></p>
          <p style="margin-top:1rem"><?php part('link-arrow', ['label' => $label, 'href' => $href]) ?></p>
        </article>
      <?php endforeach ?>
    </div>
  </div>
</section>

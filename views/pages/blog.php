<?php part('page-hero', [
    'crumb' => 'Blog',
    'title' => 'Blog & News',
    'lede'  => 'Automation playbooks, ERP guides and product news from the ClientcareX team.',
]) ?>

<section class="section">
  <div class="container">
    <div class="prose" style="max-width:52rem">
      <?php part('notice', ['html' => '<strong>No posts have been restored yet.</strong> The Wayback Machine
          captured only the ClientcareX homepage, so no blog articles were
          recoverable. Add posts by copying a <code>.post</code> card into this
          page — see <code>README.md</code> for the snippet.']) ?>

      <h2>Want the next one in your inbox?</h2>
      <p>
        We write about the unglamorous side of automation: what actually breaks,
        what actually saves time, and what we'd do differently. Tell us what
        you're trying to automate and we'll point you at the right piece.
      </p>
      <?php part('buttons', [
          'items' => [
              ['Get in touch', 'contact', ''],
              ['See the 90-Day Challenge', 'challenge', 'btn--ghost'],
          ],
          'style' => 'margin-top:1.5rem',
      ]) ?>
    </div>
  </div>
</section>

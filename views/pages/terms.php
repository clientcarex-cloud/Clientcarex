<?php part('page-hero', [
    'crumb' => 'Terms & Conditions',
    'title' => 'Terms & Conditions',
    'lede'  => 'The terms governing your use of ClientcareX software and services.',
]) ?>

<section class="section">
  <div class="container prose">

    <h2>1. Agreement</h2>
    <p>
      These terms form an agreement between you (or the organisation you
      represent) and <?= COMPANY ?>. By creating an account or using
      the service you accept them.
    </p>

    <h2>2. The service</h2>
    <p>
      ClientcareX provides a subscription ERP and automation platform, together
      with the implementation and consulting services described in your order.
      Feature availability depends on the plan you purchase.
    </p>

    <h2>3. Subscriptions and billing</h2>
    <ul>
      <li>Plans are priced per user, per month, and billed yearly in advance unless agreed otherwise in writing.</li>
      <li>Minimum seat counts apply: five users on Professional, ten on Business and Enterprise.</li>
      <li>Fees are exclusive of applicable taxes.</li>
      <li>Subscriptions renew for a further term unless cancelled before the renewal date.</li>
    </ul>

    <h2>4. Your responsibilities</h2>
    <ul>
      <li>Keep account credentials confidential and accurate.</li>
      <li>Ensure you have the right to upload and process the data you put into the platform.</li>
      <li>Use the outreach features (SMS, WhatsApp, email) in line with applicable consent and anti-spam law.</li>
      <li>Do not attempt to breach, overload or reverse engineer the service.</li>
    </ul>

    <h2>5. Your data</h2>
    <p>
      You own the content you put into ClientcareX. We process it to run the
      service on your behalf, as described in our
      <a href="<?= url('privacy') ?>">Privacy Policy</a>. On termination you may export
      your data for the period stated in your order.
    </p>

    <h2>6. Intellectual property</h2>
    <p>
      The platform, its software, branding and documentation remain our property.
      Your subscription grants a non-exclusive, non-transferable right to use it
      for your internal business purposes during the term.
    </p>

    <h2>7. The 90-day guarantee</h2>
    <p>
      Where you have purchased the 90-Day Business Transformation Challenge, our
      commitments and the money-back conditions are set out in the
      <a href="<?= url('refund') ?>">Refund Policy</a>, which forms part of these terms.
    </p>

    <h2>8. Availability</h2>
    <p>
      We aim for continuous availability but do not warrant uninterrupted
      service. Planned maintenance will be notified in advance where practical.
    </p>

    <h2>9. Liability</h2>
    <p>
      To the extent permitted by law, neither party is liable for indirect or
      consequential loss, and our aggregate liability is limited to the fees paid
      by you in the twelve months preceding the claim.
    </p>

    <h2>10. Termination</h2>
    <p>
      Either party may terminate for material breach that remains uncured 30 days
      after written notice. We may suspend access for non-payment or for use that
      threatens the security of the platform.
    </p>

    <h2>11. Governing law</h2>
    <p>
      These terms are governed by the laws of India, and the courts having
      jurisdiction over our registered office will hear any dispute.
    </p>

    <h2>12. Contact</h2>
    <p>
      Questions about these terms:
      <a href="mailto:<?= EMAIL ?>"><?= EMAIL ?></a> or
      <?= PHONE ?>.
    </p>

    <p class="muted" style="margin-top:2rem">Last updated: <?= date('Y') ?></p>
  </div>
</section>

<?php part('page-hero', [
    'crumb' => 'Refund Policy',
    'title' => 'Refund Policy',
    'lede'  => 'Including the 30-day money back guarantee and the 90-day challenge terms.',
]) ?>

<section class="section">
  <div class="container prose">

    <h2>1. 30-day money back guarantee</h2>
    <p>
      If you are not satisfied with ClientcareX within 30 days of your first paid
      subscription starting, contact us and we will refund the subscription fee
      for that initial term, less any usage-based charges already incurred on
      your behalf (for example SMS, WhatsApp or telephony credits).
    </p>

    <h2>2. The 90-day challenge guarantee</h2>
    <p>
      For customers on the 90-Day Business Transformation Challenge: if we do not
      deliver any automation or process improvement within the 90-day window
      against the KPIs agreed at kickoff, you may claim a refund of the programme
      fee as set out in your order.
    </p>
    <p>To qualify, the following need to have happened:</p>
    <ul>
      <li>KPIs and scope were agreed in writing during phase one.</li>
      <li>Your team attended the scheduled discovery and review sessions.</li>
      <li>Requested access, data and approvals were provided without material delay.</li>
      <li>The claim is raised in writing within 14 days of the 90-day window ending.</li>
    </ul>

    <h2>3. What is not refundable</h2>
    <ul>
      <li>Third-party pass-through costs already spent — SMS, WhatsApp, telephony, payment gateway and domain fees.</li>
      <li>Custom development delivered and accepted outside the standard plan scope.</li>
      <li>Renewal terms after the first paid term, other than as required by law.</li>
    </ul>

    <h2>4. How to request a refund</h2>
    <p>
      Email <a href="mailto:<?= EMAIL ?>"><?= EMAIL ?></a> from
      the account owner's address with your organisation name, plan and the
      reason for the request. We will acknowledge within two working days.
    </p>

    <h2>5. Processing</h2>
    <p>
      Approved refunds are returned to the original payment method. Depending on
      your bank or card issuer, funds typically appear within 7–14 working days.
    </p>

    <h2>6. Cancellations</h2>
    <p>
      You can cancel a subscription at any time; cancellation stops the next
      renewal and does not, by itself, trigger a refund of the current term
      except under sections 1 and 2 above.
    </p>

    <h2>7. Contact</h2>
    <p>
      Questions about this policy:
      <a href="mailto:<?= EMAIL ?>"><?= EMAIL ?></a> or
      <?= PHONE ?>.
    </p>

    <p class="muted" style="margin-top:2rem">Last updated: <?= date('Y') ?></p>
  </div>
</section>

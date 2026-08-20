<?php
/**
 * Page content as data. Anything that was repeated markup — plan tables,
 * FAQs, integration cards, reviews, modules, steps — is a row in an array
 * here and is rendered once by a partial.
 */
declare(strict_types=1);

/** Pricing tables, shown on the homepage and the pricing page. */
const PLANS = [
    [
        'name'    => 'Professional',
        'badge'   => 'Save 25%',
        'price'   => '₹499',
        'summary' => 'Essential tools to manage your business smoothly.',
        'terms'   => '5 user minimum • Yearly billing',
        'features' => [
            'Leads Management',
            'Clients Management',
            'Sales Management',
            'Auto Payment Reminders',
            'Auto Greetings & Wishes to Customers, Staff & Leads',
            'Docs & SOPs (25+)',
            'Tasks Management',
            'HR Records',
            'HR Payroll',
            'HR Attendance & Leave',
            'Expense Management',
            'Contacts Management',
            'Knowledge Base',
            'Staff Announcements',
            'Activity Logs',
        ],
    ],
    [
        'name'     => 'Business',
        'badge'    => 'Save 25% • Most popular',
        'price'    => '₹799',
        'summary'  => 'Smarter workflow with semi-automation and AI power.',
        'terms'    => '10 user minimum • Yearly billing',
        'featured' => true,
        'inherits' => 'Professional',
        'features' => [
            'Leads Integrations (30+)',
            'Bulk Promotional SMS',
            'WhatsApp Official',
            'Leads Auto Follow-up',
            'Leads Rollercoaster',
            'Survey Forms & QnAs',
            'Testimonial Forms',
            'Dashboard Banner',
            'Team Chat',
            'Ticket Support System',
            'Appointments Booking',
            'Staff Reminders',
        ],
    ],
    [
        'name'     => 'Enterprise',
        'badge'    => 'Save 80%',
        'price'    => '₹1,400',
        'summary'  => 'Full automation for 10x growth and zero manual effort.',
        'terms'    => '10 user minimum • Yearly billing',
        'inherits' => 'Business',
        'features' => [
            'Bulk SMS, WhatsApp & Email',
            'Use your own Domain',
            'Payment Gateway Integration',
            'Auto Smart Accounting',
            'Google Workspace Integrated',
            'GMap Leads Finder',
            'Landing Pages for Digital Marketing',
            'Link Shortener & Tracking',
            'Google Analytics Integrated',
            'Approvals Management',
            'Project Management',
            'Affiliate Management',
            'Referral Management',
            'AI Ticketing Support',
            'Telecalling Integrated',
            'AI Call Agents Integrated',
            'AI Prompt Reports & Charts',
        ],
    ],
];

/** Shown on the homepage, pricing page and contact page. */
const FAQS = [
    [
        'q' => 'What is the 90-Day Business Automation Challenge?',
        'a' => '<p>The 90-Day Challenge is our bold promise to automate, streamline and transform your business operations using AI and ERP solutions — delivering real, measurable results in just 90 days.</p>',
    ],
    [
        'q' => 'What kind of businesses do you work with?',
        'a' => '<p>We work with SMEs, startups and growing enterprises across industries like retail, healthcare, services, manufacturing and education. If you have manual processes, we can automate them.</p>',
    ],
    [
        'q' => 'What areas of my business will be automated?',
        'a' => '<p>Our AI-driven ERP and automation solutions can streamline:</p>
          <ul>
            <li>CRM &amp; sales pipelines</li>
            <li>Inventory &amp; billing</li>
            <li>HR &amp; payroll</li>
            <li>Customer support (AI chat &amp; call agents)</li>
            <li>Workflow approvals</li>
            <li>Reports &amp; dashboards</li>
            <li>Marketing &amp; outreach automation</li>
          </ul>',
    ],
    [
        'q' => 'Is this a ready-made ERP or custom-built?',
        'a' => '<p>We offer a core AI-powered ERP that is tailored specifically for your business workflows, integrations and objectives — fully customised to suit your operations.</p>',
    ],
    [
        'q' => 'How do you guarantee transformation in 90 days?',
        'a' => '<p>We follow a proven 3-phase plan:</p>
          <ol>
            <li>Understanding &amp; workflows (week 1–2)</li>
            <li>Automation &amp; customisation (week 3–8)</li>
            <li>Implementation &amp; scaling (week 9–13)</li>
          </ol>
          <p style="margin-top:.75rem">Backed by KPIs, AI tools and ERP modules that evolve with your business.</p>',
    ],
    [
        'q' => 'What makes your solution different from Zoho, Odoo or others?',
        'a' => '<p>We blend powerful AI automation, ERP and business consulting into one streamlined solution. Unlike generic platforms, we focus on outcomes, not just tools — with hands-on implementation, not just subscriptions.</p>',
    ],
    [
        'q' => "Is there a money-back guarantee if it doesn't work?",
        'a' => "<p>Yes. If we don't deliver any automation or process improvements within 90 days, we offer a risk-free money-back guarantee as per our terms.</p>",
    ],
];

/** Homepage: client logo row. */
const CLIENT_LOGOS = [
    ['iiet-logo-1-removebg-preview.png', 'IIET', 813, 307],
    ['Dr._Care_Logo-removebg-preview.png', 'Dr. Care', 200, 87],
    ['Toot-Logo-1.png', 'Toot', 200, 87],
    ['Autism-Logo-e1753695588960.png', 'Autism', 50, 50],
    ['new-Harvest-helpdesklatest.png', 'Harvest', 552, 195],
    ['new-Quantum-helpdesklatest.png', 'Quantum', 552, 195],
    ['new-Urban-helpdesklatest.png', 'Urban', 552, 195],
    ['new-mrbeat.png', 'Mr Beat', 552, 195],
];

/** Homepage: the three pillars. */
const PILLARS = [
    ['Group-114.svg', 'Multi-Channel Support', 'Manage customer inquiries from various channels — including email, social media, live chat and phone — all in one place.'],
    ['Group-115.svg', 'Customizable Workflows', 'Customise workflows to match your business processes and preferences, from automated responses through to ticket routing.'],
    ['Group-113.svg', 'Seamless Integrations', 'Integrates with popular business tools such as CRM systems, project management software and communication platforms.'],
];

/** Homepage: integration cards. */
const INTEGRATIONS = [
    ['Slack', 'Productivity', 'tool-slack.svg', 'Notify your teammates of the latest activities with instant Slack messages.'],
    ['Zapier', 'Productivity', 'tool-zapier.svg', 'Chain ClientcareX into 5,000+ apps and trigger actions without writing code.'],
    ['HubSpot', 'CRM', 'tool-hubspot.svg', 'Keep contacts, deals and lifecycle stages in sync between HubSpot and ClientcareX.'],
    ['PayPal', 'Payment', 'tool-paypal.svg', 'Collect payments against invoices and reconcile them automatically.'],
    ['Stripe', 'Payment', 'tool-stripe.svg', 'Take card payments and subscriptions with settlement data flowing straight into accounting.'],
    ['Salesforce', 'CRM', 'tool-salesforce.svg', 'Two-way sync for accounts, opportunities and activity history.'],
];

/** Homepage: reviews. Initials for the avatar are derived from the name. */
const REVIEWS = [
    ['Ami Smith', 'Shop Keeper', 'Experience powerful project management tools that streamline your workflow, all while staying within your budget.'],
    ['Khyati', 'Web Designer', 'Experience powerful project management tools that streamline your workflow, all while staying within your budget.'],
    ['Chiranjit', 'Business Owner', 'Access advanced features to boost your project management efficiency, without breaking the bank — ClientcareX is the best.'],
    ['Achara', 'Youtuber', 'Enjoy a range of features designed to enhance your project management experience, all at a price that fits your budget.'],
];

/** The pull quote used on the risk-free band. */
const QUOTE = [
    'text' => 'The platform is user-friendly and has improved our response times significantly. Our team and customers are happier than ever.',
    'name' => 'James Wilson',
    'role' => 'IT Support Specialist',
];

/** Features page: module grid. */
const MODULES = [
    ['users', 'Leads & CRM', [
        'Leads management with stages and owners',
        'Leads integrations (30+ sources)',
        'Leads auto follow-up sequences',
        'Leads Rollercoaster re-engagement',
        'GMap Leads Finder',
        'Contacts management',
    ]],
    ['chart', 'Sales & Clients', [
        'Clients management',
        'Sales management and pipelines',
        'Proposals with dynamic pricing tables',
        'Auto payment reminders',
        'Payment gateway integration',
        'Auto smart accounting',
    ]],
    ['briefcase', 'HR & People', [
        'HR records',
        'HR payroll',
        'Attendance & leave',
        'Staff announcements and reminders',
        'Approvals management',
        'Activity logs',
    ]],
    ['chat', 'Support & Service', [
        'Ticket support system',
        'AI ticketing support',
        'Multi-channel inbox: email, chat, social, phone',
        'Knowledge base',
        'Appointments booking',
        'Team chat',
    ]],
    ['bolt', 'Marketing & Outreach', [
        'Bulk SMS, WhatsApp & email',
        'WhatsApp Official API',
        'Landing pages for digital marketing',
        'Link shortener & click tracking',
        'Survey forms, QnAs and testimonial forms',
        'Referral and affiliate management',
    ]],
    ['cog', 'AI & Automation', [
        'AI call agents integrated',
        'Telecalling integrated',
        'AI prompt reports & charts',
        'Customisable workflow automation',
        'Auto greetings and wishes',
        'Docs & SOPs library (25+)',
    ]],
];

/** Challenge page: what gets automated. */
const CHALLENGE_SCOPE = [
    [null, 'Revenue', ['CRM & sales pipelines', 'Lead capture and auto follow-up', 'Proposals and payment reminders']],
    [null, 'Operations', ['Inventory & billing', 'Workflow approvals', 'Reports & dashboards']],
    [null, 'People & service', ['HR & payroll', 'Customer support with AI chat & call agents', 'Marketing & outreach automation']],
];

/** The 3-phase plan, told at implementation depth on how-it-works. */
const STEPS_IMPLEMENTATION = [
    ['Week 1–2', 'Understanding & workflows', 'We map how your business actually runs today — every handoff, every spreadsheet, every message thread that holds a process together. You get a written workflow map and an agreed set of KPIs before a single module is configured.'],
    ['Week 3–8', 'Automation & customisation', 'The core AI-powered ERP is tailored to those workflows: lead routing, follow-up sequences, approvals, payroll rules, ticket queues, integrations and reporting. You review each module as it lands rather than at the end.'],
    ['Week 9–13', 'Implementation & scaling', "Your team moves onto the system with training, SOPs and a support line. We watch the KPIs with you, tune the automations that aren't paying off, and plan the next set of processes to absorb."],
];

/** The same plan, told as the challenge programme. */
const STEPS_CHALLENGE = [
    ['Week 1–2', 'Understanding & workflows', 'Process discovery across sales, operations, HR and support. We agree the KPIs the challenge will be judged on before anything is built.'],
    ['Week 3–8', 'Automation & customisation', 'Your ERP is configured module by module: CRM and sales pipelines, inventory and billing, HR and payroll, AI chat and call agents, workflow approvals, dashboards and outreach automation.'],
    ['Week 9–13', 'Implementation & scaling', 'Rollout, training, SOPs and tuning. By the end of the window you have live automations and a KPI report showing what moved.'],
];

const STATS_PRICING = [
    ['30', 'Day money back guarantee'],
    ['90', 'Day transformation programme'],
    ['2,000+', 'Users on the platform'],
    ['100+', 'Teams already scaling'],
];

const STATS_CHALLENGE = [
    ['90', 'Days from kickoff to measured results'],
    ['3', 'Phases, each with its own checkpoint'],
    ['100+', 'Teams already through the programme'],
    ['0', "Risk — money back if we don't deliver"],
];

/** Team-size options on the demo form. */
const TEAM_SIZES = ['1–5', '6–10', '11–25', '26–100', '100+'];

/** Initials for an avatar circle: "Ami Smith" -> "AS", "Khyati" -> "K". */
function initials(string $name): string
{
    $out = '';
    foreach (preg_split('/\s+/', trim($name)) as $word) {
        $out .= mb_strtoupper(mb_substr($word, 0, 1));
    }

    return $out;
}

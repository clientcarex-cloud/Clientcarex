<?php
/**
 * Site-wide configuration and the page registry.
 * Every constant here has exactly one definition — nothing is repeated in a
 * template. Change a value once and it changes everywhere it is rendered.
 */
declare(strict_types=1);

const SITE_NAME    = 'ClientcareX';
const SITE_URL     = 'https://clientcarex.com';
const COMPANY      = 'Clientcarex Private Limited';
const PHONE        = '+91 93908 93024';
const PHONE_HREF   = '+919390893024';
const EMAIL        = 'care@clientcarex.com';
const APP_LOGIN    = 'https://clientcarex.com/ccx/authentication/login';
const APP_REGISTER = 'https://clientcarex.com/ccx/authentication/register';

/** Where demo requests are delivered. */
const MAIL_TO = EMAIL;

/** Full-page cache. Turn off while editing templates if you prefer. */
const CACHE_ENABLED = true;

/**
 * Page registry: route => metadata.
 *   view        file under views/pages/
 *   nav         which header link is highlighted ('' = none)
 *   cache       false for pages that vary per request
 *   noindex     keep out of robots and the sitemap
 */
const PAGES = [
    '' => [
        'view'  => 'home',
        'nav'   => 'home',
        'title' => 'ClientcareX — AI Driven ERP Software & Automation',
        'description' => 'Automate your business and scale fast with ClientcareX — an AI and data driven ERP that unifies leads, sales, HR, support and billing in one place.',
        'priority' => '1.0',
    ],
    'features' => [
        'view'  => 'features',
        'nav'   => 'features',
        'title' => 'Features — ClientcareX AI Driven ERP',
        'description' => 'Every module in ClientcareX: leads and CRM, sales, HR and payroll, support ticketing, automation, accounting and AI reporting.',
    ],
    'pricing' => [
        'view'  => 'pricing',
        'nav'   => 'pricing',
        'title' => 'Pricing — ClientcareX Plans from ₹499/user/month',
        'description' => 'Professional, Business and Enterprise plans for ClientcareX. Transparent per-user pricing with a 30-day money back guarantee.',
    ],
    'how-it-works' => [
        'view'  => 'how-it-works',
        'nav'   => 'how',
        'title' => 'How It Works — ClientcareX Implementation in 3 Phases',
        'description' => 'Understanding and workflows, automation and customisation, then implementation and scaling — how ClientcareX transforms operations in 90 days.',
    ],
    'challenge' => [
        'view'  => 'challenge',
        'nav'   => 'challenge',
        'title' => '90-Day Business Transformation Challenge — ClientcareX',
        'description' => 'Our bold promise: automate, streamline and transform your business operations with AI and ERP in 90 days — or your money back.',
    ],
    'blog' => [
        'view'  => 'blog',
        'nav'   => 'blog',
        'title' => 'Blog & News — ClientcareX',
        'description' => 'Automation playbooks, ERP guides and product news from the ClientcareX team.',
    ],
    'contact' => [
        'view'  => 'contact',
        'nav'   => '',
        'cache' => false,
        'title' => 'Contact & Request a Demo — ClientcareX',
        'description' => 'Talk to the ClientcareX team. Book a demo, ask about pricing or get implementation support.',
    ],
    'privacy' => [
        'view'  => 'privacy',
        'nav'   => '',
        'title' => 'Privacy Policy — ClientcareX',
        'description' => 'How Clientcarex Private Limited collects, uses and protects your data.',
    ],
    'terms' => [
        'view'  => 'terms',
        'nav'   => '',
        'title' => 'Terms & Conditions — ClientcareX',
        'description' => 'The terms governing your use of ClientcareX software and services.',
    ],
    'refund' => [
        'view'  => 'refund',
        'nav'   => '',
        'title' => 'Refund Policy — ClientcareX',
        'description' => 'ClientcareX refund terms, including the 30-day money back guarantee.',
    ],
    '404' => [
        'view'    => '404',
        'nav'     => '',
        'title'   => 'Page not found — ClientcareX',
        'description' => "The page you were looking for doesn't exist.",
        'noindex' => true,
    ],
];

/** Header navigation: route => [label, hot]. */
const NAV = [
    'home'      => ['Home', '', false],
    'features'  => ['Features', 'features', false],
    'pricing'   => ['Pricing', 'pricing', false],
    'how'       => ['How It Works', 'how-it-works', false],
    'challenge' => ['🔥 90 Day Challenge', 'challenge', true],
    'blog'      => ['Blog', 'blog', false],
];

/** Footer link columns: heading => [label => href]. */
const FOOTER_COLUMNS = [
    'Company' => [
        ['About Us', 'contact'],
        ['Our Team', 'contact'],
        ['Our Program', 'challenge'],
        ['Work With Us', 'contact'],
    ],
    'Help & Support' => [
        ['Help center', 'contact'],
        ['Expert team', 'contact'],
        ['Contact Us', 'contact'],
        ['Report Abuse', 'mailto:' . EMAIL],
    ],
    'Information' => [
        ['Testimonials', '#reviews'],
        ['Pricing Plans', 'pricing'],
        ['Referral Program', 'contact'],
        ['Payment Gateway', 'features'],
    ],
    'Useful Links' => [
        ['Blog & News', 'blog'],
        ['How It Works', 'how-it-works'],
        ['Our Features', 'features'],
        ['Compare Plans', 'pricing'],
    ],
];

/** Footer legal row and social icons. */
const FOOTER_LEGAL = [
    ['Privacy Policy', 'privacy'],
    ['Terms & Conditions', 'terms'],
    ['Refund policy', 'refund'],
];

const SOCIAL = [
    ['linkedin', 'LinkedIn', '#'],
    ['x', 'X', '#'],
    ['facebook', 'Facebook', '#'],
    ['instagram', 'Instagram', '#'],
];

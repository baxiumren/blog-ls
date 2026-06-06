<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        $about = <<<'MD'
Welcome to **{{SITE}}** — your trusted destination for live football scores, fixtures, results, league tables, team and player statistics, plus the latest football news, predictions and match highlights from around the world.

We cover the world's biggest competitions, including the Premier League, La Liga, Serie A, Bundesliga, Ligue 1, the UEFA Champions League and Europa League, the FIFA World Cup, and dozens of domestic leagues and cups across every continent.

### What we offer
- **Live scores & results** updated in real time
- **Fixtures, standings & statistics** for leagues and teams
- **Football news, previews and analysis** from our editorial team
- **Match predictions & tips** for upcoming games
- **Video highlights** and key match moments

### Our mission
Our goal is simple: to deliver fast, accurate and easy-to-read football information to fans everywhere, on any device. We are passionate about the game and committed to a clean, reliable experience.

### Data sources
Match data, statistics and images are sourced from reputable third-party providers and the respective clubs and competitions. While we strive for accuracy, scores and statistics are provided for informational purposes only.

Have feedback, a correction, or a partnership idea? Visit our [Contact](/page/contact) page — we'd love to hear from you.
MD;

        $contact = <<<'MD'
We'd love to hear from you. Whether you have a question, a correction, feedback, an advertising enquiry or a partnership proposal, please get in touch.

### Get in touch
- **General:** contact@yourdomain.com
- **Advertising & business:** ads@yourdomain.com

### Social media
- Telegram: @yourusername
- Twitter / X: @yourusername

We aim to respond to all enquiries within 1–2 business days. Please note we do not provide betting advice or guarantee the accuracy of predictions.

For privacy-related requests, please see our [Privacy Policy](/page/privacy-policy).
MD;

        $privacy = <<<'MD'
Your privacy is important to us. This Privacy Policy explains what information **{{SITE}}** ("we", "us", "our", or the "Website") collects when you use the Website, how we use it, and the choices available to you. By using this Website, you agree to the practices described below.

### Information we collect
You do not need to create an account or provide personal information to browse the Website. We may automatically collect non-identifying technical information such as your browser type, device, operating system, referring page, and the pages you visit. This data is used in aggregate to understand traffic and improve our service.

### Cookies
We use cookies and similar technologies to remember preferences, measure traffic, and deliver and personalise advertising. A cookie is a small text file stored on your device. You can control or delete cookies through your browser settings at any time, though disabling them may affect some features.

### Advertising & Google AdSense
We use third-party advertising companies, including **Google AdSense**, to serve ads when you visit the Website. These companies may use cookies and web beacons to collect information about your visits to this and other websites to provide relevant advertisements.

- Google, as a third-party vendor, uses cookies to serve ads on this Website.
- Google's use of advertising cookies (including the **DoubleClick DART cookie**) enables it and its partners to serve ads based on your visit to this and/or other sites.
- You may opt out of personalised advertising via [Google Ads Settings](https://www.google.com/settings/ads), or opt out of third-party vendor cookies at [aboutads.info/choices](https://www.aboutads.info/choices).

### Analytics
We may use analytics services such as **Google Analytics** to understand how visitors interact with the Website. These services collect information sent by your browser, including pages visited and aggregated usage data, processed under the providers' own privacy policies.

### Third-party links
The Website may link to other websites we do not operate. We are not responsible for the privacy practices or content of those third-party sites and encourage you to review their policies.

### Children's privacy
The Website is not directed to children under 13, and we do not knowingly collect personal information from children. If you believe a child has provided us information, please contact us so we can remove it.

### Your rights
Depending on your location, you may have the right to access, correct or delete the personal data we hold, or to object to certain processing. To make a request, contact us via our [Contact](/page/contact) page.

### Changes to this policy
We may update this Privacy Policy from time to time. Changes will be posted on this page with an updated revision date. Continued use of the Website after changes constitutes acceptance.

### Contact
Questions about this Privacy Policy? Reach us via our [Contact](/page/contact) page.
MD;

        $terms = <<<'MD'
These Terms of Service ("Terms") govern your access to and use of **{{SITE}}** (the "Website"). By accessing or using the Website, you agree to be bound by these Terms. If you do not agree, please do not use the Website.

### Use of the Website
You may use the Website for personal, non-commercial purposes. You agree not to misuse it, including by attempting unauthorised access, disrupting its operation, scraping content at scale, or using it for any unlawful purpose.

### Content & accuracy
All scores, fixtures, statistics, news, predictions and other content are provided for **general informational purposes only**. While we make reasonable efforts to keep information accurate and current, we do not warrant that it is complete, reliable or error-free. Live data may be delayed or contain inaccuracies.

### No betting advice
Predictions and tips are opinions published for entertainment purposes only and do not constitute betting, financial or professional advice. We are not responsible for any losses resulting from reliance on this content. Always gamble responsibly and within the law of your jurisdiction.

### Intellectual property
Club names, logos, competition names and related data are the property of their respective owners and are used for identification purposes only. All other content, including text and design, is owned by us or our licensors and may not be reproduced without permission.

### Third-party links & ads
The Website may display advertisements and links to third-party websites and services. We are not responsible for the content, products or practices of any third parties.

### Disclaimer of warranties
The Website is provided on an "as is" and "as available" basis, without warranties of any kind, express or implied.

### Limitation of liability
To the fullest extent permitted by law, we shall not be liable for any indirect, incidental or consequential damages arising from your use of, or inability to use, the Website.

### Changes to these Terms
We may revise these Terms at any time. The updated version will be posted on this page with a new revision date, and continued use constitutes acceptance.

### Contact
Questions about these Terms? Contact us via our [Contact](/page/contact) page.
MD;

        $pages = [
            ['slug' => 'about',          'title' => 'About Us',         'meta' => 'Learn about {{SITE}} — live football scores, fixtures, results, stats, news, predictions and highlights from top leagues and competitions.', 'body' => $about],
            ['slug' => 'contact',        'title' => 'Contact Us',       'meta' => 'Get in touch with {{SITE}} for questions, feedback, corrections, advertising and partnership enquiries.', 'body' => $contact],
            ['slug' => 'privacy-policy', 'title' => 'Privacy Policy',   'meta' => 'How {{SITE}} collects, uses and protects your data, including cookies, Google AdSense advertising and analytics.', 'body' => $privacy],
            ['slug' => 'terms',          'title' => 'Terms of Service', 'meta' => 'The terms and conditions for using {{SITE}}, including content accuracy, intellectual property and limitation of liability.', 'body' => $terms],
        ];

        foreach ($pages as $p) {
            Page::updateOrCreate(['slug' => $p['slug']], [
                'title'            => $p['title'],
                'meta_description' => $p['meta'],
                'body'             => $p['body'],
            ]);
        }
    }
}
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    private function groups(): array
    {
        $tz = ['UTC', 'Asia/Jakarta', 'Asia/Makassar', 'Asia/Jayapura', 'Asia/Singapore', 'Asia/Kuala_Lumpur', 'Asia/Bangkok', 'Asia/Manila', 'Asia/Tokyo', 'Asia/Shanghai', 'Asia/Dubai', 'Asia/Kolkata', 'Europe/London', 'Europe/Paris', 'Europe/Madrid', 'America/New_York', 'America/Los_Angeles', 'America/Sao_Paulo', 'Australia/Sydney'];

        return [
            'theme' => [
                'section' => 'Interface',
                'title' => 'Theme',
                'icon' => 'fa-solid fa-palette',
                'desc'  => 'Primary accent color of the site.',
                'fields' => [
                    'accent_color' => ['label' => 'Accent color', 'type' => 'color', 'hint' => 'Main color for buttons, links, badges and highlights across the whole site. Default is blue (#2563eb).'],
                    'font_family' => ['label' => 'Font family', 'type' => 'select', 'options' => [
                        ''            => 'Manrope (default)',
                        'Inter'       => 'Inter',
                        'Poppins'     => 'Poppins',
                        'Montserrat'  => 'Montserrat',
                        'Roboto'      => 'Roboto',
                        'Open Sans'   => 'Open Sans',
                        'Lato'        => 'Lato',
                        'Nunito Sans' => 'Nunito Sans',
                        'Rubik'       => 'Rubik',
                        'Work Sans'   => 'Work Sans',
                    ], 'hint' => 'Main font for the public site, loaded from Google Fonts. Leave as Manrope (default) to keep the current font.'],
                ],
            ],
            'custom_code' => [
                'section' => 'Interface',
                'title' => 'Custom JS / CSS',
                'icon' => 'fa-solid fa-code',
                'desc'  => 'Inject your own CSS, head code & scripts.',
                'fields' => [
                    'custom_css' => ['label' => 'Custom CSS', 'type' => 'textarea', 'hint' => 'Your own CSS rules to fine-tune the look. Added inside a <style> tag in the page head — no <style> tags needed.'],
                    'head_code'  => ['label' => 'Header code (raw HTML)', 'type' => 'textarea', 'hint' => 'Raw HTML injected into the <head> of every page. Use for meta tags, site verification, or third-party widget snippets.'],
                    'custom_js'  => ['label' => 'Custom JavaScript', 'type' => 'textarea', 'hint' => 'Your own JavaScript, loaded just before </body>. No <script> tags needed.'],
                ],
            ],
            'cookie' => [
                'section' => 'Interface',
                'title' => 'Cookie consent',
                'icon' => 'fa-solid fa-cookie-bite',
                'desc'  => 'The cookie notice banner.',
                'fields' => [
                    'cookie_enabled'   => ['label' => 'Show cookie banner', 'type' => 'toggle', 'hint' => 'Display a cookie consent notice to visitors. Turn off to hide it completely.'],
                    'cookie_message'   => ['label' => 'Banner message', 'type' => 'textarea', 'hint' => 'The text shown in the banner. Leave blank to use the default message.'],
                    'cookie_button'    => ['label' => 'Accept button text', 'type' => 'text', 'hint' => 'Label for the accept button. Leave blank to use "Accept".'],
                    'cookie_link_text' => ['label' => 'Privacy link text', 'type' => 'text', 'hint' => 'Label for the link to your privacy page. Leave blank to use "Privacy Policy".'],
                    'cookie_layout'     => ['label' => 'Layout', 'type' => 'select', 'options' => ['box' => 'Box (card)', 'bar' => 'Bar (full width)'], 'hint' => 'Box = a floating card in a corner. Bar = a full-width strip across the screen.'],
                    'cookie_position_y' => ['label' => 'Position Y', 'type' => 'select', 'options' => ['bottom' => 'Bottom', 'top' => 'Top'], 'hint' => 'Vertical position of the banner: top or bottom of the screen.'],
                    'cookie_position_x' => ['label' => 'Position X', 'type' => 'select', 'options' => ['right' => 'Right', 'center' => 'Center', 'left' => 'Left'], 'hint' => 'Horizontal position of the banner. Only applies to the Box layout.'],
                ],
            ],
            'announcement' => [
                'section' => 'Interface',
                'title' => 'Announcements',
                'icon'  => 'fa-solid fa-bullhorn',
                'desc'  => 'A site-wide banner shown at the top of every page.',
                'fields' => [
                    'announcement_enabled'     => ['label' => 'Show announcement bar', 'type' => 'toggle', 'hint' => 'Display a banner at the top of every public page.'],
                    'announcement_message'     => ['label' => 'Message', 'type' => 'textarea', 'hint' => 'The text shown in the bar. Keep it short.'],
                    'announcement_link'        => ['label' => 'Link URL (optional)', 'type' => 'text', 'hint' => 'Makes the bar clickable, e.g. a Telegram invite or an article. Leave blank for no link.'],
                    'announcement_link_text'   => ['label' => 'Link text (optional)', 'type' => 'text', 'hint' => 'Label for the link, e.g. Join now. Defaults to "Learn more".'],
                    'announcement_color'       => ['label' => 'Color', 'type' => 'select', 'options' => ['blue' => 'Blue (info)', 'green' => 'Green (success)', 'amber' => 'Amber (warning)', 'red' => 'Red (alert)'], 'hint' => 'Background color of the bar.'],
                    'announcement_dismissible' => ['label' => 'Allow visitors to close it', 'type' => 'toggle', 'hint' => 'Show an X so visitors can dismiss the bar. It reappears whenever you change the message.'],
                ],
            ],
            'general' => [
                'section' => 'Site',
                'title' => 'General',
                'icon' => 'fa-solid fa-gear',
                'desc'  => 'Site identity, contact & defaults.',
                'fields' => [
                    'site_name'     => ['label' => 'Website title', 'type' => 'text', 'hint' => 'Your site name. Appears in the browser tab, header, footer, emails and search results.'],
                    'site_tagline'  => ['label' => 'Tagline', 'type' => 'text', 'hint' => 'A short phrase describing your site, shown in the footer.'],
                    'contact_email' => ['label' => 'Contact email', 'type' => 'text', 'hint' => 'Public email address where visitors can reach you.'],
                    'timezone'      => ['label' => 'Default timezone', 'type' => 'select', 'options' => array_combine($tz, $tz), 'hint' => 'Timezone used to display match kickoff times and dates.'],
                    'footer_text'   => ['label' => 'Footer text', 'type' => 'textarea', 'hint' => 'Small print or disclaimer shown at the very bottom of every page.'],
                ],
            ],
            'branding' => [
                'section' => 'Site',
                'title' => 'Branding',
                'icon' => 'fa-solid fa-image',
                'desc'  => 'Logo, favicon, share image & color.',
                'fields' => [
                    'logo'        => ['label' => 'Logo', 'type' => 'file', 'hint' => 'Shown in the site header. Use a PNG or SVG with a transparent background.'],
                    'favicon'     => ['label' => 'Favicon', 'type' => 'file', 'hint' => 'Small icon shown in the browser tab. A square .png, .ico or .svg works best.'],
                    'og_image'    => ['label' => 'Opengraph / share image', 'type' => 'file', 'hint' => 'Default preview image when your site is shared on social media. Recommended 1200×630.'],
                    'theme_color' => ['label' => 'Theme color', 'type' => 'color', 'hint' => 'Browser UI and PWA accent color (mobile address bar, app icon background).'],
                ],
            ],
            'seo' => [
                'section' => 'Marketing',
                'title' => 'SEO',
                'icon' => 'fa-solid fa-magnifying-glass',
                'desc'  => 'Meta, analytics & verification.',
                'fields' => [
                    'meta_title_suffix'   => ['label' => 'Title suffix', 'type' => 'text', 'hint' => 'Text added after every page title, e.g. " — LiveScore". Helps brand your tabs and search results.'],
                    'meta_description'    => ['label' => 'Default meta description', 'type' => 'textarea', 'hint' => 'Fallback description for search engines and social shares when a page has none of its own.'],
                    'ga_id'               => ['label' => 'Google Analytics ID', 'type' => 'text', 'hint' => 'Your Google Analytics 4 measurement ID, e.g. G-XXXXXXXXXX. Leave blank to disable analytics.'],
                    'gtm_id'              => ['label' => 'Google Tag Manager ID', 'type' => 'text', 'hint' => 'Your Google Tag Manager container ID, e.g. GTM-XXXXXXX.'],
                    'google_verification' => ['label' => 'Google Search Console code', 'type' => 'text', 'hint' => 'Verification code from Google Search Console (the content value of its meta tag).'],
                    'bing_verification'   => ['label' => 'Bing verification code', 'type' => 'text', 'hint' => 'Verification code from Bing Webmaster Tools.'],
                ],
            ],
            'social' => [
                'section' => 'Marketing',
                'title' => 'Social',
                'icon' => 'fa-solid fa-share-nodes',
                'desc'  => 'Social profile links & handle.',
                'fields' => [
                    'facebook_url'   => ['label' => 'Facebook URL', 'type' => 'text', 'hint' => 'Full link to your Facebook page. Leave blank to hide the icon.'],
                    'twitter_url'    => ['label' => 'Twitter / X URL', 'type' => 'text', 'hint' => 'Full link to your Twitter / X profile. Leave blank to hide the icon.'],
                    'twitter_handle' => ['label' => 'Twitter handle', 'type' => 'text', 'hint' => 'Your Twitter / X username without the @, e.g. livescore. Used for Twitter share cards.'],
                    'instagram_url'  => ['label' => 'Instagram URL', 'type' => 'text', 'hint' => 'Full link to your Instagram profile. Leave blank to hide the icon.'],
                    'youtube_url'    => ['label' => 'YouTube URL', 'type' => 'text', 'hint' => 'Full link to your YouTube channel. Leave blank to hide the icon.'],
                    'telegram_url'   => ['label' => 'Telegram URL', 'type' => 'text', 'hint' => 'Full link to your Telegram channel. Leave blank to hide the icon.'],
                    'tiktok_url'     => ['label' => 'TikTok URL', 'type' => 'text', 'hint' => 'Full link to your TikTok profile. Leave blank to hide the icon.'],
                    'community_label' => ['label' => 'Community button text', 'type' => 'text', 'hint' => 'Text shown on the community button, e.g. Join our community.'],
                    'community_url'   => ['label' => 'Community button link', 'type' => 'text', 'hint' => 'Link to your group (Telegram/WhatsApp/Discord). The button appears after a user votes on a match. Leave blank to hide it.'],
                ],
            ],
            'content' => [
                'section' => 'Site',
                'title' => 'Content',
                'icon' => 'fa-solid fa-newspaper',
                'desc'  => 'News & navigation toggles.',
                'fields' => [
                    'news_enabled'      => ['label' => 'Enable News section', 'type' => 'toggle', 'hint' => 'Master switch for the whole News/blog. Turn OFF for a livescore-only site — hides the News menu, homepage news block, footer link and mobile tab. Your saved articles stay safe for when you re-enable it.'],
                    'articles_per_page' => ['label' => 'Articles per page', 'type' => 'number', 'hint' => 'How many news articles to show per page on the news listing. Default is 9.'],
                    'show_tips'         => ['label' => 'Show "Tips" in menu', 'type' => 'toggle', 'hint' => 'Show the "Tips" link in the main navigation menu.'],
                    'show_transfers'    => ['label' => 'Show "Transfers" in menu', 'type' => 'toggle', 'hint' => 'Show the "Transfers" link in the main navigation menu.'],
                    'comments_enabled' => ['label' => 'Enable comments', 'type' => 'toggle', 'hint' => 'Allow visitors to comment on articles. Comments are moderated before they appear.'],
                    'highlights_enabled' => ['label' => 'Enable Highlights', 'type' => 'toggle', 'hint' => 'Show video highlights — menu link, /highlights page, match embeds and the admin Highlights menu. Turn off if you are not curating highlights yet.'],
                    'motd_enabled'       => ['label' => 'Enable Match of the Day', 'type' => 'toggle', 'hint' => 'Show the featured "Match of the Day" hero on the homepage, plus the admin menu. Turn off to hide it.'],
                    'newsletter_enabled' => ['label' => 'Enable Newsletter', 'type' => 'toggle', 'hint' => 'Show the email signup band in the footer, plus the admin Newsletter & Subscribers menus. Turn off if you are not collecting emails yet.'],
                ],
            ],
            'ads' => [
                'section' => 'Marketing',
                'title' => 'Ads',
                'icon' => 'fa-solid fa-rectangle-ad',
                'desc'  => 'AdSense & custom ad code.',
                'fields' => [
                    'ads_enabled'    => ['label' => 'Enable ads', 'type' => 'toggle', 'hint' => 'Master switch for all ads. Keep OFF until you have ad code — this hides every ad slot across the site.'],
                    'adsense_client' => ['label' => 'AdSense Publisher ID', 'type' => 'text', 'hint' => 'Your Google AdSense publisher ID, e.g. ca-pub-XXXXXXXXXXXXXXXX.'],
                    'ad_horizontal' => ['label' => 'Horizontal ad (banner / leaderboard)', 'type' => 'textarea', 'hint' => 'Ad code for wide banners at the top of the home and news pages (~728×90).', 'placeholder' => "<ins class=\"adsbygoogle\" style=\"display:block\" data-ad-client=\"ca-pub-XXXXXXXXXXXXXXXX\" data-ad-slot=\"1234567890\" data-ad-format=\"horizontal\" data-full-width-responsive=\"true\"></ins>\n<script>(adsbygoogle = window.adsbygoogle || []).push({});</script>"],
                    'ad_rectangle'  => ['label' => 'Rectangle / square ad (1:1)', 'type' => 'textarea', 'hint' => 'Ad code shown inside articles and on match pages (~336×280).', 'placeholder' => "<ins class=\"adsbygoogle\" style=\"display:inline-block;width:336px;height:280px\" data-ad-client=\"ca-pub-XXXXXXXXXXXXXXXX\" data-ad-slot=\"1234567890\"></ins>\n<script>(adsbygoogle = window.adsbygoogle || []).push({});</script>"],
                    'ad_vertical'   => ['label' => 'Vertical ad (sidebar / skyscraper)', 'type' => 'textarea', 'hint' => 'Ad code for tall ads in the sidebars (~300×600).', 'placeholder' => "<ins class=\"adsbygoogle\" style=\"display:inline-block;width:300px;height:600px\" data-ad-client=\"ca-pub-XXXXXXXXXXXXXXXX\" data-ad-slot=\"1234567890\"></ins>\n<script>(adsbygoogle = window.adsbygoogle || []).push({});</script>"],
                ],
            ],
            'cloudflare' => [
                'section' => 'Integrations',
                'title' => 'Cloudflare',
                'icon'  => 'fa-solid fa-cloud',
                'desc'  => 'Auto-add backup domains via the Cloudflare API.',
                'fields' => [
                    'cf_email'   => ['label' => 'Cloudflare account email', 'type' => 'text', 'hint' => 'The email you log in to Cloudflare with.'],
                    'cf_api_key' => ['label' => 'Global API key', 'type' => 'secret', 'hint' => 'Cloudflare → My Profile → API Tokens → Global API Key (View). Stored encrypted; leave blank to keep current.'],
                    'vps_ip'     => ['label' => 'Server IP (A record target)', 'type' => 'text', 'hint' => 'Public IP of this VPS. New domains point here.'],
                    'cf_proxied' => ['label' => 'Proxy through Cloudflare', 'type' => 'toggle', 'hint' => 'Orange cloud — enables Cloudflare SSL, CDN and DDoS protection (recommended ON).'],
                ],
            ],
            'maintenance' => [
                'section' => 'System',
                'title' => 'Maintenance',
                'icon' => 'fa-solid fa-wrench',
                'desc'  => 'Take the public site offline temporarily.',
                'fields' => [
                    'maintenance_mode'    => ['label' => 'Enable maintenance mode', 'type' => 'toggle', 'hint' => 'Show a maintenance page to visitors. Admins can still browse and manage the site normally.'],
                    'maintenance_image'   => ['label' => 'Maintenance image (optional)', 'type' => 'file', 'hint' => 'Custom illustration for the maintenance page. Leave empty to use the default animated icon.'],
                    'maintenance_title'   => ['label' => 'Heading', 'type' => 'text', 'hint' => 'Main heading on the maintenance page, e.g. We will be back soon.'],
                    'maintenance_message' => ['label' => 'Message', 'type' => 'textarea', 'hint' => 'Short message shown to visitors while the site is offline.'],
                ],
            ],
        ];
    }

    public function show(string $group = 'general')
    {
        $groups = $this->groups();
        abort_unless(isset($groups[$group]), 404);
        return view('admin.settings', [
            'group'  => $group,
            'config' => $groups[$group],
            'groups' => $groups,
            'values' => Setting::allCached(),
        ]);
    }

    public function update(Request $request, string $group)
    {
        $groups = $this->groups();
        abort_unless(isset($groups[$group]), 404);

        foreach ($groups[$group]['fields'] as $key => $field) {
            $type = $field['type'] ?? 'text';

            if ($type === 'file') {
                if ($request->boolean($key . '_delete')) {
                    if ($old = Setting::get($key)) {
                        Storage::disk('public')->delete($old);
                    }
                    Setting::put($key, null);
                }
                if ($request->hasFile($key)) {
                    $request->validate([$key => ['file', 'max:2048']]);
                    if ($old = Setting::get($key)) {
                        Storage::disk('public')->delete($old);
                    }
                    Setting::put($key, $request->file($key)->store('settings', 'public'));
                }
                continue;
            }

            if ($type === 'secret') {
                $input = $request->input($key);
                if (filled($input)) {
                    Setting::put($key, \Illuminate\Support\Facades\Crypt::encryptString($input));
                }
                continue; // blank = keep existing key
            }

            $value = $type === 'toggle' ? ($request->boolean($key) ? '1' : '0') : $request->input($key);
            Setting::put($key, $value);
        }

        return back()->with('ok', 'Settings saved.');
    }
}
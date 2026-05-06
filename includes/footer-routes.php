<?php
/**
 * Map CMS page slugs to canonical front-end URLs.
 * Dedicated PHP pages use canonical URLs. Thin wrappers hit page.php with a preset slug;
 * unpublished/missing CMS rows fall back to includes/static-cms-pages.php seeds.
 */
if (!function_exists('footer_resolve_url')) {
    function footer_resolve_url($slug) {
        $slug = strtolower(trim((string) $slug));

        static $canonical = [
            'become-agent'          => 'become-agent.php',
            'mortgage-calculator'   => 'mortgage-calculator.php',
            'referral-network'      => 'referral-network.php',
            'about'                 => 'about.php',
            'partners'              => 'partners.php',
            'careers'               => 'careers.php',
            'why-iproply'           => 'why-iproply.php',
            'our-story'             => 'our-story.php',
            'community-impact'      => 'community-impact.php',
            'inclusion'             => 'inclusion.php',
            'press'                 => 'press.php',
            'help-center'           => 'help-center.php',
            'market-reports'        => 'market-reports.php',
            'advertise'             => 'advertise.php',
            'terms-of-use'          => 'terms-of-use.php',
            'privacy-policy'        => 'privacy-policy.php',
            'do-not-sell'           => 'do-not-sell.php',
            'fair-housing'          => 'fair-housing.php',
            'fair-housing-policy'   => 'fair-housing-policy.php',
            'texas-ibs'             => 'texas-ibs.php',
        ];

        if (isset($canonical[$slug])) {
            return base_url($canonical[$slug]);
        }

        return base_url('page.php?slug=' . urlencode($slug));
    }
}

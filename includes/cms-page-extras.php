<?php
/**
 * Optional HTML appended after CMS page body (CTAs and official links).
 */
if (!function_exists('cms_page_after_content_html')) {
    function cms_page_after_content_html($slug) {
        $slug = strtolower(trim((string) $slug));

        $contact = htmlspecialchars(base_url('contact.php'), ENT_QUOTES, 'UTF-8');
        $listings = htmlspecialchars(base_url('listings.php'), ENT_QUOTES, 'UTF-8');
        $agentLogin = htmlspecialchars(base_url('agent/login.php'), ENT_QUOTES, 'UTF-8');
        $calc = htmlspecialchars(base_url('mortgage-calculator.php'), ENT_QUOTES, 'UTF-8');
        $blog = htmlspecialchars(base_url('blog.php'), ENT_QUOTES, 'UTF-8');
        $reviews = htmlspecialchars(base_url('reviews.php'), ENT_QUOTES, 'UTF-8');
        $help = htmlspecialchars(base_url('help-center.php'), ENT_QUOTES, 'UTF-8');
        $trec = htmlspecialchars('https://www.trec.texas.gov/forms/consumer-protection-notice', ENT_QUOTES, 'UTF-8');

        ob_start();

        switch ($slug) {
            case 'texas-ibs':
                ?>
                <div class="cms-page-extras">
                    <p><a class="btn btn-primary" href="<?php echo $trec; ?>" target="_blank" rel="noopener">Open TREC Consumer Protection Notice</a></p>
                </div>
                <?php
                break;

            case 'help-center':
                ?>
                <div class="cms-page-extras cms-quick-links">
                    <h3 class="cms-heading">Quick links</h3>
                    <div class="cms-quick-links__grid">
                        <a class="btn btn-secondary" href="<?php echo $listings; ?>">Browse listings</a>
                        <a class="btn btn-secondary" href="<?php echo $contact; ?>">Contact support</a>
                        <a class="btn btn-secondary" href="<?php echo $reviews; ?>">Reviews</a>
                        <a class="btn btn-secondary" href="<?php echo $blog; ?>">Blog &amp; insights</a>
                        <a class="btn btn-secondary" href="<?php echo $agentLogin; ?>">Agent login</a>
                        <a class="btn btn-secondary" href="<?php echo $calc; ?>">Mortgage calculator</a>
                    </div>
                </div>
                <?php
                break;

            case 'market-reports':
                ?>
                <div class="cms-page-extras cms-cta-banner">
                    <a class="btn btn-secondary" href="<?php echo $listings; ?>">Browse current listings</a>
                    <a class="btn btn-primary" href="<?php echo $contact; ?>">Request a market snapshot</a>
                </div>
                <div class="cms-page-extras cms-inline-links">
                    <p class="cms-inline-links__label">More tools</p>
                    <p class="cms-inline-links__row">
                        <a href="<?php echo $calc; ?>">Mortgage calculator</a>
                        <span aria-hidden="true"> · </span>
                        <a href="<?php echo $help; ?>">Help center</a>
                        <span aria-hidden="true"> · </span>
                        <a href="<?php echo $blog; ?>">Market insights (blog)</a>
                    </p>
                </div>
                <?php
                break;

            case 'about':
            case 'why-iproply':
            case 'our-story':
            case 'community-impact':
            case 'inclusion':
                ?>
                <div class="cms-page-extras cms-cta-banner">
                    <a class="btn btn-secondary" href="<?php echo $listings; ?>">Browse listings</a>
                    <a class="btn btn-secondary" href="<?php echo $blog; ?>">Blog &amp; insights</a>
                    <a class="btn btn-primary" href="<?php echo $contact; ?>">Contact us</a>
                </div>
                <?php
                break;

            case 'careers':
                ?>
                <div class="cms-page-extras cms-cta-banner">
                    <a class="btn btn-secondary" href="<?php echo $listings; ?>">Browse listings</a>
                    <a class="btn btn-secondary" href="<?php echo $blog; ?>">Blog &amp; insights</a>
                    <a class="btn btn-primary" href="<?php echo $contact; ?>">Apply / get in touch</a>
                </div>
                <p class="cms-extra-hint">Send your resume and the role you want through our contact form — we reply to every serious inquiry.</p>
                <?php
                break;

            case 'press':
                ?>
                <div class="cms-page-extras cms-cta-banner">
                    <a class="btn btn-secondary" href="<?php echo $blog; ?>">Blog &amp; insights</a>
                    <a class="btn btn-primary" href="<?php echo $contact; ?>">Media &amp; press contact</a>
                </div>
                <?php
                break;

            case 'partners':
            case 'advertise':
            case 'do-not-sell':
            case 'referral-network':
                ?>
                <div class="cms-page-extras cms-cta-banner">
                    <a class="btn btn-secondary" href="<?php echo $listings; ?>">Browse listings</a>
                    <a class="btn btn-primary btn-lg" href="<?php echo $contact; ?>">Contact us</a>
                </div>
                <?php
                break;

            default:
                break;
        }

        return (string) ob_get_clean();
    }
}

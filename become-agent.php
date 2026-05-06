<?php
/**
 * iProply — Become an Agent (full experience; not CMS-only).
 */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/Database.php';

$currentPage       = 'become-agent';
$pageTitle         = 'Become an Agent';
$metaTitle         = 'Become a Real Estate Agent on ' . APP_NAME . ' | Partner Tools & Visibility';
$metaDescription   = 'Join ' . APP_NAME . ' as a licensed agent: polished profiles, listing tools, inquiry management, and a path from application to your first live listings.';
$extraCss          = ['become-agent.css'];

$db           = Database::getInstance();
$siteSettings = $db->selectOne('site_settings', '*');

include __DIR__ . '/partials/header.php';
?>

<header class="ba-hero">
    <div class="ba-hero-bg" aria-hidden="true"></div>
    <div class="container ba-hero-inner">
        <div>
            <div class="ba-kicker"><i class="fas fa-star"></i> Grow with us</div>
            <h1 class="ba-h1">Turn your license into a <em>premium presence</em> clients trust.</h1>
            <p class="ba-lead">
                <?php echo sanitize($siteSettings['site_name'] ?? APP_NAME); ?> helps agents present listings beautifully, respond faster to serious buyers and renters,
                and keep momentum from first application to published inventory — without juggling five different tools.
            </p>
            <div class="ba-actions">
                <a class="btn btn-primary btn-lg" href="<?php echo base_url('agent/register.php'); ?>">
                    <i class="fas fa-user-plus"></i> Start your application
                </a>
                <a class="btn btn-outline btn-lg ba-outline-light" href="<?php echo base_url('contact.php'); ?>">
                    <i class="fas fa-comments"></i> Ask a question
                </a>
            </div>
        </div>

        <aside class="ba-side-card reveal-ba" aria-labelledby="ba-side-h">
            <h2 id="ba-side-h" class="ba-side-title">What agents use every week</h2>
            <ul class="ba-check" style="list-style:none;margin:0;padding:0">
                <li><i class="fas fa-check-circle"></i> A clean public profile that matches our luxury site design</li>
                <li><i class="fas fa-check-circle"></i> Listing workflows for sale and rent with rich media</li>
                <li><i class="fas fa-check-circle"></i> Inquiry routing you can action from one dashboard</li>
                <li><i class="fas fa-check-circle"></i> Guidance content for buyers, renters, and partners</li>
                <li><i class="fas fa-check-circle"></i> Optional referral and partner programs as we expand</li>
            </ul>
            <div style="margin-top:1.35rem">
                <a href="<?php echo base_url('agents.php'); ?>" class="btn btn-primary" style="width:100%;justify-content:center">
                    See featured agents <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </aside>
    </div>
</header>

<section class="section section-bg">
    <div class="container">
        <div class="section-header" style="text-align:center;max-width:700px;margin:0 auto">
            <div class="section-eyebrow" style="justify-content:center">Why <?php echo sanitize($siteSettings['site_name'] ?? APP_NAME); ?></div>
            <h2 class="section-title">Built for agents who care about experience</h2>
            <div class="section-divider" style="margin:1rem auto"></div>
            <p class="section-subtitle">Practical tools and presentation standards so your name stays credible from the first click to the closing table.</p>
        </div>

        <div class="ba-grid">
            <div class="ba-card reveal-ba">
                <div class="ba-card-ic"><i class="fas fa-bolt"></i></div>
                <h3>Faster first impressions</h3>
                <p>Listings and profiles follow a consistent layout — sharp photos, clear specs, and scannable copy so shoppers stay engaged.</p>
            </div>
            <div class="ba-card reveal-ba reveal-ba-d1">
                <div class="ba-card-ic"><i class="fas fa-inbox"></i></div>
                <h3>Leads in one lane</h3>
                <p>Buyer and renter inquiries land where you already work, with context from the listing so replies are specific, not generic.</p>
            </div>
            <div class="ba-card reveal-ba reveal-ba-d2">
                <div class="ba-card-ic"><i class="fas fa-chart-line"></i></div>
                <h3>Room to scale</h3>
                <p>Add inventory as you grow. Whether you are solo or building a small team, the same workflow supports more listings without chaos.</p>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="flex-header">
            <div style="max-width:640px">
                <div class="section-eyebrow"><i class="fas fa-route"></i> Simple path in</div>
                <h2 class="section-title">From application to live listings</h2>
                <div class="section-divider"></div>
                <p class="section-subtitle">We review every application to protect quality on the marketplace. Here is the typical flow.</p>
            </div>
        </div>

        <div class="ba-steps">
            <div class="ba-step reveal-ba">
                <div class="ba-step-num">1</div>
                <h3 class="section-title" style="font-size:1.1rem;margin-bottom:.5rem">Apply online</h3>
                <p class="section-subtitle" style="font-size:.95rem;margin:0">Create your agent account, share your license and markets, and tell us how you serve clients.</p>
            </div>
            <div class="ba-step reveal-ba reveal-ba-d1">
                <div class="ba-step-num">2</div>
                <h3 class="section-title" style="font-size:1.1rem;margin-bottom:.5rem">Team review</h3>
                <p class="section-subtitle" style="font-size:.95rem;margin:0">We verify basics and activate approved accounts so you can sign in to the agent portal.</p>
            </div>
            <div class="ba-step reveal-ba reveal-ba-d2">
                <div class="ba-step-num">3</div>
                <h3 class="section-title" style="font-size:1.1rem;margin-bottom:.5rem">Publish &amp; respond</h3>
                <p class="section-subtitle" style="font-size:.95rem;margin:0">Add listings, upload media, and keep inquiry response times strong to build trust on the platform.</p>
            </div>
        </div>

        <div class="ba-requirements">
            <h3 class="section-title" style="text-align:center;font-size:1.25rem">Before you apply</h3>
            <p class="section-subtitle" style="text-align:center;max-width:520px;margin:.75rem auto 0">Have this ready to move faster through review (requirements may vary by market).</p>
            <ul class="ba-req-grid" style="list-style:none;padding:0;margin:0">
                <li><i class="fas fa-id-card"></i> Active real estate license (or equivalent for your state)</li>
                <li><i class="fas fa-building"></i> Brokerage affiliation and compliance where required</li>
                <li><i class="fas fa-envelope"></i> Professional email and phone you check daily</li>
                <li><i class="fas fa-images"></i> Headshot and short bio for your public profile</li>
            </ul>
        </div>

        <div class="cta-banner reveal-ba" style="margin-top:3rem">
            <div>
                <h2>Ready to apply?</h2>
                <p>It takes a few minutes to submit your profile. If you are not sure yet, contact us — we are happy to walk through fit and next steps.</p>
            </div>
            <div class="cta-btns">
                <a href="<?php echo base_url('agent/register.php'); ?>" class="btn-cta-w"><i class="fas fa-user-plus"></i> Apply now</a>
                <a href="<?php echo base_url('contact.php'); ?>" class="btn-cta-g"><i class="fas fa-phone"></i> Contact</a>
            </div>
        </div>

        <p class="ba-disclaimer">
            <?php echo sanitize($siteSettings['site_name'] ?? APP_NAME); ?> supports equal housing opportunity. Applications are reviewed for professionalism and policy fit;
            approval is not guaranteed. Nothing on this page is legal or tax advice — work with your broker and counsel for compliance in your market.
        </p>
    </div>
</section>

<script>
(function () {
    var obs = new IntersectionObserver(function (entries) {
        entries.forEach(function (e) {
            if (!e.isIntersecting) return;
            e.target.classList.add('in');
            obs.unobserve(e.target);
        });
    }, { threshold: 0.12 });
    document.querySelectorAll('.reveal-ba').forEach(function (el) { obs.observe(el); });
})();
</script>

<?php include __DIR__ . '/partials/footer.php'; ?>

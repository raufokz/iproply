<?php
/**
 * iProply - Referral Network
 * A partner-facing page inspired by common real-estate referral flows:
 * hero → value props → pillars → how it works → trust → CTA.
 */
require_once 'config/config.php';
require_once 'includes/Database.php';
require_once 'includes/Property.php';

$currentPage = 'referrals';
$pageTitle   = 'Referral Network';
$metaTitle   = 'Referral Network for Trusted Agents | ' . APP_NAME;
$metaDescription = 'Join a trusted referral program that helps clients connect with the right local expert — with streamlined handoffs, clear status updates, and measurable outcomes.';
$extraCss    = ['referral-network.css'];

include 'partials/header.php';
?>

<header class="ref-hero">
    <div class="ref-hero-bg" aria-hidden="true"></div>
    <div class="container ref-hero-inner">
        <div class="ref-hero-copy">
            <div class="ref-kicker"><i class="fas fa-handshake"></i> Partner Program</div>
            <h1 class="ref-h1">Referrals that feel <em>effortless</em> — from introduction to closing.</h1>
            <p class="ref-sub">
                <?php echo sanitize($siteSettings['site_name'] ?? APP_NAME); ?> brings buyers, sellers, and vetted agents
                together with a structured handoff: the right details at the right time, clear expectations,
                and a partner experience tuned for conversions — not juggling spreadsheets or unclear fees.
            </p>
            <div class="ref-hero-actions">
                <a class="btn btn-primary btn-lg" href="<?php echo base_url('become-agent.php'); ?>">
                    <i class="fas fa-user-check"></i> Apply as a Preferred Partner
                </a>
                <a class="btn btn-outline btn-lg ref-outline-on-light" href="<?php echo base_url('contact.php'); ?>">
                    <i class="fas fa-phone"></i> Talk to Our Team
                </a>
            </div>

            <div class="ref-metrics">
                <div class="ref-metric">
                    <div class="ref-metric-num">Verified</div>
                    <div class="ref-metric-lbl">Partner Standards</div>
                </div>
                <div class="ref-metric">
                    <div class="ref-metric-num">Faster</div>
                    <div class="ref-metric-lbl">Handoffs & Follow-ups</div>
                </div>
                <div class="ref-metric">
                    <div class="ref-metric-num">Clear</div>
                    <div class="ref-metric-lbl">Status + Commission Terms</div>
                </div>
            </div>
        </div>

        <div class="ref-hero-card">
            <h2 class="ref-card-title">What you get</h2>
            <ul class="ref-checklist">
                <li><i class="fas fa-check-circle"></i> Structured intake so you start with the right details</li>
                <li><i class="fas fa-check-circle"></i> Match to the best-fit local expert (not random routing)</li>
                <li><i class="fas fa-check-circle"></i> Shared timeline, notes, and milestones</li>
                <li><i class="fas fa-check-circle"></i> Clear referral terms and payout visibility</li>
                <li><i class="fas fa-check-circle"></i> Client experience standards from first call to closing</li>
            </ul>
            <div class="ref-card-cta">
                <a href="<?php echo base_url('agent/login.php'); ?>" class="btn btn-primary" style="width:100%;justify-content:center">
                    Partner Sign In <i class="fas fa-arrow-right"></i>
                </a>
                <div class="ref-card-note">Already a partner? Sign in to view and manage referrals.</div>
            </div>
        </div>
    </div>
</header>

<section class="ref-trust-wrap" aria-label="Partnership commitments">
    <div class="container ref-trust-inner">
        <div class="ref-trust-intro">
            <div class="section-eyebrow" style="margin-bottom:.5rem"><i class="fas fa-heart-pulse"></i> Why it works</div>
            <p class="ref-trust-lead">
                Strong introductions combine three ideas that clients actually notice:
                <strong>clarity from the start</strong> (what happens next — and why),
                <strong>local credibility</strong> (real expertise where the address is),
                and <strong>transparent operations</strong> (consistent rules teams can reinforce).
                That is how we structured the <?php echo sanitize($siteSettings['site_name'] ?? APP_NAME); ?> referral experience.
            </p>
        </div>
        <div class="ref-trust-cards">
            <div class="ref-trust-card reveal">
                <div class="ref-trust-ic"><i class="fas fa-map-location-dot"></i></div>
                <h3>Market-smart matching</h3>
                <p>Clients meet agents with relevant coverage and responsiveness — not random round-robin routing.</p>
            </div>
            <div class="ref-trust-card reveal reveal-d1">
                <div class="ref-trust-ic"><i class="fas fa-shield-halved"></i></div>
                <h3>Standards you can feel</h3>
                <p>Expectations for follow-up, presentation, and client care are defined up front, so every intro starts strong.</p>
            </div>
            <div class="ref-trust-card reveal reveal-d2">
                <div class="ref-trust-ic"><i class="fas fa-chart-line"></i></div>
                <h3>Progress you can see</h3>
                <p>Stages, notes, and outcomes stay visible to the right people — fewer “status unknown” handoffs.</p>
            </div>
        </div>
    </div>
</section>

<section class="section section-bg">
    <div class="container">
        <div class="section-header" style="text-align:center;max-width:760px">
            <div class="section-eyebrow" style="justify-content:center">Designed for outcomes</div>
            <h2 class="section-title">A smoother referral journey for everyone</h2>
            <div class="section-divider" style="margin:1rem auto"></div>
            <p class="section-subtitle">The same essentials across every market — with room for local expertise.</p>
        </div>

        <div class="ref-feature-grid">
            <div class="ref-feature reveal">
                <div class="ref-feature-ic"><i class="fas fa-clipboard-check"></i></div>
                <h3>Cleaner intake</h3>
                <p>We collect the right context upfront: timeline, financing stage, must-haves, and constraints — so you don’t start blind.</p>
            </div>
            <div class="ref-feature reveal reveal-d1">
                <div class="ref-feature-ic"><i class="fas fa-bullseye"></i></div>
                <h3>Smarter matching</h3>
                <p>Referrals are placed based on fit and performance signals — market coverage, responsiveness, and track record.</p>
            </div>
            <div class="ref-feature reveal reveal-d2">
                <div class="ref-feature-ic"><i class="fas fa-route"></i></div>
                <h3>White‑glove handoff</h3>
                <p>We keep the handoff simple with a clear next-step plan, client expectations, and a shared timeline for follow-ups.</p>
            </div>
            <div class="ref-feature reveal">
                <div class="ref-feature-ic"><i class="fas fa-comments"></i></div>
                <h3>Consistent communication</h3>
                <p>Clients know who’s calling, when, and why. Partners get clear milestones and updates, not vague “checking in” messages.</p>
            </div>
            <div class="ref-feature reveal reveal-d1">
                <div class="ref-feature-ic"><i class="fas fa-chart-line"></i></div>
                <h3>Measurable progress</h3>
                <p>Track referral status from intro to closing with clarity on activity, stages, and outcomes.</p>
            </div>
            <div class="ref-feature reveal reveal-d2">
                <div class="ref-feature-ic"><i class="fas fa-file-signature"></i></div>
                <h3>Transparent terms</h3>
                <p>Referral terms are captured early, so everyone has alignment — including payout visibility when the deal closes.</p>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="flex-header">
            <div style="max-width:720px">
                <div class="section-eyebrow"><i class="fas fa-gem"></i> Partnership pillars</div>
                <h2 class="section-title">What makes a great network</h2>
                <div class="section-divider"></div>
                <p class="section-subtitle">Eight pillars we use to keep referrals consistent, professional, and scalable.</p>
            </div>
        </div>

        <div class="ref-pillars">
            <?php
            $pillars = [
                ['fa-shield-alt','Trust & verification','Partners meet baseline standards for responsiveness, professionalism, and local market knowledge.'],
                ['fa-user-tie','Professional presentation','Clients get a clear introduction, expectations, and a strong first call experience.'],
                ['fa-globe','Market reach','Coverage across major metros and emerging markets, with local nuance where it matters.'],
                ['fa-layer-group','Scalable process','A consistent workflow that works for solo agents, teams, and brokerages without extra complexity.'],
                ['fa-inbox','Efficient lead handling','Clear ownership, fast follow-ups, and fewer “lost in the cracks” moments.'],
                ['fa-sliders-h','Flexible rules','Adaptable referral terms, client preferences, and partner constraints — documented upfront.'],
                ['fa-people-group','Collaboration','Shared notes and milestones to reduce back-and-forth and improve continuity for the client.'],
                ['fa-receipt','Transparent tracking','Clear status updates and referral terms visibility, so reporting is simple and disputes are rare.'],
            ];
            foreach ($pillars as [$ic,$title,$desc]):
            ?>
            <div class="ref-pillar reveal">
                <div class="ref-pillar-ic"><i class="fas <?php echo $ic; ?>"></i></div>
                <div>
                    <div class="ref-pillar-title"><?php echo $title; ?></div>
                    <div class="ref-pillar-desc"><?php echo $desc; ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section section-bg">
    <div class="container">
        <div class="section-header sec-center" style="text-align:center">
            <div class="section-eyebrow" style="justify-content:center">How it works</div>
            <h2 class="section-title">From intro to closing — without the chaos</h2>
            <div class="section-divider" style="margin:1rem auto"></div>
            <p class="section-subtitle">A simple flow that keeps clients confident and partners aligned.</p>
        </div>

        <div class="steps-wrap">
            <div class="step-card-enh reveal">
                <div class="step-num-circle">1</div>
                <h3>Intake & context</h3>
                <p>We capture timeline, motivation, budget, and next steps. Then we confirm the client’s preferred contact method and urgency.</p>
            </div>
            <div class="step-card-enh reveal reveal-d1">
                <div class="step-num-circle">2</div>
                <h3>Match & handoff</h3>
                <p>We connect the client with a best-fit local expert and share a structured summary so the first call is productive.</p>
            </div>
            <div class="step-card-enh reveal reveal-d2">
                <div class="step-num-circle">3</div>
                <h3>Track & close</h3>
                <p>Partners update milestones as the client tours, negotiates, and closes. Everyone stays informed through the full lifecycle.</p>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="ref-split">
            <div class="ref-split-card reveal">
                <div class="section-eyebrow">For agents</div>
                <h2 class="section-title" style="margin-bottom:.75rem">Grow with referrals you can actually convert</h2>
                <p class="section-subtitle" style="font-size:1rem">
                    You’ll get better context, clearer expectations, and a more consistent client experience — which improves close-rate and referral satisfaction.
                </p>
                <ul class="ref-bullets">
                    <li><i class="fas fa-check"></i> Response-time expectations and quality standards</li>
                    <li><i class="fas fa-check"></i> Better client fit through structured intake</li>
                    <li><i class="fas fa-check"></i> Visibility into referral terms and status</li>
                </ul>
                <div style="display:flex;gap:1rem;flex-wrap:wrap;margin-top:1.5rem">
                    <a href="<?php echo base_url('become-agent.php'); ?>" class="btn btn-primary">Become a Partner <i class="fas fa-arrow-right"></i></a>
                    <a href="<?php echo base_url('agents.php'); ?>" class="btn btn-outline" style="color:var(--primary);border-color:var(--primary);background:transparent">Meet Preferred Agents</a>
                </div>
            </div>

            <div class="ref-split-card reveal reveal-d1">
                <div class="section-eyebrow">For clients</div>
                <h2 class="section-title" style="margin-bottom:.75rem">Get connected to the right local expert</h2>
                <p class="section-subtitle" style="font-size:1rem">
                    Whether you’re buying, selling, or relocating, our goal is simple: match you with an agent who can deliver in your market.
                </p>
                <ul class="ref-bullets">
                    <li><i class="fas fa-check"></i> One introduction, clear next steps</li>
                    <li><i class="fas fa-check"></i> Local expertise and neighborhood guidance</li>
                    <li><i class="fas fa-check"></i> A smoother experience from search to closing</li>
                </ul>
                <div style="margin-top:1.5rem">
                    <a href="<?php echo base_url('contact.php'); ?>" class="btn btn-primary" style="width:100%;justify-content:center">
                        Request an Introduction <i class="fas fa-arrow-right"></i>
                    </a>
                    <div class="ref-card-note" style="text-align:center;margin-top:.75rem">No pressure. Just helpful guidance.</div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="cta-banner reveal">
            <div>
                <h2>Ready to partner with <?php echo sanitize($siteSettings['site_name'] ?? APP_NAME); ?>?</h2>
                <p>Apply to join the referral network and start receiving well-structured introductions — backed by clear standards and transparent tracking.</p>
            </div>
            <div class="cta-btns">
                <a href="<?php echo base_url('become-agent.php'); ?>" class="btn-cta-w"><i class="fas fa-user-check"></i> Apply Now</a>
                <a href="<?php echo base_url('contact.php'); ?>" class="btn-cta-g"><i class="fas fa-comments"></i> Ask a Question</a>
            </div>
        </div>
    </div>
</section>

<script>
/* Match homepage reveal animation */
const revObs2 = new IntersectionObserver(entries => {
    entries.forEach(e => {
        if (!e.isIntersecting) return;
        e.target.classList.add('in');
        revObs2.unobserve(e.target);
    });
}, { threshold: 0.12 });
document.querySelectorAll('.reveal').forEach(el => revObs2.observe(el));
</script>

<?php include 'partials/footer.php'; ?>


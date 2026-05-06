<?php
/**
 * iProply - Reviews (illustrative testimonials; no aggregate claims until live data exists)
 */
require_once 'config/config.php';
require_once 'includes/Database.php';

$currentPage     = 'reviews';
$pageTitle       = 'Client Stories';
$metaTitle       = 'Client Stories & Reviews | iProply';
$metaDescription = 'See the kind of experience iProply aims to deliver. Share your own feedback anytime via our contact form.';

include 'partials/header.php';
?>

<section class="cms-hero">
    <div class="container">
        <h1 class="cms-title">Client stories</h1>
        <p class="cms-subtitle">
            These sample narratives illustrate the service experience we build toward with agents and clients.
            We do not display live review counts or verified purchase badges here yet.
            <a href="<?php echo base_url('contact.php'); ?>" style="color: rgba(255,255,255,0.95); text-decoration: underline;">Tell us how we did</a>
            — we read every message.
        </p>
    </div>
</section>

<section class="cms-section">
    <div class="container">
        <div class="cms-content" style="max-width:1000px">

            <p class="reviews-disclaimer" style="padding:1rem 1.25rem;background:var(--warm-100);border-radius:var(--radius-lg);margin-bottom:2rem;font-size:0.95rem;color:var(--text-secondary)">
                <strong style="color:var(--navy-900)">Note:</strong> Quotes below are <em>illustrative examples</em> for layout and tone only. They are not attributed to real transactions on this site.
            </p>

            <?php
            $reviews = [
                ['Brian Gallagher','New York, NY','Example scenario','Exceptional from first call to closing. My agent knew the local market and helped us weigh offers with clear, practical advice.',5,'Upper West Side, NYC'],
                ['Jessica Tran','Los Angeles, CA','Example scenario','Responsive communication, organized tour schedule, and a smooth application process from search to lease signing.',5,'Silver Lake, Los Angeles'],
                ['Kevin O\'Brien','Chicago, IL','Example scenario','Strong listing preparation guidance and steady updates through inspection and closing.',5,'Lincoln Park, Chicago'],
                ['Angela Morris','Miami, FL','Example scenario','Transparent pros and cons for each property we toured made it easier to decide with confidence.',4,'Coral Gables, Miami'],
            ];
            foreach ($reviews as [$name,$city,$meta,$text,$stars,$prop]):
            ?>
            <div style="border:1px solid var(--border);border-radius:var(--radius-lg);padding:1.75rem;margin-bottom:1.5rem;background:#fff">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:1rem;margin-bottom:1rem">
                    <div>
                        <div style="font-weight:600;font-size:1.05rem;color:var(--primary)"><?php echo htmlspecialchars($name); ?></div>
                        <div style="font-size:0.875rem;color:var(--text-secondary)"><?php echo htmlspecialchars($city); ?> &bull; <?php echo htmlspecialchars($meta); ?></div>
                        <div style="color:#f59e0b;font-size:1rem;margin-top:0.25rem">
                            <?php for ($i = 0; $i < $stars; $i++) {
                                echo '★';
                            } for ($i = $stars; $i < 5; $i++) {
                                echo '☆';
                            } ?>
                        </div>
                    </div>
                    <span style="font-size:0.8rem;background:rgba(30,59,90,0.07);color:var(--primary);padding:0.25rem 0.75rem;border-radius:var(--radius-full);font-weight:500"><?php echo htmlspecialchars($prop); ?></span>
                </div>
                <p style="color:var(--text-secondary);line-height:1.75;margin:0">"<?php echo htmlspecialchars($text); ?>"</p>
            </div>
            <?php endforeach; ?>

            <div style="text-align:center;margin-top:2rem">
                <a href="<?php echo base_url('contact.php'); ?>" class="btn btn-primary btn-lg">
                    <i class="fas fa-envelope"></i> Share feedback
                </a>
            </div>
        </div>
    </div>
</section>

<?php include 'partials/footer.php'; ?>

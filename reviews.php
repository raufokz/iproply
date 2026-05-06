<?php
/**
 * iProply - Reviews (redirect to contact for now)
 */
require_once 'config/config.php';
require_once 'includes/Database.php';
require_once 'includes/Page.php';
require_once 'includes/Property.php';

$currentPage    = 'reviews';
$pageTitle      = 'Client Reviews';
$metaTitle      = 'Verified Client Reviews | iProply';
$metaDescription = 'Read thousands of verified reviews from buyers, sellers, and renters across America who chose iProply.';

$db = Database::getInstance();
include 'partials/header.php';
?>

<section class="cms-hero">
    <div class="container">
        <h1 class="cms-title">Verified Client Reviews</h1>
        <p class="cms-subtitle">12,847 verified reviews from buyers, sellers, and renters across America.</p>
    </div>
</section>

<section class="cms-section">
    <div class="container">
        <div class="cms-content" style="max-width:1000px">

            <!-- Rating Summary -->
            <div style="display:flex;gap:3rem;flex-wrap:wrap;align-items:center;margin-bottom:3rem;padding:2rem;background:var(--warm-100);border-radius:var(--radius-xl);">
                <div style="text-align:center;flex-shrink:0;">
                    <div style="font-size:4rem;font-weight:700;color:var(--primary);line-height:1">4.9</div>
                    <div style="color:#f59e0b;font-size:1.25rem;margin:0.25rem 0">★★★★★</div>
                    <div style="font-size:0.875rem;color:var(--text-secondary)">Based on 12,847 reviews</div>
                </div>
                <div style="flex:1;min-width:200px">
                    <?php foreach([[5,91],[4,6],[3,2],[2,1],[1,0]] as [$s,$p]): ?>
                    <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:0.5rem">
                        <span style="font-size:0.875rem;width:1rem;text-align:right"><?php echo $s; ?></span>
                        <div style="flex:1;height:8px;background:var(--border);border-radius:4px;overflow:hidden">
                            <div style="width:<?php echo $p; ?>%;height:100%;background:var(--gold-100);border-radius:4px"></div>
                        </div>
                        <span style="font-size:0.875rem;color:var(--text-secondary);width:2.5rem"><?php echo $p; ?>%</span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Reviews List -->
            <?php
            $reviews = [
                ['Brian Gallagher','New York, NY','Purchased Dec 2024','Exceptional from first call to closing. My agent knew the Manhattan market inside out and helped us secure a 2-bed at below asking. The legal team handled all paperwork flawlessly.',5,'Upper West Side, NYC'],
                ['Jessica Tran','Los Angeles, CA','Rented Nov 2024','Found my apartment in Silver Lake in 5 days. Accurate photos, responsive agent, entirely digital application. If I ever move again, iProply is my first call.',5,'Silver Lake, Los Angeles'],
                ['Kevin O\'Brien','Chicago, IL','Sold Oct 2024','Listed my Lincoln Park home and had a full-price offer in 9 days. The professional listing package — staging advice, pro photography, featured placement — is worth every penny.',5,'Lincoln Park, Chicago'],
                ['Angela Morris','Miami, FL','Purchased Sep 2024','The neighborhood scoring and school district data helped us narrow 200 listings to 12 worth visiting. Our agent walked us through each one honestly, including the downsides.',4,'Coral Gables, Miami'],
                ['Michael Torres','Houston, TX','Sold Aug 2024','Sold my 4-bed in Katy in 14 days. The pricing recommendation tool was spot-on — we listed $15k above what Zillow suggested and still got multiple offers.',5,'Katy, Houston TX'],
                ['Sarah Whitfield','Phoenix, AZ','Purchased Jul 2024','First-time homebuyer, zero experience. My iProply agent was incredibly patient and educational. Closed in 30 days with no surprises.',5,'Scottsdale, AZ'],
                ['David Park','Seattle, WA','Rented Jun 2024','Moved from South Korea for work. Language support was excellent. Found a fully furnished apartment in Bellevue within a week of landing.',5,'Bellevue, WA'],
                ['Carmen Rivera','Dallas, TX','Sold May 2024','Listed and sold my condo in Uptown Dallas in 6 days. The featured placement on the Pro plan absolutely delivers ROI.',5,'Uptown Dallas, TX'],
            ];
            foreach ($reviews as [$name,$city,$meta,$text,$stars,$prop]):
            ?>
            <div style="border:1px solid var(--border);border-radius:var(--radius-lg);padding:1.75rem;margin-bottom:1.5rem;background:#fff">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:1rem;margin-bottom:1rem">
                    <div>
                        <div style="font-weight:600;font-size:1.05rem;color:var(--primary)"><?php echo htmlspecialchars($name); ?></div>
                        <div style="font-size:0.875rem;color:var(--text-secondary)"><?php echo htmlspecialchars($city); ?> &bull; <?php echo htmlspecialchars($meta); ?></div>
                        <div style="color:#f59e0b;font-size:1rem;margin-top:0.25rem">
                            <?php for($i=0;$i<$stars;$i++) echo '★'; for($i=$stars;$i<5;$i++) echo '☆'; ?>
                        </div>
                    </div>
                    <span style="font-size:0.8rem;background:rgba(30,59,90,0.07);color:var(--primary);padding:0.25rem 0.75rem;border-radius:var(--radius-full);font-weight:500"><?php echo htmlspecialchars($prop); ?></span>
                </div>
                <p style="color:var(--text-secondary);line-height:1.75;margin:0">"<?php echo htmlspecialchars($text); ?>"</p>
                <div style="margin-top:0.75rem;font-size:0.8rem;color:var(--success);display:flex;align-items:center;gap:0.35rem">
                    <i class="fas fa-check-circle"></i> Verified Purchase
                </div>
            </div>
            <?php endforeach; ?>

            <div style="text-align:center;margin-top:2rem">
                <a href="<?php echo base_url('contact.php'); ?>" class="btn btn-primary btn-lg">
                    <i class="fas fa-star"></i> Share Your Review
                </a>
            </div>
        </div>
    </div>
</section>

<?php include 'partials/footer.php'; ?>
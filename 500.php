<?php
/**
 * iProply - 500 Server Error
 */
require_once 'config/config.php';

$currentPage = '';
$pageTitle = 'Server Error';
$metaTitle = '500 - Server Error | ' . APP_NAME;
$metaDescription = 'An unexpected error occurred. Please try again later.';

include 'partials/header.php';
?>

<section class="section" style="padding: 5rem 0;">
    <div class="container" style="max-width: 760px; text-align: center;">
        <div class="section-label" style="justify-content:center;">Error</div>
        <h1 style="font-size: clamp(2.5rem, 5vw, 4rem); margin: 0.75rem 0 0.5rem;">500</h1>
        <p style="color: var(--text-secondary); font-size: 1.05rem; margin-bottom: 1.5rem;">
            Something went wrong on our side. Please try again in a few minutes.
        </p>
        <div style="display:flex; gap:0.75rem; justify-content:center; flex-wrap:wrap;">
            <a class="btn btn-primary" href="<?php echo base_url(); ?>"><i class="fas fa-home" aria-hidden="true"></i> Home</a>
            <a class="btn btn-outline" href="<?php echo base_url('contact.php'); ?>"><i class="fas fa-envelope" aria-hidden="true"></i> Contact Us</a>
        </div>
    </div>
</section>

<?php include 'partials/footer.php'; ?>


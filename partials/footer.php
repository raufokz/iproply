<?php
/**
 * iProply - Footer Partial (Premium Luxury Design)
 * No App Section - Improved Typography
 */

if (!isset($siteSettings)) {
    $db = Database::getInstance();
    $siteSettings = $db->selectOne('site_settings', '*');
}

function format_phone_url($phone) {
    return preg_replace('/[^0-9]/', '', $phone);
}

$footerPages = [
    'join' => [],
    'about' => [],
    'resources' => [],
];

try {
    $pageModel = new Page();
    $footerPages['join'] = $pageModel->getFooterPages('join');
    $footerPages['about'] = $pageModel->getFooterPages('about');
    $footerPages['resources'] = $pageModel->getFooterPages('resources');
} catch (Exception $e) {
    // If the pages table hasn't been migrated yet, keep footer usable with static links below.
    $footerPages = [
        'join' => [],
        'about' => [],
        'resources' => [],
    ];
}

require_once __DIR__ . '/../includes/footer-routes.php';
?>
    </main>

    <!-- Site Footer (Premium Luxury Design) -->
    <div class="SiteFooter main-content">
        
        <!-- Footer Content -->
        <div class="footerContent fluid-gutter row">
            
            <!-- Column 1: Join Us -->
            <div class="flex flex-column col-12 col-md-4 col-lg-3 col-xl-2 linkSection links">
                <p class="linkHeading">Join Us</p>
                <?php if (!empty($footerPages['join'])): ?>
                    <?php foreach ($footerPages['join'] as $p): ?>
                        <div class="LinkItem">
                            <a href="<?php echo sanitize(footer_resolve_url($p['slug'])); ?>"><?php echo sanitize($p['title']); ?></a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="LinkItem"><a href="<?php echo sanitize(footer_resolve_url('become-agent')); ?>">Become an Agent</a></div>
                    <div class="LinkItem"><a href="<?php echo sanitize(footer_resolve_url('referral-network')); ?>">Referral Network</a></div>
                    <div class="LinkItem"><a href="<?php echo sanitize(footer_resolve_url('partners')); ?>">Partner With Us</a></div>
                    <div class="LinkItem"><a href="<?php echo sanitize(footer_resolve_url('careers')); ?>">Careers</a></div>
                <?php endif; ?>
                
                <!-- Optional: Newsletter Signup -->
                <div class="newsletter-inline" >
                    <p>Stay Updated</p>
                    <form class="newsletter-form-inline" action="<?php echo base_url('subscribe.php'); ?>" method="POST">
                        <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo generate_csrf_token(); ?>">
                        <input type="email" name="email" placeholder="Your email address" required>
                        <button type="submit">Join</button>
                    </form>
                </div>
            </div>
            
            <!-- Column 2: About Us -->
            <div class="flex flex-column col-12 col-md-2 col-lg-2 linkSection links">
                <p class="linkHeading">About</p>
                <?php if (!empty($footerPages['about'])): ?>
                    <?php foreach ($footerPages['about'] as $p): ?>
                        <div class="LinkItem">
                            <a href="<?php echo sanitize(footer_resolve_url($p['slug'])); ?>"><?php echo sanitize($p['title']); ?></a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="LinkItem"><a href="<?php echo sanitize(footer_resolve_url('why-iproply')); ?>">Why iProply?</a></div>
                    <div class="LinkItem"><a href="<?php echo sanitize(footer_resolve_url('our-story')); ?>">Our Story</a></div>
                    <div class="LinkItem"><a href="<?php echo sanitize(footer_resolve_url('community-impact')); ?>">Community Impact</a></div>
                    <div class="LinkItem"><a href="<?php echo sanitize(footer_resolve_url('inclusion')); ?>">Diversity & Inclusion</a></div>
                    <div class="LinkItem"><a href="<?php echo sanitize(footer_resolve_url('press')); ?>">Press & Media</a></div>
                <?php endif; ?>
                <div class="LinkItem"><a href="<?php echo base_url('blog.php'); ?>">Blog & Insights</a></div>
            </div>
            
            <!-- Column 3: Resources -->
            <div class="flex flex-column col-12 col-md-2 col-lg-2 linkSection links">
                <p class="linkHeading">Resources</p>
                <div class="LinkItem"><a href="<?php echo base_url('contact.php'); ?>">Contact Us</a></div>
                <div class="LinkItem"><a href="<?php echo base_url('reviews.php'); ?>">Reviews</a></div>
                <?php if (!empty($footerPages['resources'])): ?>
                    <?php foreach ($footerPages['resources'] as $p): ?>
                        <div class="LinkItem">
                            <a href="<?php echo sanitize(footer_resolve_url($p['slug'])); ?>"><?php echo sanitize($p['title']); ?></a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="LinkItem"><a href="<?php echo sanitize(footer_resolve_url('help-center')); ?>">Help Center</a></div>
                    <div class="LinkItem"><a href="<?php echo sanitize(footer_resolve_url('market-reports')); ?>">Market Reports</a></div>
                    <div class="LinkItem"><a href="<?php echo sanitize(footer_resolve_url('mortgage-calculator')); ?>">Mortgage Calculator</a></div>
                    <div class="LinkItem"><a href="<?php echo sanitize(footer_resolve_url('advertise')); ?>">Advertise</a></div>
                <?php endif; ?>
                
                <!-- Social Media -->
                <div class="socials">
                    <ul class="footerSocialButtons inlineList">
                        <li><a href="https://facebook.com/iproply" target="_blank" rel="noopener" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a></li>
                        <li><a href="https://twitter.com/iproply" target="_blank" rel="noopener" aria-label="Twitter"><i class="fab fa-twitter"></i></a></li>
                        <li><a href="https://instagram.com/iproply" target="_blank" rel="noopener" aria-label="Instagram"><i class="fab fa-instagram"></i></a></li>
                        <li><a href="https://linkedin.com/company/iproply" target="_blank" rel="noopener" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a></li>
                    </ul>
                </div>
            </div>
            
            <!-- Column 4: Legal & Info -->
            <div class="flex flex-column col-12 col-md-4 col-lg-5 col-xl-6">
                <div class="legal">
                    <p class="copyright">Copyright &copy; <?php echo date('Y'); ?> <?php echo sanitize($siteSettings['site_name'] ?? 'iProply'); ?>. All rights reserved.</p>
                    
                    <p class="tos-eula">Updated <?php echo date('F Y'); ?>: By using this site, you agree to our <a href="<?php echo sanitize(footer_resolve_url('terms-of-use')); ?>" target="_blank" rel="noopener">Terms of Use</a> and <a href="<?php echo sanitize(footer_resolve_url('privacy-policy')); ?>" target="_blank" rel="noopener">Privacy Policy</a>.</p>
                    
                    <p class="do-not-sell"><a href="<?php echo sanitize(footer_resolve_url('do-not-sell')); ?>" target="_blank" rel="noopener">Do Not Sell or Share My Personal Information</a>.</p>
                    
                    <p class="trademark">iPROPLY and all related marks are trademarks of iProply Corporation. All other marks are property of their respective owners.</p>
                    
                    <p class="ca-dre">California DRE #<?php echo sanitize($siteSettings['ca_dre'] ?? '01521930'); ?></p>
                    
                    <p class="trec">Texas: <a href="<?php echo sanitize(footer_resolve_url('texas-ibs')); ?>" target="_blank" rel="noopener">Info About Brokerage Services</a> | <a href="https://www.trec.texas.gov/forms/consumer-protection-notice" target="_blank" rel="noopener">Consumer Protection Notice</a></p>
                    
                    <p class="helpReading">Need assistance? Call us at <a class="phoneNumber" href="tel:<?php echo format_phone_url($siteSettings['site_phone'] ?? '1-844-759-7732'); ?>"><?php echo sanitize($siteSettings['site_phone'] ?? '1-844-IPROPLY'); ?></a>. We're here to help.</p>
                    
                    <p class="fairHousingPolicyBold">
                        <span class="eho">
                            <a href="<?php echo sanitize(footer_resolve_url('fair-housing')); ?>" target="_blank" rel="noopener">
                                <img class="ehoLogo" src="<?php echo asset_url('images/equal-housing.png'); ?>" alt="Equal Housing Opportunity" loading="lazy">
                            </a>
                        </span>
                        <span>iPROPLY IS COMMITTED TO THE FAIR HOUSING ACT AND EQUAL OPPORTUNITY. READ OUR <a href="<?php echo sanitize(footer_resolve_url('fair-housing-policy')); ?>">FAIR HOUSING POLICY</a>. NEW YORK RESIDENTS: SEE THE <a href="https://ag.ny.gov/civil-rights/fair-housing" target="_blank" rel="noopener">ATTORNEY GENERAL’S FAIR HOUSING INFORMATION</a>; OTHER STATES MAY PROVIDE SIMILAR NOTICES.</span>
                    </p>
                </div>
            </div>
            
        </div>
    </div>
    
    <!-- Back to Top -->
    <button class="back-to-top" id="backToTop" aria-label="Back to Top">
        <i class="fas fa-arrow-up"></i>
    </button>
    
    <!-- Scripts -->
    <script src="<?php echo asset_url('js/main.js'); ?>"></script>
    <?php if (isset($extraJs)): ?>
        <?php foreach ($extraJs as $js): ?>
            <script src="<?php echo asset_url('js/' . $js); ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <script>
    (function() {
        var backToTop = document.getElementById('backToTop');
        function toggle() {
            if (window.pageYOffset > 300) backToTop.classList.add('visible');
            else backToTop.classList.remove('visible');
        }
        window.addEventListener('scroll', toggle);
        backToTop.addEventListener('click', function() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    })();
    </script>
</body>
</html>

<?php
/**
 * iProply – Premium Real Estate Homepage (US Edition)
 * Uses original style.css CSS variables & design tokens.
 * Additions: Geolocation detection, Testimonials, Reviews/Ratings,
 *            Pricing Plans, How It Works, Trust Strip, About, CTA.
 */

require_once 'config/config.php';
require_once 'includes/Database.php';
require_once 'includes/Property.php';

$currentPage = 'home';
$pageTitle   = 'Find Your Dream Property';
$extraCss   = ['home.css'];

try {
    $db            = Database::getInstance();
    $propertyModel = new Property();
    $featuredProperties = $propertyModel->getFeatured(6) ?: [];
    $latestProperties   = $propertyModel->getLatest(6)   ?: [];
    $propertyTypes      = $propertyModel->getPropertyTypes() ?: [];
    $states             = $propertyModel->getStates()        ?: [];
} catch (Exception $e) {
    $featuredProperties = $latestProperties = $propertyTypes = $states = [];
}

include 'partials/header.php';
?>
<!-- ── Geo Banner ── -->
<div id="geo-banner">
    <i class="fas fa-map-marker-alt"></i>
    <span>Showing properties near <strong id="geo-city-lbl">your location</strong></span>
    <a href="#" onclick="clearGeo();return false;">Change</a>
    <button class="geo-close" onclick="document.getElementById('geo-banner').style.display='none'">×</button>
</div>

<!-- =====================================================
     HERO
     ===================================================== -->
<header class="hero" id="top">
    <div class="hero-bg">
        <img src="https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=2000&auto=format&fit=crop&q=80" alt="Luxury home">
    </div>
    <div class="hero-overlay"></div>

    <div class="container hero-container">

        <!-- Left content -->
        <div class="hero-content">
            <div class="hero-eyebrow">America's Premier Real Estate Platform</div>
            <h1 class="hero-title">
                Find Your <em>Dream Property</em><br>Near You
            </h1>
            <p class="hero-subtitle">
                Thousands of verified homes, condos, and commercial spaces — personalized
                to your location, budget, and lifestyle.
            </p>

            <div class="hero-actions">
                <a href="listings.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-search"></i> Browse Properties
                </a>
                <a href="contact.php" class="btn btn-outline btn-lg"
                   style="border-color:rgba(255,255,255,.6);color:#fff;background:rgba(255,255,255,.1)">
                    <i class="fas fa-phone"></i> Talk to an Agent
                </a>
            </div>

            <div class="hero-stats-row">
                <div>
                    <div class="hsr-num" data-counter="50000" data-suffix="+">0</div>
                    <div class="hsr-lbl">Active Listings</div>
                </div>
                <div>
                    <div class="hsr-num" data-counter="800" data-suffix="+">0</div>
                    <div class="hsr-lbl">Licensed Agents</div>
                </div>
                <div>
                    <div class="hsr-num" data-counter="98" data-suffix="%">0</div>
                    <div class="hsr-lbl">Satisfaction Rate</div>
                </div>
            </div>
        </div>

        <!-- Right search card -->
        <div class="hero-search-panel">
            <h3>Find Properties Near You</h3>

            <div id="location-indicator">
                <i class="fas fa-location-dot"></i>
                Near <strong id="loc-label">you</strong>
            </div>

            <form action="listings.php" method="GET" id="search-form">
                <input type="hidden" name="lat"   id="f-lat">
                <input type="hidden" name="lng"   id="f-lng">
                <input type="hidden" name="city"  id="f-city">
                <input type="hidden" name="state" id="f-state">

                <div class="hsp-field">
                    <label class="hsp-label"><i class="fas fa-map-marker-alt"></i> Location</label>
                    <input type="text" name="keyword" id="kw" class="hsp-input"
                           placeholder="City, address, ZIP, neighborhood"
                           value="<?php echo isset($_GET['keyword']) ? sanitize($_GET['keyword']) : ''; ?>">
                </div>
                <div class="hsp-row">
                    <div class="hsp-field">
                        <label class="hsp-label">Property Type</label>
                        <select name="type" class="hsp-input">
                            <option value="">All Types</option>
                            <?php foreach ((array)$propertyTypes as $t): ?>
                            <option value="<?php echo $t['id']??''; ?>"
                                <?php echo (isset($_GET['type'])&&$_GET['type']==($t['id']??''))?'selected':''; ?>>
                                <?php echo sanitize($t['name']??''); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="hsp-field">
                        <label class="hsp-label">Status</label>
                        <select name="status" class="hsp-input">
                            <option value="">Buy or Rent</option>
                            <option value="sale"  <?php echo (isset($_GET['status'])&&$_GET['status']==='sale') ?'selected':''; ?>>For Sale</option>
                            <option value="rent"  <?php echo (isset($_GET['status'])&&$_GET['status']==='rent') ?'selected':''; ?>>For Rent</option>
                        </select>
                    </div>
                </div>
                <div class="hsp-row">
                    <div class="hsp-field">
                        <label class="hsp-label">Min Price</label>
                        <input type="number" name="min_price" class="hsp-input" placeholder="$ Min"
                               value="<?php echo isset($_GET['min_price'])?(int)$_GET['min_price']:''; ?>">
                    </div>
                    <div class="hsp-field">
                        <label class="hsp-label">Max Price</label>
                        <input type="number" name="max_price" class="hsp-input" placeholder="$ Max"
                               value="<?php echo isset($_GET['max_price'])?(int)$_GET['max_price']:''; ?>">
                    </div>
                </div>
                <button type="submit" class="hsp-btn">
                    <i class="fas fa-search"></i> Search Properties
                </button>
            </form>

            <button class="hsp-geo-btn" id="geo-btn" onclick="detectLocation()">
                <i class="fas fa-location-crosshairs"></i> Use My Current Location
            </button>
        </div>
    </div>

    <div class="scroll-hint">
        <span>Scroll</span><div class="scroll-dot"></div>
    </div>
</header>

<!-- =====================================================
     PROPERTY TYPES
     ===================================================== -->
<section class="section property-types-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Browse by Property Type</h2>
            <p class="section-subtitle">Find exactly what fits your lifestyle</p>
        </div>
        <div class="property-types-grid">
            <?php
            $typeIcons=['House'=>'fa-home','Apartment'=>'fa-building','Condo'=>'fa-city',
                'Villa'=>'fa-landmark','Townhouse'=>'fa-house-user','Commercial'=>'fa-store',
                'Land'=>'fa-map','Office'=>'fa-briefcase','Warehouse'=>'fa-warehouse','Studio'=>'fa-door-open'];
            foreach (array_slice((array)$propertyTypes,0,6) as $type):
                $tn=$type['name']??''; $tid=$type['id']??''; $icon=$typeIcons[$tn]??'fa-home';
            ?>
            <a href="listings.php?type=<?php echo $tid; ?>" class="property-type-card reveal">
                <div class="type-icon"><i class="fas <?php echo $icon; ?>"></i></div>
                <div class="type-name"><?php echo sanitize($tn); ?></div>
            </a>
            <?php endforeach; ?>
            <?php if(empty($propertyTypes)):
                foreach([['fa-home','House'],['fa-building','Apartment'],['fa-city','Condo'],['fa-landmark','Villa'],['fa-store','Commercial'],['fa-map','Land']] as [$ic,$tn]):
            ?><a href="listings.php" class="property-type-card reveal"><div class="type-icon"><i class="fas <?php echo $ic;?>"></i></div><div class="type-name"><?php echo $tn;?></div></a><?php endforeach; endif; ?>
        </div>
    </div>
</section>

<!-- =====================================================
     TRUST MARQUEE
     ===================================================== -->
<div class="trust-strip">
    <div class="marquee-track">
        <?php
        $items=[['fa-shield-alt','Verified Listings'],['fa-award','Award-Winning Service'],
            ['fa-headset','24/7 Support'],['fa-lock','Secure Transactions'],
            ['fa-chart-line','Market Experts'],['fa-handshake','100,000+ Happy Clients'],
            ['fa-star','Top-Rated Platform'],['fa-certificate','Licensed Agents'],
            ['fa-shield-alt','Verified Listings'],['fa-award','Award-Winning Service'],
            ['fa-headset','24/7 Support'],['fa-lock','Secure Transactions'],
            ['fa-chart-line','Market Experts'],['fa-handshake','100,000+ Happy Clients'],
            ['fa-star','Top-Rated Platform'],['fa-certificate','Licensed Agents']];
        foreach($items as [$ic,$lb]):
        ?><div class="marquee-item"><i class="fas <?php echo $ic;?>"></i><?php echo $lb;?></div><?php endforeach; ?>
    </div>
</div>

<!-- =====================================================
     FEATURED PROPERTIES
     ===================================================== -->
<section class="section featured-properties-section">
    <div class="container">
        <div class="flex-header">
            <div>
                <div class="section-eyebrow"><i class="fas fa-star"></i> Featured</div>
                <h2 class="section-title">Featured Properties</h2>
                <div class="section-divider"></div>
                <p class="section-subtitle" id="feat-sub">Handpicked premium properties for discerning buyers</p>
            </div>
            <a href="listings.php?featured=1" class="btn-ghost-primary">View All <i class="fas fa-arrow-right"></i></a>
        </div>

        <div class="property-grid" id="featured-grid">
            <?php foreach ($featuredProperties as $p): ?>
            <div class="property-card reveal">
                <a href="property.php?slug=<?php echo $p['slug']; ?>" style="text-decoration:none;color:inherit">
                    <div class="property-image">
                        <?php if ($p['primary_image']): ?>
                            <img src="<?php echo UPLOAD_URL.'properties/'.$p['primary_image']; ?>" alt="<?php echo sanitize($p['title']); ?>">
                        <?php else: ?>
                            <img src="https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=600&h=400&fit=crop" alt="Property">
                        <?php endif; ?>
                        <span class="property-badge badge-<?php echo $p['status']; ?>">For <?php echo ucfirst($p['status']); ?></span>
                        <?php if($p['is_featured']): ?>
                        <span class="property-badge badge-featured" style="left:auto;right:15px">Featured</span>
                        <?php endif; ?>
                        <div class="property-price"><?php echo format_price($p['price'],$p['status']); ?></div>
                        <div class="nearby-badge" data-city="<?php echo htmlspecialchars($p['city']??''); ?>">
                            <i class="fas fa-location-dot"></i> Nearby
                        </div>
                    </div>
                    <div class="property-content">
                        <h3 class="property-title"><?php echo sanitize($p['title']); ?></h3>
                        <div class="property-location"><i class="fas fa-map-marker-alt"></i><?php echo sanitize($p['city']).', '.sanitize($p['state']); ?></div>
                        <div class="property-features">
                            <?php if($p['bedrooms']>0): ?><div class="property-feature"><i class="fas fa-bed"></i><span><?php echo $p['bedrooms']; ?> Beds</span></div><?php endif; ?>
                            <?php if($p['bathrooms']>0): ?><div class="property-feature"><i class="fas fa-bath"></i><span><?php echo $p['bathrooms']; ?> Baths</span></div><?php endif; ?>
                            <?php if($p['area_sqft']>0): ?><div class="property-feature"><i class="fas fa-ruler-combined"></i><span><?php echo number_format($p['area_sqft']); ?> sqft</span></div><?php endif; ?>
                        </div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
            <?php if(empty($featuredProperties)): for($i=1;$i<=6;$i++): ?>
            <div class="property-card reveal">
                <div class="property-image">
                    <img src="https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=600&h=400&fit=crop&sig=<?php echo $i; ?>" alt="Property">
                    <span class="property-badge badge-sale">For Sale</span>
                    <div class="property-price">$<?php echo number_format(rand(350,950)*1000); ?></div>
                </div>
                <div class="property-content">
                    <h3 class="property-title">Premium Residence #<?php echo $i; ?></h3>
                    <div class="property-location"><i class="fas fa-map-marker-alt"></i>Beverly Hills, CA</div>
                    <div class="property-features">
                        <div class="property-feature"><i class="fas fa-bed"></i><span><?php echo rand(3,6); ?> Beds</span></div>
                        <div class="property-feature"><i class="fas fa-bath"></i><span><?php echo rand(2,4); ?> Baths</span></div>
                        <div class="property-feature"><i class="fas fa-ruler-combined"></i><span><?php echo number_format(rand(2500,7500)); ?> sqft</span></div>
                    </div>
                </div>
            </div>
            <?php endfor; endif; ?>
        </div>
        <div class="text-center mt-2xl">
            <a href="listings.php" class="btn btn-primary btn-lg">View All Properties <i class="fas fa-arrow-right"></i></a>
        </div>
    </div>
</section>

<!-- =====================================================
     STATS BAND
     ===================================================== -->
<div class="stats-band">
    <div class="container" style="padding:0 2rem">
        <div class="stats-band-grid">
            <div class="sband-item reveal"><div class="sband-icon"><i class="fas fa-building"></i></div><div class="sband-num" data-counter="50000" data-suffix="+">0</div><div class="sband-lbl">Active Listings</div></div>
            <div class="sband-item reveal reveal-d1"><div class="sband-icon"><i class="fas fa-user-tie"></i></div><div class="sband-num" data-counter="800" data-suffix="+">0</div><div class="sband-lbl">Licensed Agents</div></div>
            <div class="sband-item reveal reveal-d2"><div class="sband-icon"><i class="fas fa-handshake"></i></div><div class="sband-num" data-counter="42000" data-suffix="+">0</div><div class="sband-lbl">Deals Closed</div></div>
            <div class="sband-item reveal reveal-d3"><div class="sband-icon"><i class="fas fa-smile"></i></div><div class="sband-num" data-counter="98" data-suffix="%">0</div><div class="sband-lbl">Client Satisfaction</div></div>
        </div>
    </div>
</div>

<!-- =====================================================
     HOW IT WORKS
     ===================================================== -->
<section class="section section-bg">
    <div class="container">
        <div class="section-header sec-center" style="text-align:center">
            <div class="section-eyebrow" style="justify-content:center">Process</div>
            <h2 class="section-title">Three Simple Steps<br>to Your New Home</h2>
            <div class="section-divider" style="margin:1rem auto"></div>
            <p class="section-subtitle">We remove every friction point from the property journey.</p>
        </div>
        <div class="steps-wrap">
            <div class="step-card-enh reveal">
                <div class="step-num-circle">1</div>
                <h3>Search &amp; Filter</h3>
                <p>Use our location-aware search to discover properties near you. Filter by price, type, beds, school district, commute time, and walk score.</p>
            </div>
            <div class="step-card-enh reveal reveal-d1">
                <div class="step-num-circle">2</div>
                <h3>Tour &amp; Verify</h3>
                <p>Book in-person or virtual tours with licensed agents. Every listing is independently verified before it goes live — no surprises.</p>
            </div>
            <div class="step-card-enh reveal reveal-d2">
                <div class="step-num-circle">3</div>
                <h3>Close &amp; Move In</h3>
                <p>Our legal and escrow partners guide you through every document. We celebrate when you get the keys — and support you after.</p>
            </div>
        </div>
    </div>
</section>

<!-- =====================================================
     ABOUT
     ===================================================== -->
<section class="section">
    <div class="container">
        <div class="about-grid-enh">
            <div class="about-img-wrap reveal">
                <img class="about-img-main" src="https://images.unsplash.com/photo-1600880292203-757bb62b4baf?w=800&h=600&fit=crop" alt="About iProply">
                <img class="about-img-accent" src="https://images.unsplash.com/photo-1560518883-ce09059eeffa?w=400&h=400&fit=crop" alt="Team">
                <div class="about-frame-deco"></div>
            </div>
            <div class="reveal reveal-d1">
                <span class="section-label">About Us</span>
                <h2 class="about-content h2" style="font-family:var(--font-family);font-size:var(--font-size-3xl);font-weight:700;color:var(--text-primary);margin-bottom:var(--spacing-lg)">America's Most Trusted Property Platform</h2>
                <div class="section-divider"></div>
                <p style="color:var(--text-secondary);line-height:1.75;margin-bottom:1rem">
                    With over 15 years of experience, we've reimagined how Americans buy, sell, and rent property. Our 800+ licensed agents span every major market from coast to coast.
                </p>
                <ul class="about-check">
                    <li><i class="fas fa-check-circle"></i>Every listing independently verified before publication</li>
                    <li><i class="fas fa-check-circle"></i>AI-powered recommendations tailored to your location &amp; lifestyle</li>
                    <li><i class="fas fa-check-circle"></i>End-to-end support including escrow and title services</li>
                    <li><i class="fas fa-check-circle"></i>School district data, walk scores &amp; neighborhood insights</li>
                    <li><i class="fas fa-check-circle"></i>Post-sale concierge, utility hookups &amp; move-in assistance</li>
                </ul>
                <div class="about-mini-grid">
                    <div class="amg"><div class="amg-n">15+</div><div class="amg-l">Years Active</div></div>
                    <div class="amg"><div class="amg-n">42K+</div><div class="amg-l">Homes Sold</div></div>
                    <div class="amg"><div class="amg-n">98%</div><div class="amg-l">Satisfaction</div></div>
                </div>
                <div style="display:flex;gap:1rem;flex-wrap:wrap;margin-top:2rem">
                    <a href="about.php" class="btn btn-primary">Our Story <i class="fas fa-arrow-right"></i></a>
                    <a href="agents.php" class="btn btn-outline" style="border-color:var(--primary);color:var(--primary)">Meet the Team</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- =====================================================
     TESTIMONIALS
     ===================================================== -->
<section class="section section-bg" id="testimonials">
    <div class="container">
        <div class="section-header" style="text-align:center;margin-bottom:0">
            <div class="section-eyebrow" style="justify-content:center">Testimonials</div>
            <h2 class="section-title">What Our Clients Are Saying</h2>
            <div class="section-divider" style="margin:1rem auto"></div>
            <p class="section-subtitle">Real stories from buyers, sellers and renters across America.</p>
        </div>

        <!-- Featured -->
        <div class="testi-featured reveal">
            <p class="testi-featured-text">
                "iProply found us our perfect home in Austin in under two weeks. The location search surfaced properties we never would have discovered on our own. Our agent Sarah was phenomenal — patient, knowledgeable, and genuinely invested in our outcome."
            </p>
            <div class="testi-author-row">
                <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=200&h=200&fit=crop&crop=face" class="tfa-img" alt="James Carter">
                <div>
                    <div class="tfa-name">James Carter</div>
                    <div class="tfa-role">Entrepreneur · Austin, TX</div>
                    <div class="tfa-stars">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                        <i class="fas fa-star"></i><i class="fas fa-star"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="testi-grid-3">
            <?php
            $testimons=[
                ['Emily Rhodes','Interior Designer · Seattle, WA','ER','The listing photos actually matched the real property. After bad experiences elsewhere, that mattered enormously. Rented my studio in 4 days.',5,'Rented'],
                ['Marcus Webb','Software Engineer · San Jose, CA','MW','The location filter showed me condos within walking distance of my office. Bought a 2-bed in SoMa — transparent process, fast close.',5,'Purchased'],
                ['Diane Fortuna','Retired Teacher · Naples, FL','DF','Nervous about selling my home of 22 years. My iProply agent guided every step. Sold above asking in 11 days. Couldn\'t be happier.',5,'Sold'],
                ['Robert Kim','Banker · New York, NY','RK','The neighborhood data — schools, crime, commute times — is genuinely unmatched. Made my decision so much easier. Highly recommend the Pro plan.',4,'Purchased'],
                ['Priya Nair','Doctor · Chicago, IL','PN','Listed my condo and had 8 serious inquiries in 48 hours. The featured placement on the Pro plan absolutely delivers. Sold in 3 weeks.',5,'Sold'],
                ['Tom Hartley','Architect · Denver, CO','TH','As an architect I\'m exacting about specs. Verified square footage and accurate floor plans saved me hours of due diligence.',5,'Rented'],
            ];
            foreach($testimons as [$name,$role,$init,$text,$stars,$tag]):
            ?>
            <div class="tcard reveal">
                <div class="tcard-stars">
                    <?php for($s=0;$s<5;$s++): ?><i class="fas fa-star<?php echo $s<$stars?'':'-o'; ?>"></i><?php endfor; ?>
                </div>
                <p class="tcard-text">"<?php echo htmlspecialchars($text); ?>"</p>
                <div class="tcard-author">
                    <div class="tcard-initials"><?php echo $init; ?></div>
                    <div><div class="tcard-name"><?php echo $name; ?></div><div class="tcard-role"><?php echo $role; ?></div></div>
                </div>
                <span class="tcard-tag"><?php echo $tag; ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- =====================================================
     REVIEWS & RATINGS
     ===================================================== -->
<section class="section" id="reviews">
    <div class="container">
        <div style="margin-bottom:3rem">
            <div class="section-eyebrow">Reviews</div>
            <h2 class="section-title">Verified Client Reviews</h2>
            <div class="section-divider"></div>
            <p class="section-subtitle">Honest feedback from buyers, sellers and renters nationwide.</p>
        </div>

        <div class="reviews-layout">
            <!-- Summary card -->
            <div class="reviews-summary reveal">
                <div class="rscore">4.9<span class="rscore-denom">/5</span></div>
                <div class="rstar-row">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                    <i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i>
                </div>
                <div class="rtotal">Based on 12,847 verified reviews</div>
                <?php foreach([[5,91],[4,6],[3,2],[2,1],[1,0]] as [$s,$p]): ?>
                <div class="rbar-row">
                    <span class="rbar-lbl"><?php echo $s; ?></span>
                    <div class="rbar-bg"><div class="rbar-fill" style="width:<?php echo $p; ?>%"></div></div>
                    <span class="rbar-pct"><?php echo $p; ?>%</span>
                </div>
                <?php endforeach; ?>
                <div style="margin-top:1.5rem">
                    <?php foreach([['Agent Expertise','4.9'],['Listing Accuracy','4.8'],['Communication','5.0'],['Value for Money','4.7'],['Speed of Service','4.9']] as [$cat,$score]): ?>
                    <div class="rcat-row"><span><?php echo $cat; ?></span><span><?php echo $score; ?> ★</span></div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Review list -->
            <div>
                <?php
                $reviews=[
                    ['Brian Gallagher','New York, NY · Purchased Dec 2024','Exceptional from first call to closing. My agent knew the Manhattan market inside out and helped us secure a 2-bed at below asking. The legal team handled all paperwork flawlessly. This is how real estate should work.',5,'Upper West Side, NYC'],
                    ['Jessica Tran','Los Angeles, CA · Rented Nov 2024','Found my apartment in Silver Lake in 5 days. Accurate photos, responsive agent, entirely digital application. If I ever move again, iProply is my first call.',5,'Silver Lake, Los Angeles'],
                    ['Kevin O\'Brien','Chicago, IL · Sold Oct 2024','Listed my Lincoln Park home and had a full-price offer in 9 days. The professional listing package — staging advice, pro photography, featured placement — is worth every penny.',5,'Lincoln Park, Chicago'],
                    ['Angela Morris','Miami, FL · Purchased Sep 2024','The neighborhood scoring and school district data helped us narrow 200 listings to 12 worth visiting. Our agent walked us through each one honestly, including the downsides. Four stars because the mobile app needs polish.',4,'Coral Gables, Miami'],
                ];
                foreach($reviews as [$name,$meta,$text,$stars,$prop]):
                ?>
                <div class="rcard reveal">
                    <div class="rcard-top">
                        <div>
                            <div class="rcard-name"><?php echo $name; ?></div>
                            <div class="rcard-meta"><?php echo $meta; ?></div>
                            <div class="rcard-stars">
                                <?php for($s=0;$s<5;$s++): ?><i class="fas fa-star<?php echo $s<$stars?'':'-o'; ?>"></i><?php endfor; ?>
                            </div>
                        </div>
                        <div class="rcard-prop"><?php echo $prop; ?></div>
                    </div>
                    <p class="rcard-text"><?php echo htmlspecialchars($text); ?></p>
                    <div class="rcard-verified"><i class="fas fa-check-circle"></i> Verified Purchase</div>
                </div>
                <?php endforeach; ?>
                <div style="text-align:center;margin-top:1.5rem">
                    <a href="reviews.php" class="btn-ghost-primary">Read All 12,847 Reviews <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- =====================================================
     PRICING
     ===================================================== -->
<section class="section section-bg" id="pricing">
    <div class="container">
        <div class="section-header" style="text-align:center;margin-bottom:0">
            <div class="section-eyebrow" style="justify-content:center">Pricing</div>
            <h2 class="section-title">Simple, Transparent Pricing</h2>
            <div class="section-divider" style="margin:1rem auto"></div>
            <p class="section-subtitle">Whether you're buying, renting, or listing — pick the plan that fits.</p>
        </div>

        <div class="billing-toggle-row">
            <span class="btl on" id="m-lbl">Monthly</span>
            <button class="tpill" id="btog" onclick="toggleBilling()"><div class="tknob"></div></button>
            <span class="btl" id="a-lbl">Annual <span class="save-tag">Save 20%</span></span>
        </div>

        <div class="plans-grid">
            <!-- Starter -->
            <div class="plan reveal">
                <div class="plan-icon-wrap"><i class="fas fa-seedling"></i></div>
                <div class="plan-name">Starter</div>
                <div class="plan-desc">For buyers and renters who want to browse and save listings.</div>
                <div class="plan-price-wrap">
                    <div class="plan-price-n monthly-p"><span class="cur">$</span>0</div>
                    <div class="plan-price-n annual-p" style="display:none"><span class="cur">$</span>0</div>
                    <div class="plan-per">Free forever</div>
                </div>
                <ul class="plan-feats">
                    <li><i class="fas fa-check"></i> Browse all listings</li>
                    <li><i class="fas fa-check"></i> Save up to 10 favorites</li>
                    <li><i class="fas fa-check"></i> Location-aware search</li>
                    <li><i class="fas fa-check"></i> New listing email alerts</li>
                    <li class="na"><i class="fas fa-times"></i> Featured placement</li>
                    <li class="na"><i class="fas fa-times"></i> Analytics dashboard</li>
                    <li class="na"><i class="fas fa-times"></i> Dedicated agent</li>
                </ul>
                <a href="register.php" class="plan-btn plan-btn-out">Get Started Free</a>
            </div>

            <!-- Professional -->
            <div class="plan pop reveal reveal-d1">
                <div class="pop-badge">Most Popular</div>
                <div class="plan-icon-wrap"><i class="fas fa-gem"></i></div>
                <div class="plan-name">Professional</div>
                <div class="plan-desc">For active sellers and agents who need premium visibility and analytics.</div>
                <div class="plan-price-wrap">
                    <div class="plan-price-n monthly-p"><span class="cur">$</span>49</div>
                    <div class="plan-price-n annual-p" style="display:none"><span class="cur">$</span>39</div>
                    <div class="plan-per">per month</div>
                    <div class="plan-annual-line">Billed $468/year — save $120</div>
                </div>
                <ul class="plan-feats">
                    <li><i class="fas fa-check"></i> Unlimited listings</li>
                    <li><i class="fas fa-check"></i> Featured search placement</li>
                    <li><i class="fas fa-check"></i> Analytics &amp; performance dashboard</li>
                    <li><i class="fas fa-check"></i> Priority phone &amp; email support</li>
                    <li><i class="fas fa-check"></i> Verified agent badge</li>
                    <li><i class="fas fa-check"></i> Professional photography guide</li>
                    <li class="na"><i class="fas fa-times"></i> Dedicated account manager</li>
                </ul>
                <a href="register.php?plan=pro" class="plan-btn plan-btn-pri">
                    Start 14-Day Free Trial <i class="fas fa-arrow-right"></i>
                </a>
            </div>

            <!-- Enterprise -->
            <div class="plan reveal reveal-d2">
                <div class="plan-icon-wrap"><i class="fas fa-building"></i></div>
                <div class="plan-name">Enterprise</div>
                <div class="plan-desc">For agencies &amp; developers who need maximum exposure and white-glove support.</div>
                <div class="plan-price-wrap">
                    <div class="plan-price-n monthly-p"><span class="cur">$</span>149</div>
                    <div class="plan-price-n annual-p" style="display:none"><span class="cur">$</span>119</div>
                    <div class="plan-per">per month</div>
                    <div class="plan-annual-line">Billed $1,428/year — save $360</div>
                </div>
                <ul class="plan-feats">
                    <li><i class="fas fa-check"></i> Unlimited listings</li>
                    <li><i class="fas fa-check"></i> Homepage &amp; top-of-search placement</li>
                    <li><i class="fas fa-check"></i> Full analytics suite</li>
                    <li><i class="fas fa-check"></i> Dedicated account manager</li>
                    <li><i class="fas fa-check"></i> Custom agency branding</li>
                    <li><i class="fas fa-check"></i> API &amp; CRM integration</li>
                    <li><i class="fas fa-check"></i> Legal &amp; escrow document support</li>
                </ul>
                <a href="contact.php?plan=enterprise" class="plan-btn plan-btn-out">Contact Sales</a>
            </div>
        </div>

        <p class="mbg-note"><i class="fas fa-shield-alt"></i> All paid plans include a <strong>30-day money-back guarantee</strong> — no questions asked.</p>
    </div>
</section>

<!-- =====================================================
     LATEST LISTINGS
     ===================================================== -->
<section class="section latest-properties-section">
    <div class="container">
        <div class="flex-header">
            <div>
                <div class="section-eyebrow"><i class="fas fa-clock"></i> Just Listed</div>
                <h2 class="section-title">Latest Listings</h2>
                <div class="section-divider"></div>
                <p class="section-subtitle" id="latest-sub">Newest properties just added to our platform</p>
            </div>
            <a href="listings.php" class="btn-ghost-primary">All Listings <i class="fas fa-arrow-right"></i></a>
        </div>

        <div class="property-grid" id="latest-grid">
            <?php foreach ($latestProperties as $p): ?>
            <div class="property-card reveal">
                <a href="property.php?slug=<?php echo $p['slug']; ?>" style="text-decoration:none;color:inherit">
                    <div class="property-image">
                        <?php if($p['primary_image']): ?><img src="<?php echo UPLOAD_URL.'properties/'.$p['primary_image']; ?>" alt="<?php echo sanitize($p['title']); ?>">
                        <?php else: ?><img src="https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=600&h=400&fit=crop" alt="Property"><?php endif; ?>
                        <span class="property-badge badge-<?php echo $p['status']; ?>">For <?php echo ucfirst($p['status']); ?></span>
                        <div class="property-price"><?php echo format_price($p['price'],$p['status']); ?></div>
                        <div class="nearby-badge" data-city="<?php echo htmlspecialchars($p['city']??''); ?>">
                            <i class="fas fa-location-dot"></i> Nearby
                        </div>
                    </div>
                    <div class="property-content">
                        <h3 class="property-title"><?php echo sanitize($p['title']); ?></h3>
                        <div class="property-location"><i class="fas fa-map-marker-alt"></i><?php echo sanitize($p['city']).', '.sanitize($p['state']); ?></div>
                        <div class="property-features">
                            <?php if($p['bedrooms']>0): ?><div class="property-feature"><i class="fas fa-bed"></i><span><?php echo $p['bedrooms']; ?> Beds</span></div><?php endif; ?>
                            <?php if($p['bathrooms']>0): ?><div class="property-feature"><i class="fas fa-bath"></i><span><?php echo $p['bathrooms']; ?> Baths</span></div><?php endif; ?>
                            <?php if($p['area_sqft']>0): ?><div class="property-feature"><i class="fas fa-ruler-combined"></i><span><?php echo number_format($p['area_sqft']); ?> sqft</span></div><?php endif; ?>
                        </div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
            <?php if(empty($latestProperties)): for($i=1;$i<=6;$i++): ?>
            <div class="property-card reveal">
                <div class="property-image">
                    <img src="https://images.unsplash.com/photo-1580587771525-78b9dba3b914?w=600&h=400&fit=crop&sig=<?php echo $i+10; ?>" alt="Property">
                    <span class="property-badge badge-rent">For Rent</span>
                    <div class="property-price">$<?php echo number_format(rand(2500,8500)); ?>/mo</div>
                </div>
                <div class="property-content">
                    <h3 class="property-title">Modern Apartment #<?php echo $i; ?></h3>
                    <div class="property-location"><i class="fas fa-map-marker-alt"></i>Brooklyn, NY</div>
                    <div class="property-features">
                        <div class="property-feature"><i class="fas fa-bed"></i><span><?php echo rand(1,3); ?> Beds</span></div>
                        <div class="property-feature"><i class="fas fa-bath"></i><span><?php echo rand(1,2); ?> Baths</span></div>
                        <div class="property-feature"><i class="fas fa-ruler-combined"></i><span><?php echo number_format(rand(700,2200)); ?> sqft</span></div>
                    </div>
                </div>
            </div>
            <?php endfor; endif; ?>
        </div>
    </div>
</section>

<!-- =====================================================
     CTA
     ===================================================== -->
<section class="section">
    <div class="container">
        <div class="cta-banner reveal">
            <div>
                <h2>Ready to Find Your Dream Home?</h2>
                <p>Join over 100,000 satisfied clients across America. Our licensed agents are ready to help you find the perfect property — personalized to your exact location and budget.</p>
            </div>
            <div class="cta-btns">
                <a href="listings.php" class="btn-cta-w"><i class="fas fa-search"></i> Browse Properties</a>
                <a href="contact.php"  class="btn-cta-g"><i class="fas fa-phone"></i> Contact an Agent</a>
            </div>
        </div>
    </div>
</section>



<!-- =====================================================
     JAVASCRIPT
     ===================================================== -->
<script>
/* ── Geolocation ─────────────────────────── */
function detectLocation() {
    const btn = document.getElementById('geo-btn');
    if (!navigator.geolocation) { alert('Geolocation is not supported by your browser.'); return; }
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Detecting…';
    btn.disabled = true;
    navigator.geolocation.getCurrentPosition(
        pos => reverseGeocode(pos.coords.latitude, pos.coords.longitude),
        err => { btn.innerHTML = '<i class="fas fa-location-crosshairs"></i> Use My Current Location'; btn.disabled = false; },
        { timeout: 8000 }
    );
}

function reverseGeocode(lat, lng) {
    fetch(`https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=json`)
        .then(r => r.json())
        .then(d => {
            const a = d.address || {};
            applyGeo(a.city || a.town || a.village || a.county || '', a.state || '', lat, lng);
        })
        .catch(() => applyGeo('', '', lat, lng));
}

function applyGeo(city, state, lat, lng) {
    // Fill hidden form fields
    ['f-lat','f-lng','f-city','f-state'].forEach((id,i) => {
        const v = [lat, lng, city, state][i];
        const el = document.getElementById(id);
        if (el) el.value = v;
    });
    // Pre-fill keyword
    const kw = document.getElementById('kw');
    if (kw && !kw.value && city) kw.value = city + (state ? ', ' + state : '');
    // Inline indicator
    if (city) {
        document.getElementById('loc-label').textContent = city + (state ? ', ' + state : '');
        document.getElementById('location-indicator').style.display = 'flex';
    }
    // Top banner
    if (city) {
        document.getElementById('geo-city-lbl').textContent = city + (state ? ', ' + state : '');
        document.getElementById('geo-banner').style.display = 'flex';
    }
    // Section subtitles
    const sub = city ? `Properties near ${city}${state ? ', ' + state : ''}` : '';
    if (sub) {
        ['feat-sub','latest-sub'].forEach(id => { const el=document.getElementById(id); if(el) el.textContent=sub; });
    }
    // "Nearby" badges
    if (city) {
        const norm = city.toLowerCase();
        document.querySelectorAll('.nearby-badge').forEach(b => {
            if ((b.dataset.city||'').toLowerCase().includes(norm)) b.classList.add('show');
        });
    }
    // Restore button
    const btn = document.getElementById('geo-btn');
    btn.innerHTML = `<i class="fas fa-check" style="color:var(--success)"></i> Location set: ${city||'detected'}`;
    btn.disabled = false;
    // Persist
    try { sessionStorage.setItem('_geo', JSON.stringify({city,state,lat,lng})); } catch(e){}
}

function clearGeo() {
    ['f-lat','f-lng','f-city','f-state'].forEach(id=>{const el=document.getElementById(id);if(el)el.value='';});
    const kw=document.getElementById('kw'); if(kw) kw.value='';
    document.getElementById('location-indicator').style.display='none';
    document.getElementById('geo-banner').style.display='none';
    document.querySelectorAll('.nearby-badge').forEach(b=>b.classList.remove('show'));
    try{sessionStorage.removeItem('_geo');}catch(e){}
}

// Restore on load
(function(){
    try {
        const g = JSON.parse(sessionStorage.getItem('_geo')||'null');
        if (g && g.lat) applyGeo(g.city||'', g.state||'', g.lat, g.lng);
    } catch(e){}
})();

/* ── Counter ─────────────────────────────── */
function runCounter(el) {
    if (el._ran) return; el._ran = true;
    const target = +el.dataset.counter, suffix = el.dataset.suffix || '', dur = 2000;
    const step = target / (dur / 16); let cur = 0;
    const t = setInterval(() => {
        cur = Math.min(cur + step, target);
        el.textContent = Math.floor(cur).toLocaleString() + suffix;
        if (cur >= target) clearInterval(t);
    }, 16);
}

/* ── Scroll Reveal ───────────────────────── */
const revObs = new IntersectionObserver(entries => {
    entries.forEach(e => {
        if (!e.isIntersecting) return;
        e.target.classList.add('in');
        e.target.querySelectorAll('[data-counter]').forEach(runCounter);
        revObs.unobserve(e.target);
    });
}, { threshold: 0.12 });
document.querySelectorAll('.reveal').forEach(el => revObs.observe(el));

// Hero + stats band counters (on load)
window.addEventListener('load', () => {
    setTimeout(() => {
        document.querySelectorAll('.hsr-num[data-counter], .sband-num[data-counter]').forEach(runCounter);
    }, 350);
});

/* ── Pricing Toggle ──────────────────────── */
let isAnnual = false;
function toggleBilling() {
    isAnnual = !isAnnual;
    document.getElementById('btog').classList.toggle('on', isAnnual);
    document.getElementById('m-lbl').classList.toggle('on', !isAnnual);
    document.getElementById('a-lbl').classList.toggle('on', isAnnual);
    document.querySelectorAll('.monthly-p').forEach(el => el.style.display = isAnnual ? 'none' : 'block');
    document.querySelectorAll('.annual-p').forEach(el  => el.style.display = isAnnual ? 'block' : 'none');
    document.querySelectorAll('.plan-annual-line').forEach(el => el.style.display = isAnnual ? 'block' : 'none');
}
</script>

<?php include 'partials/footer.php'; ?>

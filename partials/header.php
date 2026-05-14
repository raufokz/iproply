<?php
/**
 * iProply — Header Partial (2025 Design)
 * Enhanced UI/UX with modern luxury real estate aesthetics
 */

$db            = Database::getInstance();
$siteSettings  = $db->selectOne('site_settings', '*');
$propertyModel = new Property();
$propertyTypes = $propertyModel->getPropertyTypes();

// Avoid notices on pages that don't set these explicitly (e.g., CMS pages, error pages).
$currentPage = $currentPage ?? '';
$currentScript = basename((string) parse_url($_SERVER['SCRIPT_NAME'] ?? '', PHP_URL_PATH));
$agentsNavActive = in_array($currentPage, ['agents', 'referrals', 'become-agent', 'reviews'], true)
    || in_array($currentScript, ['agents.php', 'referral-network.php', 'become-agent.php', 'reviews.php'], true);
$companyNavActive = in_array($currentPage, ['about'], true)
    || in_array($currentScript, ['about.php', 'why-iproply.php', 'our-story.php', 'community-impact.php', 'inclusion.php', 'press.php', 'careers.php', 'partners.php'], true);
$resourcesNavActive = in_array($currentPage, ['blog', 'mortgage-calculator'], true)
    || in_array($currentScript, ['blog.php', 'blog-post.php', 'market-reports.php', 'mortgage-calculator.php', 'help-center.php', 'advertise.php'], true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo sanitize($metaDescription ?? ($siteSettings['meta_description'] ?? (APP_NAME . ' - Find Your Dream Home'))); ?>">
    <meta name="keywords"    content="<?php echo sanitize($metaKeywords ?? ($siteSettings['meta_keywords'] ?? 'real estate, property, homes, apartments')); ?>">
    <title>
        <?php if (!empty($metaTitle)): ?>
            <?php echo sanitize($metaTitle); ?>
        <?php else: ?>
            <?php echo isset($pageTitle) ? sanitize($pageTitle) . ' — ' : ''; ?><?php echo sanitize($siteSettings['site_name'] ?? APP_NAME); ?>
        <?php endif; ?>
    </title>

    <link rel="icon" type="image/x-icon" href="<?php echo asset_url('../assets/favicon.ico'); ?>">

    <!-- Fonts: Outfit (Headings) & Inter (Body) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <?php
        $stylePath = BASE_PATH . '/assets/css/style.css';
        $styleVersion = is_file($stylePath) ? (string) filemtime($stylePath) : (string) time();
    ?>
    <!-- Site stylesheet -->
    <link rel="stylesheet" href="<?php echo asset_url('css/style.css') . '?v=' . $styleVersion; ?>">

    <?php if (isset($extraCss)): foreach ($extraCss as $css): ?>
        <?php
            $extraCssPath = BASE_PATH . '/assets/css/' . ltrim($css, '/');
            $extraCssVersion = is_file($extraCssPath) ? (string) filemtime($extraCssPath) : $styleVersion;
        ?>
        <link rel="stylesheet" href="<?php echo asset_url('css/' . $css) . '?v=' . $extraCssVersion; ?>">
    <?php endforeach; endif; ?>
</head>
<body>

<!-- ==========================================================
     SEARCH OVERLAY
     ========================================================== -->
<div class="search-overlay" id="searchOverlay" role="dialog" aria-modal="true" aria-label="Site search" hidden>
    <button class="search-overlay-close" id="searchClose" aria-label="Close search">
        <i class="fas fa-times" aria-hidden="true"></i>
    </button>
    <div class="search-overlay-content">
        <form action="<?php echo base_url('listings.php'); ?>" method="GET">
            <input 
                type="text" 
                name="keyword" 
                class="search-overlay-input" 
                placeholder="Search properties, locations, or keywords..." 
                aria-label="Search properties"
                autocomplete="off"
                value="<?php echo sanitize($_GET['keyword'] ?? ''); ?>"
            >
        </form>
    </div>
</div>

<!-- ==========================================================
     NAVBAR
     ========================================================== -->
<nav class="navbar at-top" id="navbar" role="navigation" aria-label="Main navigation">
    <div class="navbar-inner">

        <!-- ── DUAL LOGO ────────────────────────────────────── -->
        <a href="<?php echo base_url(); ?>"
           class="logo"
           aria-label="<?php echo sanitize($siteSettings['site_name'] ?? APP_NAME); ?> – homepage">

            <?php $siteName = sanitize($siteSettings['site_name'] ?? APP_NAME); ?>

            <!-- Light logo: over dark/transparent hero nav -->
            <img
                src="<?php echo asset_url('images/iproply-logo-light.png'); ?>"
                alt="<?php echo $siteName; ?>"
                class="logo-image logo-image--light"
                width="170" height="100"
                loading="eager" decoding="async"
            >

            <!-- Dark logo: on scrolled cream/white nav -->
            <img
                src="<?php echo asset_url('images/iproply-logo-dark.png'); ?>"
                alt=""
                class="logo-image logo-image--dark"
                width="170" height="42"
                loading="eager" decoding="async"
                aria-hidden="true"
            >
        </a>

        <!-- ── DESKTOP NAV ───────────────────────────────────── -->
        <div class="nav-menu" id="navMenu">
            <ul class="nav-list" role="menubar">

                <li class="nav-item" role="none">
                    <a href="<?php echo base_url(); ?>"
                       class="nav-link <?php echo $currentPage === 'home' ? 'active' : ''; ?>"
                       role="menuitem"
                       <?php echo $currentPage === 'home' ? 'aria-current="page"' : ''; ?>>
                        Home
                    </a>
                </li>

                <li class="nav-item has-dropdown" role="none">
                    <a href="<?php echo base_url('listings.php'); ?>"
                       class="nav-link <?php echo $currentPage === 'listings' ? 'active' : ''; ?>"
                       role="menuitem"
                       aria-haspopup="true"
                       aria-expanded="false"
                       <?php echo $currentPage === 'listings' ? 'aria-current="page"' : ''; ?>>
                        Listings test
                        <i class="fas fa-chevron-down" aria-hidden="true"></i>
                    </a>
                    <ul class="dropdown-menu" role="menu" aria-label="Listings submenu">
                        <li role="none">
                            <a href="<?php echo base_url('listings.php'); ?>" role="menuitem">
                                <i class="fas fa-th-large fa-fw" aria-hidden="true"></i> All Properties
                            </a>
                        </li>
                        <li role="none">
                            <a href="<?php echo base_url('listings.php?status=sale'); ?>" role="menuitem">
                                <i class="fas fa-tag fa-fw" aria-hidden="true"></i> For Sale
                            </a>
                        </li>
                        <li role="none">
                            <a href="<?php echo base_url('listings.php?status=rent'); ?>" role="menuitem">
                                <i class="fas fa-key fa-fw" aria-hidden="true"></i> For Rent
                            </a>
                        </li>
                        <?php foreach ($propertyTypes as $type): ?>
                        <li role="none">
                            <a href="<?php echo base_url('listings.php?type=' . $type['id']); ?>" role="menuitem">
                                <i class="fas fa-building fa-fw" aria-hidden="true"></i>
                                <?php echo sanitize($type['name']); ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </li>

                <li class="nav-item has-dropdown" role="none">
                    <a href="<?php echo base_url('agents.php'); ?>"
                       class="nav-link <?php echo $agentsNavActive ? 'active' : ''; ?>"
                       role="menuitem"
                       aria-haspopup="true"
                       aria-expanded="false"
                       <?php echo $agentsNavActive ? 'aria-current="page"' : ''; ?>>
                        Agents
                        <i class="fas fa-chevron-down" aria-hidden="true"></i>
                    </a>
                    <ul class="dropdown-menu" role="menu" aria-label="Agents submenu">
                        <li role="none">
                            <a href="<?php echo base_url('agents.php'); ?>" role="menuitem">
                                <i class="fas fa-user-tie fa-fw" aria-hidden="true"></i> Preferred Agents
                            </a>
                        </li>
                        <li role="none">
                            <a href="<?php echo base_url('become-agent.php'); ?>" role="menuitem">
                                <i class="fas fa-id-badge fa-fw" aria-hidden="true"></i> Become an Agent
                            </a>
                        </li>
                        <li role="none">
                            <a href="<?php echo base_url('referral-network.php'); ?>" role="menuitem">
                                <i class="fas fa-share-nodes fa-fw" aria-hidden="true"></i> Referral Network
                            </a>
                        </li>
                        <li role="none">
                            <a href="<?php echo base_url('reviews.php'); ?>" role="menuitem">
                                <i class="fas fa-star fa-fw" aria-hidden="true"></i> Reviews
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="nav-item has-dropdown" role="none">
                    <a href="<?php echo base_url('about.php'); ?>"
                       class="nav-link <?php echo $companyNavActive ? 'active' : ''; ?>"
                       role="menuitem"
                       aria-haspopup="true"
                       aria-expanded="false"
                       <?php echo $companyNavActive ? 'aria-current="page"' : ''; ?>>
                        Company
                        <i class="fas fa-chevron-down" aria-hidden="true"></i>
                    </a>
                    <ul class="dropdown-menu" role="menu" aria-label="Company submenu">
                        <li role="none">
                            <a href="<?php echo base_url('about.php'); ?>" role="menuitem">
                                <i class="fas fa-circle-info fa-fw" aria-hidden="true"></i> About iProply
                            </a>
                        </li>
                        <li role="none">
                            <a href="<?php echo base_url('why-iproply.php'); ?>" role="menuitem">
                                <i class="fas fa-gem fa-fw" aria-hidden="true"></i> Why iProply
                            </a>
                        </li>
                        <li role="none">
                            <a href="<?php echo base_url('our-story.php'); ?>" role="menuitem">
                                <i class="fas fa-book-open fa-fw" aria-hidden="true"></i> Our Story
                            </a>
                        </li>
                        <li role="none">
                            <a href="<?php echo base_url('community-impact.php'); ?>" role="menuitem">
                                <i class="fas fa-handshake-angle fa-fw" aria-hidden="true"></i> Community Impact
                            </a>
                        </li>
                        <li role="none">
                            <a href="<?php echo base_url('inclusion.php'); ?>" role="menuitem">
                                <i class="fas fa-people-group fa-fw" aria-hidden="true"></i> Inclusion
                            </a>
                        </li>
                        <li role="none">
                            <a href="<?php echo base_url('press.php'); ?>" role="menuitem">
                                <i class="fas fa-newspaper fa-fw" aria-hidden="true"></i> Press
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="nav-item has-dropdown" role="none">
                    <a href="<?php echo base_url('blog.php'); ?>"
                       class="nav-link <?php echo $resourcesNavActive ? 'active' : ''; ?>"
                       role="menuitem"
                       aria-haspopup="true"
                       aria-expanded="false"
                       <?php echo $resourcesNavActive ? 'aria-current="page"' : ''; ?>>
                        Resources
                        <i class="fas fa-chevron-down" aria-hidden="true"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu--right" role="menu" aria-label="Resources submenu">
                        <li role="none">
                            <a href="<?php echo base_url('blog.php'); ?>" role="menuitem">
                                <i class="fas fa-blog fa-fw" aria-hidden="true"></i> Blog
                            </a>
                        </li>
                        <li role="none">
                            <a href="<?php echo base_url('market-reports.php'); ?>" role="menuitem">
                                <i class="fas fa-chart-line fa-fw" aria-hidden="true"></i> Market Reports
                            </a>
                        </li>
                        <li role="none">
                            <a href="<?php echo base_url('mortgage-calculator.php'); ?>" role="menuitem">
                                <i class="fas fa-calculator fa-fw" aria-hidden="true"></i> Mortgage Calculator
                            </a>
                        </li>
                        <li role="none">
                            <a href="<?php echo base_url('help-center.php'); ?>" role="menuitem">
                                <i class="fas fa-circle-question fa-fw" aria-hidden="true"></i> Help Center
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="nav-item" role="none">
                    <a href="<?php echo base_url('contact.php'); ?>"
                       class="nav-link <?php echo $currentPage === 'contact' ? 'active' : ''; ?>"
                       role="menuitem"
                       <?php echo $currentPage === 'contact' ? 'aria-current="page"' : ''; ?>>
                        Contact
                    </a>
                </li>

            </ul>
        </div>

        <!-- ── CTA BUTTONS ──────────────────────────────────── -->
        <div class="nav-actions">
            <!-- Search Trigger -->
            <button class="search-trigger" id="searchTrigger" aria-label="Open search">
                <i class="fas fa-search" aria-hidden="true"></i>
            </button>

            <?php if (is_agent()): ?>
                <a href="<?php echo base_url('agent/dashboard.php'); ?>" class="btn btn-outline">
                    <i class="fas fa-tachometer-alt" aria-hidden="true"></i> Dashboard
                </a>
            <?php elseif (is_admin()): ?>
                <a href="<?php echo base_url('admin/dashboard.php'); ?>" class="btn btn-outline">
                    <i class="fas fa-shield-alt" aria-hidden="true"></i> Admin
                </a>
            <?php else: ?>
                <a href="<?php echo base_url('agent/login.php'); ?>" class="btn btn-primary">Join/Sign In</a>
            <?php endif; ?>
            
        
        </div>

        <!-- ── MOBILE TOGGLE ────────────────────────────────── -->
        <button
            class="mobile-toggle"
            id="mobileToggle"
            aria-label="Open navigation menu"
            aria-expanded="false"
            aria-controls="mobileMenu"
        >
            <span></span>
            <span></span>
            <span></span>
        </button>

    </div>
</nav>

<!-- ==========================================================
     MOBILE OVERLAY BACKDROP
     ========================================================== -->
<div class="mobile-menu-overlay" id="mobileOverlay" aria-hidden="true"></div>

<!-- ==========================================================
     MOBILE SLIDE-IN PANEL
     ========================================================== -->
<aside
    class="mobile-menu"
    id="mobileMenu"
    role="dialog"
    aria-modal="true"
    aria-label="Navigation menu"
    hidden
>
    <!-- Panel header — always shows light logo on dark bg -->
    <div class="mobile-menu-header">
        <a href="<?php echo base_url(); ?>" class="logo" aria-label="Homepage">
            <img
                src="<?php echo asset_url('images/iproply-logo-light.png'); ?>"
                alt="<?php echo sanitize($siteSettings['site_name'] ?? APP_NAME); ?>"
                class="logo-image logo-image--light"
                width="150" height="40"
                loading="lazy" decoding="async"
            >
        </a>
        <button class="mobile-close" id="mobileClose" aria-label="Close menu">
            <i class="fas fa-times" aria-hidden="true"></i>
        </button>
    </div>

    <form class="mobile-menu-search" action="<?php echo base_url('listings.php'); ?>" method="GET">
        <label for="mobile-menu-keyword" class="sr-only">Search properties</label>
        <input
            type="text"
            id="mobile-menu-keyword"
            name="keyword"
            placeholder="Search city, ZIP, or keyword"
            value="<?php echo sanitize($_GET['keyword'] ?? ''); ?>"
        >
        <button type="submit" aria-label="Search listings">
            <i class="fas fa-search" aria-hidden="true"></i>
        </button>
    </form>

    <!-- Nav links -->
    <ul class="mobile-nav-list" role="list">
        <li><a href="<?php echo base_url(); ?>"               <?php echo $currentPage==='home'     ? 'aria-current="page"' : ''; ?>>Home</a></li>
        <li class="mobile-nav-group">
            <details class="mobile-nav-details" <?php echo $currentPage === 'listings' ? 'open' : ''; ?>>
                <summary <?php echo $currentPage === 'listings' ? 'aria-current="page"' : ''; ?>>
                    <span>Listings</span>
                    <i class="fas fa-chevron-down" aria-hidden="true"></i>
                </summary>
                <div class="mobile-subnav">
                    <a href="<?php echo base_url('listings.php'); ?>">All Properties</a>
                    <a href="<?php echo base_url('listings.php?status=sale'); ?>">For Sale</a>
                    <a href="<?php echo base_url('listings.php?status=rent'); ?>">For Rent</a>
                    <?php foreach ($propertyTypes as $type): ?>
                        <a href="<?php echo base_url('listings.php?type=' . $type['id']); ?>">
                            <?php echo sanitize($type['name']); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </details>
        </li>
        <li class="mobile-nav-group">
            <details class="mobile-nav-details" <?php echo $agentsNavActive ? 'open' : ''; ?>>
                <summary <?php echo $agentsNavActive ? 'aria-current="page"' : ''; ?>>
                    <span>Agents</span>
                    <i class="fas fa-chevron-down" aria-hidden="true"></i>
                </summary>
                <div class="mobile-subnav">
                    <a href="<?php echo base_url('agents.php'); ?>">Preferred Agents</a>
                    <a href="<?php echo base_url('become-agent.php'); ?>">Become an Agent</a>
                    <a href="<?php echo base_url('referral-network.php'); ?>">Referral Network</a>
                    <a href="<?php echo base_url('reviews.php'); ?>">Reviews</a>
                </div>
            </details>
        </li>
        <li class="mobile-nav-group">
            <details class="mobile-nav-details" <?php echo $companyNavActive ? 'open' : ''; ?>>
                <summary <?php echo $companyNavActive ? 'aria-current="page"' : ''; ?>>
                    <span>Company</span>
                    <i class="fas fa-chevron-down" aria-hidden="true"></i>
                </summary>
                <div class="mobile-subnav">
                    <a href="<?php echo base_url('about.php'); ?>">About iProply</a>
                    <a href="<?php echo base_url('why-iproply.php'); ?>">Why iProply</a>
                    <a href="<?php echo base_url('our-story.php'); ?>">Our Story</a>
                    <a href="<?php echo base_url('community-impact.php'); ?>">Community Impact</a>
                    <a href="<?php echo base_url('inclusion.php'); ?>">Inclusion</a>
                    <a href="<?php echo base_url('press.php'); ?>">Press</a>
                    <a href="<?php echo base_url('careers.php'); ?>">Careers</a>
                </div>
            </details>
        </li>
        <li class="mobile-nav-group">
            <details class="mobile-nav-details" <?php echo $resourcesNavActive ? 'open' : ''; ?>>
                <summary <?php echo $resourcesNavActive ? 'aria-current="page"' : ''; ?>>
                    <span>Resources</span>
                    <i class="fas fa-chevron-down" aria-hidden="true"></i>
                </summary>
                <div class="mobile-subnav">
                    <a href="<?php echo base_url('blog.php'); ?>">Blog</a>
                    <a href="<?php echo base_url('market-reports.php'); ?>">Market Reports</a>
                    <a href="<?php echo base_url('mortgage-calculator.php'); ?>">Mortgage Calculator</a>
                    <a href="<?php echo base_url('help-center.php'); ?>">Help Center</a>
                </div>
            </details>
        </li>
        <li><a href="<?php echo base_url('contact.php'); ?>" <?php echo $currentPage==='contact' ? 'aria-current="page"' : ''; ?>>Contact</a></li>
    </ul>

    <!-- CTA buttons -->
    <div class="mobile-nav-actions">
        <?php if (is_agent()): ?>
            <a href="<?php echo base_url('agent/dashboard.php'); ?>" class="btn btn-primary">
                <i class="fas fa-tachometer-alt" aria-hidden="true"></i> Agent Dashboard
            </a>
        <?php elseif (is_admin()): ?>
            <a href="<?php echo base_url('admin/dashboard.php'); ?>" class="btn btn-primary">
                <i class="fas fa-shield-alt" aria-hidden="true"></i> Admin Panel
            </a>
        <?php else: ?>
            <a href="<?php echo base_url('agent/login.php'); ?>"  class="btn btn-outline">Sign In</a>
            <a href="<?php echo base_url('listings.php'); ?>"      class="btn btn-primary">
                <i class="fas fa-search" aria-hidden="true"></i> Browse Listings
            </a>
        <?php endif; ?>
    </div>
</aside>

<!-- ==========================================================
     FLASH MESSAGES
     ========================================================== -->
<?php $flashMessages = get_flash_messages(); ?>
<?php if (!empty($flashMessages)): ?>
<div class="flash-messages" role="alert" aria-live="polite">
    <?php foreach ($flashMessages as $msg): ?>
    <div class="alert alert-<?php echo $msg['type']; ?>" data-auto-dismiss="6000">
        <?php echo sanitize($msg['message']); ?>
        <button class="alert-close" aria-label="Dismiss">&times;</button>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Main content wrapper — pages append their content after this tag -->
<main class="main-content">

<!-- ==========================================================
     HEADER JAVASCRIPT
     ========================================================== -->
<script>
(function () {
    'use strict';

    var navbar       = document.getElementById('navbar');
    var toggle       = document.getElementById('mobileToggle');
    var menu         = document.getElementById('mobileMenu');
    var overlay      = document.getElementById('mobileOverlay');
    var closeBtn     = document.getElementById('mobileClose');
    var searchTrigger = document.getElementById('searchTrigger');
    var searchOverlay = document.getElementById('searchOverlay');
    var searchClose   = document.getElementById('searchClose');
    var searchInput   = searchOverlay ? searchOverlay.querySelector('input') : null;
    var menuLastFocusedElement = null;
    var searchLastFocusedElement = null;

    var lastScrollY  = window.scrollY;
    var ticking      = false;
    var SCROLL_THRESHOLD = 100;
    var NAV_HIDE_THRESHOLD = 400;

    /* ── Scroll Handler: hide/show navbar + background change ── */
    function updateNavbar() {
        var currentScrollY = window.scrollY;
        var isOverlayActive = menu.classList.contains('active') || searchOverlay.classList.contains('active');

        if (isOverlayActive) {
            navbar.classList.remove('hidden');
        }

        // Background change
        if (currentScrollY > 50) {
            navbar.classList.add('scrolled');
            navbar.classList.remove('at-top');
        } else {
            navbar.classList.remove('scrolled');
            navbar.classList.add('at-top');
        }

        // Hide/show based on scroll direction (only after threshold)
        if (!isOverlayActive && currentScrollY > NAV_HIDE_THRESHOLD) {
            if (currentScrollY > lastScrollY && !navbar.classList.contains('hidden')) {
                // Scrolling down - hide navbar
                navbar.classList.add('hidden');
            } else if (currentScrollY < lastScrollY && navbar.classList.contains('hidden')) {
                // Scrolling up - show navbar
                navbar.classList.remove('hidden');
            }
        } else {
            navbar.classList.remove('hidden');
        }

        lastScrollY = currentScrollY;
        ticking = false;
    }

    window.addEventListener('scroll', function() {
        if (!ticking) {
            window.requestAnimationFrame(updateNavbar);
            ticking = true;
        }
    }, { passive: true });

    updateNavbar(); // Run immediately on load

    /* Desktop dropdown menus */
    var dropdownItems = Array.prototype.slice.call(document.querySelectorAll('.has-dropdown'));

    function closeDropdown(item) {
        var trigger = item ? item.querySelector('.nav-link') : null;
        if (!item || !trigger) return;

        item.classList.remove('is-open');
        trigger.setAttribute('aria-expanded', 'false');
    }

    function closeAllDropdowns(exceptItem) {
        dropdownItems.forEach(function (item) {
            if (item !== exceptItem) closeDropdown(item);
        });
    }

    function openDropdown(item, focusFirstItem) {
        var trigger = item ? item.querySelector('.nav-link') : null;
        var menuEl = item ? item.querySelector('.dropdown-menu') : null;
        if (!item || !trigger || !menuEl) return;

        closeAllDropdowns(item);
        item.classList.add('is-open');
        trigger.setAttribute('aria-expanded', 'true');

        if (focusFirstItem) {
            var firstLink = menuEl.querySelector('a');
            if (firstLink) firstLink.focus();
        }
    }

    function closestElement(target, selector) {
        if (!target) return null;
        if (typeof target.closest === 'function') return target.closest(selector);
        if (target.parentElement && typeof target.parentElement.closest === 'function') {
            return target.parentElement.closest(selector);
        }

        return null;
    }

    dropdownItems.forEach(function (item) {
        var trigger = item.querySelector('.nav-link');
        var menuEl = item.querySelector('.dropdown-menu');
        if (!trigger || !menuEl) return;

        trigger.addEventListener('click', function (e) {
            e.preventDefault();

            if (item.classList.contains('is-open')) {
                closeDropdown(item);
            } else {
                openDropdown(item, false);
            }
        });

        trigger.addEventListener('keydown', function (e) {
            if (e.key === 'ArrowDown' || e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                openDropdown(item, true);
            }

            if (e.key === 'Escape') {
                closeDropdown(item);
                trigger.focus();
            }
        });

        menuEl.addEventListener('click', function (e) {
            if (closestElement(e.target, 'a')) closeDropdown(item);
        });

        item.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeDropdown(item);
                trigger.focus();
            }
        });
    });

    document.addEventListener('click', function (e) {
        if (!closestElement(e.target, '.has-dropdown')) closeAllDropdowns();
    });

    /* ── Search Overlay ─────────────────────────────────────── */
    function openSearch() {
        if (menu.classList.contains('active')) closeMenu(false);
        closeAllDropdowns();
        searchLastFocusedElement = document.activeElement;
        searchOverlay.hidden = false;
        // Trigger reflow for animation
        void searchOverlay.offsetWidth;
        searchOverlay.classList.add('active');
        navbar.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        // Focus the input after animation
        setTimeout(function() {
            if (searchInput) searchInput.focus();
        }, 100);
    }

    function closeSearch(restoreFocus) {
        restoreFocus = restoreFocus !== false;
        searchOverlay.classList.remove('active');
        document.body.style.overflow = '';
        setTimeout(function() {
            searchOverlay.hidden = true;
            if (restoreFocus && searchLastFocusedElement && typeof searchLastFocusedElement.focus === 'function') {
                searchLastFocusedElement.focus();
            }
        }, 400);
    }

    if (searchTrigger) {
        searchTrigger.addEventListener('click', openSearch);
    }
    if (searchClose) {
        searchClose.addEventListener('click', closeSearch);
    }
    if (searchOverlay) {
        searchOverlay.addEventListener('click', function(e) {
            if (e.target === searchOverlay) closeSearch();
        });
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAllDropdowns();
        }
        if (e.key === 'Escape' && searchOverlay.classList.contains('active')) {
            closeSearch();
        }
        // Cmd/Ctrl + K to open search
        if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
            e.preventDefault();
            openSearch();
        }
    });

    /* ── Mobile menu: open ─────────────────────────────────── */
    function openMenu() {
        if (searchOverlay.classList.contains('active')) closeSearch(false);
        closeAllDropdowns();
        menuLastFocusedElement = document.activeElement;
        menu.hidden = false;
        // Trigger reflow
        void menu.offsetWidth;
        menu.classList.add('active');
        overlay.classList.add('active');
        overlay.setAttribute('aria-hidden', 'false');
        toggle.classList.add('active');
        toggle.setAttribute('aria-expanded', 'true');
        toggle.setAttribute('aria-label', 'Close navigation menu');
        navbar.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        closeBtn.focus();
    }

    /* ── Mobile menu: close ────────────────────────────────── */
    function closeMenu(restoreFocus) {
        restoreFocus = restoreFocus !== false;
        menu.classList.remove('active');
        overlay.classList.remove('active');
        overlay.setAttribute('aria-hidden', 'true');
        toggle.classList.remove('active');
        toggle.setAttribute('aria-expanded', 'false');
        toggle.setAttribute('aria-label', 'Open navigation menu');
        document.body.style.overflow = '';

        menu.addEventListener('transitionend', function handler() {
            if (!menu.classList.contains('active')) menu.hidden = true;
            menu.removeEventListener('transitionend', handler);
            if (restoreFocus && menuLastFocusedElement && typeof menuLastFocusedElement.focus === 'function') {
                menuLastFocusedElement.focus();
            }
        });
    }

    toggle.addEventListener('click', function () { 
        menu.hidden ? openMenu() : closeMenu(); 
    });
    closeBtn.addEventListener('click', closeMenu);
    overlay.addEventListener('click', closeMenu);

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && menu.classList.contains('active')) closeMenu();
    });

    /* Close panel when a nav link is tapped */
    menu.querySelectorAll('.mobile-nav-list a').forEach(function (a) {
        a.addEventListener('click', closeMenu);
    });

    window.addEventListener('resize', function () {
        closeAllDropdowns();
        if (window.matchMedia('(min-width: 1180px)').matches && menu.classList.contains('active')) {
            closeMenu(false);
        }
    });

    /* ── Flash messages ────────────────────────────────────── */
    function dismissAlert(el) {
        if (!el) return;
        el.style.transition = 'opacity 400ms ease, transform 400ms ease';
        el.style.opacity = '0';
        el.style.transform = 'translateX(30px) scale(0.95)';
        setTimeout(function () { 
            if (el.parentNode) el.parentNode.removeChild(el); 
        }, 420);
    }

    document.querySelectorAll('.alert[data-auto-dismiss]').forEach(function (el) {
        setTimeout(function () { dismissAlert(el); }, parseInt(el.dataset.autoDismiss, 10) || 6000);
    });

    document.querySelectorAll('.alert-close').forEach(function (btn) {
        btn.addEventListener('click', function () { dismissAlert(btn.closest('.alert')); });
    });

})();
</script>

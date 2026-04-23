<?php
/**
 * iProply — Header Partial (2025 Design)
 * Enhanced UI/UX with modern luxury real estate aesthetics
 */

$db            = Database::getInstance();
$siteSettings  = $db->selectOne('site_settings', '*');
$propertyModel = new Property();
$propertyTypes = $propertyModel->getPropertyTypes();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo sanitize($siteSettings['meta_description'] ?? APP_NAME . ' - Find Your Dream Home'); ?>">
    <meta name="keywords"    content="<?php echo sanitize($siteSettings['meta_keywords']    ?? 'real estate, property, homes, apartments'); ?>">
    <title><?php echo isset($pageTitle) ? sanitize($pageTitle) . ' — ' : ''; ?><?php echo sanitize($siteSettings['site_name'] ?? APP_NAME); ?></title>

    <link rel="icon" type="image/x-icon" href="<?php echo asset_url('../assets/favicon.ico'); ?>">

    <!-- Fonts: Poppins (site-wide) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Site stylesheet -->
    <link rel="stylesheet" href="<?php echo asset_url('css/style.css'); ?>">

    <?php if (isset($extraCss)): foreach ($extraCss as $css): ?>
        <link rel="stylesheet" href="<?php echo asset_url('css/' . $css); ?>">
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
                name="search" 
                class="search-overlay-input" 
                placeholder="Search properties, locations, or keywords..." 
                aria-label="Search properties"
                autocomplete="off"
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
                        Listings
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

                <li class="nav-item" role="none">
                    <a href="<?php echo base_url('agents.php'); ?>"
                       class="nav-link <?php echo $currentPage === 'agents' ? 'active' : ''; ?>"
                       role="menuitem"
                       <?php echo $currentPage === 'agents' ? 'aria-current="page"' : ''; ?>>
                        Preferred Agents
                    </a>
                </li>

                <li class="nav-item" role="none">
                    <a href="<?php echo base_url('about.php'); ?>"
                       class="nav-link <?php echo $currentPage === 'about' ? 'active' : ''; ?>"
                       role="menuitem"
                       <?php echo $currentPage === 'about' ? 'aria-current="page"' : ''; ?>>
                        About
                    </a>
                </li>

                <li class="nav-item" role="none">
                    <a href="<?php echo base_url('contact.php'); ?>"
                       class="nav-link <?php echo $currentPage === 'contact' ? 'active' : ''; ?>"
                       role="menuitem"
                       <?php echo $currentPage === 'contact' ? 'aria-current="page"' : ''; ?>>
                        Contact
                    </a>
                </li>

                <li class="nav-item" role="none">
                    <a href="<?php echo base_url('blog.php'); ?>"
                       class="nav-link <?php echo $currentPage === 'blog' ? 'active' : ''; ?>"
                       role="menuitem"
                       <?php echo $currentPage === 'blog' ? 'aria-current="page"' : ''; ?>>
                        Blog
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
            
            </a>
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

    <!-- Nav links -->
    <ul class="mobile-nav-list" role="list">
        <li><a href="<?php echo base_url(); ?>"               <?php echo $currentPage==='home'     ? 'aria-current="page"' : ''; ?>>Home</a></li>
        <li><a href="<?php echo base_url('listings.php'); ?>" <?php echo $currentPage==='listings' ? 'aria-current="page"' : ''; ?>>Listings</a></li>
        <li><a href="<?php echo base_url('agents.php'); ?>"   <?php echo $currentPage==='agents'   ? 'aria-current="page"' : ''; ?>>Agents</a></li>
        <li><a href="<?php echo base_url('about.php'); ?>"    <?php echo $currentPage==='about'    ? 'aria-current="page"' : ''; ?>>About</a></li>
        <li><a href="<?php echo base_url('contact.php'); ?>"  <?php echo $currentPage==='contact'  ? 'aria-current="page"' : ''; ?>>Contact</a></li>
        <li><a href="<?php echo base_url('blog.php'); ?>"     <?php echo $currentPage==='blog'     ? 'aria-current="page"' : ''; ?>>Blog</a></li>
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

    var lastScrollY  = window.scrollY;
    var ticking      = false;
    var SCROLL_THRESHOLD = 100;
    var NAV_HIDE_THRESHOLD = 400;

    /* ── Scroll Handler: hide/show navbar + background change ── */
    function updateNavbar() {
        var currentScrollY = window.scrollY;

        // Background change
        if (currentScrollY > 50) {
            navbar.classList.add('scrolled');
            navbar.classList.remove('at-top');
        } else {
            navbar.classList.remove('scrolled');
            navbar.classList.add('at-top');
        }

        // Hide/show based on scroll direction (only after threshold)
        if (currentScrollY > NAV_HIDE_THRESHOLD) {
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

    /* ── Search Overlay ─────────────────────────────────────── */
    function openSearch() {
        searchOverlay.hidden = false;
        // Trigger reflow for animation
        void searchOverlay.offsetWidth;
        searchOverlay.classList.add('active');
        document.body.style.overflow = 'hidden';
        // Focus the input after animation
        setTimeout(function() {
            searchOverlay.querySelector('input').focus();
        }, 100);
    }

    function closeSearch() {
        searchOverlay.classList.remove('active');
        document.body.style.overflow = '';
        setTimeout(function() {
            searchOverlay.hidden = true;
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
        menu.hidden = false;
        // Trigger reflow
        void menu.offsetWidth;
        menu.classList.add('active');
        overlay.classList.add('active');
        overlay.setAttribute('aria-hidden', 'false');
        toggle.classList.add('active');
        toggle.setAttribute('aria-expanded', 'true');
        toggle.setAttribute('aria-label', 'Close navigation menu');
        document.body.style.overflow = 'hidden';
        closeBtn.focus();
    }

    /* ── Mobile menu: close ────────────────────────────────── */
    function closeMenu() {
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

    /* ── Dropdown: aria-expanded ───────────────────────────── */
    document.querySelectorAll('.has-dropdown').forEach(function (item) {
        var trigger = item.querySelector('.nav-link');
        item.addEventListener('mouseenter', function () { 
            trigger.setAttribute('aria-expanded', 'true'); 
        });
        item.addEventListener('mouseleave', function () { 
            trigger.setAttribute('aria-expanded', 'false'); 
        });
        item.addEventListener('focusin', function () { 
            trigger.setAttribute('aria-expanded', 'true'); 
        });
        item.addEventListener('focusout', function (e) {
            if (!item.contains(e.relatedTarget)) trigger.setAttribute('aria-expanded', 'false');
        });
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

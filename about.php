<?php
/**
 * iProply - About Us Page
 */

require_once 'config/config.php';

// Set current page
$currentPage = 'about';
$pageTitle = 'About Us';

// Include header
include 'partials/header.php';
?>

<!-- About Hero -->
<section class="about-hero">
    <div class="container">
        <h1>About iProply</h1>
        <p>Your trusted partner in finding the perfect property. We make real estate simple, transparent, and enjoyable.</p>
    </div>
</section>

<!-- Our Story Section -->
<section class="section">
    <div class="container">
        <div class="about-grid">
            <div class="about-content">
                <span class="section-label">Our Story</span>
                <h2>We've Been Helping People Find Their Dream Homes Since 2009</h2>
                <p>
                    Founded with a simple mission - to make real estate transactions transparent and stress-free, 
                    iProply has grown from a small local agency to one of the most trusted names in the industry.
                </p>
                <p>
                    Our team of experienced agents combines local market knowledge with cutting-edge technology 
                    to deliver exceptional results for buyers, sellers, and renters alike.
                </p>
                <p>
                    We believe that everyone deserves a place to call home, and we're committed to making that 
                    dream a reality for our clients through personalized service and expert guidance.
                </p>
            </div>
            <div class="about-image">
                <img src="https://images.unsplash.com/photo-1600880292203-757bb62b4baf?w=600&h=500&fit=crop" alt="Our Team">
                <div class="about-image-frame"></div>
            </div>
        </div>
    </div>
</section>

<!-- Our Values Section -->
<section class="section section-bg">
    <div class="container">
        <div class="section-header">
            <span class="section-label">Our Values</span>
            <h2 class="section-title">What We Stand For</h2>
            <p class="section-subtitle">
                Our core values guide everything we do
            </p>
        </div>
        
        <div class="values-grid">
            <div class="value-card">
                <div class="value-icon">
                    <i class="fas fa-handshake"></i>
                </div>
                <h3>Integrity</h3>
                <p>We believe in honest, transparent dealings with all our clients. Your trust is our most valuable asset.</p>
            </div>
            
            <div class="value-card">
                <div class="value-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3>Client First</h3>
                <p>Your needs and goals are our top priority. We work tirelessly to exceed your expectations.</p>
            </div>
            
            <div class="value-card">
                <div class="value-icon">
                    <i class="fas fa-lightbulb"></i>
                </div>
                <h3>Innovation</h3>
                <p>We leverage the latest technology and market insights to give you a competitive advantage.</p>
            </div>
            
            <div class="value-card">
                <div class="value-icon">
                    <i class="fas fa-award"></i>
                </div>
                <h3>Excellence</h3>
                <p>We strive for excellence in every transaction, ensuring the best possible outcomes for our clients.</p>
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="section">
    <div class="container">
        <div class="about-stats" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 2rem; text-align: center;">
            <div class="stat-item">
                <div class="stat-number" data-counter="15" data-suffix="+">0</div>
                <div class="stat-label">Years Experience</div>
            </div>
            <div class="stat-item">
                <div class="stat-number" data-counter="2500" data-suffix="+">0</div>
                <div class="stat-label">Properties Sold</div>
            </div>
            <div class="stat-item">
                <div class="stat-number" data-counter="150" data-suffix="+">0</div>
                <div class="stat-label">Expert Agents</div>
            </div>
            <div class="stat-item">
                <div class="stat-number" data-counter="98" data-suffix="%">0</div>
                <div class="stat-label">Client Satisfaction</div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="section section-bg">
    <div class="container">
        <div class="cta-section">
            <div class="cta-grid">
                <div class="cta-content">
                    <h2>Ready to Work With Us?</h2>
                    <p>Whether you're buying, selling, or renting, our team is here to help you every step of the way.</p>
                </div>
                <div class="cta-action">
                    <a href="contact.php" class="btn btn-white btn-lg">Get in Touch</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
// Include footer
include 'partials/footer.php';
?>

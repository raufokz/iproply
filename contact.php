<?php
/**
 * iProply - Contact Us Page
 */

require_once 'config/config.php';
require_once 'includes/Database.php';
require_once 'includes/Mail.php';

// Set current page
$currentPage = 'contact';
$pageTitle = 'Contact Us';

// Get site settings
$db = Database::getInstance();
$siteSettings = $db->selectOne('site_settings', '*');

// Handle form submission
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');
        
        // Validation
        if (empty($name)) {
            $errors[] = 'Name is required';
        }
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Valid email is required';
        }
        
        if (empty($subject)) {
            $errors[] = 'Subject is required';
        }
        
        if (empty($message)) {
            $errors[] = 'Message is required';
        }
        
        if (empty($errors)) {
            // Send email
            $mail = new Mail();
            $body = "
                <h2>New Contact Form Submission</h2>
                <p><strong>Name:</strong> {$name}</p>
                <p><strong>Email:</strong> {$email}</p>
                <p><strong>Phone:</strong> {$phone}</p>
                <p><strong>Subject:</strong> {$subject}</p>
                <p><strong>Message:</strong></p>
                <p>" . nl2br(sanitize($message)) . "</p>
            ";
            
            $mail->send(
                $siteSettings['site_email'] ?? 'info@iproply.com',
                'iProply Team',
                'Contact Form: ' . $subject,
                $body
            );
            
            $success = true;
        }
    }
}

// Include header
include 'partials/header.php';
?>

<!-- Contact Page Hero Background Overlay -->
<style>
    .page-header {
        position: relative;
        background: none; /* Remove default gradient */
        background-image: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('./assets/images/contact-hero.png');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        color: white;
        text-align: center;
    }
</style>

<!-- Page Header -->
<header class="page-header">
    <div class="container">
        <h1>Contact Us</h1>
        <p>Get in touch with our team. We're here to help!</p>
    </div>
</header>

<!-- Contact Section -->
<section class="section">
    <div class="container">
        <div class="contact-grid">
            <div class="contact-info">
                <h2>Get In Touch</h2>
                <p>Have questions about buying, selling, or renting? Our team is here to help you with all your real estate needs.</p>
                
                <div class="contact-details">
                    <div class="contact-detail">
                        <i class="fas fa-map-marker-alt"></i>
                        <div>
                            <h4>Address</h4>
                            <p><?php echo nl2br(sanitize($siteSettings['site_address'] ?? "123 Real Estate Ave\nNew York, NY 10001")); ?></p>
                        </div>
                    </div>
                    
                    <div class="contact-detail">
                        <i class="fas fa-phone"></i>
                        <div>
                            <h4>Phone</h4>
                            <p><a href="tel:<?php echo preg_replace('/[^0-9]/', '', $siteSettings['site_phone'] ?? ''); ?>"><?php echo sanitize($siteSettings['site_phone'] ?? '(555) 123-4567'); ?></a></p>
                        </div>
                    </div>
                    
                    <div class="contact-detail">
                        <i class="fas fa-envelope"></i>
                        <div>
                            <h4>Email</h4>
                            <p><a href="mailto:<?php echo sanitize($siteSettings['site_email'] ?? 'info@iproply.com'); ?>"><?php echo sanitize($siteSettings['site_email'] ?? 'info@iproply.com'); ?></a></p>
                        </div>
                    </div>
                    
                    <div class="contact-detail">
                        <i class="fas fa-clock"></i>
                        <div>
                            <h4>Business Hours</h4>
                            <p>Monday - Friday: 9:00 AM - 6:00 PM<br>
                            Saturday: 10:00 AM - 4:00 PM<br>
                            Sunday: Closed</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="contact-form-wrapper">
                <h3>Send Us a Message</h3>
                
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        Thank you for your message! We'll get back to you soon.
                    </div>
                <?php else: ?>
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-error">
                            <ul style="margin: 0; padding-left: 1.5rem;">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo sanitize($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form action="" method="POST" data-validate>
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        
                        <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div class="form-group">
                                <label for="name">Your Name *</label>
                                <input type="text" id="name" name="name" required value="<?php echo sanitize($_POST['name'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email Address *</label>
                                <input type="email" id="email" name="email" required value="<?php echo sanitize($_POST['email'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 1rem;">
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="tel" id="phone" name="phone" value="<?php echo sanitize($_POST['phone'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="subject">Subject *</label>
                                <input type="text" id="subject" name="subject" required value="<?php echo sanitize($_POST['subject'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="form-group" style="margin-top: 1rem;">
                            <label for="message">Message *</label>
                            <textarea id="message" name="message" rows="5" required><?php echo sanitize($_POST['message'] ?? ''); ?></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">
                            <i class="fas fa-paper-plane"></i> Send Message
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Map Section -->
<section class="section section-bg">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Visit Our Office</h2>
            <p class="section-subtitle">Come meet us in person</p>
        </div>
        
        <div style="background: white; border-radius: 12px; overflow: hidden; box-shadow: var(--shadow-md);">
            <iframe 
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3022.217676750664!2d-73.98784408459418!3d40.75797467932688!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c25855c6480299%3A0x55194ec5a1ae072e!2sTimes%20Square!5e0!3m2!1sen!2sus!4v1645564756245!5m2!1sen!2sus" 
                width="100%" 
                height="400" 
                style="border:0;" 
                allowfullscreen="" 
                loading="lazy">
            </iframe>
        </div>
    </div>
</section>

<?php
// Include footer
include 'partials/footer.php';
?>
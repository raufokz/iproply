<?php
/**
 * iProply - Privacy Policy Page
 */

require_once 'config/config.php';
require_once 'includes/Database.php';

// Get site settings
$db = Database::getInstance();
$siteSettings = $db->selectOne('site_settings', '*');

// Set page variables
$currentPage = 'privacy';
$pageTitle = 'Privacy Policy';

// Include header
include 'partials/header.php';
?>

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <h1>Privacy Policy</h1>
        <nav class="breadcrumb">
            <a href="<?php echo base_url(); ?>">Home</a>
            <span>/</span>
            <span>Privacy Policy</span>
        </nav>
    </div>
</section>

<!-- Privacy Content -->
<section class="section">
    <div class="container">
        <div class="privacy-content" style="max-width: 900px; margin: 0 auto;">
            
            <p class="last-updated" style="color: var(--text-secondary); font-style: italic; margin-bottom: 2rem;">
                Last Updated: March 24, 2026
            </p>

            <div class="privacy-section">
                <h2>1. Introduction</h2>
                <p>
                    Welcome to <?php echo APP_NAME; ?> ("we," "our," or "us"). We are committed to protecting your personal information 
                    and your right to privacy. This Privacy Policy explains how we collect, use, disclose, and safeguard your 
                    information when you visit our website <?php echo APP_URL; ?> (the "Site") or use our services.
                </p>
                <p>
                    Please read this privacy policy carefully. If you do not agree with the terms of this privacy policy, 
                    please do not access the site.
                </p>
            </div>

            <div class="privacy-section">
                <h2>2. Information We Collect</h2>
                <p>We collect personal information that you voluntarily provide to us when you:</p>
                <ul>
                    <li>Register on the Site as an agent or user</li>
                    <li>Express interest in obtaining information about our properties</li>
                    <li>Participate in activities on the Site (such as posting comments or inquiries)</li>
                    <li>Contact us directly</li>
                </ul>
                
                <h3>2.1 Personal Information</h3>
                <p>The personal information we collect may include:</p>
                <ul>
                    <li>Name, email address, phone number, and mailing address</li>
                    <li>Login credentials (username and encrypted password)</li>
                    <li>Property preferences and search history</li>
                    <li>Inquiry messages and communication records</li>
                    <li>Professional information (for agents: license number, agency affiliation)</li>
                </ul>

                <h3>2.2 Automatically Collected Information</h3>
                <p>We automatically collect certain information when you visit our Site, including:</p>
                <ul>
                    <li>IP address, browser type, and operating system</li>
                    <li>Pages viewed, links clicked, and time spent on pages</li>
                    <li>Device information and unique device identifiers</li>
                    <li>Referring website or search terms</li>
                </ul>
            </div>

            <div class="privacy-section">
                <h2>3. How We Use Your Information</h2>
                <p>We use the information we collect to:</p>
                <ul>
                    <li>Provide, operate, and maintain our website and services</li>
                    <li>Process and manage property inquiries and transactions</li>
                    <li>Connect buyers/renters with real estate agents</li>
                    <li>Send administrative information, updates, and security alerts</li>
                    <li>Respond to customer service requests and support needs</li>
                    <li>Send marketing and promotional communications (with your consent)</li>
                    <li>Improve our website, products, and services</li>
                    <li>Prevent fraudulent transactions and monitor against theft</li>
                    <li>Comply with legal obligations</li>
                </ul>
            </div>

            <div class="privacy-section">
                <h2>4. Sharing Your Information</h2>
                <p>We may share your information with:</p>
                <ul>
                    <li><strong>Real Estate Agents:</strong> When you submit an inquiry about a property, we share your contact information with the listing agent.</li>
                    <li><strong>Service Providers:</strong> Third-party vendors who provide services on our behalf (hosting, email delivery, analytics).</li>
                    <li><strong>Business Transfers:</strong> In connection with any merger, sale of company assets, or acquisition.</li>
                    <li><strong>Legal Requirements:</strong> When required by law, court order, or governmental regulation.</li>
                </ul>
                <p>We do not sell, rent, or trade your personal information to third parties for their marketing purposes.</p>
            </div>

            <div class="privacy-section">
                <h2>5. Cookies and Tracking Technologies</h2>
                <p>
                    We use cookies and similar tracking technologies to track activity on our Site and hold certain information. 
                    You can instruct your browser to refuse all cookies or to indicate when a cookie is being sent. However, 
                    if you do not accept cookies, you may not be able to use some portions of our Site.
                </p>
                <p>Types of cookies we use:</p>
                <ul>
                    <li><strong>Essential Cookies:</strong> Necessary for the website to function properly.</li>
                    <li><strong>Analytics Cookies:</strong> Help us understand how visitors interact with our website.</li>
                    <li><strong>Preference Cookies:</strong> Remember your settings and preferences.</li>
                    <li><strong>Marketing Cookies:</strong> Track visitors across websites to display relevant advertisements.</li>
                </ul>
            </div>

            <div class="privacy-section">
                <h2>6. Data Security</h2>
                <p>
                    We implement appropriate technical and organizational security measures to protect your personal information. 
                    However, please be aware that no method of transmission over the internet or electronic storage is 100% secure, 
                    and we cannot guarantee absolute security.
                </p>
                <p>Our security measures include:</p>
                <ul>
                    <li>SSL/TLS encryption for data transmission</li>
                    <li>Encrypted password storage using bcrypt hashing</li>
                    <li>Regular security assessments and updates</li>
                    <li>Limited access to personal information by authorized personnel only</li>
                </ul>
            </div>

            <div class="privacy-section">
                <h2>7. Your Privacy Rights</h2>
                <p>Depending on your location, you may have the following rights:</p>
                <ul>
                    <li><strong>Access:</strong> Request a copy of your personal information.</li>
                    <li><strong>Correction:</strong> Request correction of inaccurate information.</li>
                    <li><strong>Deletion:</strong> Request deletion of your personal information.</li>
                    <li><strong>Restriction:</strong> Request restriction of processing your information.</li>
                    <li><strong>Portability:</strong> Request transfer of your information to another service.</li>
                    <li><strong>Objection:</strong> Object to processing of your personal information.</li>
                </ul>
                <p>
                    To exercise these rights, please contact us at 
                    <a href="mailto:<?php echo sanitize($siteSettings['site_email'] ?? 'privacy@iproply.com'); ?>">
                        <?php echo sanitize($siteSettings['site_email'] ?? 'privacy@iproply.com'); ?>
                    </a>.
                </p>
            </div>

            <div class="privacy-section">
                <h2>8. Data Retention</h2>
                <p>
                    We will retain your personal information only for as long as is necessary for the purposes set out in this 
                    Privacy Policy. We will retain and use your information to the extent necessary to comply with our legal 
                    obligations, resolve disputes, and enforce our policies.
                </p>
            </div>

            <div class="privacy-section">
                <h2>9. Children's Privacy</h2>
                <p>
                    Our Site is not intended for children under 13 years of age. We do not knowingly collect personal 
                    information from children under 13. If you are a parent or guardian and believe your child has provided 
                    us with personal information, please contact us immediately.
                </p>
            </div>

            <div class="privacy-section">
                <h2>10. Third-Party Websites</h2>
                <p>
                    Our Site may contain links to third-party websites. We have no control over and assume no responsibility 
                    for the content, privacy policies, or practices of any third-party sites or services. We encourage you 
                    to review the privacy policy of every site you visit.
                </p>
            </div>

            <div class="privacy-section">
                <h2>11. Changes to This Privacy Policy</h2>
                <p>
                    We may update our Privacy Policy from time to time. We will notify you of any changes by posting the new 
                    Privacy Policy on this page and updating the "Last Updated" date. You are advised to review this Privacy 
                    Policy periodically for any changes.
                </p>
            </div>

            <div class="privacy-section">
                <h2>12. Contact Us</h2>
                <p>If you have any questions about this Privacy Policy, please contact us:</p>
                <ul>
                    <li>
                        <strong>Email:</strong> 
                        <a href="mailto:<?php echo sanitize($siteSettings['site_email'] ?? 'privacy@iproply.com'); ?>">
                            <?php echo sanitize($siteSettings['site_email'] ?? 'privacy@iproply.com'); ?>
                        </a>
                    </li>
                    <li>
                        <strong>Address:</strong><br>
                        <?php echo nl2br(sanitize($siteSettings['site_address'] ?? "iProply\n123 Real Estate Ave\nNew York, NY 10001")); ?>
                    </li>
                    <li>
                        <strong>Phone:</strong> 
                        <a href="tel:<?php echo preg_replace('/[^0-9]/', '', $siteSettings['site_phone'] ?? ''); ?>">
                            <?php echo sanitize($siteSettings['site_phone'] ?? '(555) 123-4567'); ?>
                        </a>
                    </li>
                </ul>
            </div>

        </div>
    </div>
</section>

<?php
// Include footer
include 'partials/footer.php';
?>
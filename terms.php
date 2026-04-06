<?php
/**
 * iProply - Terms of Service Page
 */

require_once 'config/config.php';
require_once 'includes/Database.php';

// Get site settings
$db = Database::getInstance();
$siteSettings = $db->selectOne('site_settings', '*');

// Set page variables
$currentPage = 'terms';
$pageTitle = 'Terms of Service';

// Include header
include 'partials/header.php';
?>

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <h1>Terms of Service</h1>
        <nav class="breadcrumb">
            <a href="<?php echo base_url(); ?>">Home</a>
            <span>/</span>
            <span>Terms of Service</span>
        </nav>
    </div>
</section>

<!-- Terms Content -->
<section class="section">
    <div class="container">
        <div class="terms-content" style="max-width: 900px; margin: 0 auto;">
            
            <p class="last-updated" style="color: var(--text-secondary); font-style: italic; margin-bottom: 2rem;">
                Last Updated: March 24, 2026
            </p>

            <div class="terms-section">
                <h2>1. Agreement to Terms</h2>
                <p>
                    Welcome to <?php echo APP_NAME; ?> ("Company," "we," "us," or "our"). These Terms of Service ("Terms") 
                    constitute a legally binding agreement between you and <?php echo APP_NAME; ?> governing your access to 
                    and use of the <?php echo APP_URL; ?> website ("Site"), including any content, functionality, and services 
                    offered on or through the Site.
                </p>
                <p>
                    By accessing or using our Site, you agree to be bound by these Terms. If you do not agree to these Terms, 
                    you must not access or use the Site.
                </p>
            </div>

            <div class="terms-section">
                <h2>2. Changes to Terms</h2>
                <p>
                    We may revise and update these Terms from time to time at our sole discretion. All changes are effective 
                    immediately when we post them and apply to all access to and use of the Site thereafter.
                </p>
                <p>
                    Your continued use of the Site following the posting of revised Terms means that you accept and agree to 
                    the changes. You are expected to check this page frequently so you are aware of any changes.
                </p>
            </div>

            <div class="terms-section">
                <h2>3. Accessing the Site and Account Security</h2>
                <p>
                    We reserve the right to withdraw or amend this Site, and any service or material we provide on the Site, 
                    in our sole discretion without notice. We will not be liable if for any reason all or any part of the Site 
                    is unavailable at any time or for any period.
                </p>
                <p>To access certain features of the Site, you may be required to register for an account. You agree to:</p>
                <ul>
                    <li>Provide accurate, current, and complete information during registration</li>
                    <li>Maintain and promptly update your account information</li>
                    <li>Maintain the security of your password and accept all risks of unauthorized access</li>
                    <li>Notify us immediately of any unauthorized use of your account</li>
                    <li>Accept responsibility for all activities that occur under your account</li>
                </ul>
            </div>

            <div class="terms-section">
                <h2>4. User Types and Eligibility</h2>
                
                <h3>4.1 General Users</h3>
                <p>
                    The Site is available to individuals who are at least 18 years old and capable of forming legally binding 
                    contracts under applicable law. By using the Site, you represent and warrant that you meet these requirements.
                </p>

                <h3>4.2 Real Estate Agents</h3>
                <p>
                    Agents using our platform must:
                </p>
                <ul>
                    <li>Hold a valid real estate license in the state(s) where they operate</li>
                    <li>Provide accurate licensing information and professional credentials</li>
                    <li>Maintain all required professional insurance and bonding</li>
                    <li>Comply with all applicable real estate laws and regulations</li>
                    <li>Provide truthful and accurate property listings</li>
                </ul>

                <h3>4.3 Prohibited Users</h3>
                <p>We reserve the right to terminate or refuse service to anyone for any reason at our discretion.</p>
            </div>

            <div class="terms-section">
                <h2>5. Property Listings and Content</h2>
                
                <h3>5.1 Listing Accuracy</h3>
                <p>
                    All property listings must be accurate, truthful, and not misleading. Agents are solely responsible for 
                    the content of their listings, including:
                </p>
                <ul>
                    <li>Property descriptions, specifications, and features</li>
                    <li>Pricing and availability information</li>
                    <li>Images and media representing the property</li>
                    <li>Disclosure of material defects or issues</li>
                </ul>

                <h3>5.2 Prohibited Content</h3>
                <p>You may not post listings or content that:</p>
                <ul>
                    <li>Violates any local, state, or federal laws</li>
                    <li>Infringes on intellectual property rights</li>
                    <li>Contains false, misleading, or deceptive information</li>
                    <li>Discriminates based on race, color, religion, sex, handicap, familial status, or national origin</li>
                    <li>Promotes illegal activities or services</li>
                    <li>Contains viruses, malware, or harmful code</li>
                </ul>

                <h3>5.3 Content License</h3>
                <p>
                    By posting content on our Site, you grant us a non-exclusive, worldwide, royalty-free license to use, 
                    reproduce, modify, and display such content in connection with operating and promoting the Site.
                </p>
            </div>

            <div class="terms-section">
                <h2>6. Fees and Payments</h2>
                
                <h3>6.1 Listing Fees</h3>
                <p>
                    <?php echo APP_NAME; ?> reserves the right to charge fees for certain services, including but not limited 
                    to featured listings, premium placement, and advertising. All fees are non-refundable unless otherwise stated.
                </p>

                <h3>6.2 Payment Terms</h3>
                <p>
                    All payments must be made in U.S. dollars. You agree to provide current, complete, and accurate billing 
                    information. We reserve the right to suspend or terminate accounts with overdue balances.
                </p>

                <h3>6.3 Taxes</h3>
                <p>
                    You are responsible for all applicable taxes related to your use of the Site and any transactions conducted 
                    through the Site.
                </p>
            </div>

            <div class="terms-section">
                <h2>7. Prohibited Activities</h2>
                <p>You agree not to engage in any of the following prohibited activities:</p>
                <ul>
                    <li>Scraping, data mining, or harvesting information from the Site</li>
                    <li>Using automated systems to access the Site without authorization</li>
                    <li>Attempting to interfere with the proper working of the Site</li>
                    <li>Circumventing any security measures or access controls</li>
                    <li>Impersonating any person or entity</li>
                    <li>Engaging in any activity that disrupts or diminishes the quality of the Site</li>
                    <li>Contacting property owners or agents for purposes other than genuine interest</li>
                    <li>Posting duplicate or spam listings</li>
                    <li>Using the Site to conduct any illegal activities</li>
                </ul>
            </div>

            <div class="terms-section">
                <h2>8. Intellectual Property Rights</h2>
                
                <h3>8.1 Our Content</h3>
                <p>
                    The Site and its entire contents, features, and functionality (including but not limited to all information, 
                    software, text, displays, images, video, and audio, and the design, selection, and arrangement thereof) 
                    are owned by <?php echo APP_NAME; ?>, its licensors, or other providers and are protected by United States 
                    and international copyright, trademark, patent, trade secret, and other intellectual property laws.
                </p>

                <h3>8.2 Trademarks</h3>
                <p>
                    The <?php echo APP_NAME; ?> name, logo, and all related names, logos, product and service names, designs, 
                    and slogans are trademarks of <?php echo APP_NAME; ?> or its affiliates. You may not use such marks 
                    without our prior written permission.
                </p>

                <h3>8.3 User Content</h3>
                <p>
                    You retain ownership of content you submit to the Site. However, by submitting content, you grant us 
                    the rights described in Section 5.3.
                </p>
            </div>

            <div class="terms-section">
                <h2>9. Disclaimer of Warranties</h2>
                <p>
                    THE SITE IS PROVIDED ON AN "AS IS" AND "AS AVAILABLE" BASIS, WITHOUT ANY WARRANTIES OF ANY KIND, EITHER 
                    EXPRESS OR IMPLIED. TO THE FULLEST EXTENT PERMITTED BY LAW, WE DISCLAIM ALL WARRANTIES, INCLUDING BUT NOT 
                    LIMITED TO:
                </p>
                <ul>
                    <li>Merchantability, fitness for a particular purpose, and non-infringement</li>
                    <li>Accuracy, completeness, or reliability of any content on the Site</li>
                    <li>That the Site will be uninterrupted, timely, secure, or error-free</li>
                    <li>That defects will be corrected or that the Site is free of viruses</li>
                    <li>That any property listings are accurate, available, or legally compliant</li>
                </ul>
                <p>
                    WE DO NOT WARRANT, ENDORSE, GUARANTEE, OR ASSUME RESPONSIBILITY FOR ANY PROPERTY LISTED ON THE SITE OR 
                    ANY TRANSACTION BETWEEN USERS.
                </p>
            </div>

            <div class="terms-section">
                <h2>10. Limitation of Liability</h2>
                <p>
                    TO THE FULLEST EXTENT PERMITTED BY APPLICABLE LAW, IN NO EVENT WILL <?php echo APP_NAME; ?>, ITS AFFILIATES, 
                    OR THEIR LICENSORS, SERVICE PROVIDERS, EMPLOYEES, AGENTS, OFFICERS, OR DIRECTORS BE LIABLE FOR DAMAGES OF 
                    ANY KIND, UNDER ANY LEGAL THEORY, ARISING OUT OF OR IN CONNECTION WITH YOUR USE OF THE SITE, INCLUDING:
                </p>
                <ul>
                    <li>Direct, indirect, incidental, special, consequential, or punitive damages</li>
                    <li>Personal injury, pain and suffering, emotional distress</li>
                    <li>Loss of revenue, profits, business, or anticipated savings</li>
                    <li>Loss of use, goodwill, or data</li>
                    <li>Any other intangible losses</li>
                </ul>
                <p>
                    THIS LIMITATION APPLIES WHETHER THE DAMAGES ARISE FROM TORT, CONTRACT, NEGLIGENCE, STRICT LIABILITY, OR 
                    ANY OTHER LEGAL THEORY, EVEN IF WE HAVE BEEN ADVISED OF THE POSSIBILITY OF SUCH DAMAGES.
                </p>
            </div>

            <div class="terms-section">
                <h2>11. Indemnification</h2>
                <p>
                    You agree to defend, indemnify, and hold harmless <?php echo APP_NAME; ?>, its affiliates, licensors, and 
                    service providers, and its and their respective officers, directors, employees, contractors, agents, 
                    licensors, suppliers, successors, and assigns from and against any claims, liabilities, damages, judgments, 
                    awards, losses, costs, expenses, or fees (including reasonable attorneys' fees) arising out of or relating 
                    to your:
                </p>
                <ul>
                    <li>Violation of these Terms</li>
                    <li>Use of the Site</li>
                    <li>Content you submit, post, or transmit through the Site</li>
                    <li>Your violation of the rights of any third party</li>
                    <li>Your violation of any applicable laws or regulations</li>
                </ul>
            </div>

            <div class="terms-section">
                <h2>12. Governing Law and Jurisdiction</h2>
                <p>
                    These Terms and your use of the Site shall be governed by and construed in accordance with the laws of 
                    the State of New York, without regard to its conflict of law provisions. Any legal suit, action, or 
                    proceeding arising out of or related to these Terms or the Site shall be instituted exclusively in 
                    the federal or state courts located in New York County, New York.
                </p>
            </div>

            <div class="terms-section">
                <h2>13. Dispute Resolution</h2>
                
                <h3>13.1 Informal Resolution</h3>
                <p>
                    We encourage you to contact us first to seek an informal resolution to any dispute. You may contact us 
                    at <a href="mailto:<?php echo sanitize($siteSettings['site_email'] ?? 'support@iproply.com'); ?>">
                    <?php echo sanitize($siteSettings['site_email'] ?? 'support@iproply.com'); ?></a>.
                </p>

                <h3>13.2 Arbitration</h3>
                <p>
                    Any dispute arising from these Terms or your use of the Site shall be resolved through binding 
                    arbitration in accordance with the rules of the American Arbitration Association. The arbitration 
                    shall be conducted in New York, New York.
                </p>

                <h3>13.3 Class Action Waiver</h3>
                <p>
                    YOU AGREE THAT ANY PROCEEDINGS, WHETHER IN ARBITRATION OR COURT, WILL BE CONDUCTED ONLY ON AN INDIVIDUAL 
                    BASIS AND NOT IN A CLASS, CONSOLIDATED, OR REPRESENTATIVE ACTION.
                </p>
            </div>

            <div class="terms-section">
                <h2>14. Termination</h2>
                <p>
                    We may terminate or suspend your account and bar access to the Site immediately, without prior notice or 
                    liability, for any reason whatsoever, including without limitation if you breach these Terms.
                </p>
                <p>
                    Upon termination, your right to use the Site will immediately cease. All provisions of these Terms which 
                    by their nature should survive termination shall survive, including ownership provisions, warranty 
                    disclaimers, indemnity, and limitations of liability.
                </p>
            </div>

            <div class="terms-section">
                <h2>15. Fair Housing Compliance</h2>
                <p>
                    <?php echo APP_NAME; ?> is committed to compliance with all fair housing laws. We do not discriminate on 
                    the basis of race, color, national origin, religion, sex, familial status, or handicap (disability) as 
                    prohibited by federal law, or any other protected class under state or local law.
                </p>
                <p>
                    All users of our platform, including agents and property seekers, are expected to comply with all 
                    applicable fair housing laws. Any discriminatory conduct is strictly prohibited and may result in 
                    immediate termination of account access.
                </p>
            </div>

            <div class="terms-section">
                <h2>16. Miscellaneous</h2>
                
                <h3>16.1 Entire Agreement</h3>
                <p>
                    These Terms constitute the sole and entire agreement between you and <?php echo APP_NAME; ?> regarding 
                    the Site and supersede all prior and contemporaneous understandings, agreements, representations, and 
                    warranties.
                </p>

                <h3>16.2 Waiver and Severability</h3>
                <p>
                    No waiver by <?php echo APP_NAME; ?> of any term or condition set out in these Terms shall be deemed a 
                    further or continuing waiver of such term or condition. If any provision of these Terms is held to be 
                    invalid, illegal, or unenforceable, such provision shall be eliminated or limited to the minimum extent 
                    such that the remaining provisions will continue in full force and effect.
                </p>

                <h3>16.3 Assignment</h3>
                <p>
                    You may not assign or transfer these Terms, by operation of law or otherwise, without our prior written 
                    consent. We may assign these Terms at any time without notice.
                </p>
            </div>

            <div class="terms-section">
                <h2>17. Contact Information</h2>
                <p>Questions about the Terms of Service should be sent to us at:</p>
                <ul>
                    <li>
                        <strong>Email:</strong> 
                        <a href="mailto:<?php echo sanitize($siteSettings['site_email'] ?? 'legal@iproply.com'); ?>">
                            <?php echo sanitize($siteSettings['site_email'] ?? 'legal@iproply.com'); ?>
                        </a>
                    </li>
                    <li>
                        <strong>Address:</strong><br>
                        <?php echo nl2br(sanitize($siteSettings['site_address'] ?? "iProply\nAttn: Legal Department\n123 Real Estate Ave\nNew York, NY 10001")); ?>
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
<?php
/**
 * Mail Class
 * Handles email sending using PHPMailer or native PHP mail()
 *
 * FIXES APPLIED:
 * 1. Null-safe helpers (nullsafe_sanitize, nullsafe_name) prevent
 *    "Undefined array key" and "trim(): null" warnings on PHP 8+.
 * 2. send() now validates $to before attempting delivery and logs
 *    clearly when it is missing, instead of passing null to mail().
 * 3. sendInquiryToAgent() / sendAgentWelcome() guard against an
 *    incomplete $agent array before calling send().
 * 4. getInquiryEmailTemplate() uses format_price() with the correct
 *    column — property_status — not status (which is the admin workflow
 *    column in the properties table).
 * 5. SMTP-disabled notice added: when SMTP credentials are not set,
 *    a clear error_log message is written instead of silently falling
 *    back to a localhost mail() call that always fails on XAMPP.
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Mail {
    private $mailer;
    private $errors = [];
    private $useSMTP;

    public function __construct() {
        $this->useSMTP = !empty(SMTP_HOST) && !empty(SMTP_USERNAME);

        if ($this->useSMTP) {
            $this->initPHPMailer();
        }
    }

    // ------------------------------------------------------------------
    // FIX: Null-safe sanitize — avoids "trim(): null" on PHP 8+
    // ------------------------------------------------------------------
    private function nullsafe_sanitize($value, string $fallback = ''): string {
        return sanitize($value ?? $fallback);
    }

    // ------------------------------------------------------------------
    // FIX: Null-safe name builder — avoids "Undefined array key" warnings
    // ------------------------------------------------------------------
    private function nullsafe_name(array $row, string $firstKey = 'first_name', string $lastKey = 'last_name'): string {
        $first = $row[$firstKey] ?? '';
        $last  = $row[$lastKey]  ?? '';
        return trim($first . ' ' . $last) ?: 'Unknown';
    }

    /**
     * Initialize PHPMailer
     */
    private function initPHPMailer() {
        require_once BASE_PATH . '/includes/PHPMailer/PHPMailer.php';
        require_once BASE_PATH . '/includes/PHPMailer/SMTP.php';
        require_once BASE_PATH . '/includes/PHPMailer/Exception.php';

        $this->mailer = new PHPMailer(true);

        try {
            $this->mailer->isSMTP();
            $this->mailer->Host       = SMTP_HOST;
            $this->mailer->SMTPAuth   = true;
            $this->mailer->Username   = SMTP_USERNAME;
            $this->mailer->Password   = SMTP_PASSWORD;
            $this->mailer->SMTPSecure = SMTP_ENCRYPTION;
            $this->mailer->Port       = SMTP_PORT;

            $this->mailer->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            $this->mailer->CharSet = 'UTF-8';
            $this->mailer->isHTML(true);

        } catch (Exception $e) {
            error_log("PHPMailer initialization error: " . $e->getMessage());
            $this->useSMTP = false;
        }
    }

    // ------------------------------------------------------------------
    // Public send helpers
    // ------------------------------------------------------------------

    /**
     * Send inquiry notification to agent
     *
     * FIX: Guard against missing keys in $agent before calling send().
     */
    public function sendInquiryToAgent($agent, $inquiry, $property) {
        // FIX: validate the agent array has the keys we need
        if (empty($agent['email'])) {
            $msg = "sendInquiryToAgent: agent record is missing 'email'. Agent data: " . json_encode($agent);
            error_log($msg);
            $this->errors[] = $msg;
            return false;
        }

        $subject = "New Property Inquiry - " . ($property['title'] ?? 'Property');
        $body    = $this->getInquiryEmailTemplate($agent, $inquiry, $property);
        $toName  = $this->nullsafe_name($agent);

        return $this->send($agent['email'], $toName, $subject, $body);
    }

    /**
     * Send inquiry confirmation to user
     */
    public function sendInquiryConfirmation($inquiry, $property) {
        if (empty($inquiry['email'])) {
            $msg = "sendInquiryConfirmation: inquiry record is missing 'email'.";
            error_log($msg);
            $this->errors[] = $msg;
            return false;
        }

        $subject = "Your Inquiry for " . ($property['title'] ?? 'Property');
        $body    = $this->getConfirmationEmailTemplate($inquiry, $property);
        $toName  = $this->nullsafe_sanitize($inquiry['name'] ?? '', 'Valued Customer');

        return $this->send($inquiry['email'], $toName, $subject, $body);
    }

    /**
     * Send agent welcome email
     *
     * FIX: Guard against missing keys in $agent.
     */
    public function sendAgentWelcome($agent, $password = null) {
        if (empty($agent['email'])) {
            $msg = "sendAgentWelcome: agent record is missing 'email'. Agent data: " . json_encode($agent);
            error_log($msg);
            $this->errors[] = $msg;
            return false;
        }

        $subject = "Welcome to " . APP_NAME;
        $body    = $this->getAgentWelcomeTemplate($agent, $password);
        $toName  = $this->nullsafe_name($agent);

        return $this->send($agent['email'], $toName, $subject, $body);
    }

    /**
     * Send password reset email
     */
    public function sendPasswordReset($email, $name, $resetToken) {
        if (empty($email)) {
            $msg = "sendPasswordReset: email address is empty.";
            error_log($msg);
            $this->errors[] = $msg;
            return false;
        }

        $resetUrl = base_url('reset-password.php?token=' . $resetToken);
        $subject  = "Password Reset Request - " . APP_NAME;
        $body     = $this->getPasswordResetTemplate($name ?? 'User', $resetUrl);

        return $this->send($email, $name ?? 'User', $subject, $body);
    }

    /**
     * Send generic email
     *
     * FIX: Validate $to before attempting delivery.
     *      On XAMPP (no SMTP configured), log a clear warning instead
     *      of silently calling mail() against a non-existent mailserver.
     */
    public function send($to, $toName, $subject, $body, $attachments = []) {
        // FIX: catch empty recipient before it reaches mail() / PHPMailer
        if (empty($to)) {
            $msg = "Mail::send() called with empty \$to address. Subject: {$subject}";
            error_log($msg);
            $this->errors[] = $msg;
            return false;
        }

        // FIX: warn clearly when SMTP is not configured (common on XAMPP)
        if (!$this->useSMTP) {
            error_log(
                "Mail::send() — SMTP credentials are not configured in config.php. " .
                "Falling back to PHP mail() which typically fails on localhost/XAMPP. " .
                "Set SMTP_HOST, SMTP_USERNAME, and SMTP_PASSWORD to send real emails. " .
                "Subject: {$subject} | To: {$to}"
            );
        }

        try {
            if ($this->useSMTP && $this->mailer) {
                return $this->sendWithPHPMailer($to, $toName, $subject, $body, $attachments);
            } else {
                return $this->sendWithNativeMail($to, $toName, $subject, $body);
            }
        } catch (Exception $e) {
            error_log("Email sending error: " . $e->getMessage());
            $this->errors[] = "Failed to send email: " . $e->getMessage();
            return false;
        } catch (\Throwable $e) {
            // Catch any other PHP error (e.g. from mail() on PHP 8+)
            error_log("Email sending throwable: " . $e->getMessage());
            $this->errors[] = "Failed to send email: " . $e->getMessage();
            return false;
        }
    }

    // ------------------------------------------------------------------
    // Private delivery methods
    // ------------------------------------------------------------------

    /**
     * Send using PHPMailer
     */
    private function sendWithPHPMailer($to, $toName, $subject, $body, $attachments = []) {
        $this->mailer->clearAddresses();
        $this->mailer->clearAttachments();

        $this->mailer->addAddress($to, $toName);

        foreach ($attachments as $attachment) {
            if (file_exists($attachment)) {
                $this->mailer->addAttachment($attachment);
            }
        }

        $this->mailer->Subject = $subject;
        $this->mailer->Body    = $body;
        $this->mailer->AltBody = strip_tags($body);

        return $this->mailer->send();
    }

    /**
     * Send using native PHP mail()
     *
     * FIX: "From" header was formatted incorrectly — name and email were swapped.
     * Correct format: From: Display Name <email@example.com>
     */
    private function sendWithNativeMail($to, $toName, $subject, $body) {
        $headers  = "From: " . SMTP_FROM_NAME . " <" . SMTP_FROM_EMAIL . ">\r\n"; // FIX: was reversed
        $headers .= "Reply-To: " . SMTP_FROM_EMAIL . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

        $fullBody = $this->getEmailWrapper($body);

        return mail($to, $subject, $fullBody, $headers);
    }

    // ------------------------------------------------------------------
    // Email templates
    // ------------------------------------------------------------------

    /**
     * Get email wrapper with styling
     */
    private function getEmailWrapper($content) {
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . APP_NAME . '</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; }
        .header { background-color: #1e3b5a; padding: 30px; text-align: center; }
        .header h1 { color: #ffffff; margin: 0; font-size: 28px; }
        .content { padding: 30px; }
        .footer { background-color: #f5f5f5; padding: 20px; text-align: center; font-size: 12px; color: #666; }
        .button { display: inline-block; padding: 12px 30px; background-color: #1e3b5a; color: #ffffff; text-decoration: none; border-radius: 5px; margin: 20px 0; }
        .button:hover { background-color: #2c5282; }
        .info-box { background-color: #f5f5f5; padding: 20px; border-radius: 5px; margin: 20px 0; }
        .property-card { border: 1px solid #e0e0e0; border-radius: 5px; padding: 15px; margin: 15px 0; }
        h2 { color: #1e3b5a; }
        .label { font-weight: bold; color: #666; }
        .value { color: #333; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>' . APP_NAME . '</h1>
        </div>
        <div class="content">
            ' . $content . '
        </div>
        <div class="footer">
            <p>&copy; ' . date('Y') . ' ' . APP_NAME . '. All rights reserved.</p>
            <p>' . SMTP_FROM_EMAIL . '</p>
        </div>
    </div>
</body>
</html>';
    }

    /**
     * Inquiry email template for agent
     *
     * FIX: format_price() now uses $property['property_status'] (the real-estate
     *      status column: for-sale / for-rent) instead of $property['status']
     *      (the admin workflow column: pending / active / inactive).
     */
    private function getInquiryEmailTemplate($agent, $inquiry, $property) {
        $propertyUrl = base_url('property.php?slug=' . ($property['slug'] ?? ''));

        return '
        <h2>Hello ' . $this->nullsafe_sanitize($agent['first_name'] ?? '') . ',</h2>
        <p>You have received a new inquiry for your property listing.</p>

        <div class="property-card">
            <h3>' . $this->nullsafe_sanitize($property['title'] ?? '') . '</h3>
            <p>
                <span class="label">Price:</span>
                <span class="value">' . format_price($property['price'] ?? 0, $property['property_status'] ?? 'sale') . '</span>
            </p>
            <p>
                <span class="label">Location:</span>
                <span class="value">'
                    . $this->nullsafe_sanitize($property['city'] ?? '') . ', '
                    . $this->nullsafe_sanitize($property['state'] ?? '')
                . '</span>
            </p>
            <p><a href="' . $propertyUrl . '">View Property</a></p>
        </div>

        <div class="info-box">
            <h3>Inquiry Details</h3>
            <p><span class="label">Name:</span>  <span class="value">' . $this->nullsafe_sanitize($inquiry['name']  ?? '') . '</span></p>
            <p><span class="label">Email:</span> <span class="value"><a href="mailto:' . $this->nullsafe_sanitize($inquiry['email'] ?? '') . '">' . $this->nullsafe_sanitize($inquiry['email'] ?? '') . '</a></span></p>
            <p><span class="label">Phone:</span> <span class="value">' . $this->nullsafe_sanitize($inquiry['phone'] ?? 'Not provided') . '</span></p>
            <p><span class="label">Message:</span></p>
            <p style="background:#fff;padding:15px;border-left:3px solid #1e3b5a;">'
                . nl2br($this->nullsafe_sanitize($inquiry['message'] ?? ''))
            . '</p>
        </div>

        <p style="text-align:center;">
            <a href="mailto:' . $this->nullsafe_sanitize($inquiry['email'] ?? '') . '?subject=Re:+' . rawurlencode('Inquiry about ' . ($property['title'] ?? '')) . '" class="button">Reply to Inquiry</a>
        </p>

        <p>You can also manage this inquiry in your <a href="' . base_url('agent/inquiries.php') . '">Agent Dashboard</a>.</p>
        ';
    }

    /**
     * Confirmation email template for the enquiring user
     *
     * FIX: same property_status fix as above.
     */
    private function getConfirmationEmailTemplate($inquiry, $property) {
        $propertyUrl = base_url('property.php?slug=' . ($property['slug'] ?? ''));

        return '
        <h2>Hello ' . $this->nullsafe_sanitize($inquiry['name'] ?? '') . ',</h2>
        <p>Thank you for your interest in our property. We have received your inquiry and will get back to you shortly.</p>

        <div class="property-card">
            <h3>' . $this->nullsafe_sanitize($property['title'] ?? '') . '</h3>
            <p>
                <span class="label">Price:</span>
                <span class="value">' . format_price($property['price'] ?? 0, $property['property_status'] ?? 'sale') . '</span>
            </p>
            <p>
                <span class="label">Location:</span>
                <span class="value">'
                    . $this->nullsafe_sanitize($property['city'] ?? '') . ', '
                    . $this->nullsafe_sanitize($property['state'] ?? '')
                . '</span>
            </p>
            <p><a href="' . $propertyUrl . '">View Property</a></p>
        </div>

        <div class="info-box">
            <h3>Your Inquiry</h3>
            <p>' . nl2br($this->nullsafe_sanitize($inquiry['message'] ?? '')) . '</p>
        </div>

        <p>If you have any questions, please don\'t hesitate to contact us.</p>
        <p>Best regards,<br>The ' . APP_NAME . ' Team</p>
        ';
    }

    /**
     * Agent welcome email template
     */
    private function getAgentWelcomeTemplate($agent, $password = null) {
        $loginUrl = base_url('agent/login.php');

        $content = '
        <h2>Welcome to ' . APP_NAME . ', ' . $this->nullsafe_sanitize($agent['first_name'] ?? '') . '!</h2>
        <p>Your agent account has been created successfully. We\'re excited to have you on board.</p>
        ';

        if ($password) {
            $content .= '
            <div class="info-box">
                <h3>Your Login Credentials</h3>
                <p><span class="label">Username:</span> <span class="value">' . $this->nullsafe_sanitize($agent['username'] ?? '') . '</span></p>
                <p><span class="label">Password:</span> <span class="value">' . $this->nullsafe_sanitize($password) . '</span></p>
                <p style="color:#e53e3e;font-size:12px;">Please change your password after your first login.</p>
            </div>
            ';
        }

        $content .= '
        <p style="text-align:center;">
            <a href="' . $loginUrl . '" class="button">Login to Your Account</a>
        </p>

        <p>With your agent account, you can:</p>
        <ul>
            <li>Add and manage property listings</li>
            <li>Track inquiries from potential buyers</li>
            <li>Update your profile and contact information</li>
            <li>View your performance statistics</li>
        </ul>

        <p>If you have any questions, please don\'t hesitate to contact our support team.</p>
        <p>Best regards,<br>The ' . APP_NAME . ' Team</p>
        ';

        return $content;
    }

    /**
     * Password reset email template
     */
    private function getPasswordResetTemplate($name, $resetUrl) {
        return '
        <h2>Hello ' . $this->nullsafe_sanitize($name) . ',</h2>
        <p>We received a request to reset your password for your ' . APP_NAME . ' account.</p>

        <p style="text-align:center;">
            <a href="' . $resetUrl . '" class="button">Reset Your Password</a>
        </p>

        <p>Or copy and paste this link into your browser:</p>
        <p style="word-break:break-all;background:#f5f5f5;padding:10px;font-size:12px;">' . $resetUrl . '</p>

        <p>This link will expire in 24 hours for security reasons.</p>
        <p>If you did not request a password reset, please ignore this email.</p>

        <p>Best regards,<br>The ' . APP_NAME . ' Team</p>
        ';
    }

    // ------------------------------------------------------------------
    // Utility
    // ------------------------------------------------------------------

    public function getErrors(): array {
        return $this->errors;
    }
}
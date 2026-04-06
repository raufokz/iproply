<?php
/**
 * Authentication Class
 * Handles user authentication for Admin and Agent roles
 */

class Auth {
    private $db;
    private $errors = [];

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Admin Login
     */
    public function adminLogin($username, $password) {
        $this->errors = [];

        // Validation
        if (empty($username)) {
            $this->errors[] = "Username is required";
        }
        if (empty($password)) {
            $this->errors[] = "Password is required";
        }

        if (!empty($this->errors)) {
            return false;
        }

        // Check login attempts
        if ($this->isLockedOut($username)) {
            $this->errors[] = "Too many failed attempts. Please try again later.";
            return false;
        }

        // Find admin
        $admin = $this->db->selectOne('admins', '*', 'username = :username OR email = :email', [
            'username' => $username,
            'email' => $username
        ]);

        if (!$admin) {
            $this->recordFailedAttempt($username);
            $this->errors[] = "Invalid username or password";
            return false;
        }

        // Check status
        if ($admin['status'] !== 'active') {
            $this->errors[] = "Your account has been " . $admin['status'];
            return false;
        }

        // Verify password
        if (!password_verify($password, $admin['password'])) {
            $this->recordFailedAttempt($username);
            $this->errors[] = "Invalid username or password";
            return false;
        }

        // Clear failed attempts
        $this->clearFailedAttempts($username);

        // Update last login
        $this->db->update('admins', ['last_login' => date('Y-m-d H:i:s')], 'id = :id', ['id' => $admin['id']]);

        // Set session
        $_SESSION['user_id'] = $admin['id'];
        $_SESSION['user_type'] = 'admin';
        $_SESSION['user_name'] = $admin['first_name'] . ' ' . $admin['last_name'];
        $_SESSION['user_email'] = $admin['email'];
        $_SESSION['user_avatar'] = $admin['avatar'];
        $_SESSION['user_role'] = $admin['role'];
        $_SESSION['login_time'] = time();

        // Regenerate session ID for security
        session_regenerate_id(true);

        return true;
    }

    /**
     * Agent Login
     */
    public function agentLogin($username, $password) {
        $this->errors = [];

        // Validation
        if (empty($username)) {
            $this->errors[] = "Username is required";
        }
        if (empty($password)) {
            $this->errors[] = "Password is required";
        }

        if (!empty($this->errors)) {
            return false;
        }

        // Check login attempts
        if ($this->isLockedOut($username)) {
            $this->errors[] = "Too many failed attempts. Please try again later.";
            return false;
        }

        // Find agent
        $agent = $this->db->selectOne('agents', '*', 'username = :username OR email = :email', [
            'username' => $username,
            'email' => $username
        ]);

        if (!$agent) {
            $this->recordFailedAttempt($username);
            $this->errors[] = "Invalid username or password";
            return false;
        }

        // Check status
        if ($agent['status'] !== 'active') {
            if ($agent['status'] === 'pending') {
                $this->errors[] = "Your account is pending approval";
            } else {
                $this->errors[] = "Your account has been " . $agent['status'];
            }
            return false;
        }

        // Verify password
        if (!password_verify($password, $agent['password'])) {
            $this->recordFailedAttempt($username);
            $this->errors[] = "Invalid username or password";
            return false;
        }

        // Clear failed attempts
        $this->clearFailedAttempts($username);

        // Update last login
        $this->db->update('agents', ['last_login' => date('Y-m-d H:i:s')], 'id = :id', ['id' => $agent['id']]);

        // Set session
        $_SESSION['user_id'] = $agent['id'];
        $_SESSION['user_type'] = 'agent';
        $_SESSION['user_name'] = $agent['first_name'] . ' ' . $agent['last_name'];
        $_SESSION['user_email'] = $agent['email'];
        $_SESSION['user_avatar'] = $agent['avatar'];
        $_SESSION['login_time'] = time();

        // Regenerate session ID for security
        session_regenerate_id(true);

        return true;
    }

    /**
     * Logout
     */
    public function logout() {
        // Clear session data
        $_SESSION = [];

        // Destroy session cookie
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', [
                'expires' => time() - 3600,
                'path' => '/',
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Strict'
            ]);
        }

        // Destroy session
        session_destroy();

        return true;
    }

    /**
     * Check if user is logged in
     */
    public function check() {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
            return false;
        }

        // Check session expiration
        if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > SESSION_LIFETIME)) {
            $this->logout();
            return false;
        }

        // Update login time to extend session
        $_SESSION['login_time'] = time();

        return true;
    }

    /**
     * Get current user
     */
    public function user() {
        if (!$this->check()) {
            return null;
        }

        $table = $_SESSION['user_type'] === 'admin' ? 'admins' : 'agents';
        return $this->db->selectOne($table, '*', 'id = :id', ['id' => $_SESSION['user_id']]);
    }

    /**
     * Change password
     */
    public function changePassword($userId, $userType, $currentPassword, $newPassword) {
        $this->errors = [];

        $table = $userType === 'admin' ? 'admins' : 'agents';
        $user = $this->db->selectOne($table, '*', 'id = :id', ['id' => $userId]);

        if (!$user) {
            $this->errors[] = "User not found";
            return false;
        }

        // Verify current password
        if (!password_verify($currentPassword, $user['password'])) {
            $this->errors[] = "Current password is incorrect";
            return false;
        }

        // Validate new password
        if (strlen($newPassword) < PASSWORD_MIN_LENGTH) {
            $this->errors[] = "Password must be at least " . PASSWORD_MIN_LENGTH . " characters";
            return false;
        }

        // Hash and update password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $this->db->update($table, ['password' => $hashedPassword], 'id = :id', ['id' => $userId]);

        return true;
    }

    /**
     * Reset password (admin only)
     */
    public function resetPassword($userType, $userId, $newPassword) {
        $table = $userType === 'admin' ? 'admins' : 'agents';
        
        if (strlen($newPassword) < PASSWORD_MIN_LENGTH) {
            $this->errors[] = "Password must be at least " . PASSWORD_MIN_LENGTH . " characters";
            return false;
        }

        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $this->db->update($table, ['password' => $hashedPassword], 'id = :id', ['id' => $userId]);

        return true;
    }

    /**
     * Check if account is locked out
     */
    private function isLockedOut($username) {
        $key = 'login_attempts_' . md5($username);
        $attempts = $_SESSION[$key]['count'] ?? 0;
        $lastAttempt = $_SESSION[$key]['time'] ?? 0;

        if ($attempts >= MAX_LOGIN_ATTEMPTS && (time() - $lastAttempt) < LOGIN_LOCKOUT_TIME) {
            return true;
        }

        // Reset if lockout time has passed
        if ((time() - $lastAttempt) > LOGIN_LOCKOUT_TIME) {
            unset($_SESSION[$key]);
        }

        return false;
    }

    /**
     * Record failed login attempt
     */
    private function recordFailedAttempt($username) {
        $key = 'login_attempts_' . md5($username);
        
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = ['count' => 0, 'time' => time()];
        }
        
        $_SESSION[$key]['count']++;
        $_SESSION[$key]['time'] = time();
    }

    /**
     * Clear failed login attempts
     */
    private function clearFailedAttempts($username) {
        $key = 'login_attempts_' . md5($username);
        unset($_SESSION[$key]);
    }

    /**
     * Get errors
     */
    public function getErrors() {
        return $this->errors;
    }

    /**
     * Agent registration
     */
    public function agentRegister($data) {
        $this->errors = [];

        $username = trim($data['username'] ?? '');
        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';
        $confirmPassword = $data['confirm_password'] ?? '';
        $firstName = trim($data['first_name'] ?? '');
        $lastName = trim($data['last_name'] ?? '');

        if (empty($firstName) || empty($lastName)) {
            $this->errors[] = "First name and last name are required";
        }

        if (empty($username)) {
            $this->errors[] = "Username is required";
        }

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = "A valid email address is required";
        }

        if (empty($password)) {
            $this->errors[] = "Password is required";
        } elseif (strlen($password) < PASSWORD_MIN_LENGTH) {
            $this->errors[] = "Password must be at least " . PASSWORD_MIN_LENGTH . " characters";
        }

        if ($password !== $confirmPassword) {
            $this->errors[] = "Passwords do not match";
        }

        if ($this->db->exists('agents', 'username = :username', ['username' => $username])) {
            $this->errors[] = "Username already exists";
        }

        if ($this->db->exists('agents', 'email = :email', ['email' => $email])) {
            $this->errors[] = "Email is already registered";
        }

        if (!empty($this->errors)) {
            return false;
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $agentData = [
            'username' => $username,
            'email' => $email,
            'password' => $hashedPassword,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'phone' => trim($data['phone'] ?? ''),
            'mobile' => trim($data['mobile'] ?? ''),
            'license_number' => trim($data['license_number'] ?? ''),
            'years_experience' => intval($data['years_experience'] ?? 0),
            'specialties' => trim($data['specialties'] ?? ''),
            'address' => trim($data['address'] ?? ''),
            'city' => trim($data['city'] ?? ''),
            'state' => trim($data['state'] ?? ''),
            'zip_code' => trim($data['zip_code'] ?? ''),
            'website' => trim($data['website'] ?? ''),
            'facebook' => trim($data['facebook'] ?? ''),
            'twitter' => trim($data['twitter'] ?? ''),
            'instagram' => trim($data['instagram'] ?? ''),
            'linkedin' => trim($data['linkedin'] ?? ''),
            'status' => 'pending',
            'email_notifications' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->db->insert('agents', $agentData);

        return true;
    }

    /**
     * Require authentication
     */
    public function requireAuth() {
        if (!$this->check()) {
            set_flash_message('error', 'Please login to access this page');
            redirect('login.php');
        }
    }

    /**
     * Require admin authentication
     */
    public function requireAdmin() {
        $this->requireAuth();
        
        if ($_SESSION['user_type'] !== 'admin') {
            set_flash_message('error', 'You do not have permission to access this page');
            redirect('index.php');
        }
    }

    /**
     * Require agent authentication
     */
    public function requireAgent() {
        $this->requireAuth();
        
        if ($_SESSION['user_type'] !== 'agent') {
            set_flash_message('error', 'You do not have permission to access this page');
            redirect('index.php');
        }
    }
}
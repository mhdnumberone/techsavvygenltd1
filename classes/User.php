<?php
/**
 * فئة المستخدم
 * User class
 */

class User {
    private $db;
    private $id;
    private $username;
    private $email;
    private $firstName;
    private $lastName;
    private $role;
    private $status;
    private $profileImage;
    private $walletBalance;
    private $preferredLanguage;

    /**
     * إنشاء كائن المستخدم
     * Create user object
     */
    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * تسجيل مستخدم جديد
     * Register new user
     */
    public function register($userData) {
        // التحقق من وجود اسم المستخدم أو البريد الإلكتروني
        // Check if username or email already exists
        if ($this->db->exists('users', 'username', $userData['username'])) {
            throw new Exception(translate('username_already_exists'));
        }

        if ($this->db->exists('users', 'email', $userData['email'])) {
            throw new Exception(translate('email_already_exists'));
        }

        // تشفير كلمة المرور
        // Hash password
        $userData['password'] = hashPassword($userData['password']);

        // إنشاء رمز التحقق
        // Create verification token
        $userData['verification_token'] = bin2hex(random_bytes(16));
        $userData['is_verified'] = 0;
        $userData['registration_date'] = date('Y-m-d H:i:s');
        $userData['status'] = USER_STATUS_ACTIVE;
        $userData['role'] = USER_ROLE_CUSTOMER;

        // إدراج المستخدم في قاعدة البيانات
        // Insert user into database
        $userId = $this->db->insert('users', $userData);

        // إرسال بريد التحقق
        // Send verification email
        // $this->sendVerificationEmail($userData['email'], $userData['verification_token']);

        return $userId;
    }

    /**
     * تسجيل الدخول
     * Login
     */
    public function login($username, $password) {
        // الحصول على بيانات المستخدم
        // Get user data
        $user = $this->db->getRow("SELECT * FROM users WHERE (username = :username OR email = :email) AND status != :banned", [
            ':username' => $username,
            ':email' => $username,
            ':banned' => USER_STATUS_BANNED
        ]);

        if (!$user) {
            throw new Exception(translate('invalid_credentials'));
        }

        // التحقق من كلمة المرور
        // Verify password
        if (!verifyPassword($password, $user['password'])) {
            throw new Exception(translate('invalid_credentials'));
        }

        // التحقق من حالة المستخدم
        // Check user status
        if ($user['status'] !== USER_STATUS_ACTIVE) {
            throw new Exception(translate('account_inactive'));
        }

        // تحديث آخر تسجيل دخول
        // Update last login
        $this->db->update('users', ['last_login' => date('Y-m-d H:i:s')], 'id = :id', [':id' => $user['id']]);

        // تعيين بيانات الجلسة
        // Set session data
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['preferred_language'] = $user['preferred_language'];

        // تسجيل نشاط تسجيل الدخول
        // Log login activity
        logActivity($user['id'], 'login', 'User logged in');

        return $user;
    }

    /**
     * تسجيل الخروج
     * Logout
     */
    public function logout() {
        // تسجيل نشاط تسجيل الخروج إذا كان المستخدم مسجل الدخول
        // Log logout activity if user is logged in
        if (isset($_SESSION['user_id'])) {
            logActivity($_SESSION['user_id'], 'logout', 'User logged out');
        }

        // حذف بيانات الجلسة
        // Delete session data
        session_unset();
        session_destroy();
        
        // إعادة بدء الجلسة
        // Restart session
        session_start();
    }

    /**
     * الحصول على بيانات المستخدم
     * Get user data
     */
    public function getById($userId) {
        $user = $this->db->getRow("SELECT * FROM users WHERE id = :id", [':id' => $userId]);
        
        if (!$user) {
            return false;
        }

        // حذف كلمة المرور من النتيجة
        // Remove password from result
        unset($user['password']);
        unset($user['verification_token']);
        unset($user['reset_token']);
        unset($user['reset_token_expiry']);

        return $user;
    }

    /**
     * تحديث بيانات المستخدم
     * Update user data
     */
    public function update($userId, $userData) {
        // التحقق من وجود اسم المستخدم أو البريد الإلكتروني
        // Check if username or email already exists
        if (isset($userData['username']) && $this->db->exists('users', 'username', $userData['username'], $userId)) {
            throw new Exception(translate('username_already_exists'));
        }

        if (isset($userData['email']) && $this->db->exists('users', 'email', $userData['email'], $userId)) {
            throw new Exception(translate('email_already_exists'));
        }

        // تحديث بيانات المستخدم
        // Update user data
        $result = $this->db->update('users', $userData, 'id = :id', [':id' => $userId]);

        // تسجيل نشاط تحديث البيانات
        // Log update activity
        logActivity($userId, 'update_profile', 'User updated profile');

        return $result;
    }

    /**
     * تغيير كلمة المرور
     * Change password
     */
    public function changePassword($userId, $currentPassword, $newPassword) {
        // الحصول على كلمة المرور الحالية
        // Get current password
        $user = $this->db->getRow("SELECT password FROM users WHERE id = :id", [':id' => $userId]);

        if (!$user) {
            throw new Exception(translate('user_not_found'));
        }

        // التحقق من كلمة المرور الحالية
        // Verify current password
        if (!verifyPassword($currentPassword, $user['password'])) {
            throw new Exception(translate('current_password_incorrect'));
        }

        // تشفير كلمة المرور الجديدة
        // Hash new password
        $hashedPassword = hashPassword($newPassword);

        // تحديث كلمة المرور
        // Update password
        $result = $this->db->update('users', ['password' => $hashedPassword], 'id = :id', [':id' => $userId]);

        // تسجيل نشاط تغيير كلمة المرور
        // Log password change activity
        logActivity($userId, 'change_password', 'User changed password');

        return $result;
    }

    /**
     * إعادة تعيين كلمة المرور
     * Reset password
     */
    public function resetPassword($email) {
        // التحقق من وجود البريد الإلكتروني
        // Check if email exists
        $user = $this->db->getRow("SELECT id, email FROM users WHERE email = :email", [':email' => $email]);

        if (!$user) {
            throw new Exception(translate('email_not_found'));
        }

        // إنشاء رمز إعادة تعيين كلمة المرور
        // Create password reset token
        $resetToken = bin2hex(random_bytes(16));
        $resetTokenExpiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // تحديث بيانات المستخدم
        // Update user data
        $this->db->update('users', [
            'reset_token' => $resetToken,
            'reset_token_expiry' => $resetTokenExpiry
        ], 'id = :id', [':id' => $user['id']]);

        // إرسال بريد إعادة تعيين كلمة المرور
        // Send password reset email
        // $this->sendResetPasswordEmail($user['email'], $resetToken);

        return true;
    }

    /**
     * التحقق من رمز إعادة تعيين كلمة المرور
     * Verify password reset token
     */
    public function verifyResetToken($token) {
        $user = $this->db->getRow("SELECT id FROM users WHERE reset_token = :token AND reset_token_expiry > NOW()", [':token' => $token]);

        return $user ? $user['id'] : false;
    }

    /**
     * تعيين كلمة مرور جديدة بعد إعادة التعيين
     * Set new password after reset
     */
    public function setNewPassword($userId, $newPassword) {
        // تشفير كلمة المرور الجديدة
        // Hash new password
        $hashedPassword = hashPassword($newPassword);

        // تحديث كلمة المرور وحذف رمز إعادة التعيين
        // Update password and remove reset token
        $result = $this->db->update('users', [
            'password' => $hashedPassword,
            'reset_token' => null,
            'reset_token_expiry' => null
        ], 'id = :id', [':id' => $userId]);

        // تسجيل نشاط إعادة تعيين كلمة المرور
        // Log password reset activity
        logActivity($userId, 'reset_password', 'User reset password');

        return $result;
    }

    /**
     * التحقق من البريد الإلكتروني
     * Verify email
     */
    public function verifyEmail($token) {
        $user = $this->db->getRow("SELECT id FROM users WHERE verification_token = :token AND is_verified = 0", [':token' => $token]);

        if (!$user) {
            return false;
        }

        // تحديث حالة التحقق
        // Update verification status
        $result = $this->db->update('users', [
            'is_verified' => 1,
            'verification_token' => null
        ], 'id = :id', [':id' => $user['id']]);

        // تسجيل نشاط التحقق من البريد الإلكتروني
        // Log email verification activity
        logActivity($user['id'], 'verify_email', 'User verified email');

        return $result;
    }

    /**
     * الحصول على قائمة المستخدمين
     * Get users list
     */
    public function getUsers($page = 1, $perPage = ITEMS_PER_PAGE, $search = '', $role = '', $status = '') {
        $where = '';
        $params = [];

        if (!empty($search)) {
            $where .= "username LIKE :search OR email LIKE :search OR first_name LIKE :search OR last_name LIKE :search";
            $params[':search'] = "%{$search}%";
        }

        if (!empty($role)) {
            if (!empty($where)) {
                $where .= " AND ";
            }
            $where .= "role = :role";
            $params[':role'] = $role;
        }

        if (!empty($status)) {
            if (!empty($where)) {
                $where .= " AND ";
            }
            $where .= "status = :status";
            $params[':status'] = $status;
        }

        return $this->db->getPaginated('users', $page, $perPage, $where, $params, 'registration_date DESC');
    }

    /**
     * حذف مستخدم
     * Delete user
     */
    public function delete($userId) {
        // التحقق من وجود المستخدم
        // Check if user exists
        $user = $this->getById($userId);

        if (!$user) {
            throw new Exception(translate('user_not_found'));
        }

        // حذف المستخدم
        // Delete user
        $result = $this->db->delete('users', 'id = :id', [':id' => $userId]);

        // تسجيل نشاط حذف المستخدم
        // Log user deletion activity
        logActivity($_SESSION['user_id'], 'delete_user', 'User deleted: ' . $user['username']);

        return $result;
    }

    /**
     * تحديث حالة المستخدم
     * Update user status
     */
    public function updateStatus($userId, $status) {
        // التحقق من وجود المستخدم
        // Check if user exists
        $user = $this->getById($userId);

        if (!$user) {
            throw new Exception(translate('user_not_found'));
        }

        // تحديث حالة المستخدم
        // Update user status
        $result = $this->db->update('users', ['status' => $status], 'id = :id', [':id' => $userId]);

        // تسجيل نشاط تحديث حالة المستخدم
        // Log user status update activity
        logActivity($_SESSION['user_id'], 'update_user_status', 'User status updated: ' . $user['username'] . ' to ' . $status);

        return $result;
    }

    /**
     * تحديث رصيد المحفظة
     * Update wallet balance
     */
    public function updateWalletBalance($userId, $amount, $operation = 'add') {
        // التحقق من وجود المستخدم
        // Check if user exists
        $user = $this->getById($userId);

        if (!$user) {
            throw new Exception(translate('user_not_found'));
        }

        // حساب الرصيد الجديد
        // Calculate new balance
        $currentBalance = $user['wallet_balance'];
        $newBalance = $operation === 'add' ? $currentBalance + $amount : $currentBalance - $amount;

        if ($newBalance < 0) {
            throw new Exception(translate('insufficient_wallet_balance'));
        }

        // تحديث رصيد المحفظة
        // Update wallet balance
        $result = $this->db->update('users', ['wallet_balance' => $newBalance], 'id = :id', [':id' => $userId]);

        // تسجيل نشاط تحديث رصيد المحفظة
        // Log wallet balance update activity
        $action = $operation === 'add' ? 'add_to_wallet' : 'subtract_from_wallet';
        $details = $operation === 'add' ? 'Added ' : 'Subtracted ';
        $details .= formatPrice($amount) . ' to/from wallet. New balance: ' . formatPrice($newBalance);
        
        logActivity($userId, $action, $details);

        return $result;
    }

    /**
     * إرسال بريد التحقق
     * Send verification email
     */
    private function sendVerificationEmail($email, $token) {
        // تنفيذ إرسال البريد الإلكتروني
        // Implement email sending
        // ...
    }

    /**
     * إرسال بريد إعادة تعيين كلمة المرور
     * Send password reset email
     */
    private function sendResetPasswordEmail($email, $token) {
        // تنفيذ إرسال البريد الإلكتروني
        // Implement email sending
        // ...
    }
}

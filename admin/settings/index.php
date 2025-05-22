<?php
/**
 * صفحة إدارة الإعدادات
 * Settings Management Page
 */

// تضمين ملفات الإعدادات والوظائف المساعدة
// Include configuration and helper files
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// بدء الجلسة
// Start session
session_start();

// التحقق من تسجيل الدخول والصلاحيات
// Check login and permissions
if (!isLoggedIn() || !isAdmin()) {
    redirect('../../login.php');
    exit;
}

// تهيئة متغيرات الصفحة
// Initialize page variables
$pageTitle = 'إدارة الإعدادات | ' . SITE_NAME;
$activeMenu = 'settings';

// تهيئة قاعدة البيانات
// Initialize database
$db = new Database();
$conn = $db->getConnection();

// تحديد الإجراء المطلوب
// Determine requested action
$action = isset($_GET['section']) ? $_GET['section'] : 'general';

// معالجة الإجراءات
// Process actions
switch ($action) {
    case 'general':
        // إعدادات عامة
        // General settings
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // التحقق من البيانات
            // Validate data
            $site_name = trim($_POST['site_name']);
            $site_description = trim($_POST['site_description']);
            $site_email = trim($_POST['site_email']);
            $site_phone = trim($_POST['site_phone']);
            $site_address = trim($_POST['site_address']);
            $site_currency = trim($_POST['site_currency']);
            $site_language = trim($_POST['site_language']);
            $site_timezone = trim($_POST['site_timezone']);
            
            $errors = [];
            
            if (empty($site_name)) {
                $errors[] = 'اسم الموقع مطلوب';
            }
            
            if (empty($site_email)) {
                $errors[] = 'البريد الإلكتروني للموقع مطلوب';
            } elseif (!filter_var($site_email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'البريد الإلكتروني للموقع غير صالح';
            }
            
            // إذا لم تكن هناك أخطاء، تحديث الإعدادات
            // If no errors, update settings
            if (empty($errors)) {
                // تحديث الإعدادات
                // Update settings
                updateSetting('site_name', $site_name);
                updateSetting('site_description', $site_description);
                updateSetting('site_email', $site_email);
                updateSetting('site_phone', $site_phone);
                updateSetting('site_address', $site_address);
                updateSetting('site_currency', $site_currency);
                updateSetting('site_language', $site_language);
                updateSetting('site_timezone', $site_timezone);
                
                // إضافة إشعار
                // Add notification
                addNotification('تم تحديث الإعدادات العامة', 'settings');
                
                // إعادة التوجيه إلى صفحة الإعدادات
                // Redirect to settings page
                redirect('index.php?section=general&success=تم تحديث الإعدادات بنجاح');
                exit;
            }
        }
        
        // الحصول على الإعدادات الحالية
        // Get current settings
        $settings = [
            'site_name' => getSetting('site_name'),
            'site_description' => getSetting('site_description'),
            'site_email' => getSetting('site_email'),
            'site_phone' => getSetting('site_phone'),
            'site_address' => getSetting('site_address'),
            'site_currency' => getSetting('site_currency'),
            'site_language' => getSetting('site_language'),
            'site_timezone' => getSetting('site_timezone')
        ];
        
        // تضمين ملف الرأس
        // Include header file
        include '../includes/header.php';
        
        // عرض نموذج الإعدادات العامة
        // Display general settings form
        include 'templates/general_settings.php';
        
        break;
        
    case 'payment':
        // إعدادات الدفع
        // Payment settings
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // التحقق من البيانات
            // Validate data
            $payment_methods = isset($_POST['payment_methods']) ? $_POST['payment_methods'] : [];
            $paypal_email = trim($_POST['paypal_email']);
            $paypal_sandbox = isset($_POST['paypal_sandbox']) ? 1 : 0;
            $stripe_public_key = trim($_POST['stripe_public_key']);
            $stripe_secret_key = trim($_POST['stripe_secret_key']);
            $stripe_webhook_secret = trim($_POST['stripe_webhook_secret']);
            $bank_account_name = trim($_POST['bank_account_name']);
            $bank_account_number = trim($_POST['bank_account_number']);
            $bank_name = trim($_POST['bank_name']);
            $bank_branch = trim($_POST['bank_branch']);
            $bank_swift = trim($_POST['bank_swift']);
            
            $errors = [];
            
            if (in_array('paypal', $payment_methods) && empty($paypal_email)) {
                $errors[] = 'البريد الإلكتروني لحساب PayPal مطلوب';
            }
            
            if (in_array('stripe', $payment_methods) && (empty($stripe_public_key) || empty($stripe_secret_key))) {
                $errors[] = 'مفاتيح Stripe مطلوبة';
            }
            
            if (in_array('bank_transfer', $payment_methods) && (empty($bank_account_name) || empty($bank_account_number) || empty($bank_name))) {
                $errors[] = 'معلومات الحساب البنكي مطلوبة';
            }
            
            // إذا لم تكن هناك أخطاء، تحديث الإعدادات
            // If no errors, update settings
            if (empty($errors)) {
                // تحديث الإعدادات
                // Update settings
                updateSetting('payment_methods', json_encode($payment_methods));
                updateSetting('paypal_email', $paypal_email);
                updateSetting('paypal_sandbox', $paypal_sandbox);
                updateSetting('stripe_public_key', $stripe_public_key);
                updateSetting('stripe_secret_key', $stripe_secret_key);
                updateSetting('stripe_webhook_secret', $stripe_webhook_secret);
                updateSetting('bank_account_name', $bank_account_name);
                updateSetting('bank_account_number', $bank_account_number);
                updateSetting('bank_name', $bank_name);
                updateSetting('bank_branch', $bank_branch);
                updateSetting('bank_swift', $bank_swift);
                
                // إضافة إشعار
                // Add notification
                addNotification('تم تحديث إعدادات الدفع', 'settings');
                
                // إعادة التوجيه إلى صفحة إعدادات الدفع
                // Redirect to payment settings page
                redirect('index.php?section=payment&success=تم تحديث إعدادات الدفع بنجاح');
                exit;
            }
        }
        
        // الحصول على الإعدادات الحالية
        // Get current settings
        $payment_methods = json_decode(getSetting('payment_methods', '[]'), true);
        $settings = [
            'paypal_email' => getSetting('paypal_email'),
            'paypal_sandbox' => getSetting('paypal_sandbox'),
            'stripe_public_key' => getSetting('stripe_public_key'),
            'stripe_secret_key' => getSetting('stripe_secret_key'),
            'stripe_webhook_secret' => getSetting('stripe_webhook_secret'),
            'bank_account_name' => getSetting('bank_account_name'),
            'bank_account_number' => getSetting('bank_account_number'),
            'bank_name' => getSetting('bank_name'),
            'bank_branch' => getSetting('bank_branch'),
            'bank_swift' => getSetting('bank_swift')
        ];
        
        // تضمين ملف الرأس
        // Include header file
        include '../includes/header.php';
        
        // عرض نموذج إعدادات الدفع
        // Display payment settings form
        include 'templates/payment_settings.php';
        
        break;
        
    case 'email':
        // إعدادات البريد الإلكتروني
        // Email settings
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // التحقق من البيانات
            // Validate data
            $mail_driver = trim($_POST['mail_driver']);
            $mail_host = trim($_POST['mail_host']);
            $mail_port = trim($_POST['mail_port']);
            $mail_username = trim($_POST['mail_username']);
            $mail_password = trim($_POST['mail_password']);
            $mail_encryption = trim($_POST['mail_encryption']);
            $mail_from_address = trim($_POST['mail_from_address']);
            $mail_from_name = trim($_POST['mail_from_name']);
            
            $errors = [];
            
            if (empty($mail_driver)) {
                $errors[] = 'نوع خدمة البريد الإلكتروني مطلوب';
            }
            
            if ($mail_driver === 'smtp' && (empty($mail_host) || empty($mail_port) || empty($mail_username) || empty($mail_password))) {
                $errors[] = 'معلومات SMTP مطلوبة';
            }
            
            if (empty($mail_from_address)) {
                $errors[] = 'عنوان البريد الإلكتروني المرسل مطلوب';
            } elseif (!filter_var($mail_from_address, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'عنوان البريد الإلكتروني المرسل غير صالح';
            }
            
            if (empty($mail_from_name)) {
                $errors[] = 'اسم المرسل مطلوب';
            }
            
            // إذا لم تكن هناك أخطاء، تحديث الإعدادات
            // If no errors, update settings
            if (empty($errors)) {
                // تحديث الإعدادات
                // Update settings
                updateSetting('mail_driver', $mail_driver);
                updateSetting('mail_host', $mail_host);
                updateSetting('mail_port', $mail_port);
                updateSetting('mail_username', $mail_username);
                updateSetting('mail_password', $mail_password);
                updateSetting('mail_encryption', $mail_encryption);
                updateSetting('mail_from_address', $mail_from_address);
                updateSetting('mail_from_name', $mail_from_name);
                
                // إضافة إشعار
                // Add notification
                addNotification('تم تحديث إعدادات البريد الإلكتروني', 'settings');
                
                // إعادة التوجيه إلى صفحة إعدادات البريد الإلكتروني
                // Redirect to email settings page
                redirect('index.php?section=email&success=تم تحديث إعدادات البريد الإلكتروني بنجاح');
                exit;
            }
        }
        
        // الحصول على الإعدادات الحالية
        // Get current settings
        $settings = [
            'mail_driver' => getSetting('mail_driver'),
            'mail_host' => getSetting('mail_host'),
            'mail_port' => getSetting('mail_port'),
            'mail_username' => getSetting('mail_username'),
            'mail_password' => getSetting('mail_password'),
            'mail_encryption' => getSetting('mail_encryption'),
            'mail_from_address' => getSetting('mail_from_address'),
            'mail_from_name' => getSetting('mail_from_name')
        ];
        
        // تضمين ملف الرأس
        // Include header file
        include '../includes/header.php';
        
        // عرض نموذج إعدادات البريد الإلكتروني
        // Display email settings form
        include 'templates/email_settings.php';
        
        break;
        
    case 'social':
        // إعدادات وسائل التواصل الاجتماعي
        // Social media settings
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // التحقق من البيانات
            // Validate data
            $facebook_url = trim($_POST['facebook_url']);
            $twitter_url = trim($_POST['twitter_url']);
            $instagram_url = trim($_POST['instagram_url']);
            $linkedin_url = trim($_POST['linkedin_url']);
            $youtube_url = trim($_POST['youtube_url']);
            $whatsapp_number = trim($_POST['whatsapp_number']);
            
            $errors = [];
            
            // إذا لم تكن هناك أخطاء، تحديث الإعدادات
            // If no errors, update settings
            if (empty($errors)) {
                // تحديث الإعدادات
                // Update settings
                updateSetting('facebook_url', $facebook_url);
                updateSetting('twitter_url', $twitter_url);
                updateSetting('instagram_url', $instagram_url);
                updateSetting('linkedin_url', $linkedin_url);
                updateSetting('youtube_url', $youtube_url);
                updateSetting('whatsapp_number', $whatsapp_number);
                
                // إضافة إشعار
                // Add notification
                addNotification('تم تحديث إعدادات وسائل التواصل الاجتماعي', 'settings');
                
                // إعادة التوجيه إلى صفحة إعدادات وسائل التواصل الاجتماعي
                // Redirect to social media settings page
                redirect('index.php?section=social&success=تم تحديث إعدادات وسائل التواصل الاجتماعي بنجاح');
                exit;
            }
        }
        
        // الحصول على الإعدادات الحالية
        // Get current settings
        $settings = [
            'facebook_url' => getSetting('facebook_url'),
            'twitter_url' => getSetting('twitter_url'),
            'instagram_url' => getSetting('instagram_url'),
            'linkedin_url' => getSetting('linkedin_url'),
            'youtube_url' => getSetting('youtube_url'),
            'whatsapp_number' => getSetting('whatsapp_number')
        ];
        
        // تضمين ملف الرأس
        // Include header file
        include '../includes/header.php';
        
        // عرض نموذج إعدادات وسائل التواصل الاجتماعي
        // Display social media settings form
        include 'templates/social_settings.php';
        
        break;
        
    case 'appearance':
        // إعدادات المظهر
        // Appearance settings
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // التحقق من البيانات
            // Validate data
            $site_theme = trim($_POST['site_theme']);
            $site_logo = '';
            $site_favicon = '';
            
            $errors = [];
            
            // معالجة الشعار
            // Process logo
            if (isset($_FILES['site_logo']) && $_FILES['site_logo']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = '../../assets/images/';
                $fileName = 'logo.' . pathinfo($_FILES['site_logo']['name'], PATHINFO_EXTENSION);
                $uploadFile = $uploadDir . $fileName;
                
                // التحقق من نوع الملف
                // Check file type
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
                if (in_array($_FILES['site_logo']['type'], $allowedTypes)) {
                    if (move_uploaded_file($_FILES['site_logo']['tmp_name'], $uploadFile)) {
                        $site_logo = $fileName;
                    } else {
                        $errors[] = 'فشل في تحميل الشعار';
                    }
                } else {
                    $errors[] = 'نوع ملف الشعار غير مدعوم. يرجى تحميل صورة بتنسيق JPEG أو PNG أو GIF أو WebP أو SVG';
                }
            }
            
            // معالجة الأيقونة المفضلة
            // Process favicon
            if (isset($_FILES['site_favicon']) && $_FILES['site_favicon']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = '../../assets/images/';
                $fileName = 'favicon.' . pathinfo($_FILES['site_favicon']['name'], PATHINFO_EXTENSION);
                $uploadFile = $uploadDir . $fileName;
                
                // التحقق من نوع الملف
                // Check file type
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/x-icon', 'image/vnd.microsoft.icon'];
                if (in_array($_FILES['site_favicon']['type'], $allowedTypes)) {
                    if (move_uploaded_file($_FILES['site_favicon']['tmp_name'], $uploadFile)) {
                        $site_favicon = $fileName;
                    } else {
                        $errors[] = 'فشل في تحميل الأيقونة المفضلة';
                    }
                } else {
                    $errors[] = 'نوع ملف الأيقونة المفضلة غير مدعوم. يرجى تحميل صورة بتنسيق ICO أو PNG أو JPEG';
                }
            }
            
            // إذا لم تكن هناك أخطاء، تحديث الإعدادات
            // If no errors, update settings
            if (empty($errors)) {
                // تحديث الإعدادات
                // Update settings
                updateSetting('site_theme', $site_theme);
                
                if (!empty($site_logo)) {
                    updateSetting('site_logo', $site_logo);
                }
                
                if (!empty($site_favicon)) {
                    updateSetting('site_favicon', $site_favicon);
                }
                
                // إضافة إشعار
                // Add notification
                addNotification('تم تحديث إعدادات المظهر', 'settings');
                
                // إعادة التوجيه إلى صفحة إعدادات المظهر
                // Redirect to appearance settings page
                redirect('index.php?section=appearance&success=تم تحديث إعدادات المظهر بنجاح');
                exit;
            }
        }
        
        // الحصول على الإعدادات الحالية
        // Get current settings
        $settings = [
            'site_theme' => getSetting('site_theme'),
            'site_logo' => getSetting('site_logo'),
            'site_favicon' => getSetting('site_favicon')
        ];
        
        // تضمين ملف الرأس
        // Include header file
        include '../includes/header.php';
        
        // عرض نموذج إعدادات المظهر
        // Display appearance settings form
        include 'templates/appearance_settings.php';
        
        break;
        
    case 'backup':
        // إعدادات النسخ الاحتياطي
        // Backup settings
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'create_backup':
                    // إنشاء نسخة احتياطية
                    // Create backup
                    $backupFile = createBackup();
                    
                    if ($backupFile) {
                        // إضافة إشعار
                        // Add notification
                        addNotification('تم إنشاء نسخة احتياطية جديدة: ' . $backupFile, 'backup');
                        
                        // إعادة التوجيه إلى صفحة إعدادات النسخ الاحتياطي
                        // Redirect to backup settings page
                        redirect('index.php?section=backup&success=تم إنشاء النسخة الاحتياطية بنجاح');
                        exit;
                    } else {
                        $errors[] = 'فشل في إنشاء النسخة الاحتياطية';
                    }
                    break;
                    
                case 'restore_backup':
                    // استعادة نسخة احتياطية
                    // Restore backup
                    if (isset($_POST['backup_file']) && !empty($_POST['backup_file'])) {
                        $backupFile = $_POST['backup_file'];
                        $result = restoreBackup($backupFile);
                        
                        if ($result) {
                            // إضافة إشعار
                            // Add notification
                            addNotification('تم استعادة النسخة الاحتياطية: ' . $backupFile, 'backup');
                            
                            // إعادة التوجيه إلى صفحة إعدادات النسخ الاحتياطي
                            // Redirect to backup settings page
                            redirect('index.php?section=backup&success=تم استعادة النسخة الاحتياطية بنجاح');
                            exit;
                        } else {
                            $errors[] = 'فشل في استعادة النسخة الاحتياطية';
                        }
                    } else {
                        $errors[] = 'يرجى اختيار ملف النسخة الاحتياطية';
                    }
                    break;
                    
                case 'delete_backup':
                    // حذف نسخة احتياطية
                    // Delete backup
                    if (isset($_POST['backup_file']) && !empty($_POST['backup_file'])) {
                        $backupFile = $_POST['backup_file'];
                        $result = deleteBackup($backupFile);
                        
                        if ($result) {
                            // إضافة إشعار
                            // Add notification
                            addNotification('تم حذف النسخة الاحتياطية: ' . $backupFile, 'backup');
                            
                            // إعادة التوجيه إلى صفحة إعدادات النسخ الاحتياطي
                            // Redirect to backup settings page
                            redirect('index.php?section=backup&success=تم حذف النسخة الاحتياطية بنجاح');
                            exit;
                        } else {
                            $errors[] = 'فشل في حذف النسخة الاحتياطية';
                        }
                    } else {
                        $errors[] = 'يرجى اختيار ملف النسخة الاحتياطية';
                    }
                    break;
            }
        }
        
        // الحصول على قائمة النسخ الاحتياطية
        // Get backup list
        $backups = getBackupsList();
        
        // تضمين ملف الرأس
        // Include header file
        include '../includes/header.php';
        
        // عرض نموذج إعدادات النسخ الاحتياطي
        // Display backup settings form
        include 'templates/backup_settings.php';
        
        break;
        
    default:
        // إعادة التوجيه إلى الإعدادات العامة
        // Redirect to general settings
        redirect('index.php?section=general');
        exit;
}

// تضمين ملف التذييل
// Include footer file
include '../includes/footer.php';

/**
 * الحصول على قيمة إعداد
 * Get setting value
 * 
 * @param string $key مفتاح الإعداد
 * @param string $default القيمة الافتراضية
 * @return string قيمة الإعداد
 */
function getSetting($key, $default = '') {
    global $conn;
    
    $stmt = $conn->prepare("SELECT value FROM settings WHERE `key` = ?");
    $stmt->bind_param("s", $key);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc()['value'];
    }
    
    return $default;
}

/**
 * تحديث قيمة إعداد
 * Update setting value
 * 
 * @param string $key مفتاح الإعداد
 * @param string $value قيمة الإعداد
 * @return bool نتيجة التحديث
 */
function updateSetting($key, $value) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT id FROM settings WHERE `key` = ?");
    $stmt->bind_param("s", $key);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $stmt = $conn->prepare("UPDATE settings SET value = ?, updated_at = NOW() WHERE `key` = ?");
        $stmt->bind_param("ss", $value, $key);
    } else {
        $stmt = $conn->prepare("INSERT INTO settings (`key`, value, created_at, updated_at) VALUES (?, ?, NOW(), NOW())");
        $stmt->bind_param("ss", $key, $value);
    }
    
    return $stmt->execute();
}

/**
 * إضافة إشعار
 * Add notification
 * 
 * @param string $message نص الإشعار
 * @param string $type نوع الإشعار
 * @return void
 */
function addNotification($message, $type = 'system') {
    global $conn;
    
    // الحصول على معرفات المستخدمين المسؤولين
    // Get admin user IDs
    $stmt = $conn->prepare("SELECT id FROM users WHERE role = 'admin'");
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($user = $result->fetch_assoc()) {
        $stmt = $conn->prepare("
            INSERT INTO notifications (user_id, message, type, is_read, created_at)
            VALUES (?, ?, ?, 0, NOW())
        ");
        
        $stmt->bind_param("iss", $user['id'], $message, $type);
        $stmt->execute();
    }
}

/**
 * إنشاء نسخة احتياطية
 * Create backup
 * 
 * @return string|bool اسم ملف النسخة الاحتياطية أو false في حالة الفشل
 */
function createBackup() {
    // هذه دالة وهمية، يجب استبدالها بالتنفيذ الفعلي
    // This is a dummy function, should be replaced with actual implementation
    $backupDir = '../../backups/';
    $backupFile = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
    $backupPath = $backupDir . $backupFile;
    
    // التأكد من وجود مجلد النسخ الاحتياطية
    // Make sure backup directory exists
    if (!file_exists($backupDir)) {
        mkdir($backupDir, 0755, true);
    }
    
    // إنشاء ملف النسخة الاحتياطية
    // Create backup file
    $command = 'mysqldump -u ' . DB_USER . ' -p' . DB_PASS . ' ' . DB_NAME . ' > ' . $backupPath;
    exec($command, $output, $returnVar);
    
    if ($returnVar === 0) {
        return $backupFile;
    }
    
    return false;
}

/**
 * استعادة نسخة احتياطية
 * Restore backup
 * 
 * @param string $backupFile اسم ملف النسخة الاحتياطية
 * @return bool نتيجة الاستعادة
 */
function restoreBackup($backupFile) {
    // هذه دالة وهمية، يجب استبدالها بالتنفيذ الفعلي
    // This is a dummy function, should be replaced with actual implementation
    $backupDir = '../../backups/';
    $backupPath = $backupDir . $backupFile;
    
    if (!file_exists($backupPath)) {
        return false;
    }
    
    // استعادة ملف النسخة الاحتياطية
    // Restore backup file
    $command = 'mysql -u ' . DB_USER . ' -p' . DB_PASS . ' ' . DB_NAME . ' < ' . $backupPath;
    exec($command, $output, $returnVar);
    
    return $returnVar === 0;
}

/**
 * حذف نسخة احتياطية
 * Delete backup
 * 
 * @param string $backupFile اسم ملف النسخة الاحتياطية
 * @return bool نتيجة الحذف
 */
function deleteBackup($backupFile) {
    $backupDir = '../../backups/';
    $backupPath = $backupDir . $backupFile;
    
    if (file_exists($backupPath)) {
        return unlink($backupPath);
    }
    
    return false;
}

/**
 * الحصول على قائمة النسخ الاحتياطية
 * Get backups list
 * 
 * @return array قائمة النسخ الاحتياطية
 */
function getBackupsList() {
    $backupDir = '../../backups/';
    $backups = [];
    
    if (file_exists($backupDir)) {
        $files = scandir($backupDir);
        
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..' && pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
                $backups[] = [
                    'name' => $file,
                    'size' => filesize($backupDir . $file),
                    'date' => filemtime($backupDir . $file)
                ];
            }
        }
        
        // ترتيب النسخ الاحتياطية حسب التاريخ (الأحدث أولاً)
        // Sort backups by date (newest first)
        usort($backups, function($a, $b) {
            return $b['date'] - $a['date'];
        });
    }
    
    return $backups;
}
?>

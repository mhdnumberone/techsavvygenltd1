<?php
/**
 * الإعدادات العامة للموقع
 * General website settings
 */

// معلومات الموقع الأساسية - Basic website information
define('SITE_NAME_AR', 'تك سافي جين المحدودة');
define('SITE_NAME_EN', 'TechSavvyGenLtd');
define('SITE_URL', 'http://localhost/techsavvygenltd');
define('ADMIN_URL', SITE_URL . '/admin');
define('DEFAULT_LANGUAGE', 'ar'); // ar or en

// إعدادات البريد الإلكتروني - Email settings
define('MAIL_HOST', 'smtp.example.com');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', 'info@example.com');
define('MAIL_PASSWORD', 'your_password');
define('MAIL_ENCRYPTION', 'tls');
define('MAIL_FROM_ADDRESS', 'info@example.com');
define('MAIL_FROM_NAME', 'TechSavvyGenLtd');

// إعدادات الملفات - File settings
define('UPLOAD_DIR', $_SERVER['DOCUMENT_ROOT'] . '/techsavvygenltd/assets/uploads/');
define('UPLOAD_URL', SITE_URL . '/assets/uploads/');
define('ALLOWED_EXTENSIONS', 'jpg,jpeg,png,gif,pdf,zip,doc,docx,xls,xlsx');
define('MAX_FILE_SIZE', 5242880); // 5MB

// إعدادات الأمان - Security settings
define('SESSION_LIFETIME', 7200); // 2 hours
define('HASH_COST', 10); // لتشفير كلمات المرور - For password hashing

// إعدادات الدفع - Payment settings
define('STRIPE_PUBLIC_KEY', 'your_stripe_public_key');
define('STRIPE_SECRET_KEY', 'your_stripe_secret_key');
define('PAYPAL_CLIENT_ID', 'your_paypal_client_id');
define('PAYPAL_SECRET', 'your_paypal_secret');
define('CURRENCY', 'USD');
define('TAX_RATE', 0.15); // 15%

// إعدادات التصفح - Pagination settings
define('ITEMS_PER_PAGE', 10);

// إعدادات التحميل - Download settings
define('DOWNLOAD_EXPIRY', 24); // ساعات - hours

// إعدادات الإشعارات - Notification settings
define('NOTIFICATION_EXPIRY', 30); // أيام - days

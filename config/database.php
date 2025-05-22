<?php
/**
 * إعدادات قاعدة البيانات
 * Database settings
 */

// معلومات الاتصال بقاعدة البيانات - Database connection information
define('DB_HOST', 'localhost');
define('DB_NAME', 'techsavvygenltd');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', 'utf8mb4_unicode_ci');

// خيارات PDO - PDO options
define('PDO_OPTIONS', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
]);

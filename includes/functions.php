<?php
/**
 * الدوال المساعدة المستخدمة في المشروع
 * Helper functions used in the project
 */

// بدء الجلسة إذا لم تكن قد بدأت بالفعل
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// تضمين ملفات الإعدادات
// Include configuration files
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';

// دالة للاتصال بقاعدة البيانات
// Function to connect to the database
function getDBConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, PDO_OPTIONS);
        return $pdo;
    } catch (PDOException $e) {
        // في بيئة الإنتاج، يجب تسجيل الخطأ بدلاً من عرضه
        // In a production environment, the error should be logged instead of displayed
        error_log("Database Connection Error: " . $e->getMessage());
        die("An error occurred while connecting to the database. Please try again later.");
    }
}

// دالة لإعادة التوجيه إلى صفحة أخرى
// Function to redirect to another page
function redirect($url) {
    header("Location: " . $url);
    exit;
}

// دالة لتنظيف المدخلات
// Function to sanitize input
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// دالة لتشفير كلمة المرور
// Function to hash password
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => HASH_COST]);
}

// دالة للتحقق من كلمة المرور
// Function to verify password
function verifyPassword($password, $hashedPassword) {
    return password_verify($password, $hashedPassword);
}

// دالة للتحقق من تسجيل دخول المستخدم
// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// دالة للتحقق من أن المستخدم هو مسؤول
// Function to check if user is an admin
function isAdmin() {
    return (isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === USER_ROLE_ADMIN);
}

// دالة للحصول على لغة المستخدم الحالية
// Function to get current user language
function getCurrentLanguage() {
    if (isset($_SESSION['lang'])) {
        return $_SESSION['lang'];
    } elseif (isset($_COOKIE['lang'])) {
        return $_COOKIE['lang'];
    }
    return DEFAULT_LANGUAGE;
}

// دالة لتعيين لغة المستخدم
// Function to set user language
function setLanguage($lang) {
    if ($lang === LANGUAGE_ARABIC || $lang === LANGUAGE_ENGLISH) {
        $_SESSION['lang'] = $lang;
        setcookie('lang', $lang, time() + (86400 * 30), "/"); // 30 days
    }
}

// دالة لترجمة النصوص
// Function to translate texts
function translate($key, $lang = null) {
    if ($lang === null) {
        $lang = getCurrentLanguage();
    }
    $langFile = __DIR__ . "/../languages/{$lang}/messages.php";
    static $translations = [];

    if (!isset($translations[$lang])) {
        if (file_exists($langFile)) {
            $translations[$lang] = require $langFile;
        } else {
            // Fallback to default language if specific language file not found
            $defaultLangFile = __DIR__ . "/../languages/" . DEFAULT_LANGUAGE . "/messages.php";
            if (file_exists($defaultLangFile)) {
                 $translations[$lang] = require $defaultLangFile;
            } else {
                $translations[$lang] = []; // No translations available
            }
        }
    }
    return isset($translations[$lang][$key]) ? $translations[$lang][$key] : $key;
}

// دالة لتوليد CSRF token
// Function to generate CSRF token
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// دالة للتحقق من CSRF token
// Function to verify CSRF token
function verifyCsrfToken($token) {
    return hash_equals($_SESSION['csrf_token'], $token);
}

// دالة لعرض رسائل الخطأ أو النجاح
// Function to display error or success messages
function displayMessage($type = 'success', $message = '') {
    if (!empty($message)) {
        echo '<div class="alert alert-' . htmlspecialchars($type) . ' alert-dismissible fade show" role="alert">';
        echo htmlspecialchars($message);
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
    }
}

// دالة لتوليد رابط فريد
// Function to generate a unique link
function generateUniqueLink($length = 16) {
    return bin2hex(random_bytes($length / 2));
}

// دالة لتهيئة التاريخ والوقت
// Function to format date and time
function formatDateTime($datetime, $format = 'Y-m-d H:i:s') {
    if (empty($datetime) || $datetime === '0000-00-00 00:00:00') {
        return '-';
    }
    $date = new DateTime($datetime);
    return $date->format($format);
}

// دالة لتهيئة السعر
// Function to format price
function formatPrice($price, $currency = CURRENCY) {
    return number_format($price, 2) . ' ' . $currency;
}

// دالة لإنشاء Slug من نص
// Function to create a slug from text
function createSlug($text, string $divider = '-'){
  // replace non letter or digits by divider
  $text = preg_replace('~[\pL\d]+~u', '$0', mb_strtolower($text));
  // remove unwanted characters
  $text = preg_replace('~[^\pL\d]+~u', $divider, $text);
  // transliterate
  $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
  // remove unwanted characters
  $text = preg_replace('~[^-\w]+~', '', $text);
  // trim
  $text = trim($text, $divider);
  // remove duplicate divider
  $text = preg_replace('~-+~', $divider, $text);
  // lowercase
  $text = strtolower($text);
  if (empty($text)) {
    return 'n-a';
  }
  return $text;
}

// دالة للحصول على عنوان IP الخاص بالمستخدم
// Function to get user's IP address
function getUserIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

// دالة لتسجيل الأنشطة في النظام
// Function to log system activities
function logActivity($userId, $action, $details = '') {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("INSERT INTO system_logs (user_id, action, details, ip_address) VALUES (:user_id, :action, :details, :ip_address)");
        $stmt->execute([
            ':user_id' => $userId,
            ':action' => $action,
            ':details' => $details,
            ':ip_address' => getUserIP()
        ]);
    } catch (PDOException $e) {
        error_log("Log Activity Error: " . $e->getMessage());
        // لا تعرض الخطأ للمستخدم، فقط قم بتسجيله
        // Do not display the error to the user, just log it
    }
}

// دالة للتحقق من صلاحيات المستخدم
// Function to check user permissions (مثال بسيط، يمكن توسيعه)
// (Simple example, can be expanded)
function checkPermission($requiredRole) {
    if (!isLoggedIn()) {
        redirect(SITE_URL . '/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    }
    if ($_SESSION['user_role'] !== $requiredRole && $_SESSION['user_role'] !== USER_ROLE_ADMIN) {
        // يمكن إعادة التوجيه إلى صفحة خطأ أو الصفحة الرئيسية
        // Can redirect to an error page or homepage
        displayMessage('danger', translate('access_denied'));
        // redirect(SITE_URL);
        return false;
    }
    return true;
}

// دالة لإنشاء رابط تصفح الصفحات
// Function to create pagination links
function createPaginationLinks($currentPage, $totalPages, $baseUrl, $queryParams = []) {
    $links = '<nav aria-label="Page navigation"><ul class="pagination justify-content-center">';

    // Previous button
    if ($currentPage > 1) {
        $prevParams = array_merge($queryParams, ['page' => $currentPage - 1]);
        $links .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '?' . http_build_query($prevParams) . '">' . translate('previous') . '</a></li>';
    } else {
        $links .= '<li class="page-item disabled"><span class="page-link">' . translate('previous') . '</span></li>';
    }

    // Page numbers
    $numLinks = 2; // Number of links to show on each side of the current page
    for ($i = 1; $i <= $totalPages; $i++) {
        if ($i == $currentPage) {
            $links .= '<li class="page-item active" aria-current="page"><span class="page-link">' . $i . '</span></li>';
        } elseif ($i >= $currentPage - $numLinks && $i <= $currentPage + $numLinks) {
            $pageParams = array_merge($queryParams, ['page' => $i]);
            $links .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '?' . http_build_query($pageParams) . '">' . $i . '</a></li>';
        } elseif (($i == 1 && $currentPage > $numLinks + 1) || ($i == $totalPages && $currentPage < $totalPages - $numLinks)) {
            $links .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
    }

    // Next button
    if ($currentPage < $totalPages) {
        $nextParams = array_merge($queryParams, ['page' => $currentPage + 1]);
        $links .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '?' . http_build_query($nextParams) . '">' . translate('next') . '</a></li>';
    } else {
        $links .= '<li class="page-item disabled"><span class="page-link">' . translate('next') . '</span></li>';
    }

    $links .= '</ul></nav>';
    return $links;
}

?>

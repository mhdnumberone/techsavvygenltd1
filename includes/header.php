<?php
/**
 * رأس الصفحة
 * Page header
 */

// بدء الجلسة إذا لم تكن قد بدأت بالفعل
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// تضمين ملفات الإعدادات والدوال المساعدة
// Include configuration files and helper functions
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/functions.php';

// تحديد اللغة الحالية
// Determine current language
$currentLang = getCurrentLanguage();
$dir = $currentLang === LANGUAGE_ARABIC ? 'rtl' : 'ltr';
$lang = $currentLang === LANGUAGE_ARABIC ? 'ar' : 'en';

// تحديد الصفحة الحالية
// Determine current page
$currentPage = basename($_SERVER['PHP_SELF'], '.php');

// تحديد عنوان الصفحة
// Determine page title
$pageTitle = '';
switch ($currentPage) {
    case 'index':
        $pageTitle = translate('home');
        break;
    case 'about':
        $pageTitle = translate('about_us');
        break;
    case 'services':
        $pageTitle = translate('services');
        break;
    case 'products':
        $pageTitle = translate('products');
        break;
    case 'achievements':
        $pageTitle = translate('achievements');
        break;
    case 'contact':
        $pageTitle = translate('contact_us');
        break;
    case 'login':
        $pageTitle = translate('login');
        break;
    case 'register':
        $pageTitle = translate('register');
        break;
    case 'profile':
        $pageTitle = translate('profile');
        break;
    case 'cart':
        $pageTitle = translate('cart');
        break;
    case 'checkout':
        $pageTitle = translate('checkout');
        break;
    case 'service-details':
        $pageTitle = translate('service_details');
        break;
    case 'product-details':
        $pageTitle = translate('product_details');
        break;
    case 'custom-service':
        $pageTitle = translate('custom_service');
        break;
    default:
        $pageTitle = SITE_NAME_EN;
}

// التحقق من وجود رسائل في الجلسة
// Check for messages in session
$successMessage = '';
$errorMessage = '';
if (isset($_SESSION['success_message'])) {
    $successMessage = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
if (isset($_SESSION['error_message'])) {
    $errorMessage = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}

// التحقق من وجود عناصر في سلة التسوق
// Check for items in shopping cart
$cartItemCount = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $items) {
        $cartItemCount += count($items);
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $dir; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - <?php echo $currentLang === LANGUAGE_ARABIC ? SITE_NAME_AR : SITE_NAME_EN; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap RTL CSS (for Arabic) -->
    <?php if ($currentLang === LANGUAGE_ARABIC): ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <?php endif; ?>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    
    <?php if ($currentLang === LANGUAGE_ARABIC): ?>
    <!-- Custom RTL CSS -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/rtl.css">
    <?php endif; ?>
</head>
<body>
    <!-- Header -->
    <header class="bg-dark text-white">
        <div class="container">
            <div class="row py-2">
                <div class="col-md-6">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-envelope me-2"></i>
                        <a href="mailto:<?php echo MAIL_FROM_ADDRESS; ?>" class="text-white text-decoration-none"><?php echo MAIL_FROM_ADDRESS; ?></a>
                        <span class="mx-3">|</span>
                        <i class="fas fa-phone me-2"></i>
                        <a href="tel:+1234567890" class="text-white text-decoration-none">+1 (234) 567-890</a>
                    </div>
                </div>
                <div class="col-md-6 text-end">
                    <div class="d-flex justify-content-end align-items-center">
                        <!-- Language Switcher -->
                        <div class="dropdown me-3">
                            <a class="text-white text-decoration-none dropdown-toggle" href="#" role="button" id="languageDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <?php echo $currentLang === LANGUAGE_ARABIC ? 'العربية' : 'English'; ?>
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="languageDropdown">
                                <li><a class="dropdown-item" href="?lang=ar">العربية</a></li>
                                <li><a class="dropdown-item" href="?lang=en">English</a></li>
                            </ul>
                        </div>
                        
                        <!-- Social Media Links -->
                        <a href="#" class="text-white me-2"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-white me-2"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-white me-2"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </header>
    
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand" href="<?php echo SITE_URL; ?>">
                <img src="<?php echo SITE_URL; ?>/assets/images/logo.png" alt="<?php echo $currentLang === LANGUAGE_ARABIC ? SITE_NAME_AR : SITE_NAME_EN; ?>" height="40">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage === 'index' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>"><?php echo translate('home'); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage === 'about' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/about.php"><?php echo translate('about_us'); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage === 'services' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/services.php"><?php echo translate('services'); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage === 'products' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/products.php"><?php echo translate('products'); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage === 'achievements' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/achievements.php"><?php echo translate('achievements'); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage === 'contact' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/contact.php"><?php echo translate('contact_us'); ?></a>
                    </li>
                </ul>
                <div class="ms-3 d-flex align-items-center">
                    <!-- Cart Icon -->
                    <a href="<?php echo SITE_URL; ?>/cart.php" class="position-relative me-3">
                        <i class="fas fa-shopping-cart fs-5"></i>
                        <?php if ($cartItemCount > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            <?php echo $cartItemCount; ?>
                        </span>
                        <?php endif; ?>
                    </a>
                    
                    <!-- User Account -->
                    <?php if (isLoggedIn()): ?>
                    <div class="dropdown">
                        <a class="btn btn-outline-primary dropdown-toggle" href="#" role="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle me-1"></i> <?php echo $_SESSION['username']; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/profile.php"><i class="fas fa-user me-2"></i><?php echo translate('profile'); ?></a></li>
                            <?php if (isAdmin()): ?>
                            <li><a class="dropdown-item" href="<?php echo ADMIN_URL; ?>"><i class="fas fa-cog me-2"></i><?php echo translate('admin_panel'); ?></a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/logout.php"><i class="fas fa-sign-out-alt me-2"></i><?php echo translate('logout'); ?></a></li>
                        </ul>
                    </div>
                    <?php else: ?>
                    <a href="<?php echo SITE_URL; ?>/login.php" class="btn btn-outline-primary me-2"><?php echo translate('login'); ?></a>
                    <a href="<?php echo SITE_URL; ?>/register.php" class="btn btn-primary"><?php echo translate('register'); ?></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Alert Messages -->
    <?php if (!empty($successMessage)): ?>
    <div class="container mt-3">
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $successMessage; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($errorMessage)): ?>
    <div class="container mt-3">
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $errorMessage; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Main Content -->
    <main>

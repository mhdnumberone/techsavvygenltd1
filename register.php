<?php
/**
 * صفحة التسجيل
 * Register page
 */

// تضمين ملفات الإعدادات والوظائف المساعدة
// Include configuration and helper files
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'config/constants.php';
require_once 'includes/functions.php';

// بدء الجلسة
// Start session
session_start();

// التحقق مما إذا كان المستخدم مسجل الدخول بالفعل
// Check if user is already logged in
if (isLoggedIn()) {
    // إعادة التوجيه إلى الصفحة الرئيسية أو لوحة التحكم
    // Redirect to home page or dashboard
    if (isAdmin()) {
        redirect('admin/index.php');
    } else {
        redirect('account.php');
    }
}

// معالجة نموذج التسجيل
// Process registration form
$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    $agree_terms = isset($_POST['agree_terms']) ? (bool)$_POST['agree_terms'] : false;
    
    // التحقق من البيانات
    // Validate data
    if (empty($name)) {
        $error = translate('name_required');
    } elseif (empty($email)) {
        $error = translate('email_required');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = translate('invalid_email');
    } elseif (empty($password)) {
        $error = translate('password_required');
    } elseif (strlen($password) < 6) {
        $error = translate('password_too_short');
    } elseif ($password !== $confirm_password) {
        $error = translate('passwords_not_match');
    } elseif (!$agree_terms) {
        $error = translate('agree_terms_required');
    } else {
        // محاولة تسجيل المستخدم
        // Attempt to register user
        $user = new User();
        $result = $user->register($name, $email, $password, $phone);
        
        if ($result === true) {
            // التسجيل بنجاح
            // Registration successful
            $success = true;
            
            // إرسال بريد إلكتروني للتحقق
            // Send verification email
            // تنفيذ إرسال البريد الإلكتروني
            // Implement email sending
            // ...
        } else {
            // فشل التسجيل
            // Registration failed
            $error = $result;
        }
    }
}

// تعيين العنوان
// Set page title
$pageTitle = translate('register');

// تضمين ملف الرأس
// Include header file
include 'includes/header.php';
?>

<!-- قسم العنوان -->
<!-- Title Section -->
<section class="page-title-section">
    <div class="container">
        <h1><?php echo translate('register'); ?></h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php"><?php echo translate('home'); ?></a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo translate('register'); ?></li>
            </ol>
        </nav>
    </div>
</section>

<!-- قسم التسجيل -->
<!-- Register Section -->
<section class="register-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="register-form-container">
                    <div class="section-title text-center">
                        <h2><?php echo translate('create_new_account'); ?></h2>
                        <p><?php echo translate('register_description'); ?></p>
                    </div>
                    
                    <?php if ($success): ?>
                    <div class="alert alert-success">
                        <?php echo translate('registration_successful'); ?>
                        <p><?php echo translate('verification_email_sent'); ?></p>
                        <p><a href="login.php" class="btn btn-primary"><?php echo translate('login_now'); ?></a></p>
                    </div>
                    <?php else: ?>
                    
                    <?php if (!empty($error)): ?>
                    <div class="alert alert-danger">
                        <?php echo $error; ?>
                    </div>
                    <?php endif; ?>
                    
                    <form id="register-form" method="post" action="register.php">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name"><?php echo translate('full_name'); ?> <span class="required">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email"><?php echo translate('email'); ?> <span class="required">*</span></label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="phone"><?php echo translate('phone'); ?></label>
                            <input type="text" class="form-control" id="phone" name="phone">
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password"><?php echo translate('password'); ?> <span class="required">*</span></label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                    <small class="form-text text-muted"><?php echo translate('password_requirements'); ?></small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="confirm_password"><?php echo translate('confirm_password'); ?> <span class="required">*</span></label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group form-check">
                            <input type="checkbox" class="form-check-input" id="agree_terms" name="agree_terms" required>
                            <label class="form-check-label" for="agree_terms"><?php echo translate('agree_terms_text'); ?> <a href="terms.php"><?php echo translate('terms_and_conditions'); ?></a></label>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block"><?php echo translate('register'); ?></button>
                    </form>
                    
                    <div class="register-links">
                        <p><?php echo translate('already_have_account'); ?> <a href="login.php"><?php echo translate('login_here'); ?></a></p>
                    </div>
                    
                    <?php if (ENABLE_SOCIAL_LOGIN): ?>
                    <div class="social-login">
                        <div class="social-login-divider">
                            <span><?php echo translate('or_register_with'); ?></span>
                        </div>
                        <div class="social-login-buttons">
                            <?php if (ENABLE_FACEBOOK_LOGIN): ?>
                            <a href="social-login.php?provider=facebook" class="btn btn-facebook">
                                <i class="fab fa-facebook-f"></i> <?php echo translate('facebook'); ?>
                            </a>
                            <?php endif; ?>
                            
                            <?php if (ENABLE_GOOGLE_LOGIN): ?>
                            <a href="social-login.php?provider=google" class="btn btn-google">
                                <i class="fab fa-google"></i> <?php echo translate('google'); ?>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- قسم مميزات العضوية -->
<!-- Membership Benefits Section -->
<section class="membership-benefits-section">
    <div class="container">
        <div class="section-title text-center">
            <h2><?php echo translate('membership_benefits'); ?></h2>
            <p><?php echo translate('membership_benefits_description'); ?></p>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="benefit-box">
                    <div class="benefit-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <h3><?php echo translate('faster_checkout'); ?></h3>
                    <p><?php echo translate('faster_checkout_description'); ?></p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="benefit-box">
                    <div class="benefit-icon">
                        <i class="fas fa-history"></i>
                    </div>
                    <h3><?php echo translate('order_history'); ?></h3>
                    <p><?php echo translate('order_history_description'); ?></p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="benefit-box">
                    <div class="benefit-icon">
                        <i class="fas fa-tag"></i>
                    </div>
                    <h3><?php echo translate('special_offers'); ?></h3>
                    <p><?php echo translate('special_offers_description'); ?></p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
// تضمين ملف التذييل
// Include footer file
include 'includes/footer.php';
?>

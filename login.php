<?php
/**
 * صفحة تسجيل الدخول
 * Login pagehklhlahslhdl
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

// معالجة نموذج تسجيل الدخول
// Process login form
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $remember = isset($_POST['remember']) ? (bool)$_POST['remember'] : false;
    
    // التحقق من البيانات
    // Validate data
    if (empty($email)) {
        $error = translate('email_required');
    } elseif (empty($password)) {
        $error = translate('password_required');
    } else {
        // محاولة تسجيل الدخول
        // Attempt to login
        $user = new User();
        $result = $user->login($email, $password, $remember);
        
        if ($result === true) {
            // تسجيل الدخول بنجاح
            // Login successful
            if (isAdmin()) {
                redirect('admin/index.php');
            } else {
                redirect('account.php');
            }
        } else {
            // فشل تسجيل الدخول
            // Login failed
            $error = $result;
        }
    }
}

// تعيين العنوان
// Set page title
$pageTitle = translate('login');

// تضمين ملف الرأس
// Include header file
include 'includes/header.php';
?>

<!-- قسم العنوان -->
<!-- Title Section -->
<section class="page-title-section">
    <div class="container">
        <h1><?php echo translate('login'); ?></h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php"><?php echo translate('home'); ?></a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo translate('login'); ?></li>
            </ol>
        </nav>
    </div>
</section>

<!-- قسم تسجيل الدخول -->
<!-- Login Section -->
<section class="login-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="login-form-container">
                    <div class="section-title text-center">
                        <h2><?php echo translate('login_to_your_account'); ?></h2>
                        <p><?php echo translate('login_description'); ?></p>
                    </div>
                    
                    <?php if (!empty($error)): ?>
                    <div class="alert alert-danger">
                        <?php echo $error; ?>
                    </div>
                    <?php endif; ?>
                    
                    <form id="login-form" method="post" action="login.php">
                        <div class="form-group">
                            <label for="email"><?php echo translate('email'); ?> <span class="required">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="password"><?php echo translate('password'); ?> <span class="required">*</span></label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="form-group form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember"><?php echo translate('remember_me'); ?></label>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block"><?php echo translate('login'); ?></button>
                    </form>
                    
                    <div class="login-links">
                        <p><a href="forgot-password.php"><?php echo translate('forgot_password'); ?></a></p>
                        <p><?php echo translate('dont_have_account'); ?> <a href="register.php"><?php echo translate('register_now'); ?></a></p>
                    </div>
                    
                    <?php if (ENABLE_SOCIAL_LOGIN): ?>
                    <div class="social-login">
                        <div class="social-login-divider">
                            <span><?php echo translate('or_login_with'); ?></span>
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

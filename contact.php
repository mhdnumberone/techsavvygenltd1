<?php
/**
 * صفحة اتصل بنا
 * Contact Us page
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

// تهيئة الاتصال بقاعدة البيانات
// Initialize database connection
$db = Database::getInstance();

// معالجة نموذج الاتصال
// Process contact form
$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';
    
    // التحقق من البيانات
    // Validate data
    if (empty($name)) {
        $error = translate('name_required');
    } elseif (empty($email)) {
        $error = translate('email_required');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = translate('invalid_email');
    } elseif (empty($subject)) {
        $error = translate('subject_required');
    } elseif (empty($message)) {
        $error = translate('message_required');
    } else {
        // إدراج الرسالة في قاعدة البيانات
        // Insert message into database
        $data = [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'subject' => $subject,
            'message' => $message,
            'status' => 'new',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $result = $db->insert('contact_messages', $data);
        
        if ($result) {
            $success = true;
            
            // إرسال إشعار للمسؤولين
            // Send notification to administrators
            $notification = new Notification();
            $notification->createForAdmins(
                'new_contact_message',
                translate('new_contact_message_notification'),
                ['message_id' => $result]
            );
            
            // إرسال بريد إلكتروني للمسؤول
            // Send email to administrator
            // تنفيذ إرسال البريد الإلكتروني
            // Implement email sending
            // ...
        } else {
            $error = translate('error_sending_message');
        }
    }
}

// تعيين العنوان
// Set page title
$pageTitle = translate('contact_us');

// تضمين ملف الرأس
// Include header file
include 'includes/header.php';
?>

<!-- قسم العنوان -->
<!-- Title Section -->
<section class="page-title-section">
    <div class="container">
        <h1><?php echo translate('contact_us'); ?></h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php"><?php echo translate('home'); ?></a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo translate('contact_us'); ?></li>
            </ol>
        </nav>
    </div>
</section>

<!-- قسم معلومات الاتصال -->
<!-- Contact Information Section -->
<section class="contact-info-section">
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <div class="contact-info-box">
                    <div class="contact-info-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <h3><?php echo translate('our_location'); ?></h3>
                    <p><?php echo COMPANY_ADDRESS; ?></p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="contact-info-box">
                    <div class="contact-info-icon">
                        <i class="fas fa-phone-alt"></i>
                    </div>
                    <h3><?php echo translate('phone_number'); ?></h3>
                    <p><?php echo COMPANY_PHONE; ?></p>
                    <p><?php echo COMPANY_MOBILE; ?></p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="contact-info-box">
                    <div class="contact-info-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <h3><?php echo translate('email_address'); ?></h3>
                    <p><?php echo COMPANY_EMAIL; ?></p>
                    <p><?php echo COMPANY_SUPPORT_EMAIL; ?></p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- قسم نموذج الاتصال -->
<!-- Contact Form Section -->
<section class="contact-form-section">
    <div class="container">
        <div class="row">
            <div class="col-lg-6">
                <div class="contact-form-container">
                    <div class="section-title">
                        <h2><?php echo translate('send_us_message'); ?></h2>
                        <p><?php echo translate('contact_form_description'); ?></p>
                    </div>
                    
                    <?php if ($success): ?>
                    <div class="alert alert-success">
                        <?php echo translate('message_sent_successfully'); ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($error)): ?>
                    <div class="alert alert-danger">
                        <?php echo $error; ?>
                    </div>
                    <?php endif; ?>
                    
                    <form id="contact-form" method="post" action="contact.php">
                        <div class="form-group">
                            <label for="name"><?php echo translate('your_name'); ?> <span class="required">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="email"><?php echo translate('your_email'); ?> <span class="required">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="phone"><?php echo translate('your_phone'); ?></label>
                            <input type="text" class="form-control" id="phone" name="phone">
                        </div>
                        <div class="form-group">
                            <label for="subject"><?php echo translate('subject'); ?> <span class="required">*</span></label>
                            <input type="text" class="form-control" id="subject" name="subject" required>
                        </div>
                        <div class="form-group">
                            <label for="message"><?php echo translate('your_message'); ?> <span class="required">*</span></label>
                            <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary"><?php echo translate('send_message'); ?></button>
                    </form>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="contact-map">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3456.789012345678!2d-123.45678901234567!3d12.345678901234567!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMTLCsDIwJzQ0LjQiTiAxMjPCsDI3JzI0LjQiVw!5e0!3m2!1sen!2sus!4v1234567890123!5m2!1sen!2sus" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- قسم ساعات العمل -->
<!-- Working Hours Section -->
<section class="working-hours-section">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <div class="working-hours-content">
                    <h2><?php echo translate('working_hours'); ?></h2>
                    <p><?php echo translate('working_hours_description'); ?></p>
                    <ul class="working-hours-list">
                        <li>
                            <span class="day"><?php echo translate('monday'); ?> - <?php echo translate('friday'); ?></span>
                            <span class="hours">9:00 AM - 6:00 PM</span>
                        </li>
                        <li>
                            <span class="day"><?php echo translate('saturday'); ?></span>
                            <span class="hours">10:00 AM - 4:00 PM</span>
                        </li>
                        <li>
                            <span class="day"><?php echo translate('sunday'); ?></span>
                            <span class="hours"><?php echo translate('closed'); ?></span>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="col-md-6">
                <div class="faq-content">
                    <h2><?php echo translate('frequently_asked_questions'); ?></h2>
                    <div class="accordion" id="contactFaqAccordion">
                        <div class="card">
                            <div class="card-header" id="headingOne">
                                <h2 class="mb-0">
                                    <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                        <?php echo translate('contact_faq_question_1'); ?>
                                    </button>
                                </h2>
                            </div>
                            <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#contactFaqAccordion">
                                <div class="card-body">
                                    <?php echo translate('contact_faq_answer_1'); ?>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header" id="headingTwo">
                                <h2 class="mb-0">
                                    <button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                        <?php echo translate('contact_faq_question_2'); ?>
                                    </button>
                                </h2>
                            </div>
                            <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#contactFaqAccordion">
                                <div class="card-body">
                                    <?php echo translate('contact_faq_answer_2'); ?>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header" id="headingThree">
                                <h2 class="mb-0">
                                    <button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                        <?php echo translate('contact_faq_question_3'); ?>
                                    </button>
                                </h2>
                            </div>
                            <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#contactFaqAccordion">
                                <div class="card-body">
                                    <?php echo translate('contact_faq_answer_3'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- قسم التواصل الاجتماعي -->
<!-- Social Media Section -->
<section class="social-media-section">
    <div class="container">
        <div class="social-media-content text-center">
            <h2><?php echo translate('follow_us'); ?></h2>
            <p><?php echo translate('social_media_description'); ?></p>
            <div class="social-media-icons">
                <a href="<?php echo SOCIAL_FACEBOOK; ?>" target="_blank" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                <a href="<?php echo SOCIAL_TWITTER; ?>" target="_blank" class="social-icon"><i class="fab fa-twitter"></i></a>
                <a href="<?php echo SOCIAL_INSTAGRAM; ?>" target="_blank" class="social-icon"><i class="fab fa-instagram"></i></a>
                <a href="<?php echo SOCIAL_LINKEDIN; ?>" target="_blank" class="social-icon"><i class="fab fa-linkedin-in"></i></a>
                <a href="<?php echo SOCIAL_YOUTUBE; ?>" target="_blank" class="social-icon"><i class="fab fa-youtube"></i></a>
            </div>
        </div>
    </div>
</section>

<?php
// تضمين ملف التذييل
// Include footer file
include 'includes/footer.php';
?>

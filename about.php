<?php
/**
 * صفحة من نحن
 * About Us page
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

// تعيين العنوان
// Set page title
$pageTitle = translate('about_us');

// تضمين ملف الرأس
// Include header file
include 'includes/header.php';
?>

<!-- قسم العنوان -->
<!-- Title Section -->
<section class="page-title-section">
    <div class="container">
        <h1><?php echo translate('about_us'); ?></h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php"><?php echo translate('home'); ?></a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo translate('about_us'); ?></li>
            </ol>
        </nav>
    </div>
</section>

<!-- قسم من نحن -->
<!-- About Us Section -->
<section class="about-section">
    <div class="container">
        <div class="row">
            <div class="col-lg-6">
                <div class="about-content">
                    <div class="section-title">
                        <h2><?php echo translate('about_company'); ?></h2>
                    </div>
                    <p><?php echo translate('about_company_description_1'); ?></p>
                    <p><?php echo translate('about_company_description_2'); ?></p>
                    <div class="about-features">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="about-feature-item">
                                    <div class="feature-icon">
                                        <i class="fas fa-rocket"></i>
                                    </div>
                                    <h4><?php echo translate('our_mission'); ?></h4>
                                    <p><?php echo translate('mission_description'); ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="about-feature-item">
                                    <div class="feature-icon">
                                        <i class="fas fa-eye"></i>
                                    </div>
                                    <h4><?php echo translate('our_vision'); ?></h4>
                                    <p><?php echo translate('vision_description'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="about-image">
                    <img src="assets/images/about/about-company.jpg" alt="<?php echo translate('about_company'); ?>" class="img-fluid">
                    <div class="experience-box">
                        <div class="experience-years">10</div>
                        <div class="experience-text"><?php echo translate('years_of_experience'); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- قسم القيم -->
<!-- Values Section -->
<section class="values-section">
    <div class="container">
        <div class="section-title text-center">
            <h2><?php echo translate('our_values'); ?></h2>
            <p><?php echo translate('values_description'); ?></p>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="value-box">
                    <div class="value-icon">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <h3><?php echo translate('value_1_title'); ?></h3>
                    <p><?php echo translate('value_1_description'); ?></p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="value-box">
                    <div class="value-icon">
                        <i class="fas fa-lightbulb"></i>
                    </div>
                    <h3><?php echo translate('value_2_title'); ?></h3>
                    <p><?php echo translate('value_2_description'); ?></p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="value-box">
                    <div class="value-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3><?php echo translate('value_3_title'); ?></h3>
                    <p><?php echo translate('value_3_description'); ?></p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- قسم الإحصائيات -->
<!-- Statistics Section -->
<section class="statistics-section">
    <div class="container">
        <div class="row">
            <div class="col-md-3">
                <div class="statistic-box">
                    <div class="statistic-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="statistic-number" data-count="1500">0</div>
                    <div class="statistic-title"><?php echo translate('happy_clients'); ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="statistic-box">
                    <div class="statistic-icon">
                        <i class="fas fa-project-diagram"></i>
                    </div>
                    <div class="statistic-number" data-count="850">0</div>
                    <div class="statistic-title"><?php echo translate('completed_projects'); ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="statistic-box">
                    <div class="statistic-icon">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <div class="statistic-number" data-count="25">0</div>
                    <div class="statistic-title"><?php echo translate('awards_won'); ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="statistic-box">
                    <div class="statistic-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="statistic-number" data-count="10">0</div>
                    <div class="statistic-title"><?php echo translate('years_experience'); ?></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- قسم فريق العمل -->
<!-- Team Section -->
<section class="team-section">
    <div class="container">
        <div class="section-title text-center">
            <h2><?php echo translate('our_team'); ?></h2>
            <p><?php echo translate('team_description'); ?></p>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="team-member">
                    <div class="member-image">
                        <img src="assets/images/team/team-1.jpg" alt="<?php echo translate('team_member_1_name'); ?>" class="img-fluid">
                        <div class="member-social">
                            <a href="#"><i class="fab fa-facebook-f"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fab fa-linkedin-in"></i></a>
                        </div>
                    </div>
                    <div class="member-info">
                        <h3><?php echo translate('team_member_1_name'); ?></h3>
                        <p><?php echo translate('team_member_1_position'); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="team-member">
                    <div class="member-image">
                        <img src="assets/images/team/team-2.jpg" alt="<?php echo translate('team_member_2_name'); ?>" class="img-fluid">
                        <div class="member-social">
                            <a href="#"><i class="fab fa-facebook-f"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fab fa-linkedin-in"></i></a>
                        </div>
                    </div>
                    <div class="member-info">
                        <h3><?php echo translate('team_member_2_name'); ?></h3>
                        <p><?php echo translate('team_member_2_position'); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="team-member">
                    <div class="member-image">
                        <img src="assets/images/team/team-3.jpg" alt="<?php echo translate('team_member_3_name'); ?>" class="img-fluid">
                        <div class="member-social">
                            <a href="#"><i class="fab fa-facebook-f"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fab fa-linkedin-in"></i></a>
                        </div>
                    </div>
                    <div class="member-info">
                        <h3><?php echo translate('team_member_3_name'); ?></h3>
                        <p><?php echo translate('team_member_3_position'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- قسم الشركاء -->
<!-- Partners Section -->
<section class="partners-section">
    <div class="container">
        <div class="section-title text-center">
            <h2><?php echo translate('our_partners'); ?></h2>
            <p><?php echo translate('partners_description'); ?></p>
        </div>
        <div class="partners-slider">
            <div class="partner-item">
                <img src="assets/images/partners/partner-1.png" alt="Partner 1" class="img-fluid">
            </div>
            <div class="partner-item">
                <img src="assets/images/partners/partner-2.png" alt="Partner 2" class="img-fluid">
            </div>
            <div class="partner-item">
                <img src="assets/images/partners/partner-3.png" alt="Partner 3" class="img-fluid">
            </div>
            <div class="partner-item">
                <img src="assets/images/partners/partner-4.png" alt="Partner 4" class="img-fluid">
            </div>
            <div class="partner-item">
                <img src="assets/images/partners/partner-5.png" alt="Partner 5" class="img-fluid">
            </div>
        </div>
    </div>
</section>

<!-- قسم الأسئلة الشائعة -->
<!-- FAQ Section -->
<section class="faq-section">
    <div class="container">
        <div class="section-title text-center">
            <h2><?php echo translate('frequently_asked_questions'); ?></h2>
            <p><?php echo translate('faq_description'); ?></p>
        </div>
        <div class="row">
            <div class="col-lg-8 offset-lg-2">
                <div class="accordion" id="faqAccordion">
                    <div class="card">
                        <div class="card-header" id="headingOne">
                            <h2 class="mb-0">
                                <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                    <?php echo translate('faq_question_1'); ?>
                                </button>
                            </h2>
                        </div>
                        <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#faqAccordion">
                            <div class="card-body">
                                <?php echo translate('faq_answer_1'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header" id="headingTwo">
                            <h2 class="mb-0">
                                <button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                    <?php echo translate('faq_question_2'); ?>
                                </button>
                            </h2>
                        </div>
                        <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#faqAccordion">
                            <div class="card-body">
                                <?php echo translate('faq_answer_2'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header" id="headingThree">
                            <h2 class="mb-0">
                                <button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                    <?php echo translate('faq_question_3'); ?>
                                </button>
                            </h2>
                        </div>
                        <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#faqAccordion">
                            <div class="card-body">
                                <?php echo translate('faq_answer_3'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header" id="headingFour">
                            <h2 class="mb-0">
                                <button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                    <?php echo translate('faq_question_4'); ?>
                                </button>
                            </h2>
                        </div>
                        <div id="collapseFour" class="collapse" aria-labelledby="headingFour" data-parent="#faqAccordion">
                            <div class="card-body">
                                <?php echo translate('faq_answer_4'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header" id="headingFive">
                            <h2 class="mb-0">
                                <button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                                    <?php echo translate('faq_question_5'); ?>
                                </button>
                            </h2>
                        </div>
                        <div id="collapseFive" class="collapse" aria-labelledby="headingFive" data-parent="#faqAccordion">
                            <div class="card-body">
                                <?php echo translate('faq_answer_5'); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- قسم الاتصال السريع -->
<!-- Quick Contact Section -->
<section class="quick-contact-section">
    <div class="container">
        <div class="quick-contact-content">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2><?php echo translate('have_questions'); ?></h2>
                    <p><?php echo translate('contact_us_description'); ?></p>
                </div>
                <div class="col-md-4 text-center text-md-right">
                    <a href="contact.php" class="btn btn-light"><?php echo translate('contact_us'); ?></a>
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

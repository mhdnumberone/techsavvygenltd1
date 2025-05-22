<?php
/**
 * صفحة تفاصيل الإنجاز
 * Achievement Details Page
 */

// تضمين ملفات الإعدادات والوظائف المساعدة
// Include configuration and helper files
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';

// بدء الجلسة
// Start session
session_start();

// التحقق من وجود معرف الإنجاز
// Check if achievement ID exists
if (!isset($_GET['id']) || empty($_GET['id'])) {
    redirect('achievements.php');
    exit;
}

$achievementId = (int)$_GET['id'];

// الحصول على بيانات الإنجاز
// Get achievement data
$db = new Database();
$conn = $db->getConnection();

$stmt = $conn->prepare("
    SELECT a.*, c.name as client_name, c.logo as client_logo, c.website as client_website, c.description as client_description
    FROM achievements a 
    LEFT JOIN clients c ON a.client_id = c.id 
    WHERE a.id = ? AND a.status = 'active'
");
$stmt->bind_param("i", $achievementId);
$stmt->execute();
$achievement = $stmt->get_result()->fetch_assoc();

// إذا لم يتم العثور على الإنجاز، إعادة التوجيه إلى صفحة الإنجازات
// If achievement not found, redirect to achievements page
if (!$achievement) {
    redirect('achievements.php');
    exit;
}

// تهيئة متغيرات الصفحة
// Initialize page variables
$pageTitle = $achievement['title'] . ' | ' . SITE_NAME;
$pageDescription = substr(strip_tags($achievement['description']), 0, 160);
$pageKeywords = 'إنجاز, مشروع, ' . $achievement['title'] . ', ' . $achievement['category'] . ', ' . SITE_NAME;

// الحصول على صور الإنجاز
// Get achievement images
$stmt = $conn->prepare("SELECT * FROM achievement_images WHERE achievement_id = ? ORDER BY sort_order");
$stmt->bind_param("i", $achievementId);
$stmt->execute();
$images = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// الحصول على إنجازات مشابهة
// Get similar achievements
$stmt = $conn->prepare("
    SELECT a.*, c.name as client_name 
    FROM achievements a 
    LEFT JOIN clients c ON a.client_id = c.id 
    WHERE a.category = ? AND a.id != ? AND a.status = 'active' 
    ORDER BY a.completion_date DESC 
    LIMIT 3
");
$stmt->bind_param("si", $achievement['category'], $achievementId);
$stmt->execute();
$similarAchievements = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// تضمين ملف الرأس
// Include header file
include 'includes/header.php';
?>

<!-- قسم العنوان الرئيسي -->
<!-- Main Title Section -->
<section class="page-title-section bg-primary">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1 class="page-title text-white"><?php echo $achievement['title']; ?></h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb bg-transparent justify-content-center">
                        <li class="breadcrumb-item"><a href="index.php" class="text-white">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="achievements.php" class="text-white">الإنجازات</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page"><?php echo $achievement['title']; ?></li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<!-- قسم تفاصيل الإنجاز -->
<!-- Achievement Details Section -->
<section class="section-padding">
    <div class="container">
        <div class="row">
            <!-- صور الإنجاز -->
            <!-- Achievement Images -->
            <div class="col-lg-6 mb-4 mb-lg-0">
                <div class="achievement-gallery">
                    <?php if (!empty($achievement['image'])): ?>
                        <div class="main-image mb-4">
                            <img src="<?php echo SITE_URL; ?>/assets/images/achievements/<?php echo $achievement['image']; ?>" alt="<?php echo $achievement['title']; ?>" class="img-fluid rounded">
                        </div>
                    <?php endif; ?>
                    
                    <?php if (count($images) > 0): ?>
                        <div class="image-thumbnails row">
                            <?php foreach ($images as $image): ?>
                                <div class="col-4 mb-3">
                                    <a href="<?php echo SITE_URL; ?>/assets/images/achievements/gallery/<?php echo $image['image']; ?>" class="gallery-item" data-fancybox="gallery">
                                        <img src="<?php echo SITE_URL; ?>/assets/images/achievements/gallery/thumbnails/<?php echo $image['image']; ?>" alt="<?php echo $image['title']; ?>" class="img-fluid rounded">
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- معلومات الإنجاز -->
            <!-- Achievement Information -->
            <div class="col-lg-6">
                <div class="achievement-details">
                    <h2 class="achievement-title"><?php echo $achievement['title']; ?></h2>
                    
                    <div class="achievement-meta mb-4">
                        <span class="badge badge-primary"><?php echo $achievement['category']; ?></span>
                        <span class="completion-date"><i class="fas fa-calendar-alt"></i> تاريخ الإنجاز: <?php echo date('d/m/Y', strtotime($achievement['completion_date'])); ?></span>
                        <?php if (!empty($achievement['client_name'])): ?>
                            <span class="client-name"><i class="fas fa-building"></i> العميل: <?php echo $achievement['client_name']; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="achievement-description mb-4">
                        <?php echo nl2br($achievement['description']); ?>
                    </div>
                    
                    <?php if (!empty($achievement['features'])): ?>
                        <div class="achievement-features mb-4">
                            <h3 class="features-title">المميزات الرئيسية</h3>
                            <ul class="features-list">
                                <?php 
                                $features = explode("\n", $achievement['features']);
                                foreach ($features as $feature):
                                    if (!empty(trim($feature))):
                                ?>
                                    <li><i class="fas fa-check-circle text-success"></i> <?php echo trim($feature); ?></li>
                                <?php 
                                    endif;
                                endforeach; 
                                ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($achievement['technologies'])): ?>
                        <div class="achievement-technologies mb-4">
                            <h3 class="technologies-title">التقنيات المستخدمة</h3>
                            <div class="technologies-list">
                                <?php 
                                $technologies = explode(",", $achievement['technologies']);
                                foreach ($technologies as $technology):
                                    if (!empty(trim($technology))):
                                ?>
                                    <span class="technology-badge"><?php echo trim($technology); ?></span>
                                <?php 
                                    endif;
                                endforeach; 
                                ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($achievement['project_url'])): ?>
                        <div class="achievement-link mb-4">
                            <a href="<?php echo $achievement['project_url']; ?>" target="_blank" class="btn btn-primary">
                                <i class="fas fa-external-link-alt"></i> زيارة المشروع
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- قسم معلومات العميل -->
<!-- Client Information Section -->
<?php if (!empty($achievement['client_id']) && !empty($achievement['client_name'])): ?>
<section class="section-padding bg-light">
    <div class="container">
        <div class="row">
            <div class="col-md-12 text-center mb-5">
                <h2 class="section-title">عن العميل</h2>
            </div>
        </div>
        
        <div class="row align-items-center">
            <?php if (!empty($achievement['client_logo'])): ?>
                <div class="col-md-4 text-center mb-4 mb-md-0">
                    <div class="client-logo">
                        <img src="<?php echo SITE_URL; ?>/assets/images/clients/<?php echo $achievement['client_logo']; ?>" alt="<?php echo $achievement['client_name']; ?>" class="img-fluid">
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="col-md-<?php echo !empty($achievement['client_logo']) ? '8' : '12'; ?>">
                <div class="client-info">
                    <h3 class="client-name"><?php echo $achievement['client_name']; ?></h3>
                    
                    <?php if (!empty($achievement['client_description'])): ?>
                        <div class="client-description mb-4">
                            <?php echo nl2br($achievement['client_description']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($achievement['client_website'])): ?>
                        <div class="client-website">
                            <a href="<?php echo $achievement['client_website']; ?>" target="_blank" class="btn btn-outline-primary">
                                <i class="fas fa-globe"></i> زيارة موقع العميل
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- قسم التحديات والحلول -->
<!-- Challenges and Solutions Section -->
<?php if (!empty($achievement['challenges']) && !empty($achievement['solutions'])): ?>
<section class="section-padding">
    <div class="container">
        <div class="row">
            <div class="col-md-12 text-center mb-5">
                <h2 class="section-title">التحديات والحلول</h2>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6 mb-4 mb-md-0">
                <div class="challenges-box">
                    <h3 class="box-title"><i class="fas fa-exclamation-triangle text-warning"></i> التحديات</h3>
                    <div class="box-content">
                        <?php echo nl2br($achievement['challenges']); ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="solutions-box">
                    <h3 class="box-title"><i class="fas fa-lightbulb text-success"></i> الحلول</h3>
                    <div class="box-content">
                        <?php echo nl2br($achievement['solutions']); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- قسم النتائج -->
<!-- Results Section -->
<?php if (!empty($achievement['results'])): ?>
<section class="section-padding bg-light">
    <div class="container">
        <div class="row">
            <div class="col-md-12 text-center mb-5">
                <h2 class="section-title">النتائج والإنجازات</h2>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-10 mx-auto">
                <div class="results-box">
                    <div class="results-content">
                        <?php echo nl2br($achievement['results']); ?>
                    </div>
                    
                    <?php if (!empty($achievement['testimonial'])): ?>
                        <div class="testimonial-box mt-5">
                            <div class="testimonial-content">
                                <i class="fas fa-quote-left text-primary"></i>
                                <p class="testimonial-text"><?php echo $achievement['testimonial']; ?></p>
                                <?php if (!empty($achievement['testimonial_author'])): ?>
                                    <div class="testimonial-author">
                                        <p class="author-name">- <?php echo $achievement['testimonial_author']; ?></p>
                                        <?php if (!empty($achievement['testimonial_position'])): ?>
                                            <p class="author-position"><?php echo $achievement['testimonial_position']; ?></p>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- قسم إنجازات مشابهة -->
<!-- Similar Achievements Section -->
<?php if (count($similarAchievements) > 0): ?>
<section class="section-padding">
    <div class="container">
        <div class="row">
            <div class="col-md-12 text-center mb-5">
                <h2 class="section-title">إنجازات مشابهة</h2>
                <p class="section-subtitle">مشاريع أخرى في نفس المجال</p>
            </div>
        </div>
        
        <div class="row">
            <?php foreach ($similarAchievements as $similar): ?>
                <div class="col-md-4 mb-4">
                    <div class="achievement-card">
                        <div class="achievement-image">
                            <img src="<?php echo SITE_URL; ?>/assets/images/achievements/<?php echo $similar['image']; ?>" alt="<?php echo $similar['title']; ?>" class="img-fluid">
                        </div>
                        <div class="achievement-content">
                            <h3 class="achievement-title"><?php echo $similar['title']; ?></h3>
                            <div class="achievement-meta">
                                <span class="achievement-date"><i class="fas fa-calendar-alt"></i> <?php echo date('Y', strtotime($similar['completion_date'])); ?></span>
                                <?php if (!empty($similar['client_name'])): ?>
                                    <span class="achievement-client"><i class="fas fa-building"></i> <?php echo $similar['client_name']; ?></span>
                                <?php endif; ?>
                            </div>
                            <p class="achievement-description"><?php echo substr($similar['description'], 0, 100); ?>...</p>
                            <a href="achievement-details.php?id=<?php echo $similar['id']; ?>" class="btn btn-outline-primary btn-sm">عرض التفاصيل</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- قسم الدعوة للعمل -->
<!-- Call to Action Section -->
<section class="cta-section section-padding bg-primary text-white">
    <div class="container">
        <div class="row">
            <div class="col-md-12 text-center">
                <h2 class="cta-title">هل لديك مشروع مشابه؟</h2>
                <p class="cta-subtitle">دعنا نساعدك في تحقيق أهدافك وتطوير أعمالك من خلال حلولنا التكنولوجية المبتكرة</p>
                <div class="cta-buttons">
                    <a href="contact.php" class="btn btn-light btn-lg">تواصل معنا</a>
                    <a href="services.php" class="btn btn-outline-light btn-lg">استكشف خدماتنا</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- سكريبت خاص بالصفحة -->
<!-- Page Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // تهيئة معرض الصور
    // Initialize image gallery
    if (typeof $.fn.fancybox !== 'undefined') {
        $('[data-fancybox="gallery"]').fancybox({
            buttons: [
                "zoom",
                "slideShow",
                "fullScreen",
                "download",
                "thumbs",
                "close"
            ],
            loop: true,
            protect: true
        });
    }
});
</script>

<?php
// تضمين ملف التذييل
// Include footer file
include 'includes/footer.php';
?>

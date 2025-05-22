<?php
/**
 * صفحة الإنجازات
 * Achievements Page
 */

// تضمين ملفات الإعدادات والوظائف المساعدة
// Include configuration and helper files
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';

// بدء الجلسة
// Start session
session_start();

// تهيئة متغيرات الصفحة
// Initialize page variables
$pageTitle = 'الإنجازات | ' . SITE_NAME;
$pageDescription = 'استعرض أهم إنجازات شركة ' . SITE_NAME . ' في مجال التكنولوجيا والحلول الرقمية';
$pageKeywords = 'إنجازات, مشاريع, نجاحات, تكنولوجيا, حلول رقمية';

// الحصول على قائمة الإنجازات
// Get achievements list
$db = new Database();
$conn = $db->getConnection();

$stmt = $conn->prepare("
    SELECT a.*, c.name as client_name, c.logo as client_logo 
    FROM achievements a 
    LEFT JOIN clients c ON a.client_id = c.id 
    WHERE a.status = 'active' 
    ORDER BY a.completion_date DESC
");
$stmt->execute();
$achievements = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

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
                <h1 class="page-title text-white">إنجازاتنا</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb bg-transparent justify-content-center">
                        <li class="breadcrumb-item"><a href="index.php" class="text-white">الرئيسية</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page">الإنجازات</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<!-- قسم المقدمة -->
<!-- Introduction Section -->
<section class="section-padding">
    <div class="container">
        <div class="row">
            <div class="col-md-12 text-center mb-5">
                <h2 class="section-title">نفخر بإنجازاتنا</h2>
                <p class="section-subtitle">نقدم لكم مجموعة من أهم المشاريع والإنجازات التي حققناها على مدار السنوات الماضية</p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="intro-text">
                    <p>في <?php echo SITE_NAME; ?>، نسعى دائماً لتقديم أفضل الحلول التكنولوجية والخدمات الرقمية لعملائنا. نحن نفخر بسجل حافل من الإنجازات والمشاريع الناجحة التي ساهمت في تطوير أعمال عملائنا وتحقيق أهدافهم.</p>
                    <p>من خلال فريقنا المتخصص والمبدع، استطعنا تنفيذ العديد من المشاريع المتميزة في مختلف المجالات التقنية، بدءاً من تطوير المواقع والتطبيقات، مروراً بحلول التجارة الإلكترونية، وصولاً إلى أنظمة إدارة الأعمال المتكاملة.</p>
                    <p>نقدم لكم هنا لمحة عن بعض إنجازاتنا البارزة، والتي تعكس التزامنا بالجودة والابتكار في كل ما نقدمه.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- قسم الإنجازات -->
<!-- Achievements Section -->
<section class="section-padding bg-light">
    <div class="container">
        <div class="row">
            <div class="col-md-12 text-center mb-5">
                <h2 class="section-title">أبرز مشاريعنا</h2>
                <p class="section-subtitle">استعرض أهم المشاريع التي قمنا بتنفيذها بنجاح</p>
            </div>
        </div>
        
        <div class="row">
            <?php if (count($achievements) > 0): ?>
                <?php foreach ($achievements as $achievement): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="achievement-card">
                            <div class="achievement-image">
                                <img src="<?php echo SITE_URL; ?>/assets/images/achievements/<?php echo $achievement['image']; ?>" alt="<?php echo $achievement['title']; ?>" class="img-fluid">
                                <?php if (!empty($achievement['client_logo'])): ?>
                                    <div class="client-logo">
                                        <img src="<?php echo SITE_URL; ?>/assets/images/clients/<?php echo $achievement['client_logo']; ?>" alt="<?php echo $achievement['client_name']; ?>" class="img-fluid">
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="achievement-content">
                                <h3 class="achievement-title"><?php echo $achievement['title']; ?></h3>
                                <div class="achievement-meta">
                                    <span class="achievement-date"><i class="fas fa-calendar-alt"></i> <?php echo date('Y', strtotime($achievement['completion_date'])); ?></span>
                                    <?php if (!empty($achievement['client_name'])): ?>
                                        <span class="achievement-client"><i class="fas fa-building"></i> <?php echo $achievement['client_name']; ?></span>
                                    <?php endif; ?>
                                    <span class="achievement-category"><i class="fas fa-tag"></i> <?php echo $achievement['category']; ?></span>
                                </div>
                                <p class="achievement-description"><?php echo substr($achievement['description'], 0, 150); ?>...</p>
                                <a href="achievement-details.php?id=<?php echo $achievement['id']; ?>" class="btn btn-outline-primary btn-sm">عرض التفاصيل</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-md-12 text-center">
                    <div class="alert alert-info">
                        <p>لا توجد إنجازات لعرضها حالياً. يرجى العودة لاحقاً.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- قسم الإحصائيات -->
<!-- Statistics Section -->
<section class="section-padding">
    <div class="container">
        <div class="row">
            <div class="col-md-12 text-center mb-5">
                <h2 class="section-title">إحصائيات وأرقام</h2>
                <p class="section-subtitle">إنجازاتنا بالأرقام</p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="stat-box text-center">
                    <div class="stat-icon">
                        <i class="fas fa-project-diagram fa-3x text-primary"></i>
                    </div>
                    <h3 class="stat-number counter" data-count="<?php echo getStatCount('projects'); ?>">0</h3>
                    <p class="stat-title">مشروع ناجح</p>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="stat-box text-center">
                    <div class="stat-icon">
                        <i class="fas fa-users fa-3x text-primary"></i>
                    </div>
                    <h3 class="stat-number counter" data-count="<?php echo getStatCount('clients'); ?>">0</h3>
                    <p class="stat-title">عميل سعيد</p>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="stat-box text-center">
                    <div class="stat-icon">
                        <i class="fas fa-globe fa-3x text-primary"></i>
                    </div>
                    <h3 class="stat-number counter" data-count="<?php echo getStatCount('countries'); ?>">0</h3>
                    <p class="stat-title">دولة حول العالم</p>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="stat-box text-center">
                    <div class="stat-icon">
                        <i class="fas fa-award fa-3x text-primary"></i>
                    </div>
                    <h3 class="stat-number counter" data-count="<?php echo getStatCount('awards'); ?>">0</h3>
                    <p class="stat-title">جائزة وتكريم</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- قسم الشهادات والجوائز -->
<!-- Testimonials and Awards Section -->
<section class="section-padding bg-light">
    <div class="container">
        <div class="row">
            <div class="col-md-12 text-center mb-5">
                <h2 class="section-title">الشهادات والجوائز</h2>
                <p class="section-subtitle">تقدير لجهودنا وإنجازاتنا</p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="awards-wrapper">
                    <h3 class="sub-title mb-4">الجوائز التي حصلنا عليها</h3>
                    
                    <?php
                    // الحصول على الجوائز
                    // Get awards
                    $stmt = $conn->prepare("SELECT * FROM awards WHERE status = 'active' ORDER BY award_date DESC LIMIT 5");
                    $stmt->execute();
                    $awards = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                    
                    if (count($awards) > 0):
                    ?>
                        <div class="awards-list">
                            <?php foreach ($awards as $award): ?>
                                <div class="award-item">
                                    <div class="award-icon">
                                        <i class="fas fa-trophy text-primary"></i>
                                    </div>
                                    <div class="award-content">
                                        <h4 class="award-title"><?php echo $award['title']; ?></h4>
                                        <p class="award-description"><?php echo $award['description']; ?></p>
                                        <div class="award-meta">
                                            <span class="award-date"><i class="fas fa-calendar-alt"></i> <?php echo date('Y', strtotime($award['award_date'])); ?></span>
                                            <span class="award-organization"><i class="fas fa-building"></i> <?php echo $award['organization']; ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <p>لا توجد جوائز لعرضها حالياً.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="testimonials-wrapper">
                    <h3 class="sub-title mb-4">ماذا يقول عملاؤنا</h3>
                    
                    <?php
                    // الحصول على آراء العملاء
                    // Get testimonials
                    $stmt = $conn->prepare("
                        SELECT t.*, c.name as client_name, c.position, c.company, c.image as client_image 
                        FROM testimonials t 
                        LEFT JOIN clients c ON t.client_id = c.id 
                        WHERE t.status = 'active' 
                        ORDER BY t.created_at DESC 
                        LIMIT 3
                    ");
                    $stmt->execute();
                    $testimonials = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                    
                    if (count($testimonials) > 0):
                    ?>
                        <div class="testimonials-carousel">
                            <?php foreach ($testimonials as $testimonial): ?>
                                <div class="testimonial-item">
                                    <div class="testimonial-content">
                                        <div class="testimonial-text">
                                            <i class="fas fa-quote-left text-primary"></i>
                                            <p><?php echo $testimonial['content']; ?></p>
                                        </div>
                                        <div class="testimonial-author">
                                            <?php if (!empty($testimonial['client_image'])): ?>
                                                <div class="author-image">
                                                    <img src="<?php echo SITE_URL; ?>/assets/images/clients/<?php echo $testimonial['client_image']; ?>" alt="<?php echo $testimonial['client_name']; ?>" class="img-fluid rounded-circle">
                                                </div>
                                            <?php endif; ?>
                                            <div class="author-info">
                                                <h4 class="author-name"><?php echo $testimonial['client_name']; ?></h4>
                                                <p class="author-position"><?php echo $testimonial['position']; ?><?php echo !empty($testimonial['company']) ? ', ' . $testimonial['company'] : ''; ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <p>لا توجد شهادات لعرضها حالياً.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- قسم الدعوة للعمل -->
<!-- Call to Action Section -->
<section class="cta-section section-padding">
    <div class="container">
        <div class="row">
            <div class="col-md-12 text-center">
                <h2 class="cta-title">هل تريد أن تكون جزءاً من قصص نجاحنا القادمة؟</h2>
                <p class="cta-subtitle">دعنا نساعدك في تحقيق أهدافك وتطوير أعمالك من خلال حلولنا التكنولوجية المبتكرة</p>
                <div class="cta-buttons">
                    <a href="contact.php" class="btn btn-primary btn-lg">تواصل معنا</a>
                    <a href="services.php" class="btn btn-outline-primary btn-lg">استكشف خدماتنا</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
// تضمين ملف التذييل
// Include footer file
include 'includes/footer.php';

/**
 * الحصول على عدد الإحصائيات
 * Get statistics count
 * 
 * @param string $type نوع الإحصائية
 * @return int العدد
 */
function getStatCount($type) {
    global $conn;
    
    switch ($type) {
        case 'projects':
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM achievements WHERE status = 'active'");
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            return $result['count'];
            
        case 'clients':
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM clients WHERE status = 'active'");
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            return $result['count'];
            
        case 'countries':
            $stmt = $conn->prepare("SELECT COUNT(DISTINCT country) as count FROM clients WHERE status = 'active'");
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            return $result['count'];
            
        case 'awards':
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM awards WHERE status = 'active'");
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            return $result['count'];
            
        default:
            return 0;
    }
}
?>

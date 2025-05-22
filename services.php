<?php
/**
 * صفحة الخدمات
 * Services page
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

// الحصول على رقم الصفحة
// Get page number
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

// الحصول على معرف التصنيف
// Get category ID
$categoryId = isset($_GET['category']) ? (int)$_GET['category'] : null;

// الحصول على كلمة البحث
// Get search keyword
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// الحصول على الخدمات
// Get services
$service = new Service();
$services = $service->getServices($page, ITEMS_PER_PAGE, $search, $categoryId, SERVICE_STATUS_ACTIVE);

// الحصول على التصنيفات
// Get categories
$categories = $db->getRows("SELECT * FROM categories WHERE type = :type ORDER BY name_" . getCurrentLanguage(), [':type' => 'service']);

// تعيين العنوان
// Set page title
$pageTitle = translate('services');

// تضمين ملف الرأس
// Include header file
include 'includes/header.php';
?>

<!-- قسم العنوان -->
<!-- Title Section -->
<section class="page-title-section">
    <div class="container">
        <h1><?php echo translate('services'); ?></h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php"><?php echo translate('home'); ?></a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo translate('services'); ?></li>
            </ol>
        </nav>
    </div>
</section>

<!-- قسم الخدمات -->
<!-- Services Section -->
<section class="services-section">
    <div class="container">
        <div class="row">
            <!-- القائمة الجانبية -->
            <!-- Sidebar -->
            <div class="col-lg-3">
                <div class="sidebar">
                    <!-- البحث -->
                    <!-- Search -->
                    <div class="sidebar-widget">
                        <h3><?php echo translate('search'); ?></h3>
                        <form action="services.php" method="get" class="search-form">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control" placeholder="<?php echo translate('search_services'); ?>" value="<?php echo htmlspecialchars($search); ?>">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <!-- التصنيفات -->
                    <!-- Categories -->
                    <?php if (!empty($categories)): ?>
                    <div class="sidebar-widget">
                        <h3><?php echo translate('categories'); ?></h3>
                        <ul class="category-list">
                            <li class="<?php echo $categoryId === null ? 'active' : ''; ?>">
                                <a href="services.php"><?php echo translate('all_categories'); ?></a>
                            </li>
                            <?php foreach ($categories as $category): ?>
                            <li class="<?php echo $categoryId === (int)$category['id'] ? 'active' : ''; ?>">
                                <a href="services.php?category=<?php echo $category['id']; ?>"><?php echo $category['name_' . getCurrentLanguage()]; ?></a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                    
                    <!-- الخدمة المخصصة -->
                    <!-- Custom Service -->
                    <div class="sidebar-widget">
                        <h3><?php echo translate('custom_service'); ?></h3>
                        <div class="custom-service-widget">
                            <p><?php echo translate('custom_service_sidebar_text'); ?></p>
                            <a href="custom-service.php" class="btn btn-primary btn-block"><?php echo translate('request_custom_service'); ?></a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- محتوى الخدمات -->
            <!-- Services Content -->
            <div class="col-lg-9">
                <?php if (!empty($search)): ?>
                <div class="search-results-info">
                    <h2><?php echo translate('search_results_for'); ?>: "<?php echo htmlspecialchars($search); ?>"</h2>
                    <p><?php echo sprintf(translate('found_results'), $services['total']); ?></p>
                </div>
                <?php elseif ($categoryId !== null && !empty($categories)): ?>
                <?php 
                    $currentCategory = null;
                    foreach ($categories as $category) {
                        if ((int)$category['id'] === $categoryId) {
                            $currentCategory = $category;
                            break;
                        }
                    }
                ?>
                <?php if ($currentCategory): ?>
                <div class="category-info">
                    <h2><?php echo $currentCategory['name_' . getCurrentLanguage()]; ?></h2>
                    <p><?php echo $currentCategory['description_' . getCurrentLanguage()]; ?></p>
                </div>
                <?php endif; ?>
                <?php else: ?>
                <div class="services-info">
                    <h2><?php echo translate('our_services'); ?></h2>
                    <p><?php echo translate('services_description'); ?></p>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($services['data'])): ?>
                <div class="row">
                    <?php foreach ($services['data'] as $service): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="service-card">
                            <div class="service-image">
                                <img src="<?php echo UPLOAD_URL . $service['image']; ?>" alt="<?php echo $service['name_' . getCurrentLanguage()]; ?>" class="img-fluid">
                                <?php if ($service['is_featured']): ?>
                                <span class="badge badge-featured"><?php echo translate('featured'); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="service-info">
                                <h3><a href="service.php?slug=<?php echo $service['slug']; ?>"><?php echo $service['name_' . getCurrentLanguage()]; ?></a></h3>
                                <p><?php echo limitText($service['description_' . getCurrentLanguage()], 100); ?></p>
                                <div class="service-price">
                                    <span class="current-price"><?php echo formatPrice($service['price']); ?></span>
                                </div>
                                <div class="service-rating">
                                    <?php
                                    $review = new Review();
                                    $rating = $review->getServiceAverageRating($service['id']);
                                    $reviewsCount = $review->getServiceReviewsCount($service['id']);
                                    
                                    for ($i = 1; $i <= 5; $i++) {
                                        if ($i <= $rating) {
                                            echo '<i class="fas fa-star"></i>';
                                        } elseif ($i - 0.5 <= $rating) {
                                            echo '<i class="fas fa-star-half-alt"></i>';
                                        } else {
                                            echo '<i class="far fa-star"></i>';
                                        }
                                    }
                                    ?>
                                    <span class="reviews-count">(<?php echo $reviewsCount; ?>)</span>
                                </div>
                                <div class="service-actions">
                                    <a href="cart.php?action=add&id=<?php echo $service['id']; ?>&type=service" class="btn btn-primary btn-sm"><i class="fas fa-shopping-cart"></i> <?php echo translate('add_to_cart'); ?></a>
                                    <a href="service.php?slug=<?php echo $service['slug']; ?>" class="btn btn-outline-primary btn-sm"><i class="fas fa-eye"></i> <?php echo translate('view_details'); ?></a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- الترقيم -->
                <!-- Pagination -->
                <?php if ($services['total_pages'] > 1): ?>
                <div class="pagination-container">
                    <nav aria-label="Page navigation">
                        <ul class="pagination">
                            <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="services.php?page=<?php echo $page - 1; ?><?php echo $categoryId !== null ? '&category=' . $categoryId : ''; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $services['total_pages']; $i++): ?>
                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                <a class="page-link" href="services.php?page=<?php echo $i; ?><?php echo $categoryId !== null ? '&category=' . $categoryId : ''; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>"><?php echo $i; ?></a>
                            </li>
                            <?php endfor; ?>
                            
                            <?php if ($page < $services['total_pages']): ?>
                            <li class="page-item">
                                <a class="page-link" href="services.php?page=<?php echo $page + 1; ?><?php echo $categoryId !== null ? '&category=' . $categoryId : ''; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
                <?php endif; ?>
                
                <?php else: ?>
                <div class="no-results">
                    <div class="no-results-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3><?php echo translate('no_services_found'); ?></h3>
                    <p><?php echo translate('no_services_found_description'); ?></p>
                    <?php if (!empty($search) || $categoryId !== null): ?>
                    <a href="services.php" class="btn btn-primary"><?php echo translate('view_all_services'); ?></a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- قسم الخدمة المخصصة -->
<!-- Custom Service Section -->
<section class="custom-service-section">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <div class="custom-service-image">
                    <img src="assets/images/custom-service.png" alt="<?php echo translate('custom_service_title'); ?>" class="img-fluid">
                </div>
            </div>
            <div class="col-md-6">
                <div class="custom-service-content">
                    <h2><?php echo translate('custom_service_title'); ?></h2>
                    <p><?php echo translate('custom_service_description'); ?></p>
                    <ul class="custom-service-features">
                        <li><i class="fas fa-check"></i> <?php echo translate('custom_service_feature_1'); ?></li>
                        <li><i class="fas fa-check"></i> <?php echo translate('custom_service_feature_2'); ?></li>
                        <li><i class="fas fa-check"></i> <?php echo translate('custom_service_feature_3'); ?></li>
                        <li><i class="fas fa-check"></i> <?php echo translate('custom_service_feature_4'); ?></li>
                    </ul>
                    <a href="custom-service.php" class="btn btn-primary"><?php echo translate('request_custom_service'); ?></a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- قسم الشهادات -->
<!-- Testimonials Section -->
<section class="testimonials-section">
    <div class="container">
        <div class="section-title">
            <h2><?php echo translate('testimonials'); ?></h2>
            <p><?php echo translate('testimonials_description'); ?></p>
        </div>
        <div class="testimonials-slider">
            <div class="testimonial-item">
                <div class="testimonial-content">
                    <div class="testimonial-text">
                        <p><?php echo translate('testimonial_1_text'); ?></p>
                    </div>
                    <div class="testimonial-author">
                        <div class="testimonial-author-image">
                            <img src="assets/images/testimonials/testimonial-1.jpg" alt="<?php echo translate('testimonial_1_author'); ?>">
                        </div>
                        <div class="testimonial-author-info">
                            <h4><?php echo translate('testimonial_1_author'); ?></h4>
                            <p><?php echo translate('testimonial_1_position'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="testimonial-item">
                <div class="testimonial-content">
                    <div class="testimonial-text">
                        <p><?php echo translate('testimonial_2_text'); ?></p>
                    </div>
                    <div class="testimonial-author">
                        <div class="testimonial-author-image">
                            <img src="assets/images/testimonials/testimonial-2.jpg" alt="<?php echo translate('testimonial_2_author'); ?>">
                        </div>
                        <div class="testimonial-author-info">
                            <h4><?php echo translate('testimonial_2_author'); ?></h4>
                            <p><?php echo translate('testimonial_2_position'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="testimonial-item">
                <div class="testimonial-content">
                    <div class="testimonial-text">
                        <p><?php echo translate('testimonial_3_text'); ?></p>
                    </div>
                    <div class="testimonial-author">
                        <div class="testimonial-author-image">
                            <img src="assets/images/testimonials/testimonial-3.jpg" alt="<?php echo translate('testimonial_3_author'); ?>">
                        </div>
                        <div class="testimonial-author-info">
                            <h4><?php echo translate('testimonial_3_author'); ?></h4>
                            <p><?php echo translate('testimonial_3_position'); ?></p>
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

<?php
/**
 * الصفحة الرئيسية
 * Home page
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

// الحصول على المنتجات المميزة
// Get featured products
$product = new Product();
$featuredProducts = $product->getFeaturedProducts();

// الحصول على الخدمات المميزة
// Get featured services
$service = new Service();
$featuredServices = $service->getFeaturedServices();

// الحصول على أحدث المنتجات
// Get latest products
$latestProducts = $product->getLatestProducts();

// الحصول على أحدث الخدمات
// Get latest services
$latestServices = $service->getLatestServices();

// تضمين ملف الرأس
// Include header file
include 'includes/header.php';
?>

<!-- قسم البانر الرئيسي -->
<!-- Main Banner Section -->
<section class="main-banner">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <div class="banner-content">
                    <h1><?php echo translate('home_banner_title'); ?></h1>
                    <p><?php echo translate('home_banner_description'); ?></p>
                    <div class="banner-buttons">
                        <a href="products.php" class="btn btn-primary"><?php echo translate('explore_products'); ?></a>
                        <a href="services.php" class="btn btn-outline-primary"><?php echo translate('explore_services'); ?></a>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="banner-image">
                    <img src="assets/images/banner-image.png" alt="<?php echo translate('home_banner_title'); ?>" class="img-fluid">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- قسم المميزات -->
<!-- Features Section -->
<section class="features-section">
    <div class="container">
        <div class="section-title">
            <h2><?php echo translate('why_choose_us'); ?></h2>
            <p><?php echo translate('why_choose_us_description'); ?></p>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="feature-box">
                    <div class="feature-icon">
                        <i class="fas fa-rocket"></i>
                    </div>
                    <h3><?php echo translate('feature_1_title'); ?></h3>
                    <p><?php echo translate('feature_1_description'); ?></p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-box">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3><?php echo translate('feature_2_title'); ?></h3>
                    <p><?php echo translate('feature_2_description'); ?></p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-box">
                    <div class="feature-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h3><?php echo translate('feature_3_title'); ?></h3>
                    <p><?php echo translate('feature_3_description'); ?></p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- قسم المنتجات المميزة -->
<!-- Featured Products Section -->
<?php if (!empty($featuredProducts)): ?>
<section class="featured-products">
    <div class="container">
        <div class="section-title">
            <h2><?php echo translate('featured_products'); ?></h2>
            <p><?php echo translate('featured_products_description'); ?></p>
        </div>
        <div class="row">
            <?php foreach ($featuredProducts as $product): ?>
            <div class="col-md-4">
                <div class="product-card">
                    <div class="product-image">
                        <img src="<?php echo UPLOAD_URL . $product['image']; ?>" alt="<?php echo $product['name_' . getCurrentLanguage()]; ?>" class="img-fluid">
                        <?php if ($product['is_featured']): ?>
                        <span class="badge badge-featured"><?php echo translate('featured'); ?></span>
                        <?php endif; ?>
                        <?php if ($product['discount_price'] > 0): ?>
                        <span class="badge badge-sale"><?php echo translate('sale'); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="product-info">
                        <h3><a href="product.php?slug=<?php echo $product['slug']; ?>"><?php echo $product['name_' . getCurrentLanguage()]; ?></a></h3>
                        <div class="product-price">
                            <?php if ($product['discount_price'] > 0): ?>
                            <span class="old-price"><?php echo formatPrice($product['price']); ?></span>
                            <span class="current-price"><?php echo formatPrice($product['discount_price']); ?></span>
                            <?php else: ?>
                            <span class="current-price"><?php echo formatPrice($product['price']); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="product-rating">
                            <?php
                            $review = new Review();
                            $rating = $review->getProductAverageRating($product['id']);
                            $reviewsCount = $review->getProductReviewsCount($product['id']);
                            
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
                        <div class="product-actions">
                            <a href="cart.php?action=add&id=<?php echo $product['id']; ?>&type=product" class="btn btn-primary btn-sm"><i class="fas fa-shopping-cart"></i> <?php echo translate('add_to_cart'); ?></a>
                            <a href="product.php?slug=<?php echo $product['slug']; ?>" class="btn btn-outline-primary btn-sm"><i class="fas fa-eye"></i> <?php echo translate('view_details'); ?></a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-4">
            <a href="products.php" class="btn btn-outline-primary"><?php echo translate('view_all_products'); ?></a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- قسم الخدمات المميزة -->
<!-- Featured Services Section -->
<?php if (!empty($featuredServices)): ?>
<section class="featured-services">
    <div class="container">
        <div class="section-title">
            <h2><?php echo translate('featured_services'); ?></h2>
            <p><?php echo translate('featured_services_description'); ?></p>
        </div>
        <div class="row">
            <?php foreach ($featuredServices as $service): ?>
            <div class="col-md-4">
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
        <div class="text-center mt-4">
            <a href="services.php" class="btn btn-outline-primary"><?php echo translate('view_all_services'); ?></a>
        </div>
    </div>
</section>
<?php endif; ?>

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

<!-- قسم الشركاء -->
<!-- Partners Section -->
<section class="partners-section">
    <div class="container">
        <div class="section-title">
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

<!-- قسم النشرة الإخبارية -->
<!-- Newsletter Section -->
<section class="newsletter-section">
    <div class="container">
        <div class="newsletter-content">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h2><?php echo translate('subscribe_newsletter'); ?></h2>
                    <p><?php echo translate('newsletter_description'); ?></p>
                </div>
                <div class="col-md-6">
                    <form id="newsletter-form" class="newsletter-form" method="post" action="process/subscribe.php">
                        <div class="input-group">
                            <input type="email" name="email" class="form-control" placeholder="<?php echo translate('enter_email'); ?>" required>
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="submit"><?php echo translate('subscribe'); ?></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- قسم المدونة -->
<!-- Blog Section -->
<section class="blog-section">
    <div class="container">
        <div class="section-title">
            <h2><?php echo translate('latest_blog_posts'); ?></h2>
            <p><?php echo translate('blog_description'); ?></p>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="blog-card">
                    <div class="blog-image">
                        <img src="assets/images/blog/blog-1.jpg" alt="<?php echo translate('blog_post_1_title'); ?>" class="img-fluid">
                        <div class="blog-date">
                            <span class="day">15</span>
                            <span class="month"><?php echo translate('may'); ?></span>
                        </div>
                    </div>
                    <div class="blog-content">
                        <div class="blog-category"><?php echo translate('technology'); ?></div>
                        <h3><a href="blog-post.php?id=1"><?php echo translate('blog_post_1_title'); ?></a></h3>
                        <p><?php echo translate('blog_post_1_excerpt'); ?></p>
                        <div class="blog-meta">
                            <span><i class="fas fa-user"></i> <?php echo translate('admin'); ?></span>
                            <span><i class="fas fa-comments"></i> 5 <?php echo translate('comments'); ?></span>
                        </div>
                        <a href="blog-post.php?id=1" class="read-more"><?php echo translate('read_more'); ?> <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="blog-card">
                    <div class="blog-image">
                        <img src="assets/images/blog/blog-2.jpg" alt="<?php echo translate('blog_post_2_title'); ?>" class="img-fluid">
                        <div class="blog-date">
                            <span class="day">10</span>
                            <span class="month"><?php echo translate('may'); ?></span>
                        </div>
                    </div>
                    <div class="blog-content">
                        <div class="blog-category"><?php echo translate('business'); ?></div>
                        <h3><a href="blog-post.php?id=2"><?php echo translate('blog_post_2_title'); ?></a></h3>
                        <p><?php echo translate('blog_post_2_excerpt'); ?></p>
                        <div class="blog-meta">
                            <span><i class="fas fa-user"></i> <?php echo translate('admin'); ?></span>
                            <span><i class="fas fa-comments"></i> 3 <?php echo translate('comments'); ?></span>
                        </div>
                        <a href="blog-post.php?id=2" class="read-more"><?php echo translate('read_more'); ?> <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="blog-card">
                    <div class="blog-image">
                        <img src="assets/images/blog/blog-3.jpg" alt="<?php echo translate('blog_post_3_title'); ?>" class="img-fluid">
                        <div class="blog-date">
                            <span class="day">05</span>
                            <span class="month"><?php echo translate('may'); ?></span>
                        </div>
                    </div>
                    <div class="blog-content">
                        <div class="blog-category"><?php echo translate('design'); ?></div>
                        <h3><a href="blog-post.php?id=3"><?php echo translate('blog_post_3_title'); ?></a></h3>
                        <p><?php echo translate('blog_post_3_excerpt'); ?></p>
                        <div class="blog-meta">
                            <span><i class="fas fa-user"></i> <?php echo translate('admin'); ?></span>
                            <span><i class="fas fa-comments"></i> 7 <?php echo translate('comments'); ?></span>
                        </div>
                        <a href="blog-post.php?id=3" class="read-more"><?php echo translate('read_more'); ?> <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>
        </div>
        <div class="text-center mt-4">
            <a href="blog.php" class="btn btn-outline-primary"><?php echo translate('view_all_posts'); ?></a>
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

<?php
/**
 * صفحة المنتجات
 * Products page
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

// الحصول على المنتجات
// Get products
$product = new Product();
$products = $product->getProducts($page, ITEMS_PER_PAGE, $search, $categoryId, PRODUCT_STATUS_ACTIVE);

// الحصول على التصنيفات
// Get categories
$categories = $db->getRows("SELECT * FROM categories WHERE type = :type ORDER BY name_" . getCurrentLanguage(), [':type' => 'product']);

// تعيين العنوان
// Set page title
$pageTitle = translate('products');

// تضمين ملف الرأس
// Include header file
include 'includes/header.php';
?>

<!-- قسم العنوان -->
<!-- Title Section -->
<section class="page-title-section">
    <div class="container">
        <h1><?php echo translate('products'); ?></h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php"><?php echo translate('home'); ?></a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo translate('products'); ?></li>
            </ol>
        </nav>
    </div>
</section>

<!-- قسم المنتجات -->
<!-- Products Section -->
<section class="products-section">
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
                        <form action="products.php" method="get" class="search-form">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control" placeholder="<?php echo translate('search_products'); ?>" value="<?php echo htmlspecialchars($search); ?>">
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
                                <a href="products.php"><?php echo translate('all_categories'); ?></a>
                            </li>
                            <?php foreach ($categories as $category): ?>
                            <li class="<?php echo $categoryId === (int)$category['id'] ? 'active' : ''; ?>">
                                <a href="products.php?category=<?php echo $category['id']; ?>"><?php echo $category['name_' . getCurrentLanguage()]; ?></a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                    
                    <!-- المنتجات المميزة -->
                    <!-- Featured Products -->
                    <div class="sidebar-widget">
                        <h3><?php echo translate('featured_products'); ?></h3>
                        <?php 
                        $featuredProducts = $product->getFeaturedProducts(3);
                        if (!empty($featuredProducts)):
                        ?>
                        <div class="featured-products-widget">
                            <?php foreach ($featuredProducts as $featuredProduct): ?>
                            <div class="featured-product-item">
                                <div class="featured-product-image">
                                    <a href="product.php?slug=<?php echo $featuredProduct['slug']; ?>">
                                        <img src="<?php echo UPLOAD_URL . $featuredProduct['image']; ?>" alt="<?php echo $featuredProduct['name_' . getCurrentLanguage()]; ?>" class="img-fluid">
                                    </a>
                                </div>
                                <div class="featured-product-info">
                                    <h4><a href="product.php?slug=<?php echo $featuredProduct['slug']; ?>"><?php echo $featuredProduct['name_' . getCurrentLanguage()]; ?></a></h4>
                                    <div class="featured-product-price">
                                        <?php if ($featuredProduct['discount_price'] > 0): ?>
                                        <span class="old-price"><?php echo formatPrice($featuredProduct['price']); ?></span>
                                        <span class="current-price"><?php echo formatPrice($featuredProduct['discount_price']); ?></span>
                                        <?php else: ?>
                                        <span class="current-price"><?php echo formatPrice($featuredProduct['price']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <p><?php echo translate('no_featured_products'); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- محتوى المنتجات -->
            <!-- Products Content -->
            <div class="col-lg-9">
                <?php if (!empty($search)): ?>
                <div class="search-results-info">
                    <h2><?php echo translate('search_results_for'); ?>: "<?php echo htmlspecialchars($search); ?>"</h2>
                    <p><?php echo sprintf(translate('found_results'), $products['total']); ?></p>
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
                <div class="products-info">
                    <h2><?php echo translate('our_products'); ?></h2>
                    <p><?php echo translate('products_description'); ?></p>
                </div>
                <?php endif; ?>
                
                <!-- فلاتر المنتجات -->
                <!-- Products Filters -->
                <div class="products-filters">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="products-count">
                                <?php echo sprintf(translate('showing_products'), count($products['data']), $products['total']); ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="products-sort">
                                <form action="products.php" method="get" id="sort-form">
                                    <?php if ($categoryId !== null): ?>
                                    <input type="hidden" name="category" value="<?php echo $categoryId; ?>">
                                    <?php endif; ?>
                                    <?php if (!empty($search)): ?>
                                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                                    <?php endif; ?>
                                    <div class="form-group">
                                        <label for="sort"><?php echo translate('sort_by'); ?>:</label>
                                        <select name="sort" id="sort" class="form-control" onchange="document.getElementById('sort-form').submit();">
                                            <option value="newest"><?php echo translate('newest'); ?></option>
                                            <option value="price_low"><?php echo translate('price_low_to_high'); ?></option>
                                            <option value="price_high"><?php echo translate('price_high_to_low'); ?></option>
                                            <option value="name_asc"><?php echo translate('name_a_to_z'); ?></option>
                                            <option value="name_desc"><?php echo translate('name_z_to_a'); ?></option>
                                        </select>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php if (!empty($products['data'])): ?>
                <div class="row">
                    <?php foreach ($products['data'] as $product): ?>
                    <div class="col-md-6 col-lg-4">
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
                
                <!-- الترقيم -->
                <!-- Pagination -->
                <?php if ($products['total_pages'] > 1): ?>
                <div class="pagination-container">
                    <nav aria-label="Page navigation">
                        <ul class="pagination">
                            <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="products.php?page=<?php echo $page - 1; ?><?php echo $categoryId !== null ? '&category=' . $categoryId : ''; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $products['total_pages']; $i++): ?>
                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                <a class="page-link" href="products.php?page=<?php echo $i; ?><?php echo $categoryId !== null ? '&category=' . $categoryId : ''; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>"><?php echo $i; ?></a>
                            </li>
                            <?php endfor; ?>
                            
                            <?php if ($page < $products['total_pages']): ?>
                            <li class="page-item">
                                <a class="page-link" href="products.php?page=<?php echo $page + 1; ?><?php echo $categoryId !== null ? '&category=' . $categoryId : ''; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" aria-label="Next">
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
                    <h3><?php echo translate('no_products_found'); ?></h3>
                    <p><?php echo translate('no_products_found_description'); ?></p>
                    <?php if (!empty($search) || $categoryId !== null): ?>
                    <a href="products.php" class="btn btn-primary"><?php echo translate('view_all_products'); ?></a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- قسم المنتجات الشائعة -->
<!-- Popular Products Section -->
<section class="popular-products-section">
    <div class="container">
        <div class="section-title text-center">
            <h2><?php echo translate('popular_products'); ?></h2>
            <p><?php echo translate('popular_products_description'); ?></p>
        </div>
        <?php 
        $popularProducts = $product->getPopularProducts();
        if (!empty($popularProducts)):
        ?>
        <div class="popular-products-slider">
            <?php foreach ($popularProducts as $popularProduct): ?>
            <div class="popular-product-item">
                <div class="product-card">
                    <div class="product-image">
                        <img src="<?php echo UPLOAD_URL . $popularProduct['image']; ?>" alt="<?php echo $popularProduct['name_' . getCurrentLanguage()]; ?>" class="img-fluid">
                        <?php if ($popularProduct['is_featured']): ?>
                        <span class="badge badge-featured"><?php echo translate('featured'); ?></span>
                        <?php endif; ?>
                        <?php if ($popularProduct['discount_price'] > 0): ?>
                        <span class="badge badge-sale"><?php echo translate('sale'); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="product-info">
                        <h3><a href="product.php?slug=<?php echo $popularProduct['slug']; ?>"><?php echo $popularProduct['name_' . getCurrentLanguage()]; ?></a></h3>
                        <div class="product-price">
                            <?php if ($popularProduct['discount_price'] > 0): ?>
                            <span class="old-price"><?php echo formatPrice($popularProduct['price']); ?></span>
                            <span class="current-price"><?php echo formatPrice($popularProduct['discount_price']); ?></span>
                            <?php else: ?>
                            <span class="current-price"><?php echo formatPrice($popularProduct['price']); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="product-rating">
                            <?php
                            $review = new Review();
                            $rating = $review->getProductAverageRating($popularProduct['id']);
                            $reviewsCount = $review->getProductReviewsCount($popularProduct['id']);
                            
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
                            <a href="cart.php?action=add&id=<?php echo $popularProduct['id']; ?>&type=product" class="btn btn-primary btn-sm"><i class="fas fa-shopping-cart"></i> <?php echo translate('add_to_cart'); ?></a>
                            <a href="product.php?slug=<?php echo $popularProduct['slug']; ?>" class="btn btn-outline-primary btn-sm"><i class="fas fa-eye"></i> <?php echo translate('view_details'); ?></a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
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

<?php
// تضمين ملف التذييل
// Include footer file
include 'includes/footer.php';
?>

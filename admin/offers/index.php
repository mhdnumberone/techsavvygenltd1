<?php
/**
 * صفحة إدارة العروض
 * Offers Management Page
 */

// تضمين ملفات الإعدادات والوظائف المساعدة
// Include configuration and helper files
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// بدء الجلسة
// Start session
session_start();

// التحقق من تسجيل الدخول والصلاحيات
// Check login and permissions
if (!isLoggedIn() || !isAdmin()) {
    redirect('../../login.php');
    exit;
}

// تهيئة متغيرات الصفحة
// Initialize page variables
$pageTitle = 'إدارة العروض | ' . SITE_NAME;
$activeMenu = 'offers';

// تهيئة قاعدة البيانات
// Initialize database
$db = new Database();
$conn = $db->getConnection();

// تحديد الإجراء المطلوب
// Determine requested action
$action = isset($_GET['action']) ? $_GET['action'] : 'list';

// معالجة الإجراءات
// Process actions
switch ($action) {
    case 'add':
        // إضافة عرض جديد
        // Add new offer
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // التحقق من البيانات
            // Validate data
            $title = trim($_POST['title']);
            $description = trim($_POST['description']);
            $discount_type = trim($_POST['discount_type']);
            $discount_value = (float)$_POST['discount_value'];
            $start_date = trim($_POST['start_date']);
            $end_date = trim($_POST['end_date']);
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            $product_ids = isset($_POST['product_ids']) ? $_POST['product_ids'] : [];
            $service_ids = isset($_POST['service_ids']) ? $_POST['service_ids'] : [];
            $category_ids = isset($_POST['category_ids']) ? $_POST['category_ids'] : [];
            $min_order_amount = (float)$_POST['min_order_amount'];
            $usage_limit = (int)$_POST['usage_limit'];
            $code = trim($_POST['code']);
            
            $errors = [];
            
            if (empty($title)) {
                $errors[] = 'عنوان العرض مطلوب';
            }
            
            if (empty($discount_type)) {
                $errors[] = 'نوع الخصم مطلوب';
            }
            
            if ($discount_value <= 0) {
                $errors[] = 'قيمة الخصم يجب أن تكون أكبر من صفر';
            }
            
            if ($discount_type === 'percentage' && $discount_value > 100) {
                $errors[] = 'قيمة الخصم بالنسبة المئوية يجب أن تكون أقل من أو تساوي 100';
            }
            
            if (empty($start_date)) {
                $errors[] = 'تاريخ بدء العرض مطلوب';
            }
            
            if (empty($end_date)) {
                $errors[] = 'تاريخ انتهاء العرض مطلوب';
            }
            
            if (!empty($start_date) && !empty($end_date) && strtotime($start_date) > strtotime($end_date)) {
                $errors[] = 'تاريخ بدء العرض يجب أن يكون قبل تاريخ انتهاء العرض';
            }
            
            if (!empty($code)) {
                // التحقق من عدم وجود كود مماثل
                // Check if code already exists
                $stmt = $conn->prepare("SELECT id FROM offers WHERE code = ?");
                $stmt->bind_param("s", $code);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $errors[] = 'كود العرض موجود بالفعل';
                }
            }
            
            // إذا لم تكن هناك أخطاء، إضافة العرض
            // If no errors, add offer
            if (empty($errors)) {
                // إضافة العرض
                // Add offer
                $stmt = $conn->prepare("
                    INSERT INTO offers (
                        title, description, discount_type, discount_value, 
                        start_date, end_date, is_active, min_order_amount, 
                        usage_limit, code, created_at, updated_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
                ");
                
                $stmt->bind_param(
                    "sssdssidis",
                    $title, $description, $discount_type, $discount_value,
                    $start_date, $end_date, $is_active, $min_order_amount,
                    $usage_limit, $code
                );
                
                if ($stmt->execute()) {
                    $offerId = $conn->insert_id;
                    
                    // إضافة المنتجات المرتبطة
                    // Add related products
                    if (!empty($product_ids)) {
                        foreach ($product_ids as $productId) {
                            $stmt = $conn->prepare("
                                INSERT INTO offer_products (offer_id, product_id)
                                VALUES (?, ?)
                            ");
                            
                            $stmt->bind_param("ii", $offerId, $productId);
                            $stmt->execute();
                        }
                    }
                    
                    // إضافة الخدمات المرتبطة
                    // Add related services
                    if (!empty($service_ids)) {
                        foreach ($service_ids as $serviceId) {
                            $stmt = $conn->prepare("
                                INSERT INTO offer_services (offer_id, service_id)
                                VALUES (?, ?)
                            ");
                            
                            $stmt->bind_param("ii", $offerId, $serviceId);
                            $stmt->execute();
                        }
                    }
                    
                    // إضافة الفئات المرتبطة
                    // Add related categories
                    if (!empty($category_ids)) {
                        foreach ($category_ids as $categoryId) {
                            $stmt = $conn->prepare("
                                INSERT INTO offer_categories (offer_id, category_id)
                                VALUES (?, ?)
                            ");
                            
                            $stmt->bind_param("ii", $offerId, $categoryId);
                            $stmt->execute();
                        }
                    }
                    
                    // إعادة التوجيه إلى قائمة العروض
                    // Redirect to offers list
                    redirect('index.php?success=تم إضافة العرض بنجاح');
                    exit;
                } else {
                    $errors[] = 'حدث خطأ أثناء إضافة العرض';
                }
            }
        }
        
        // الحصول على قائمة المنتجات
        // Get products list
        $stmt = $conn->prepare("SELECT id, name FROM products ORDER BY name");
        $stmt->execute();
        $products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // الحصول على قائمة الخدمات
        // Get services list
        $stmt = $conn->prepare("SELECT id, name FROM services ORDER BY name");
        $stmt->execute();
        $services = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // الحصول على قائمة الفئات
        // Get categories list
        $stmt = $conn->prepare("SELECT id, name, type FROM categories ORDER BY name");
        $stmt->execute();
        $categories = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // تضمين ملف الرأس
        // Include header file
        include '../includes/header.php';
        
        // عرض نموذج إضافة عرض
        // Display add offer form
        include 'templates/add_offer.php';
        
        break;
        
    case 'edit':
        // تعديل عرض
        // Edit offer
        
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            redirect('index.php');
            exit;
        }
        
        $offerId = (int)$_GET['id'];
        
        // الحصول على بيانات العرض
        // Get offer data
        $stmt = $conn->prepare("SELECT * FROM offers WHERE id = ?");
        $stmt->bind_param("i", $offerId);
        $stmt->execute();
        $offer = $stmt->get_result()->fetch_assoc();
        
        if (!$offer) {
            redirect('index.php?error=العرض غير موجود');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // التحقق من البيانات
            // Validate data
            $title = trim($_POST['title']);
            $description = trim($_POST['description']);
            $discount_type = trim($_POST['discount_type']);
            $discount_value = (float)$_POST['discount_value'];
            $start_date = trim($_POST['start_date']);
            $end_date = trim($_POST['end_date']);
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            $product_ids = isset($_POST['product_ids']) ? $_POST['product_ids'] : [];
            $service_ids = isset($_POST['service_ids']) ? $_POST['service_ids'] : [];
            $category_ids = isset($_POST['category_ids']) ? $_POST['category_ids'] : [];
            $min_order_amount = (float)$_POST['min_order_amount'];
            $usage_limit = (int)$_POST['usage_limit'];
            $code = trim($_POST['code']);
            
            $errors = [];
            
            if (empty($title)) {
                $errors[] = 'عنوان العرض مطلوب';
            }
            
            if (empty($discount_type)) {
                $errors[] = 'نوع الخصم مطلوب';
            }
            
            if ($discount_value <= 0) {
                $errors[] = 'قيمة الخصم يجب أن تكون أكبر من صفر';
            }
            
            if ($discount_type === 'percentage' && $discount_value > 100) {
                $errors[] = 'قيمة الخصم بالنسبة المئوية يجب أن تكون أقل من أو تساوي 100';
            }
            
            if (empty($start_date)) {
                $errors[] = 'تاريخ بدء العرض مطلوب';
            }
            
            if (empty($end_date)) {
                $errors[] = 'تاريخ انتهاء العرض مطلوب';
            }
            
            if (!empty($start_date) && !empty($end_date) && strtotime($start_date) > strtotime($end_date)) {
                $errors[] = 'تاريخ بدء العرض يجب أن يكون قبل تاريخ انتهاء العرض';
            }
            
            if (!empty($code)) {
                // التحقق من عدم وجود كود مماثل
                // Check if code already exists
                $stmt = $conn->prepare("SELECT id FROM offers WHERE code = ? AND id != ?");
                $stmt->bind_param("si", $code, $offerId);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $errors[] = 'كود العرض موجود بالفعل';
                }
            }
            
            // إذا لم تكن هناك أخطاء، تحديث العرض
            // If no errors, update offer
            if (empty($errors)) {
                // تحديث العرض
                // Update offer
                $stmt = $conn->prepare("
                    UPDATE offers SET
                        title = ?, description = ?, discount_type = ?, discount_value = ?,
                        start_date = ?, end_date = ?, is_active = ?, min_order_amount = ?,
                        usage_limit = ?, code = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                
                $stmt->bind_param(
                    "sssdssidisi",
                    $title, $description, $discount_type, $discount_value,
                    $start_date, $end_date, $is_active, $min_order_amount,
                    $usage_limit, $code, $offerId
                );
                
                if ($stmt->execute()) {
                    // حذف العلاقات السابقة
                    // Delete previous relations
                    $stmt = $conn->prepare("DELETE FROM offer_products WHERE offer_id = ?");
                    $stmt->bind_param("i", $offerId);
                    $stmt->execute();
                    
                    $stmt = $conn->prepare("DELETE FROM offer_services WHERE offer_id = ?");
                    $stmt->bind_param("i", $offerId);
                    $stmt->execute();
                    
                    $stmt = $conn->prepare("DELETE FROM offer_categories WHERE offer_id = ?");
                    $stmt->bind_param("i", $offerId);
                    $stmt->execute();
                    
                    // إضافة المنتجات المرتبطة
                    // Add related products
                    if (!empty($product_ids)) {
                        foreach ($product_ids as $productId) {
                            $stmt = $conn->prepare("
                                INSERT INTO offer_products (offer_id, product_id)
                                VALUES (?, ?)
                            ");
                            
                            $stmt->bind_param("ii", $offerId, $productId);
                            $stmt->execute();
                        }
                    }
                    
                    // إضافة الخدمات المرتبطة
                    // Add related services
                    if (!empty($service_ids)) {
                        foreach ($service_ids as $serviceId) {
                            $stmt = $conn->prepare("
                                INSERT INTO offer_services (offer_id, service_id)
                                VALUES (?, ?)
                            ");
                            
                            $stmt->bind_param("ii", $offerId, $serviceId);
                            $stmt->execute();
                        }
                    }
                    
                    // إضافة الفئات المرتبطة
                    // Add related categories
                    if (!empty($category_ids)) {
                        foreach ($category_ids as $categoryId) {
                            $stmt = $conn->prepare("
                                INSERT INTO offer_categories (offer_id, category_id)
                                VALUES (?, ?)
                            ");
                            
                            $stmt->bind_param("ii", $offerId, $categoryId);
                            $stmt->execute();
                        }
                    }
                    
                    // إعادة التوجيه إلى قائمة العروض
                    // Redirect to offers list
                    redirect('index.php?success=تم تحديث العرض بنجاح');
                    exit;
                } else {
                    $errors[] = 'حدث خطأ أثناء تحديث العرض';
                }
            }
        }
        
        // الحصول على المنتجات المرتبطة
        // Get related products
        $stmt = $conn->prepare("
            SELECT product_id FROM offer_products WHERE offer_id = ?
        ");
        
        $stmt->bind_param("i", $offerId);
        $stmt->execute();
        $relatedProducts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        $selectedProductIds = [];
        foreach ($relatedProducts as $product) {
            $selectedProductIds[] = $product['product_id'];
        }
        
        // الحصول على الخدمات المرتبطة
        // Get related services
        $stmt = $conn->prepare("
            SELECT service_id FROM offer_services WHERE offer_id = ?
        ");
        
        $stmt->bind_param("i", $offerId);
        $stmt->execute();
        $relatedServices = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        $selectedServiceIds = [];
        foreach ($relatedServices as $service) {
            $selectedServiceIds[] = $service['service_id'];
        }
        
        // الحصول على الفئات المرتبطة
        // Get related categories
        $stmt = $conn->prepare("
            SELECT category_id FROM offer_categories WHERE offer_id = ?
        ");
        
        $stmt->bind_param("i", $offerId);
        $stmt->execute();
        $relatedCategories = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        $selectedCategoryIds = [];
        foreach ($relatedCategories as $category) {
            $selectedCategoryIds[] = $category['category_id'];
        }
        
        // الحصول على قائمة المنتجات
        // Get products list
        $stmt = $conn->prepare("SELECT id, name FROM products ORDER BY name");
        $stmt->execute();
        $products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // الحصول على قائمة الخدمات
        // Get services list
        $stmt = $conn->prepare("SELECT id, name FROM services ORDER BY name");
        $stmt->execute();
        $services = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // الحصول على قائمة الفئات
        // Get categories list
        $stmt = $conn->prepare("SELECT id, name, type FROM categories ORDER BY name");
        $stmt->execute();
        $categories = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // تضمين ملف الرأس
        // Include header file
        include '../includes/header.php';
        
        // عرض نموذج تعديل عرض
        // Display edit offer form
        include 'templates/edit_offer.php';
        
        break;
        
    case 'delete':
        // حذف عرض
        // Delete offer
        
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            redirect('index.php');
            exit;
        }
        
        $offerId = (int)$_GET['id'];
        
        // الحصول على بيانات العرض
        // Get offer data
        $stmt = $conn->prepare("SELECT * FROM offers WHERE id = ?");
        $stmt->bind_param("i", $offerId);
        $stmt->execute();
        $offer = $stmt->get_result()->fetch_assoc();
        
        if (!$offer) {
            redirect('index.php?error=العرض غير موجود');
            exit;
        }
        
        // حذف العلاقات
        // Delete relations
        $stmt = $conn->prepare("DELETE FROM offer_products WHERE offer_id = ?");
        $stmt->bind_param("i", $offerId);
        $stmt->execute();
        
        $stmt = $conn->prepare("DELETE FROM offer_services WHERE offer_id = ?");
        $stmt->bind_param("i", $offerId);
        $stmt->execute();
        
        $stmt = $conn->prepare("DELETE FROM offer_categories WHERE offer_id = ?");
        $stmt->bind_param("i", $offerId);
        $stmt->execute();
        
        // حذف العرض
        // Delete offer
        $stmt = $conn->prepare("DELETE FROM offers WHERE id = ?");
        $stmt->bind_param("i", $offerId);
        
        if ($stmt->execute()) {
            // إعادة التوجيه إلى قائمة العروض
            // Redirect to offers list
            redirect('index.php?success=تم حذف العرض بنجاح');
            exit;
        } else {
            redirect('index.php?error=حدث خطأ أثناء حذف العرض');
            exit;
        }
        
        break;
        
    case 'toggle_status':
        // تغيير حالة العرض
        // Toggle offer status
        
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            redirect('index.php');
            exit;
        }
        
        $offerId = (int)$_GET['id'];
        
        // الحصول على بيانات العرض
        // Get offer data
        $stmt = $conn->prepare("SELECT * FROM offers WHERE id = ?");
        $stmt->bind_param("i", $offerId);
        $stmt->execute();
        $offer = $stmt->get_result()->fetch_assoc();
        
        if (!$offer) {
            redirect('index.php?error=العرض غير موجود');
            exit;
        }
        
        // تغيير حالة العرض
        // Toggle offer status
        $newStatus = $offer['is_active'] ? 0 : 1;
        
        $stmt = $conn->prepare("UPDATE offers SET is_active = ?, updated_at = NOW() WHERE id = ?");
        $stmt->bind_param("ii", $newStatus, $offerId);
        
        if ($stmt->execute()) {
            // إعادة التوجيه إلى قائمة العروض
            // Redirect to offers list
            $statusMessage = $newStatus ? 'تم تفعيل العرض بنجاح' : 'تم إلغاء تفعيل العرض بنجاح';
            redirect('index.php?success=' . $statusMessage);
            exit;
        } else {
            redirect('index.php?error=حدث خطأ أثناء تغيير حالة العرض');
            exit;
        }
        
        break;
        
    default:
        // قائمة العروض
        // Offers list
        
        // تهيئة متغيرات البحث والترتيب
        // Initialize search and sort variables
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $status = isset($_GET['status']) ? $_GET['status'] : '';
        $sort = isset($_GET['sort']) ? $_GET['sort'] : 'created_at';
        $order = isset($_GET['order']) ? $_GET['order'] : 'DESC';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        // بناء استعلام البحث
        // Build search query
        $query = "SELECT * FROM offers WHERE 1=1";
        $countQuery = "SELECT COUNT(*) as total FROM offers WHERE 1=1";
        
        $params = [];
        $types = "";
        
        if (!empty($search)) {
            $searchTerm = "%{$search}%";
            $query .= " AND (title LIKE ? OR description LIKE ? OR code LIKE ?)";
            $countQuery .= " AND (title LIKE ? OR description LIKE ? OR code LIKE ?)";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= "sss";
        }
        
        if ($status !== '') {
            $statusValue = (int)$status;
            $query .= " AND is_active = ?";
            $countQuery .= " AND is_active = ?";
            $params[] = $statusValue;
            $types .= "i";
        }
        
        // إضافة الترتيب
        // Add sorting
        $allowedSortFields = ['id', 'title', 'discount_value', 'start_date', 'end_date', 'is_active', 'created_at', 'updated_at'];
        $allowedOrderDirections = ['ASC', 'DESC'];
        
        if (!in_array($sort, $allowedSortFields)) {
            $sort = 'created_at';
        }
        
        if (!in_array($order, $allowedOrderDirections)) {
            $order = 'DESC';
        }
        
        $query .= " ORDER BY {$sort} {$order} LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $types .= "ii";
        
        // تنفيذ استعلام العدد الإجمالي
        // Execute count query
        $stmt = $conn->prepare($countQuery);
        
        if (!empty($types)) {
            $stmt->bind_param(substr($types, 0, -2), ...array_slice($params, 0, -2));
        }
        
        $stmt->execute();
        $totalResult = $stmt->get_result()->fetch_assoc();
        $total = $totalResult['total'];
        
        // تنفيذ استعلام البحث
        // Execute search query
        $stmt = $conn->prepare($query);
        
        if (!empty($types)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $offers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // حساب عدد الصفحات
        // Calculate total pages
        $totalPages = ceil($total / $limit);
        
        // تضمين ملف الرأس
        // Include header file
        include '../includes/header.php';
        
        // عرض قائمة العروض
        // Display offers list
        include 'templates/list_offers.php';
        
        break;
}

// تضمين ملف التذييل
// Include footer file
include '../includes/footer.php';
?>

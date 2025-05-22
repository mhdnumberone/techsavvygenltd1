<?php
/**
 * صفحة إدارة المنتجات
 * Products Management Page
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
$pageTitle = 'إدارة المنتجات | ' . SITE_NAME;
$activeMenu = 'products';

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
        // إضافة منتج جديد
        // Add new product
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // التحقق من البيانات
            // Validate data
            $name = trim($_POST['name']);
            $description = trim($_POST['description']);
            $price = (float)$_POST['price'];
            $category_id = (int)$_POST['category_id'];
            $sku = trim($_POST['sku']);
            $stock = (int)$_POST['stock'];
            $status = $_POST['status'];
            
            $errors = [];
            
            if (empty($name)) {
                $errors[] = 'اسم المنتج مطلوب';
            }
            
            if (empty($description)) {
                $errors[] = 'وصف المنتج مطلوب';
            }
            
            if ($price <= 0) {
                $errors[] = 'السعر يجب أن يكون أكبر من صفر';
            }
            
            if ($category_id <= 0) {
                $errors[] = 'يجب اختيار فئة صالحة';
            }
            
            if (empty($sku)) {
                $errors[] = 'رمز المنتج (SKU) مطلوب';
            } else {
                // التحقق من عدم تكرار رمز المنتج
                // Check if SKU already exists
                $stmt = $conn->prepare("SELECT id FROM products WHERE sku = ?");
                $stmt->bind_param("s", $sku);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $errors[] = 'رمز المنتج (SKU) موجود بالفعل';
                }
            }
            
            // إذا لم تكن هناك أخطاء، إضافة المنتج
            // If no errors, add product
            if (empty($errors)) {
                // معالجة الصورة
                // Process image
                $image = '';
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = '../../assets/images/products/';
                    $fileName = time() . '_' . basename($_FILES['image']['name']);
                    $uploadFile = $uploadDir . $fileName;
                    
                    // التحقق من نوع الملف
                    // Check file type
                    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                    if (in_array($_FILES['image']['type'], $allowedTypes)) {
                        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
                            $image = $fileName;
                        } else {
                            $errors[] = 'فشل في تحميل الصورة';
                        }
                    } else {
                        $errors[] = 'نوع الملف غير مدعوم. يرجى تحميل صورة بتنسيق JPEG أو PNG أو GIF أو WebP';
                    }
                }
                
                if (empty($errors)) {
                    // إضافة المنتج إلى قاعدة البيانات
                    // Add product to database
                    $stmt = $conn->prepare("
                        INSERT INTO products (name, description, price, category_id, sku, stock, image, status, created_at, updated_at)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
                    ");
                    
                    $stmt->bind_param("sssisss", $name, $description, $price, $category_id, $sku, $stock, $image, $status);
                    
                    if ($stmt->execute()) {
                        $productId = $conn->insert_id;
                        
                        // معالجة الخصائص
                        // Process attributes
                        if (isset($_POST['attribute_name']) && is_array($_POST['attribute_name'])) {
                            foreach ($_POST['attribute_name'] as $key => $attrName) {
                                if (!empty($attrName) && isset($_POST['attribute_value'][$key]) && !empty($_POST['attribute_value'][$key])) {
                                    $attrValue = $_POST['attribute_value'][$key];
                                    
                                    $stmt = $conn->prepare("
                                        INSERT INTO product_attributes (product_id, name, value, created_at)
                                        VALUES (?, ?, ?, NOW())
                                    ");
                                    
                                    $stmt->bind_param("iss", $productId, $attrName, $attrValue);
                                    $stmt->execute();
                                }
                            }
                        }
                        
                        // معالجة الصور الإضافية
                        // Process additional images
                        if (isset($_FILES['additional_images']) && is_array($_FILES['additional_images']['name'])) {
                            $additionalImages = $_FILES['additional_images'];
                            
                            for ($i = 0; $i < count($additionalImages['name']); $i++) {
                                if ($additionalImages['error'][$i] === UPLOAD_ERR_OK) {
                                    $uploadDir = '../../assets/images/products/gallery/';
                                    $fileName = time() . '_' . $i . '_' . basename($additionalImages['name'][$i]);
                                    $uploadFile = $uploadDir . $fileName;
                                    
                                    // التحقق من نوع الملف
                                    // Check file type
                                    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                                    if (in_array($additionalImages['type'][$i], $allowedTypes)) {
                                        if (move_uploaded_file($additionalImages['tmp_name'][$i], $uploadFile)) {
                                            $stmt = $conn->prepare("
                                                INSERT INTO product_images (product_id, image, sort_order, created_at)
                                                VALUES (?, ?, ?, NOW())
                                            ");
                                            
                                            $sortOrder = $i + 1;
                                            $stmt->bind_param("isi", $productId, $fileName, $sortOrder);
                                            $stmt->execute();
                                        }
                                    }
                                }
                            }
                        }
                        
                        // إضافة إشعار
                        // Add notification
                        addNotification('تمت إضافة منتج جديد: ' . $name, 'product');
                        
                        // إعادة التوجيه إلى قائمة المنتجات
                        // Redirect to products list
                        redirect('index.php?success=تمت إضافة المنتج بنجاح');
                        exit;
                    } else {
                        $errors[] = 'حدث خطأ أثناء إضافة المنتج';
                    }
                }
            }
        }
        
        // الحصول على فئات المنتجات
        // Get product categories
        $stmt = $conn->prepare("SELECT * FROM categories WHERE type = 'product' ORDER BY name");
        $stmt->execute();
        $categories = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // تضمين ملف الرأس
        // Include header file
        include '../includes/header.php';
        
        // عرض نموذج إضافة منتج
        // Display add product form
        include 'templates/add_product.php';
        
        break;
        
    case 'edit':
        // تحرير منتج
        // Edit product
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            redirect('index.php');
            exit;
        }
        
        $productId = (int)$_GET['id'];
        
        // الحصول على بيانات المنتج
        // Get product data
        $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();
        
        if (!$product) {
            redirect('index.php?error=المنتج غير موجود');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // التحقق من البيانات
            // Validate data
            $name = trim($_POST['name']);
            $description = trim($_POST['description']);
            $price = (float)$_POST['price'];
            $category_id = (int)$_POST['category_id'];
            $sku = trim($_POST['sku']);
            $stock = (int)$_POST['stock'];
            $status = $_POST['status'];
            
            $errors = [];
            
            if (empty($name)) {
                $errors[] = 'اسم المنتج مطلوب';
            }
            
            if (empty($description)) {
                $errors[] = 'وصف المنتج مطلوب';
            }
            
            if ($price <= 0) {
                $errors[] = 'السعر يجب أن يكون أكبر من صفر';
            }
            
            if ($category_id <= 0) {
                $errors[] = 'يجب اختيار فئة صالحة';
            }
            
            if (empty($sku)) {
                $errors[] = 'رمز المنتج (SKU) مطلوب';
            } else {
                // التحقق من عدم تكرار رمز المنتج
                // Check if SKU already exists
                $stmt = $conn->prepare("SELECT id FROM products WHERE sku = ? AND id != ?");
                $stmt->bind_param("si", $sku, $productId);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $errors[] = 'رمز المنتج (SKU) موجود بالفعل';
                }
            }
            
            // إذا لم تكن هناك أخطاء، تحديث المنتج
            // If no errors, update product
            if (empty($errors)) {
                // معالجة الصورة
                // Process image
                $image = $product['image'];
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = '../../assets/images/products/';
                    $fileName = time() . '_' . basename($_FILES['image']['name']);
                    $uploadFile = $uploadDir . $fileName;
                    
                    // التحقق من نوع الملف
                    // Check file type
                    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                    if (in_array($_FILES['image']['type'], $allowedTypes)) {
                        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
                            // حذف الصورة القديمة إذا كانت موجودة
                            // Delete old image if exists
                            if (!empty($product['image']) && file_exists($uploadDir . $product['image'])) {
                                unlink($uploadDir . $product['image']);
                            }
                            
                            $image = $fileName;
                        } else {
                            $errors[] = 'فشل في تحميل الصورة';
                        }
                    } else {
                        $errors[] = 'نوع الملف غير مدعوم. يرجى تحميل صورة بتنسيق JPEG أو PNG أو GIF أو WebP';
                    }
                }
                
                if (empty($errors)) {
                    // تحديث المنتج في قاعدة البيانات
                    // Update product in database
                    $stmt = $conn->prepare("
                        UPDATE products 
                        SET name = ?, description = ?, price = ?, category_id = ?, sku = ?, stock = ?, image = ?, status = ?, updated_at = NOW()
                        WHERE id = ?
                    ");
                    
                    $stmt->bind_param("sssisssi", $name, $description, $price, $category_id, $sku, $stock, $image, $status, $productId);
                    
                    if ($stmt->execute()) {
                        // حذف الخصائص الحالية
                        // Delete current attributes
                        $stmt = $conn->prepare("DELETE FROM product_attributes WHERE product_id = ?");
                        $stmt->bind_param("i", $productId);
                        $stmt->execute();
                        
                        // إضافة الخصائص الجديدة
                        // Add new attributes
                        if (isset($_POST['attribute_name']) && is_array($_POST['attribute_name'])) {
                            foreach ($_POST['attribute_name'] as $key => $attrName) {
                                if (!empty($attrName) && isset($_POST['attribute_value'][$key]) && !empty($_POST['attribute_value'][$key])) {
                                    $attrValue = $_POST['attribute_value'][$key];
                                    
                                    $stmt = $conn->prepare("
                                        INSERT INTO product_attributes (product_id, name, value, created_at)
                                        VALUES (?, ?, ?, NOW())
                                    ");
                                    
                                    $stmt->bind_param("iss", $productId, $attrName, $attrValue);
                                    $stmt->execute();
                                }
                            }
                        }
                        
                        // معالجة الصور الإضافية
                        // Process additional images
                        if (isset($_FILES['additional_images']) && is_array($_FILES['additional_images']['name'])) {
                            $additionalImages = $_FILES['additional_images'];
                            
                            for ($i = 0; $i < count($additionalImages['name']); $i++) {
                                if ($additionalImages['error'][$i] === UPLOAD_ERR_OK) {
                                    $uploadDir = '../../assets/images/products/gallery/';
                                    $fileName = time() . '_' . $i . '_' . basename($additionalImages['name'][$i]);
                                    $uploadFile = $uploadDir . $fileName;
                                    
                                    // التحقق من نوع الملف
                                    // Check file type
                                    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                                    if (in_array($additionalImages['type'][$i], $allowedTypes)) {
                                        if (move_uploaded_file($additionalImages['tmp_name'][$i], $uploadFile)) {
                                            $stmt = $conn->prepare("
                                                INSERT INTO product_images (product_id, image, sort_order, created_at)
                                                VALUES (?, ?, ?, NOW())
                                            ");
                                            
                                            $sortOrder = $i + 1;
                                            $stmt->bind_param("isi", $productId, $fileName, $sortOrder);
                                            $stmt->execute();
                                        }
                                    }
                                }
                            }
                        }
                        
                        // حذف الصور المحددة
                        // Delete selected images
                        if (isset($_POST['delete_image']) && is_array($_POST['delete_image'])) {
                            foreach ($_POST['delete_image'] as $imageId) {
                                $stmt = $conn->prepare("SELECT image FROM product_images WHERE id = ? AND product_id = ?");
                                $stmt->bind_param("ii", $imageId, $productId);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                
                                if ($result->num_rows > 0) {
                                    $imageName = $result->fetch_assoc()['image'];
                                    $uploadDir = '../../assets/images/products/gallery/';
                                    
                                    if (file_exists($uploadDir . $imageName)) {
                                        unlink($uploadDir . $imageName);
                                    }
                                    
                                    $stmt = $conn->prepare("DELETE FROM product_images WHERE id = ?");
                                    $stmt->bind_param("i", $imageId);
                                    $stmt->execute();
                                }
                            }
                        }
                        
                        // إضافة إشعار
                        // Add notification
                        addNotification('تم تحديث المنتج: ' . $name, 'product');
                        
                        // إعادة التوجيه إلى قائمة المنتجات
                        // Redirect to products list
                        redirect('index.php?success=تم تحديث المنتج بنجاح');
                        exit;
                    } else {
                        $errors[] = 'حدث خطأ أثناء تحديث المنتج';
                    }
                }
            }
        }
        
        // الحصول على فئات المنتجات
        // Get product categories
        $stmt = $conn->prepare("SELECT * FROM categories WHERE type = 'product' ORDER BY name");
        $stmt->execute();
        $categories = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // الحصول على خصائص المنتج
        // Get product attributes
        $stmt = $conn->prepare("SELECT * FROM product_attributes WHERE product_id = ? ORDER BY id");
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $attributes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // الحصول على صور المنتج
        // Get product images
        $stmt = $conn->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order");
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $productImages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // تضمين ملف الرأس
        // Include header file
        include '../includes/header.php';
        
        // عرض نموذج تحرير منتج
        // Display edit product form
        include 'templates/edit_product.php';
        
        break;
        
    case 'delete':
        // حذف منتج
        // Delete product
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            redirect('index.php');
            exit;
        }
        
        $productId = (int)$_GET['id'];
        
        // الحصول على بيانات المنتج
        // Get product data
        $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();
        
        if (!$product) {
            redirect('index.php?error=المنتج غير موجود');
            exit;
        }
        
        // حذف صورة المنتج
        // Delete product image
        if (!empty($product['image'])) {
            $uploadDir = '../../assets/images/products/';
            if (file_exists($uploadDir . $product['image'])) {
                unlink($uploadDir . $product['image']);
            }
        }
        
        // حذف صور المنتج الإضافية
        // Delete additional product images
        $stmt = $conn->prepare("SELECT image FROM product_images WHERE product_id = ?");
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $uploadDir = '../../assets/images/products/gallery/';
            if (file_exists($uploadDir . $row['image'])) {
                unlink($uploadDir . $row['image']);
            }
        }
        
        // حذف خصائص المنتج
        // Delete product attributes
        $stmt = $conn->prepare("DELETE FROM product_attributes WHERE product_id = ?");
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        
        // حذف صور المنتج
        // Delete product images
        $stmt = $conn->prepare("DELETE FROM product_images WHERE product_id = ?");
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        
        // حذف المنتج
        // Delete product
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->bind_param("i", $productId);
        
        if ($stmt->execute()) {
            // إضافة إشعار
            // Add notification
            addNotification('تم حذف المنتج: ' . $product['name'], 'product');
            
            // إعادة التوجيه إلى قائمة المنتجات
            // Redirect to products list
            redirect('index.php?success=تم حذف المنتج بنجاح');
            exit;
        } else {
            redirect('index.php?error=حدث خطأ أثناء حذف المنتج');
            exit;
        }
        
        break;
        
    case 'view':
        // عرض تفاصيل المنتج
        // View product details
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            redirect('index.php');
            exit;
        }
        
        $productId = (int)$_GET['id'];
        
        // الحصول على بيانات المنتج
        // Get product data
        $stmt = $conn->prepare("
            SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.id = ?
        ");
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();
        
        if (!$product) {
            redirect('index.php?error=المنتج غير موجود');
            exit;
        }
        
        // الحصول على خصائص المنتج
        // Get product attributes
        $stmt = $conn->prepare("SELECT * FROM product_attributes WHERE product_id = ? ORDER BY id");
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $attributes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // الحصول على صور المنتج
        // Get product images
        $stmt = $conn->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order");
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $productImages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // الحصول على مراجعات المنتج
        // Get product reviews
        $stmt = $conn->prepare("
            SELECT r.*, u.name as user_name 
            FROM reviews r 
            LEFT JOIN users u ON r.user_id = u.id 
            WHERE r.product_id = ? 
            ORDER BY r.created_at DESC
        ");
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $reviews = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // تضمين ملف الرأس
        // Include header file
        include '../includes/header.php';
        
        // عرض تفاصيل المنتج
        // Display product details
        include 'templates/view_product.php';
        
        break;
        
    default:
        // قائمة المنتجات
        // Products list
        
        // تهيئة متغيرات البحث والترتيب
        // Initialize search and sort variables
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $category = isset($_GET['category']) ? (int)$_GET['category'] : 0;
        $status = isset($_GET['status']) ? $_GET['status'] : '';
        $sort = isset($_GET['sort']) ? $_GET['sort'] : 'id';
        $order = isset($_GET['order']) ? $_GET['order'] : 'DESC';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;
        
        // بناء استعلام البحث
        // Build search query
        $query = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE 1=1";
        $countQuery = "SELECT COUNT(*) as total FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE 1=1";
        $params = [];
        $types = "";
        
        if (!empty($search)) {
            $searchTerm = "%$search%";
            $query .= " AND (p.name LIKE ? OR p.description LIKE ? OR p.sku LIKE ?)";
            $countQuery .= " AND (p.name LIKE ? OR p.description LIKE ? OR p.sku LIKE ?)";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= "sss";
        }
        
        if ($category > 0) {
            $query .= " AND p.category_id = ?";
            $countQuery .= " AND p.category_id = ?";
            $params[] = $category;
            $types .= "i";
        }
        
        if (!empty($status)) {
            $query .= " AND p.status = ?";
            $countQuery .= " AND p.status = ?";
            $params[] = $status;
            $types .= "s";
        }
        
        // إضافة الترتيب
        // Add sorting
        $allowedSortFields = ['id', 'name', 'price', 'stock', 'created_at'];
        $allowedOrderDirections = ['ASC', 'DESC'];
        
        if (!in_array($sort, $allowedSortFields)) {
            $sort = 'id';
        }
        
        if (!in_array($order, $allowedOrderDirections)) {
            $order = 'DESC';
        }
        
        $query .= " ORDER BY p.$sort $order LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $types .= "ii";
        
        // تنفيذ استعلام العدد الإجمالي
        // Execute count query
        $stmt = $conn->prepare($countQuery);
        if (!empty($types) && count($params) > 0) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $totalResult = $stmt->get_result()->fetch_assoc();
        $total = $totalResult['total'];
        
        // تنفيذ استعلام البحث
        // Execute search query
        $stmt = $conn->prepare($query);
        if (!empty($types) && count($params) > 0) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // حساب عدد الصفحات
        // Calculate total pages
        $totalPages = ceil($total / $limit);
        
        // الحصول على فئات المنتجات
        // Get product categories
        $stmt = $conn->prepare("SELECT * FROM categories WHERE type = 'product' ORDER BY name");
        $stmt->execute();
        $categories = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // تضمين ملف الرأس
        // Include header file
        include '../includes/header.php';
        
        // عرض قائمة المنتجات
        // Display products list
        include 'templates/list_products.php';
        
        break;
}

// تضمين ملف التذييل
// Include footer file
include '../includes/footer.php';

/**
 * إضافة إشعار
 * Add notification
 * 
 * @param string $message نص الإشعار
 * @param string $type نوع الإشعار
 * @return void
 */
function addNotification($message, $type = 'system') {
    global $conn;
    
    // الحصول على معرفات المستخدمين المسؤولين
    // Get admin user IDs
    $stmt = $conn->prepare("SELECT id FROM users WHERE role = 'admin'");
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($user = $result->fetch_assoc()) {
        $stmt = $conn->prepare("
            INSERT INTO notifications (user_id, message, type, is_read, created_at)
            VALUES (?, ?, ?, 0, NOW())
        ");
        
        $stmt->bind_param("iss", $user['id'], $message, $type);
        $stmt->execute();
    }
}
?>

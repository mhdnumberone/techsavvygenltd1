<?php
/**
 * صفحة إدارة الخدمات
 * Services Management Page
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
$pageTitle = 'إدارة الخدمات | ' . SITE_NAME;
$activeMenu = 'services';

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
        // إضافة خدمة جديدة
        // Add new service
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // التحقق من البيانات
            // Validate data
            $name = trim($_POST['name']);
            $description = trim($_POST['description']);
            $price = (float)$_POST['price'];
            $category_id = (int)$_POST['category_id'];
            $duration = trim($_POST['duration']);
            $status = $_POST['status'];
            
            $errors = [];
            
            if (empty($name)) {
                $errors[] = 'اسم الخدمة مطلوب';
            }
            
            if (empty($description)) {
                $errors[] = 'وصف الخدمة مطلوب';
            }
            
            if ($price <= 0) {
                $errors[] = 'السعر يجب أن يكون أكبر من صفر';
            }
            
            if ($category_id <= 0) {
                $errors[] = 'يجب اختيار فئة صالحة';
            }
            
            // إذا لم تكن هناك أخطاء، إضافة الخدمة
            // If no errors, add service
            if (empty($errors)) {
                // معالجة الصورة
                // Process image
                $image = '';
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = '../../assets/images/services/';
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
                    // إضافة الخدمة إلى قاعدة البيانات
                    // Add service to database
                    $stmt = $conn->prepare("
                        INSERT INTO services (name, description, price, category_id, duration, image, status, created_at, updated_at)
                        VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
                    ");
                    
                    $stmt->bind_param("sssisss", $name, $description, $price, $category_id, $duration, $image, $status);
                    
                    if ($stmt->execute()) {
                        $serviceId = $conn->insert_id;
                        
                        // معالجة الخصائص
                        // Process attributes
                        if (isset($_POST['attribute_name']) && is_array($_POST['attribute_name'])) {
                            foreach ($_POST['attribute_name'] as $key => $attrName) {
                                if (!empty($attrName) && isset($_POST['attribute_value'][$key]) && !empty($_POST['attribute_value'][$key])) {
                                    $attrValue = $_POST['attribute_value'][$key];
                                    
                                    $stmt = $conn->prepare("
                                        INSERT INTO service_attributes (service_id, name, value, created_at)
                                        VALUES (?, ?, ?, NOW())
                                    ");
                                    
                                    $stmt->bind_param("iss", $serviceId, $attrName, $attrValue);
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
                                    $uploadDir = '../../assets/images/services/gallery/';
                                    $fileName = time() . '_' . $i . '_' . basename($additionalImages['name'][$i]);
                                    $uploadFile = $uploadDir . $fileName;
                                    
                                    // التحقق من نوع الملف
                                    // Check file type
                                    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                                    if (in_array($additionalImages['type'][$i], $allowedTypes)) {
                                        if (move_uploaded_file($additionalImages['tmp_name'][$i], $uploadFile)) {
                                            $stmt = $conn->prepare("
                                                INSERT INTO service_images (service_id, image, sort_order, created_at)
                                                VALUES (?, ?, ?, NOW())
                                            ");
                                            
                                            $sortOrder = $i + 1;
                                            $stmt->bind_param("isi", $serviceId, $fileName, $sortOrder);
                                            $stmt->execute();
                                        }
                                    }
                                }
                            }
                        }
                        
                        // إضافة إشعار
                        // Add notification
                        addNotification('تمت إضافة خدمة جديدة: ' . $name, 'service');
                        
                        // إعادة التوجيه إلى قائمة الخدمات
                        // Redirect to services list
                        redirect('index.php?success=تمت إضافة الخدمة بنجاح');
                        exit;
                    } else {
                        $errors[] = 'حدث خطأ أثناء إضافة الخدمة';
                    }
                }
            }
        }
        
        // الحصول على فئات الخدمات
        // Get service categories
        $stmt = $conn->prepare("SELECT * FROM categories WHERE type = 'service' ORDER BY name");
        $stmt->execute();
        $categories = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // تضمين ملف الرأس
        // Include header file
        include '../includes/header.php';
        
        // عرض نموذج إضافة خدمة
        // Display add service form
        include 'templates/add_service.php';
        
        break;
        
    case 'edit':
        // تحرير خدمة
        // Edit service
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            redirect('index.php');
            exit;
        }
        
        $serviceId = (int)$_GET['id'];
        
        // الحصول على بيانات الخدمة
        // Get service data
        $stmt = $conn->prepare("SELECT * FROM services WHERE id = ?");
        $stmt->bind_param("i", $serviceId);
        $stmt->execute();
        $service = $stmt->get_result()->fetch_assoc();
        
        if (!$service) {
            redirect('index.php?error=الخدمة غير موجودة');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // التحقق من البيانات
            // Validate data
            $name = trim($_POST['name']);
            $description = trim($_POST['description']);
            $price = (float)$_POST['price'];
            $category_id = (int)$_POST['category_id'];
            $duration = trim($_POST['duration']);
            $status = $_POST['status'];
            
            $errors = [];
            
            if (empty($name)) {
                $errors[] = 'اسم الخدمة مطلوب';
            }
            
            if (empty($description)) {
                $errors[] = 'وصف الخدمة مطلوب';
            }
            
            if ($price <= 0) {
                $errors[] = 'السعر يجب أن يكون أكبر من صفر';
            }
            
            if ($category_id <= 0) {
                $errors[] = 'يجب اختيار فئة صالحة';
            }
            
            // إذا لم تكن هناك أخطاء، تحديث الخدمة
            // If no errors, update service
            if (empty($errors)) {
                // معالجة الصورة
                // Process image
                $image = $service['image'];
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = '../../assets/images/services/';
                    $fileName = time() . '_' . basename($_FILES['image']['name']);
                    $uploadFile = $uploadDir . $fileName;
                    
                    // التحقق من نوع الملف
                    // Check file type
                    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                    if (in_array($_FILES['image']['type'], $allowedTypes)) {
                        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
                            // حذف الصورة القديمة إذا كانت موجودة
                            // Delete old image if exists
                            if (!empty($service['image']) && file_exists($uploadDir . $service['image'])) {
                                unlink($uploadDir . $service['image']);
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
                    // تحديث الخدمة في قاعدة البيانات
                    // Update service in database
                    $stmt = $conn->prepare("
                        UPDATE services 
                        SET name = ?, description = ?, price = ?, category_id = ?, duration = ?, image = ?, status = ?, updated_at = NOW()
                        WHERE id = ?
                    ");
                    
                    $stmt->bind_param("sssisssi", $name, $description, $price, $category_id, $duration, $image, $status, $serviceId);
                    
                    if ($stmt->execute()) {
                        // حذف الخصائص الحالية
                        // Delete current attributes
                        $stmt = $conn->prepare("DELETE FROM service_attributes WHERE service_id = ?");
                        $stmt->bind_param("i", $serviceId);
                        $stmt->execute();
                        
                        // إضافة الخصائص الجديدة
                        // Add new attributes
                        if (isset($_POST['attribute_name']) && is_array($_POST['attribute_name'])) {
                            foreach ($_POST['attribute_name'] as $key => $attrName) {
                                if (!empty($attrName) && isset($_POST['attribute_value'][$key]) && !empty($_POST['attribute_value'][$key])) {
                                    $attrValue = $_POST['attribute_value'][$key];
                                    
                                    $stmt = $conn->prepare("
                                        INSERT INTO service_attributes (service_id, name, value, created_at)
                                        VALUES (?, ?, ?, NOW())
                                    ");
                                    
                                    $stmt->bind_param("iss", $serviceId, $attrName, $attrValue);
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
                                    $uploadDir = '../../assets/images/services/gallery/';
                                    $fileName = time() . '_' . $i . '_' . basename($additionalImages['name'][$i]);
                                    $uploadFile = $uploadDir . $fileName;
                                    
                                    // التحقق من نوع الملف
                                    // Check file type
                                    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                                    if (in_array($additionalImages['type'][$i], $allowedTypes)) {
                                        if (move_uploaded_file($additionalImages['tmp_name'][$i], $uploadFile)) {
                                            $stmt = $conn->prepare("
                                                INSERT INTO service_images (service_id, image, sort_order, created_at)
                                                VALUES (?, ?, ?, NOW())
                                            ");
                                            
                                            $sortOrder = $i + 1;
                                            $stmt->bind_param("isi", $serviceId, $fileName, $sortOrder);
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
                                $stmt = $conn->prepare("SELECT image FROM service_images WHERE id = ? AND service_id = ?");
                                $stmt->bind_param("ii", $imageId, $serviceId);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                
                                if ($result->num_rows > 0) {
                                    $imageName = $result->fetch_assoc()['image'];
                                    $uploadDir = '../../assets/images/services/gallery/';
                                    
                                    if (file_exists($uploadDir . $imageName)) {
                                        unlink($uploadDir . $imageName);
                                    }
                                    
                                    $stmt = $conn->prepare("DELETE FROM service_images WHERE id = ?");
                                    $stmt->bind_param("i", $imageId);
                                    $stmt->execute();
                                }
                            }
                        }
                        
                        // إضافة إشعار
                        // Add notification
                        addNotification('تم تحديث الخدمة: ' . $name, 'service');
                        
                        // إعادة التوجيه إلى قائمة الخدمات
                        // Redirect to services list
                        redirect('index.php?success=تم تحديث الخدمة بنجاح');
                        exit;
                    } else {
                        $errors[] = 'حدث خطأ أثناء تحديث الخدمة';
                    }
                }
            }
        }
        
        // الحصول على فئات الخدمات
        // Get service categories
        $stmt = $conn->prepare("SELECT * FROM categories WHERE type = 'service' ORDER BY name");
        $stmt->execute();
        $categories = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // الحصول على خصائص الخدمة
        // Get service attributes
        $stmt = $conn->prepare("SELECT * FROM service_attributes WHERE service_id = ? ORDER BY id");
        $stmt->bind_param("i", $serviceId);
        $stmt->execute();
        $attributes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // الحصول على صور الخدمة
        // Get service images
        $stmt = $conn->prepare("SELECT * FROM service_images WHERE service_id = ? ORDER BY sort_order");
        $stmt->bind_param("i", $serviceId);
        $stmt->execute();
        $serviceImages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // تضمين ملف الرأس
        // Include header file
        include '../includes/header.php';
        
        // عرض نموذج تحرير خدمة
        // Display edit service form
        include 'templates/edit_service.php';
        
        break;
        
    case 'delete':
        // حذف خدمة
        // Delete service
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            redirect('index.php');
            exit;
        }
        
        $serviceId = (int)$_GET['id'];
        
        // الحصول على بيانات الخدمة
        // Get service data
        $stmt = $conn->prepare("SELECT * FROM services WHERE id = ?");
        $stmt->bind_param("i", $serviceId);
        $stmt->execute();
        $service = $stmt->get_result()->fetch_assoc();
        
        if (!$service) {
            redirect('index.php?error=الخدمة غير موجودة');
            exit;
        }
        
        // حذف صورة الخدمة
        // Delete service image
        if (!empty($service['image'])) {
            $uploadDir = '../../assets/images/services/';
            if (file_exists($uploadDir . $service['image'])) {
                unlink($uploadDir . $service['image']);
            }
        }
        
        // حذف صور الخدمة الإضافية
        // Delete additional service images
        $stmt = $conn->prepare("SELECT image FROM service_images WHERE service_id = ?");
        $stmt->bind_param("i", $serviceId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $uploadDir = '../../assets/images/services/gallery/';
            if (file_exists($uploadDir . $row['image'])) {
                unlink($uploadDir . $row['image']);
            }
        }
        
        // حذف خصائص الخدمة
        // Delete service attributes
        $stmt = $conn->prepare("DELETE FROM service_attributes WHERE service_id = ?");
        $stmt->bind_param("i", $serviceId);
        $stmt->execute();
        
        // حذف صور الخدمة
        // Delete service images
        $stmt = $conn->prepare("DELETE FROM service_images WHERE service_id = ?");
        $stmt->bind_param("i", $serviceId);
        $stmt->execute();
        
        // حذف الخدمة
        // Delete service
        $stmt = $conn->prepare("DELETE FROM services WHERE id = ?");
        $stmt->bind_param("i", $serviceId);
        
        if ($stmt->execute()) {
            // إضافة إشعار
            // Add notification
            addNotification('تم حذف الخدمة: ' . $service['name'], 'service');
            
            // إعادة التوجيه إلى قائمة الخدمات
            // Redirect to services list
            redirect('index.php?success=تم حذف الخدمة بنجاح');
            exit;
        } else {
            redirect('index.php?error=حدث خطأ أثناء حذف الخدمة');
            exit;
        }
        
        break;
        
    case 'view':
        // عرض تفاصيل الخدمة
        // View service details
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            redirect('index.php');
            exit;
        }
        
        $serviceId = (int)$_GET['id'];
        
        // الحصول على بيانات الخدمة
        // Get service data
        $stmt = $conn->prepare("
            SELECT s.*, c.name as category_name 
            FROM services s 
            LEFT JOIN categories c ON s.category_id = c.id 
            WHERE s.id = ?
        ");
        $stmt->bind_param("i", $serviceId);
        $stmt->execute();
        $service = $stmt->get_result()->fetch_assoc();
        
        if (!$service) {
            redirect('index.php?error=الخدمة غير موجودة');
            exit;
        }
        
        // الحصول على خصائص الخدمة
        // Get service attributes
        $stmt = $conn->prepare("SELECT * FROM service_attributes WHERE service_id = ? ORDER BY id");
        $stmt->bind_param("i", $serviceId);
        $stmt->execute();
        $attributes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // الحصول على صور الخدمة
        // Get service images
        $stmt = $conn->prepare("SELECT * FROM service_images WHERE service_id = ? ORDER BY sort_order");
        $stmt->bind_param("i", $serviceId);
        $stmt->execute();
        $serviceImages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // الحصول على مراجعات الخدمة
        // Get service reviews
        $stmt = $conn->prepare("
            SELECT r.*, u.name as user_name 
            FROM reviews r 
            LEFT JOIN users u ON r.user_id = u.id 
            WHERE r.service_id = ? 
            ORDER BY r.created_at DESC
        ");
        $stmt->bind_param("i", $serviceId);
        $stmt->execute();
        $reviews = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // تضمين ملف الرأس
        // Include header file
        include '../includes/header.php';
        
        // عرض تفاصيل الخدمة
        // Display service details
        include 'templates/view_service.php';
        
        break;
        
    default:
        // قائمة الخدمات
        // Services list
        
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
        $query = "SELECT s.*, c.name as category_name FROM services s LEFT JOIN categories c ON s.category_id = c.id WHERE 1=1";
        $countQuery = "SELECT COUNT(*) as total FROM services s LEFT JOIN categories c ON s.category_id = c.id WHERE 1=1";
        $params = [];
        $types = "";
        
        if (!empty($search)) {
            $searchTerm = "%$search%";
            $query .= " AND (s.name LIKE ? OR s.description LIKE ?)";
            $countQuery .= " AND (s.name LIKE ? OR s.description LIKE ?)";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= "ss";
        }
        
        if ($category > 0) {
            $query .= " AND s.category_id = ?";
            $countQuery .= " AND s.category_id = ?";
            $params[] = $category;
            $types .= "i";
        }
        
        if (!empty($status)) {
            $query .= " AND s.status = ?";
            $countQuery .= " AND s.status = ?";
            $params[] = $status;
            $types .= "s";
        }
        
        // إضافة الترتيب
        // Add sorting
        $allowedSortFields = ['id', 'name', 'price', 'created_at'];
        $allowedOrderDirections = ['ASC', 'DESC'];
        
        if (!in_array($sort, $allowedSortFields)) {
            $sort = 'id';
        }
        
        if (!in_array($order, $allowedOrderDirections)) {
            $order = 'DESC';
        }
        
        $query .= " ORDER BY s.$sort $order LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $types .= "ii";
        
        // تنفيذ استعلام العدد الإجمالي
        // Execute count query
        $stmt = $conn->prepare($countQuery);
        if (!empty($types) && count($params) > 0) {
            $stmt->bind_param(substr($types, 0, -2), ...array_slice($params, 0, -2));
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
        $services = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // حساب عدد الصفحات
        // Calculate total pages
        $totalPages = ceil($total / $limit);
        
        // الحصول على فئات الخدمات
        // Get service categories
        $stmt = $conn->prepare("SELECT * FROM categories WHERE type = 'service' ORDER BY name");
        $stmt->execute();
        $categories = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // تضمين ملف الرأس
        // Include header file
        include '../includes/header.php';
        
        // عرض قائمة الخدمات
        // Display services list
        include 'templates/list_services.php';
        
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

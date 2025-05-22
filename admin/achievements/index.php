<?php
/**
 * صفحة إدارة الإنجازات
 * Achievements Management Page
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
$pageTitle = 'إدارة الإنجازات | ' . SITE_NAME;
$activeMenu = 'achievements';

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
        // إضافة إنجاز جديد
        // Add new achievement
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // التحقق من البيانات
            // Validate data
            $title = trim($_POST['title']);
            $description = trim($_POST['description']);
            $client_name = trim($_POST['client_name']);
            $completion_date = trim($_POST['completion_date']);
            $is_featured = isset($_POST['is_featured']) ? 1 : 0;
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            $category_id = (int)$_POST['category_id'];
            
            $errors = [];
            
            if (empty($title)) {
                $errors[] = 'عنوان الإنجاز مطلوب';
            }
            
            if (empty($description)) {
                $errors[] = 'وصف الإنجاز مطلوب';
            }
            
            if (empty($client_name)) {
                $errors[] = 'اسم العميل مطلوب';
            }
            
            // معالجة الصورة
            // Process image
            $image = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = '../../assets/images/achievements/';
                
                // التأكد من وجود المجلد
                // Make sure directory exists
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $fileName = uniqid('achievement_') . '.' . pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
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
                    $errors[] = 'نوع ملف الصورة غير مدعوم. يرجى تحميل صورة بتنسيق JPEG أو PNG أو GIF أو WebP';
                }
            } else {
                $errors[] = 'الصورة مطلوبة';
            }
            
            // إذا لم تكن هناك أخطاء، إضافة الإنجاز
            // If no errors, add achievement
            if (empty($errors)) {
                // إضافة الإنجاز
                // Add achievement
                $stmt = $conn->prepare("
                    INSERT INTO achievements (
                        title, description, client_name, completion_date, 
                        image, is_featured, is_active, category_id, created_at, updated_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
                ");
                
                $stmt->bind_param(
                    "sssssiii",
                    $title, $description, $client_name, $completion_date,
                    $image, $is_featured, $is_active, $category_id
                );
                
                if ($stmt->execute()) {
                    // إعادة التوجيه إلى قائمة الإنجازات
                    // Redirect to achievements list
                    redirect('index.php?success=تم إضافة الإنجاز بنجاح');
                    exit;
                } else {
                    $errors[] = 'حدث خطأ أثناء إضافة الإنجاز';
                }
            }
        }
        
        // الحصول على قائمة الفئات
        // Get categories list
        $stmt = $conn->prepare("SELECT id, name FROM categories WHERE type = 'achievement' ORDER BY name");
        $stmt->execute();
        $categories = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // تضمين ملف الرأس
        // Include header file
        include '../includes/header.php';
        
        // عرض نموذج إضافة إنجاز
        // Display add achievement form
        include 'templates/add_achievement.php';
        
        break;
        
    case 'edit':
        // تعديل إنجاز
        // Edit achievement
        
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            redirect('index.php');
            exit;
        }
        
        $achievementId = (int)$_GET['id'];
        
        // الحصول على بيانات الإنجاز
        // Get achievement data
        $stmt = $conn->prepare("SELECT * FROM achievements WHERE id = ?");
        $stmt->bind_param("i", $achievementId);
        $stmt->execute();
        $achievement = $stmt->get_result()->fetch_assoc();
        
        if (!$achievement) {
            redirect('index.php?error=الإنجاز غير موجود');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // التحقق من البيانات
            // Validate data
            $title = trim($_POST['title']);
            $description = trim($_POST['description']);
            $client_name = trim($_POST['client_name']);
            $completion_date = trim($_POST['completion_date']);
            $is_featured = isset($_POST['is_featured']) ? 1 : 0;
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            $category_id = (int)$_POST['category_id'];
            
            $errors = [];
            
            if (empty($title)) {
                $errors[] = 'عنوان الإنجاز مطلوب';
            }
            
            if (empty($description)) {
                $errors[] = 'وصف الإنجاز مطلوب';
            }
            
            if (empty($client_name)) {
                $errors[] = 'اسم العميل مطلوب';
            }
            
            // معالجة الصورة
            // Process image
            $image = $achievement['image'];
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = '../../assets/images/achievements/';
                
                // التأكد من وجود المجلد
                // Make sure directory exists
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $fileName = uniqid('achievement_') . '.' . pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $uploadFile = $uploadDir . $fileName;
                
                // التحقق من نوع الملف
                // Check file type
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                if (in_array($_FILES['image']['type'], $allowedTypes)) {
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
                        // حذف الصورة القديمة
                        // Delete old image
                        if (!empty($achievement['image']) && file_exists($uploadDir . $achievement['image'])) {
                            unlink($uploadDir . $achievement['image']);
                        }
                        
                        $image = $fileName;
                    } else {
                        $errors[] = 'فشل في تحميل الصورة';
                    }
                } else {
                    $errors[] = 'نوع ملف الصورة غير مدعوم. يرجى تحميل صورة بتنسيق JPEG أو PNG أو GIF أو WebP';
                }
            }
            
            // إذا لم تكن هناك أخطاء، تحديث الإنجاز
            // If no errors, update achievement
            if (empty($errors)) {
                // تحديث الإنجاز
                // Update achievement
                $stmt = $conn->prepare("
                    UPDATE achievements SET
                        title = ?, description = ?, client_name = ?, completion_date = ?,
                        image = ?, is_featured = ?, is_active = ?, category_id = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                
                $stmt->bind_param(
                    "sssssiii",
                    $title, $description, $client_name, $completion_date,
                    $image, $is_featured, $is_active, $category_id, $achievementId
                );
                
                if ($stmt->execute()) {
                    // إعادة التوجيه إلى قائمة الإنجازات
                    // Redirect to achievements list
                    redirect('index.php?success=تم تحديث الإنجاز بنجاح');
                    exit;
                } else {
                    $errors[] = 'حدث خطأ أثناء تحديث الإنجاز';
                }
            }
        }
        
        // الحصول على قائمة الفئات
        // Get categories list
        $stmt = $conn->prepare("SELECT id, name FROM categories WHERE type = 'achievement' ORDER BY name");
        $stmt->execute();
        $categories = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // تضمين ملف الرأس
        // Include header file
        include '../includes/header.php';
        
        // عرض نموذج تعديل إنجاز
        // Display edit achievement form
        include 'templates/edit_achievement.php';
        
        break;
        
    case 'delete':
        // حذف إنجاز
        // Delete achievement
        
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            redirect('index.php');
            exit;
        }
        
        $achievementId = (int)$_GET['id'];
        
        // الحصول على بيانات الإنجاز
        // Get achievement data
        $stmt = $conn->prepare("SELECT * FROM achievements WHERE id = ?");
        $stmt->bind_param("i", $achievementId);
        $stmt->execute();
        $achievement = $stmt->get_result()->fetch_assoc();
        
        if (!$achievement) {
            redirect('index.php?error=الإنجاز غير موجود');
            exit;
        }
        
        // حذف الصورة
        // Delete image
        $uploadDir = '../../assets/images/achievements/';
        if (!empty($achievement['image']) && file_exists($uploadDir . $achievement['image'])) {
            unlink($uploadDir . $achievement['image']);
        }
        
        // حذف الإنجاز
        // Delete achievement
        $stmt = $conn->prepare("DELETE FROM achievements WHERE id = ?");
        $stmt->bind_param("i", $achievementId);
        
        if ($stmt->execute()) {
            // إعادة التوجيه إلى قائمة الإنجازات
            // Redirect to achievements list
            redirect('index.php?success=تم حذف الإنجاز بنجاح');
            exit;
        } else {
            redirect('index.php?error=حدث خطأ أثناء حذف الإنجاز');
            exit;
        }
        
        break;
        
    case 'toggle_status':
        // تغيير حالة الإنجاز
        // Toggle achievement status
        
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            redirect('index.php');
            exit;
        }
        
        $achievementId = (int)$_GET['id'];
        
        // الحصول على بيانات الإنجاز
        // Get achievement data
        $stmt = $conn->prepare("SELECT * FROM achievements WHERE id = ?");
        $stmt->bind_param("i", $achievementId);
        $stmt->execute();
        $achievement = $stmt->get_result()->fetch_assoc();
        
        if (!$achievement) {
            redirect('index.php?error=الإنجاز غير موجود');
            exit;
        }
        
        // تغيير حالة الإنجاز
        // Toggle achievement status
        $newStatus = $achievement['is_active'] ? 0 : 1;
        
        $stmt = $conn->prepare("UPDATE achievements SET is_active = ?, updated_at = NOW() WHERE id = ?");
        $stmt->bind_param("ii", $newStatus, $achievementId);
        
        if ($stmt->execute()) {
            // إعادة التوجيه إلى قائمة الإنجازات
            // Redirect to achievements list
            $statusMessage = $newStatus ? 'تم تفعيل الإنجاز بنجاح' : 'تم إلغاء تفعيل الإنجاز بنجاح';
            redirect('index.php?success=' . $statusMessage);
            exit;
        } else {
            redirect('index.php?error=حدث خطأ أثناء تغيير حالة الإنجاز');
            exit;
        }
        
        break;
        
    case 'toggle_featured':
        // تغيير حالة العرض المميز للإنجاز
        // Toggle achievement featured status
        
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            redirect('index.php');
            exit;
        }
        
        $achievementId = (int)$_GET['id'];
        
        // الحصول على بيانات الإنجاز
        // Get achievement data
        $stmt = $conn->prepare("SELECT * FROM achievements WHERE id = ?");
        $stmt->bind_param("i", $achievementId);
        $stmt->execute();
        $achievement = $stmt->get_result()->fetch_assoc();
        
        if (!$achievement) {
            redirect('index.php?error=الإنجاز غير موجود');
            exit;
        }
        
        // تغيير حالة العرض المميز للإنجاز
        // Toggle achievement featured status
        $newStatus = $achievement['is_featured'] ? 0 : 1;
        
        $stmt = $conn->prepare("UPDATE achievements SET is_featured = ?, updated_at = NOW() WHERE id = ?");
        $stmt->bind_param("ii", $newStatus, $achievementId);
        
        if ($stmt->execute()) {
            // إعادة التوجيه إلى قائمة الإنجازات
            // Redirect to achievements list
            $statusMessage = $newStatus ? 'تم تعيين الإنجاز كمميز بنجاح' : 'تم إلغاء تعيين الإنجاز كمميز بنجاح';
            redirect('index.php?success=' . $statusMessage);
            exit;
        } else {
            redirect('index.php?error=حدث خطأ أثناء تغيير حالة العرض المميز للإنجاز');
            exit;
        }
        
        break;
        
    default:
        // قائمة الإنجازات
        // Achievements list
        
        // تهيئة متغيرات البحث والترتيب
        // Initialize search and sort variables
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $categoryId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
        $status = isset($_GET['status']) ? $_GET['status'] : '';
        $featured = isset($_GET['featured']) ? $_GET['featured'] : '';
        $sort = isset($_GET['sort']) ? $_GET['sort'] : 'created_at';
        $order = isset($_GET['order']) ? $_GET['order'] : 'DESC';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        // بناء استعلام البحث
        // Build search query
        $query = "
            SELECT a.*, c.name as category_name
            FROM achievements a
            LEFT JOIN categories c ON a.category_id = c.id
            WHERE 1=1
        ";
        
        $countQuery = "SELECT COUNT(*) as total FROM achievements a WHERE 1=1";
        
        $params = [];
        $types = "";
        
        if (!empty($search)) {
            $searchTerm = "%{$search}%";
            $query .= " AND (a.title LIKE ? OR a.description LIKE ? OR a.client_name LIKE ?)";
            $countQuery .= " AND (a.title LIKE ? OR a.description LIKE ? OR a.client_name LIKE ?)";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= "sss";
        }
        
        if ($categoryId > 0) {
            $query .= " AND a.category_id = ?";
            $countQuery .= " AND a.category_id = ?";
            $params[] = $categoryId;
            $types .= "i";
        }
        
        if ($status !== '') {
            $statusValue = (int)$status;
            $query .= " AND a.is_active = ?";
            $countQuery .= " AND a.is_active = ?";
            $params[] = $statusValue;
            $types .= "i";
        }
        
        if ($featured !== '') {
            $featuredValue = (int)$featured;
            $query .= " AND a.is_featured = ?";
            $countQuery .= " AND a.is_featured = ?";
            $params[] = $featuredValue;
            $types .= "i";
        }
        
        // إضافة الترتيب
        // Add sorting
        $allowedSortFields = ['id', 'title', 'client_name', 'completion_date', 'is_active', 'is_featured', 'created_at', 'updated_at'];
        $allowedOrderDirections = ['ASC', 'DESC'];
        
        if (!in_array($sort, $allowedSortFields)) {
            $sort = 'created_at';
        }
        
        if (!in_array($order, $allowedOrderDirections)) {
            $order = 'DESC';
        }
        
        $query .= " ORDER BY a.{$sort} {$order} LIMIT ? OFFSET ?";
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
        $achievements = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // الحصول على قائمة الفئات
        // Get categories list
        $stmt = $conn->prepare("SELECT id, name FROM categories WHERE type = 'achievement' ORDER BY name");
        $stmt->execute();
        $categories = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // حساب عدد الصفحات
        // Calculate total pages
        $totalPages = ceil($total / $limit);
        
        // تضمين ملف الرأس
        // Include header file
        include '../includes/header.php';
        
        // عرض قائمة الإنجازات
        // Display achievements list
        include 'templates/list_achievements.php';
        
        break;
}

// تضمين ملف التذييل
// Include footer file
include '../includes/footer.php';
?>

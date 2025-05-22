<?php
/**
 * صفحة إدارة المستخدمين
 * Users Management Page
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
$pageTitle = 'إدارة المستخدمين | ' . SITE_NAME;
$activeMenu = 'users';

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
        // إضافة مستخدم جديد
        // Add new user
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // التحقق من البيانات
            // Validate data
            $name = trim($_POST['name']);
            $email = trim($_POST['email']);
            $password = trim($_POST['password']);
            $confirm_password = trim($_POST['confirm_password']);
            $role = $_POST['role'];
            $status = $_POST['status'];
            
            $errors = [];
            
            if (empty($name)) {
                $errors[] = 'اسم المستخدم مطلوب';
            }
            
            if (empty($email)) {
                $errors[] = 'البريد الإلكتروني مطلوب';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'البريد الإلكتروني غير صالح';
            } else {
                // التحقق من عدم تكرار البريد الإلكتروني
                // Check if email already exists
                $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $errors[] = 'البريد الإلكتروني مستخدم بالفعل';
                }
            }
            
            if (empty($password)) {
                $errors[] = 'كلمة المرور مطلوبة';
            } elseif (strlen($password) < 6) {
                $errors[] = 'كلمة المرور يجب أن تكون على الأقل 6 أحرف';
            } elseif ($password !== $confirm_password) {
                $errors[] = 'كلمة المرور وتأكيدها غير متطابقين';
            }
            
            // إذا لم تكن هناك أخطاء، إضافة المستخدم
            // If no errors, add user
            if (empty($errors)) {
                // معالجة الصورة
                // Process image
                $profile_image = '';
                if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = '../../assets/images/users/';
                    $fileName = time() . '_' . basename($_FILES['profile_image']['name']);
                    $uploadFile = $uploadDir . $fileName;
                    
                    // التحقق من نوع الملف
                    // Check file type
                    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                    if (in_array($_FILES['profile_image']['type'], $allowedTypes)) {
                        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadFile)) {
                            $profile_image = $fileName;
                        } else {
                            $errors[] = 'فشل في تحميل الصورة';
                        }
                    } else {
                        $errors[] = 'نوع الملف غير مدعوم. يرجى تحميل صورة بتنسيق JPEG أو PNG أو GIF أو WebP';
                    }
                }
                
                if (empty($errors)) {
                    // تشفير كلمة المرور
                    // Hash password
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    
                    // إضافة المستخدم إلى قاعدة البيانات
                    // Add user to database
                    $stmt = $conn->prepare("
                        INSERT INTO users (name, email, password, role, status, profile_image, created_at, updated_at)
                        VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
                    ");
                    
                    $stmt->bind_param("ssssss", $name, $email, $hashed_password, $role, $status, $profile_image);
                    
                    if ($stmt->execute()) {
                        $userId = $conn->insert_id;
                        
                        // إضافة إشعار
                        // Add notification
                        addNotification('تمت إضافة مستخدم جديد: ' . $name, 'user');
                        
                        // إعادة التوجيه إلى قائمة المستخدمين
                        // Redirect to users list
                        redirect('index.php?success=تمت إضافة المستخدم بنجاح');
                        exit;
                    } else {
                        $errors[] = 'حدث خطأ أثناء إضافة المستخدم';
                    }
                }
            }
        }
        
        // تضمين ملف الرأس
        // Include header file
        include '../includes/header.php';
        
        // عرض نموذج إضافة مستخدم
        // Display add user form
        include 'templates/add_user.php';
        
        break;
        
    case 'edit':
        // تحرير مستخدم
        // Edit user
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            redirect('index.php');
            exit;
        }
        
        $userId = (int)$_GET['id'];
        
        // الحصول على بيانات المستخدم
        // Get user data
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        
        if (!$user) {
            redirect('index.php?error=المستخدم غير موجود');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // التحقق من البيانات
            // Validate data
            $name = trim($_POST['name']);
            $email = trim($_POST['email']);
            $password = trim($_POST['password']);
            $confirm_password = trim($_POST['confirm_password']);
            $role = $_POST['role'];
            $status = $_POST['status'];
            
            $errors = [];
            
            if (empty($name)) {
                $errors[] = 'اسم المستخدم مطلوب';
            }
            
            if (empty($email)) {
                $errors[] = 'البريد الإلكتروني مطلوب';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'البريد الإلكتروني غير صالح';
            } else {
                // التحقق من عدم تكرار البريد الإلكتروني
                // Check if email already exists
                $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                $stmt->bind_param("si", $email, $userId);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $errors[] = 'البريد الإلكتروني مستخدم بالفعل';
                }
            }
            
            if (!empty($password) && strlen($password) < 6) {
                $errors[] = 'كلمة المرور يجب أن تكون على الأقل 6 أحرف';
            } elseif (!empty($password) && $password !== $confirm_password) {
                $errors[] = 'كلمة المرور وتأكيدها غير متطابقين';
            }
            
            // إذا لم تكن هناك أخطاء، تحديث المستخدم
            // If no errors, update user
            if (empty($errors)) {
                // معالجة الصورة
                // Process image
                $profile_image = $user['profile_image'];
                if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = '../../assets/images/users/';
                    $fileName = time() . '_' . basename($_FILES['profile_image']['name']);
                    $uploadFile = $uploadDir . $fileName;
                    
                    // التحقق من نوع الملف
                    // Check file type
                    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                    if (in_array($_FILES['profile_image']['type'], $allowedTypes)) {
                        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadFile)) {
                            // حذف الصورة القديمة إذا كانت موجودة
                            // Delete old image if exists
                            if (!empty($user['profile_image']) && file_exists($uploadDir . $user['profile_image'])) {
                                unlink($uploadDir . $user['profile_image']);
                            }
                            
                            $profile_image = $fileName;
                        } else {
                            $errors[] = 'فشل في تحميل الصورة';
                        }
                    } else {
                        $errors[] = 'نوع الملف غير مدعوم. يرجى تحميل صورة بتنسيق JPEG أو PNG أو GIF أو WebP';
                    }
                }
                
                if (empty($errors)) {
                    // تحديث المستخدم في قاعدة البيانات
                    // Update user in database
                    if (!empty($password)) {
                        // تشفير كلمة المرور الجديدة
                        // Hash new password
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        
                        $stmt = $conn->prepare("
                            UPDATE users 
                            SET name = ?, email = ?, password = ?, role = ?, status = ?, profile_image = ?, updated_at = NOW()
                            WHERE id = ?
                        ");
                        
                        $stmt->bind_param("ssssssi", $name, $email, $hashed_password, $role, $status, $profile_image, $userId);
                    } else {
                        $stmt = $conn->prepare("
                            UPDATE users 
                            SET name = ?, email = ?, role = ?, status = ?, profile_image = ?, updated_at = NOW()
                            WHERE id = ?
                        ");
                        
                        $stmt->bind_param("sssssi", $name, $email, $role, $status, $profile_image, $userId);
                    }
                    
                    if ($stmt->execute()) {
                        // إضافة إشعار
                        // Add notification
                        addNotification('تم تحديث المستخدم: ' . $name, 'user');
                        
                        // إعادة التوجيه إلى قائمة المستخدمين
                        // Redirect to users list
                        redirect('index.php?success=تم تحديث المستخدم بنجاح');
                        exit;
                    } else {
                        $errors[] = 'حدث خطأ أثناء تحديث المستخدم';
                    }
                }
            }
        }
        
        // تضمين ملف الرأس
        // Include header file
        include '../includes/header.php';
        
        // عرض نموذج تحرير مستخدم
        // Display edit user form
        include 'templates/edit_user.php';
        
        break;
        
    case 'delete':
        // حذف مستخدم
        // Delete user
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            redirect('index.php');
            exit;
        }
        
        $userId = (int)$_GET['id'];
        
        // التحقق من عدم حذف المستخدم الحالي
        // Check if not deleting current user
        if ($userId === (int)$_SESSION['user_id']) {
            redirect('index.php?error=لا يمكنك حذف حسابك الحالي');
            exit;
        }
        
        // الحصول على بيانات المستخدم
        // Get user data
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        
        if (!$user) {
            redirect('index.php?error=المستخدم غير موجود');
            exit;
        }
        
        // حذف صورة المستخدم
        // Delete user image
        if (!empty($user['profile_image'])) {
            $uploadDir = '../../assets/images/users/';
            if (file_exists($uploadDir . $user['profile_image'])) {
                unlink($uploadDir . $user['profile_image']);
            }
        }
        
        // حذف المستخدم
        // Delete user
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        
        if ($stmt->execute()) {
            // إضافة إشعار
            // Add notification
            addNotification('تم حذف المستخدم: ' . $user['name'], 'user');
            
            // إعادة التوجيه إلى قائمة المستخدمين
            // Redirect to users list
            redirect('index.php?success=تم حذف المستخدم بنجاح');
            exit;
        } else {
            redirect('index.php?error=حدث خطأ أثناء حذف المستخدم');
            exit;
        }
        
        break;
        
    case 'view':
        // عرض تفاصيل المستخدم
        // View user details
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            redirect('index.php');
            exit;
        }
        
        $userId = (int)$_GET['id'];
        
        // الحصول على بيانات المستخدم
        // Get user data
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        
        if (!$user) {
            redirect('index.php?error=المستخدم غير موجود');
            exit;
        }
        
        // الحصول على طلبات المستخدم
        // Get user orders
        $stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // الحصول على مدفوعات المستخدم
        // Get user payments
        $stmt = $conn->prepare("SELECT * FROM payments WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $payments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // الحصول على مراجعات المستخدم
        // Get user reviews
        $stmt = $conn->prepare("
            SELECT r.*, p.name as product_name, s.name as service_name 
            FROM reviews r 
            LEFT JOIN products p ON r.product_id = p.id 
            LEFT JOIN services s ON r.service_id = s.id 
            WHERE r.user_id = ? 
            ORDER BY r.created_at DESC 
            LIMIT 5
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $reviews = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // تضمين ملف الرأس
        // Include header file
        include '../includes/header.php';
        
        // عرض تفاصيل المستخدم
        // Display user details
        include 'templates/view_user.php';
        
        break;
        
    default:
        // قائمة المستخدمين
        // Users list
        
        // تهيئة متغيرات البحث والترتيب
        // Initialize search and sort variables
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $role = isset($_GET['role']) ? $_GET['role'] : '';
        $status = isset($_GET['status']) ? $_GET['status'] : '';
        $sort = isset($_GET['sort']) ? $_GET['sort'] : 'id';
        $order = isset($_GET['order']) ? $_GET['order'] : 'DESC';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;
        
        // بناء استعلام البحث
        // Build search query
        $query = "SELECT * FROM users WHERE 1=1";
        $countQuery = "SELECT COUNT(*) as total FROM users WHERE 1=1";
        $params = [];
        $types = "";
        
        if (!empty($search)) {
            $searchTerm = "%$search%";
            $query .= " AND (name LIKE ? OR email LIKE ?)";
            $countQuery .= " AND (name LIKE ? OR email LIKE ?)";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= "ss";
        }
        
        if (!empty($role)) {
            $query .= " AND role = ?";
            $countQuery .= " AND role = ?";
            $params[] = $role;
            $types .= "s";
        }
        
        if (!empty($status)) {
            $query .= " AND status = ?";
            $countQuery .= " AND status = ?";
            $params[] = $status;
            $types .= "s";
        }
        
        // إضافة الترتيب
        // Add sorting
        $allowedSortFields = ['id', 'name', 'email', 'role', 'status', 'created_at'];
        $allowedOrderDirections = ['ASC', 'DESC'];
        
        if (!in_array($sort, $allowedSortFields)) {
            $sort = 'id';
        }
        
        if (!in_array($order, $allowedOrderDirections)) {
            $order = 'DESC';
        }
        
        $query .= " ORDER BY $sort $order LIMIT ? OFFSET ?";
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
        $users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // حساب عدد الصفحات
        // Calculate total pages
        $totalPages = ceil($total / $limit);
        
        // تضمين ملف الرأس
        // Include header file
        include '../includes/header.php';
        
        // عرض قائمة المستخدمين
        // Display users list
        include 'templates/list_users.php';
        
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

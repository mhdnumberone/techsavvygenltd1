<?php
/**
 * صفحة إدارة الإشعارات
 * Notifications Management Page
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
$pageTitle = 'إدارة الإشعارات | ' . SITE_NAME;
$activeMenu = 'notifications';

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
    case 'mark_read':
        // تحديد الإشعار كمقروء
        // Mark notification as read
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            redirect('index.php');
            exit;
        }
        
        $notificationId = (int)$_GET['id'];
        
        // تحديث حالة الإشعار
        // Update notification status
        $stmt = $conn->prepare("UPDATE notifications SET is_read = 1, updated_at = NOW() WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $notificationId, $_SESSION['user_id']);
        
        if ($stmt->execute()) {
            // إعادة التوجيه إلى قائمة الإشعارات
            // Redirect to notifications list
            redirect('index.php?success=تم تحديد الإشعار كمقروء');
            exit;
        } else {
            redirect('index.php?error=حدث خطأ أثناء تحديث حالة الإشعار');
            exit;
        }
        
        break;
        
    case 'mark_all_read':
        // تحديد جميع الإشعارات كمقروءة
        // Mark all notifications as read
        
        // تحديث حالة جميع الإشعارات
        // Update all notifications status
        $stmt = $conn->prepare("UPDATE notifications SET is_read = 1, updated_at = NOW() WHERE user_id = ? AND is_read = 0");
        $stmt->bind_param("i", $_SESSION['user_id']);
        
        if ($stmt->execute()) {
            // إعادة التوجيه إلى قائمة الإشعارات
            // Redirect to notifications list
            redirect('index.php?success=تم تحديد جميع الإشعارات كمقروءة');
            exit;
        } else {
            redirect('index.php?error=حدث خطأ أثناء تحديث حالة الإشعارات');
            exit;
        }
        
        break;
        
    case 'delete':
        // حذف إشعار
        // Delete notification
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            redirect('index.php');
            exit;
        }
        
        $notificationId = (int)$_GET['id'];
        
        // حذف الإشعار
        // Delete notification
        $stmt = $conn->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $notificationId, $_SESSION['user_id']);
        
        if ($stmt->execute()) {
            // إعادة التوجيه إلى قائمة الإشعارات
            // Redirect to notifications list
            redirect('index.php?success=تم حذف الإشعار بنجاح');
            exit;
        } else {
            redirect('index.php?error=حدث خطأ أثناء حذف الإشعار');
            exit;
        }
        
        break;
        
    case 'delete_all':
        // حذف جميع الإشعارات
        // Delete all notifications
        
        // حذف جميع الإشعارات
        // Delete all notifications
        $stmt = $conn->prepare("DELETE FROM notifications WHERE user_id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        
        if ($stmt->execute()) {
            // إعادة التوجيه إلى قائمة الإشعارات
            // Redirect to notifications list
            redirect('index.php?success=تم حذف جميع الإشعارات بنجاح');
            exit;
        } else {
            redirect('index.php?error=حدث خطأ أثناء حذف الإشعارات');
            exit;
        }
        
        break;
        
    case 'get-unread':
        // الحصول على عدد الإشعارات غير المقروءة
        // Get unread notifications count
        
        // تحديد نوع المحتوى
        // Set content type
        header('Content-Type: application/json');
        
        // الحصول على عدد الإشعارات غير المقروءة
        // Get unread notifications count
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        // إرجاع النتيجة
        // Return result
        echo json_encode(['count' => $result['count']]);
        exit;
        
        break;
        
    default:
        // قائمة الإشعارات
        // Notifications list
        
        // تهيئة متغيرات البحث والترتيب
        // Initialize search and sort variables
        $type = isset($_GET['type']) ? $_GET['type'] : '';
        $is_read = isset($_GET['is_read']) ? (int)$_GET['is_read'] : -1;
        $sort = isset($_GET['sort']) ? $_GET['sort'] : 'created_at';
        $order = isset($_GET['order']) ? $_GET['order'] : 'DESC';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        // بناء استعلام البحث
        // Build search query
        $query = "SELECT * FROM notifications WHERE user_id = ?";
        $countQuery = "SELECT COUNT(*) as total FROM notifications WHERE user_id = ?";
        $params = [$_SESSION['user_id']];
        $types = "i";
        
        if (!empty($type)) {
            $query .= " AND type = ?";
            $countQuery .= " AND type = ?";
            $params[] = $type;
            $types .= "s";
        }
        
        if ($is_read !== -1) {
            $query .= " AND is_read = ?";
            $countQuery .= " AND is_read = ?";
            $params[] = $is_read;
            $types .= "i";
        }
        
        // إضافة الترتيب
        // Add sorting
        $allowedSortFields = ['id', 'message', 'type', 'is_read', 'created_at'];
        $allowedOrderDirections = ['ASC', 'DESC'];
        
        if (!in_array($sort, $allowedSortFields)) {
            $sort = 'created_at';
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
        $stmt->bind_param(substr($types, 0, -2), ...array_slice($params, 0, -2));
        $stmt->execute();
        $totalResult = $stmt->get_result()->fetch_assoc();
        $total = $totalResult['total'];
        
        // تنفيذ استعلام البحث
        // Execute search query
        $stmt = $conn->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $notifications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // حساب عدد الصفحات
        // Calculate total pages
        $totalPages = ceil($total / $limit);
        
        // تضمين ملف الرأس
        // Include header file
        include '../includes/header.php';
        
        // عرض قائمة الإشعارات
        // Display notifications list
        include 'templates/list_notifications.php';
        
        break;
}

// تضمين ملف التذييل
// Include footer file
include '../includes/footer.php';
?>

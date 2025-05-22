<?php
/**
 * صفحة إدارة المراجعات
 * Reviews Management Page
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
$pageTitle = 'إدارة المراجعات | ' . SITE_NAME;
$activeMenu = 'reviews';

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
    case 'view':
        // عرض المراجعة
        // View review
        
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            redirect('index.php');
            exit;
        }
        
        $reviewId = (int)$_GET['id'];
        
        // الحصول على بيانات المراجعة
        // Get review data
        $stmt = $conn->prepare("
            SELECT r.*, u.name as user_name, u.email as user_email,
            CASE 
                WHEN r.product_id IS NOT NULL THEN p.name
                WHEN r.service_id IS NOT NULL THEN s.name
                ELSE NULL
            END as item_name
            FROM reviews r
            LEFT JOIN users u ON r.user_id = u.id
            LEFT JOIN products p ON r.product_id = p.id
            LEFT JOIN services s ON r.service_id = s.id
            WHERE r.id = ?
        ");
        
        $stmt->bind_param("i", $reviewId);
        $stmt->execute();
        $review = $stmt->get_result()->fetch_assoc();
        
        if (!$review) {
            redirect('index.php?error=المراجعة غير موجودة');
            exit;
        }
        
        // تضمين ملف الرأس
        // Include header file
        include '../includes/header.php';
        
        // عرض المراجعة
        // Display review
        include 'templates/view_review.php';
        
        break;
        
    case 'approve':
        // الموافقة على المراجعة
        // Approve review
        
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            redirect('index.php');
            exit;
        }
        
        $reviewId = (int)$_GET['id'];
        
        // الحصول على بيانات المراجعة
        // Get review data
        $stmt = $conn->prepare("
            SELECT r.*, u.name as user_name, u.email as user_email
            FROM reviews r
            LEFT JOIN users u ON r.user_id = u.id
            WHERE r.id = ?
        ");
        
        $stmt->bind_param("i", $reviewId);
        $stmt->execute();
        $review = $stmt->get_result()->fetch_assoc();
        
        if (!$review) {
            redirect('index.php?error=المراجعة غير موجودة');
            exit;
        }
        
        // تحديث حالة المراجعة
        // Update review status
        $stmt = $conn->prepare("UPDATE reviews SET status = 'approved', updated_at = NOW() WHERE id = ?");
        $stmt->bind_param("i", $reviewId);
        
        if ($stmt->execute()) {
            // إضافة إشعار للمستخدم
            // Add notification to user
            $notificationMessage = 'تمت الموافقة على مراجعتك';
            
            $stmt = $conn->prepare("
                INSERT INTO notifications (user_id, message, type, is_read, created_at)
                VALUES (?, ?, 'review', 0, NOW())
            ");
            
            $stmt->bind_param("is", $review['user_id'], $notificationMessage);
            $stmt->execute();
            
            // إرسال بريد إلكتروني للمستخدم
            // Send email to user
            $emailSubject = 'تمت الموافقة على مراجعتك';
            $emailMessage = "
                <p>مرحباً {$review['user_name']},</p>
                <p>تمت الموافقة على مراجعتك وهي الآن معروضة على موقعنا.</p>
                <p>شكراً لك على مشاركتك!</p>
                <p>مع تحيات,<br>" . SITE_NAME . "</p>
            ";
            
            sendEmail($review['user_email'], $emailSubject, $emailMessage);
            
            // إعادة التوجيه إلى قائمة المراجعات
            // Redirect to reviews list
            redirect('index.php?success=تمت الموافقة على المراجعة بنجاح');
            exit;
        } else {
            redirect('index.php?error=حدث خطأ أثناء تحديث حالة المراجعة');
            exit;
        }
        
        break;
        
    case 'reject':
        // رفض المراجعة
        // Reject review
        
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            redirect('index.php');
            exit;
        }
        
        $reviewId = (int)$_GET['id'];
        
        // الحصول على بيانات المراجعة
        // Get review data
        $stmt = $conn->prepare("
            SELECT r.*, u.name as user_name, u.email as user_email
            FROM reviews r
            LEFT JOIN users u ON r.user_id = u.id
            WHERE r.id = ?
        ");
        
        $stmt->bind_param("i", $reviewId);
        $stmt->execute();
        $review = $stmt->get_result()->fetch_assoc();
        
        if (!$review) {
            redirect('index.php?error=المراجعة غير موجودة');
            exit;
        }
        
        // تحديث حالة المراجعة
        // Update review status
        $stmt = $conn->prepare("UPDATE reviews SET status = 'rejected', updated_at = NOW() WHERE id = ?");
        $stmt->bind_param("i", $reviewId);
        
        if ($stmt->execute()) {
            // إضافة إشعار للمستخدم
            // Add notification to user
            $notificationMessage = 'تم رفض مراجعتك';
            
            $stmt = $conn->prepare("
                INSERT INTO notifications (user_id, message, type, is_read, created_at)
                VALUES (?, ?, 'review', 0, NOW())
            ");
            
            $stmt->bind_param("is", $review['user_id'], $notificationMessage);
            $stmt->execute();
            
            // إرسال بريد إلكتروني للمستخدم
            // Send email to user
            $emailSubject = 'تم رفض مراجعتك';
            $emailMessage = "
                <p>مرحباً {$review['user_name']},</p>
                <p>نأسف لإبلاغك أنه تم رفض مراجعتك لأنها لا تتوافق مع إرشادات المراجعة لدينا.</p>
                <p>يرجى التأكد من أن مراجعاتك المستقبلية تتبع إرشاداتنا.</p>
                <p>مع تحيات,<br>" . SITE_NAME . "</p>
            ";
            
            sendEmail($review['user_email'], $emailSubject, $emailMessage);
            
            // إعادة التوجيه إلى قائمة المراجعات
            // Redirect to reviews list
            redirect('index.php?success=تم رفض المراجعة بنجاح');
            exit;
        } else {
            redirect('index.php?error=حدث خطأ أثناء تحديث حالة المراجعة');
            exit;
        }
        
        break;
        
    case 'delete':
        // حذف المراجعة
        // Delete review
        
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            redirect('index.php');
            exit;
        }
        
        $reviewId = (int)$_GET['id'];
        
        // الحصول على بيانات المراجعة
        // Get review data
        $stmt = $conn->prepare("SELECT * FROM reviews WHERE id = ?");
        $stmt->bind_param("i", $reviewId);
        $stmt->execute();
        $review = $stmt->get_result()->fetch_assoc();
        
        if (!$review) {
            redirect('index.php?error=المراجعة غير موجودة');
            exit;
        }
        
        // حذف المراجعة
        // Delete review
        $stmt = $conn->prepare("DELETE FROM reviews WHERE id = ?");
        $stmt->bind_param("i", $reviewId);
        
        if ($stmt->execute()) {
            // إعادة التوجيه إلى قائمة المراجعات
            // Redirect to reviews list
            redirect('index.php?success=تم حذف المراجعة بنجاح');
            exit;
        } else {
            redirect('index.php?error=حدث خطأ أثناء حذف المراجعة');
            exit;
        }
        
        break;
        
    case 'bulk_action':
        // إجراء جماعي
        // Bulk action
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['reviews']) || !is_array($_POST['reviews']) || empty($_POST['reviews'])) {
                redirect('index.php?error=يرجى اختيار مراجعة واحدة على الأقل');
                exit;
            }
            
            $reviews = $_POST['reviews'];
            $action = isset($_POST['bulk_action']) ? $_POST['bulk_action'] : '';
            
            switch ($action) {
                case 'approve':
                    // الموافقة على المراجعات المحددة
                    // Approve selected reviews
                    $stmt = $conn->prepare("UPDATE reviews SET status = 'approved', updated_at = NOW() WHERE id IN (" . implode(',', array_fill(0, count($reviews), '?')) . ")");
                    $stmt->bind_param(str_repeat('i', count($reviews)), ...$reviews);
                    
                    if ($stmt->execute()) {
                        redirect('index.php?success=تمت الموافقة على المراجعات المحددة بنجاح');
                        exit;
                    } else {
                        redirect('index.php?error=حدث خطأ أثناء تحديث حالة المراجعات');
                        exit;
                    }
                    break;
                    
                case 'reject':
                    // رفض المراجعات المحددة
                    // Reject selected reviews
                    $stmt = $conn->prepare("UPDATE reviews SET status = 'rejected', updated_at = NOW() WHERE id IN (" . implode(',', array_fill(0, count($reviews), '?')) . ")");
                    $stmt->bind_param(str_repeat('i', count($reviews)), ...$reviews);
                    
                    if ($stmt->execute()) {
                        redirect('index.php?success=تم رفض المراجعات المحددة بنجاح');
                        exit;
                    } else {
                        redirect('index.php?error=حدث خطأ أثناء تحديث حالة المراجعات');
                        exit;
                    }
                    break;
                    
                case 'delete':
                    // حذف المراجعات المحددة
                    // Delete selected reviews
                    $stmt = $conn->prepare("DELETE FROM reviews WHERE id IN (" . implode(',', array_fill(0, count($reviews), '?')) . ")");
                    $stmt->bind_param(str_repeat('i', count($reviews)), ...$reviews);
                    
                    if ($stmt->execute()) {
                        redirect('index.php?success=تم حذف المراجعات المحددة بنجاح');
                        exit;
                    } else {
                        redirect('index.php?error=حدث خطأ أثناء حذف المراجعات');
                        exit;
                    }
                    break;
                    
                default:
                    redirect('index.php?error=الإجراء غير صالح');
                    exit;
            }
        } else {
            redirect('index.php');
            exit;
        }
        
        break;
        
    default:
        // قائمة المراجعات
        // Reviews list
        
        // تهيئة متغيرات البحث والترتيب
        // Initialize search and sort variables
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $status = isset($_GET['status']) ? $_GET['status'] : '';
        $type = isset($_GET['type']) ? $_GET['type'] : '';
        $rating = isset($_GET['rating']) ? (int)$_GET['rating'] : 0;
        $sort = isset($_GET['sort']) ? $_GET['sort'] : 'created_at';
        $order = isset($_GET['order']) ? $_GET['order'] : 'DESC';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        // بناء استعلام البحث
        // Build search query
        $query = "
            SELECT r.*, u.name as user_name, u.email as user_email,
            CASE 
                WHEN r.product_id IS NOT NULL THEN p.name
                WHEN r.service_id IS NOT NULL THEN s.name
                ELSE NULL
            END as item_name,
            CASE 
                WHEN r.product_id IS NOT NULL THEN 'product'
                WHEN r.service_id IS NOT NULL THEN 'service'
                ELSE NULL
            END as item_type
            FROM reviews r
            LEFT JOIN users u ON r.user_id = u.id
            LEFT JOIN products p ON r.product_id = p.id
            LEFT JOIN services s ON r.service_id = s.id
            WHERE 1=1
        ";
        
        $countQuery = "SELECT COUNT(*) as total FROM reviews r WHERE 1=1";
        
        $params = [];
        $types = "";
        
        if (!empty($search)) {
            $searchTerm = "%{$search}%";
            $query .= " AND (r.title LIKE ? OR r.content LIKE ? OR u.name LIKE ? OR u.email LIKE ?)";
            $countQuery .= " AND (r.title LIKE ? OR r.content LIKE ? OR u.name LIKE ? OR u.email LIKE ?)";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= "ssss";
        }
        
        if (!empty($status)) {
            $query .= " AND r.status = ?";
            $countQuery .= " AND r.status = ?";
            $params[] = $status;
            $types .= "s";
        }
        
        if (!empty($type)) {
            if ($type === 'product') {
                $query .= " AND r.product_id IS NOT NULL";
                $countQuery .= " AND r.product_id IS NOT NULL";
            } elseif ($type === 'service') {
                $query .= " AND r.service_id IS NOT NULL";
                $countQuery .= " AND r.service_id IS NOT NULL";
            }
        }
        
        if ($rating > 0) {
            $query .= " AND r.rating = ?";
            $countQuery .= " AND r.rating = ?";
            $params[] = $rating;
            $types .= "i";
        }
        
        // إضافة الترتيب
        // Add sorting
        $allowedSortFields = ['id', 'title', 'rating', 'status', 'created_at', 'updated_at'];
        $allowedOrderDirections = ['ASC', 'DESC'];
        
        if (!in_array($sort, $allowedSortFields)) {
            $sort = 'created_at';
        }
        
        if (!in_array($order, $allowedOrderDirections)) {
            $order = 'DESC';
        }
        
        $query .= " ORDER BY r.{$sort} {$order} LIMIT ? OFFSET ?";
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
        $reviews = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // حساب عدد الصفحات
        // Calculate total pages
        $totalPages = ceil($total / $limit);
        
        // تضمين ملف الرأس
        // Include header file
        include '../includes/header.php';
        
        // عرض قائمة المراجعات
        // Display reviews list
        include 'templates/list_reviews.php';
        
        break;
}

// تضمين ملف التذييل
// Include footer file
include '../includes/footer.php';

/**
 * إرسال بريد إلكتروني
 * Send email
 * 
 * @param string $to البريد الإلكتروني للمستلم
 * @param string $subject عنوان البريد الإلكتروني
 * @param string $message محتوى البريد الإلكتروني
 * @return bool نتيجة الإرسال
 */
function sendEmail($to, $subject, $message) {
    // هذه دالة وهمية، يجب استبدالها بالتنفيذ الفعلي
    // This is a dummy function, should be replaced with actual implementation
    
    // الحصول على إعدادات البريد الإلكتروني
    // Get email settings
    $mailDriver = getSetting('mail_driver');
    $mailFromAddress = getSetting('mail_from_address');
    $mailFromName = getSetting('mail_from_name');
    
    // إعداد رأس البريد الإلكتروني
    // Setup email headers
    $headers = "From: {$mailFromName} <{$mailFromAddress}>\r\n";
    $headers .= "Reply-To: {$mailFromAddress}\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    // إرسال البريد الإلكتروني
    // Send email
    return mail($to, $subject, $message, $headers);
}

/**
 * الحصول على قيمة إعداد
 * Get setting value
 * 
 * @param string $key مفتاح الإعداد
 * @param string $default القيمة الافتراضية
 * @return string قيمة الإعداد
 */
function getSetting($key, $default = '') {
    global $conn;
    
    $stmt = $conn->prepare("SELECT value FROM settings WHERE `key` = ?");
    $stmt->bind_param("s", $key);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc()['value'];
    }
    
    return $default;
}
?>

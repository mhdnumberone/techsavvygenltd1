<?php
/**
 * صفحة إدارة دعم العملاء
 * Customer Support Management Page
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
$pageTitle = 'إدارة دعم العملاء | ' . SITE_NAME;
$activeMenu = 'support';

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
        // عرض تذكرة الدعم
        // View support ticket
        
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            redirect('index.php');
            exit;
        }
        
        $ticketId = (int)$_GET['id'];
        
        // الحصول على بيانات التذكرة
        // Get ticket data
        $stmt = $conn->prepare("
            SELECT t.*, u.name as user_name, u.email as user_email
            FROM support_tickets t
            LEFT JOIN users u ON t.user_id = u.id
            WHERE t.id = ?
        ");
        
        $stmt->bind_param("i", $ticketId);
        $stmt->execute();
        $ticket = $stmt->get_result()->fetch_assoc();
        
        if (!$ticket) {
            redirect('index.php?error=التذكرة غير موجودة');
            exit;
        }
        
        // الحصول على ردود التذكرة
        // Get ticket replies
        $stmt = $conn->prepare("
            SELECT r.*, u.name as user_name, u.email as user_email, u.role as user_role
            FROM support_replies r
            LEFT JOIN users u ON r.user_id = u.id
            WHERE r.ticket_id = ?
            ORDER BY r.created_at ASC
        ");
        
        $stmt->bind_param("i", $ticketId);
        $stmt->execute();
        $replies = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // تحديث حالة التذكرة إلى "مقروءة" إذا كانت "جديدة"
        // Update ticket status to "read" if it was "new"
        if ($ticket['status'] === 'new') {
            $stmt = $conn->prepare("UPDATE support_tickets SET status = 'read', updated_at = NOW() WHERE id = ?");
            $stmt->bind_param("i", $ticketId);
            $stmt->execute();
            
            // تحديث متغير التذكرة
            // Update ticket variable
            $ticket['status'] = 'read';
        }
        
        // معالجة إضافة رد جديد
        // Process adding new reply
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $content = trim($_POST['content']);
            $userId = $_SESSION['user_id'];
            $isPrivate = isset($_POST['is_private']) ? 1 : 0;
            
            $errors = [];
            
            if (empty($content)) {
                $errors[] = 'محتوى الرد مطلوب';
            }
            
            // إذا لم تكن هناك أخطاء، إضافة الرد
            // If no errors, add reply
            if (empty($errors)) {
                // إضافة الرد
                // Add reply
                $stmt = $conn->prepare("
                    INSERT INTO support_replies (
                        ticket_id, user_id, content, is_private, created_at
                    ) VALUES (?, ?, ?, ?, NOW())
                ");
                
                $stmt->bind_param("iisi", $ticketId, $userId, $content, $isPrivate);
                
                if ($stmt->execute()) {
                    // تحديث حالة التذكرة إلى "تم الرد" إذا كان المستخدم مدير
                    // Update ticket status to "replied" if user is admin
                    if (isAdmin()) {
                        $stmt = $conn->prepare("UPDATE support_tickets SET status = 'replied', updated_at = NOW() WHERE id = ?");
                        $stmt->bind_param("i", $ticketId);
                        $stmt->execute();
                        
                        // تحديث متغير التذكرة
                        // Update ticket variable
                        $ticket['status'] = 'replied';
                        
                        // إضافة إشعار للمستخدم
                        // Add notification to user
                        $notificationMessage = 'تم الرد على تذكرة الدعم الخاصة بك: ' . $ticket['subject'];
                        
                        $stmt = $conn->prepare("
                            INSERT INTO notifications (user_id, message, type, is_read, created_at)
                            VALUES (?, ?, 'support', 0, NOW())
                        ");
                        
                        $stmt->bind_param("is", $ticket['user_id'], $notificationMessage);
                        $stmt->execute();
                        
                        // إرسال بريد إلكتروني للمستخدم
                        // Send email to user
                        $emailSubject = 'تم الرد على تذكرة الدعم الخاصة بك';
                        $emailMessage = "
                            <p>مرحباً {$ticket['user_name']},</p>
                            <p>تم الرد على تذكرة الدعم الخاصة بك: {$ticket['subject']}</p>
                            <p>يمكنك عرض الرد من خلال الرابط التالي:</p>
                            <p><a href='" . SITE_URL . "/support/view.php?id={$ticketId}'>عرض التذكرة</a></p>
                            <p>شكراً لك,<br>" . SITE_NAME . "</p>
                        ";
                        
                        sendEmail($ticket['user_email'], $emailSubject, $emailMessage);
                    } else {
                        // تحديث حالة التذكرة إلى "مفتوحة" إذا كان المستخدم عادي
                        // Update ticket status to "open" if user is regular
                        $stmt = $conn->prepare("UPDATE support_tickets SET status = 'open', updated_at = NOW() WHERE id = ?");
                        $stmt->bind_param("i", $ticketId);
                        $stmt->execute();
                        
                        // تحديث متغير التذكرة
                        // Update ticket variable
                        $ticket['status'] = 'open';
                        
                        // إضافة إشعار للمديرين
                        // Add notification to admins
                        $notificationMessage = 'تم إضافة رد جديد على تذكرة الدعم: ' . $ticket['subject'];
                        
                        // الحصول على قائمة المديرين
                        // Get admins list
                        $stmt = $conn->prepare("SELECT id FROM users WHERE role = 'admin'");
                        $stmt->execute();
                        $admins = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                        
                        foreach ($admins as $admin) {
                            $stmt = $conn->prepare("
                                INSERT INTO notifications (user_id, message, type, is_read, created_at)
                                VALUES (?, ?, 'support', 0, NOW())
                            ");
                            
                            $stmt->bind_param("is", $admin['id'], $notificationMessage);
                            $stmt->execute();
                        }
                    }
                    
                    // إعادة تحميل الصفحة لعرض الرد الجديد
                    // Reload page to show new reply
                    redirect('index.php?action=view&id=' . $ticketId . '&success=تم إضافة الرد بنجاح');
                    exit;
                } else {
                    $errors[] = 'حدث خطأ أثناء إضافة الرد';
                }
            }
        }
        
        // تضمين ملف الرأس
        // Include header file
        include '../includes/header.php';
        
        // عرض تذكرة الدعم
        // Display support ticket
        include 'templates/view_ticket.php';
        
        break;
        
    case 'close':
        // إغلاق تذكرة الدعم
        // Close support ticket
        
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            redirect('index.php');
            exit;
        }
        
        $ticketId = (int)$_GET['id'];
        
        // الحصول على بيانات التذكرة
        // Get ticket data
        $stmt = $conn->prepare("
            SELECT t.*, u.name as user_name, u.email as user_email
            FROM support_tickets t
            LEFT JOIN users u ON t.user_id = u.id
            WHERE t.id = ?
        ");
        
        $stmt->bind_param("i", $ticketId);
        $stmt->execute();
        $ticket = $stmt->get_result()->fetch_assoc();
        
        if (!$ticket) {
            redirect('index.php?error=التذكرة غير موجودة');
            exit;
        }
        
        // تحديث حالة التذكرة إلى "مغلقة"
        // Update ticket status to "closed"
        $stmt = $conn->prepare("UPDATE support_tickets SET status = 'closed', updated_at = NOW() WHERE id = ?");
        $stmt->bind_param("i", $ticketId);
        
        if ($stmt->execute()) {
            // إضافة إشعار للمستخدم
            // Add notification to user
            $notificationMessage = 'تم إغلاق تذكرة الدعم الخاصة بك: ' . $ticket['subject'];
            
            $stmt = $conn->prepare("
                INSERT INTO notifications (user_id, message, type, is_read, created_at)
                VALUES (?, ?, 'support', 0, NOW())
            ");
            
            $stmt->bind_param("is", $ticket['user_id'], $notificationMessage);
            $stmt->execute();
            
            // إرسال بريد إلكتروني للمستخدم
            // Send email to user
            $emailSubject = 'تم إغلاق تذكرة الدعم الخاصة بك';
            $emailMessage = "
                <p>مرحباً {$ticket['user_name']},</p>
                <p>تم إغلاق تذكرة الدعم الخاصة بك: {$ticket['subject']}</p>
                <p>إذا كنت بحاجة إلى مزيد من المساعدة، يرجى فتح تذكرة جديدة.</p>
                <p>شكراً لك,<br>" . SITE_NAME . "</p>
            ";
            
            sendEmail($ticket['user_email'], $emailSubject, $emailMessage);
            
            // إعادة التوجيه إلى قائمة التذاكر
            // Redirect to tickets list
            redirect('index.php?success=تم إغلاق التذكرة بنجاح');
            exit;
        } else {
            redirect('index.php?error=حدث خطأ أثناء إغلاق التذكرة');
            exit;
        }
        
        break;
        
    case 'reopen':
        // إعادة فتح تذكرة الدعم
        // Reopen support ticket
        
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            redirect('index.php');
            exit;
        }
        
        $ticketId = (int)$_GET['id'];
        
        // الحصول على بيانات التذكرة
        // Get ticket data
        $stmt = $conn->prepare("
            SELECT t.*, u.name as user_name, u.email as user_email
            FROM support_tickets t
            LEFT JOIN users u ON t.user_id = u.id
            WHERE t.id = ?
        ");
        
        $stmt->bind_param("i", $ticketId);
        $stmt->execute();
        $ticket = $stmt->get_result()->fetch_assoc();
        
        if (!$ticket) {
            redirect('index.php?error=التذكرة غير موجودة');
            exit;
        }
        
        // تحديث حالة التذكرة إلى "مفتوحة"
        // Update ticket status to "open"
        $stmt = $conn->prepare("UPDATE support_tickets SET status = 'open', updated_at = NOW() WHERE id = ?");
        $stmt->bind_param("i", $ticketId);
        
        if ($stmt->execute()) {
            // إضافة إشعار للمستخدم
            // Add notification to user
            $notificationMessage = 'تم إعادة فتح تذكرة الدعم الخاصة بك: ' . $ticket['subject'];
            
            $stmt = $conn->prepare("
                INSERT INTO notifications (user_id, message, type, is_read, created_at)
                VALUES (?, ?, 'support', 0, NOW())
            ");
            
            $stmt->bind_param("is", $ticket['user_id'], $notificationMessage);
            $stmt->execute();
            
            // إرسال بريد إلكتروني للمستخدم
            // Send email to user
            $emailSubject = 'تم إعادة فتح تذكرة الدعم الخاصة بك';
            $emailMessage = "
                <p>مرحباً {$ticket['user_name']},</p>
                <p>تم إعادة فتح تذكرة الدعم الخاصة بك: {$ticket['subject']}</p>
                <p>يمكنك عرض التذكرة من خلال الرابط التالي:</p>
                <p><a href='" . SITE_URL . "/support/view.php?id={$ticketId}'>عرض التذكرة</a></p>
                <p>شكراً لك,<br>" . SITE_NAME . "</p>
            ";
            
            sendEmail($ticket['user_email'], $emailSubject, $emailMessage);
            
            // إعادة التوجيه إلى قائمة التذاكر
            // Redirect to tickets list
            redirect('index.php?success=تم إعادة فتح التذكرة بنجاح');
            exit;
        } else {
            redirect('index.php?error=حدث خطأ أثناء إعادة فتح التذكرة');
            exit;
        }
        
        break;
        
    case 'delete':
        // حذف تذكرة الدعم
        // Delete support ticket
        
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            redirect('index.php');
            exit;
        }
        
        $ticketId = (int)$_GET['id'];
        
        // الحصول على بيانات التذكرة
        // Get ticket data
        $stmt = $conn->prepare("SELECT * FROM support_tickets WHERE id = ?");
        $stmt->bind_param("i", $ticketId);
        $stmt->execute();
        $ticket = $stmt->get_result()->fetch_assoc();
        
        if (!$ticket) {
            redirect('index.php?error=التذكرة غير موجودة');
            exit;
        }
        
        // حذف ردود التذكرة
        // Delete ticket replies
        $stmt = $conn->prepare("DELETE FROM support_replies WHERE ticket_id = ?");
        $stmt->bind_param("i", $ticketId);
        $stmt->execute();
        
        // حذف التذكرة
        // Delete ticket
        $stmt = $conn->prepare("DELETE FROM support_tickets WHERE id = ?");
        $stmt->bind_param("i", $ticketId);
        
        if ($stmt->execute()) {
            // إعادة التوجيه إلى قائمة التذاكر
            // Redirect to tickets list
            redirect('index.php?success=تم حذف التذكرة بنجاح');
            exit;
        } else {
            redirect('index.php?error=حدث خطأ أثناء حذف التذكرة');
            exit;
        }
        
        break;
        
    case 'bulk_action':
        // إجراء جماعي
        // Bulk action
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['tickets']) || !is_array($_POST['tickets']) || empty($_POST['tickets'])) {
                redirect('index.php?error=يرجى اختيار تذكرة واحدة على الأقل');
                exit;
            }
            
            $tickets = $_POST['tickets'];
            $action = isset($_POST['bulk_action']) ? $_POST['bulk_action'] : '';
            
            switch ($action) {
                case 'close':
                    // إغلاق التذاكر المحددة
                    // Close selected tickets
                    $stmt = $conn->prepare("UPDATE support_tickets SET status = 'closed', updated_at = NOW() WHERE id IN (" . implode(',', array_fill(0, count($tickets), '?')) . ")");
                    $stmt->bind_param(str_repeat('i', count($tickets)), ...$tickets);
                    
                    if ($stmt->execute()) {
                        redirect('index.php?success=تم إغلاق التذاكر المحددة بنجاح');
                        exit;
                    } else {
                        redirect('index.php?error=حدث خطأ أثناء إغلاق التذاكر');
                        exit;
                    }
                    break;
                    
                case 'reopen':
                    // إعادة فتح التذاكر المحددة
                    // Reopen selected tickets
                    $stmt = $conn->prepare("UPDATE support_tickets SET status = 'open', updated_at = NOW() WHERE id IN (" . implode(',', array_fill(0, count($tickets), '?')) . ")");
                    $stmt->bind_param(str_repeat('i', count($tickets)), ...$tickets);
                    
                    if ($stmt->execute()) {
                        redirect('index.php?success=تم إعادة فتح التذاكر المحددة بنجاح');
                        exit;
                    } else {
                        redirect('index.php?error=حدث خطأ أثناء إعادة فتح التذاكر');
                        exit;
                    }
                    break;
                    
                case 'delete':
                    // حذف التذاكر المحددة
                    // Delete selected tickets
                    
                    // حذف ردود التذاكر
                    // Delete ticket replies
                    $stmt = $conn->prepare("DELETE FROM support_replies WHERE ticket_id IN (" . implode(',', array_fill(0, count($tickets), '?')) . ")");
                    $stmt->bind_param(str_repeat('i', count($tickets)), ...$tickets);
                    $stmt->execute();
                    
                    // حذف التذاكر
                    // Delete tickets
                    $stmt = $conn->prepare("DELETE FROM support_tickets WHERE id IN (" . implode(',', array_fill(0, count($tickets), '?')) . ")");
                    $stmt->bind_param(str_repeat('i', count($tickets)), ...$tickets);
                    
                    if ($stmt->execute()) {
                        redirect('index.php?success=تم حذف التذاكر المحددة بنجاح');
                        exit;
                    } else {
                        redirect('index.php?error=حدث خطأ أثناء حذف التذاكر');
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
        // قائمة تذاكر الدعم
        // Support tickets list
        
        // تهيئة متغيرات البحث والترتيب
        // Initialize search and sort variables
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $status = isset($_GET['status']) ? $_GET['status'] : '';
        $priority = isset($_GET['priority']) ? $_GET['priority'] : '';
        $department = isset($_GET['department']) ? $_GET['department'] : '';
        $sort = isset($_GET['sort']) ? $_GET['sort'] : 'created_at';
        $order = isset($_GET['order']) ? $_GET['order'] : 'DESC';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        // بناء استعلام البحث
        // Build search query
        $query = "
            SELECT t.*, u.name as user_name, u.email as user_email,
            (SELECT COUNT(*) FROM support_replies WHERE ticket_id = t.id) as reply_count
            FROM support_tickets t
            LEFT JOIN users u ON t.user_id = u.id
            WHERE 1=1
        ";
        
        $countQuery = "SELECT COUNT(*) as total FROM support_tickets t WHERE 1=1";
        
        $params = [];
        $types = "";
        
        if (!empty($search)) {
            $searchTerm = "%{$search}%";
            $query .= " AND (t.subject LIKE ? OR t.content LIKE ? OR u.name LIKE ? OR u.email LIKE ?)";
            $countQuery .= " AND (t.subject LIKE ? OR t.content LIKE ? OR u.name LIKE ? OR u.email LIKE ?)";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= "ssss";
        }
        
        if (!empty($status)) {
            $query .= " AND t.status = ?";
            $countQuery .= " AND t.status = ?";
            $params[] = $status;
            $types .= "s";
        }
        
        if (!empty($priority)) {
            $query .= " AND t.priority = ?";
            $countQuery .= " AND t.priority = ?";
            $params[] = $priority;
            $types .= "s";
        }
        
        if (!empty($department)) {
            $query .= " AND t.department = ?";
            $countQuery .= " AND t.department = ?";
            $params[] = $department;
            $types .= "s";
        }
        
        // إضافة الترتيب
        // Add sorting
        $allowedSortFields = ['id', 'subject', 'status', 'priority', 'department', 'created_at', 'updated_at'];
        $allowedOrderDirections = ['ASC', 'DESC'];
        
        if (!in_array($sort, $allowedSortFields)) {
            $sort = 'created_at';
        }
        
        if (!in_array($order, $allowedOrderDirections)) {
            $order = 'DESC';
        }
        
        $query .= " ORDER BY t.{$sort} {$order} LIMIT ? OFFSET ?";
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
        $tickets = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // حساب عدد الصفحات
        // Calculate total pages
        $totalPages = ceil($total / $limit);
        
        // تضمين ملف الرأس
        // Include header file
        include '../includes/header.php';
        
        // عرض قائمة تذاكر الدعم
        // Display support tickets list
        include 'templates/list_tickets.php';
        
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

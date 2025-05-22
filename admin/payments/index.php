<?php
/**
 * صفحة إدارة المدفوعات
 * Payments Management Page
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
$pageTitle = 'إدارة المدفوعات | ' . SITE_NAME;
$activeMenu = 'payments';

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
        // عرض المدفوعات
        // View payment
        
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            redirect('index.php');
            exit;
        }
        
        $paymentId = (int)$_GET['id'];
        
        // الحصول على بيانات المدفوعات
        // Get payment data
        $stmt = $conn->prepare("
            SELECT p.*, o.order_number, u.name as user_name, u.email as user_email
            FROM payments p
            LEFT JOIN orders o ON p.order_id = o.id
            LEFT JOIN users u ON p.user_id = u.id
            WHERE p.id = ?
        ");
        
        $stmt->bind_param("i", $paymentId);
        $stmt->execute();
        $payment = $stmt->get_result()->fetch_assoc();
        
        if (!$payment) {
            redirect('index.php?error=المدفوعات غير موجودة');
            exit;
        }
        
        // تضمين ملف الرأس
        // Include header file
        include '../includes/header.php';
        
        // عرض المدفوعات
        // Display payment
        include 'templates/view_payment.php';
        
        break;
        
    case 'add':
        // إضافة مدفوعات جديدة
        // Add new payment
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // التحقق من البيانات
            // Validate data
            $orderId = (int)$_POST['order_id'];
            $userId = (int)$_POST['user_id'];
            $amount = (float)$_POST['amount'];
            $paymentMethod = trim($_POST['payment_method']);
            $transactionId = trim($_POST['transaction_id']);
            $status = trim($_POST['status']);
            $notes = trim($_POST['notes']);
            
            $errors = [];
            
            if ($orderId <= 0) {
                $errors[] = 'رقم الطلب مطلوب';
            }
            
            if ($userId <= 0) {
                $errors[] = 'رقم المستخدم مطلوب';
            }
            
            if ($amount <= 0) {
                $errors[] = 'المبلغ يجب أن يكون أكبر من صفر';
            }
            
            if (empty($paymentMethod)) {
                $errors[] = 'طريقة الدفع مطلوبة';
            }
            
            if (empty($status)) {
                $errors[] = 'حالة الدفع مطلوبة';
            }
            
            // التحقق من وجود الطلب
            // Check if order exists
            $stmt = $conn->prepare("SELECT id FROM orders WHERE id = ?");
            $stmt->bind_param("i", $orderId);
            $stmt->execute();
            $orderResult = $stmt->get_result();
            
            if ($orderResult->num_rows === 0) {
                $errors[] = 'الطلب غير موجود';
            }
            
            // التحقق من وجود المستخدم
            // Check if user exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $userResult = $stmt->get_result();
            
            if ($userResult->num_rows === 0) {
                $errors[] = 'المستخدم غير موجود';
            }
            
            // التحقق من عدم وجود مدفوعات مكتملة للطلب
            // Check if completed payment already exists for the order
            $stmt = $conn->prepare("SELECT id FROM payments WHERE order_id = ? AND status = 'completed'");
            $stmt->bind_param("i", $orderId);
            $stmt->execute();
            $existingPayment = $stmt->get_result();
            
            if ($existingPayment->num_rows > 0 && $status === 'completed') {
                $errors[] = 'يوجد بالفعل مدفوعات مكتملة لهذا الطلب';
            }
            
            // إذا لم تكن هناك أخطاء، إضافة المدفوعات
            // If no errors, add payment
            if (empty($errors)) {
                // إضافة المدفوعات
                // Add payment
                $stmt = $conn->prepare("
                    INSERT INTO payments (
                        order_id, user_id, amount, payment_method, 
                        transaction_id, status, notes, created_at, updated_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
                ");
                
                $stmt->bind_param(
                    "iidsss",
                    $orderId, $userId, $amount, $paymentMethod,
                    $transactionId, $status, $notes
                );
                
                if ($stmt->execute()) {
                    $paymentId = $conn->insert_id;
                    
                    // تحديث حالة الطلب إذا كانت المدفوعات مكتملة
                    // Update order status if payment is completed
                    if ($status === 'completed') {
                        $stmt = $conn->prepare("UPDATE orders SET status = 'paid', updated_at = NOW() WHERE id = ?");
                        $stmt->bind_param("i", $orderId);
                        $stmt->execute();
                        
                        // إضافة إشعار للمستخدم
                        // Add notification to user
                        $notificationMessage = 'تم استلام مدفوعاتك بنجاح للطلب رقم ' . $orderId;
                        
                        $stmt = $conn->prepare("
                            INSERT INTO notifications (user_id, message, type, is_read, created_at)
                            VALUES (?, ?, 'payment', 0, NOW())
                        ");
                        
                        $stmt->bind_param("is", $userId, $notificationMessage);
                        $stmt->execute();
                        
                        // الحصول على بريد المستخدم
                        // Get user email
                        $stmt = $conn->prepare("SELECT email, name FROM users WHERE id = ?");
                        $stmt->bind_param("i", $userId);
                        $stmt->execute();
                        $userInfo = $stmt->get_result()->fetch_assoc();
                        
                        // إرسال بريد إلكتروني للمستخدم
                        // Send email to user
                        $emailSubject = 'تأكيد استلام المدفوعات';
                        $emailMessage = "
                            <p>مرحباً {$userInfo['name']},</p>
                            <p>تم استلام مدفوعاتك بنجاح للطلب رقم {$orderId}.</p>
                            <p>المبلغ: {$amount}</p>
                            <p>طريقة الدفع: {$paymentMethod}</p>
                            <p>رقم المعاملة: {$transactionId}</p>
                            <p>شكراً لك,<br>" . SITE_NAME . "</p>
                        ";
                        
                        sendEmail($userInfo['email'], $emailSubject, $emailMessage);
                    }
                    
                    // إعادة التوجيه إلى قائمة المدفوعات
                    // Redirect to payments list
                    redirect('index.php?success=تم إضافة المدفوعات بنجاح');
                    exit;
                } else {
                    $errors[] = 'حدث خطأ أثناء إضافة المدفوعات';
                }
            }
        }
        
        // الحصول على قائمة الطلبات
        // Get orders list
        $stmt = $conn->prepare("SELECT id, order_number FROM orders ORDER BY id DESC");
        $stmt->execute();
        $orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // الحصول على قائمة المستخدمين
        // Get users list
        $stmt = $conn->prepare("SELECT id, name, email FROM users ORDER BY name");
        $stmt->execute();
        $users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // تضمين ملف الرأس
        // Include header file
        include '../includes/header.php';
        
        // عرض نموذج إضافة مدفوعات
        // Display add payment form
        include 'templates/add_payment.php';
        
        break;
        
    case 'edit':
        // تعديل المدفوعات
        // Edit payment
        
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            redirect('index.php');
            exit;
        }
        
        $paymentId = (int)$_GET['id'];
        
        // الحصول على بيانات المدفوعات
        // Get payment data
        $stmt = $conn->prepare("SELECT * FROM payments WHERE id = ?");
        $stmt->bind_param("i", $paymentId);
        $stmt->execute();
        $payment = $stmt->get_result()->fetch_assoc();
        
        if (!$payment) {
            redirect('index.php?error=المدفوعات غير موجودة');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // التحقق من البيانات
            // Validate data
            $orderId = (int)$_POST['order_id'];
            $userId = (int)$_POST['user_id'];
            $amount = (float)$_POST['amount'];
            $paymentMethod = trim($_POST['payment_method']);
            $transactionId = trim($_POST['transaction_id']);
            $status = trim($_POST['status']);
            $notes = trim($_POST['notes']);
            
            $errors = [];
            
            if ($orderId <= 0) {
                $errors[] = 'رقم الطلب مطلوب';
            }
            
            if ($userId <= 0) {
                $errors[] = 'رقم المستخدم مطلوب';
            }
            
            if ($amount <= 0) {
                $errors[] = 'المبلغ يجب أن يكون أكبر من صفر';
            }
            
            if (empty($paymentMethod)) {
                $errors[] = 'طريقة الدفع مطلوبة';
            }
            
            if (empty($status)) {
                $errors[] = 'حالة الدفع مطلوبة';
            }
            
            // التحقق من وجود الطلب
            // Check if order exists
            $stmt = $conn->prepare("SELECT id FROM orders WHERE id = ?");
            $stmt->bind_param("i", $orderId);
            $stmt->execute();
            $orderResult = $stmt->get_result();
            
            if ($orderResult->num_rows === 0) {
                $errors[] = 'الطلب غير موجود';
            }
            
            // التحقق من وجود المستخدم
            // Check if user exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $userResult = $stmt->get_result();
            
            if ($userResult->num_rows === 0) {
                $errors[] = 'المستخدم غير موجود';
            }
            
            // التحقق من عدم وجود مدفوعات مكتملة للطلب (باستثناء المدفوعات الحالية)
            // Check if completed payment already exists for the order (except current payment)
            $stmt = $conn->prepare("SELECT id FROM payments WHERE order_id = ? AND status = 'completed' AND id != ?");
            $stmt->bind_param("ii", $orderId, $paymentId);
            $stmt->execute();
            $existingPayment = $stmt->get_result();
            
            if ($existingPayment->num_rows > 0 && $status === 'completed') {
                $errors[] = 'يوجد بالفعل مدفوعات مكتملة لهذا الطلب';
            }
            
            // إذا لم تكن هناك أخطاء، تحديث المدفوعات
            // If no errors, update payment
            if (empty($errors)) {
                // تحديث المدفوعات
                // Update payment
                $stmt = $conn->prepare("
                    UPDATE payments SET
                        order_id = ?, user_id = ?, amount = ?, payment_method = ?,
                        transaction_id = ?, status = ?, notes = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                
                $stmt->bind_param(
                    "iidsssi",
                    $orderId, $userId, $amount, $paymentMethod,
                    $transactionId, $status, $notes, $paymentId
                );
                
                if ($stmt->execute()) {
                    // تحديث حالة الطلب إذا كانت المدفوعات مكتملة
                    // Update order status if payment is completed
                    if ($status === 'completed') {
                        $stmt = $conn->prepare("UPDATE orders SET status = 'paid', updated_at = NOW() WHERE id = ?");
                        $stmt->bind_param("i", $orderId);
                        $stmt->execute();
                        
                        // إضافة إشعار للمستخدم إذا تغيرت الحالة من غير مكتملة إلى مكتملة
                        // Add notification to user if status changed from not completed to completed
                        if ($payment['status'] !== 'completed') {
                            $notificationMessage = 'تم استلام مدفوعاتك بنجاح للطلب رقم ' . $orderId;
                            
                            $stmt = $conn->prepare("
                                INSERT INTO notifications (user_id, message, type, is_read, created_at)
                                VALUES (?, ?, 'payment', 0, NOW())
                            ");
                            
                            $stmt->bind_param("is", $userId, $notificationMessage);
                            $stmt->execute();
                            
                            // الحصول على بريد المستخدم
                            // Get user email
                            $stmt = $conn->prepare("SELECT email, name FROM users WHERE id = ?");
                            $stmt->bind_param("i", $userId);
                            $stmt->execute();
                            $userInfo = $stmt->get_result()->fetch_assoc();
                            
                            // إرسال بريد إلكتروني للمستخدم
                            // Send email to user
                            $emailSubject = 'تأكيد استلام المدفوعات';
                            $emailMessage = "
                                <p>مرحباً {$userInfo['name']},</p>
                                <p>تم استلام مدفوعاتك بنجاح للطلب رقم {$orderId}.</p>
                                <p>المبلغ: {$amount}</p>
                                <p>طريقة الدفع: {$paymentMethod}</p>
                                <p>رقم المعاملة: {$transactionId}</p>
                                <p>شكراً لك,<br>" . SITE_NAME . "</p>
                            ";
                            
                            sendEmail($userInfo['email'], $emailSubject, $emailMessage);
                        }
                    }
                    
                    // إعادة التوجيه إلى قائمة المدفوعات
                    // Redirect to payments list
                    redirect('index.php?success=تم تحديث المدفوعات بنجاح');
                    exit;
                } else {
                    $errors[] = 'حدث خطأ أثناء تحديث المدفوعات';
                }
            }
        }
        
        // الحصول على قائمة الطلبات
        // Get orders list
        $stmt = $conn->prepare("SELECT id, order_number FROM orders ORDER BY id DESC");
        $stmt->execute();
        $orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // الحصول على قائمة المستخدمين
        // Get users list
        $stmt = $conn->prepare("SELECT id, name, email FROM users ORDER BY name");
        $stmt->execute();
        $users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // تضمين ملف الرأس
        // Include header file
        include '../includes/header.php';
        
        // عرض نموذج تعديل المدفوعات
        // Display edit payment form
        include 'templates/edit_payment.php';
        
        break;
        
    case 'delete':
        // حذف المدفوعات
        // Delete payment
        
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            redirect('index.php');
            exit;
        }
        
        $paymentId = (int)$_GET['id'];
        
        // الحصول على بيانات المدفوعات
        // Get payment data
        $stmt = $conn->prepare("SELECT * FROM payments WHERE id = ?");
        $stmt->bind_param("i", $paymentId);
        $stmt->execute();
        $payment = $stmt->get_result()->fetch_assoc();
        
        if (!$payment) {
            redirect('index.php?error=المدفوعات غير موجودة');
            exit;
        }
        
        // حذف المدفوعات
        // Delete payment
        $stmt = $conn->prepare("DELETE FROM payments WHERE id = ?");
        $stmt->bind_param("i", $paymentId);
        
        if ($stmt->execute()) {
            // إعادة التوجيه إلى قائمة المدفوعات
            // Redirect to payments list
            redirect('index.php?success=تم حذف المدفوعات بنجاح');
            exit;
        } else {
            redirect('index.php?error=حدث خطأ أثناء حذف المدفوعات');
            exit;
        }
        
        break;
        
    default:
        // قائمة المدفوعات
        // Payments list
        
        // تهيئة متغيرات البحث والترتيب
        // Initialize search and sort variables
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $status = isset($_GET['status']) ? $_GET['status'] : '';
        $paymentMethod = isset($_GET['payment_method']) ? $_GET['payment_method'] : '';
        $dateFrom = isset($_GET['date_from']) ? $_GET['date_from'] : '';
        $dateTo = isset($_GET['date_to']) ? $_GET['date_to'] : '';
        $sort = isset($_GET['sort']) ? $_GET['sort'] : 'created_at';
        $order = isset($_GET['order']) ? $_GET['order'] : 'DESC';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        // بناء استعلام البحث
        // Build search query
        $query = "
            SELECT p.*, o.order_number, u.name as user_name, u.email as user_email
            FROM payments p
            LEFT JOIN orders o ON p.order_id = o.id
            LEFT JOIN users u ON p.user_id = u.id
            WHERE 1=1
        ";
        
        $countQuery = "
            SELECT COUNT(*) as total
            FROM payments p
            LEFT JOIN orders o ON p.order_id = o.id
            LEFT JOIN users u ON p.user_id = u.id
            WHERE 1=1
        ";
        
        $params = [];
        $types = "";
        
        if (!empty($search)) {
            $searchTerm = "%{$search}%";
            $query .= " AND (p.transaction_id LIKE ? OR u.name LIKE ? OR u.email LIKE ? OR o.order_number LIKE ?)";
            $countQuery .= " AND (p.transaction_id LIKE ? OR u.name LIKE ? OR u.email LIKE ? OR o.order_number LIKE ?)";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= "ssss";
        }
        
        if (!empty($status)) {
            $query .= " AND p.status = ?";
            $countQuery .= " AND p.status = ?";
            $params[] = $status;
            $types .= "s";
        }
        
        if (!empty($paymentMethod)) {
            $query .= " AND p.payment_method = ?";
            $countQuery .= " AND p.payment_method = ?";
            $params[] = $paymentMethod;
            $types .= "s";
        }
        
        if (!empty($dateFrom)) {
            $query .= " AND DATE(p.created_at) >= ?";
            $countQuery .= " AND DATE(p.created_at) >= ?";
            $params[] = $dateFrom;
            $types .= "s";
        }
        
        if (!empty($dateTo)) {
            $query .= " AND DATE(p.created_at) <= ?";
            $countQuery .= " AND DATE(p.created_at) <= ?";
            $params[] = $dateTo;
            $types .= "s";
        }
        
        // إضافة الترتيب
        // Add sorting
        $allowedSortFields = ['id', 'amount', 'payment_method', 'status', 'created_at'];
        $allowedOrderDirections = ['ASC', 'DESC'];
        
        if (!in_array($sort, $allowedSortFields)) {
            $sort = 'created_at';
        }
        
        if (!in_array($order, $allowedOrderDirections)) {
            $order = 'DESC';
        }
        
        $query .= " ORDER BY p.{$sort} {$order} LIMIT ? OFFSET ?";
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
        $payments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // حساب عدد الصفحات
        // Calculate total pages
        $totalPages = ceil($total / $limit);
        
        // تضمين ملف الرأس
        // Include header file
        include '../includes/header.php';
        
        // عرض قائمة المدفوعات
        // Display payments list
        include 'templates/list_payments.php';
        
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

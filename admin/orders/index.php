<?php
/**
 * صفحة إدارة الطلبات
 * Orders Management Page
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
$pageTitle = 'إدارة الطلبات | ' . SITE_NAME;
$activeMenu = 'orders';

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
        // عرض تفاصيل الطلب
        // View order details
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            redirect('index.php');
            exit;
        }
        
        $orderId = (int)$_GET['id'];
        
        // الحصول على بيانات الطلب
        // Get order data
        $stmt = $conn->prepare("
            SELECT o.*, u.name as customer_name, u.email as customer_email, u.phone as customer_phone
            FROM orders o 
            LEFT JOIN users u ON o.user_id = u.id 
            WHERE o.id = ?
        ");
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $order = $stmt->get_result()->fetch_assoc();
        
        if (!$order) {
            redirect('index.php?error=الطلب غير موجود');
            exit;
        }
        
        // الحصول على عناصر الطلب
        // Get order items
        $stmt = $conn->prepare("
            SELECT oi.*, p.name as product_name, p.sku as product_sku, s.name as service_name
            FROM order_items oi 
            LEFT JOIN products p ON oi.product_id = p.id 
            LEFT JOIN services s ON oi.service_id = s.id 
            WHERE oi.order_id = ?
        ");
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $orderItems = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // الحصول على مدفوعات الطلب
        // Get order payments
        $stmt = $conn->prepare("
            SELECT * FROM payments WHERE order_id = ? ORDER BY created_at DESC
        ");
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $payments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // الحصول على فواتير الطلب
        // Get order invoices
        $stmt = $conn->prepare("
            SELECT * FROM invoices WHERE order_id = ? ORDER BY created_at DESC
        ");
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $invoices = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // الحصول على سجل الطلب
        // Get order log
        $stmt = $conn->prepare("
            SELECT * FROM order_logs WHERE order_id = ? ORDER BY created_at DESC
        ");
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $orderLogs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // تضمين ملف الرأس
        // Include header file
        include '../includes/header.php';
        
        // عرض تفاصيل الطلب
        // Display order details
        include 'templates/view_order.php';
        
        break;
        
    case 'update_status':
        // تحديث حالة الطلب
        // Update order status
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            redirect('index.php');
            exit;
        }
        
        $orderId = (int)$_GET['id'];
        
        // الحصول على بيانات الطلب
        // Get order data
        $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $order = $stmt->get_result()->fetch_assoc();
        
        if (!$order) {
            redirect('index.php?error=الطلب غير موجود');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // التحقق من البيانات
            // Validate data
            $status = $_POST['status'];
            $notes = trim($_POST['notes']);
            
            $errors = [];
            
            if (empty($status)) {
                $errors[] = 'حالة الطلب مطلوبة';
            }
            
            // إذا لم تكن هناك أخطاء، تحديث حالة الطلب
            // If no errors, update order status
            if (empty($errors)) {
                // تحديث حالة الطلب
                // Update order status
                $stmt = $conn->prepare("
                    UPDATE orders 
                    SET status = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                
                $stmt->bind_param("si", $status, $orderId);
                
                if ($stmt->execute()) {
                    // إضافة سجل للطلب
                    // Add order log
                    $stmt = $conn->prepare("
                        INSERT INTO order_logs (order_id, status, notes, user_id, created_at)
                        VALUES (?, ?, ?, ?, NOW())
                    ");
                    
                    $userId = $_SESSION['user_id'];
                    $stmt->bind_param("issi", $orderId, $status, $notes, $userId);
                    $stmt->execute();
                    
                    // إضافة إشعار
                    // Add notification
                    $message = 'تم تحديث حالة الطلب #' . $order['order_number'] . ' إلى ' . translate($status);
                    addNotification($message, 'order');
                    
                    // إرسال إشعار للعميل
                    // Send notification to customer
                    $stmt = $conn->prepare("
                        INSERT INTO notifications (user_id, message, type, is_read, created_at)
                        VALUES (?, ?, ?, 0, NOW())
                    ");
                    
                    $customerMessage = 'تم تحديث حالة طلبك #' . $order['order_number'] . ' إلى ' . translate($status);
                    $stmt->bind_param("iss", $order['user_id'], $customerMessage, 'order');
                    $stmt->execute();
                    
                    // إعادة التوجيه إلى تفاصيل الطلب
                    // Redirect to order details
                    redirect('index.php?action=view&id=' . $orderId . '&success=تم تحديث حالة الطلب بنجاح');
                    exit;
                } else {
                    $errors[] = 'حدث خطأ أثناء تحديث حالة الطلب';
                }
            }
        }
        
        // تضمين ملف الرأس
        // Include header file
        include '../includes/header.php';
        
        // عرض نموذج تحديث حالة الطلب
        // Display update order status form
        include 'templates/update_order_status.php';
        
        break;
        
    case 'add_payment':
        // إضافة مدفوعة للطلب
        // Add payment to order
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            redirect('index.php');
            exit;
        }
        
        $orderId = (int)$_GET['id'];
        
        // الحصول على بيانات الطلب
        // Get order data
        $stmt = $conn->prepare("
            SELECT o.*, u.name as customer_name, u.email as customer_email
            FROM orders o 
            LEFT JOIN users u ON o.user_id = u.id 
            WHERE o.id = ?
        ");
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $order = $stmt->get_result()->fetch_assoc();
        
        if (!$order) {
            redirect('index.php?error=الطلب غير موجود');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // التحقق من البيانات
            // Validate data
            $amount = (float)$_POST['amount'];
            $payment_method = $_POST['payment_method'];
            $transaction_id = trim($_POST['transaction_id']);
            $status = $_POST['status'];
            $notes = trim($_POST['notes']);
            
            $errors = [];
            
            if ($amount <= 0) {
                $errors[] = 'المبلغ يجب أن يكون أكبر من صفر';
            }
            
            if (empty($payment_method)) {
                $errors[] = 'طريقة الدفع مطلوبة';
            }
            
            // إذا لم تكن هناك أخطاء، إضافة المدفوعة
            // If no errors, add payment
            if (empty($errors)) {
                // إضافة المدفوعة
                // Add payment
                $stmt = $conn->prepare("
                    INSERT INTO payments (order_id, user_id, amount, payment_method, transaction_id, status, notes, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
                ");
                
                $stmt->bind_param("iidssss", $orderId, $order['user_id'], $amount, $payment_method, $transaction_id, $status, $notes);
                
                if ($stmt->execute()) {
                    $paymentId = $conn->insert_id;
                    
                    // تحديث حالة الطلب إذا كانت المدفوعة مكتملة
                    // Update order status if payment is completed
                    if ($status === 'completed') {
                        // حساب إجمالي المدفوعات المكتملة للطلب
                        // Calculate total completed payments for the order
                        $stmt = $conn->prepare("
                            SELECT SUM(amount) as total_paid 
                            FROM payments 
                            WHERE order_id = ? AND status = 'completed'
                        ");
                        
                        $stmt->bind_param("i", $orderId);
                        $stmt->execute();
                        $result = $stmt->get_result()->fetch_assoc();
                        $totalPaid = $result['total_paid'];
                        
                        // إذا كان إجمالي المدفوعات يساوي أو أكبر من إجمالي الطلب، تحديث حالة الطلب إلى مدفوع
                        // If total payments equal or greater than order total, update order status to paid
                        if ($totalPaid >= $order['total']) {
                            $stmt = $conn->prepare("
                                UPDATE orders 
                                SET status = 'paid', updated_at = NOW()
                                WHERE id = ?
                            ");
                            
                            $stmt->bind_param("i", $orderId);
                            $stmt->execute();
                            
                            // إضافة سجل للطلب
                            // Add order log
                            $stmt = $conn->prepare("
                                INSERT INTO order_logs (order_id, status, notes, user_id, created_at)
                                VALUES (?, 'paid', 'تم دفع الطلب بالكامل', ?, NOW())
                            ");
                            
                            $userId = $_SESSION['user_id'];
                            $stmt->bind_param("ii", $orderId, $userId);
                            $stmt->execute();
                        }
                    }
                    
                    // إضافة إشعار
                    // Add notification
                    $message = 'تمت إضافة مدفوعة جديدة للطلب #' . $order['order_number'] . ' بمبلغ ' . formatCurrency($amount);
                    addNotification($message, 'payment');
                    
                    // إرسال إشعار للعميل
                    // Send notification to customer
                    $stmt = $conn->prepare("
                        INSERT INTO notifications (user_id, message, type, is_read, created_at)
                        VALUES (?, ?, ?, 0, NOW())
                    ");
                    
                    $customerMessage = 'تمت إضافة مدفوعة جديدة لطلبك #' . $order['order_number'] . ' بمبلغ ' . formatCurrency($amount);
                    $stmt->bind_param("iss", $order['user_id'], $customerMessage, 'payment');
                    $stmt->execute();
                    
                    // إعادة التوجيه إلى تفاصيل الطلب
                    // Redirect to order details
                    redirect('index.php?action=view&id=' . $orderId . '&success=تمت إضافة المدفوعة بنجاح');
                    exit;
                } else {
                    $errors[] = 'حدث خطأ أثناء إضافة المدفوعة';
                }
            }
        }
        
        // تضمين ملف الرأس
        // Include header file
        include '../includes/header.php';
        
        // عرض نموذج إضافة مدفوعة
        // Display add payment form
        include 'templates/add_payment.php';
        
        break;
        
    case 'generate_invoice':
        // إنشاء فاتورة للطلب
        // Generate invoice for order
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            redirect('index.php');
            exit;
        }
        
        $orderId = (int)$_GET['id'];
        
        // الحصول على بيانات الطلب
        // Get order data
        $stmt = $conn->prepare("
            SELECT o.*, u.name as customer_name, u.email as customer_email, u.phone as customer_phone, u.address as customer_address
            FROM orders o 
            LEFT JOIN users u ON o.user_id = u.id 
            WHERE o.id = ?
        ");
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $order = $stmt->get_result()->fetch_assoc();
        
        if (!$order) {
            redirect('index.php?error=الطلب غير موجود');
            exit;
        }
        
        // الحصول على عناصر الطلب
        // Get order items
        $stmt = $conn->prepare("
            SELECT oi.*, p.name as product_name, p.sku as product_sku, s.name as service_name
            FROM order_items oi 
            LEFT JOIN products p ON oi.product_id = p.id 
            LEFT JOIN services s ON oi.service_id = s.id 
            WHERE oi.order_id = ?
        ");
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $orderItems = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // إنشاء رقم الفاتورة
        // Generate invoice number
        $invoiceNumber = 'INV-' . date('Ymd') . '-' . $orderId;
        
        // إنشاء الفاتورة
        // Generate invoice
        $stmt = $conn->prepare("
            INSERT INTO invoices (order_id, user_id, invoice_number, amount, status, created_at, updated_at)
            VALUES (?, ?, ?, ?, 'pending', NOW(), NOW())
        ");
        
        $stmt->bind_param("iisd", $orderId, $order['user_id'], $invoiceNumber, $order['total']);
        
        if ($stmt->execute()) {
            $invoiceId = $conn->insert_id();
            
            // إضافة إشعار
            // Add notification
            $message = 'تم إنشاء فاتورة جديدة للطلب #' . $order['order_number'] . ' برقم ' . $invoiceNumber;
            addNotification($message, 'invoice');
            
            // إرسال إشعار للعميل
            // Send notification to customer
            $stmt = $conn->prepare("
                INSERT INTO notifications (user_id, message, type, is_read, created_at)
                VALUES (?, ?, ?, 0, NOW())
            ");
            
            $customerMessage = 'تم إنشاء فاتورة جديدة لطلبك #' . $order['order_number'] . ' برقم ' . $invoiceNumber;
            $stmt->bind_param("iss", $order['user_id'], $customerMessage, 'invoice');
            $stmt->execute();
            
            // إعادة التوجيه إلى تفاصيل الطلب
            // Redirect to order details
            redirect('index.php?action=view&id=' . $orderId . '&success=تم إنشاء الفاتورة بنجاح');
            exit;
        } else {
            redirect('index.php?action=view&id=' . $orderId . '&error=حدث خطأ أثناء إنشاء الفاتورة');
            exit;
        }
        
        break;
        
    case 'delete':
        // حذف الطلب
        // Delete order
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            redirect('index.php');
            exit;
        }
        
        $orderId = (int)$_GET['id'];
        
        // الحصول على بيانات الطلب
        // Get order data
        $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $order = $stmt->get_result()->fetch_assoc();
        
        if (!$order) {
            redirect('index.php?error=الطلب غير موجود');
            exit;
        }
        
        // التحقق من إمكانية حذف الطلب
        // Check if order can be deleted
        if ($order['status'] !== 'pending' && $order['status'] !== 'cancelled') {
            redirect('index.php?error=لا يمكن حذف الطلب لأنه قيد المعالجة أو مكتمل');
            exit;
        }
        
        // حذف عناصر الطلب
        // Delete order items
        $stmt = $conn->prepare("DELETE FROM order_items WHERE order_id = ?");
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        
        // حذف سجلات الطلب
        // Delete order logs
        $stmt = $conn->prepare("DELETE FROM order_logs WHERE order_id = ?");
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        
        // حذف الطلب
        // Delete order
        $stmt = $conn->prepare("DELETE FROM orders WHERE id = ?");
        $stmt->bind_param("i", $orderId);
        
        if ($stmt->execute()) {
            // إضافة إشعار
            // Add notification
            $message = 'تم حذف الطلب #' . $order['order_number'];
            addNotification($message, 'order');
            
            // إعادة التوجيه إلى قائمة الطلبات
            // Redirect to orders list
            redirect('index.php?success=تم حذف الطلب بنجاح');
            exit;
        } else {
            redirect('index.php?error=حدث خطأ أثناء حذف الطلب');
            exit;
        }
        
        break;
        
    default:
        // قائمة الطلبات
        // Orders list
        
        // تهيئة متغيرات البحث والترتيب
        // Initialize search and sort variables
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $status = isset($_GET['status']) ? $_GET['status'] : '';
        $dateFrom = isset($_GET['date_from']) ? $_GET['date_from'] : '';
        $dateTo = isset($_GET['date_to']) ? $_GET['date_to'] : '';
        $sort = isset($_GET['sort']) ? $_GET['sort'] : 'id';
        $order = isset($_GET['order']) ? $_GET['order'] : 'DESC';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;
        
        // بناء استعلام البحث
        // Build search query
        $query = "
            SELECT o.*, u.name as customer_name, u.email as customer_email 
            FROM orders o 
            LEFT JOIN users u ON o.user_id = u.id 
            WHERE 1=1
        ";
        $countQuery = "
            SELECT COUNT(*) as total 
            FROM orders o 
            LEFT JOIN users u ON o.user_id = u.id 
            WHERE 1=1
        ";
        $params = [];
        $types = "";
        
        if (!empty($search)) {
            $searchTerm = "%$search%";
            $query .= " AND (o.order_number LIKE ? OR u.name LIKE ? OR u.email LIKE ?)";
            $countQuery .= " AND (o.order_number LIKE ? OR u.name LIKE ? OR u.email LIKE ?)";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= "sss";
        }
        
        if (!empty($status)) {
            $query .= " AND o.status = ?";
            $countQuery .= " AND o.status = ?";
            $params[] = $status;
            $types .= "s";
        }
        
        if (!empty($dateFrom)) {
            $query .= " AND DATE(o.created_at) >= ?";
            $countQuery .= " AND DATE(o.created_at) >= ?";
            $params[] = $dateFrom;
            $types .= "s";
        }
        
        if (!empty($dateTo)) {
            $query .= " AND DATE(o.created_at) <= ?";
            $countQuery .= " AND DATE(o.created_at) <= ?";
            $params[] = $dateTo;
            $types .= "s";
        }
        
        // إضافة الترتيب
        // Add sorting
        $allowedSortFields = ['id', 'order_number', 'total', 'status', 'created_at'];
        $allowedOrderDirections = ['ASC', 'DESC'];
        
        if (!in_array($sort, $allowedSortFields)) {
            $sort = 'id';
        }
        
        if (!in_array($order, $allowedOrderDirections)) {
            $order = 'DESC';
        }
        
        $query .= " ORDER BY o.$sort $order LIMIT ? OFFSET ?";
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
        $orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // حساب عدد الصفحات
        // Calculate total pages
        $totalPages = ceil($total / $limit);
        
        // تضمين ملف الرأس
        // Include header file
        include '../includes/header.php';
        
        // عرض قائمة الطلبات
        // Display orders list
        include 'templates/list_orders.php';
        
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

/**
 * تنسيق العملة
 * Format currency
 * 
 * @param float $amount المبلغ
 * @return string المبلغ المنسق
 */
function formatCurrency($amount) {
    return CURRENCY_SYMBOL . ' ' . number_format($amount, 2);
}

/**
 * ترجمة النص
 * Translate text
 * 
 * @param string $key المفتاح
 * @return string النص المترجم
 */
function translate($key) {
    // هذه دالة وهمية، يجب استبدالها بالتنفيذ الفعلي
    // This is a dummy function, should be replaced with actual implementation
    $translations = [
        'pending' => 'معلق',
        'processing' => 'قيد المعالجة',
        'completed' => 'مكتمل',
        'cancelled' => 'ملغي',
        'refunded' => 'مسترجع',
        'paid' => 'مدفوع',
        'failed' => 'فاشل',
        'paypal' => 'باي بال',
        'stripe' => 'سترايب',
        'bank_transfer' => 'تحويل بنكي',
        'cash_on_delivery' => 'الدفع عند الاستلام'
    ];
    
    return isset($translations[$key]) ? $translations[$key] : $key;
}
?>

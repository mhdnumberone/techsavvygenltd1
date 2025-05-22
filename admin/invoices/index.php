<?php
/**
 * صفحة إدارة الفواتير
 * Invoices Management Page
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
$pageTitle = 'إدارة الفواتير | ' . SITE_NAME;
$activeMenu = 'invoices';

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
        // عرض الفاتورة
        // View invoice
        
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            redirect('index.php');
            exit;
        }
        
        $invoiceId = (int)$_GET['id'];
        
        // الحصول على بيانات الفاتورة
        // Get invoice data
        $stmt = $conn->prepare("
            SELECT i.*, o.order_number, u.name as user_name, u.email as user_email, u.phone as user_phone,
            u.address as user_address, p.transaction_id, p.payment_method
            FROM invoices i
            LEFT JOIN orders o ON i.order_id = o.id
            LEFT JOIN users u ON i.user_id = u.id
            LEFT JOIN payments p ON i.payment_id = p.id
            WHERE i.id = ?
        ");
        
        $stmt->bind_param("i", $invoiceId);
        $stmt->execute();
        $invoice = $stmt->get_result()->fetch_assoc();
        
        if (!$invoice) {
            redirect('index.php?error=الفاتورة غير موجودة');
            exit;
        }
        
        // الحصول على عناصر الفاتورة
        // Get invoice items
        $stmt = $conn->prepare("
            SELECT i.*, 
            CASE 
                WHEN i.product_id IS NOT NULL THEN p.name
                WHEN i.service_id IS NOT NULL THEN s.name
                ELSE NULL
            END as item_name
            FROM invoice_items i
            LEFT JOIN products p ON i.product_id = p.id
            LEFT JOIN services s ON i.service_id = s.id
            WHERE i.invoice_id = ?
        ");
        
        $stmt->bind_param("i", $invoiceId);
        $stmt->execute();
        $invoiceItems = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // تضمين ملف الرأس
        // Include header file
        include '../includes/header.php';
        
        // عرض الفاتورة
        // Display invoice
        include 'templates/view_invoice.php';
        
        break;
        
    case 'generate':
        // إنشاء فاتورة جديدة
        // Generate new invoice
        
        if (!isset($_GET['order_id']) || empty($_GET['order_id'])) {
            redirect('index.php');
            exit;
        }
        
        $orderId = (int)$_GET['order_id'];
        
        // التحقق من عدم وجود فاتورة للطلب
        // Check if invoice already exists for the order
        $stmt = $conn->prepare("SELECT id FROM invoices WHERE order_id = ?");
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $existingInvoice = $stmt->get_result()->fetch_assoc();
        
        if ($existingInvoice) {
            redirect('index.php?action=view&id=' . $existingInvoice['id'] . '&error=الفاتورة موجودة بالفعل لهذا الطلب');
            exit;
        }
        
        // الحصول على بيانات الطلب
        // Get order data
        $stmt = $conn->prepare("
            SELECT o.*, u.name as user_name, u.email as user_email, u.phone as user_phone,
            u.address as user_address, p.id as payment_id, p.transaction_id, p.payment_method
            FROM orders o
            LEFT JOIN users u ON o.user_id = u.id
            LEFT JOIN payments p ON p.order_id = o.id
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
            SELECT oi.*, 
            CASE 
                WHEN oi.product_id IS NOT NULL THEN p.name
                WHEN oi.service_id IS NOT NULL THEN s.name
                ELSE NULL
            END as item_name
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
        $invoiceNumber = 'INV-' . date('Ymd') . '-' . rand(1000, 9999);
        
        // إنشاء الفاتورة
        // Create invoice
        $stmt = $conn->prepare("
            INSERT INTO invoices (
                invoice_number, order_id, user_id, payment_id, subtotal, 
                tax, discount, total, status, due_date, 
                notes, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        
        $status = 'paid'; // أو 'unpaid' حسب حالة الدفع
        $dueDate = date('Y-m-d', strtotime('+7 days'));
        $notes = 'تم إنشاء الفاتورة تلقائياً';
        
        $stmt->bind_param(
            "siiidddsss",
            $invoiceNumber, $orderId, $order['user_id'], $order['payment_id'],
            $order['subtotal'], $order['tax'], $order['discount'],
            $order['total'], $status, $dueDate, $notes
        );
        
        if ($stmt->execute()) {
            $invoiceId = $conn->insert_id;
            
            // إضافة عناصر الفاتورة
            // Add invoice items
            foreach ($orderItems as $item) {
                $stmt = $conn->prepare("
                    INSERT INTO invoice_items (
                        invoice_id, product_id, service_id, quantity, 
                        price, tax, discount, total
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                $productId = $item['product_id'] ?: null;
                $serviceId = $item['service_id'] ?: null;
                
                $stmt->bind_param(
                    "iiidddd",
                    $invoiceId, $productId, $serviceId, $item['quantity'],
                    $item['price'], $item['tax'], $item['discount'], $item['total']
                );
                
                $stmt->execute();
            }
            
            // إضافة إشعار للمستخدم
            // Add notification to user
            $notificationMessage = 'تم إنشاء فاتورة جديدة لطلبك رقم ' . $order['order_number'];
            
            $stmt = $conn->prepare("
                INSERT INTO notifications (user_id, message, type, is_read, created_at)
                VALUES (?, ?, 'invoice', 0, NOW())
            ");
            
            $stmt->bind_param("is", $order['user_id'], $notificationMessage);
            $stmt->execute();
            
            // إرسال بريد إلكتروني للمستخدم
            // Send email to user
            $emailSubject = 'فاتورة جديدة - ' . $invoiceNumber;
            $emailMessage = "
                <p>مرحباً {$order['user_name']},</p>
                <p>تم إنشاء فاتورة جديدة لطلبك رقم {$order['order_number']}.</p>
                <p>رقم الفاتورة: {$invoiceNumber}</p>
                <p>المبلغ الإجمالي: {$order['total']}</p>
                <p>يمكنك عرض الفاتورة وتنزيلها من خلال الرابط التالي:</p>
                <p><a href='" . SITE_URL . "/invoices/view.php?id={$invoiceId}'>عرض الفاتورة</a></p>
                <p>شكراً لك,<br>" . SITE_NAME . "</p>
            ";
            
            sendEmail($order['user_email'], $emailSubject, $emailMessage);
            
            // إعادة التوجيه إلى صفحة الفاتورة
            // Redirect to invoice page
            redirect('index.php?action=view&id=' . $invoiceId . '&success=تم إنشاء الفاتورة بنجاح');
            exit;
        } else {
            redirect('index.php?error=حدث خطأ أثناء إنشاء الفاتورة');
            exit;
        }
        
        break;
        
    case 'download':
        // تنزيل الفاتورة
        // Download invoice
        
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            redirect('index.php');
            exit;
        }
        
        $invoiceId = (int)$_GET['id'];
        
        // الحصول على بيانات الفاتورة
        // Get invoice data
        $stmt = $conn->prepare("
            SELECT i.*, o.order_number, u.name as user_name, u.email as user_email, u.phone as user_phone,
            u.address as user_address, p.transaction_id, p.payment_method
            FROM invoices i
            LEFT JOIN orders o ON i.order_id = o.id
            LEFT JOIN users u ON i.user_id = u.id
            LEFT JOIN payments p ON i.payment_id = p.id
            WHERE i.id = ?
        ");
        
        $stmt->bind_param("i", $invoiceId);
        $stmt->execute();
        $invoice = $stmt->get_result()->fetch_assoc();
        
        if (!$invoice) {
            redirect('index.php?error=الفاتورة غير موجودة');
            exit;
        }
        
        // الحصول على عناصر الفاتورة
        // Get invoice items
        $stmt = $conn->prepare("
            SELECT i.*, 
            CASE 
                WHEN i.product_id IS NOT NULL THEN p.name
                WHEN i.service_id IS NOT NULL THEN s.name
                ELSE NULL
            END as item_name
            FROM invoice_items i
            LEFT JOIN products p ON i.product_id = p.id
            LEFT JOIN services s ON i.service_id = s.id
            WHERE i.invoice_id = ?
        ");
        
        $stmt->bind_param("i", $invoiceId);
        $stmt->execute();
        $invoiceItems = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // إنشاء ملف PDF
        // Create PDF file
        require_once '../../vendor/autoload.php';
        
        // إنشاء مستند PDF
        // Create PDF document
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        // تعيين معلومات المستند
        // Set document information
        $pdf->SetCreator(SITE_NAME);
        $pdf->SetAuthor(SITE_NAME);
        $pdf->SetTitle('فاتورة - ' . $invoice['invoice_number']);
        $pdf->SetSubject('فاتورة - ' . $invoice['invoice_number']);
        
        // تعيين الهوامش
        // Set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        
        // تعيين فواصل الصفحات التلقائية
        // Set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        
        // تعيين عامل مقياس الصورة
        // Set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        
        // تعيين الخط
        // Set font
        $pdf->SetFont('aealarabiya', '', 12);
        
        // إضافة صفحة
        // Add a page
        $pdf->AddPage();
        
        // إنشاء محتوى الفاتورة
        // Create invoice content
        $html = '
            <h1 style="text-align: center;">فاتورة</h1>
            <table>
                <tr>
                    <td><strong>رقم الفاتورة:</strong> ' . $invoice['invoice_number'] . '</td>
                    <td><strong>تاريخ الإنشاء:</strong> ' . date('Y-m-d', strtotime($invoice['created_at'])) . '</td>
                </tr>
                <tr>
                    <td><strong>رقم الطلب:</strong> ' . $invoice['order_number'] . '</td>
                    <td><strong>تاريخ الاستحقاق:</strong> ' . $invoice['due_date'] . '</td>
                </tr>
                <tr>
                    <td><strong>الحالة:</strong> ' . ($invoice['status'] === 'paid' ? 'مدفوعة' : 'غير مدفوعة') . '</td>
                    <td><strong>طريقة الدفع:</strong> ' . $invoice['payment_method'] . '</td>
                </tr>
            </table>
            
            <h2>معلومات العميل</h2>
            <table>
                <tr>
                    <td><strong>الاسم:</strong> ' . $invoice['user_name'] . '</td>
                    <td><strong>البريد الإلكتروني:</strong> ' . $invoice['user_email'] . '</td>
                </tr>
                <tr>
                    <td><strong>الهاتف:</strong> ' . $invoice['user_phone'] . '</td>
                    <td><strong>العنوان:</strong> ' . $invoice['user_address'] . '</td>
                </tr>
            </table>
            
            <h2>عناصر الفاتورة</h2>
            <table border="1" cellpadding="5">
                <tr style="background-color: #f2f2f2;">
                    <th>العنصر</th>
                    <th>الكمية</th>
                    <th>السعر</th>
                    <th>الضريبة</th>
                    <th>الخصم</th>
                    <th>الإجمالي</th>
                </tr>
        ';
        
        foreach ($invoiceItems as $item) {
            $html .= '
                <tr>
                    <td>' . $item['item_name'] . '</td>
                    <td>' . $item['quantity'] . '</td>
                    <td>' . $item['price'] . '</td>
                    <td>' . $item['tax'] . '</td>
                    <td>' . $item['discount'] . '</td>
                    <td>' . $item['total'] . '</td>
                </tr>
            ';
        }
        
        $html .= '
            </table>
            
            <h2>ملخص الفاتورة</h2>
            <table>
                <tr>
                    <td><strong>المجموع الفرعي:</strong></td>
                    <td>' . $invoice['subtotal'] . '</td>
                </tr>
                <tr>
                    <td><strong>الضريبة:</strong></td>
                    <td>' . $invoice['tax'] . '</td>
                </tr>
                <tr>
                    <td><strong>الخصم:</strong></td>
                    <td>' . $invoice['discount'] . '</td>
                </tr>
                <tr>
                    <td><strong>المجموع الكلي:</strong></td>
                    <td>' . $invoice['total'] . '</td>
                </tr>
            </table>
            
            <h2>ملاحظات</h2>
            <p>' . $invoice['notes'] . '</p>
        ';
        
        // طباعة المحتوى
        // Print content
        $pdf->writeHTML($html, true, false, false, false, '');
        
        // إغلاق وإخراج مستند PDF
        // Close and output PDF document
        $pdf->Output('invoice_' . $invoice['invoice_number'] . '.pdf', 'D');
        exit;
        
        break;
        
    case 'send':
        // إرسال الفاتورة بالبريد الإلكتروني
        // Send invoice by email
        
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            redirect('index.php');
            exit;
        }
        
        $invoiceId = (int)$_GET['id'];
        
        // الحصول على بيانات الفاتورة
        // Get invoice data
        $stmt = $conn->prepare("
            SELECT i.*, o.order_number, u.name as user_name, u.email as user_email
            FROM invoices i
            LEFT JOIN orders o ON i.order_id = o.id
            LEFT JOIN users u ON i.user_id = u.id
            WHERE i.id = ?
        ");
        
        $stmt->bind_param("i", $invoiceId);
        $stmt->execute();
        $invoice = $stmt->get_result()->fetch_assoc();
        
        if (!$invoice) {
            redirect('index.php?error=الفاتورة غير موجودة');
            exit;
        }
        
        // إنشاء رابط تنزيل الفاتورة
        // Create invoice download link
        $downloadLink = SITE_URL . '/invoices/download.php?id=' . $invoiceId;
        
        // إرسال بريد إلكتروني للمستخدم
        // Send email to user
        $emailSubject = 'فاتورة - ' . $invoice['invoice_number'];
        $emailMessage = "
            <p>مرحباً {$invoice['user_name']},</p>
            <p>مرفق فاتورة لطلبك رقم {$invoice['order_number']}.</p>
            <p>رقم الفاتورة: {$invoice['invoice_number']}</p>
            <p>المبلغ الإجمالي: {$invoice['total']}</p>
            <p>يمكنك تنزيل الفاتورة من خلال الرابط التالي:</p>
            <p><a href='{$downloadLink}'>تنزيل الفاتورة</a></p>
            <p>شكراً لك,<br>" . SITE_NAME . "</p>
        ";
        
        if (sendEmail($invoice['user_email'], $emailSubject, $emailMessage)) {
            // إضافة إشعار للمستخدم
            // Add notification to user
            $notificationMessage = 'تم إرسال الفاتورة رقم ' . $invoice['invoice_number'] . ' إلى بريدك الإلكتروني';
            
            $stmt = $conn->prepare("
                INSERT INTO notifications (user_id, message, type, is_read, created_at)
                VALUES (?, ?, 'invoice', 0, NOW())
            ");
            
            $stmt->bind_param("is", $invoice['user_id'], $notificationMessage);
            $stmt->execute();
            
            // إعادة التوجيه إلى صفحة الفاتورة
            // Redirect to invoice page
            redirect('index.php?action=view&id=' . $invoiceId . '&success=تم إرسال الفاتورة بنجاح');
            exit;
        } else {
            redirect('index.php?action=view&id=' . $invoiceId . '&error=حدث خطأ أثناء إرسال الفاتورة');
            exit;
        }
        
        break;
        
    case 'delete':
        // حذف الفاتورة
        // Delete invoice
        
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            redirect('index.php');
            exit;
        }
        
        $invoiceId = (int)$_GET['id'];
        
        // الحصول على بيانات الفاتورة
        // Get invoice data
        $stmt = $conn->prepare("SELECT * FROM invoices WHERE id = ?");
        $stmt->bind_param("i", $invoiceId);
        $stmt->execute();
        $invoice = $stmt->get_result()->fetch_assoc();
        
        if (!$invoice) {
            redirect('index.php?error=الفاتورة غير موجودة');
            exit;
        }
        
        // حذف عناصر الفاتورة
        // Delete invoice items
        $stmt = $conn->prepare("DELETE FROM invoice_items WHERE invoice_id = ?");
        $stmt->bind_param("i", $invoiceId);
        $stmt->execute();
        
        // حذف الفاتورة
        // Delete invoice
        $stmt = $conn->prepare("DELETE FROM invoices WHERE id = ?");
        $stmt->bind_param("i", $invoiceId);
        
        if ($stmt->execute()) {
            // إعادة التوجيه إلى قائمة الفواتير
            // Redirect to invoices list
            redirect('index.php?success=تم حذف الفاتورة بنجاح');
            exit;
        } else {
            redirect('index.php?error=حدث خطأ أثناء حذف الفاتورة');
            exit;
        }
        
        break;
        
    default:
        // قائمة الفواتير
        // Invoices list
        
        // تهيئة متغيرات البحث والترتيب
        // Initialize search and sort variables
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $status = isset($_GET['status']) ? $_GET['status'] : '';
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
            SELECT i.*, o.order_number, u.name as user_name, u.email as user_email
            FROM invoices i
            LEFT JOIN orders o ON i.order_id = o.id
            LEFT JOIN users u ON i.user_id = u.id
            WHERE 1=1
        ";
        
        $countQuery = "
            SELECT COUNT(*) as total
            FROM invoices i
            LEFT JOIN orders o ON i.order_id = o.id
            LEFT JOIN users u ON i.user_id = u.id
            WHERE 1=1
        ";
        
        $params = [];
        $types = "";
        
        if (!empty($search)) {
            $searchTerm = "%{$search}%";
            $query .= " AND (i.invoice_number LIKE ? OR u.name LIKE ? OR u.email LIKE ? OR o.order_number LIKE ?)";
            $countQuery .= " AND (i.invoice_number LIKE ? OR u.name LIKE ? OR u.email LIKE ? OR o.order_number LIKE ?)";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= "ssss";
        }
        
        if (!empty($status)) {
            $query .= " AND i.status = ?";
            $countQuery .= " AND i.status = ?";
            $params[] = $status;
            $types .= "s";
        }
        
        if (!empty($dateFrom)) {
            $query .= " AND DATE(i.created_at) >= ?";
            $countQuery .= " AND DATE(i.created_at) >= ?";
            $params[] = $dateFrom;
            $types .= "s";
        }
        
        if (!empty($dateTo)) {
            $query .= " AND DATE(i.created_at) <= ?";
            $countQuery .= " AND DATE(i.created_at) <= ?";
            $params[] = $dateTo;
            $types .= "s";
        }
        
        // إضافة الترتيب
        // Add sorting
        $allowedSortFields = ['id', 'invoice_number', 'total', 'status', 'created_at', 'due_date'];
        $allowedOrderDirections = ['ASC', 'DESC'];
        
        if (!in_array($sort, $allowedSortFields)) {
            $sort = 'created_at';
        }
        
        if (!in_array($order, $allowedOrderDirections)) {
            $order = 'DESC';
        }
        
        $query .= " ORDER BY i.{$sort} {$order} LIMIT ? OFFSET ?";
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
        $invoices = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // حساب عدد الصفحات
        // Calculate total pages
        $totalPages = ceil($total / $limit);
        
        // تضمين ملف الرأس
        // Include header file
        include '../includes/header.php';
        
        // عرض قائمة الفواتير
        // Display invoices list
        include 'templates/list_invoices.php';
        
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

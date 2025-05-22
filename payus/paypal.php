<?php
/**
 * صفحة بوابة الدفع PayPal
 * PayPal Payment Gateway Page
 */

// تضمين ملفات الإعدادات والوظائف المساعدة
// Include configuration and helper files
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

// بدء الجلسة
// Start session
session_start();

// التحقق من وجود معرف الطلب
// Check if order ID exists
if (!isset($_GET['order_id']) || empty($_GET['order_id'])) {
    redirect('../index.php');
    exit;
}

$orderId = (int)$_GET['order_id'];

// الحصول على بيانات الطلب
// Get order data
$db = new Database();
$conn = $db->getConnection();
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->bind_param("i", $orderId);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    redirect('../index.php');
    exit;
}

// التحقق من أن الطلب للمستخدم الحالي
// Verify that the order belongs to the current user
if (isLoggedIn() && $order['user_id'] != $_SESSION['user_id'] && !isAdmin()) {
    redirect('../index.php');
    exit;
}

// الحصول على بيانات العميل
// Get customer data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $order['user_id']);
$stmt->execute();
$customer = $stmt->get_result()->fetch_assoc();

// إعدادات PayPal
// PayPal settings
$paypalClientId = PAYPAL_CLIENT_ID;
$paypalSecret = PAYPAL_SECRET;
$paypalMode = PAYPAL_MODE; // sandbox or live

// تهيئة PayPal API
// Initialize PayPal API
$paypalUrl = ($paypalMode === 'live') ? 'https://api.paypal.com' : 'https://api.sandbox.paypal.com';

// إنشاء معاملة PayPal
// Create PayPal transaction
$paypalOrderId = isset($_GET['paypal_order_id']) ? $_GET['paypal_order_id'] : '';
$paymentStatus = isset($_GET['status']) ? $_GET['status'] : '';

// معالجة الدفع
// Process payment
if ($paymentStatus === 'success' && !empty($paypalOrderId)) {
    // التحقق من حالة الدفع
    // Verify payment status
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $paypalUrl . '/v2/checkout/orders/' . $paypalOrderId);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Basic ' . base64_encode($paypalClientId . ':' . $paypalSecret)
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $paypalOrder = json_decode($response, true);
        
        if ($paypalOrder['status'] === 'COMPLETED') {
            // تحديث حالة الطلب
            // Update order status
            $stmt = $conn->prepare("UPDATE orders SET status = 'paid', payment_status = 'completed', payment_method = 'paypal', payment_id = ?, updated_at = NOW() WHERE id = ?");
            $stmt->bind_param("si", $paypalOrderId, $orderId);
            $stmt->execute();
            
            // إنشاء سجل الدفع
            // Create payment record
            $stmt = $conn->prepare("
                INSERT INTO payments (order_id, user_id, amount, payment_method, transaction_id, status, created_at)
                VALUES (?, ?, ?, 'paypal', ?, 'completed', NOW())
            ");
            $stmt->bind_param("iids", $orderId, $order['user_id'], $order['total'], $paypalOrderId);
            $stmt->execute();
            
            // إنشاء الفاتورة
            // Create invoice
            $invoiceNumber = generateInvoiceNumber();
            $stmt = $conn->prepare("
                INSERT INTO invoices (order_id, user_id, invoice_number, amount, status, created_at)
                VALUES (?, ?, ?, ?, 'paid', NOW())
            ");
            $stmt->bind_param("iisd", $orderId, $order['user_id'], $invoiceNumber, $order['total']);
            $stmt->execute();
            $invoiceId = $conn->insert_id;
            
            // إرسال إشعار للعميل
            // Send notification to customer
            $notification = new Notification();
            $notification->createNotification([
                'user_id' => $order['user_id'],
                'title' => 'تم استلام الدفع',
                'message' => 'تم استلام دفعتك بنجاح للطلب رقم ' . $order['order_number'] . '.',
                'type' => 'payment',
                'reference_id' => $orderId
            ]);
            
            // إرسال بريد إلكتروني للعميل
            // Send email to customer
            $emailData = [
                'to' => $customer['email'],
                'subject' => 'تأكيد الدفع - ' . SITE_NAME,
                'template' => 'payment_confirmation',
                'data' => [
                    'customer_name' => $customer['name'],
                    'order_number' => $order['order_number'],
                    'order_date' => date('Y-m-d', strtotime($order['created_at'])),
                    'payment_method' => 'PayPal',
                    'payment_id' => $paypalOrderId,
                    'amount' => formatCurrency($order['total']),
                    'invoice_url' => SITE_URL . '/account/invoices.php?id=' . $invoiceId
                ]
            ];
            
            sendEmail($emailData);
            
            // إعادة التوجيه إلى صفحة التأكيد
            // Redirect to confirmation page
            redirect('../account/order-confirmation.php?id=' . $orderId);
            exit;
        }
    }
}

// تضمين ملف الرأس
// Include header file
include '../includes/header.php';
?>

<!-- محتوى الصفحة -->
<!-- Page Content -->
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">الدفع عبر PayPal</h4>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <img src="<?php echo SITE_URL; ?>/assets/images/paypal-logo.png" alt="PayPal" class="img-fluid" style="max-height: 60px;">
                    </div>
                    
                    <div class="order-summary mb-4">
                        <h5>ملخص الطلب</h5>
                        <table class="table table-bordered">
                            <tr>
                                <th>رقم الطلب:</th>
                                <td>#<?php echo $order['order_number']; ?></td>
                            </tr>
                            <tr>
                                <th>تاريخ الطلب:</th>
                                <td><?php echo date('Y-m-d', strtotime($order['created_at'])); ?></td>
                            </tr>
                            <tr>
                                <th>المبلغ الإجمالي:</th>
                                <td><?php echo formatCurrency($order['total']); ?></td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="payment-options">
                        <div id="paypal-button-container" class="text-center"></div>
                        <div class="text-center mt-3">
                            <p class="text-muted">سيتم تحويلك إلى PayPal لإتمام عملية الدفع بأمان.</p>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="../account/orders.php" class="btn btn-secondary">العودة إلى الطلبات</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- سكريبت PayPal -->
<!-- PayPal Script -->
<script src="https://www.paypal.com/sdk/js?client-id=<?php echo $paypalClientId; ?>&currency=<?php echo CURRENCY_CODE; ?>"></script>
<script>
    paypal.Buttons({
        createOrder: function(data, actions) {
            return actions.order.create({
                purchase_units: [{
                    description: 'Order #<?php echo $order['order_number']; ?>',
                    amount: {
                        value: '<?php echo number_format($order['total'], 2, '.', ''); ?>'
                    }
                }]
            });
        },
        onApprove: function(data, actions) {
            return actions.order.capture().then(function(details) {
                // إعادة التوجيه إلى صفحة التأكيد
                // Redirect to confirmation page
                window.location.href = '<?php echo SITE_URL; ?>/payus/paypal.php?order_id=<?php echo $orderId; ?>&paypal_order_id=' + data.orderID + '&status=success';
            });
        },
        onError: function(err) {
            console.error('Error', err);
            alert('حدث خطأ أثناء معالجة الدفع. يرجى المحاولة مرة أخرى.');
        }
    }).render('#paypal-button-container');
</script>

<?php
// تضمين ملف التذييل
// Include footer file
include '../includes/footer.php';

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
 * توليد رقم الفاتورة
 * Generate invoice number
 * 
 * @return string رقم الفاتورة
 */
function generateInvoiceNumber() {
    $prefix = 'INV';
    $year = date('Y');
    $month = date('m');
    $random = mt_rand(1000, 9999);
    
    return $prefix . $year . $month . $random;
}

/**
 * إرسال بريد إلكتروني
 * Send email
 * 
 * @param array $data بيانات البريد الإلكتروني
 * @return bool نتيجة العملية
 */
function sendEmail($data) {
    // هذه دالة وهمية، يجب استبدالها بالتنفيذ الفعلي
    // This is a dummy function, should be replaced with actual implementation
    return true;
}
?>

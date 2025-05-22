<?php
/**
 * صفحة إنشاء جلسة Stripe
 * Create Stripe Session Page
 */

// تضمين ملفات الإعدادات والوظائف المساعدة
// Include configuration and helper files
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

// تحميل مكتبة Stripe
// Load Stripe library
require_once '../vendor/autoload.php';

// تهيئة Stripe API
// Initialize Stripe API
\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

// التحقق من طريقة الطلب
// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// الحصول على البيانات المرسلة
// Get posted data
$input = json_decode(file_get_contents('php://input'), true);
$orderId = isset($input['order_id']) ? (int)$input['order_id'] : 0;

if ($orderId <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid order ID']);
    exit;
}

// الحصول على بيانات الطلب
// Get order data
$db = new Database();
$conn = $db->getConnection();
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->bind_param("i", $orderId);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    http_response_code(404);
    echo json_encode(['error' => 'Order not found']);
    exit;
}

try {
    // إنشاء جلسة دفع Stripe
    // Create Stripe checkout session
    $session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => [[
            'price_data' => [
                'currency' => strtolower(CURRENCY_CODE),
                'product_data' => [
                    'name' => 'Order #' . $order['order_number'],
                    'description' => 'Payment for order #' . $order['order_number'],
                ],
                'unit_amount' => round($order['total'] * 100), // Stripe requires amount in cents
            ],
            'quantity' => 1,
        ]],
        'mode' => 'payment',
        'success_url' => SITE_URL . '/payus/stripe.php?order_id=' . $orderId . '&session_id={CHECKOUT_SESSION_ID}&status=success',
        'cancel_url' => SITE_URL . '/payus/stripe.php?order_id=' . $orderId . '&status=cancel',
        'customer_email' => $order['email'],
        'client_reference_id' => $order['order_number'],
        'metadata' => [
            'order_id' => $orderId,
            'order_number' => $order['order_number']
        ]
    ]);
    
    // إرسال معرف الجلسة
    // Send session ID
    echo json_encode(['id' => $session->id]);
} catch (Exception $e) {
    // تسجيل الخطأ
    // Log error
    error_log('Stripe session creation error: ' . $e->getMessage());
    
    http_response_code(500);
    echo json_encode(['error' => 'Failed to create checkout session']);
}
?>

<?php
/**
 * صفحة بوابة الدفع
 * Payment Gateway Page
 */

// تضمين ملفات الإعدادات والوظائف المساعدة
// Include configuration and helper files
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

// بدء الجلسة
// Start session
session_start();

// التحقق من تسجيل الدخول
// Check login
if (!isLoggedIn()) {
    redirect('../login.php');
    exit;
}

// التحقق من وجود معرف الطلب
// Check if order ID exists
if (!isset($_GET['order_id']) || empty($_GET['order_id'])) {
    redirect('../index.php');
    exit;
}

$orderId = (int)$_GET['order_id'];

// إنشاء كائن الطلب
// Create order object
$order = new Order();

// الحصول على بيانات الطلب
// Get order data
$orderData = $order->getOrderById($orderId);

// التحقق من وجود الطلب وملكيته
// Check if order exists and belongs to user
if (!$orderData || $orderData['user_id'] != getCurrentUserId()) {
    redirect('../index.php');
    exit;
}

// التحقق من حالة الطلب
// Check order status
if ($orderData['status'] !== 'pending') {
    redirect('../account/orders.php');
    exit;
}

// الحصول على طريقة الدفع
// Get payment method
$paymentMethod = isset($_GET['method']) ? $_GET['method'] : 'credit_card';

// تهيئة متغيرات الصفحة
// Initialize page variables
$pageTitle = translate('payment_gateway');
$paymentAmount = $orderData['total_amount'];
$paymentCurrency = CURRENCY_CODE;
$paymentDescription = translate('payment_for_order') . ' #' . $orderId;

// معالجة نموذج الدفع
// Process payment form
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // التحقق من البيانات المطلوبة
    // Validate required data
    if ($paymentMethod === 'credit_card') {
        if (empty($_POST['card_number']) || empty($_POST['card_holder']) || empty($_POST['expiry_date']) || empty($_POST['cvv'])) {
            $message = translate('missing_required_fields');
            $messageType = 'danger';
        } else {
            // معالجة الدفع ببطاقة الائتمان
            // Process credit card payment
            $result = processCreditCardPayment($orderId, $_POST);
            
            if ($result['success']) {
                // تحديث حالة الطلب
                // Update order status
                $order->updateOrderStatus($orderId, 'processing');
                
                // إنشاء الفاتورة
                // Create invoice
                createInvoice($orderId);
                
                // إعادة التوجيه إلى صفحة التأكيد
                // Redirect to confirmation page
                redirect('../checkout/confirmation.php?order_id=' . $orderId);
                exit;
            } else {
                $message = $result['message'];
                $messageType = 'danger';
            }
        }
    } else if ($paymentMethod === 'paypal') {
        // معالجة الدفع عبر باي بال
        // Process PayPal payment
        $result = processPayPalPayment($orderId);
        
        if ($result['success']) {
            // إعادة التوجيه إلى صفحة باي بال
            // Redirect to PayPal page
            redirect($result['redirect_url']);
            exit;
        } else {
            $message = $result['message'];
            $messageType = 'danger';
        }
    } else if ($paymentMethod === 'bank_transfer') {
        // معالجة الدفع عبر التحويل البنكي
        // Process bank transfer payment
        $result = processBankTransferPayment($orderId, $_POST);
        
        if ($result['success']) {
            // تحديث حالة الطلب
            // Update order status
            $order->updateOrderStatus($orderId, 'pending_payment');
            
            // إعادة التوجيه إلى صفحة التأكيد
            // Redirect to confirmation page
            redirect('../checkout/confirmation.php?order_id=' . $orderId . '&payment=bank_transfer');
            exit;
        } else {
            $message = $result['message'];
            $messageType = 'danger';
        }
    }
}

// تضمين ملف الرأس
// Include header file
include '../includes/header.php';
?>

<!-- محتوى الصفحة -->
<!-- Page Content -->
<div class="payment-page">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card">
                    <div class="card-header">
                        <h1 class="card-title"><?php echo $pageTitle; ?></h1>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($message)): ?>
                        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                            <?php echo $message; ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <?php endif; ?>
                        
                        <div class="payment-summary mb-4">
                            <h4><?php echo translate('order_summary'); ?></h4>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <tbody>
                                        <tr>
                                            <th><?php echo translate('order_number'); ?></th>
                                            <td>#<?php echo $orderId; ?></td>
                                        </tr>
                                        <tr>
                                            <th><?php echo translate('order_date'); ?></th>
                                            <td><?php echo formatDate($orderData['created_at']); ?></td>
                                        </tr>
                                        <tr>
                                            <th><?php echo translate('payment_amount'); ?></th>
                                            <td><?php echo formatCurrency($paymentAmount); ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <div class="payment-methods mb-4">
                            <h4><?php echo translate('payment_method'); ?></h4>
                            <ul class="nav nav-pills mb-3" id="payment-methods-tab" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link <?php echo ($paymentMethod === 'credit_card') ? 'active' : ''; ?>" id="credit-card-tab" href="?order_id=<?php echo $orderId; ?>&method=credit_card">
                                        <i class="fas fa-credit-card"></i> <?php echo translate('credit_card'); ?>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?php echo ($paymentMethod === 'paypal') ? 'active' : ''; ?>" id="paypal-tab" href="?order_id=<?php echo $orderId; ?>&method=paypal">
                                        <i class="fab fa-paypal"></i> <?php echo translate('paypal'); ?>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?php echo ($paymentMethod === 'bank_transfer') ? 'active' : ''; ?>" id="bank-transfer-tab" href="?order_id=<?php echo $orderId; ?>&method=bank_transfer">
                                        <i class="fas fa-university"></i> <?php echo translate('bank_transfer'); ?>
                                    </a>
                                </li>
                            </ul>
                            
                            <div class="tab-content" id="payment-methods-content">
                                <?php if ($paymentMethod === 'credit_card'): ?>
                                <!-- نموذج بطاقة الائتمان -->
                                <!-- Credit Card Form -->
                                <div class="tab-pane fade show active" id="credit-card" role="tabpanel" aria-labelledby="credit-card-tab">
                                    <form action="?order_id=<?php echo $orderId; ?>&method=credit_card" method="post" id="credit-card-form">
                                        <div class="form-group">
                                            <label for="card_number"><?php echo translate('card_number'); ?> <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="card_number" name="card_number" placeholder="1234 5678 9012 3456" required>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="card_holder"><?php echo translate('card_holder_name'); ?> <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="card_holder" name="card_holder" placeholder="John Doe" required>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="expiry_date"><?php echo translate('expiry_date'); ?> <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" id="expiry_date" name="expiry_date" placeholder="MM/YY" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="cvv"><?php echo translate('cvv'); ?> <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" id="cvv" name="cvv" placeholder="123" required>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="form-group">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="save_card" name="save_card" value="1">
                                                <label class="custom-control-label" for="save_card"><?php echo translate('save_card_for_future_payments'); ?></label>
                                            </div>
                                        </div>
                                        
                                        <div class="form-group text-right">
                                            <button type="submit" class="btn btn-primary btn-lg">
                                                <?php echo translate('pay_now'); ?> <?php echo formatCurrency($paymentAmount); ?>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                                <?php elseif ($paymentMethod === 'paypal'): ?>
                                <!-- نموذج باي بال -->
                                <!-- PayPal Form -->
                                <div class="tab-pane fade show active" id="paypal" role="tabpanel" aria-labelledby="paypal-tab">
                                    <div class="paypal-info mb-4">
                                        <p><?php echo translate('paypal_description'); ?></p>
                                    </div>
                                    
                                    <form action="?order_id=<?php echo $orderId; ?>&method=paypal" method="post" id="paypal-form">
                                        <div class="form-group text-center">
                                            <button type="submit" class="btn btn-primary btn-lg">
                                                <i class="fab fa-paypal"></i> <?php echo translate('pay_with_paypal'); ?>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                                <?php elseif ($paymentMethod === 'bank_transfer'): ?>
                                <!-- نموذج التحويل البنكي -->
                                <!-- Bank Transfer Form -->
                                <div class="tab-pane fade show active" id="bank-transfer" role="tabpanel" aria-labelledby="bank-transfer-tab">
                                    <div class="bank-info mb-4">
                                        <p><?php echo translate('bank_transfer_description'); ?></p>
                                        
                                        <div class="alert alert-info">
                                            <h5><?php echo translate('bank_account_details'); ?></h5>
                                            <p><strong><?php echo translate('bank_name'); ?>:</strong> <?php echo BANK_NAME; ?></p>
                                            <p><strong><?php echo translate('account_name'); ?>:</strong> <?php echo ACCOUNT_NAME; ?></p>
                                            <p><strong><?php echo translate('account_number'); ?>:</strong> <?php echo ACCOUNT_NUMBER; ?></p>
                                            <p><strong><?php echo translate('iban'); ?>:</strong> <?php echo IBAN; ?></p>
                                            <p><strong><?php echo translate('swift_code'); ?>:</strong> <?php echo SWIFT_CODE; ?></p>
                                        </div>
                                    </div>
                                    
                                    <form action="?order_id=<?php echo $orderId; ?>&method=bank_transfer" method="post" id="bank-transfer-form" enctype="multipart/form-data">
                                        <div class="form-group">
                                            <label for="transfer_date"><?php echo translate('transfer_date'); ?> <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control" id="transfer_date" name="transfer_date" required>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="transfer_amount"><?php echo translate('transfer_amount'); ?> <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" id="transfer_amount" name="transfer_amount" value="<?php echo $paymentAmount; ?>" step="0.01" required>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="transfer_reference"><?php echo translate('transfer_reference'); ?> <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="transfer_reference" name="transfer_reference" required>
                                            <small class="form-text text-muted"><?php echo translate('transfer_reference_help'); ?></small>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="transfer_receipt"><?php echo translate('transfer_receipt'); ?></label>
                                            <input type="file" class="form-control-file" id="transfer_receipt" name="transfer_receipt">
                                            <small class="form-text text-muted"><?php echo translate('transfer_receipt_help'); ?></small>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="transfer_notes"><?php echo translate('notes'); ?></label>
                                            <textarea class="form-control" id="transfer_notes" name="transfer_notes" rows="3"></textarea>
                                        </div>
                                        
                                        <div class="form-group text-right">
                                            <button type="submit" class="btn btn-primary btn-lg">
                                                <?php echo translate('confirm_bank_transfer'); ?>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="payment-security">
                            <h4><?php echo translate('secure_payment'); ?></h4>
                            <p><?php echo translate('secure_payment_description'); ?></p>
                            <div class="security-icons">
                                <i class="fas fa-lock fa-2x"></i>
                                <i class="fab fa-cc-visa fa-2x"></i>
                                <i class="fab fa-cc-mastercard fa-2x"></i>
                                <i class="fab fa-cc-amex fa-2x"></i>
                                <i class="fab fa-cc-paypal fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// تضمين ملف التذييل
// Include footer file
include '../includes/footer.php';

/**
 * معالجة الدفع ببطاقة الائتمان
 * Process credit card payment
 * 
 * @param int $orderId معرف الطلب
 * @param array $data بيانات الدفع
 * @return array نتيجة العملية
 */
function processCreditCardPayment($orderId, $data) {
    try {
        // في بيئة الإنتاج، يجب استخدام بوابة دفع حقيقية
        // In production, a real payment gateway should be used
        
        // محاكاة معالجة الدفع
        // Simulate payment processing
        sleep(1);
        
        // إنشاء كائن الدفع
        // Create payment object
        $payment = new Payment();
        
        // إنشاء سجل الدفع
        // Create payment record
        $paymentData = [
            'order_id' => $orderId,
            'amount' => getOrderTotal($orderId),
            'payment_method' => 'credit_card',
            'payment_details' => json_encode([
                'card_number' => maskCardNumber($data['card_number']),
                'card_holder' => $data['card_holder'],
                'expiry_date' => $data['expiry_date']
            ]),
            'transaction_id' => generateTransactionId(),
            'status' => 'completed'
        ];
        
        $paymentId = $payment->createPayment($paymentData);
        
        if ($paymentId) {
            return [
                'success' => true,
                'message' => translate('payment_successful'),
                'payment_id' => $paymentId
            ];
        } else {
            return [
                'success' => false,
                'message' => translate('payment_failed')
            ];
        }
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

/**
 * معالجة الدفع عبر باي بال
 * Process PayPal payment
 * 
 * @param int $orderId معرف الطلب
 * @return array نتيجة العملية
 */
function processPayPalPayment($orderId) {
    try {
        // في بيئة الإنتاج، يجب استخدام واجهة برمجة تطبيقات باي بال
        // In production, PayPal API should be used
        
        // إنشاء عنوان URL للتحويل إلى باي بال
        // Create redirect URL to PayPal
        $redirectUrl = 'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=' . generatePayPalToken();
        
        return [
            'success' => true,
            'message' => translate('redirecting_to_paypal'),
            'redirect_url' => $redirectUrl
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

/**
 * معالجة الدفع عبر التحويل البنكي
 * Process bank transfer payment
 * 
 * @param int $orderId معرف الطلب
 * @param array $data بيانات التحويل
 * @return array نتيجة العملية
 */
function processBankTransferPayment($orderId, $data) {
    try {
        // التحقق من البيانات المطلوبة
        // Validate required data
        if (empty($data['transfer_date']) || empty($data['transfer_amount']) || empty($data['transfer_reference'])) {
            return [
                'success' => false,
                'message' => translate('missing_required_fields')
            ];
        }
        
        // معالجة إيصال التحويل إذا تم تحميله
        // Process transfer receipt if uploaded
        $receiptPath = '';
        if (isset($_FILES['transfer_receipt']) && $_FILES['transfer_receipt']['error'] === UPLOAD_ERR_OK) {
            $receiptPath = uploadTransferReceipt($_FILES['transfer_receipt']);
        }
        
        // إنشاء كائن الدفع
        // Create payment object
        $payment = new Payment();
        
        // إنشاء سجل الدفع
        // Create payment record
        $paymentData = [
            'order_id' => $orderId,
            'amount' => $data['transfer_amount'],
            'payment_method' => 'bank_transfer',
            'payment_details' => json_encode([
                'transfer_date' => $data['transfer_date'],
                'transfer_reference' => $data['transfer_reference'],
                'transfer_notes' => $data['transfer_notes'] ?? '',
                'transfer_receipt' => $receiptPath
            ]),
            'transaction_id' => $data['transfer_reference'],
            'status' => 'pending'
        ];
        
        $paymentId = $payment->createPayment($paymentData);
        
        if ($paymentId) {
            return [
                'success' => true,
                'message' => translate('bank_transfer_confirmed'),
                'payment_id' => $paymentId
            ];
        } else {
            return [
                'success' => false,
                'message' => translate('bank_transfer_confirmation_failed')
            ];
        }
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

/**
 * تحميل إيصال التحويل
 * Upload transfer receipt
 * 
 * @param array $file ملف الإيصال
 * @return string|false مسار الإيصال أو false في حالة الفشل
 */
function uploadTransferReceipt($file) {
    // التحقق من نوع الملف
    // Check file type
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
    if (!in_array($file['type'], $allowedTypes)) {
        return false;
    }
    
    // إنشاء اسم فريد للملف
    // Create unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('receipt_') . '.' . $extension;
    
    // مسار الحفظ
    // Save path
    $uploadDir = '../assets/uploads/receipts/';
    
    // إنشاء المجلد إذا لم يكن موجودًا
    // Create directory if not exists
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $uploadPath = $uploadDir . $filename;
    
    // نقل الملف
    // Move file
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        return '/assets/uploads/receipts/' . $filename;
    }
    
    return false;
}

/**
 * إخفاء رقم البطاقة
 * Mask card number
 * 
 * @param string $cardNumber رقم البطاقة
 * @return string رقم البطاقة المخفي
 */
function maskCardNumber($cardNumber) {
    // إزالة المسافات
    // Remove spaces
    $cardNumber = str_replace(' ', '', $cardNumber);
    
    // الحصول على الأرقام الأربعة الأخيرة
    // Get last four digits
    $lastFour = substr($cardNumber, -4);
    
    // إنشاء رقم البطاقة المخفي
    // Create masked card number
    $maskedNumber = str_repeat('*', strlen($cardNumber) - 4) . $lastFour;
    
    return $maskedNumber;
}

/**
 * إنشاء معرف المعاملة
 * Generate transaction ID
 * 
 * @return string معرف المعاملة
 */
function generateTransactionId() {
    return 'TXN' . time() . rand(1000, 9999);
}

/**
 * إنشاء رمز باي بال
 * Generate PayPal token
 * 
 * @return string رمز باي بال
 */
function generatePayPalToken() {
    return 'EC-' . strtoupper(bin2hex(random_bytes(16)));
}

/**
 * الحصول على إجمالي الطلب
 * Get order total
 * 
 * @param int $orderId معرف الطلب
 * @return float إجمالي الطلب
 */
function getOrderTotal($orderId) {
    // إنشاء كائن الطلب
    // Create order object
    $order = new Order();
    
    // الحصول على بيانات الطلب
    // Get order data
    $orderData = $order->getOrderById($orderId);
    
    if ($orderData) {
        return $orderData['total_amount'];
    }
    
    return 0;
}

/**
 * إنشاء فاتورة
 * Create invoice
 * 
 * @param int $orderId معرف الطلب
 * @return int|false معرف الفاتورة أو false في حالة الفشل
 */
function createInvoice($orderId) {
    try {
        // إنشاء كائن الطلب
        // Create order object
        $order = new Order();
        
        // الحصول على بيانات الطلب
        // Get order data
        $orderData = $order->getOrderById($orderId);
        
        if (!$orderData) {
            return false;
        }
        
        // إنشاء كائن الفاتورة
        // Create invoice object
        $invoice = new Invoice();
        
        // إنشاء الفاتورة
        // Create invoice
        $invoiceData = [
            'order_id' => $orderId,
            'user_id' => $orderData['user_id'],
            'amount' => $orderData['total_amount'],
            'status' => 'paid',
            'invoice_number' => generateInvoiceNumber(),
            'invoice_date' => date('Y-m-d')
        ];
        
        return $invoice->createInvoice($invoiceData);
    } catch (Exception $e) {
        return false;
    }
}

/**
 * إنشاء رقم الفاتورة
 * Generate invoice number
 * 
 * @return string رقم الفاتورة
 */
function generateInvoiceNumber() {
    return 'INV-' . date('Ymd') . '-' . rand(1000, 9999);
}

/**
 * تنسيق العملة
 * Format currency
 * 
 * @param float $amount المبلغ
 * @return string المبلغ المنسق
 */
function formatCurrency($amount) {
    return CURRENCY_SYMBOL . number_format($amount, 2);
}

/**
 * تنسيق التاريخ
 * Format date
 * 
 * @param string $date التاريخ
 * @return string التاريخ المنسق
 */
function formatDate($date) {
    return date('Y-m-d', strtotime($date));
}
?>

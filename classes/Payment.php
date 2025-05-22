<?php
/**
 * فئة الدفع
 * Payment class
 */

class Payment {
    private $db;

    /**
     * إنشاء كائن الدفع
     * Create payment object
     */
    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * إنشاء دفعة جديدة
     * Create new payment
     */
    public function create($orderId, $userId, $paymentData) {
        // التحقق من وجود الطلب
        // Check if order exists
        $order = new Order();
        $orderData = $order->getById($orderId);
        
        if (!$orderData) {
            throw new Exception(translate('order_not_found'));
        }

        // إضافة بيانات الدفع
        // Add payment data
        $paymentData['order_id'] = $orderId;
        $paymentData['user_id'] = $userId;
        $paymentData['created_at'] = date('Y-m-d H:i:s');

        // إدراج الدفعة في قاعدة البيانات
        // Insert payment into database
        $paymentId = $this->db->insert('payments', $paymentData);

        // تحديث حالة الدفع في الطلب
        // Update payment status in order
        $order->updatePaymentStatus($orderId, $paymentData['status']);

        // إنشاء فاتورة إذا كانت الدفعة مكتملة
        // Create invoice if payment is completed
        if ($paymentData['status'] === PAYMENT_STATUS_COMPLETED) {
            $invoice = new Invoice();
            $invoice->create($orderId, $userId);
        }

        // تسجيل نشاط إنشاء الدفعة
        // Log payment creation activity
        logActivity($userId, 'create_payment', 'Created payment for order: ' . $orderData['order_number']);

        return $paymentId;
    }

    /**
     * تحديث حالة الدفع
     * Update payment status
     */
    public function updateStatus($paymentId, $status) {
        // التحقق من وجود الدفعة
        // Check if payment exists
        $payment = $this->getById($paymentId);
        
        if (!$payment) {
            throw new Exception(translate('payment_not_found'));
        }

        // تحديث حالة الدفع
        // Update payment status
        $result = $this->db->update('payments', ['status' => $status, 'updated_at' => date('Y-m-d H:i:s')], 'id = :id', [':id' => $paymentId]);

        // تحديث حالة الدفع في الطلب
        // Update payment status in order
        $order = new Order();
        $order->updatePaymentStatus($payment['order_id'], $status);

        // إنشاء فاتورة إذا كانت الدفعة مكتملة
        // Create invoice if payment is completed
        if ($status === PAYMENT_STATUS_COMPLETED) {
            $invoice = new Invoice();
            $invoice->create($payment['order_id'], $payment['user_id']);
        }

        // تسجيل نشاط تحديث حالة الدفع
        // Log payment status update activity
        logActivity($_SESSION['user_id'], 'update_payment_status', 'Updated payment status to: ' . $status);

        return $result;
    }

    /**
     * الحصول على دفعة بواسطة المعرف
     * Get payment by ID
     */
    public function getById($paymentId) {
        $payment = $this->db->getRow("SELECT * FROM payments WHERE id = :id", [':id' => $paymentId]);
        
        if (!$payment) {
            return false;
        }

        // الحصول على بيانات الطلب
        // Get order data
        $order = new Order();
        $payment['order'] = $order->getById($payment['order_id']);

        // الحصول على بيانات المستخدم
        // Get user data
        if ($payment['user_id']) {
            $user = new User();
            $payment['user'] = $user->getById($payment['user_id']);
        } else {
            $payment['user'] = null;
        }

        return $payment;
    }

    /**
     * الحصول على دفعة بواسطة رقم المعاملة
     * Get payment by transaction ID
     */
    public function getByTransactionId($transactionId) {
        $payment = $this->db->getRow("SELECT * FROM payments WHERE transaction_id = :transaction_id", [':transaction_id' => $transactionId]);
        
        if (!$payment) {
            return false;
        }

        // الحصول على بيانات الطلب
        // Get order data
        $order = new Order();
        $payment['order'] = $order->getById($payment['order_id']);

        // الحصول على بيانات المستخدم
        // Get user data
        if ($payment['user_id']) {
            $user = new User();
            $payment['user'] = $user->getById($payment['user_id']);
        } else {
            $payment['user'] = null;
        }

        return $payment;
    }

    /**
     * الحصول على دفعات الطلب
     * Get order payments
     */
    public function getOrderPayments($orderId) {
        $payments = $this->db->getRows("SELECT * FROM payments WHERE order_id = :order_id ORDER BY created_at DESC", [':order_id' => $orderId]);
        
        // الحصول على بيانات المستخدمين
        // Get users data
        foreach ($payments as &$payment) {
            if ($payment['user_id']) {
                $user = new User();
                $payment['user'] = $user->getById($payment['user_id']);
            } else {
                $payment['user'] = null;
            }
        }

        return $payments;
    }

    /**
     * الحصول على دفعات المستخدم
     * Get user payments
     */
    public function getUserPayments($userId, $page = 1, $perPage = ITEMS_PER_PAGE) {
        $where = "user_id = :user_id";
        $params = [':user_id' => $userId];

        $result = $this->db->getPaginated('payments', $page, $perPage, $where, $params, 'created_at DESC');

        // الحصول على بيانات الطلبات
        // Get orders data
        foreach ($result['data'] as &$payment) {
            $order = new Order();
            $payment['order'] = $order->getById($payment['order_id']);
        }

        return $result;
    }

    /**
     * الحصول على جميع الدفعات
     * Get all payments
     */
    public function getAllPayments($page = 1, $perPage = ITEMS_PER_PAGE, $status = null, $paymentMethod = null) {
        $where = '';
        $params = [];

        if ($status !== null) {
            $where = "status = :status";
            $params[':status'] = $status;
        }

        if ($paymentMethod !== null) {
            if (!empty($where)) {
                $where .= " AND ";
            }
            $where .= "payment_method = :payment_method";
            $params[':payment_method'] = $paymentMethod;
        }

        $result = $this->db->getPaginated('payments', $page, $perPage, $where, $params, 'created_at DESC');

        // الحصول على بيانات الطلبات والمستخدمين
        // Get orders and users data
        foreach ($result['data'] as &$payment) {
            $order = new Order();
            $payment['order'] = $order->getById($payment['order_id']);
            
            if ($payment['user_id']) {
                $user = new User();
                $payment['user'] = $user->getById($payment['user_id']);
            } else {
                $payment['user'] = null;
            }
        }

        return $result;
    }

    /**
     * البحث عن الدفعات
     * Search payments
     */
    public function searchPayments($query, $page = 1, $perPage = ITEMS_PER_PAGE) {
        $where = "transaction_id LIKE :query";
        $params = [':query' => "%{$query}%"];

        $result = $this->db->getPaginated('payments', $page, $perPage, $where, $params, 'created_at DESC');

        // الحصول على بيانات الطلبات والمستخدمين
        // Get orders and users data
        foreach ($result['data'] as &$payment) {
            $order = new Order();
            $payment['order'] = $order->getById($payment['order_id']);
            
            if ($payment['user_id']) {
                $user = new User();
                $payment['user'] = $user->getById($payment['user_id']);
            } else {
                $payment['user'] = null;
            }
        }

        return $result;
    }

    /**
     * معالجة الدفع باستخدام Stripe
     * Process payment using Stripe
     */
    public function processStripePayment($orderId, $userId, $token) {
        // التحقق من وجود الطلب
        // Check if order exists
        $order = new Order();
        $orderData = $order->getById($orderId);
        
        if (!$orderData) {
            throw new Exception(translate('order_not_found'));
        }

        try {
            // تهيئة Stripe
            // Initialize Stripe
            \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

            // إنشاء العميل
            // Create customer
            $user = new User();
            $userData = $user->getById($userId);
            
            $customer = \Stripe\Customer::create([
                'email' => $userData['email'],
                'source' => $token
            ]);

            // إنشاء الدفع
            // Create charge
            $charge = \Stripe\Charge::create([
                'customer' => $customer->id,
                'amount' => $orderData['total_amount'] * 100, // Stripe يتعامل بالسنت
                'currency' => CURRENCY,
                'description' => 'Order #' . $orderData['order_number']
            ]);

            // إنشاء سجل الدفع
            // Create payment record
            $paymentData = [
                'transaction_id' => $charge->id,
                'payment_method' => PAYMENT_METHOD_STRIPE,
                'amount' => $orderData['total_amount'],
                'currency' => CURRENCY,
                'status' => PAYMENT_STATUS_COMPLETED,
                'payment_data' => json_encode($charge)
            ];

            return $this->create($orderId, $userId, $paymentData);
        } catch (\Stripe\Exception\CardException $e) {
            // خطأ في البطاقة
            // Card error
            throw new Exception($e->getMessage());
        } catch (\Exception $e) {
            // خطأ عام
            // General error
            throw new Exception($e->getMessage());
        }
    }

    /**
     * معالجة الدفع باستخدام PayPal
     * Process payment using PayPal
     */
    public function processPayPalPayment($orderId, $userId, $paymentId, $payerId) {
        // التحقق من وجود الطلب
        // Check if order exists
        $order = new Order();
        $orderData = $order->getById($orderId);
        
        if (!$orderData) {
            throw new Exception(translate('order_not_found'));
        }

        try {
            // تهيئة PayPal
            // Initialize PayPal
            $apiContext = new \PayPal\Rest\ApiContext(
                new \PayPal\Auth\OAuthTokenCredential(
                    PAYPAL_CLIENT_ID,
                    PAYPAL_SECRET
                )
            );

            // تنفيذ الدفع
            // Execute payment
            $payment = \PayPal\Api\Payment::get($paymentId, $apiContext);
            
            $execution = new \PayPal\Api\PaymentExecution();
            $execution->setPayerId($payerId);
            
            $result = $payment->execute($execution, $apiContext);

            // إنشاء سجل الدفع
            // Create payment record
            $paymentData = [
                'transaction_id' => $paymentId,
                'payment_method' => PAYMENT_METHOD_PAYPAL,
                'amount' => $orderData['total_amount'],
                'currency' => CURRENCY,
                'status' => PAYMENT_STATUS_COMPLETED,
                'payment_data' => json_encode($result)
            ];

            return $this->create($orderId, $userId, $paymentData);
        } catch (\Exception $e) {
            // خطأ عام
            // General error
            throw new Exception($e->getMessage());
        }
    }

    /**
     * إنشاء دفع بتحويل بنكي
     * Create bank transfer payment
     */
    public function createBankTransferPayment($orderId, $userId, $reference) {
        // التحقق من وجود الطلب
        // Check if order exists
        $order = new Order();
        $orderData = $order->getById($orderId);
        
        if (!$orderData) {
            throw new Exception(translate('order_not_found'));
        }

        // إنشاء سجل الدفع
        // Create payment record
        $paymentData = [
            'transaction_id' => 'BT-' . time() . '-' . $orderId,
            'payment_method' => PAYMENT_METHOD_BANK_TRANSFER,
            'amount' => $orderData['total_amount'],
            'currency' => CURRENCY,
            'status' => PAYMENT_STATUS_PENDING,
            'payment_data' => json_encode(['reference' => $reference])
        ];

        return $this->create($orderId, $userId, $paymentData);
    }

    /**
     * الحصول على إحصائيات الدفع
     * Get payment statistics
     */
    public function getPaymentStats() {
        $stats = [
            'total_payments' => $this->db->count('payments'),
            'completed_payments' => $this->db->count('payments', 'status = :status', [':status' => PAYMENT_STATUS_COMPLETED]),
            'pending_payments' => $this->db->count('payments', 'status = :status', [':status' => PAYMENT_STATUS_PENDING]),
            'failed_payments' => $this->db->count('payments', 'status = :status', [':status' => PAYMENT_STATUS_FAILED]),
            'total_revenue' => $this->db->getValue("SELECT SUM(amount) FROM payments WHERE status = :status", [':status' => PAYMENT_STATUS_COMPLETED]),
            'stripe_revenue' => $this->db->getValue("SELECT SUM(amount) FROM payments WHERE payment_method = :method AND status = :status", [':method' => PAYMENT_METHOD_STRIPE, ':status' => PAYMENT_STATUS_COMPLETED]),
            'paypal_revenue' => $this->db->getValue("SELECT SUM(amount) FROM payments WHERE payment_method = :method AND status = :status", [':method' => PAYMENT_METHOD_PAYPAL, ':status' => PAYMENT_STATUS_COMPLETED]),
            'bank_transfer_revenue' => $this->db->getValue("SELECT SUM(amount) FROM payments WHERE payment_method = :method AND status = :status", [':method' => PAYMENT_METHOD_BANK_TRANSFER, ':status' => PAYMENT_STATUS_COMPLETED])
        ];

        return $stats;
    }

    /**
     * الحصول على إحصائيات الدفع حسب الفترة
     * Get payment statistics by period
     */
    public function getPaymentStatsByPeriod($period = 'monthly') {
        $stats = [];
        
        switch ($period) {
            case 'daily':
                // إحصائيات يومية (آخر 30 يوم)
                // Daily statistics (last 30 days)
                for ($i = 29; $i >= 0; $i--) {
                    $date = date('Y-m-d', strtotime("-$i days"));
                    $nextDate = date('Y-m-d', strtotime("-" . ($i - 1) . " days"));
                    
                    $count = $this->db->getValue("SELECT COUNT(*) FROM payments WHERE DATE(created_at) = :date AND status = :status", [':date' => $date, ':status' => PAYMENT_STATUS_COMPLETED]);
                    $revenue = $this->db->getValue("SELECT SUM(amount) FROM payments WHERE DATE(created_at) = :date AND status = :status", [':date' => $date, ':status' => PAYMENT_STATUS_COMPLETED]);
                    
                    $stats[] = [
                        'date' => $date,
                        'count' => (int) $count,
                        'revenue' => (float) ($revenue ?: 0)
                    ];
                }
                break;
                
            case 'weekly':
                // إحصائيات أسبوعية (آخر 12 أسبوع)
                // Weekly statistics (last 12 weeks)
                for ($i = 11; $i >= 0; $i--) {
                    $startDate = date('Y-m-d', strtotime("-$i weeks"));
                    $endDate = date('Y-m-d', strtotime("-" . ($i - 1) . " weeks"));
                    
                    $count = $this->db->getValue("SELECT COUNT(*) FROM payments WHERE DATE(created_at) >= :start_date AND DATE(created_at) < :end_date AND status = :status", [':start_date' => $startDate, ':end_date' => $endDate, ':status' => PAYMENT_STATUS_COMPLETED]);
                    $revenue = $this->db->getValue("SELECT SUM(amount) FROM payments WHERE DATE(created_at) >= :start_date AND DATE(created_at) < :end_date AND status = :status", [':start_date' => $startDate, ':end_date' => $endDate, ':status' => PAYMENT_STATUS_COMPLETED]);
                    
                    $stats[] = [
                        'date' => $startDate,
                        'count' => (int) $count,
                        'revenue' => (float) ($revenue ?: 0)
                    ];
                }
                break;
                
            case 'monthly':
            default:
                // إحصائيات شهرية (آخر 12 شهر)
                // Monthly statistics (last 12 months)
                for ($i = 11; $i >= 0; $i--) {
                    $month = date('Y-m', strtotime("-$i months"));
                    $startDate = $month . '-01';
                    $endDate = date('Y-m-d', strtotime($startDate . ' +1 month'));
                    
                    $count = $this->db->getValue("SELECT COUNT(*) FROM payments WHERE DATE(created_at) >= :start_date AND DATE(created_at) < :end_date AND status = :status", [':start_date' => $startDate, ':end_date' => $endDate, ':status' => PAYMENT_STATUS_COMPLETED]);
                    $revenue = $this->db->getValue("SELECT SUM(amount) FROM payments WHERE DATE(created_at) >= :start_date AND DATE(created_at) < :end_date AND status = :status", [':start_date' => $startDate, ':end_date' => $endDate, ':status' => PAYMENT_STATUS_COMPLETED]);
                    
                    $stats[] = [
                        'date' => $month,
                        'count' => (int) $count,
                        'revenue' => (float) ($revenue ?: 0)
                    ];
                }
                break;
        }
        
        return $stats;
    }
}

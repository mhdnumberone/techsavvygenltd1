<?php
/**
 * فئة الطلب
 * Order class
 */

class Order {
    private $db;

    /**
     * إنشاء كائن الطلب
     * Create order object
     */
    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * إنشاء طلب جديد
     * Create new order
     */
    public function create($userId, $items, $orderData) {
        // التحقق من وجود عناصر في الطلب
        // Check if there are items in the order
        if (empty($items)) {
            throw new Exception(translate('no_items_in_order'));
        }

        // بدء المعاملة
        // Begin transaction
        $this->db->beginTransaction();

        try {
            // إنشاء رقم الطلب
            // Create order number
            $orderData['order_number'] = $this->generateOrderNumber();
            $orderData['user_id'] = $userId;
            $orderData['created_at'] = date('Y-m-d H:i:s');
            $orderData['status'] = ORDER_STATUS_PENDING;
            $orderData['payment_status'] = PAYMENT_STATUS_PENDING;

            // حساب المبلغ الإجمالي
            // Calculate total amount
            $totalAmount = 0;
            foreach ($items as $item) {
                $totalAmount += $item['price'] * $item['quantity'];
            }
            $orderData['total_amount'] = $totalAmount;

            // إدراج الطلب في قاعدة البيانات
            // Insert order into database
            $orderId = $this->db->insert('orders', $orderData);

            // إدراج عناصر الطلب
            // Insert order items
            foreach ($items as $item) {
                $itemData = [
                    'order_id' => $orderId,
                    'item_type' => $item['type'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total' => $item['price'] * $item['quantity'],
                    'created_at' => date('Y-m-d H:i:s')
                ];

                // تعيين المعرف المناسب حسب نوع العنصر
                // Set appropriate ID based on item type
                switch ($item['type']) {
                    case ITEM_TYPE_PRODUCT:
                        $itemData['product_id'] = $item['id'];
                        break;
                    case ITEM_TYPE_SERVICE:
                        $itemData['service_id'] = $item['id'];
                        break;
                    case ITEM_TYPE_CUSTOM_SERVICE:
                        $itemData['custom_service_id'] = $item['id'];
                        break;
                }

                $this->db->insert('order_items', $itemData);

                // تحديث المخزون إذا كان العنصر منتجًا
                // Update stock if item is a product
                if ($item['type'] === ITEM_TYPE_PRODUCT) {
                    $product = new Product();
                    $product->updateStock($item['id'], $item['quantity'], 'subtract');
                }

                // تحديث حالة الخدمة المخصصة إذا كان العنصر خدمة مخصصة
                // Update custom service status if item is a custom service
                if ($item['type'] === ITEM_TYPE_CUSTOM_SERVICE) {
                    $service = new Service();
                    $service->updateCustomServiceStatus($item['id'], CUSTOM_SERVICE_STATUS_PAID);
                }
            }

            // تأكيد المعاملة
            // Commit transaction
            $this->db->commit();

            // تسجيل نشاط إنشاء الطلب
            // Log order creation activity
            logActivity($userId, 'create_order', 'Created new order: ' . $orderData['order_number']);

            return $orderId;
        } catch (Exception $e) {
            // التراجع عن المعاملة في حالة حدوث خطأ
            // Rollback transaction in case of error
            $this->db->rollback();
            throw $e;
        }
    }

    /**
     * تحديث حالة الطلب
     * Update order status
     */
    public function updateStatus($orderId, $status) {
        // التحقق من وجود الطلب
        // Check if order exists
        $order = $this->getById($orderId);
        
        if (!$order) {
            throw new Exception(translate('order_not_found'));
        }

        // تحديث حالة الطلب
        // Update order status
        $result = $this->db->update('orders', ['status' => $status], 'id = :id', [':id' => $orderId]);

        // تسجيل نشاط تحديث حالة الطلب
        // Log order status update activity
        logActivity($_SESSION['user_id'], 'update_order_status', 'Updated order status: ' . $order['order_number'] . ' to ' . $status);

        return $result;
    }

    /**
     * تحديث حالة الدفع
     * Update payment status
     */
    public function updatePaymentStatus($orderId, $paymentStatus) {
        // التحقق من وجود الطلب
        // Check if order exists
        $order = $this->getById($orderId);
        
        if (!$order) {
            throw new Exception(translate('order_not_found'));
        }

        // تحديث حالة الدفع
        // Update payment status
        $result = $this->db->update('orders', ['payment_status' => $paymentStatus], 'id = :id', [':id' => $orderId]);

        // تسجيل نشاط تحديث حالة الدفع
        // Log payment status update activity
        logActivity($_SESSION['user_id'], 'update_payment_status', 'Updated payment status: ' . $order['order_number'] . ' to ' . $paymentStatus);

        return $result;
    }

    /**
     * الحصول على طلب بواسطة المعرف
     * Get order by ID
     */
    public function getById($orderId) {
        $order = $this->db->getRow("SELECT * FROM orders WHERE id = :id", [':id' => $orderId]);
        
        if (!$order) {
            return false;
        }

        // الحصول على عناصر الطلب
        // Get order items
        $order['items'] = $this->getOrderItems($orderId);

        // الحصول على بيانات المستخدم
        // Get user data
        if ($order['user_id']) {
            $user = new User();
            $order['user'] = $user->getById($order['user_id']);
        } else {
            $order['user'] = null;
        }

        return $order;
    }

    /**
     * الحصول على طلب بواسطة رقم الطلب
     * Get order by order number
     */
    public function getByOrderNumber($orderNumber) {
        $order = $this->db->getRow("SELECT * FROM orders WHERE order_number = :order_number", [':order_number' => $orderNumber]);
        
        if (!$order) {
            return false;
        }

        // الحصول على عناصر الطلب
        // Get order items
        $order['items'] = $this->getOrderItems($order['id']);

        // الحصول على بيانات المستخدم
        // Get user data
        if ($order['user_id']) {
            $user = new User();
            $order['user'] = $user->getById($order['user_id']);
        } else {
            $order['user'] = null;
        }

        return $order;
    }

    /**
     * الحصول على عناصر الطلب
     * Get order items
     */
    public function getOrderItems($orderId) {
        $items = $this->db->getRows("SELECT * FROM order_items WHERE order_id = :order_id", [':order_id' => $orderId]);
        
        // الحصول على بيانات العناصر
        // Get items data
        foreach ($items as &$item) {
            switch ($item['item_type']) {
                case ITEM_TYPE_PRODUCT:
                    $product = new Product();
                    $item['product'] = $product->getById($item['product_id']);
                    break;
                case ITEM_TYPE_SERVICE:
                    $service = new Service();
                    $item['service'] = $service->getById($item['service_id']);
                    break;
                case ITEM_TYPE_CUSTOM_SERVICE:
                    $service = new Service();
                    $item['custom_service'] = $service->getCustomServiceByLink($item['custom_service_id']);
                    break;
            }
        }

        return $items;
    }

    /**
     * الحصول على طلبات المستخدم
     * Get user orders
     */
    public function getUserOrders($userId, $page = 1, $perPage = ITEMS_PER_PAGE) {
        $where = "user_id = :user_id";
        $params = [':user_id' => $userId];

        $result = $this->db->getPaginated('orders', $page, $perPage, $where, $params, 'created_at DESC');

        // الحصول على عناصر الطلبات
        // Get orders items
        foreach ($result['data'] as &$order) {
            $order['items'] = $this->getOrderItems($order['id']);
        }

        return $result;
    }

    /**
     * الحصول على جميع الطلبات
     * Get all orders
     */
    public function getAllOrders($page = 1, $perPage = ITEMS_PER_PAGE, $status = null, $paymentStatus = null) {
        $where = '';
        $params = [];

        if ($status !== null) {
            $where = "status = :status";
            $params[':status'] = $status;
        }

        if ($paymentStatus !== null) {
            if (!empty($where)) {
                $where .= " AND ";
            }
            $where .= "payment_status = :payment_status";
            $params[':payment_status'] = $paymentStatus;
        }

        $result = $this->db->getPaginated('orders', $page, $perPage, $where, $params, 'created_at DESC');

        // الحصول على عناصر الطلبات وبيانات المستخدمين
        // Get orders items and users data
        foreach ($result['data'] as &$order) {
            $order['items'] = $this->getOrderItems($order['id']);
            
            if ($order['user_id']) {
                $user = new User();
                $order['user'] = $user->getById($order['user_id']);
            } else {
                $order['user'] = null;
            }
        }

        return $result;
    }

    /**
     * البحث عن الطلبات
     * Search orders
     */
    public function searchOrders($query, $page = 1, $perPage = ITEMS_PER_PAGE) {
        $where = "order_number LIKE :query OR notes LIKE :query";
        $params = [':query' => "%{$query}%"];

        $result = $this->db->getPaginated('orders', $page, $perPage, $where, $params, 'created_at DESC');

        // الحصول على عناصر الطلبات وبيانات المستخدمين
        // Get orders items and users data
        foreach ($result['data'] as &$order) {
            $order['items'] = $this->getOrderItems($order['id']);
            
            if ($order['user_id']) {
                $user = new User();
                $order['user'] = $user->getById($order['user_id']);
            } else {
                $order['user'] = null;
            }
        }

        return $result;
    }

    /**
     * حذف طلب
     * Delete order
     */
    public function delete($orderId) {
        // التحقق من وجود الطلب
        // Check if order exists
        $order = $this->getById($orderId);
        
        if (!$order) {
            throw new Exception(translate('order_not_found'));
        }

        // بدء المعاملة
        // Begin transaction
        $this->db->beginTransaction();

        try {
            // استعادة المخزون للمنتجات
            // Restore stock for products
            foreach ($order['items'] as $item) {
                if ($item['item_type'] === ITEM_TYPE_PRODUCT && isset($item['product'])) {
                    $product = new Product();
                    $product->updateStock($item['product_id'], $item['quantity'], 'add');
                }
            }

            // حذف عناصر الطلب
            // Delete order items
            $this->db->delete('order_items', 'order_id = :order_id', [':order_id' => $orderId]);

            // حذف الطلب
            // Delete order
            $this->db->delete('orders', 'id = :id', [':id' => $orderId]);

            // تأكيد المعاملة
            // Commit transaction
            $this->db->commit();

            // تسجيل نشاط حذف الطلب
            // Log order deletion activity
            logActivity($_SESSION['user_id'], 'delete_order', 'Deleted order: ' . $order['order_number']);

            return true;
        } catch (Exception $e) {
            // التراجع عن المعاملة في حالة حدوث خطأ
            // Rollback transaction in case of error
            $this->db->rollback();
            throw $e;
        }
    }

    /**
     * توليد رقم طلب فريد
     * Generate unique order number
     */
    private function generateOrderNumber() {
        $prefix = 'ORD';
        $timestamp = time();
        $random = mt_rand(1000, 9999);
        return $prefix . $timestamp . $random;
    }

    /**
     * الحصول على إحصائيات الطلبات
     * Get orders statistics
     */
    public function getOrdersStats() {
        $stats = [
            'total_orders' => $this->db->count('orders'),
            'pending_orders' => $this->db->count('orders', 'status = :status', [':status' => ORDER_STATUS_PENDING]),
            'processing_orders' => $this->db->count('orders', 'status = :status', [':status' => ORDER_STATUS_PROCESSING]),
            'completed_orders' => $this->db->count('orders', 'status = :status', [':status' => ORDER_STATUS_COMPLETED]),
            'cancelled_orders' => $this->db->count('orders', 'status = :status', [':status' => ORDER_STATUS_CANCELLED]),
            'total_revenue' => $this->db->getValue("SELECT SUM(total_amount) FROM orders WHERE payment_status = :status", [':status' => PAYMENT_STATUS_COMPLETED]),
            'pending_revenue' => $this->db->getValue("SELECT SUM(total_amount) FROM orders WHERE payment_status = :status", [':status' => PAYMENT_STATUS_PENDING])
        ];

        return $stats;
    }

    /**
     * الحصول على إحصائيات الطلبات حسب الفترة
     * Get orders statistics by period
     */
    public function getOrdersStatsByPeriod($period = 'monthly') {
        $stats = [];
        
        switch ($period) {
            case 'daily':
                // إحصائيات يومية (آخر 30 يوم)
                // Daily statistics (last 30 days)
                for ($i = 29; $i >= 0; $i--) {
                    $date = date('Y-m-d', strtotime("-$i days"));
                    $nextDate = date('Y-m-d', strtotime("-" . ($i - 1) . " days"));
                    
                    $count = $this->db->getValue("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = :date", [':date' => $date]);
                    $revenue = $this->db->getValue("SELECT SUM(total_amount) FROM orders WHERE DATE(created_at) = :date AND payment_status = :status", [':date' => $date, ':status' => PAYMENT_STATUS_COMPLETED]);
                    
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
                    
                    $count = $this->db->getValue("SELECT COUNT(*) FROM orders WHERE DATE(created_at) >= :start_date AND DATE(created_at) < :end_date", [':start_date' => $startDate, ':end_date' => $endDate]);
                    $revenue = $this->db->getValue("SELECT SUM(total_amount) FROM orders WHERE DATE(created_at) >= :start_date AND DATE(created_at) < :end_date AND payment_status = :status", [':start_date' => $startDate, ':end_date' => $endDate, ':status' => PAYMENT_STATUS_COMPLETED]);
                    
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
                    
                    $count = $this->db->getValue("SELECT COUNT(*) FROM orders WHERE DATE(created_at) >= :start_date AND DATE(created_at) < :end_date", [':start_date' => $startDate, ':end_date' => $endDate]);
                    $revenue = $this->db->getValue("SELECT SUM(total_amount) FROM orders WHERE DATE(created_at) >= :start_date AND DATE(created_at) < :end_date AND payment_status = :status", [':start_date' => $startDate, ':end_date' => $endDate, ':status' => PAYMENT_STATUS_COMPLETED]);
                    
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

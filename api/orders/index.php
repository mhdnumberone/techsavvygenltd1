<?php
/**
 * واجهة برمجة التطبيقات للطلبات
 * Orders API
 */

// تضمين ملفات الإعدادات والوظائف المساعدة
// Include configuration and helper files
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// التحقق من طريقة الطلب
// Check request method
$method = $_SERVER['REQUEST_METHOD'];
$requestUri = $_SERVER['REQUEST_URI'];
$uriSegments = explode('/', trim(parse_url($requestUri, PHP_URL_PATH), '/'));

// تحديد معرف الطلب إذا كان موجودًا في المسار
// Determine order ID if present in path
$orderId = null;
if (isset($uriSegments[2]) && is_numeric($uriSegments[2])) {
    $orderId = (int)$uriSegments[2];
}

// تهيئة الاستجابة
// Initialize response
header('Content-Type: application/json');
$response = [
    'status' => 'error',
    'message' => 'Invalid request',
    'data' => null
];

// معالجة الطلب بناءً على الطريقة
// Process request based on method
switch ($method) {
    case 'GET':
        if ($orderId) {
            // الحصول على طلب محدد
            // Get specific order
            getOrder($orderId);
        } else if (isset($_GET['user_id'])) {
            // الحصول على طلبات المستخدم
            // Get user orders
            getUserOrders($_GET['user_id']);
        } else {
            // الحصول على قائمة الطلبات
            // Get list of orders
            getOrders();
        }
        break;
        
    case 'POST':
        // إنشاء طلب جديد
        // Create new order
        createOrder();
        break;
        
    case 'PUT':
        // تحديث حالة الطلب
        // Update order status
        if ($orderId && isAuthenticated()) {
            updateOrderStatus($orderId);
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Unauthorized or invalid order ID';
            http_response_code(401);
        }
        break;
        
    case 'DELETE':
        // حذف طلب (عادة لا يتم حذف الطلبات)
        // Delete order (usually orders are not deleted)
        if ($orderId && isAuthenticated() && hasPermission('manage_orders')) {
            deleteOrder($orderId);
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Unauthorized or invalid order ID';
            http_response_code(401);
        }
        break;
        
    default:
        // طريقة غير مدعومة
        // Unsupported method
        $response['status'] = 'error';
        $response['message'] = 'Method not allowed';
        http_response_code(405);
        break;
}

// إرسال الاستجابة
// Send response
echo json_encode($response);
exit;

/**
 * الحصول على قائمة الطلبات
 * Get list of orders
 */
function getOrders() {
    global $response;
    
    // التحقق من الصلاحيات
    // Check permissions
    if (!isAuthenticated() || !hasPermission('view_orders')) {
        $response['status'] = 'error';
        $response['message'] = 'Unauthorized';
        http_response_code(401);
        return;
    }
    
    // الحصول على معلمات التصفح
    // Get pagination parameters
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $limit;
    
    // الحصول على معلمات الفرز
    // Get sorting parameters
    $sort = isset($_GET['sort']) ? $_GET['sort'] : 'id';
    $order = isset($_GET['order']) && strtolower($_GET['order']) === 'desc' ? 'DESC' : 'ASC';
    
    // التحقق من صحة حقل الفرز
    // Validate sort field
    $allowedSortFields = ['id', 'user_id', 'total_amount', 'status', 'created_at'];
    if (!in_array($sort, $allowedSortFields)) {
        $sort = 'id';
    }
    
    try {
        // إنشاء كائن الطلب
        // Create order object
        $order = new Order();
        
        // الحصول على الطلبات
        // Get orders
        $orders = $order->getOrders($limit, $offset, $sort, $order);
        $total = $order->getTotalOrders();
        
        // تهيئة الاستجابة
        // Prepare response
        $response['status'] = 'success';
        $response['message'] = 'Orders retrieved successfully';
        $response['data'] = $orders;
        $response['meta'] = [
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ];
        
        http_response_code(200);
    } catch (Exception $e) {
        $response['status'] = 'error';
        $response['message'] = 'Failed to retrieve orders: ' . $e->getMessage();
        http_response_code(500);
    }
}

/**
 * الحصول على طلب محدد
 * Get specific order
 * 
 * @param int $id معرف الطلب
 */
function getOrder($id) {
    global $response;
    
    // التحقق من الصلاحيات
    // Check permissions
    if (!isAuthenticated()) {
        $response['status'] = 'error';
        $response['message'] = 'Unauthorized';
        http_response_code(401);
        return;
    }
    
    try {
        // إنشاء كائن الطلب
        // Create order object
        $order = new Order();
        
        // الحصول على الطلب
        // Get order
        $orderData = $order->getOrderById($id);
        
        if ($orderData) {
            // التحقق من صلاحية الوصول
            // Check access permission
            if (getCurrentUserId() != $orderData['user_id'] && !hasPermission('view_orders')) {
                $response['status'] = 'error';
                $response['message'] = 'Unauthorized';
                http_response_code(401);
                return;
            }
            
            // الحصول على تفاصيل الطلب
            // Get order details
            $orderDetails = $order->getOrderItems($id);
            $orderData['items'] = $orderDetails;
            
            $response['status'] = 'success';
            $response['message'] = 'Order retrieved successfully';
            $response['data'] = $orderData;
            http_response_code(200);
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Order not found';
            http_response_code(404);
        }
    } catch (Exception $e) {
        $response['status'] = 'error';
        $response['message'] = 'Failed to retrieve order: ' . $e->getMessage();
        http_response_code(500);
    }
}

/**
 * الحصول على طلبات المستخدم
 * Get user orders
 * 
 * @param int $userId معرف المستخدم
 */
function getUserOrders($userId) {
    global $response;
    
    // التحقق من الصلاحيات
    // Check permissions
    if (!isAuthenticated() || (getCurrentUserId() != $userId && !hasPermission('view_orders'))) {
        $response['status'] = 'error';
        $response['message'] = 'Unauthorized';
        http_response_code(401);
        return;
    }
    
    // الحصول على معلمات التصفح
    // Get pagination parameters
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $limit;
    
    try {
        // إنشاء كائن الطلب
        // Create order object
        $order = new Order();
        
        // الحصول على طلبات المستخدم
        // Get user orders
        $orders = $order->getOrdersByUserId($userId, $limit, $offset);
        $total = $order->getTotalUserOrders($userId);
        
        $response['status'] = 'success';
        $response['message'] = 'User orders retrieved successfully';
        $response['data'] = $orders;
        $response['meta'] = [
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit),
            'user_id' => $userId
        ];
        
        http_response_code(200);
    } catch (Exception $e) {
        $response['status'] = 'error';
        $response['message'] = 'Failed to retrieve user orders: ' . $e->getMessage();
        http_response_code(500);
    }
}

/**
 * إنشاء طلب جديد
 * Create new order
 */
function createOrder() {
    global $response;
    
    // التحقق من تسجيل الدخول
    // Check login
    if (!isAuthenticated()) {
        $response['status'] = 'error';
        $response['message'] = 'Unauthorized';
        http_response_code(401);
        return;
    }
    
    // الحصول على بيانات الطلب
    // Get request data
    $data = json_decode(file_get_contents('php://input'), true);
    
    // التحقق من البيانات المطلوبة
    // Validate required data
    if (!isset($data['items']) || empty($data['items'])) {
        $response['status'] = 'error';
        $response['message'] = 'Missing required fields';
        http_response_code(400);
        return;
    }
    
    try {
        // إنشاء كائن الطلب
        // Create order object
        $order = new Order();
        
        // إعداد بيانات الطلب
        // Prepare order data
        $orderData = [
            'user_id' => getCurrentUserId(),
            'shipping_address' => $data['shipping_address'] ?? '',
            'billing_address' => $data['billing_address'] ?? '',
            'payment_method' => $data['payment_method'] ?? 'cash',
            'shipping_method' => $data['shipping_method'] ?? 'standard',
            'notes' => $data['notes'] ?? '',
            'status' => 'pending'
        ];
        
        // إنشاء الطلب
        // Create order
        $orderId = $order->createOrder($orderData, $data['items']);
        
        if ($orderId) {
            $response['status'] = 'success';
            $response['message'] = 'Order created successfully';
            $response['data'] = [
                'id' => $orderId,
                'redirect_url' => '/checkout/payment.php?order_id=' . $orderId
            ];
            http_response_code(201);
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Failed to create order';
            http_response_code(500);
        }
    } catch (Exception $e) {
        $response['status'] = 'error';
        $response['message'] = 'Failed to create order: ' . $e->getMessage();
        http_response_code(500);
    }
}

/**
 * تحديث حالة الطلب
 * Update order status
 * 
 * @param int $id معرف الطلب
 */
function updateOrderStatus($id) {
    global $response;
    
    // التحقق من الصلاحيات
    // Check permissions
    if (!hasPermission('manage_orders')) {
        $response['status'] = 'error';
        $response['message'] = 'Unauthorized';
        http_response_code(401);
        return;
    }
    
    // الحصول على بيانات الطلب
    // Get request data
    $data = json_decode(file_get_contents('php://input'), true);
    
    // التحقق من البيانات المطلوبة
    // Validate required data
    if (!isset($data['status'])) {
        $response['status'] = 'error';
        $response['message'] = 'Missing required fields';
        http_response_code(400);
        return;
    }
    
    // التحقق من صحة الحالة
    // Validate status
    $allowedStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded'];
    if (!in_array($data['status'], $allowedStatuses)) {
        $response['status'] = 'error';
        $response['message'] = 'Invalid status';
        http_response_code(400);
        return;
    }
    
    try {
        // إنشاء كائن الطلب
        // Create order object
        $order = new Order();
        
        // التحقق من وجود الطلب
        // Check if order exists
        $orderData = $order->getOrderById($id);
        
        if (!$orderData) {
            $response['status'] = 'error';
            $response['message'] = 'Order not found';
            http_response_code(404);
            return;
        }
        
        // تحديث حالة الطلب
        // Update order status
        $result = $order->updateOrderStatus($id, $data['status'], $data['notes'] ?? null);
        
        if ($result) {
            $response['status'] = 'success';
            $response['message'] = 'Order status updated successfully';
            $response['data'] = ['id' => $id, 'status' => $data['status']];
            http_response_code(200);
            
            // إرسال إشعار للمستخدم
            // Send notification to user
            sendOrderStatusNotification($id, $data['status']);
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Failed to update order status';
            http_response_code(500);
        }
    } catch (Exception $e) {
        $response['status'] = 'error';
        $response['message'] = 'Failed to update order status: ' . $e->getMessage();
        http_response_code(500);
    }
}

/**
 * حذف طلب
 * Delete order
 * 
 * @param int $id معرف الطلب
 */
function deleteOrder($id) {
    global $response;
    
    try {
        // إنشاء كائن الطلب
        // Create order object
        $order = new Order();
        
        // التحقق من وجود الطلب
        // Check if order exists
        $orderData = $order->getOrderById($id);
        
        if (!$orderData) {
            $response['status'] = 'error';
            $response['message'] = 'Order not found';
            http_response_code(404);
            return;
        }
        
        // حذف الطلب
        // Delete order
        $result = $order->deleteOrder($id);
        
        if ($result) {
            $response['status'] = 'success';
            $response['message'] = 'Order deleted successfully';
            $response['data'] = null;
            http_response_code(200);
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Failed to delete order';
            http_response_code(500);
        }
    } catch (Exception $e) {
        $response['status'] = 'error';
        $response['message'] = 'Failed to delete order: ' . $e->getMessage();
        http_response_code(500);
    }
}

/**
 * إرسال إشعار بتغيير حالة الطلب
 * Send order status notification
 * 
 * @param int $orderId معرف الطلب
 * @param string $status الحالة الجديدة
 */
function sendOrderStatusNotification($orderId, $status) {
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
        
        // إنشاء كائن المستخدم
        // Create user object
        $user = new User();
        
        // الحصول على بيانات المستخدم
        // Get user data
        $userData = $user->getUserById($orderData['user_id']);
        
        if (!$userData) {
            return false;
        }
        
        // إنشاء كائن الإشعارات
        // Create notification object
        $notification = new Notification();
        
        // تحديد نص الإشعار بناءً على الحالة
        // Determine notification text based on status
        $notificationText = '';
        switch ($status) {
            case 'processing':
                $notificationText = 'تم بدء معالجة طلبك رقم #' . $orderId;
                break;
            case 'shipped':
                $notificationText = 'تم شحن طلبك رقم #' . $orderId;
                break;
            case 'delivered':
                $notificationText = 'تم تسليم طلبك رقم #' . $orderId;
                break;
            case 'cancelled':
                $notificationText = 'تم إلغاء طلبك رقم #' . $orderId;
                break;
            case 'refunded':
                $notificationText = 'تم إرجاع المبلغ لطلبك رقم #' . $orderId;
                break;
            default:
                $notificationText = 'تم تحديث حالة طلبك رقم #' . $orderId . ' إلى ' . $status;
                break;
        }
        
        // إضافة الإشعار
        // Add notification
        $notification->addNotification([
            'user_id' => $orderData['user_id'],
            'title' => 'تحديث حالة الطلب',
            'message' => $notificationText,
            'type' => 'order',
            'reference_id' => $orderId,
            'is_read' => 0
        ]);
        
        // إرسال بريد إلكتروني (اختياري)
        // Send email (optional)
        if (!empty($userData['email'])) {
            // يمكن إضافة كود إرسال البريد الإلكتروني هنا
            // Email sending code can be added here
        }
        
        return true;
    } catch (Exception $e) {
        return false;
    }
}

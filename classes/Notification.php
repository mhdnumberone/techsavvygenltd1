<?php
/**
 * فئة الإشعارات
 * Notification class
 */

class Notification {
    private $db;

    /**
     * إنشاء كائن الإشعارات
     * Create notification object
     */
    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * إنشاء إشعار جديد
     * Create new notification
     */
    public function create($userId, $type, $message, $data = null) {
        // التحقق من وجود المستخدم
        // Check if user exists
        $user = new User();
        $userData = $user->getById($userId);
        
        if (!$userData) {
            throw new Exception(translate('user_not_found'));
        }

        // إنشاء بيانات الإشعار
        // Create notification data
        $notificationData = [
            'user_id' => $userId,
            'type' => $type,
            'message' => $message,
            'data' => $data ? json_encode($data) : null,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];

        // إدراج الإشعار في قاعدة البيانات
        // Insert notification into database
        $notificationId = $this->db->insert('notifications', $notificationData);

        // إرسال الإشعار عبر البريد الإلكتروني إذا كان مفعلاً
        // Send notification via email if enabled
        if ($userData['notification_email'] == 1) {
            $this->sendEmailNotification($userId, $type, $message, $data);
        }

        return $notificationId;
    }

    /**
     * إنشاء إشعار للمسؤولين
     * Create notification for administrators
     */
    public function createForAdmins($type, $message, $data = null) {
        // الحصول على جميع المسؤولين
        // Get all administrators
        $user = new User();
        $admins = $user->getAdmins();

        foreach ($admins as $admin) {
            $this->create($admin['id'], $type, $message, $data);
        }

        return true;
    }

    /**
     * تحديث حالة قراءة الإشعار
     * Update notification read status
     */
    public function markAsRead($notificationId) {
        // التحقق من وجود الإشعار
        // Check if notification exists
        $notification = $this->getById($notificationId);
        
        if (!$notification) {
            throw new Exception(translate('notification_not_found'));
        }

        // تحديث حالة قراءة الإشعار
        // Update notification read status
        $result = $this->db->update('notifications', ['is_read' => 1, 'read_at' => date('Y-m-d H:i:s')], 'id = :id', [':id' => $notificationId]);

        return $result;
    }

    /**
     * تحديث حالة قراءة جميع إشعارات المستخدم
     * Mark all user notifications as read
     */
    public function markAllAsRead($userId) {
        // تحديث حالة قراءة جميع الإشعارات
        // Update read status for all notifications
        $result = $this->db->update('notifications', ['is_read' => 1, 'read_at' => date('Y-m-d H:i:s')], 'user_id = :user_id AND is_read = 0', [':user_id' => $userId]);

        return $result;
    }

    /**
     * حذف إشعار
     * Delete notification
     */
    public function delete($notificationId) {
        // التحقق من وجود الإشعار
        // Check if notification exists
        $notification = $this->getById($notificationId);
        
        if (!$notification) {
            throw new Exception(translate('notification_not_found'));
        }

        // حذف الإشعار
        // Delete notification
        $result = $this->db->delete('notifications', 'id = :id', [':id' => $notificationId]);

        return $result;
    }

    /**
     * حذف جميع إشعارات المستخدم
     * Delete all user notifications
     */
    public function deleteAll($userId) {
        // حذف جميع الإشعارات
        // Delete all notifications
        $result = $this->db->delete('notifications', 'user_id = :user_id', [':user_id' => $userId]);

        return $result;
    }

    /**
     * الحصول على إشعار بواسطة المعرف
     * Get notification by ID
     */
    public function getById($notificationId) {
        $notification = $this->db->getRow("SELECT * FROM notifications WHERE id = :id", [':id' => $notificationId]);
        
        if (!$notification) {
            return false;
        }

        // تحويل البيانات من JSON إلى مصفوفة
        // Convert data from JSON to array
        if ($notification['data']) {
            $notification['data'] = json_decode($notification['data'], true);
        }

        return $notification;
    }

    /**
     * الحصول على إشعارات المستخدم
     * Get user notifications
     */
    public function getUserNotifications($userId, $page = 1, $perPage = ITEMS_PER_PAGE, $isRead = null) {
        $where = "user_id = :user_id";
        $params = [':user_id' => $userId];

        if ($isRead !== null) {
            $where .= " AND is_read = :is_read";
            $params[':is_read'] = $isRead ? 1 : 0;
        }

        $result = $this->db->getPaginated('notifications', $page, $perPage, $where, $params, 'created_at DESC');

        // تحويل البيانات من JSON إلى مصفوفة
        // Convert data from JSON to array
        foreach ($result['data'] as &$notification) {
            if ($notification['data']) {
                $notification['data'] = json_decode($notification['data'], true);
            }
        }

        return $result;
    }

    /**
     * الحصول على عدد الإشعارات غير المقروءة للمستخدم
     * Get unread notifications count for user
     */
    public function getUnreadCount($userId) {
        $count = $this->db->count('notifications', 'user_id = :user_id AND is_read = 0', [':user_id' => $userId]);

        return $count;
    }

    /**
     * إرسال إشعار عبر البريد الإلكتروني
     * Send notification via email
     */
    private function sendEmailNotification($userId, $type, $message, $data = null) {
        // الحصول على بيانات المستخدم
        // Get user data
        $user = new User();
        $userData = $user->getById($userId);
        
        if (!$userData) {
            return false;
        }

        // إعداد عنوان البريد الإلكتروني
        // Prepare email subject
        $subject = SITE_NAME_EN . ' - ' . translate('notification');

        // إعداد محتوى البريد الإلكتروني
        // Prepare email content
        $content = '<p>' . translate('hello') . ' ' . $userData['first_name'] . ',</p>';
        $content .= '<p>' . $message . '</p>';
        
        // إضافة رابط للموقع
        // Add link to website
        $content .= '<p><a href="' . SITE_URL . '">' . translate('visit_website') . '</a></p>';
        
        // إضافة تذييل البريد الإلكتروني
        // Add email footer
        $content .= '<p>' . translate('email_footer') . '</p>';

        // إرسال البريد الإلكتروني
        // Send email
        // تنفيذ إرسال البريد الإلكتروني
        // Implement email sending
        // ...

        return true;
    }

    /**
     * إنشاء إشعار لطلب جديد
     * Create notification for new order
     */
    public function createOrderNotification($orderId) {
        // الحصول على بيانات الطلب
        // Get order data
        $order = new Order();
        $orderData = $order->getById($orderId);
        
        if (!$orderData) {
            return false;
        }

        // إنشاء إشعار للمستخدم
        // Create notification for user
        $this->create(
            $orderData['user_id'],
            'new_order',
            translate('new_order_notification', ['order_number' => $orderData['order_number']]),
            ['order_id' => $orderId, 'order_number' => $orderData['order_number']]
        );

        // إنشاء إشعار للمسؤولين
        // Create notification for administrators
        $this->createForAdmins(
            'new_order_admin',
            translate('new_order_admin_notification', ['order_number' => $orderData['order_number']]),
            ['order_id' => $orderId, 'order_number' => $orderData['order_number']]
        );

        return true;
    }

    /**
     * إنشاء إشعار لتحديث حالة الطلب
     * Create notification for order status update
     */
    public function createOrderStatusNotification($orderId, $status) {
        // الحصول على بيانات الطلب
        // Get order data
        $order = new Order();
        $orderData = $order->getById($orderId);
        
        if (!$orderData) {
            return false;
        }

        // إنشاء إشعار للمستخدم
        // Create notification for user
        $this->create(
            $orderData['user_id'],
            'order_status',
            translate('order_status_notification', ['order_number' => $orderData['order_number'], 'status' => translate($status)]),
            ['order_id' => $orderId, 'order_number' => $orderData['order_number'], 'status' => $status]
        );

        return true;
    }

    /**
     * إنشاء إشعار لتحديث حالة الدفع
     * Create notification for payment status update
     */
    public function createPaymentStatusNotification($paymentId, $status) {
        // الحصول على بيانات الدفع
        // Get payment data
        $payment = new Payment();
        $paymentData = $payment->getById($paymentId);
        
        if (!$paymentData) {
            return false;
        }

        // الحصول على بيانات الطلب
        // Get order data
        $order = new Order();
        $orderData = $order->getById($paymentData['order_id']);
        
        if (!$orderData) {
            return false;
        }

        // إنشاء إشعار للمستخدم
        // Create notification for user
        $this->create(
            $orderData['user_id'],
            'payment_status',
            translate('payment_status_notification', ['order_number' => $orderData['order_number'], 'status' => translate($status)]),
            ['order_id' => $paymentData['order_id'], 'order_number' => $orderData['order_number'], 'payment_id' => $paymentId, 'status' => $status]
        );

        // إنشاء إشعار للمسؤولين إذا كان الدفع مكتملاً
        // Create notification for administrators if payment is completed
        if ($status === PAYMENT_STATUS_COMPLETED) {
            $this->createForAdmins(
                'payment_completed_admin',
                translate('payment_completed_admin_notification', ['order_number' => $orderData['order_number']]),
                ['order_id' => $paymentData['order_id'], 'order_number' => $orderData['order_number'], 'payment_id' => $paymentId]
            );
        }

        return true;
    }

    /**
     * إنشاء إشعار لمراجعة جديدة
     * Create notification for new review
     */
    public function createReviewNotification($reviewId) {
        // الحصول على بيانات المراجعة
        // Get review data
        $review = new Review();
        $reviewData = $review->getById($reviewId);
        
        if (!$reviewData) {
            return false;
        }

        // إنشاء إشعار للمسؤولين
        // Create notification for administrators
        $this->createForAdmins(
            'new_review_admin',
            translate('new_review_admin_notification'),
            ['review_id' => $reviewId]
        );

        return true;
    }

    /**
     * إنشاء إشعار لتحديث حالة المراجعة
     * Create notification for review status update
     */
    public function createReviewStatusNotification($reviewId, $status) {
        // الحصول على بيانات المراجعة
        // Get review data
        $review = new Review();
        $reviewData = $review->getById($reviewId);
        
        if (!$reviewData) {
            return false;
        }

        // إنشاء إشعار للمستخدم
        // Create notification for user
        $this->create(
            $reviewData['user_id'],
            'review_status',
            translate('review_status_notification', ['status' => translate($status)]),
            ['review_id' => $reviewId, 'status' => $status]
        );

        return true;
    }

    /**
     * إنشاء إشعار لخدمة مخصصة جديدة
     * Create notification for new custom service
     */
    public function createCustomServiceNotification($customServiceId) {
        // الحصول على بيانات الخدمة المخصصة
        // Get custom service data
        $service = new Service();
        $customServiceData = $service->getCustomServiceByLink($customServiceId);
        
        if (!$customServiceData) {
            return false;
        }

        // إنشاء إشعار للمسؤولين
        // Create notification for administrators
        $this->createForAdmins(
            'new_custom_service_admin',
            translate('new_custom_service_admin_notification'),
            ['custom_service_id' => $customServiceId]
        );

        return true;
    }

    /**
     * إنشاء إشعار لتحديث حالة الخدمة المخصصة
     * Create notification for custom service status update
     */
    public function createCustomServiceStatusNotification($customServiceId, $status) {
        // الحصول على بيانات الخدمة المخصصة
        // Get custom service data
        $service = new Service();
        $customServiceData = $service->getCustomServiceByLink($customServiceId);
        
        if (!$customServiceData) {
            return false;
        }

        // إنشاء إشعار للمستخدم
        // Create notification for user
        $this->create(
            $customServiceData['user_id'],
            'custom_service_status',
            translate('custom_service_status_notification', ['status' => translate($status)]),
            ['custom_service_id' => $customServiceId, 'status' => $status]
        );

        return true;
    }
}

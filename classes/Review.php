<?php
/**
 * فئة المراجعة
 * Review class
 */

class Review {
    private $db;

    /**
     * إنشاء كائن المراجعة
     * Create review object
     */
    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * إضافة مراجعة جديدة
     * Add new review
     */
    public function add($reviewData) {
        // التحقق من وجود المستخدم
        // Check if user exists
        $user = new User();
        $userData = $user->getById($reviewData['user_id']);
        
        if (!$userData) {
            throw new Exception(translate('user_not_found'));
        }

        // التحقق من وجود المنتج أو الخدمة
        // Check if product or service exists
        if (isset($reviewData['product_id']) && $reviewData['product_id']) {
            $product = new Product();
            $productData = $product->getById($reviewData['product_id']);
            
            if (!$productData) {
                throw new Exception(translate('product_not_found'));
            }
        } elseif (isset($reviewData['service_id']) && $reviewData['service_id']) {
            $service = new Service();
            $serviceData = $service->getById($reviewData['service_id']);
            
            if (!$serviceData) {
                throw new Exception(translate('service_not_found'));
            }
        } else {
            throw new Exception(translate('product_or_service_required'));
        }

        // التحقق من عدم وجود مراجعة سابقة
        // Check if review already exists
        if (isset($reviewData['product_id']) && $reviewData['product_id']) {
            $existingReview = $this->db->getRow("SELECT * FROM reviews WHERE user_id = :user_id AND product_id = :product_id", [
                ':user_id' => $reviewData['user_id'],
                ':product_id' => $reviewData['product_id']
            ]);
            
            if ($existingReview) {
                throw new Exception(translate('review_already_exists'));
            }
        } elseif (isset($reviewData['service_id']) && $reviewData['service_id']) {
            $existingReview = $this->db->getRow("SELECT * FROM reviews WHERE user_id = :user_id AND service_id = :service_id", [
                ':user_id' => $reviewData['user_id'],
                ':service_id' => $reviewData['service_id']
            ]);
            
            if ($existingReview) {
                throw new Exception(translate('review_already_exists'));
            }
        }

        // إضافة تاريخ الإنشاء
        // Add creation date
        $reviewData['created_at'] = date('Y-m-d H:i:s');
        
        // تعيين حالة المراجعة
        // Set review status
        $reviewData['status'] = REVIEW_STATUS_PENDING;

        // إدراج المراجعة في قاعدة البيانات
        // Insert review into database
        $reviewId = $this->db->insert('reviews', $reviewData);

        // تسجيل نشاط إضافة المراجعة
        // Log review addition activity
        logActivity($reviewData['user_id'], 'add_review', 'Added new review');

        return $reviewId;
    }

    /**
     * تحديث حالة المراجعة
     * Update review status
     */
    public function updateStatus($reviewId, $status) {
        // التحقق من وجود المراجعة
        // Check if review exists
        $review = $this->getById($reviewId);
        
        if (!$review) {
            throw new Exception(translate('review_not_found'));
        }

        // تحديث حالة المراجعة
        // Update review status
        $result = $this->db->update('reviews', ['status' => $status, 'updated_at' => date('Y-m-d H:i:s')], 'id = :id', [':id' => $reviewId]);

        // تسجيل نشاط تحديث حالة المراجعة
        // Log review status update activity
        logActivity($_SESSION['user_id'], 'update_review_status', 'Updated review status to: ' . $status);

        return $result;
    }

    /**
     * حذف مراجعة
     * Delete review
     */
    public function delete($reviewId) {
        // التحقق من وجود المراجعة
        // Check if review exists
        $review = $this->getById($reviewId);
        
        if (!$review) {
            throw new Exception(translate('review_not_found'));
        }

        // حذف المراجعة
        // Delete review
        $result = $this->db->delete('reviews', 'id = :id', [':id' => $reviewId]);

        // تسجيل نشاط حذف المراجعة
        // Log review deletion activity
        logActivity($_SESSION['user_id'], 'delete_review', 'Deleted review');

        return $result;
    }

    /**
     * الحصول على مراجعة بواسطة المعرف
     * Get review by ID
     */
    public function getById($reviewId) {
        $review = $this->db->getRow("SELECT * FROM reviews WHERE id = :id", [':id' => $reviewId]);
        
        if (!$review) {
            return false;
        }

        // الحصول على بيانات المستخدم
        // Get user data
        $user = new User();
        $review['user'] = $user->getById($review['user_id']);

        // الحصول على بيانات المنتج أو الخدمة
        // Get product or service data
        if ($review['product_id']) {
            $product = new Product();
            $review['product'] = $product->getById($review['product_id']);
        } elseif ($review['service_id']) {
            $service = new Service();
            $review['service'] = $service->getById($review['service_id']);
        }

        return $review;
    }

    /**
     * الحصول على مراجعات المنتج
     * Get product reviews
     */
    public function getProductReviews($productId, $page = 1, $perPage = ITEMS_PER_PAGE, $status = REVIEW_STATUS_APPROVED) {
        $where = "product_id = :product_id";
        $params = [':product_id' => $productId];

        if ($status !== null) {
            $where .= " AND status = :status";
            $params[':status'] = $status;
        }

        $result = $this->db->getPaginated('reviews', $page, $perPage, $where, $params, 'created_at DESC');

        // الحصول على بيانات المستخدمين
        // Get users data
        foreach ($result['data'] as &$review) {
            $user = new User();
            $review['user'] = $user->getById($review['user_id']);
        }

        return $result;
    }

    /**
     * الحصول على مراجعات الخدمة
     * Get service reviews
     */
    public function getServiceReviews($serviceId, $page = 1, $perPage = ITEMS_PER_PAGE, $status = REVIEW_STATUS_APPROVED) {
        $where = "service_id = :service_id";
        $params = [':service_id' => $serviceId];

        if ($status !== null) {
            $where .= " AND status = :status";
            $params[':status'] = $status;
        }

        $result = $this->db->getPaginated('reviews', $page, $perPage, $where, $params, 'created_at DESC');

        // الحصول على بيانات المستخدمين
        // Get users data
        foreach ($result['data'] as &$review) {
            $user = new User();
            $review['user'] = $user->getById($review['user_id']);
        }

        return $result;
    }

    /**
     * الحصول على مراجعات المستخدم
     * Get user reviews
     */
    public function getUserReviews($userId, $page = 1, $perPage = ITEMS_PER_PAGE) {
        $where = "user_id = :user_id";
        $params = [':user_id' => $userId];

        $result = $this->db->getPaginated('reviews', $page, $perPage, $where, $params, 'created_at DESC');

        // الحصول على بيانات المنتجات والخدمات
        // Get products and services data
        foreach ($result['data'] as &$review) {
            if ($review['product_id']) {
                $product = new Product();
                $review['product'] = $product->getById($review['product_id']);
            } elseif ($review['service_id']) {
                $service = new Service();
                $review['service'] = $service->getById($review['service_id']);
            }
        }

        return $result;
    }

    /**
     * الحصول على جميع المراجعات
     * Get all reviews
     */
    public function getAllReviews($page = 1, $perPage = ITEMS_PER_PAGE, $status = null) {
        $where = '';
        $params = [];

        if ($status !== null) {
            $where = "status = :status";
            $params[':status'] = $status;
        }

        $result = $this->db->getPaginated('reviews', $page, $perPage, $where, $params, 'created_at DESC');

        // الحصول على بيانات المستخدمين والمنتجات والخدمات
        // Get users, products and services data
        foreach ($result['data'] as &$review) {
            $user = new User();
            $review['user'] = $user->getById($review['user_id']);

            if ($review['product_id']) {
                $product = new Product();
                $review['product'] = $product->getById($review['product_id']);
            } elseif ($review['service_id']) {
                $service = new Service();
                $review['service'] = $service->getById($review['service_id']);
            }
        }

        return $result;
    }

    /**
     * الحصول على متوسط تقييم المنتج
     * Get product average rating
     */
    public function getProductAverageRating($productId) {
        $rating = $this->db->getValue("SELECT AVG(rating) FROM reviews WHERE product_id = :product_id AND status = :status", [
            ':product_id' => $productId,
            ':status' => REVIEW_STATUS_APPROVED
        ]);

        return round($rating, 1);
    }

    /**
     * الحصول على متوسط تقييم الخدمة
     * Get service average rating
     */
    public function getServiceAverageRating($serviceId) {
        $rating = $this->db->getValue("SELECT AVG(rating) FROM reviews WHERE service_id = :service_id AND status = :status", [
            ':service_id' => $serviceId,
            ':status' => REVIEW_STATUS_APPROVED
        ]);

        return round($rating, 1);
    }

    /**
     * الحصول على عدد مراجعات المنتج
     * Get product reviews count
     */
    public function getProductReviewsCount($productId, $status = REVIEW_STATUS_APPROVED) {
        $count = $this->db->count('reviews', 'product_id = :product_id AND status = :status', [
            ':product_id' => $productId,
            ':status' => $status
        ]);

        return $count;
    }

    /**
     * الحصول على عدد مراجعات الخدمة
     * Get service reviews count
     */
    public function getServiceReviewsCount($serviceId, $status = REVIEW_STATUS_APPROVED) {
        $count = $this->db->count('reviews', 'service_id = :service_id AND status = :status', [
            ':service_id' => $serviceId,
            ':status' => $status
        ]);

        return $count;
    }

    /**
     * البحث عن المراجعات
     * Search reviews
     */
    public function searchReviews($query, $page = 1, $perPage = ITEMS_PER_PAGE) {
        $where = "title LIKE :query OR comment LIKE :query";
        $params = [':query' => "%{$query}%"];

        $result = $this->db->getPaginated('reviews', $page, $perPage, $where, $params, 'created_at DESC');

        // الحصول على بيانات المستخدمين والمنتجات والخدمات
        // Get users, products and services data
        foreach ($result['data'] as &$review) {
            $user = new User();
            $review['user'] = $user->getById($review['user_id']);

            if ($review['product_id']) {
                $product = new Product();
                $review['product'] = $product->getById($review['product_id']);
            } elseif ($review['service_id']) {
                $service = new Service();
                $review['service'] = $service->getById($review['service_id']);
            }
        }

        return $result;
    }
}

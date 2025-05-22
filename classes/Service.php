<?php
/**
 * فئة الخدمة
 * Service class
 */

class Service {
    private $db;

    /**
     * إنشاء كائن الخدمة
     * Create service object
     */
    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * إضافة خدمة جديدة
     * Add new service
     */
    public function add($serviceData) {
        // التحقق من وجود اسم الخدمة
        // Check if service name already exists
        if ($this->db->exists('services', 'name_' . getCurrentLanguage(), $serviceData['name_' . getCurrentLanguage()])) {
            throw new Exception(translate('service_name_already_exists'));
        }

        // إنشاء slug للخدمة
        // Create service slug
        $serviceData['slug'] = createSlug($serviceData['name_en']);
        
        // التحقق من وجود slug
        // Check if slug already exists
        if ($this->db->exists('services', 'slug', $serviceData['slug'])) {
            $serviceData['slug'] = $serviceData['slug'] . '-' . time();
        }

        // إضافة تاريخ الإنشاء
        // Add creation date
        $serviceData['created_at'] = date('Y-m-d H:i:s');

        // إدراج الخدمة في قاعدة البيانات
        // Insert service into database
        $serviceId = $this->db->insert('services', $serviceData);

        // تسجيل نشاط إضافة الخدمة
        // Log service addition activity
        logActivity($_SESSION['user_id'], 'add_service', 'Added new service: ' . $serviceData['name_en']);

        return $serviceId;
    }

    /**
     * تحديث خدمة
     * Update service
     */
    public function update($serviceId, $serviceData) {
        // التحقق من وجود الخدمة
        // Check if service exists
        $service = $this->getById($serviceId);
        
        if (!$service) {
            throw new Exception(translate('service_not_found'));
        }

        // التحقق من وجود اسم الخدمة
        // Check if service name already exists
        if (isset($serviceData['name_' . getCurrentLanguage()]) && 
            $this->db->exists('services', 'name_' . getCurrentLanguage(), $serviceData['name_' . getCurrentLanguage()], $serviceId)) {
            throw new Exception(translate('service_name_already_exists'));
        }

        // إنشاء slug جديد إذا تم تغيير الاسم
        // Create new slug if name changed
        if (isset($serviceData['name_en']) && $serviceData['name_en'] !== $service['name_en']) {
            $serviceData['slug'] = createSlug($serviceData['name_en']);
            
            // التحقق من وجود slug
            // Check if slug already exists
            if ($this->db->exists('services', 'slug', $serviceData['slug'], $serviceId)) {
                $serviceData['slug'] = $serviceData['slug'] . '-' . time();
            }
        }

        // إضافة تاريخ التحديث
        // Add update date
        $serviceData['updated_at'] = date('Y-m-d H:i:s');

        // تحديث الخدمة في قاعدة البيانات
        // Update service in database
        $result = $this->db->update('services', $serviceData, 'id = :id', [':id' => $serviceId]);

        // تسجيل نشاط تحديث الخدمة
        // Log service update activity
        logActivity($_SESSION['user_id'], 'update_service', 'Updated service: ' . $service['name_en']);

        return $result;
    }

    /**
     * حذف خدمة
     * Delete service
     */
    public function delete($serviceId) {
        // التحقق من وجود الخدمة
        // Check if service exists
        $service = $this->getById($serviceId);
        
        if (!$service) {
            throw new Exception(translate('service_not_found'));
        }

        // حذف الخدمة من قاعدة البيانات
        // Delete service from database
        $result = $this->db->delete('services', 'id = :id', [':id' => $serviceId]);

        // تسجيل نشاط حذف الخدمة
        // Log service deletion activity
        logActivity($_SESSION['user_id'], 'delete_service', 'Deleted service: ' . $service['name_en']);

        return $result;
    }

    /**
     * الحصول على خدمة بواسطة المعرف
     * Get service by ID
     */
    public function getById($serviceId) {
        $service = $this->db->getRow("SELECT * FROM services WHERE id = :id", [':id' => $serviceId]);
        
        if (!$service) {
            return false;
        }

        // الحصول على تصنيف الخدمة
        // Get service category
        if ($service['category_id']) {
            $service['category'] = $this->db->getRow("SELECT * FROM categories WHERE id = :id", [':id' => $service['category_id']]);
        } else {
            $service['category'] = null;
        }

        return $service;
    }

    /**
     * الحصول على خدمة بواسطة الـ slug
     * Get service by slug
     */
    public function getBySlug($slug) {
        $service = $this->db->getRow("SELECT * FROM services WHERE slug = :slug", [':slug' => $slug]);
        
        if (!$service) {
            return false;
        }

        // الحصول على تصنيف الخدمة
        // Get service category
        if ($service['category_id']) {
            $service['category'] = $this->db->getRow("SELECT * FROM categories WHERE id = :id", [':id' => $service['category_id']]);
        } else {
            $service['category'] = null;
        }

        return $service;
    }

    /**
     * الحصول على قائمة الخدمات
     * Get services list
     */
    public function getServices($page = 1, $perPage = ITEMS_PER_PAGE, $search = '', $categoryId = null, $status = null, $featured = null) {
        $where = '';
        $params = [];

        if (!empty($search)) {
            $where .= "(name_ar LIKE :search OR name_en LIKE :search OR description_ar LIKE :search OR description_en LIKE :search)";
            $params[':search'] = "%{$search}%";
        }

        if ($categoryId !== null) {
            if (!empty($where)) {
                $where .= " AND ";
            }
            $where .= "category_id = :category_id";
            $params[':category_id'] = $categoryId;
        }

        if ($status !== null) {
            if (!empty($where)) {
                $where .= " AND ";
            }
            $where .= "status = :status";
            $params[':status'] = $status;
        }

        if ($featured !== null) {
            if (!empty($where)) {
                $where .= " AND ";
            }
            $where .= "is_featured = :featured";
            $params[':featured'] = $featured ? 1 : 0;
        }

        $result = $this->db->getPaginated('services', $page, $perPage, $where, $params, 'created_at DESC');

        // الحصول على تصنيفات الخدمات
        // Get service categories
        foreach ($result['data'] as &$service) {
            if ($service['category_id']) {
                $service['category'] = $this->db->getRow("SELECT * FROM categories WHERE id = :id", [':id' => $service['category_id']]);
            } else {
                $service['category'] = null;
            }
        }

        return $result;
    }

    /**
     * الحصول على الخدمات المميزة
     * Get featured services
     */
    public function getFeaturedServices($limit = 6) {
        $services = $this->db->getRows("SELECT * FROM services WHERE is_featured = 1 AND status = :status ORDER BY created_at DESC LIMIT :limit", [
            ':status' => SERVICE_STATUS_ACTIVE,
            ':limit' => $limit
        ]);

        // الحصول على تصنيفات الخدمات
        // Get service categories
        foreach ($services as &$service) {
            if ($service['category_id']) {
                $service['category'] = $this->db->getRow("SELECT * FROM categories WHERE id = :id", [':id' => $service['category_id']]);
            } else {
                $service['category'] = null;
            }
        }

        return $services;
    }

    /**
     * الحصول على الخدمات الأحدث
     * Get latest services
     */
    public function getLatestServices($limit = 8) {
        $services = $this->db->getRows("SELECT * FROM services WHERE status = :status ORDER BY created_at DESC LIMIT :limit", [
            ':status' => SERVICE_STATUS_ACTIVE,
            ':limit' => $limit
        ]);

        // الحصول على تصنيفات الخدمات
        // Get service categories
        foreach ($services as &$service) {
            if ($service['category_id']) {
                $service['category'] = $this->db->getRow("SELECT * FROM categories WHERE id = :id", [':id' => $service['category_id']]);
            } else {
                $service['category'] = null;
            }
        }

        return $services;
    }

    /**
     * الحصول على الخدمات ذات الصلة
     * Get related services
     */
    public function getRelatedServices($serviceId, $limit = 4) {
        $service = $this->getById($serviceId);
        
        if (!$service) {
            return [];
        }

        $where = "id != :service_id AND status = :status";
        $params = [
            ':service_id' => $serviceId,
            ':status' => SERVICE_STATUS_ACTIVE
        ];

        if ($service['category_id']) {
            $where .= " AND category_id = :category_id";
            $params[':category_id'] = $service['category_id'];
        }

        $services = $this->db->getRows("SELECT * FROM services WHERE {$where} ORDER BY RAND() LIMIT :limit", array_merge($params, [':limit' => $limit]));

        // الحصول على تصنيفات الخدمات
        // Get service categories
        foreach ($services as &$service) {
            if ($service['category_id']) {
                $service['category'] = $this->db->getRow("SELECT * FROM categories WHERE id = :id", [':id' => $service['category_id']]);
            } else {
                $service['category'] = null;
            }
        }

        return $services;
    }

    /**
     * تحديث حالة الخدمة
     * Update service status
     */
    public function updateStatus($serviceId, $status) {
        // التحقق من وجود الخدمة
        // Check if service exists
        $service = $this->getById($serviceId);
        
        if (!$service) {
            throw new Exception(translate('service_not_found'));
        }

        // تحديث حالة الخدمة
        // Update service status
        $result = $this->db->update('services', ['status' => $status], 'id = :id', [':id' => $serviceId]);

        // تسجيل نشاط تحديث حالة الخدمة
        // Log service status update activity
        logActivity($_SESSION['user_id'], 'update_service_status', 'Updated service status: ' . $service['name_en'] . ' to ' . $status);

        return $result;
    }

    /**
     * تحديث خاصية الخدمة المميزة
     * Update service featured status
     */
    public function updateFeatured($serviceId, $featured) {
        // التحقق من وجود الخدمة
        // Check if service exists
        $service = $this->getById($serviceId);
        
        if (!$service) {
            throw new Exception(translate('service_not_found'));
        }

        // تحديث خاصية الخدمة المميزة
        // Update service featured status
        $result = $this->db->update('services', ['is_featured' => $featured ? 1 : 0], 'id = :id', [':id' => $serviceId]);

        // تسجيل نشاط تحديث خاصية الخدمة المميزة
        // Log service featured status update activity
        $action = $featured ? 'Featured' : 'Unfeatured';
        logActivity($_SESSION['user_id'], 'update_service_featured', $action . ' service: ' . $service['name_en']);

        return $result;
    }

    /**
     * البحث عن الخدمات
     * Search services
     */
    public function searchServices($query, $page = 1, $perPage = ITEMS_PER_PAGE) {
        $where = "(name_ar LIKE :query OR name_en LIKE :query OR description_ar LIKE :query OR description_en LIKE :query) AND status = :status";
        $params = [
            ':query' => "%{$query}%",
            ':status' => SERVICE_STATUS_ACTIVE
        ];

        $result = $this->db->getPaginated('services', $page, $perPage, $where, $params, 'created_at DESC');

        // الحصول على تصنيفات الخدمات
        // Get service categories
        foreach ($result['data'] as &$service) {
            if ($service['category_id']) {
                $service['category'] = $this->db->getRow("SELECT * FROM categories WHERE id = :id", [':id' => $service['category_id']]);
            } else {
                $service['category'] = null;
            }
        }

        return $result;
    }

    /**
     * إنشاء خدمة مخصصة
     * Create custom service
     */
    public function createCustomService($customServiceData) {
        // إنشاء رابط فريد
        // Create unique link
        $customServiceData['unique_link'] = generateUniqueLink();
        
        // إضافة تاريخ الإنشاء
        // Add creation date
        $customServiceData['created_at'] = date('Y-m-d H:i:s');
        
        // تعيين حالة الخدمة المخصصة
        // Set custom service status
        $customServiceData['status'] = CUSTOM_SERVICE_STATUS_PENDING;

        // إدراج الخدمة المخصصة في قاعدة البيانات
        // Insert custom service into database
        $customServiceId = $this->db->insert('custom_services', $customServiceData);

        // تسجيل نشاط إنشاء الخدمة المخصصة
        // Log custom service creation activity
        logActivity($_SESSION['user_id'], 'create_custom_service', 'Created custom service for user ID: ' . $customServiceData['user_id']);

        return $customServiceId;
    }

    /**
     * الحصول على خدمة مخصصة بواسطة الرابط الفريد
     * Get custom service by unique link
     */
    public function getCustomServiceByLink($uniqueLink) {
        $customService = $this->db->getRow("SELECT * FROM custom_services WHERE unique_link = :unique_link", [':unique_link' => $uniqueLink]);
        
        if (!$customService) {
            return false;
        }

        // الحصول على بيانات الخدمة الأساسية إذا كانت موجودة
        // Get base service data if exists
        if ($customService['service_id']) {
            $customService['service'] = $this->getById($customService['service_id']);
        } else {
            $customService['service'] = null;
        }

        // الحصول على بيانات المستخدم
        // Get user data
        if ($customService['user_id']) {
            $user = new User();
            $customService['user'] = $user->getById($customService['user_id']);
        } else {
            $customService['user'] = null;
        }

        return $customService;
    }

    /**
     * تحديث حالة الخدمة المخصصة
     * Update custom service status
     */
    public function updateCustomServiceStatus($customServiceId, $status) {
        // التحقق من وجود الخدمة المخصصة
        // Check if custom service exists
        $customService = $this->db->getRow("SELECT * FROM custom_services WHERE id = :id", [':id' => $customServiceId]);
        
        if (!$customService) {
            throw new Exception(translate('custom_service_not_found'));
        }

        // تحديث حالة الخدمة المخصصة
        // Update custom service status
        $result = $this->db->update('custom_services', ['status' => $status], 'id = :id', [':id' => $customServiceId]);

        // تسجيل نشاط تحديث حالة الخدمة المخصصة
        // Log custom service status update activity
        logActivity($_SESSION['user_id'], 'update_custom_service_status', 'Updated custom service status to: ' . $status);

        return $result;
    }

    /**
     * الحصول على الخدمات المخصصة للمستخدم
     * Get custom services for user
     */
    public function getUserCustomServices($userId, $page = 1, $perPage = ITEMS_PER_PAGE) {
        $where = "user_id = :user_id";
        $params = [':user_id' => $userId];

        $result = $this->db->getPaginated('custom_services', $page, $perPage, $where, $params, 'created_at DESC');

        // الحصول على بيانات الخدمات الأساسية
        // Get base services data
        foreach ($result['data'] as &$customService) {
            if ($customService['service_id']) {
                $customService['service'] = $this->getById($customService['service_id']);
            } else {
                $customService['service'] = null;
            }
        }

        return $result;
    }

    /**
     * الحصول على جميع الخدمات المخصصة
     * Get all custom services
     */
    public function getAllCustomServices($page = 1, $perPage = ITEMS_PER_PAGE, $status = null) {
        $where = '';
        $params = [];

        if ($status !== null) {
            $where = "status = :status";
            $params[':status'] = $status;
        }

        $result = $this->db->getPaginated('custom_services', $page, $perPage, $where, $params, 'created_at DESC');

        // الحصول على بيانات الخدمات الأساسية والمستخدمين
        // Get base services and users data
        foreach ($result['data'] as &$customService) {
            if ($customService['service_id']) {
                $customService['service'] = $this->getById($customService['service_id']);
            } else {
                $customService['service'] = null;
            }

            if ($customService['user_id']) {
                $user = new User();
                $customService['user'] = $user->getById($customService['user_id']);
            } else {
                $customService['user'] = null;
            }
        }

        return $result;
    }
}

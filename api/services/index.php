<?php
/**
 * واجهة برمجة التطبيقات للخدمات
 * Services API
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

// تحديد معرف الخدمة إذا كان موجودًا في المسار
// Determine service ID if present in path
$serviceId = null;
if (isset($uriSegments[2]) && is_numeric($uriSegments[2])) {
    $serviceId = (int)$uriSegments[2];
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
        if ($serviceId) {
            // الحصول على خدمة محددة
            // Get specific service
            getService($serviceId);
        } else if (isset($_GET['category'])) {
            // الحصول على الخدمات حسب التصنيف
            // Get services by category
            getServicesByCategory($_GET['category']);
        } else if (isset($_GET['search'])) {
            // البحث في الخدمات
            // Search services
            searchServices($_GET['search']);
        } else {
            // الحصول على قائمة الخدمات
            // Get list of services
            getServices();
        }
        break;
        
    case 'POST':
        // إنشاء خدمة جديدة (يتطلب مصادقة)
        // Create new service (requires authentication)
        if (isAuthenticated() && hasPermission('manage_services')) {
            createService();
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Unauthorized';
            http_response_code(401);
        }
        break;
        
    case 'PUT':
        // تحديث خدمة موجودة (يتطلب مصادقة)
        // Update existing service (requires authentication)
        if ($serviceId && isAuthenticated() && hasPermission('manage_services')) {
            updateService($serviceId);
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Unauthorized or invalid service ID';
            http_response_code(401);
        }
        break;
        
    case 'DELETE':
        // حذف خدمة (يتطلب مصادقة)
        // Delete service (requires authentication)
        if ($serviceId && isAuthenticated() && hasPermission('manage_services')) {
            deleteService($serviceId);
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Unauthorized or invalid service ID';
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
 * الحصول على قائمة الخدمات
 * Get list of services
 */
function getServices() {
    global $response;
    
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
    $allowedSortFields = ['id', 'name_ar', 'name_en', 'price', 'created_at'];
    if (!in_array($sort, $allowedSortFields)) {
        $sort = 'id';
    }
    
    try {
        // إنشاء كائن الخدمة
        // Create service object
        $service = new Service();
        
        // الحصول على الخدمات
        // Get services
        $services = $service->getServices($limit, $offset, $sort, $order);
        $total = $service->getTotalServices();
        
        // تهيئة الاستجابة
        // Prepare response
        $response['status'] = 'success';
        $response['message'] = 'Services retrieved successfully';
        $response['data'] = $services;
        $response['meta'] = [
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ];
        
        http_response_code(200);
    } catch (Exception $e) {
        $response['status'] = 'error';
        $response['message'] = 'Failed to retrieve services: ' . $e->getMessage();
        http_response_code(500);
    }
}

/**
 * الحصول على خدمة محددة
 * Get specific service
 * 
 * @param int $id معرف الخدمة
 */
function getService($id) {
    global $response;
    
    try {
        // إنشاء كائن الخدمة
        // Create service object
        $service = new Service();
        
        // الحصول على الخدمة
        // Get service
        $serviceData = $service->getServiceById($id);
        
        if ($serviceData) {
            $response['status'] = 'success';
            $response['message'] = 'Service retrieved successfully';
            $response['data'] = $serviceData;
            http_response_code(200);
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Service not found';
            http_response_code(404);
        }
    } catch (Exception $e) {
        $response['status'] = 'error';
        $response['message'] = 'Failed to retrieve service: ' . $e->getMessage();
        http_response_code(500);
    }
}

/**
 * الحصول على الخدمات حسب التصنيف
 * Get services by category
 * 
 * @param int $categoryId معرف التصنيف
 */
function getServicesByCategory($categoryId) {
    global $response;
    
    // الحصول على معلمات التصفح
    // Get pagination parameters
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $limit;
    
    try {
        // إنشاء كائن الخدمة
        // Create service object
        $service = new Service();
        
        // الحصول على الخدمات حسب التصنيف
        // Get services by category
        $services = $service->getServicesByCategory($categoryId, $limit, $offset);
        $total = $service->getTotalServicesByCategory($categoryId);
        
        $response['status'] = 'success';
        $response['message'] = 'Services retrieved successfully';
        $response['data'] = $services;
        $response['meta'] = [
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit),
            'category_id' => $categoryId
        ];
        
        http_response_code(200);
    } catch (Exception $e) {
        $response['status'] = 'error';
        $response['message'] = 'Failed to retrieve services: ' . $e->getMessage();
        http_response_code(500);
    }
}

/**
 * البحث في الخدمات
 * Search services
 * 
 * @param string $query استعلام البحث
 */
function searchServices($query) {
    global $response;
    
    // الحصول على معلمات التصفح
    // Get pagination parameters
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $limit;
    
    try {
        // إنشاء كائن الخدمة
        // Create service object
        $service = new Service();
        
        // البحث في الخدمات
        // Search services
        $services = $service->searchServices($query, $limit, $offset);
        $total = $service->getTotalSearchResults($query);
        
        $response['status'] = 'success';
        $response['message'] = 'Search results retrieved successfully';
        $response['data'] = $services;
        $response['meta'] = [
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit),
            'query' => $query
        ];
        
        http_response_code(200);
    } catch (Exception $e) {
        $response['status'] = 'error';
        $response['message'] = 'Failed to search services: ' . $e->getMessage();
        http_response_code(500);
    }
}

/**
 * إنشاء خدمة جديدة
 * Create new service
 */
function createService() {
    global $response;
    
    // الحصول على بيانات الطلب
    // Get request data
    $data = json_decode(file_get_contents('php://input'), true);
    
    // التحقق من البيانات المطلوبة
    // Validate required data
    if (!isset($data['name_ar']) || !isset($data['name_en']) || !isset($data['price'])) {
        $response['status'] = 'error';
        $response['message'] = 'Missing required fields';
        http_response_code(400);
        return;
    }
    
    try {
        // إنشاء كائن الخدمة
        // Create service object
        $service = new Service();
        
        // إنشاء الخدمة
        // Create service
        $serviceId = $service->createService($data);
        
        if ($serviceId) {
            $response['status'] = 'success';
            $response['message'] = 'Service created successfully';
            $response['data'] = ['id' => $serviceId];
            http_response_code(201);
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Failed to create service';
            http_response_code(500);
        }
    } catch (Exception $e) {
        $response['status'] = 'error';
        $response['message'] = 'Failed to create service: ' . $e->getMessage();
        http_response_code(500);
    }
}

/**
 * تحديث خدمة موجودة
 * Update existing service
 * 
 * @param int $id معرف الخدمة
 */
function updateService($id) {
    global $response;
    
    // الحصول على بيانات الطلب
    // Get request data
    $data = json_decode(file_get_contents('php://input'), true);
    
    try {
        // إنشاء كائن الخدمة
        // Create service object
        $service = new Service();
        
        // التحقق من وجود الخدمة
        // Check if service exists
        $serviceData = $service->getServiceById($id);
        
        if (!$serviceData) {
            $response['status'] = 'error';
            $response['message'] = 'Service not found';
            http_response_code(404);
            return;
        }
        
        // تحديث الخدمة
        // Update service
        $result = $service->updateService($id, $data);
        
        if ($result) {
            $response['status'] = 'success';
            $response['message'] = 'Service updated successfully';
            $response['data'] = ['id' => $id];
            http_response_code(200);
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Failed to update service';
            http_response_code(500);
        }
    } catch (Exception $e) {
        $response['status'] = 'error';
        $response['message'] = 'Failed to update service: ' . $e->getMessage();
        http_response_code(500);
    }
}

/**
 * حذف خدمة
 * Delete service
 * 
 * @param int $id معرف الخدمة
 */
function deleteService($id) {
    global $response;
    
    try {
        // إنشاء كائن الخدمة
        // Create service object
        $service = new Service();
        
        // التحقق من وجود الخدمة
        // Check if service exists
        $serviceData = $service->getServiceById($id);
        
        if (!$serviceData) {
            $response['status'] = 'error';
            $response['message'] = 'Service not found';
            http_response_code(404);
            return;
        }
        
        // حذف الخدمة
        // Delete service
        $result = $service->deleteService($id);
        
        if ($result) {
            $response['status'] = 'success';
            $response['message'] = 'Service deleted successfully';
            $response['data'] = null;
            http_response_code(200);
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Failed to delete service';
            http_response_code(500);
        }
    } catch (Exception $e) {
        $response['status'] = 'error';
        $response['message'] = 'Failed to delete service: ' . $e->getMessage();
        http_response_code(500);
    }
}

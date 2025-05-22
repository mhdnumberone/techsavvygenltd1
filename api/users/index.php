<?php
/**
 * واجهة برمجة التطبيقات للمستخدمين
 * Users API
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

// تحديد معرف المستخدم إذا كان موجودًا في المسار
// Determine user ID if present in path
$userId = null;
if (isset($uriSegments[2]) && is_numeric($uriSegments[2])) {
    $userId = (int)$uriSegments[2];
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
        if ($userId) {
            // الحصول على مستخدم محدد
            // Get specific user
            getUser($userId);
        } else {
            // الحصول على قائمة المستخدمين
            // Get list of users
            getUsers();
        }
        break;
        
    case 'POST':
        // إنشاء مستخدم جديد أو تسجيل الدخول
        // Create new user or login
        if (isset($_GET['action']) && $_GET['action'] === 'login') {
            loginUser();
        } else {
            registerUser();
        }
        break;
        
    case 'PUT':
        // تحديث مستخدم موجود
        // Update existing user
        if ($userId && isAuthenticated()) {
            updateUser($userId);
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Unauthorized or invalid user ID';
            http_response_code(401);
        }
        break;
        
    case 'DELETE':
        // حذف مستخدم
        // Delete user
        if ($userId && isAuthenticated() && hasPermission('manage_users')) {
            deleteUser($userId);
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Unauthorized or invalid user ID';
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
 * الحصول على قائمة المستخدمين
 * Get list of users
 */
function getUsers() {
    global $response;
    
    // التحقق من الصلاحيات
    // Check permissions
    if (!isAuthenticated() || !hasPermission('view_users')) {
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
    $allowedSortFields = ['id', 'name', 'email', 'created_at'];
    if (!in_array($sort, $allowedSortFields)) {
        $sort = 'id';
    }
    
    try {
        // إنشاء كائن المستخدم
        // Create user object
        $user = new User();
        
        // الحصول على المستخدمين
        // Get users
        $users = $user->getUsers($limit, $offset, $sort, $order);
        $total = $user->getTotalUsers();
        
        // تهيئة الاستجابة
        // Prepare response
        $response['status'] = 'success';
        $response['message'] = 'Users retrieved successfully';
        $response['data'] = $users;
        $response['meta'] = [
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ];
        
        http_response_code(200);
    } catch (Exception $e) {
        $response['status'] = 'error';
        $response['message'] = 'Failed to retrieve users: ' . $e->getMessage();
        http_response_code(500);
    }
}

/**
 * الحصول على مستخدم محدد
 * Get specific user
 * 
 * @param int $id معرف المستخدم
 */
function getUser($id) {
    global $response;
    
    // التحقق من الصلاحيات
    // Check permissions
    if (!isAuthenticated() || (!hasPermission('view_users') && getCurrentUserId() != $id)) {
        $response['status'] = 'error';
        $response['message'] = 'Unauthorized';
        http_response_code(401);
        return;
    }
    
    try {
        // إنشاء كائن المستخدم
        // Create user object
        $user = new User();
        
        // الحصول على المستخدم
        // Get user
        $userData = $user->getUserById($id);
        
        if ($userData) {
            // إزالة كلمة المرور من البيانات
            // Remove password from data
            unset($userData['password']);
            
            $response['status'] = 'success';
            $response['message'] = 'User retrieved successfully';
            $response['data'] = $userData;
            http_response_code(200);
        } else {
            $response['status'] = 'error';
            $response['message'] = 'User not found';
            http_response_code(404);
        }
    } catch (Exception $e) {
        $response['status'] = 'error';
        $response['message'] = 'Failed to retrieve user: ' . $e->getMessage();
        http_response_code(500);
    }
}

/**
 * تسجيل مستخدم جديد
 * Register new user
 */
function registerUser() {
    global $response;
    
    // الحصول على بيانات الطلب
    // Get request data
    $data = json_decode(file_get_contents('php://input'), true);
    
    // التحقق من البيانات المطلوبة
    // Validate required data
    if (!isset($data['name']) || !isset($data['email']) || !isset($data['password'])) {
        $response['status'] = 'error';
        $response['message'] = 'Missing required fields';
        http_response_code(400);
        return;
    }
    
    try {
        // إنشاء كائن المستخدم
        // Create user object
        $user = new User();
        
        // التحقق من وجود البريد الإلكتروني
        // Check if email exists
        if ($user->emailExists($data['email'])) {
            $response['status'] = 'error';
            $response['message'] = 'Email already exists';
            http_response_code(409);
            return;
        }
        
        // تسجيل المستخدم
        // Register user
        $userId = $user->register($data['name'], $data['email'], $data['password'], $data['phone'] ?? null);
        
        if ($userId) {
            $response['status'] = 'success';
            $response['message'] = 'User registered successfully';
            $response['data'] = ['id' => $userId];
            http_response_code(201);
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Failed to register user';
            http_response_code(500);
        }
    } catch (Exception $e) {
        $response['status'] = 'error';
        $response['message'] = 'Failed to register user: ' . $e->getMessage();
        http_response_code(500);
    }
}

/**
 * تسجيل دخول المستخدم
 * Login user
 */
function loginUser() {
    global $response;
    
    // الحصول على بيانات الطلب
    // Get request data
    $data = json_decode(file_get_contents('php://input'), true);
    
    // التحقق من البيانات المطلوبة
    // Validate required data
    if (!isset($data['email']) || !isset($data['password'])) {
        $response['status'] = 'error';
        $response['message'] = 'Missing required fields';
        http_response_code(400);
        return;
    }
    
    try {
        // إنشاء كائن المستخدم
        // Create user object
        $user = new User();
        
        // تسجيل الدخول
        // Login
        $result = $user->login($data['email'], $data['password']);
        
        if ($result['success']) {
            $response['status'] = 'success';
            $response['message'] = 'Login successful';
            $response['data'] = [
                'user' => $result['user'],
                'token' => $result['token']
            ];
            http_response_code(200);
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Invalid credentials';
            http_response_code(401);
        }
    } catch (Exception $e) {
        $response['status'] = 'error';
        $response['message'] = 'Failed to login: ' . $e->getMessage();
        http_response_code(500);
    }
}

/**
 * تحديث مستخدم موجود
 * Update existing user
 * 
 * @param int $id معرف المستخدم
 */
function updateUser($id) {
    global $response;
    
    // التحقق من الصلاحيات
    // Check permissions
    if (!isAuthenticated() || (!hasPermission('manage_users') && getCurrentUserId() != $id)) {
        $response['status'] = 'error';
        $response['message'] = 'Unauthorized';
        http_response_code(401);
        return;
    }
    
    // الحصول على بيانات الطلب
    // Get request data
    $data = json_decode(file_get_contents('php://input'), true);
    
    try {
        // إنشاء كائن المستخدم
        // Create user object
        $user = new User();
        
        // التحقق من وجود المستخدم
        // Check if user exists
        $userData = $user->getUserById($id);
        
        if (!$userData) {
            $response['status'] = 'error';
            $response['message'] = 'User not found';
            http_response_code(404);
            return;
        }
        
        // تحديث المستخدم
        // Update user
        $result = $user->updateUser($id, $data);
        
        if ($result) {
            $response['status'] = 'success';
            $response['message'] = 'User updated successfully';
            $response['data'] = ['id' => $id];
            http_response_code(200);
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Failed to update user';
            http_response_code(500);
        }
    } catch (Exception $e) {
        $response['status'] = 'error';
        $response['message'] = 'Failed to update user: ' . $e->getMessage();
        http_response_code(500);
    }
}

/**
 * حذف مستخدم
 * Delete user
 * 
 * @param int $id معرف المستخدم
 */
function deleteUser($id) {
    global $response;
    
    try {
        // إنشاء كائن المستخدم
        // Create user object
        $user = new User();
        
        // التحقق من وجود المستخدم
        // Check if user exists
        $userData = $user->getUserById($id);
        
        if (!$userData) {
            $response['status'] = 'error';
            $response['message'] = 'User not found';
            http_response_code(404);
            return;
        }
        
        // حذف المستخدم
        // Delete user
        $result = $user->deleteUser($id);
        
        if ($result) {
            $response['status'] = 'success';
            $response['message'] = 'User deleted successfully';
            $response['data'] = null;
            http_response_code(200);
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Failed to delete user';
            http_response_code(500);
        }
    } catch (Exception $e) {
        $response['status'] = 'error';
        $response['message'] = 'Failed to delete user: ' . $e->getMessage();
        http_response_code(500);
    }
}

<?php
/**
 * واجهة برمجة التطبيقات للمدفوعات
 * Payments API
 */

// تضمين ملفات الإعدادات والوظائف المساعدة
// Include configuration and helper files
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// التحقق من طريقة الطلب
// Check request method
$method = $_SERVER['REQUEST_METHOD'];
$response = [];

// تهيئة قاعدة البيانات
// Initialize database
$db = new Database();
$conn = $db->getConnection();

// معالجة الطلب بناءً على الطريقة
// Process request based on method
switch ($method) {
    case 'GET':
        // الحصول على المدفوعات
        // Get payments
        if (isset($_GET['id']) && !empty($_GET['id'])) {
            // الحصول على مدفوعة محددة
            // Get specific payment
            $id = (int)$_GET['id'];
            $stmt = $conn->prepare("SELECT * FROM payments WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $payment = $result->fetch_assoc();
                $response = [
                    'status' => 'success',
                    'data' => $payment
                ];
            } else {
                $response = [
                    'status' => 'error',
                    'message' => 'Payment not found'
                ];
                http_response_code(404);
            }
        } elseif (isset($_GET['order_id']) && !empty($_GET['order_id'])) {
            // الحصول على مدفوعات طلب محدد
            // Get payments for specific order
            $orderId = (int)$_GET['order_id'];
            $stmt = $conn->prepare("SELECT * FROM payments WHERE order_id = ?");
            $stmt->bind_param("i", $orderId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $payments = [];
            while ($row = $result->fetch_assoc()) {
                $payments[] = $row;
            }
            
            $response = [
                'status' => 'success',
                'data' => $payments
            ];
        } elseif (isset($_GET['user_id']) && !empty($_GET['user_id'])) {
            // الحصول على مدفوعات مستخدم محدد
            // Get payments for specific user
            $userId = (int)$_GET['user_id'];
            $stmt = $conn->prepare("SELECT * FROM payments WHERE user_id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $payments = [];
            while ($row = $result->fetch_assoc()) {
                $payments[] = $row;
            }
            
            $response = [
                'status' => 'success',
                'data' => $payments
            ];
        } else {
            // الحصول على جميع المدفوعات
            // Get all payments
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
            
            $stmt = $conn->prepare("SELECT * FROM payments ORDER BY created_at DESC LIMIT ? OFFSET ?");
            $stmt->bind_param("ii", $limit, $offset);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $payments = [];
            while ($row = $result->fetch_assoc()) {
                $payments[] = $row;
            }
            
            // الحصول على العدد الإجمالي للمدفوعات
            // Get total count of payments
            $stmt = $conn->prepare("SELECT COUNT(*) as total FROM payments");
            $stmt->execute();
            $totalResult = $stmt->get_result();
            $total = $totalResult->fetch_assoc()['total'];
            
            $response = [
                'status' => 'success',
                'data' => $payments,
                'pagination' => [
                    'total' => (int)$total,
                    'limit' => $limit,
                    'offset' => $offset
                ]
            ];
        }
        break;
        
    case 'POST':
        // إنشاء مدفوعة جديدة
        // Create new payment
        $data = json_decode(file_get_contents('php://input'), true);
        
        // التحقق من البيانات المطلوبة
        // Validate required data
        if (!isset($data['order_id']) || !isset($data['user_id']) || !isset($data['amount']) || !isset($data['payment_method'])) {
            $response = [
                'status' => 'error',
                'message' => 'Missing required fields'
            ];
            http_response_code(400);
            break;
        }
        
        // إنشاء المدفوعة
        // Create payment
        $stmt = $conn->prepare("
            INSERT INTO payments (order_id, user_id, amount, payment_method, transaction_id, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $orderId = (int)$data['order_id'];
        $userId = (int)$data['user_id'];
        $amount = (float)$data['amount'];
        $paymentMethod = $data['payment_method'];
        $transactionId = isset($data['transaction_id']) ? $data['transaction_id'] : '';
        $status = isset($data['status']) ? $data['status'] : 'pending';
        
        $stmt->bind_param("iidsss", $orderId, $userId, $amount, $paymentMethod, $transactionId, $status);
        
        if ($stmt->execute()) {
            $paymentId = $conn->insert_id;
            
            // الحصول على المدفوعة المنشأة
            // Get created payment
            $stmt = $conn->prepare("SELECT * FROM payments WHERE id = ?");
            $stmt->bind_param("i", $paymentId);
            $stmt->execute();
            $result = $stmt->get_result();
            $payment = $result->fetch_assoc();
            
            $response = [
                'status' => 'success',
                'message' => 'Payment created successfully',
                'data' => $payment
            ];
            http_response_code(201);
        } else {
            $response = [
                'status' => 'error',
                'message' => 'Failed to create payment'
            ];
            http_response_code(500);
        }
        break;
        
    case 'PUT':
        // تحديث مدفوعة
        // Update payment
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            $response = [
                'status' => 'error',
                'message' => 'Payment ID is required'
            ];
            http_response_code(400);
            break;
        }
        
        $id = (int)$_GET['id'];
        $data = json_decode(file_get_contents('php://input'), true);
        
        // التحقق من وجود المدفوعة
        // Check if payment exists
        $stmt = $conn->prepare("SELECT * FROM payments WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $response = [
                'status' => 'error',
                'message' => 'Payment not found'
            ];
            http_response_code(404);
            break;
        }
        
        // بناء استعلام التحديث
        // Build update query
        $updateFields = [];
        $updateValues = [];
        $updateTypes = '';
        
        if (isset($data['amount'])) {
            $updateFields[] = 'amount = ?';
            $updateValues[] = (float)$data['amount'];
            $updateTypes .= 'd';
        }
        
        if (isset($data['payment_method'])) {
            $updateFields[] = 'payment_method = ?';
            $updateValues[] = $data['payment_method'];
            $updateTypes .= 's';
        }
        
        if (isset($data['transaction_id'])) {
            $updateFields[] = 'transaction_id = ?';
            $updateValues[] = $data['transaction_id'];
            $updateTypes .= 's';
        }
        
        if (isset($data['status'])) {
            $updateFields[] = 'status = ?';
            $updateValues[] = $data['status'];
            $updateTypes .= 's';
        }
        
        if (empty($updateFields)) {
            $response = [
                'status' => 'error',
                'message' => 'No fields to update'
            ];
            http_response_code(400);
            break;
        }
        
        // تحديث المدفوعة
        // Update payment
        $updateFields[] = 'updated_at = NOW()';
        $query = "UPDATE payments SET " . implode(', ', $updateFields) . " WHERE id = ?";
        $stmt = $conn->prepare($query);
        
        $updateValues[] = $id;
        $updateTypes .= 'i';
        
        $stmt->bind_param($updateTypes, ...$updateValues);
        
        if ($stmt->execute()) {
            // الحصول على المدفوعة المحدثة
            // Get updated payment
            $stmt = $conn->prepare("SELECT * FROM payments WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $payment = $result->fetch_assoc();
            
            $response = [
                'status' => 'success',
                'message' => 'Payment updated successfully',
                'data' => $payment
            ];
        } else {
            $response = [
                'status' => 'error',
                'message' => 'Failed to update payment'
            ];
            http_response_code(500);
        }
        break;
        
    case 'DELETE':
        // حذف مدفوعة
        // Delete payment
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            $response = [
                'status' => 'error',
                'message' => 'Payment ID is required'
            ];
            http_response_code(400);
            break;
        }
        
        $id = (int)$_GET['id'];
        
        // التحقق من وجود المدفوعة
        // Check if payment exists
        $stmt = $conn->prepare("SELECT * FROM payments WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $response = [
                'status' => 'error',
                'message' => 'Payment not found'
            ];
            http_response_code(404);
            break;
        }
        
        // حذف المدفوعة
        // Delete payment
        $stmt = $conn->prepare("DELETE FROM payments WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $response = [
                'status' => 'success',
                'message' => 'Payment deleted successfully'
            ];
        } else {
            $response = [
                'status' => 'error',
                'message' => 'Failed to delete payment'
            ];
            http_response_code(500);
        }
        break;
        
    default:
        // طريقة غير مدعومة
        // Unsupported method
        $response = [
            'status' => 'error',
            'message' => 'Method not allowed'
        ];
        http_response_code(405);
        break;
}

// إرسال الاستجابة
// Send response
header('Content-Type: application/json');
echo json_encode($response);
?>

<?php
/**
 * صفحة لوحة التحكم الرئيسية
 * Admin Dashboard Page
 */

// تضمين ملفات الإعدادات والوظائف المساعدة
// Include configuration and helper files
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

// بدء الجلسة
// Start session
session_start();

// التحقق من تسجيل الدخول والصلاحيات
// Check login and permissions
if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
    exit;
}

// تهيئة متغيرات الصفحة
// Initialize page variables
$pageTitle = 'لوحة التحكم | ' . SITE_NAME;
$activeMenu = 'dashboard';

// الحصول على إحصائيات النظام
// Get system statistics
$db = new Database();
$conn = $db->getConnection();

// إحصائيات المستخدمين
// User statistics
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM users");
$stmt->execute();
$totalUsers = $stmt->get_result()->fetch_assoc()['total'];

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
$stmt->execute();
$newUsers = $stmt->get_result()->fetch_assoc()['total'];

// إحصائيات المنتجات
// Product statistics
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM products");
$stmt->execute();
$totalProducts = $stmt->get_result()->fetch_assoc()['total'];

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM products WHERE status = 'active'");
$stmt->execute();
$activeProducts = $stmt->get_result()->fetch_assoc()['total'];

// إحصائيات الخدمات
// Service statistics
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM services");
$stmt->execute();
$totalServices = $stmt->get_result()->fetch_assoc()['total'];

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM services WHERE status = 'active'");
$stmt->execute();
$activeServices = $stmt->get_result()->fetch_assoc()['total'];

// إحصائيات الطلبات
// Order statistics
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM orders");
$stmt->execute();
$totalOrders = $stmt->get_result()->fetch_assoc()['total'];

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM orders WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
$stmt->execute();
$newOrders = $stmt->get_result()->fetch_assoc()['total'];

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM orders WHERE status = 'pending'");
$stmt->execute();
$pendingOrders = $stmt->get_result()->fetch_assoc()['total'];

// إحصائيات المدفوعات
// Payment statistics
$stmt = $conn->prepare("SELECT SUM(amount) as total FROM payments WHERE status = 'completed'");
$stmt->execute();
$totalRevenue = $stmt->get_result()->fetch_assoc()['total'] ?: 0;

$stmt = $conn->prepare("SELECT SUM(amount) as total FROM payments WHERE status = 'completed' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
$stmt->execute();
$monthlyRevenue = $stmt->get_result()->fetch_assoc()['total'] ?: 0;

// الحصول على آخر الطلبات
// Get latest orders
$stmt = $conn->prepare("
    SELECT o.*, u.name as customer_name, u.email as customer_email 
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC 
    LIMIT 5
");
$stmt->execute();
$latestOrders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// الحصول على آخر المستخدمين
// Get latest users
$stmt = $conn->prepare("SELECT * FROM users ORDER BY created_at DESC LIMIT 5");
$stmt->execute();
$latestUsers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// الحصول على آخر المدفوعات
// Get latest payments
$stmt = $conn->prepare("
    SELECT p.*, o.order_number, u.name as customer_name 
    FROM payments p 
    LEFT JOIN orders o ON p.order_id = o.id 
    LEFT JOIN users u ON p.user_id = u.id 
    ORDER BY p.created_at DESC 
    LIMIT 5
");
$stmt->execute();
$latestPayments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// الحصول على بيانات الرسم البياني للمبيعات
// Get sales chart data
$salesData = [];
$labels = [];

for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $labels[] = date('d/m', strtotime($date));
    
    $stmt = $conn->prepare("
        SELECT SUM(amount) as total 
        FROM payments 
        WHERE status = 'completed' 
        AND DATE(created_at) = ?
    ");
    $stmt->bind_param("s", $date);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $salesData[] = $result['total'] ?: 0;
}

// تضمين ملف الرأس
// Include header file
include '../admin/includes/header.php';
?>

<!-- محتوى الصفحة -->
<!-- Page Content -->
<div class="admin-content">
    <div class="container-fluid">
        <div class="admin-content-header">
            <h1>لوحة التحكم</h1>
            <p class="text-muted">مرحباً بك في لوحة تحكم <?php echo SITE_NAME; ?>. يمكنك إدارة موقعك من هنا.</p>
        </div>
        
        <!-- بطاقات الإحصائيات -->
        <!-- Statistics Cards -->
        <div class="row">
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="stat-icon bg-primary">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-details">
                            <h3 class="stat-number"><?php echo number_format($totalUsers); ?></h3>
                            <p class="stat-text">إجمالي المستخدمين</p>
                            <div class="stat-footer">
                                <span class="badge badge-success">+<?php echo $newUsers; ?></span>
                                <span class="text-muted">في آخر 30 يوم</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="stat-icon bg-success">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="stat-details">
                            <h3 class="stat-number"><?php echo number_format($totalOrders); ?></h3>
                            <p class="stat-text">إجمالي الطلبات</p>
                            <div class="stat-footer">
                                <span class="badge badge-warning"><?php echo $pendingOrders; ?></span>
                                <span class="text-muted">طلبات معلقة</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="stat-icon bg-info">
                            <i class="fas fa-box"></i>
                        </div>
                        <div class="stat-details">
                            <h3 class="stat-number"><?php echo number_format($totalProducts); ?></h3>
                            <p class="stat-text">إجمالي المنتجات</p>
                            <div class="stat-footer">
                                <span class="badge badge-success"><?php echo $activeProducts; ?></span>
                                <span class="text-muted">منتجات نشطة</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="stat-icon bg-warning">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="stat-details">
                            <h3 class="stat-number"><?php echo formatCurrency($totalRevenue); ?></h3>
                            <p class="stat-text">إجمالي الإيرادات</p>
                            <div class="stat-footer">
                                <span class="badge badge-info"><?php echo formatCurrency($monthlyRevenue); ?></span>
                                <span class="text-muted">في آخر 30 يوم</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- الرسوم البيانية -->
        <!-- Charts -->
        <div class="row">
            <div class="col-md-8 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">المبيعات الأسبوعية</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="salesChart" height="300"></canvas>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">توزيع المنتجات والخدمات</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="productsServicesChart" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- آخر الطلبات والمستخدمين -->
        <!-- Latest Orders and Users -->
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title">آخر الطلبات</h5>
                        <a href="orders/index.php" class="btn btn-sm btn-primary">عرض الكل</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>رقم الطلب</th>
                                        <th>العميل</th>
                                        <th>المبلغ</th>
                                        <th>الحالة</th>
                                        <th>التاريخ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($latestOrders) > 0): ?>
                                        <?php foreach ($latestOrders as $order): ?>
                                            <tr>
                                                <td><a href="orders/index.php?action=view&id=<?php echo $order['id']; ?>">#<?php echo $order['order_number']; ?></a></td>
                                                <td><?php echo $order['customer_name']; ?></td>
                                                <td><?php echo formatCurrency($order['total']); ?></td>
                                                <td><span class="badge badge-<?php echo getOrderStatusBadgeClass($order['status']); ?>"><?php echo translate($order['status']); ?></span></td>
                                                <td><?php echo formatDate($order['created_at']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center">لا توجد طلبات حتى الآن</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title">آخر المستخدمين</h5>
                        <a href="users/index.php" class="btn btn-sm btn-primary">عرض الكل</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>الاسم</th>
                                        <th>البريد الإلكتروني</th>
                                        <th>الدور</th>
                                        <th>الحالة</th>
                                        <th>تاريخ التسجيل</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($latestUsers) > 0): ?>
                                        <?php foreach ($latestUsers as $user): ?>
                                            <tr>
                                                <td><a href="users/index.php?action=view&id=<?php echo $user['id']; ?>"><?php echo $user['name']; ?></a></td>
                                                <td><?php echo $user['email']; ?></td>
                                                <td><?php echo translate($user['role']); ?></td>
                                                <td><span class="badge badge-<?php echo $user['status'] === 'active' ? 'success' : 'danger'; ?>"><?php echo translate($user['status']); ?></span></td>
                                                <td><?php echo formatDate($user['created_at']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center">لا يوجد مستخدمين حتى الآن</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- آخر المدفوعات -->
        <!-- Latest Payments -->
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title">آخر المدفوعات</h5>
                        <a href="payments/index.php" class="btn btn-sm btn-primary">عرض الكل</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>رقم المعاملة</th>
                                        <th>رقم الطلب</th>
                                        <th>العميل</th>
                                        <th>طريقة الدفع</th>
                                        <th>المبلغ</th>
                                        <th>الحالة</th>
                                        <th>التاريخ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($latestPayments) > 0): ?>
                                        <?php foreach ($latestPayments as $payment): ?>
                                            <tr>
                                                <td><a href="payments/index.php?action=view&id=<?php echo $payment['id']; ?>">#<?php echo $payment['id']; ?></a></td>
                                                <td><a href="orders/index.php?action=view&id=<?php echo $payment['order_id']; ?>">#<?php echo $payment['order_number']; ?></a></td>
                                                <td><?php echo $payment['customer_name']; ?></td>
                                                <td><?php echo translate($payment['payment_method']); ?></td>
                                                <td><?php echo formatCurrency($payment['amount']); ?></td>
                                                <td><span class="badge badge-<?php echo getPaymentStatusBadgeClass($payment['status']); ?>"><?php echo translate($payment['status']); ?></span></td>
                                                <td><?php echo formatDate($payment['created_at']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center">لا توجد مدفوعات حتى الآن</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- روابط سريعة -->
        <!-- Quick Links -->
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">روابط سريعة</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 col-sm-6 mb-3">
                                <a href="products/index.php?action=add" class="quick-link-card">
                                    <div class="quick-link-icon bg-primary">
                                        <i class="fas fa-plus"></i>
                                    </div>
                                    <div class="quick-link-text">
                                        <h4>إضافة منتج جديد</h4>
                                    </div>
                                </a>
                            </div>
                            
                            <div class="col-md-3 col-sm-6 mb-3">
                                <a href="services/index.php?action=add" class="quick-link-card">
                                    <div class="quick-link-icon bg-success">
                                        <i class="fas fa-plus"></i>
                                    </div>
                                    <div class="quick-link-text">
                                        <h4>إضافة خدمة جديدة</h4>
                                    </div>
                                </a>
                            </div>
                            
                            <div class="col-md-3 col-sm-6 mb-3">
                                <a href="users/index.php?action=add" class="quick-link-card">
                                    <div class="quick-link-icon bg-info">
                                        <i class="fas fa-user-plus"></i>
                                    </div>
                                    <div class="quick-link-text">
                                        <h4>إضافة مستخدم جديد</h4>
                                    </div>
                                </a>
                            </div>
                            
                            <div class="col-md-3 col-sm-6 mb-3">
                                <a href="settings/index.php" class="quick-link-card">
                                    <div class="quick-link-icon bg-warning">
                                        <i class="fas fa-cog"></i>
                                    </div>
                                    <div class="quick-link-text">
                                        <h4>إعدادات النظام</h4>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- سكريبت الرسوم البيانية -->
<!-- Charts Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // رسم بياني للمبيعات
    // Sales chart
    var salesCtx = document.getElementById('salesChart').getContext('2d');
    var salesChart = new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($labels); ?>,
            datasets: [{
                label: 'المبيعات اليومية',
                data: <?php echo json_encode($salesData); ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 2,
                pointBackgroundColor: 'rgba(54, 162, 235, 1)',
                pointBorderColor: '#fff',
                pointBorderWidth: 1,
                pointRadius: 4,
                pointHoverRadius: 6,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '<?php echo CURRENCY_SYMBOL; ?> ' + value;
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'المبيعات: <?php echo CURRENCY_SYMBOL; ?> ' + context.raw;
                        }
                    }
                }
            }
        }
    });
    
    // رسم بياني للمنتجات والخدمات
    // Products and services chart
    var productsServicesCtx = document.getElementById('productsServicesChart').getContext('2d');
    var productsServicesChart = new Chart(productsServicesCtx, {
        type: 'doughnut',
        data: {
            labels: ['المنتجات النشطة', 'المنتجات غير النشطة', 'الخدمات النشطة', 'الخدمات غير النشطة'],
            datasets: [{
                data: [
                    <?php echo $activeProducts; ?>,
                    <?php echo $totalProducts - $activeProducts; ?>,
                    <?php echo $activeServices; ?>,
                    <?php echo $totalServices - $activeServices; ?>
                ],
                backgroundColor: [
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(54, 162, 235, 0.2)',
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(75, 192, 192, 0.2)'
                ],
                borderColor: [
                    'rgba(54, 162, 235, 1)',
                    'rgba(54, 162, 235, 0.5)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(75, 192, 192, 0.5)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
});
</script>

<?php
// تضمين ملف التذييل
// Include footer file
include '../admin/includes/footer.php';

/**
 * تنسيق العملة
 * Format currency
 * 
 * @param float $amount المبلغ
 * @return string المبلغ المنسق
 */
function formatCurrency($amount) {
    return CURRENCY_SYMBOL . ' ' . number_format($amount, 2);
}

/**
 * تنسيق التاريخ
 * Format date
 * 
 * @param string $date التاريخ
 * @return string التاريخ المنسق
 */
function formatDate($date) {
    return date('d/m/Y', strtotime($date));
}

/**
 * الحصول على صنف شارة حالة الطلب
 * Get order status badge class
 * 
 * @param string $status الحالة
 * @return string صنف الشارة
 */
function getOrderStatusBadgeClass($status) {
    switch ($status) {
        case 'pending':
            return 'warning';
        case 'processing':
            return 'info';
        case 'completed':
            return 'success';
        case 'cancelled':
            return 'danger';
        case 'refunded':
            return 'secondary';
        case 'paid':
            return 'primary';
        default:
            return 'secondary';
    }
}

/**
 * الحصول على صنف شارة حالة الدفع
 * Get payment status badge class
 * 
 * @param string $status الحالة
 * @return string صنف الشارة
 */
function getPaymentStatusBadgeClass($status) {
    switch ($status) {
        case 'pending':
            return 'warning';
        case 'completed':
            return 'success';
        case 'failed':
            return 'danger';
        case 'refunded':
            return 'info';
        default:
            return 'secondary';
    }
}

/**
 * ترجمة النص
 * Translate text
 * 
 * @param string $key المفتاح
 * @return string النص المترجم
 */
function translate($key) {
    // هذه دالة وهمية، يجب استبدالها بالتنفيذ الفعلي
    // This is a dummy function, should be replaced with actual implementation
    $translations = [
        'pending' => 'معلق',
        'processing' => 'قيد المعالجة',
        'completed' => 'مكتمل',
        'cancelled' => 'ملغي',
        'refunded' => 'مسترجع',
        'paid' => 'مدفوع',
        'failed' => 'فاشل',
        'admin' => 'مدير',
        'user' => 'مستخدم',
        'staff' => 'موظف',
        'active' => 'نشط',
        'inactive' => 'غير نشط',
        'paypal' => 'باي بال',
        'stripe' => 'سترايب',
        'bank_transfer' => 'تحويل بنكي',
        'cash_on_delivery' => 'الدفع عند الاستلام'
    ];
    
    return isset($translations[$key]) ? $translations[$key] : $key;
}
?>

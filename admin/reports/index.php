<?php
/**
 * صفحة إدارة التقارير
 * Reports Management Page
 */

// تضمين ملفات الإعدادات والوظائف المساعدة
// Include configuration and helper files
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// بدء الجلسة
// Start session
session_start();

// التحقق من تسجيل الدخول والصلاحيات
// Check login and permissions
if (!isLoggedIn() || !isAdmin()) {
    redirect('../../login.php');
    exit;
}

// تهيئة متغيرات الصفحة
// Initialize page variables
$pageTitle = 'التقارير | ' . SITE_NAME;
$activeMenu = 'reports';

// تهيئة قاعدة البيانات
// Initialize database
$db = new Database();
$conn = $db->getConnection();

// تحديد نوع التقرير المطلوب
// Determine requested report type
$reportType = isset($_GET['type']) ? $_GET['type'] : 'sales';

// تهيئة متغيرات التاريخ
// Initialize date variables
$dateFrom = isset($_GET['date_from']) ? $_GET['date_from'] : date('Y-m-d', strtotime('-30 days'));
$dateTo = isset($_GET['date_to']) ? $_GET['date_to'] : date('Y-m-d');

// تضمين ملف الرأس
// Include header file
include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">التقارير</h4>
                </div>
                <div class="card-body">
                    <!-- نموذج تصفية التقارير -->
                    <!-- Reports filter form -->
                    <form method="get" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="type">نوع التقرير</label>
                                    <select name="type" id="type" class="form-control">
                                        <option value="sales" <?php echo $reportType === 'sales' ? 'selected' : ''; ?>>تقرير المبيعات</option>
                                        <option value="products" <?php echo $reportType === 'products' ? 'selected' : ''; ?>>تقرير المنتجات</option>
                                        <option value="services" <?php echo $reportType === 'services' ? 'selected' : ''; ?>>تقرير الخدمات</option>
                                        <option value="customers" <?php echo $reportType === 'customers' ? 'selected' : ''; ?>>تقرير العملاء</option>
                                        <option value="payments" <?php echo $reportType === 'payments' ? 'selected' : ''; ?>>تقرير المدفوعات</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="date_from">من تاريخ</label>
                                    <input type="date" name="date_from" id="date_from" class="form-control" value="<?php echo $dateFrom; ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="date_to">إلى تاريخ</label>
                                    <input type="date" name="date_to" id="date_to" class="form-control" value="<?php echo $dateTo; ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <button type="submit" class="btn btn-primary btn-block">عرض التقرير</button>
                                </div>
                            </div>
                        </div>
                    </form>
                    
                    <?php
                    // عرض التقرير المطلوب
                    // Display requested report
                    switch ($reportType) {
                        case 'sales':
                            // تقرير المبيعات
                            // Sales report
                            displaySalesReport($conn, $dateFrom, $dateTo);
                            break;
                            
                        case 'products':
                            // تقرير المنتجات
                            // Products report
                            displayProductsReport($conn, $dateFrom, $dateTo);
                            break;
                            
                        case 'services':
                            // تقرير الخدمات
                            // Services report
                            displayServicesReport($conn, $dateFrom, $dateTo);
                            break;
                            
                        case 'customers':
                            // تقرير العملاء
                            // Customers report
                            displayCustomersReport($conn, $dateFrom, $dateTo);
                            break;
                            
                        case 'payments':
                            // تقرير المدفوعات
                            // Payments report
                            displayPaymentsReport($conn, $dateFrom, $dateTo);
                            break;
                            
                        default:
                            // تقرير المبيعات (الافتراضي)
                            // Sales report (default)
                            displaySalesReport($conn, $dateFrom, $dateTo);
                            break;
                    }
                    ?>
                    
                    <!-- زر تصدير التقرير -->
                    <!-- Export report button -->
                    <div class="text-center mt-4">
                        <a href="export.php?type=<?php echo $reportType; ?>&date_from=<?php echo $dateFrom; ?>&date_to=<?php echo $dateTo; ?>" class="btn btn-success">
                            <i class="fa fa-download"></i> تصدير التقرير
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// تضمين ملف التذييل
// Include footer file
include '../includes/footer.php';

/**
 * عرض تقرير المبيعات
 * Display sales report
 * 
 * @param mysqli $conn اتصال قاعدة البيانات
 * @param string $dateFrom تاريخ البداية
 * @param string $dateTo تاريخ النهاية
 */
function displaySalesReport($conn, $dateFrom, $dateTo) {
    // الحصول على بيانات المبيعات
    // Get sales data
    $stmt = $conn->prepare("
        SELECT 
            DATE(o.created_at) as order_date,
            COUNT(o.id) as order_count,
            SUM(o.subtotal) as subtotal,
            SUM(o.tax) as tax,
            SUM(o.discount) as discount,
            SUM(o.total) as total
        FROM orders o
        WHERE DATE(o.created_at) BETWEEN ? AND ?
        GROUP BY DATE(o.created_at)
        ORDER BY DATE(o.created_at) DESC
    ");
    
    $stmt->bind_param("ss", $dateFrom, $dateTo);
    $stmt->execute();
    $salesData = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // حساب المجاميع
    // Calculate totals
    $totalOrders = 0;
    $totalSubtotal = 0;
    $totalTax = 0;
    $totalDiscount = 0;
    $totalAmount = 0;
    
    foreach ($salesData as $sale) {
        $totalOrders += $sale['order_count'];
        $totalSubtotal += $sale['subtotal'];
        $totalTax += $sale['tax'];
        $totalDiscount += $sale['discount'];
        $totalAmount += $sale['total'];
    }
    
    // عرض الرسم البياني
    // Display chart
    echo '<div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">رسم بياني للمبيعات</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="salesChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>';
    
    // عرض ملخص المبيعات
    // Display sales summary
    echo '<div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title">إجمالي الطلبات</h5>
                        <h3>' . $totalOrders . '</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h5 class="card-title">إجمالي المبيعات</h5>
                        <h3>' . number_format($totalAmount, 2) . '</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title">إجمالي الضرائب</h5>
                        <h3>' . number_format($totalTax, 2) . '</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <h5 class="card-title">إجمالي الخصومات</h5>
                        <h3>' . number_format($totalDiscount, 2) . '</h3>
                    </div>
                </div>
            </div>
        </div>';
    
    // عرض جدول المبيعات
    // Display sales table
    echo '<div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>التاريخ</th>
                        <th>عدد الطلبات</th>
                        <th>المجموع الفرعي</th>
                        <th>الضريبة</th>
                        <th>الخصم</th>
                        <th>الإجمالي</th>
                    </tr>
                </thead>
                <tbody>';
    
    foreach ($salesData as $sale) {
        echo '<tr>
                <td>' . date('Y-m-d', strtotime($sale['order_date'])) . '</td>
                <td>' . $sale['order_count'] . '</td>
                <td>' . number_format($sale['subtotal'], 2) . '</td>
                <td>' . number_format($sale['tax'], 2) . '</td>
                <td>' . number_format($sale['discount'], 2) . '</td>
                <td>' . number_format($sale['total'], 2) . '</td>
            </tr>';
    }
    
    echo '</tbody>
            <tfoot>
                <tr>
                    <th>الإجمالي</th>
                    <th>' . $totalOrders . '</th>
                    <th>' . number_format($totalSubtotal, 2) . '</th>
                    <th>' . number_format($totalTax, 2) . '</th>
                    <th>' . number_format($totalDiscount, 2) . '</th>
                    <th>' . number_format($totalAmount, 2) . '</th>
                </tr>
            </tfoot>
        </table>
    </div>';
    
    // إضافة سكريبت الرسم البياني
    // Add chart script
    echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            var ctx = document.getElementById("salesChart").getContext("2d");
            var salesChart = new Chart(ctx, {
                type: "line",
                data: {
                    labels: ' . json_encode(array_column(array_reverse($salesData), 'order_date')) . ',
                    datasets: [{
                        label: "المبيعات",
                        data: ' . json_encode(array_column(array_reverse($salesData), 'total')) . ',
                        backgroundColor: "rgba(54, 162, 235, 0.2)",
                        borderColor: "rgba(54, 162, 235, 1)",
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        });
    </script>';
}

/**
 * عرض تقرير المنتجات
 * Display products report
 * 
 * @param mysqli $conn اتصال قاعدة البيانات
 * @param string $dateFrom تاريخ البداية
 * @param string $dateTo تاريخ النهاية
 */
function displayProductsReport($conn, $dateFrom, $dateTo) {
    // الحصول على بيانات المنتجات
    // Get products data
    $stmt = $conn->prepare("
        SELECT 
            p.id,
            p.name,
            p.sku,
            COUNT(oi.id) as order_count,
            SUM(oi.quantity) as quantity_sold,
            SUM(oi.total) as total_sales
        FROM products p
        LEFT JOIN order_items oi ON p.id = oi.product_id
        LEFT JOIN orders o ON oi.order_id = o.id
        WHERE (DATE(o.created_at) BETWEEN ? AND ?) OR o.id IS NULL
        GROUP BY p.id, p.name, p.sku
        ORDER BY quantity_sold DESC
    ");
    
    $stmt->bind_param("ss", $dateFrom, $dateTo);
    $stmt->execute();
    $productsData = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // حساب المجاميع
    // Calculate totals
    $totalProducts = count($productsData);
    $totalQuantitySold = 0;
    $totalSales = 0;
    
    foreach ($productsData as $product) {
        $totalQuantitySold += $product['quantity_sold'];
        $totalSales += $product['total_sales'];
    }
    
    // عرض ملخص المنتجات
    // Display products summary
    echo '<div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title">إجمالي المنتجات</h5>
                        <h3>' . $totalProducts . '</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h5 class="card-title">إجمالي الكميات المباعة</h5>
                        <h3>' . $totalQuantitySold . '</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title">إجمالي المبيعات</h5>
                        <h3>' . number_format($totalSales, 2) . '</h3>
                    </div>
                </div>
            </div>
        </div>';
    
    // عرض جدول المنتجات
    // Display products table
    echo '<div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>المنتج</th>
                        <th>رمز المنتج</th>
                        <th>عدد الطلبات</th>
                        <th>الكمية المباعة</th>
                        <th>إجمالي المبيعات</th>
                    </tr>
                </thead>
                <tbody>';
    
    foreach ($productsData as $index => $product) {
        echo '<tr>
                <td>' . ($index + 1) . '</td>
                <td>' . $product['name'] . '</td>
                <td>' . $product['sku'] . '</td>
                <td>' . $product['order_count'] . '</td>
                <td>' . $product['quantity_sold'] . '</td>
                <td>' . number_format($product['total_sales'], 2) . '</td>
            </tr>';
    }
    
    echo '</tbody>
            <tfoot>
                <tr>
                    <th colspan="3">الإجمالي</th>
                    <th>-</th>
                    <th>' . $totalQuantitySold . '</th>
                    <th>' . number_format($totalSales, 2) . '</th>
                </tr>
            </tfoot>
        </table>
    </div>';
    
    // عرض الرسم البياني
    // Display chart
    echo '<div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">أفضل 10 منتجات مبيعاً</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="productsChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>';
    
    // إعداد بيانات الرسم البياني (أفضل 10 منتجات)
    // Prepare chart data (top 10 products)
    $topProducts = array_slice($productsData, 0, 10);
    
    // إضافة سكريبت الرسم البياني
    // Add chart script
    echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            var ctx = document.getElementById("productsChart").getContext("2d");
            var productsChart = new Chart(ctx, {
                type: "bar",
                data: {
                    labels: ' . json_encode(array_column($topProducts, 'name')) . ',
                    datasets: [{
                        label: "الكمية المباعة",
                        data: ' . json_encode(array_column($topProducts, 'quantity_sold')) . ',
                        backgroundColor: "rgba(54, 162, 235, 0.2)",
                        borderColor: "rgba(54, 162, 235, 1)",
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        });
    </script>';
}

/**
 * عرض تقرير الخدمات
 * Display services report
 * 
 * @param mysqli $conn اتصال قاعدة البيانات
 * @param string $dateFrom تاريخ البداية
 * @param string $dateTo تاريخ النهاية
 */
function displayServicesReport($conn, $dateFrom, $dateTo) {
    // الحصول على بيانات الخدمات
    // Get services data
    $stmt = $conn->prepare("
        SELECT 
            s.id,
            s.name,
            s.code,
            COUNT(oi.id) as order_count,
            SUM(oi.quantity) as quantity_sold,
            SUM(oi.total) as total_sales
        FROM services s
        LEFT JOIN order_items oi ON s.id = oi.service_id
        LEFT JOIN orders o ON oi.order_id = o.id
        WHERE (DATE(o.created_at) BETWEEN ? AND ?) OR o.id IS NULL
        GROUP BY s.id, s.name, s.code
        ORDER BY total_sales DESC
    ");
    
    $stmt->bind_param("ss", $dateFrom, $dateTo);
    $stmt->execute();
    $servicesData = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // حساب المجاميع
    // Calculate totals
    $totalServices = count($servicesData);
    $totalQuantitySold = 0;
    $totalSales = 0;
    
    foreach ($servicesData as $service) {
        $totalQuantitySold += $service['quantity_sold'];
        $totalSales += $service['total_sales'];
    }
    
    // عرض ملخص الخدمات
    // Display services summary
    echo '<div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title">إجمالي الخدمات</h5>
                        <h3>' . $totalServices . '</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h5 class="card-title">إجمالي الخدمات المباعة</h5>
                        <h3>' . $totalQuantitySold . '</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title">إجمالي المبيعات</h5>
                        <h3>' . number_format($totalSales, 2) . '</h3>
                    </div>
                </div>
            </div>
        </div>';
    
    // عرض جدول الخدمات
    // Display services table
    echo '<div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>الخدمة</th>
                        <th>رمز الخدمة</th>
                        <th>عدد الطلبات</th>
                        <th>الكمية المباعة</th>
                        <th>إجمالي المبيعات</th>
                    </tr>
                </thead>
                <tbody>';
    
    foreach ($servicesData as $index => $service) {
        echo '<tr>
                <td>' . ($index + 1) . '</td>
                <td>' . $service['name'] . '</td>
                <td>' . $service['code'] . '</td>
                <td>' . $service['order_count'] . '</td>
                <td>' . $service['quantity_sold'] . '</td>
                <td>' . number_format($service['total_sales'], 2) . '</td>
            </tr>';
    }
    
    echo '</tbody>
            <tfoot>
                <tr>
                    <th colspan="3">الإجمالي</th>
                    <th>-</th>
                    <th>' . $totalQuantitySold . '</th>
                    <th>' . number_format($totalSales, 2) . '</th>
                </tr>
            </tfoot>
        </table>
    </div>';
    
    // عرض الرسم البياني
    // Display chart
    echo '<div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">أفضل 10 خدمات مبيعاً</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="servicesChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>';
    
    // إعداد بيانات الرسم البياني (أفضل 10 خدمات)
    // Prepare chart data (top 10 services)
    $topServices = array_slice($servicesData, 0, 10);
    
    // إضافة سكريبت الرسم البياني
    // Add chart script
    echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            var ctx = document.getElementById("servicesChart").getContext("2d");
            var servicesChart = new Chart(ctx, {
                type: "bar",
                data: {
                    labels: ' . json_encode(array_column($topServices, 'name')) . ',
                    datasets: [{
                        label: "إجمالي المبيعات",
                        data: ' . json_encode(array_column($topServices, 'total_sales')) . ',
                        backgroundColor: "rgba(75, 192, 192, 0.2)",
                        borderColor: "rgba(75, 192, 192, 1)",
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        });
    </script>';
}

/**
 * عرض تقرير العملاء
 * Display customers report
 * 
 * @param mysqli $conn اتصال قاعدة البيانات
 * @param string $dateFrom تاريخ البداية
 * @param string $dateTo تاريخ النهاية
 */
function displayCustomersReport($conn, $dateFrom, $dateTo) {
    // الحصول على بيانات العملاء
    // Get customers data
    $stmt = $conn->prepare("
        SELECT 
            u.id,
            u.name,
            u.email,
            COUNT(o.id) as order_count,
            SUM(o.total) as total_spent,
            MAX(o.created_at) as last_order_date
        FROM users u
        LEFT JOIN orders o ON u.id = o.user_id
        WHERE (DATE(o.created_at) BETWEEN ? AND ?) OR o.id IS NULL
        GROUP BY u.id, u.name, u.email
        ORDER BY total_spent DESC
    ");
    
    $stmt->bind_param("ss", $dateFrom, $dateTo);
    $stmt->execute();
    $customersData = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // حساب المجاميع
    // Calculate totals
    $totalCustomers = count($customersData);
    $totalOrders = 0;
    $totalSpent = 0;
    
    foreach ($customersData as $customer) {
        $totalOrders += $customer['order_count'];
        $totalSpent += $customer['total_spent'];
    }
    
    // عرض ملخص العملاء
    // Display customers summary
    echo '<div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title">إجمالي العملاء</h5>
                        <h3>' . $totalCustomers . '</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h5 class="card-title">إجمالي الطلبات</h5>
                        <h3>' . $totalOrders . '</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title">إجمالي المبالغ المنفقة</h5>
                        <h3>' . number_format($totalSpent, 2) . '</h3>
                    </div>
                </div>
            </div>
        </div>';
    
    // عرض جدول العملاء
    // Display customers table
    echo '<div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>العميل</th>
                        <th>البريد الإلكتروني</th>
                        <th>عدد الطلبات</th>
                        <th>إجمالي المبالغ المنفقة</th>
                        <th>تاريخ آخر طلب</th>
                    </tr>
                </thead>
                <tbody>';
    
    foreach ($customersData as $index => $customer) {
        echo '<tr>
                <td>' . ($index + 1) . '</td>
                <td>' . $customer['name'] . '</td>
                <td>' . $customer['email'] . '</td>
                <td>' . $customer['order_count'] . '</td>
                <td>' . number_format($customer['total_spent'], 2) . '</td>
                <td>' . ($customer['last_order_date'] ? date('Y-m-d', strtotime($customer['last_order_date'])) : '-') . '</td>
            </tr>';
    }
    
    echo '</tbody>
            <tfoot>
                <tr>
                    <th colspan="3">الإجمالي</th>
                    <th>' . $totalOrders . '</th>
                    <th>' . number_format($totalSpent, 2) . '</th>
                    <th>-</th>
                </tr>
            </tfoot>
        </table>
    </div>';
    
    // عرض الرسم البياني
    // Display chart
    echo '<div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">أفضل 10 عملاء من حيث الإنفاق</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="customersChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>';
    
    // إعداد بيانات الرسم البياني (أفضل 10 عملاء)
    // Prepare chart data (top 10 customers)
    $topCustomers = array_slice($customersData, 0, 10);
    
    // إضافة سكريبت الرسم البياني
    // Add chart script
    echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            var ctx = document.getElementById("customersChart").getContext("2d");
            var customersChart = new Chart(ctx, {
                type: "bar",
                data: {
                    labels: ' . json_encode(array_column($topCustomers, 'name')) . ',
                    datasets: [{
                        label: "إجمالي المبالغ المنفقة",
                        data: ' . json_encode(array_column($topCustomers, 'total_spent')) . ',
                        backgroundColor: "rgba(153, 102, 255, 0.2)",
                        borderColor: "rgba(153, 102, 255, 1)",
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        });
    </script>';
}

/**
 * عرض تقرير المدفوعات
 * Display payments report
 * 
 * @param mysqli $conn اتصال قاعدة البيانات
 * @param string $dateFrom تاريخ البداية
 * @param string $dateTo تاريخ النهاية
 */
function displayPaymentsReport($conn, $dateFrom, $dateTo) {
    // الحصول على بيانات المدفوعات حسب طريقة الدفع
    // Get payments data by payment method
    $stmt = $conn->prepare("
        SELECT 
            payment_method,
            COUNT(id) as payment_count,
            SUM(amount) as total_amount
        FROM payments
        WHERE DATE(created_at) BETWEEN ? AND ?
        GROUP BY payment_method
        ORDER BY total_amount DESC
    ");
    
    $stmt->bind_param("ss", $dateFrom, $dateTo);
    $stmt->execute();
    $paymentMethodsData = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // الحصول على بيانات المدفوعات حسب الحالة
    // Get payments data by status
    $stmt = $conn->prepare("
        SELECT 
            status,
            COUNT(id) as payment_count,
            SUM(amount) as total_amount
        FROM payments
        WHERE DATE(created_at) BETWEEN ? AND ?
        GROUP BY status
        ORDER BY total_amount DESC
    ");
    
    $stmt->bind_param("ss", $dateFrom, $dateTo);
    $stmt->execute();
    $paymentStatusData = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // الحصول على بيانات المدفوعات حسب التاريخ
    // Get payments data by date
    $stmt = $conn->prepare("
        SELECT 
            DATE(created_at) as payment_date,
            COUNT(id) as payment_count,
            SUM(amount) as total_amount
        FROM payments
        WHERE DATE(created_at) BETWEEN ? AND ?
        GROUP BY DATE(created_at)
        ORDER BY DATE(created_at) DESC
    ");
    
    $stmt->bind_param("ss", $dateFrom, $dateTo);
    $stmt->execute();
    $paymentDateData = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // حساب المجاميع
    // Calculate totals
    $totalPayments = 0;
    $totalAmount = 0;
    
    foreach ($paymentDateData as $payment) {
        $totalPayments += $payment['payment_count'];
        $totalAmount += $payment['total_amount'];
    }
    
    // عرض ملخص المدفوعات
    // Display payments summary
    echo '<div class="row mb-4">
            <div class="col-md-6">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title">إجمالي المدفوعات</h5>
                        <h3>' . $totalPayments . '</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title">إجمالي المبالغ</h5>
                        <h3>' . number_format($totalAmount, 2) . '</h3>
                    </div>
                </div>
            </div>
        </div>';
    
    // عرض الرسوم البيانية
    // Display charts
    echo '<div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">المدفوعات حسب طريقة الدفع</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="paymentMethodChart" width="400" height="300"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">المدفوعات حسب الحالة</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="paymentStatusChart" width="400" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>';
    
    // عرض جدول المدفوعات حسب التاريخ
    // Display payments table by date
    echo '<div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>التاريخ</th>
                        <th>عدد المدفوعات</th>
                        <th>إجمالي المبالغ</th>
                    </tr>
                </thead>
                <tbody>';
    
    foreach ($paymentDateData as $payment) {
        echo '<tr>
                <td>' . date('Y-m-d', strtotime($payment['payment_date'])) . '</td>
                <td>' . $payment['payment_count'] . '</td>
                <td>' . number_format($payment['total_amount'], 2) . '</td>
            </tr>';
    }
    
    echo '</tbody>
            <tfoot>
                <tr>
                    <th>الإجمالي</th>
                    <th>' . $totalPayments . '</th>
                    <th>' . number_format($totalAmount, 2) . '</th>
                </tr>
            </tfoot>
        </table>
    </div>';
    
    // عرض جدول المدفوعات حسب طريقة الدفع
    // Display payments table by payment method
    echo '<div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">المدفوعات حسب طريقة الدفع</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>طريقة الدفع</th>
                                        <th>عدد المدفوعات</th>
                                        <th>إجمالي المبالغ</th>
                                    </tr>
                                </thead>
                                <tbody>';
    
    foreach ($paymentMethodsData as $payment) {
        echo '<tr>
                <td>' . $payment['payment_method'] . '</td>
                <td>' . $payment['payment_count'] . '</td>
                <td>' . number_format($payment['total_amount'], 2) . '</td>
            </tr>';
    }
    
    echo '</tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>';
    
    // عرض جدول المدفوعات حسب الحالة
    // Display payments table by status
    echo '<div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">المدفوعات حسب الحالة</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>الحالة</th>
                                        <th>عدد المدفوعات</th>
                                        <th>إجمالي المبالغ</th>
                                    </tr>
                                </thead>
                                <tbody>';
    
    foreach ($paymentStatusData as $payment) {
        echo '<tr>
                <td>' . $payment['status'] . '</td>
                <td>' . $payment['payment_count'] . '</td>
                <td>' . number_format($payment['total_amount'], 2) . '</td>
            </tr>';
    }
    
    echo '</tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>';
    
    // إضافة سكريبت الرسوم البيانية
    // Add charts script
    echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            // رسم بياني لطرق الدفع
            // Payment methods chart
            var methodCtx = document.getElementById("paymentMethodChart").getContext("2d");
            var methodChart = new Chart(methodCtx, {
                type: "pie",
                data: {
                    labels: ' . json_encode(array_column($paymentMethodsData, 'payment_method')) . ',
                    datasets: [{
                        data: ' . json_encode(array_column($paymentMethodsData, 'total_amount')) . ',
                        backgroundColor: [
                            "rgba(255, 99, 132, 0.2)",
                            "rgba(54, 162, 235, 0.2)",
                            "rgba(255, 206, 86, 0.2)",
                            "rgba(75, 192, 192, 0.2)",
                            "rgba(153, 102, 255, 0.2)"
                        ],
                        borderColor: [
                            "rgba(255, 99, 132, 1)",
                            "rgba(54, 162, 235, 1)",
                            "rgba(255, 206, 86, 1)",
                            "rgba(75, 192, 192, 1)",
                            "rgba(153, 102, 255, 1)"
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true
                }
            });
            
            // رسم بياني لحالات الدفع
            // Payment status chart
            var statusCtx = document.getElementById("paymentStatusChart").getContext("2d");
            var statusChart = new Chart(statusCtx, {
                type: "pie",
                data: {
                    labels: ' . json_encode(array_column($paymentStatusData, 'status')) . ',
                    datasets: [{
                        data: ' . json_encode(array_column($paymentStatusData, 'total_amount')) . ',
                        backgroundColor: [
                            "rgba(75, 192, 192, 0.2)",
                            "rgba(255, 99, 132, 0.2)",
                            "rgba(255, 206, 86, 0.2)"
                        ],
                        borderColor: [
                            "rgba(75, 192, 192, 1)",
                            "rgba(255, 99, 132, 1)",
                            "rgba(255, 206, 86, 1)"
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true
                }
            });
        });
    </script>';
}
?>

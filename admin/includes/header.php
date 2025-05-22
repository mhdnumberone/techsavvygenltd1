<?php
/**
 * ملف رأس لوحة التحكم
 * Admin Header File
 */

// التحقق من تسجيل الدخول والصلاحيات
// Check login and permissions
if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
    exit;
}

// الحصول على بيانات المستخدم الحالي
// Get current user data
$currentUser = getCurrentUser();

// الحصول على عدد الإشعارات غير المقروءة
// Get unread notifications count
$db = new Database();
$conn = $db->getConnection();
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0");
$stmt->bind_param("i", $currentUser['id']);
$stmt->execute();
$unreadNotifications = $stmt->get_result()->fetch_assoc()['count'];

// الحصول على الإشعارات الأخيرة
// Get latest notifications
$stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt->bind_param("i", $currentUser['id']);
$stmt->execute();
$latestNotifications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    
    <!-- الأنماط -->
    <!-- Styles -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap-rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/admin.css">
    
    <!-- السكريبتات -->
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>
</head>
<body>
    <!-- الرأس -->
    <!-- Header -->
    <header class="admin-header">
        <div class="container-fluid">
            <div class="admin-header-inner">
                <div class="admin-logo">
                    <a href="<?php echo SITE_URL; ?>/admin/index.php">
                        <img src="<?php echo SITE_URL; ?>/assets/images/logo.png" alt="<?php echo SITE_NAME; ?>" class="img-fluid">
                    </a>
                    <button class="sidebar-toggle" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
                
                <div class="admin-header-right">
                    <!-- البحث -->
                    <!-- Search -->
                    <div class="admin-search">
                        <form action="<?php echo SITE_URL; ?>/admin/search.php" method="get">
                            <div class="input-group">
                                <input type="text" name="q" class="form-control" placeholder="بحث...">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <!-- الإشعارات -->
                    <!-- Notifications -->
                    <div class="admin-notifications dropdown">
                        <button class="btn btn-link dropdown-toggle" type="button" id="notificationsDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-bell"></i>
                            <?php if ($unreadNotifications > 0): ?>
                                <span class="badge badge-danger"><?php echo $unreadNotifications; ?></span>
                            <?php endif; ?>
                        </button>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="notificationsDropdown">
                            <div class="dropdown-header">
                                <span>الإشعارات</span>
                                <?php if ($unreadNotifications > 0): ?>
                                    <a href="<?php echo SITE_URL; ?>/admin/notifications/mark-all-read.php" class="text-primary">تعيين الكل كمقروء</a>
                                <?php endif; ?>
                            </div>
                            <div class="dropdown-divider"></div>
                            <?php if (count($latestNotifications) > 0): ?>
                                <?php foreach ($latestNotifications as $notification): ?>
                                    <a class="dropdown-item <?php echo $notification['is_read'] ? '' : 'unread'; ?>" href="<?php echo SITE_URL; ?>/admin/notifications/view.php?id=<?php echo $notification['id']; ?>">
                                        <div class="notification-icon">
                                            <i class="<?php echo getNotificationIcon($notification['type']); ?>"></i>
                                        </div>
                                        <div class="notification-content">
                                            <p class="notification-text"><?php echo $notification['message']; ?></p>
                                            <p class="notification-time"><?php echo timeAgo($notification['created_at']); ?></p>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-center" href="<?php echo SITE_URL; ?>/admin/notifications/index.php">عرض جميع الإشعارات</a>
                            <?php else: ?>
                                <div class="dropdown-item text-center">لا توجد إشعارات</div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- الملف الشخصي -->
                    <!-- Profile -->
                    <div class="admin-profile dropdown">
                        <button class="btn btn-link dropdown-toggle" type="button" id="profileDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <div class="profile-image">
                                <?php if (!empty($currentUser['profile_image'])): ?>
                                    <img src="<?php echo SITE_URL; ?>/assets/images/users/<?php echo $currentUser['profile_image']; ?>" alt="<?php echo $currentUser['name']; ?>" class="img-fluid rounded-circle">
                                <?php else: ?>
                                    <div class="profile-initial"><?php echo substr($currentUser['name'], 0, 1); ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="profile-info">
                                <span class="profile-name"><?php echo $currentUser['name']; ?></span>
                                <span class="profile-role"><?php echo translate($currentUser['role']); ?></span>
                            </div>
                        </button>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="profileDropdown">
                            <a class="dropdown-item" href="<?php echo SITE_URL; ?>/admin/profile.php">
                                <i class="fas fa-user"></i> الملف الشخصي
                            </a>
                            <a class="dropdown-item" href="<?php echo SITE_URL; ?>/admin/settings/index.php">
                                <i class="fas fa-cog"></i> الإعدادات
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="<?php echo SITE_URL; ?>/logout.php">
                                <i class="fas fa-sign-out-alt"></i> تسجيل الخروج
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
    
    <!-- الشريط الجانبي والمحتوى -->
    <!-- Sidebar and Content -->
    <div class="admin-container">
        <!-- الشريط الجانبي -->
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="sidebar-inner">
                <nav class="sidebar-nav">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link <?php echo $activeMenu === 'dashboard' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/admin/index.php">
                                <i class="fas fa-tachometer-alt"></i> لوحة التحكم
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link <?php echo $activeMenu === 'products' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/admin/products/index.php">
                                <i class="fas fa-box"></i> المنتجات
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link <?php echo $activeMenu === 'services' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/admin/services/index.php">
                                <i class="fas fa-cogs"></i> الخدمات
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link <?php echo $activeMenu === 'orders' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/admin/orders/index.php">
                                <i class="fas fa-shopping-cart"></i> الطلبات
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link <?php echo $activeMenu === 'payments' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/admin/payments/index.php">
                                <i class="fas fa-money-bill-wave"></i> المدفوعات
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link <?php echo $activeMenu === 'invoices' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/admin/invoices/index.php">
                                <i class="fas fa-file-invoice"></i> الفواتير
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link <?php echo $activeMenu === 'users' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/admin/users/index.php">
                                <i class="fas fa-users"></i> المستخدمين
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link <?php echo $activeMenu === 'reviews' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/admin/reviews/index.php">
                                <i class="fas fa-star"></i> المراجعات
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link <?php echo $activeMenu === 'achievements' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/admin/achievements/index.php">
                                <i class="fas fa-trophy"></i> الإنجازات
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link <?php echo $activeMenu === 'offers' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/admin/offers/index.php">
                                <i class="fas fa-gift"></i> العروض
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link <?php echo $activeMenu === 'notifications' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/admin/notifications/index.php">
                                <i class="fas fa-bell"></i> الإشعارات
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link <?php echo $activeMenu === 'support' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/admin/support/index.php">
                                <i class="fas fa-headset"></i> الدعم الفني
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link <?php echo $activeMenu === 'settings' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/admin/settings/index.php">
                                <i class="fas fa-cog"></i> الإعدادات
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </aside>
        
        <!-- المحتوى الرئيسي -->
        <!-- Main Content -->
        <main class="admin-main">
<?php
/**
 * الحصول على أيقونة الإشعار
 * Get notification icon
 * 
 * @param string $type نوع الإشعار
 * @return string صنف الأيقونة
 */
function getNotificationIcon($type) {
    switch ($type) {
        case 'order':
            return 'fas fa-shopping-cart text-primary';
        case 'payment':
            return 'fas fa-money-bill-wave text-success';
        case 'user':
            return 'fas fa-user text-info';
        case 'product':
            return 'fas fa-box text-warning';
        case 'service':
            return 'fas fa-cogs text-danger';
        case 'review':
            return 'fas fa-star text-warning';
        case 'support':
            return 'fas fa-headset text-info';
        case 'system':
            return 'fas fa-cog text-secondary';
        default:
            return 'fas fa-bell text-primary';
    }
}

/**
 * الحصول على الوقت المنقضي
 * Get time ago
 * 
 * @param string $datetime التاريخ والوقت
 * @return string الوقت المنقضي
 */
function timeAgo($datetime) {
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 60) {
        return 'منذ لحظات';
    } elseif ($diff < 3600) {
        $minutes = floor($diff / 60);
        return 'منذ ' . $minutes . ' ' . ($minutes > 1 ? 'دقائق' : 'دقيقة');
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return 'منذ ' . $hours . ' ' . ($hours > 1 ? 'ساعات' : 'ساعة');
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return 'منذ ' . $days . ' ' . ($days > 1 ? 'أيام' : 'يوم');
    } else {
        return date('d/m/Y', $time);
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
        'admin' => 'مدير',
        'user' => 'مستخدم',
        'staff' => 'موظف',
        'editor' => 'محرر',
        'manager' => 'مدير',
        'customer' => 'عميل',
        'vendor' => 'بائع'
    ];
    
    return isset($translations[$key]) ? $translations[$key] : $key;
}
?>

<?php
/**
 * قالب عرض تفاصيل المستخدم
 * View User Details Template
 */
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title">تفاصيل المستخدم</h4>
                    <div>
                        <a href="index.php?action=edit&id=<?php echo $user['id']; ?>" class="btn btn-primary">
                            <i class="fa fa-edit"></i> تعديل
                        </a>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fa fa-arrow-left"></i> العودة إلى القائمة
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center mb-4">
                                <?php if (!empty($user['profile_image'])): ?>
                                    <img src="<?php echo SITE_URL . '/uploads/users/' . $user['profile_image']; ?>" alt="<?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>" class="img-fluid rounded-circle" style="max-width: 200px;">
                                <?php else: ?>
                                    <div class="text-center text-muted">
                                        <i class="fa fa-user-circle fa-8x"></i>
                                    </div>
                                <?php endif; ?>
                                <h3 class="mt-3"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h3>
                                <?php
                                $roleClass = '';
                                $roleText = '';
                                switch ($user['role']) {
                                    case 'admin':
                                        $roleClass = 'bg-danger';
                                        $roleText = 'مدير';
                                        break;
                                    case 'manager':
                                        $roleClass = 'bg-warning text-dark';
                                        $roleText = 'مشرف';
                                        break;
                                    case 'editor':
                                        $roleClass = 'bg-info';
                                        $roleText = 'محرر';
                                        break;
                                    case 'customer':
                                        $roleClass = 'bg-primary';
                                        $roleText = 'عميل';
                                        break;
                                    default:
                                        $roleClass = 'bg-secondary';
                                        $roleText = $user['role'];
                                }
                                
                                $statusClass = '';
                                $statusText = '';
                                switch ($user['status']) {
                                    case 'active':
                                        $statusClass = 'bg-success';
                                        $statusText = 'نشط';
                                        break;
                                    case 'inactive':
                                        $statusClass = 'bg-secondary';
                                        $statusText = 'غير نشط';
                                        break;
                                    case 'pending':
                                        $statusClass = 'bg-warning text-dark';
                                        $statusText = 'قيد الانتظار';
                                        break;
                                    case 'blocked':
                                        $statusClass = 'bg-danger';
                                        $statusText = 'محظور';
                                        break;
                                    default:
                                        $statusClass = 'bg-secondary';
                                        $statusText = $user['status'];
                                }
                                ?>
                                <div class="mt-2">
                                    <span class="badge <?php echo $roleClass; ?> me-2"><?php echo $roleText; ?></span>
                                    <span class="badge <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                                </div>
                            </div>
                            
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">معلومات الاتصال</h5>
                                </div>
                                <div class="card-body">
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item">
                                            <i class="fa fa-envelope me-2"></i>
                                            <strong>البريد الإلكتروني:</strong>
                                            <a href="mailto:<?php echo htmlspecialchars($user['email']); ?>"><?php echo htmlspecialchars($user['email']); ?></a>
                                        </li>
                                        <?php if (!empty($user['phone'])): ?>
                                            <li class="list-group-item">
                                                <i class="fa fa-phone me-2"></i>
                                                <strong>الهاتف:</strong>
                                                <a href="tel:<?php echo htmlspecialchars($user['phone']); ?>"><?php echo htmlspecialchars($user['phone']); ?></a>
                                            </li>
                                        <?php endif; ?>
                                        <?php if (!empty($user['address'])): ?>
                                            <li class="list-group-item">
                                                <i class="fa fa-map-marker me-2"></i>
                                                <strong>العنوان:</strong>
                                                <div class="mt-1"><?php echo nl2br(htmlspecialchars($user['address'])); ?></div>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </div>
                            
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">الإجراءات</h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <a href="index.php?action=edit&id=<?php echo $user['id']; ?>" class="btn btn-primary">
                                            <i class="fa fa-edit"></i> تعديل المستخدم
                                        </a>
                                        
                                        <?php if ($user['status'] === 'active'): ?>
                                            <a href="index.php?action=change_status&id=<?php echo $user['id']; ?>&status=inactive" class="btn btn-warning">
                                                <i class="fa fa-ban"></i> إلغاء تنشيط المستخدم
                                            </a>
                                        <?php else: ?>
                                            <a href="index.php?action=change_status&id=<?php echo $user['id']; ?>&status=active" class="btn btn-success">
                                                <i class="fa fa-check"></i> تنشيط المستخدم
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php if ($user['status'] === 'blocked'): ?>
                                            <a href="index.php?action=change_status&id=<?php echo $user['id']; ?>&status=active" class="btn btn-info">
                                                <i class="fa fa-unlock"></i> إلغاء حظر المستخدم
                                            </a>
                                        <?php else: ?>
                                            <a href="index.php?action=change_status&id=<?php echo $user['id']; ?>&status=blocked" class="btn btn-danger">
                                                <i class="fa fa-lock"></i> حظر المستخدم
                                            </a>
                                        <?php endif; ?>
                                        
                                        <a href="index.php?action=reset_password&id=<?php echo $user['id']; ?>" class="btn btn-secondary" onclick="return confirm('هل أنت متأكد من إعادة تعيين كلمة المرور لهذا المستخدم؟')">
                                            <i class="fa fa-key"></i> إعادة تعيين كلمة المرور
                                        </a>
                                        
                                        <a href="index.php?action=delete&id=<?php echo $user['id']; ?>" class="btn btn-danger" onclick="return confirm('هل أنت متأكد من حذف هذا المستخدم؟')">
                                            <i class="fa fa-trash"></i> حذف المستخدم
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-8">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">معلومات المستخدم</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered">
                                        <tbody>
                                            <tr>
                                                <th width="30%">المعرف</th>
                                                <td><?php echo $user['id']; ?></td>
                                            </tr>
                                            <tr>
                                                <th>الاسم الأول</th>
                                                <td><?php echo htmlspecialchars($user['first_name']); ?></td>
                                            </tr>
                                            <tr>
                                                <th>الاسم الأخير</th>
                                                <td><?php echo htmlspecialchars($user['last_name']); ?></td>
                                            </tr>
                                            <tr>
                                                <th>البريد الإلكتروني</th>
                                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            </tr>
                                            <tr>
                                                <th>الهاتف</th>
                                                <td><?php echo !empty($user['phone']) ? htmlspecialchars($user['phone']) : '<span class="text-muted">غير متوفر</span>'; ?></td>
                                            </tr>
                                            <tr>
                                                <th>الدور</th>
                                                <td><span class="badge <?php echo $roleClass; ?>"><?php echo $roleText; ?></span></td>
                                            </tr>
                                            <tr>
                                                <th>الحالة</th>
                                                <td><span class="badge <?php echo $statusClass; ?>"><?php echo $statusText; ?></span></td>
                                            </tr>
                                            <tr>
                                                <th>تاريخ التسجيل</th>
                                                <td><?php echo date('Y-m-d H:i', strtotime($user['created_at'])); ?></td>
                                            </tr>
                                            <tr>
                                                <th>آخر تحديث</th>
                                                <td><?php echo date('Y-m-d H:i', strtotime($user['updated_at'])); ?></td>
                                            </tr>
                                            <tr>
                                                <th>آخر تسجيل دخول</th>
                                                <td>
                                                    <?php echo !empty($user['last_login']) ? date('Y-m-d H:i', strtotime($user['last_login'])) : '<span class="text-muted">لم يسجل الدخول بعد</span>'; ?>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            <?php if (!empty($user['bio'])): ?>
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="mb-0">نبذة تعريفية</h5>
                                    </div>
                                    <div class="card-body">
                                        <?php echo nl2br(htmlspecialchars($user['bio'])); ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">إحصائيات المستخدم</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="card mb-3 bg-light">
                                                <div class="card-body text-center">
                                                    <h1 class="display-4"><?php echo $stats['orders_count']; ?></h1>
                                                    <p class="mb-0">إجمالي الطلبات</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="card mb-3 bg-light">
                                                <div class="card-body text-center">
                                                    <h1 class="display-4">$<?php echo number_format($stats['total_spent'], 2); ?></h1>
                                                    <p class="mb-0">إجمالي المصروفات</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="card mb-3 bg-light">
                                                <div class="card-body text-center">
                                                    <h2><?php echo $stats['reviews_count']; ?></h2>
                                                    <p class="mb-0">المراجعات</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="card mb-3 bg-light">
                                                <div class="card-body text-center">
                                                    <h2><?php echo $stats['comments_count']; ?></h2>
                                                    <p class="mb-0">التعليقات</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="card mb-3 bg-light">
                                                <div class="card-body text-center">
                                                    <h2><?php echo $stats['support_tickets_count']; ?></h2>
                                                    <p class="mb-0">تذاكر الدعم</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card mb-4">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">آخر الطلبات</h5>
                                    <a href="../orders/index.php?user_id=<?php echo $user['id']; ?>" class="btn btn-sm btn-primary">عرض جميع الطلبات</a>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($recent_orders)): ?>
                                        <p class="text-center text-muted">لا توجد طلبات حتى الآن.</p>
                                    <?php else: ?>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>رقم الطلب</th>
                                                        <th>التاريخ</th>
                                                        <th>المبلغ</th>
                                                        <th>الحالة</th>
                                                        <th>الإجراءات</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($recent_orders as $order): ?>
                                                        <tr>
                                                            <td><?php echo $order['order_number']; ?></td>
                                                            <td><?php echo date('Y-m-d', strtotime($order['created_at'])); ?></td>
                                                            <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                                            <td>
                                                                <?php
                                                                $orderStatusClass = '';
                                                                $orderStatusText = '';
                                                                switch ($order['status']) {
                                                                    case 'pending':
                                                                        $orderStatusClass = 'bg-warning text-dark';
                                                                        $orderStatusText = 'قيد الانتظار';
                                                                        break;
                                                                    case 'processing':
                                                                        $orderStatusClass = 'bg-info';
                                                                        $orderStatusText = 'قيد المعالجة';
                                                                        break;
                                                                    case 'completed':
                                                                        $orderStatusClass = 'bg-success';
                                                                        $orderStatusText = 'مكتمل';
                                                                        break;
                                                                    case 'cancelled':
                                                                        $orderStatusClass = 'bg-danger';
                                                                        $orderStatusText = 'ملغي';
                                                                        break;
                                                                    default:
                                                                        $orderStatusClass = 'bg-secondary';
                                                                        $orderStatusText = $order['status'];
                                                                }
                                                                ?>
                                                                <span class="badge <?php echo $orderStatusClass; ?>"><?php echo $orderStatusText; ?></span>
                                                            </td>
                                                            <td>
                                                                <a href="../orders/index.php?action=view&id=<?php echo $order['id']; ?>" class="btn btn-sm btn-info">
                                                                    <i class="fa fa-eye"></i> عرض
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

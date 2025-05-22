<?php
/**
 * قالب قائمة المستخدمين
 * Users List Template
 */
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title">إدارة المستخدمين</h4>
                    <a href="index.php?action=add" class="btn btn-primary">
                        <i class="fa fa-plus"></i> إضافة مستخدم جديد
                    </a>
                </div>
                <div class="card-body">
                    <!-- نموذج البحث والتصفية -->
                    <!-- Search and filter form -->
                    <form method="get" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="search">بحث</label>
                                    <input type="text" name="search" id="search" class="form-control" value="<?php echo htmlspecialchars($search); ?>" placeholder="الاسم، البريد الإلكتروني، الهاتف...">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="role">الدور</label>
                                    <select name="role" id="role" class="form-control">
                                        <option value="">الكل</option>
                                        <option value="admin" <?php echo $role === 'admin' ? 'selected' : ''; ?>>مدير</option>
                                        <option value="manager" <?php echo $role === 'manager' ? 'selected' : ''; ?>>مشرف</option>
                                        <option value="editor" <?php echo $role === 'editor' ? 'selected' : ''; ?>>محرر</option>
                                        <option value="customer" <?php echo $role === 'customer' ? 'selected' : ''; ?>>عميل</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="status">الحالة</label>
                                    <select name="status" id="status" class="form-control">
                                        <option value="">الكل</option>
                                        <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>نشط</option>
                                        <option value="inactive" <?php echo $status === 'inactive' ? 'selected' : ''; ?>>غير نشط</option>
                                        <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>قيد الانتظار</option>
                                        <option value="blocked" <?php echo $status === 'blocked' ? 'selected' : ''; ?>>محظور</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="date_range">تاريخ التسجيل</label>
                                    <select name="date_range" id="date_range" class="form-control">
                                        <option value="">الكل</option>
                                        <option value="today" <?php echo $date_range === 'today' ? 'selected' : ''; ?>>اليوم</option>
                                        <option value="yesterday" <?php echo $date_range === 'yesterday' ? 'selected' : ''; ?>>الأمس</option>
                                        <option value="this_week" <?php echo $date_range === 'this_week' ? 'selected' : ''; ?>>هذا الأسبوع</option>
                                        <option value="this_month" <?php echo $date_range === 'this_month' ? 'selected' : ''; ?>>هذا الشهر</option>
                                        <option value="last_month" <?php echo $date_range === 'last_month' ? 'selected' : ''; ?>>الشهر الماضي</option>
                                        <option value="this_year" <?php echo $date_range === 'this_year' ? 'selected' : ''; ?>>هذا العام</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <div>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fa fa-search"></i> بحث
                                        </button>
                                        <a href="index.php" class="btn btn-secondary">
                                            <i class="fa fa-times"></i> إعادة تعيين
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    
                    <!-- رسائل النجاح والخطأ -->
                    <!-- Success and error messages -->
                    <?php if (isset($_GET['success'])): ?>
                        <div class="alert alert-success">
                            <?php echo htmlspecialchars($_GET['success']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-danger">
                            <?php echo htmlspecialchars($_GET['error']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- نموذج الإجراءات الجماعية -->
                    <!-- Bulk actions form -->
                    <form method="post" action="index.php?action=bulk_action" id="bulk-form">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th width="30">
                                            <input type="checkbox" id="select-all">
                                        </th>
                                        <th width="50">#</th>
                                        <th width="80">الصورة</th>
                                        <th>
                                            <a href="index.php?sort=name&order=<?php echo $sort === 'name' && $order === 'ASC' ? 'DESC' : 'ASC'; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($role); ?>&status=<?php echo urlencode($status); ?>&date_range=<?php echo urlencode($date_range); ?>">
                                                الاسم
                                                <?php if ($sort === 'name'): ?>
                                                    <i class="fa fa-sort-<?php echo $order === 'ASC' ? 'up' : 'down'; ?>"></i>
                                                <?php endif; ?>
                                            </a>
                                        </th>
                                        <th>
                                            <a href="index.php?sort=email&order=<?php echo $sort === 'email' && $order === 'ASC' ? 'DESC' : 'ASC'; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($role); ?>&status=<?php echo urlencode($status); ?>&date_range=<?php echo urlencode($date_range); ?>">
                                                البريد الإلكتروني
                                                <?php if ($sort === 'email'): ?>
                                                    <i class="fa fa-sort-<?php echo $order === 'ASC' ? 'up' : 'down'; ?>"></i>
                                                <?php endif; ?>
                                            </a>
                                        </th>
                                        <th>الهاتف</th>
                                        <th>
                                            <a href="index.php?sort=role&order=<?php echo $sort === 'role' && $order === 'ASC' ? 'DESC' : 'ASC'; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($role); ?>&status=<?php echo urlencode($status); ?>&date_range=<?php echo urlencode($date_range); ?>">
                                                الدور
                                                <?php if ($sort === 'role'): ?>
                                                    <i class="fa fa-sort-<?php echo $order === 'ASC' ? 'up' : 'down'; ?>"></i>
                                                <?php endif; ?>
                                            </a>
                                        </th>
                                        <th>
                                            <a href="index.php?sort=status&order=<?php echo $sort === 'status' && $order === 'ASC' ? 'DESC' : 'ASC'; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($role); ?>&status=<?php echo urlencode($status); ?>&date_range=<?php echo urlencode($date_range); ?>">
                                                الحالة
                                                <?php if ($sort === 'status'): ?>
                                                    <i class="fa fa-sort-<?php echo $order === 'ASC' ? 'up' : 'down'; ?>"></i>
                                                <?php endif; ?>
                                            </a>
                                        </th>
                                        <th>
                                            <a href="index.php?sort=created_at&order=<?php echo $sort === 'created_at' && $order === 'ASC' ? 'DESC' : 'ASC'; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($role); ?>&status=<?php echo urlencode($status); ?>&date_range=<?php echo urlencode($date_range); ?>">
                                                تاريخ التسجيل
                                                <?php if ($sort === 'created_at'): ?>
                                                    <i class="fa fa-sort-<?php echo $order === 'ASC' ? 'up' : 'down'; ?>"></i>
                                                <?php endif; ?>
                                            </a>
                                        </th>
                                        <th width="150">الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($users)): ?>
                                        <tr>
                                            <td colspan="10" class="text-center">لا يوجد مستخدمين متاحين.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($users as $user): ?>
                                            <tr>
                                                <td>
                                                    <input type="checkbox" name="users[]" value="<?php echo $user['id']; ?>" class="user-checkbox">
                                                </td>
                                                <td><?php echo $user['id']; ?></td>
                                                <td>
                                                    <?php if (!empty($user['profile_image'])): ?>
                                                        <img src="<?php echo SITE_URL . '/uploads/users/' . $user['profile_image']; ?>" alt="<?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>" class="img-thumbnail" style="max-width: 50px;">
                                                    <?php else: ?>
                                                        <div class="text-center text-muted">
                                                            <i class="fa fa-user-circle fa-2x"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <a href="index.php?action=view&id=<?php echo $user['id']; ?>">
                                                        <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                                                    </a>
                                                </td>
                                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                                <td><?php echo htmlspecialchars($user['phone']); ?></td>
                                                <td>
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
                                                    ?>
                                                    <span class="badge <?php echo $roleClass; ?>"><?php echo $roleText; ?></span>
                                                </td>
                                                <td>
                                                    <?php
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
                                                    <span class="badge <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                                                </td>
                                                <td><?php echo date('Y-m-d', strtotime($user['created_at'])); ?></td>
                                                <td>
                                                    <div class="btn-group">
                                                        <a href="index.php?action=view&id=<?php echo $user['id']; ?>" class="btn btn-sm btn-info">
                                                            <i class="fa fa-eye"></i> عرض
                                                        </a>
                                                        <a href="index.php?action=edit&id=<?php echo $user['id']; ?>" class="btn btn-sm btn-primary">
                                                            <i class="fa fa-edit"></i> تعديل
                                                        </a>
                                                        <a href="index.php?action=delete&id=<?php echo $user['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('هل أنت متأكد من حذف هذا المستخدم؟')">
                                                            <i class="fa fa-trash"></i> حذف
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- الإجراءات الجماعية -->
                        <!-- Bulk actions -->
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <select name="bulk_action" id="bulk_action" class="form-control">
                                        <option value="">-- اختر إجراء --</option>
                                        <option value="activate">تنشيط المستخدمين المحددين</option>
                                        <option value="deactivate">إلغاء تنشيط المستخدمين المحددين</option>
                                        <option value="block">حظر المستخدمين المحددين</option>
                                        <option value="unblock">إلغاء حظر المستخدمين المحددين</option>
                                        <option value="delete">حذف المستخدمين المحددين</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary" id="bulk-submit" disabled>
                                    تنفيذ الإجراء
                                </button>
                            </div>
                            <div class="col-md-4 text-end">
                                <div class="pagination-info">
                                    عرض <?php echo count($users); ?> من <?php echo $total; ?> مستخدم
                                </div>
                            </div>
                        </div>
                    </form>
                    
                    <!-- ترقيم الصفحات -->
                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <nav aria-label="Page navigation">
                                    <ul class="pagination justify-content-center">
                                        <?php if ($page > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="index.php?page=1&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($role); ?>&status=<?php echo urlencode($status); ?>&date_range=<?php echo urlencode($date_range); ?>">
                                                    الأولى
                                                </a>
                                            </li>
                                            <li class="page-item">
                                                <a class="page-link" href="index.php?page=<?php echo $page - 1; ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($role); ?>&status=<?php echo urlencode($status); ?>&date_range=<?php echo urlencode($date_range); ?>">
                                                    السابقة
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                        
                                        <?php
                                        $startPage = max(1, $page - 2);
                                        $endPage = min($totalPages, $page + 2);
                                        
                                        for ($i = $startPage; $i <= $endPage; $i++):
                                        ?>
                                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                                <a class="page-link" href="index.php?page=<?php echo $i; ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($role); ?>&status=<?php echo urlencode($status); ?>&date_range=<?php echo urlencode($date_range); ?>">
                                                    <?php echo $i; ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>
                                        
                                        <?php if ($page < $totalPages): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="index.php?page=<?php echo $page + 1; ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($role); ?>&status=<?php echo urlencode($status); ?>&date_range=<?php echo urlencode($date_range); ?>">
                                                    التالية
                                                </a>
                                            </li>
                                            <li class="page-item">
                                                <a class="page-link" href="index.php?page=<?php echo $totalPages; ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($role); ?>&status=<?php echo urlencode($status); ?>&date_range=<?php echo urlencode($date_range); ?>">
                                                    الأخيرة
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // تحديد/إلغاء تحديد الكل
        // Select/deselect all
        const selectAllCheckbox = document.getElementById('select-all');
        const userCheckboxes = document.querySelectorAll('.user-checkbox');
        const bulkSubmitButton = document.getElementById('bulk-submit');
        const bulkActionSelect = document.getElementById('bulk_action');
        
        selectAllCheckbox.addEventListener('change', function() {
            userCheckboxes.forEach(function(checkbox) {
                checkbox.checked = selectAllCheckbox.checked;
            });
            
            updateBulkSubmitButton();
        });
        
        userCheckboxes.forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                updateBulkSubmitButton();
                
                // تحديث حالة "تحديد الكل" بناءً على حالة الصناديق الفردية
                // Update "select all" state based on individual checkboxes
                const allChecked = Array.from(userCheckboxes).every(function(cb) {
                    return cb.checked;
                });
                
                const anyChecked = Array.from(userCheckboxes).some(function(cb) {
                    return cb.checked;
                });
                
                selectAllCheckbox.checked = allChecked;
                selectAllCheckbox.indeterminate = anyChecked && !allChecked;
            });
        });
        
        bulkActionSelect.addEventListener('change', function() {
            updateBulkSubmitButton();
        });
        
        // تحديث حالة زر تنفيذ الإجراء الجماعي
        // Update bulk action button state
        function updateBulkSubmitButton() {
            const anyChecked = Array.from(userCheckboxes).some(function(cb) {
                return cb.checked;
            });
            
            const actionSelected = bulkActionSelect.value !== '';
            
            bulkSubmitButton.disabled = !(anyChecked && actionSelected);
        }
        
        // التحقق من نموذج الإجراء الجماعي قبل الإرسال
        // Validate bulk action form before submission
        document.getElementById('bulk-form').addEventListener('submit', function(event) {
            const anyChecked = Array.from(userCheckboxes).some(function(cb) {
                return cb.checked;
            });
            
            const actionSelected = bulkActionSelect.value !== '';
            
            if (!anyChecked || !actionSelected) {
                event.preventDefault();
                alert('يرجى اختيار مستخدم واحد على الأقل وإجراء واحد.');
                return false;
            }
            
            if (bulkActionSelect.value === 'delete') {
                if (!confirm('هل أنت متأكد من حذف المستخدمين المحددين؟')) {
                    event.preventDefault();
                    return false;
                }
            }
            
            return true;
        });
    });
</script>

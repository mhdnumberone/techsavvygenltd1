<?php
/**
 * قالب قائمة تذاكر الدعم
 * Support Tickets List Template
 */
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title">إدارة تذاكر الدعم</h4>
                    <div>
                        <a href="index.php" class="btn btn-primary">
                            <i class="fa fa-refresh"></i> تحديث
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- نموذج البحث والتصفية -->
                    <!-- Search and filter form -->
                    <form method="get" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="search">بحث</label>
                                    <input type="text" name="search" id="search" class="form-control" value="<?php echo htmlspecialchars($search); ?>" placeholder="الموضوع، المحتوى، اسم المستخدم...">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="status">الحالة</label>
                                    <select name="status" id="status" class="form-control">
                                        <option value="">الكل</option>
                                        <option value="new" <?php echo $status === 'new' ? 'selected' : ''; ?>>جديدة</option>
                                        <option value="open" <?php echo $status === 'open' ? 'selected' : ''; ?>>مفتوحة</option>
                                        <option value="read" <?php echo $status === 'read' ? 'selected' : ''; ?>>مقروءة</option>
                                        <option value="replied" <?php echo $status === 'replied' ? 'selected' : ''; ?>>تم الرد</option>
                                        <option value="closed" <?php echo $status === 'closed' ? 'selected' : ''; ?>>مغلقة</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="priority">الأولوية</label>
                                    <select name="priority" id="priority" class="form-control">
                                        <option value="">الكل</option>
                                        <option value="low" <?php echo $priority === 'low' ? 'selected' : ''; ?>>منخفضة</option>
                                        <option value="medium" <?php echo $priority === 'medium' ? 'selected' : ''; ?>>متوسطة</option>
                                        <option value="high" <?php echo $priority === 'high' ? 'selected' : ''; ?>>عالية</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="department">القسم</label>
                                    <select name="department" id="department" class="form-control">
                                        <option value="">الكل</option>
                                        <option value="technical" <?php echo $department === 'technical' ? 'selected' : ''; ?>>الدعم الفني</option>
                                        <option value="billing" <?php echo $department === 'billing' ? 'selected' : ''; ?>>الفواتير والمدفوعات</option>
                                        <option value="sales" <?php echo $department === 'sales' ? 'selected' : ''; ?>>المبيعات</option>
                                        <option value="general" <?php echo $department === 'general' ? 'selected' : ''; ?>>استفسارات عامة</option>
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
                                        <th>
                                            <a href="index.php?sort=subject&order=<?php echo $sort === 'subject' && $order === 'ASC' ? 'DESC' : 'ASC'; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>&priority=<?php echo urlencode($priority); ?>&department=<?php echo urlencode($department); ?>">
                                                الموضوع
                                                <?php if ($sort === 'subject'): ?>
                                                    <i class="fa fa-sort-<?php echo $order === 'ASC' ? 'up' : 'down'; ?>"></i>
                                                <?php endif; ?>
                                            </a>
                                        </th>
                                        <th>المستخدم</th>
                                        <th>
                                            <a href="index.php?sort=department&order=<?php echo $sort === 'department' && $order === 'ASC' ? 'DESC' : 'ASC'; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>&priority=<?php echo urlencode($priority); ?>&department=<?php echo urlencode($department); ?>">
                                                القسم
                                                <?php if ($sort === 'department'): ?>
                                                    <i class="fa fa-sort-<?php echo $order === 'ASC' ? 'up' : 'down'; ?>"></i>
                                                <?php endif; ?>
                                            </a>
                                        </th>
                                        <th>
                                            <a href="index.php?sort=priority&order=<?php echo $sort === 'priority' && $order === 'ASC' ? 'DESC' : 'ASC'; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>&priority=<?php echo urlencode($priority); ?>&department=<?php echo urlencode($department); ?>">
                                                الأولوية
                                                <?php if ($sort === 'priority'): ?>
                                                    <i class="fa fa-sort-<?php echo $order === 'ASC' ? 'up' : 'down'; ?>"></i>
                                                <?php endif; ?>
                                            </a>
                                        </th>
                                        <th>
                                            <a href="index.php?sort=status&order=<?php echo $sort === 'status' && $order === 'ASC' ? 'DESC' : 'ASC'; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>&priority=<?php echo urlencode($priority); ?>&department=<?php echo urlencode($department); ?>">
                                                الحالة
                                                <?php if ($sort === 'status'): ?>
                                                    <i class="fa fa-sort-<?php echo $order === 'ASC' ? 'up' : 'down'; ?>"></i>
                                                <?php endif; ?>
                                            </a>
                                        </th>
                                        <th>الردود</th>
                                        <th>
                                            <a href="index.php?sort=created_at&order=<?php echo $sort === 'created_at' && $order === 'ASC' ? 'DESC' : 'ASC'; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>&priority=<?php echo urlencode($priority); ?>&department=<?php echo urlencode($department); ?>">
                                                تاريخ الإنشاء
                                                <?php if ($sort === 'created_at'): ?>
                                                    <i class="fa fa-sort-<?php echo $order === 'ASC' ? 'up' : 'down'; ?>"></i>
                                                <?php endif; ?>
                                            </a>
                                        </th>
                                        <th>
                                            <a href="index.php?sort=updated_at&order=<?php echo $sort === 'updated_at' && $order === 'ASC' ? 'DESC' : 'ASC'; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>&priority=<?php echo urlencode($priority); ?>&department=<?php echo urlencode($department); ?>">
                                                آخر تحديث
                                                <?php if ($sort === 'updated_at'): ?>
                                                    <i class="fa fa-sort-<?php echo $order === 'ASC' ? 'up' : 'down'; ?>"></i>
                                                <?php endif; ?>
                                            </a>
                                        </th>
                                        <th width="150">الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($tickets)): ?>
                                        <tr>
                                            <td colspan="12" class="text-center">لا توجد تذاكر دعم متاحة.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($tickets as $ticket): ?>
                                            <tr>
                                                <td>
                                                    <input type="checkbox" name="tickets[]" value="<?php echo $ticket['id']; ?>" class="ticket-checkbox">
                                                </td>
                                                <td><?php echo $ticket['id']; ?></td>
                                                <td>
                                                    <a href="index.php?action=view&id=<?php echo $ticket['id']; ?>">
                                                        <?php echo htmlspecialchars($ticket['subject']); ?>
                                                    </a>
                                                </td>
                                                <td>
                                                    <?php echo htmlspecialchars($ticket['user_name']); ?>
                                                    <br>
                                                    <small><?php echo htmlspecialchars($ticket['user_email']); ?></small>
                                                </td>
                                                <td>
                                                    <?php
                                                    $departmentText = '';
                                                    switch ($ticket['department']) {
                                                        case 'technical':
                                                            $departmentText = 'الدعم الفني';
                                                            break;
                                                        case 'billing':
                                                            $departmentText = 'الفواتير والمدفوعات';
                                                            break;
                                                        case 'sales':
                                                            $departmentText = 'المبيعات';
                                                            break;
                                                        case 'general':
                                                            $departmentText = 'استفسارات عامة';
                                                            break;
                                                        default:
                                                            $departmentText = $ticket['department'];
                                                    }
                                                    echo $departmentText;
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    $priorityClass = '';
                                                    $priorityText = '';
                                                    switch ($ticket['priority']) {
                                                        case 'low':
                                                            $priorityClass = 'text-success';
                                                            $priorityText = 'منخفضة';
                                                            break;
                                                        case 'medium':
                                                            $priorityClass = 'text-warning';
                                                            $priorityText = 'متوسطة';
                                                            break;
                                                        case 'high':
                                                            $priorityClass = 'text-danger';
                                                            $priorityText = 'عالية';
                                                            break;
                                                        default:
                                                            $priorityClass = 'text-secondary';
                                                            $priorityText = $ticket['priority'];
                                                    }
                                                    ?>
                                                    <span class="<?php echo $priorityClass; ?>"><?php echo $priorityText; ?></span>
                                                </td>
                                                <td>
                                                    <?php
                                                    $statusClass = '';
                                                    $statusText = '';
                                                    switch ($ticket['status']) {
                                                        case 'new':
                                                            $statusClass = 'badge bg-info';
                                                            $statusText = 'جديدة';
                                                            break;
                                                        case 'open':
                                                            $statusClass = 'badge bg-primary';
                                                            $statusText = 'مفتوحة';
                                                            break;
                                                        case 'read':
                                                            $statusClass = 'badge bg-secondary';
                                                            $statusText = 'مقروءة';
                                                            break;
                                                        case 'replied':
                                                            $statusClass = 'badge bg-success';
                                                            $statusText = 'تم الرد';
                                                            break;
                                                        case 'closed':
                                                            $statusClass = 'badge bg-danger';
                                                            $statusText = 'مغلقة';
                                                            break;
                                                        default:
                                                            $statusClass = 'badge bg-secondary';
                                                            $statusText = $ticket['status'];
                                                    }
                                                    ?>
                                                    <span class="<?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                                                </td>
                                                <td><?php echo $ticket['reply_count']; ?></td>
                                                <td><?php echo date('Y-m-d H:i', strtotime($ticket['created_at'])); ?></td>
                                                <td><?php echo date('Y-m-d H:i', strtotime($ticket['updated_at'])); ?></td>
                                                <td>
                                                    <div class="btn-group">
                                                        <a href="index.php?action=view&id=<?php echo $ticket['id']; ?>" class="btn btn-sm btn-info">
                                                            <i class="fa fa-eye"></i> عرض
                                                        </a>
                                                        <?php if ($ticket['status'] !== 'closed'): ?>
                                                            <a href="index.php?action=close&id=<?php echo $ticket['id']; ?>" class="btn btn-sm btn-warning" onclick="return confirm('هل أنت متأكد من إغلاق هذه التذكرة؟')">
                                                                <i class="fa fa-times"></i> إغلاق
                                                            </a>
                                                        <?php else: ?>
                                                            <a href="index.php?action=reopen&id=<?php echo $ticket['id']; ?>" class="btn btn-sm btn-success">
                                                                <i class="fa fa-refresh"></i> إعادة فتح
                                                            </a>
                                                        <?php endif; ?>
                                                        <a href="index.php?action=delete&id=<?php echo $ticket['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('هل أنت متأكد من حذف هذه التذكرة؟ سيتم حذف جميع الردود أيضاً.')">
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
                                        <option value="close">إغلاق التذاكر المحددة</option>
                                        <option value="reopen">إعادة فتح التذاكر المحددة</option>
                                        <option value="delete">حذف التذاكر المحددة</option>
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
                                    عرض <?php echo count($tickets); ?> من <?php echo $total; ?> تذكرة
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
                                                <a class="page-link" href="index.php?page=1&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>&priority=<?php echo urlencode($priority); ?>&department=<?php echo urlencode($department); ?>">
                                                    الأولى
                                                </a>
                                            </li>
                                            <li class="page-item">
                                                <a class="page-link" href="index.php?page=<?php echo $page - 1; ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>&priority=<?php echo urlencode($priority); ?>&department=<?php echo urlencode($department); ?>">
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
                                                <a class="page-link" href="index.php?page=<?php echo $i; ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>&priority=<?php echo urlencode($priority); ?>&department=<?php echo urlencode($department); ?>">
                                                    <?php echo $i; ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>
                                        
                                        <?php if ($page < $totalPages): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="index.php?page=<?php echo $page + 1; ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>&priority=<?php echo urlencode($priority); ?>&department=<?php echo urlencode($department); ?>">
                                                    التالية
                                                </a>
                                            </li>
                                            <li class="page-item">
                                                <a class="page-link" href="index.php?page=<?php echo $totalPages; ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>&priority=<?php echo urlencode($priority); ?>&department=<?php echo urlencode($department); ?>">
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
        const ticketCheckboxes = document.querySelectorAll('.ticket-checkbox');
        const bulkSubmitButton = document.getElementById('bulk-submit');
        const bulkActionSelect = document.getElementById('bulk_action');
        
        selectAllCheckbox.addEventListener('change', function() {
            ticketCheckboxes.forEach(function(checkbox) {
                checkbox.checked = selectAllCheckbox.checked;
            });
            
            updateBulkSubmitButton();
        });
        
        ticketCheckboxes.forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                updateBulkSubmitButton();
                
                // تحديث حالة "تحديد الكل" بناءً على حالة الصناديق الفردية
                // Update "select all" state based on individual checkboxes
                const allChecked = Array.from(ticketCheckboxes).every(function(cb) {
                    return cb.checked;
                });
                
                const anyChecked = Array.from(ticketCheckboxes).some(function(cb) {
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
            const anyChecked = Array.from(ticketCheckboxes).some(function(cb) {
                return cb.checked;
            });
            
            const actionSelected = bulkActionSelect.value !== '';
            
            bulkSubmitButton.disabled = !(anyChecked && actionSelected);
        }
        
        // التحقق من نموذج الإجراء الجماعي قبل الإرسال
        // Validate bulk action form before submission
        document.getElementById('bulk-form').addEventListener('submit', function(event) {
            const anyChecked = Array.from(ticketCheckboxes).some(function(cb) {
                return cb.checked;
            });
            
            const actionSelected = bulkActionSelect.value !== '';
            
            if (!anyChecked || !actionSelected) {
                event.preventDefault();
                alert('يرجى اختيار تذكرة واحدة على الأقل وإجراء واحد.');
                return false;
            }
            
            if (bulkActionSelect.value === 'delete') {
                if (!confirm('هل أنت متأكد من حذف التذاكر المحددة؟ سيتم حذف جميع الردود أيضاً.')) {
                    event.preventDefault();
                    return false;
                }
            }
            
            return true;
        });
    });
</script>

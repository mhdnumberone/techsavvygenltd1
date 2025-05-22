<?php
/**
 * قالب قائمة الخدمات
 * Services List Template
 */
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title">إدارة الخدمات</h4>
                    <a href="index.php?action=add" class="btn btn-primary">
                        <i class="fa fa-plus"></i> إضافة خدمة جديدة
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
                                    <input type="text" name="search" id="search" class="form-control" value="<?php echo htmlspecialchars($search); ?>" placeholder="العنوان، الوصف، الرمز...">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="category_id">التصنيف</label>
                                    <select name="category_id" id="category_id" class="form-control">
                                        <option value="">الكل</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?php echo $category['id']; ?>" <?php echo $category_id == $category['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($category['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
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
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="featured">مميز</label>
                                    <select name="featured" id="featured" class="form-control">
                                        <option value="">الكل</option>
                                        <option value="1" <?php echo $featured === '1' ? 'selected' : ''; ?>>مميز</option>
                                        <option value="0" <?php echo $featured === '0' ? 'selected' : ''; ?>>غير مميز</option>
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
                                        <th width="100">الصورة</th>
                                        <th>
                                            <a href="index.php?sort=title&order=<?php echo $sort === 'title' && $order === 'ASC' ? 'DESC' : 'ASC'; ?>&search=<?php echo urlencode($search); ?>&category_id=<?php echo urlencode($category_id); ?>&status=<?php echo urlencode($status); ?>&featured=<?php echo urlencode($featured); ?>">
                                                العنوان
                                                <?php if ($sort === 'title'): ?>
                                                    <i class="fa fa-sort-<?php echo $order === 'ASC' ? 'up' : 'down'; ?>"></i>
                                                <?php endif; ?>
                                            </a>
                                        </th>
                                        <th>
                                            <a href="index.php?sort=service_code&order=<?php echo $sort === 'service_code' && $order === 'ASC' ? 'DESC' : 'ASC'; ?>&search=<?php echo urlencode($search); ?>&category_id=<?php echo urlencode($category_id); ?>&status=<?php echo urlencode($status); ?>&featured=<?php echo urlencode($featured); ?>">
                                                الرمز
                                                <?php if ($sort === 'service_code'): ?>
                                                    <i class="fa fa-sort-<?php echo $order === 'ASC' ? 'up' : 'down'; ?>"></i>
                                                <?php endif; ?>
                                            </a>
                                        </th>
                                        <th>
                                            <a href="index.php?sort=price&order=<?php echo $sort === 'price' && $order === 'ASC' ? 'DESC' : 'ASC'; ?>&search=<?php echo urlencode($search); ?>&category_id=<?php echo urlencode($category_id); ?>&status=<?php echo urlencode($status); ?>&featured=<?php echo urlencode($featured); ?>">
                                                السعر
                                                <?php if ($sort === 'price'): ?>
                                                    <i class="fa fa-sort-<?php echo $order === 'ASC' ? 'up' : 'down'; ?>"></i>
                                                <?php endif; ?>
                                            </a>
                                        </th>
                                        <th>التصنيف</th>
                                        <th>
                                            <a href="index.php?sort=status&order=<?php echo $sort === 'status' && $order === 'ASC' ? 'DESC' : 'ASC'; ?>&search=<?php echo urlencode($search); ?>&category_id=<?php echo urlencode($category_id); ?>&status=<?php echo urlencode($status); ?>&featured=<?php echo urlencode($featured); ?>">
                                                الحالة
                                                <?php if ($sort === 'status'): ?>
                                                    <i class="fa fa-sort-<?php echo $order === 'ASC' ? 'up' : 'down'; ?>"></i>
                                                <?php endif; ?>
                                            </a>
                                        </th>
                                        <th>
                                            <a href="index.php?sort=created_at&order=<?php echo $sort === 'created_at' && $order === 'ASC' ? 'DESC' : 'ASC'; ?>&search=<?php echo urlencode($search); ?>&category_id=<?php echo urlencode($category_id); ?>&status=<?php echo urlencode($status); ?>&featured=<?php echo urlencode($featured); ?>">
                                                تاريخ الإنشاء
                                                <?php if ($sort === 'created_at'): ?>
                                                    <i class="fa fa-sort-<?php echo $order === 'ASC' ? 'up' : 'down'; ?>"></i>
                                                <?php endif; ?>
                                            </a>
                                        </th>
                                        <th width="150">الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($services)): ?>
                                        <tr>
                                            <td colspan="11" class="text-center">لا توجد خدمات متاحة.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($services as $service): ?>
                                            <tr>
                                                <td>
                                                    <input type="checkbox" name="services[]" value="<?php echo $service['id']; ?>" class="service-checkbox">
                                                </td>
                                                <td><?php echo $service['id']; ?></td>
                                                <td>
                                                    <?php if (!empty($service['featured_image'])): ?>
                                                        <img src="<?php echo SITE_URL . '/uploads/services/' . $service['featured_image']; ?>" alt="<?php echo htmlspecialchars($service['title']); ?>" class="img-thumbnail" style="max-width: 80px;">
                                                    <?php else: ?>
                                                        <div class="text-center text-muted">
                                                            <i class="fa fa-image fa-2x"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <a href="index.php?action=view&id=<?php echo $service['id']; ?>">
                                                        <?php echo htmlspecialchars($service['title']); ?>
                                                    </a>
                                                    <?php if ($service['featured'] == 1): ?>
                                                        <span class="badge bg-primary ms-1">مميز</span>
                                                    <?php endif; ?>
                                                    <?php if ($service['on_sale'] == 1): ?>
                                                        <span class="badge bg-danger ms-1">عرض</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($service['service_code']); ?></td>
                                                <td>
                                                    <?php if ($service['on_sale'] == 1 && !empty($service['discount_price'])): ?>
                                                        <span class="text-decoration-line-through text-muted">$<?php echo number_format($service['price'], 2); ?></span>
                                                        <span class="text-danger fw-bold">$<?php echo number_format($service['discount_price'], 2); ?></span>
                                                    <?php else: ?>
                                                        <span>$<?php echo number_format($service['price'], 2); ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($service['category_name']); ?></td>
                                                <td>
                                                    <?php
                                                    $statusClass = '';
                                                    $statusText = '';
                                                    switch ($service['status']) {
                                                        case 'active':
                                                            $statusClass = 'bg-success';
                                                            $statusText = 'نشط';
                                                            break;
                                                        case 'inactive':
                                                            $statusClass = 'bg-secondary';
                                                            $statusText = 'غير نشط';
                                                            break;
                                                        default:
                                                            $statusClass = 'bg-secondary';
                                                            $statusText = $service['status'];
                                                    }
                                                    ?>
                                                    <span class="badge <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                                                </td>
                                                <td><?php echo date('Y-m-d', strtotime($service['created_at'])); ?></td>
                                                <td>
                                                    <div class="btn-group">
                                                        <a href="index.php?action=view&id=<?php echo $service['id']; ?>" class="btn btn-sm btn-info">
                                                            <i class="fa fa-eye"></i> عرض
                                                        </a>
                                                        <a href="index.php?action=edit&id=<?php echo $service['id']; ?>" class="btn btn-sm btn-primary">
                                                            <i class="fa fa-edit"></i> تعديل
                                                        </a>
                                                        <a href="index.php?action=delete&id=<?php echo $service['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('هل أنت متأكد من حذف هذه الخدمة؟')">
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
                                        <option value="activate">تنشيط الخدمات المحددة</option>
                                        <option value="deactivate">إلغاء تنشيط الخدمات المحددة</option>
                                        <option value="feature">تمييز الخدمات المحددة</option>
                                        <option value="unfeature">إلغاء تمييز الخدمات المحددة</option>
                                        <option value="on_sale">تفعيل العرض للخدمات المحددة</option>
                                        <option value="off_sale">إلغاء العرض للخدمات المحددة</option>
                                        <option value="delete">حذف الخدمات المحددة</option>
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
                                    عرض <?php echo count($services); ?> من <?php echo $total; ?> خدمة
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
                                                <a class="page-link" href="index.php?page=1&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>&search=<?php echo urlencode($search); ?>&category_id=<?php echo urlencode($category_id); ?>&status=<?php echo urlencode($status); ?>&featured=<?php echo urlencode($featured); ?>">
                                                    الأولى
                                                </a>
                                            </li>
                                            <li class="page-item">
                                                <a class="page-link" href="index.php?page=<?php echo $page - 1; ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>&search=<?php echo urlencode($search); ?>&category_id=<?php echo urlencode($category_id); ?>&status=<?php echo urlencode($status); ?>&featured=<?php echo urlencode($featured); ?>">
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
                                                <a class="page-link" href="index.php?page=<?php echo $i; ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>&search=<?php echo urlencode($search); ?>&category_id=<?php echo urlencode($category_id); ?>&status=<?php echo urlencode($status); ?>&featured=<?php echo urlencode($featured); ?>">
                                                    <?php echo $i; ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>
                                        
                                        <?php if ($page < $totalPages): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="index.php?page=<?php echo $page + 1; ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>&search=<?php echo urlencode($search); ?>&category_id=<?php echo urlencode($category_id); ?>&status=<?php echo urlencode($status); ?>&featured=<?php echo urlencode($featured); ?>">
                                                    التالية
                                                </a>
                                            </li>
                                            <li class="page-item">
                                                <a class="page-link" href="index.php?page=<?php echo $totalPages; ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>&search=<?php echo urlencode($search); ?>&category_id=<?php echo urlencode($category_id); ?>&status=<?php echo urlencode($status); ?>&featured=<?php echo urlencode($featured); ?>">
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
        const serviceCheckboxes = document.querySelectorAll('.service-checkbox');
        const bulkSubmitButton = document.getElementById('bulk-submit');
        const bulkActionSelect = document.getElementById('bulk_action');
        
        selectAllCheckbox.addEventListener('change', function() {
            serviceCheckboxes.forEach(function(checkbox) {
                checkbox.checked = selectAllCheckbox.checked;
            });
            
            updateBulkSubmitButton();
        });
        
        serviceCheckboxes.forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                updateBulkSubmitButton();
                
                // تحديث حالة "تحديد الكل" بناءً على حالة الصناديق الفردية
                // Update "select all" state based on individual checkboxes
                const allChecked = Array.from(serviceCheckboxes).every(function(cb) {
                    return cb.checked;
                });
                
                const anyChecked = Array.from(serviceCheckboxes).some(function(cb) {
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
            const anyChecked = Array.from(serviceCheckboxes).some(function(cb) {
                return cb.checked;
            });
            
            const actionSelected = bulkActionSelect.value !== '';
            
            bulkSubmitButton.disabled = !(anyChecked && actionSelected);
        }
        
        // التحقق من نموذج الإجراء الجماعي قبل الإرسال
        // Validate bulk action form before submission
        document.getElementById('bulk-form').addEventListener('submit', function(event) {
            const anyChecked = Array.from(serviceCheckboxes).some(function(cb) {
                return cb.checked;
            });
            
            const actionSelected = bulkActionSelect.value !== '';
            
            if (!anyChecked || !actionSelected) {
                event.preventDefault();
                alert('يرجى اختيار خدمة واحدة على الأقل وإجراء واحد.');
                return false;
            }
            
            if (bulkActionSelect.value === 'delete') {
                if (!confirm('هل أنت متأكد من حذف الخدمات المحددة؟')) {
                    event.preventDefault();
                    return false;
                }
            }
            
            return true;
        });
    });
</script>

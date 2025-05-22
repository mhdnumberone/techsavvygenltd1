<?php
/**
 * قالب قائمة الإنجازات
 * Achievements List Template
 */
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title">إدارة الإنجازات</h4>
                    <a href="index.php?action=add" class="btn btn-primary">
                        <i class="fa fa-plus"></i> إضافة إنجاز جديد
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
                                    <input type="text" name="search" id="search" class="form-control" value="<?php echo htmlspecialchars($search); ?>" placeholder="العنوان، الوصف، اسم العميل...">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="category">التصنيف</label>
                                    <select name="category" id="category" class="form-control">
                                        <option value="">الكل</option>
                                        <option value="web_development" <?php echo $category === 'web_development' ? 'selected' : ''; ?>>تطوير الويب</option>
                                        <option value="mobile_apps" <?php echo $category === 'mobile_apps' ? 'selected' : ''; ?>>تطبيقات الجوال</option>
                                        <option value="desktop_apps" <?php echo $category === 'desktop_apps' ? 'selected' : ''; ?>>تطبيقات سطح المكتب</option>
                                        <option value="ui_ux" <?php echo $category === 'ui_ux' ? 'selected' : ''; ?>>تصميم واجهات المستخدم</option>
                                        <option value="consulting" <?php echo $category === 'consulting' ? 'selected' : ''; ?>>استشارات تقنية</option>
                                        <option value="other" <?php echo $category === 'other' ? 'selected' : ''; ?>>أخرى</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="status">الحالة</label>
                                    <select name="status" id="status" class="form-control">
                                        <option value="">الكل</option>
                                        <option value="published" <?php echo $status === 'published' ? 'selected' : ''; ?>>منشور</option>
                                        <option value="draft" <?php echo $status === 'draft' ? 'selected' : ''; ?>>مسودة</option>
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
                                            <a href="index.php?sort=title&order=<?php echo $sort === 'title' && $order === 'ASC' ? 'DESC' : 'ASC'; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category); ?>&status=<?php echo urlencode($status); ?>&featured=<?php echo urlencode($featured); ?>">
                                                العنوان
                                                <?php if ($sort === 'title'): ?>
                                                    <i class="fa fa-sort-<?php echo $order === 'ASC' ? 'up' : 'down'; ?>"></i>
                                                <?php endif; ?>
                                            </a>
                                        </th>
                                        <th>
                                            <a href="index.php?sort=category&order=<?php echo $sort === 'category' && $order === 'ASC' ? 'DESC' : 'ASC'; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category); ?>&status=<?php echo urlencode($status); ?>&featured=<?php echo urlencode($featured); ?>">
                                                التصنيف
                                                <?php if ($sort === 'category'): ?>
                                                    <i class="fa fa-sort-<?php echo $order === 'ASC' ? 'up' : 'down'; ?>"></i>
                                                <?php endif; ?>
                                            </a>
                                        </th>
                                        <th>
                                            <a href="index.php?sort=client_name&order=<?php echo $sort === 'client_name' && $order === 'ASC' ? 'DESC' : 'ASC'; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category); ?>&status=<?php echo urlencode($status); ?>&featured=<?php echo urlencode($featured); ?>">
                                                العميل
                                                <?php if ($sort === 'client_name'): ?>
                                                    <i class="fa fa-sort-<?php echo $order === 'ASC' ? 'up' : 'down'; ?>"></i>
                                                <?php endif; ?>
                                            </a>
                                        </th>
                                        <th>
                                            <a href="index.php?sort=completion_date&order=<?php echo $sort === 'completion_date' && $order === 'ASC' ? 'DESC' : 'ASC'; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category); ?>&status=<?php echo urlencode($status); ?>&featured=<?php echo urlencode($featured); ?>">
                                                تاريخ الإنجاز
                                                <?php if ($sort === 'completion_date'): ?>
                                                    <i class="fa fa-sort-<?php echo $order === 'ASC' ? 'up' : 'down'; ?>"></i>
                                                <?php endif; ?>
                                            </a>
                                        </th>
                                        <th>
                                            <a href="index.php?sort=status&order=<?php echo $sort === 'status' && $order === 'ASC' ? 'DESC' : 'ASC'; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category); ?>&status=<?php echo urlencode($status); ?>&featured=<?php echo urlencode($featured); ?>">
                                                الحالة
                                                <?php if ($sort === 'status'): ?>
                                                    <i class="fa fa-sort-<?php echo $order === 'ASC' ? 'up' : 'down'; ?>"></i>
                                                <?php endif; ?>
                                            </a>
                                        </th>
                                        <th>
                                            <a href="index.php?sort=featured&order=<?php echo $sort === 'featured' && $order === 'ASC' ? 'DESC' : 'ASC'; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category); ?>&status=<?php echo urlencode($status); ?>&featured=<?php echo urlencode($featured); ?>">
                                                مميز
                                                <?php if ($sort === 'featured'): ?>
                                                    <i class="fa fa-sort-<?php echo $order === 'ASC' ? 'up' : 'down'; ?>"></i>
                                                <?php endif; ?>
                                            </a>
                                        </th>
                                        <th>
                                            <a href="index.php?sort=created_at&order=<?php echo $sort === 'created_at' && $order === 'ASC' ? 'DESC' : 'ASC'; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category); ?>&status=<?php echo urlencode($status); ?>&featured=<?php echo urlencode($featured); ?>">
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
                                    <?php if (empty($achievements)): ?>
                                        <tr>
                                            <td colspan="12" class="text-center">لا توجد إنجازات متاحة.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($achievements as $achievement): ?>
                                            <tr>
                                                <td>
                                                    <input type="checkbox" name="achievements[]" value="<?php echo $achievement['id']; ?>" class="achievement-checkbox">
                                                </td>
                                                <td><?php echo $achievement['id']; ?></td>
                                                <td>
                                                    <?php if (!empty($achievement['featured_image'])): ?>
                                                        <img src="<?php echo SITE_URL . '/uploads/achievements/' . $achievement['featured_image']; ?>" alt="<?php echo htmlspecialchars($achievement['title']); ?>" class="img-thumbnail" style="max-width: 80px;">
                                                    <?php else: ?>
                                                        <div class="text-center text-muted">
                                                            <i class="fa fa-image fa-2x"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <a href="index.php?action=view&id=<?php echo $achievement['id']; ?>">
                                                        <?php echo htmlspecialchars($achievement['title']); ?>
                                                    </a>
                                                </td>
                                                <td>
                                                    <?php
                                                    $categoryText = '';
                                                    switch ($achievement['category']) {
                                                        case 'web_development':
                                                            $categoryText = 'تطوير الويب';
                                                            break;
                                                        case 'mobile_apps':
                                                            $categoryText = 'تطبيقات الجوال';
                                                            break;
                                                        case 'desktop_apps':
                                                            $categoryText = 'تطبيقات سطح المكتب';
                                                            break;
                                                        case 'ui_ux':
                                                            $categoryText = 'تصميم واجهات المستخدم';
                                                            break;
                                                        case 'consulting':
                                                            $categoryText = 'استشارات تقنية';
                                                            break;
                                                        case 'other':
                                                            $categoryText = 'أخرى';
                                                            break;
                                                        default:
                                                            $categoryText = $achievement['category'];
                                                    }
                                                    echo $categoryText;
                                                    ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($achievement['client_name']); ?></td>
                                                <td><?php echo !empty($achievement['completion_date']) ? date('Y-m-d', strtotime($achievement['completion_date'])) : '-'; ?></td>
                                                <td>
                                                    <?php if ($achievement['status'] === 'published'): ?>
                                                        <span class="badge bg-success">منشور</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">مسودة</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($achievement['featured'] == 1): ?>
                                                        <span class="badge bg-primary">مميز</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-light text-dark">غير مميز</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo date('Y-m-d', strtotime($achievement['created_at'])); ?></td>
                                                <td>
                                                    <div class="btn-group">
                                                        <a href="index.php?action=view&id=<?php echo $achievement['id']; ?>" class="btn btn-sm btn-info">
                                                            <i class="fa fa-eye"></i> عرض
                                                        </a>
                                                        <a href="index.php?action=edit&id=<?php echo $achievement['id']; ?>" class="btn btn-sm btn-primary">
                                                            <i class="fa fa-edit"></i> تعديل
                                                        </a>
                                                        <a href="index.php?action=delete&id=<?php echo $achievement['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('هل أنت متأكد من حذف هذا الإنجاز؟')">
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
                                        <option value="publish">نشر الإنجازات المحددة</option>
                                        <option value="draft">تحويل الإنجازات المحددة إلى مسودة</option>
                                        <option value="feature">تمييز الإنجازات المحددة</option>
                                        <option value="unfeature">إلغاء تمييز الإنجازات المحددة</option>
                                        <option value="delete">حذف الإنجازات المحددة</option>
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
                                    عرض <?php echo count($achievements); ?> من <?php echo $total; ?> إنجاز
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
                                                <a class="page-link" href="index.php?page=1&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category); ?>&status=<?php echo urlencode($status); ?>&featured=<?php echo urlencode($featured); ?>">
                                                    الأولى
                                                </a>
                                            </li>
                                            <li class="page-item">
                                                <a class="page-link" href="index.php?page=<?php echo $page - 1; ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category); ?>&status=<?php echo urlencode($status); ?>&featured=<?php echo urlencode($featured); ?>">
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
                                                <a class="page-link" href="index.php?page=<?php echo $i; ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category); ?>&status=<?php echo urlencode($status); ?>&featured=<?php echo urlencode($featured); ?>">
                                                    <?php echo $i; ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>
                                        
                                        <?php if ($page < $totalPages): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="index.php?page=<?php echo $page + 1; ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category); ?>&status=<?php echo urlencode($status); ?>&featured=<?php echo urlencode($featured); ?>">
                                                    التالية
                                                </a>
                                            </li>
                                            <li class="page-item">
                                                <a class="page-link" href="index.php?page=<?php echo $totalPages; ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category); ?>&status=<?php echo urlencode($status); ?>&featured=<?php echo urlencode($featured); ?>">
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
        const achievementCheckboxes = document.querySelectorAll('.achievement-checkbox');
        const bulkSubmitButton = document.getElementById('bulk-submit');
        const bulkActionSelect = document.getElementById('bulk_action');
        
        selectAllCheckbox.addEventListener('change', function() {
            achievementCheckboxes.forEach(function(checkbox) {
                checkbox.checked = selectAllCheckbox.checked;
            });
            
            updateBulkSubmitButton();
        });
        
        achievementCheckboxes.forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                updateBulkSubmitButton();
                
                // تحديث حالة "تحديد الكل" بناءً على حالة الصناديق الفردية
                // Update "select all" state based on individual checkboxes
                const allChecked = Array.from(achievementCheckboxes).every(function(cb) {
                    return cb.checked;
                });
                
                const anyChecked = Array.from(achievementCheckboxes).some(function(cb) {
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
            const anyChecked = Array.from(achievementCheckboxes).some(function(cb) {
                return cb.checked;
            });
            
            const actionSelected = bulkActionSelect.value !== '';
            
            bulkSubmitButton.disabled = !(anyChecked && actionSelected);
        }
        
        // التحقق من نموذج الإجراء الجماعي قبل الإرسال
        // Validate bulk action form before submission
        document.getElementById('bulk-form').addEventListener('submit', function(event) {
            const anyChecked = Array.from(achievementCheckboxes).some(function(cb) {
                return cb.checked;
            });
            
            const actionSelected = bulkActionSelect.value !== '';
            
            if (!anyChecked || !actionSelected) {
                event.preventDefault();
                alert('يرجى اختيار إنجاز واحد على الأقل وإجراء واحد.');
                return false;
            }
            
            if (bulkActionSelect.value === 'delete') {
                if (!confirm('هل أنت متأكد من حذف الإنجازات المحددة؟')) {
                    event.preventDefault();
                    return false;
                }
            }
            
            return true;
        });
    });
</script>

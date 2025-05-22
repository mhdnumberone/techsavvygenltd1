<?php
/**
 * قالب عرض تفاصيل الإنجاز
 * View Achievement Details Template
 */
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title">تفاصيل الإنجاز</h4>
                    <div>
                        <a href="index.php?action=edit&id=<?php echo $achievement['id']; ?>" class="btn btn-primary">
                            <i class="fa fa-edit"></i> تعديل
                        </a>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fa fa-arrow-left"></i> العودة إلى القائمة
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h2><?php echo htmlspecialchars($achievement['title']); ?></h2>
                            
                            <?php if (!empty($achievement['featured_image'])): ?>
                                <div class="mb-4">
                                    <img src="<?php echo SITE_URL . '/uploads/achievements/' . $achievement['featured_image']; ?>" alt="<?php echo htmlspecialchars($achievement['title']); ?>" class="img-fluid rounded">
                                </div>
                            <?php endif; ?>
                            
                            <div class="mb-4">
                                <h5>الوصف:</h5>
                                <p><?php echo nl2br(htmlspecialchars($achievement['description'])); ?></p>
                            </div>
                            
                            <?php if (!empty($achievement['content'])): ?>
                                <div class="mb-4">
                                    <h5>المحتوى:</h5>
                                    <div class="content-area">
                                        <?php echo $achievement['content']; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($achievement['gallery'])): ?>
                                <div class="mb-4">
                                    <h5>معرض الصور:</h5>
                                    <div class="row">
                                        <?php
                                        $gallery = json_decode($achievement['gallery'], true);
                                        if (is_array($gallery)) {
                                            foreach ($gallery as $image) {
                                                echo '<div class="col-md-4 mb-3">';
                                                echo '<a href="' . SITE_URL . '/uploads/achievements/' . $image . '" data-lightbox="achievement-gallery" data-title="' . htmlspecialchars($achievement['title']) . '">';
                                                echo '<img src="' . SITE_URL . '/uploads/achievements/' . $image . '" alt="Gallery Image" class="img-fluid rounded">';
                                                echo '</a>';
                                                echo '</div>';
                                            }
                                        }
                                        ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h5 class="mb-0">معلومات الإنجاز</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered">
                                        <tbody>
                                            <tr>
                                                <th width="40%">المعرف</th>
                                                <td><?php echo $achievement['id']; ?></td>
                                            </tr>
                                            <tr>
                                                <th>الحالة</th>
                                                <td>
                                                    <?php if ($achievement['status'] === 'published'): ?>
                                                        <span class="badge bg-success">منشور</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">مسودة</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>مميز</th>
                                                <td>
                                                    <?php if ($achievement['featured'] == 1): ?>
                                                        <span class="badge bg-primary">نعم</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-light text-dark">لا</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>التصنيف</th>
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
                                            </tr>
                                            <?php if (!empty($achievement['client_name'])): ?>
                                                <tr>
                                                    <th>اسم العميل</th>
                                                    <td><?php echo htmlspecialchars($achievement['client_name']); ?></td>
                                                </tr>
                                            <?php endif; ?>
                                            <?php if (!empty($achievement['completion_date'])): ?>
                                                <tr>
                                                    <th>تاريخ الإنجاز</th>
                                                    <td><?php echo date('Y-m-d', strtotime($achievement['completion_date'])); ?></td>
                                                </tr>
                                            <?php endif; ?>
                                            <?php if (!empty($achievement['tags'])): ?>
                                                <tr>
                                                    <th>الوسوم</th>
                                                    <td>
                                                        <?php
                                                        $tags = explode(',', $achievement['tags']);
                                                        foreach ($tags as $tag) {
                                                            echo '<span class="badge bg-info me-1">' . htmlspecialchars(trim($tag)) . '</span>';
                                                        }
                                                        ?>
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                            <tr>
                                                <th>تاريخ الإنشاء</th>
                                                <td><?php echo date('Y-m-d H:i', strtotime($achievement['created_at'])); ?></td>
                                            </tr>
                                            <tr>
                                                <th>آخر تحديث</th>
                                                <td><?php echo date('Y-m-d H:i', strtotime($achievement['updated_at'])); ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h5 class="mb-0">الإجراءات</h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <a href="index.php?action=edit&id=<?php echo $achievement['id']; ?>" class="btn btn-primary">
                                            <i class="fa fa-edit"></i> تعديل الإنجاز
                                        </a>
                                        
                                        <?php if ($achievement['status'] === 'published'): ?>
                                            <a href="index.php?action=change_status&id=<?php echo $achievement['id']; ?>&status=draft" class="btn btn-warning">
                                                <i class="fa fa-eye-slash"></i> تحويل إلى مسودة
                                            </a>
                                        <?php else: ?>
                                            <a href="index.php?action=change_status&id=<?php echo $achievement['id']; ?>&status=published" class="btn btn-success">
                                                <i class="fa fa-eye"></i> نشر الإنجاز
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php if ($achievement['featured'] == 1): ?>
                                            <a href="index.php?action=toggle_featured&id=<?php echo $achievement['id']; ?>" class="btn btn-info">
                                                <i class="fa fa-star-o"></i> إلغاء التمييز
                                            </a>
                                        <?php else: ?>
                                            <a href="index.php?action=toggle_featured&id=<?php echo $achievement['id']; ?>" class="btn btn-info">
                                                <i class="fa fa-star"></i> تمييز الإنجاز
                                            </a>
                                        <?php endif; ?>
                                        
                                        <a href="<?php echo SITE_URL . '/achievement-details.php?id=' . $achievement['id']; ?>" class="btn btn-secondary" target="_blank">
                                            <i class="fa fa-external-link"></i> عرض في الموقع
                                        </a>
                                        
                                        <a href="index.php?action=delete&id=<?php echo $achievement['id']; ?>" class="btn btn-danger" onclick="return confirm('هل أنت متأكد من حذف هذا الإنجاز؟')">
                                            <i class="fa fa-trash"></i> حذف الإنجاز
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // تهيئة معرض الصور
        // Initialize lightbox for gallery
        if (typeof lightbox !== 'undefined') {
            lightbox.option({
                'resizeDuration': 200,
                'wrapAround': true,
                'albumLabel': 'صورة %1 من %2'
            });
        }
    });
</script>

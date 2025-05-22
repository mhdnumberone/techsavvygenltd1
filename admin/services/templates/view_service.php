<?php
/**
 * قالب عرض تفاصيل الخدمة
 * View Service Details Template
 */
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title">تفاصيل الخدمة</h4>
                    <div>
                        <a href="index.php?action=edit&id=<?php echo $service['id']; ?>" class="btn btn-primary">
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
                            <h2><?php echo htmlspecialchars($service['title']); ?></h2>
                            
                            <?php if (!empty($service['featured_image'])): ?>
                                <div class="mb-4">
                                    <img src="<?php echo SITE_URL . '/uploads/services/' . $service['featured_image']; ?>" alt="<?php echo htmlspecialchars($service['title']); ?>" class="img-fluid rounded">
                                </div>
                            <?php endif; ?>
                            
                            <div class="mb-4">
                                <h5>الوصف المختصر:</h5>
                                <p><?php echo nl2br(htmlspecialchars($service['short_description'])); ?></p>
                            </div>
                            
                            <?php if (!empty($service['description'])): ?>
                                <div class="mb-4">
                                    <h5>الوصف التفصيلي:</h5>
                                    <div class="content-area">
                                        <?php echo $service['description']; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($service['features'])): ?>
                                <div class="mb-4">
                                    <h5>المميزات:</h5>
                                    <div class="content-area">
                                        <?php echo $service['features']; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($service['gallery'])): ?>
                                <div class="mb-4">
                                    <h5>معرض الصور:</h5>
                                    <div class="row">
                                        <?php
                                        $gallery = json_decode($service['gallery'], true);
                                        if (is_array($gallery)) {
                                            foreach ($gallery as $image) {
                                                echo '<div class="col-md-4 mb-3">';
                                                echo '<a href="' . SITE_URL . '/uploads/services/' . $image . '" data-lightbox="service-gallery" data-title="' . htmlspecialchars($service['title']) . '">';
                                                echo '<img src="' . SITE_URL . '/uploads/services/' . $image . '" alt="Gallery Image" class="img-fluid rounded">';
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
                                    <h5 class="mb-0">معلومات الخدمة</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered">
                                        <tbody>
                                            <tr>
                                                <th width="40%">المعرف</th>
                                                <td><?php echo $service['id']; ?></td>
                                            </tr>
                                            <tr>
                                                <th>رمز الخدمة</th>
                                                <td><?php echo htmlspecialchars($service['service_code']); ?></td>
                                            </tr>
                                            <tr>
                                                <th>السعر</th>
                                                <td>
                                                    <?php if ($service['on_sale'] == 1 && !empty($service['discount_price'])): ?>
                                                        <span class="text-decoration-line-through text-muted">$<?php echo number_format($service['price'], 2); ?></span>
                                                        <span class="text-danger fw-bold">$<?php echo number_format($service['discount_price'], 2); ?></span>
                                                    <?php else: ?>
                                                        <span>$<?php echo number_format($service['price'], 2); ?></span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php if (!empty($service['duration'])): ?>
                                                <tr>
                                                    <th>مدة التنفيذ</th>
                                                    <td>
                                                        <?php 
                                                        echo $service['duration'] . ' ';
                                                        switch ($service['duration_unit']) {
                                                            case 'hour':
                                                                echo $service['duration'] > 1 ? 'ساعات' : 'ساعة';
                                                                break;
                                                            case 'day':
                                                                echo $service['duration'] > 1 ? 'أيام' : 'يوم';
                                                                break;
                                                            case 'week':
                                                                echo $service['duration'] > 1 ? 'أسابيع' : 'أسبوع';
                                                                break;
                                                            case 'month':
                                                                echo $service['duration'] > 1 ? 'شهور' : 'شهر';
                                                                break;
                                                            default:
                                                                echo $service['duration_unit'];
                                                        }
                                                        ?>
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                            <tr>
                                                <th>التصنيف</th>
                                                <td><?php echo htmlspecialchars($service['category_name']); ?></td>
                                            </tr>
                                            <tr>
                                                <th>الحالة</th>
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
                                            </tr>
                                            <tr>
                                                <th>خدمة مميزة</th>
                                                <td>
                                                    <?php if ($service['featured'] == 1): ?>
                                                        <span class="badge bg-primary">نعم</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-light text-dark">لا</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>عرض خاص</th>
                                                <td>
                                                    <?php if ($service['on_sale'] == 1): ?>
                                                        <span class="badge bg-danger">نعم</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-light text-dark">لا</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php if (!empty($service['tags'])): ?>
                                                <tr>
                                                    <th>الوسوم</th>
                                                    <td>
                                                        <?php
                                                        $tags = explode(',', $service['tags']);
                                                        foreach ($tags as $tag) {
                                                            echo '<span class="badge bg-info me-1">' . htmlspecialchars(trim($tag)) . '</span>';
                                                        }
                                                        ?>
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                            <tr>
                                                <th>تاريخ الإنشاء</th>
                                                <td><?php echo date('Y-m-d H:i', strtotime($service['created_at'])); ?></td>
                                            </tr>
                                            <tr>
                                                <th>آخر تحديث</th>
                                                <td><?php echo date('Y-m-d H:i', strtotime($service['updated_at'])); ?></td>
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
                                        <a href="index.php?action=edit&id=<?php echo $service['id']; ?>" class="btn btn-primary">
                                            <i class="fa fa-edit"></i> تعديل الخدمة
                                        </a>
                                        
                                        <?php if ($service['status'] === 'active'): ?>
                                            <a href="index.php?action=change_status&id=<?php echo $service['id']; ?>&status=inactive" class="btn btn-warning">
                                                <i class="fa fa-eye-slash"></i> إلغاء تنشيط الخدمة
                                            </a>
                                        <?php else: ?>
                                            <a href="index.php?action=change_status&id=<?php echo $service['id']; ?>&status=active" class="btn btn-success">
                                                <i class="fa fa-eye"></i> تنشيط الخدمة
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php if ($service['featured'] == 1): ?>
                                            <a href="index.php?action=toggle_featured&id=<?php echo $service['id']; ?>" class="btn btn-info">
                                                <i class="fa fa-star-o"></i> إلغاء تمييز الخدمة
                                            </a>
                                        <?php else: ?>
                                            <a href="index.php?action=toggle_featured&id=<?php echo $service['id']; ?>" class="btn btn-info">
                                                <i class="fa fa-star"></i> تمييز الخدمة
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php if ($service['on_sale'] == 1): ?>
                                            <a href="index.php?action=toggle_sale&id=<?php echo $service['id']; ?>" class="btn btn-secondary">
                                                <i class="fa fa-tag"></i> إلغاء العرض الخاص
                                            </a>
                                        <?php else: ?>
                                            <a href="index.php?action=toggle_sale&id=<?php echo $service['id']; ?>" class="btn btn-danger">
                                                <i class="fa fa-tag"></i> تفعيل العرض الخاص
                                            </a>
                                        <?php endif; ?>
                                        
                                        <a href="<?php echo SITE_URL . '/service-details.php?id=' . $service['id']; ?>" class="btn btn-secondary" target="_blank">
                                            <i class="fa fa-external-link"></i> عرض في الموقع
                                        </a>
                                        
                                        <a href="index.php?action=delete&id=<?php echo $service['id']; ?>" class="btn btn-danger" onclick="return confirm('هل أنت متأكد من حذف هذه الخدمة؟')">
                                            <i class="fa fa-trash"></i> حذف الخدمة
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h5 class="mb-0">إحصائيات الخدمة</h5>
                                </div>
                                <div class="card-body">
                                    <ul class="list-group">
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            عدد الطلبات
                                            <span class="badge bg-primary rounded-pill"><?php echo $stats['orders_count']; ?></span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            عدد المراجعات
                                            <span class="badge bg-info rounded-pill"><?php echo $stats['reviews_count']; ?></span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            متوسط التقييم
                                            <span class="badge bg-warning text-dark rounded-pill">
                                                <?php echo number_format($stats['avg_rating'], 1); ?> / 5
                                                <i class="fa fa-star text-warning"></i>
                                            </span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            إجمالي الإيرادات
                                            <span class="badge bg-success rounded-pill">
                                                $<?php echo number_format($stats['total_revenue'], 2); ?>
                                            </span>
                                        </li>
                                    </ul>
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

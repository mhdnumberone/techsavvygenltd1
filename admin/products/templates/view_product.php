<?php
/**
 * قالب عرض تفاصيل المنتج
 * View Product Details Template
 */
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title">تفاصيل المنتج</h4>
                    <div>
                        <a href="index.php?action=edit&id=<?php echo $product['id']; ?>" class="btn btn-primary">
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
                            <h2><?php echo htmlspecialchars($product['name']); ?></h2>
                            
                            <?php if (!empty($product['featured_image'])): ?>
                                <div class="mb-4">
                                    <img src="<?php echo SITE_URL . '/uploads/products/' . $product['featured_image']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="img-fluid rounded">
                                </div>
                            <?php endif; ?>
                            
                            <div class="mb-4">
                                <h5>الوصف المختصر:</h5>
                                <p><?php echo nl2br(htmlspecialchars($product['short_description'])); ?></p>
                            </div>
                            
                            <?php if (!empty($product['description'])): ?>
                                <div class="mb-4">
                                    <h5>الوصف التفصيلي:</h5>
                                    <div class="content-area">
                                        <?php echo $product['description']; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($product['specifications'])): ?>
                                <div class="mb-4">
                                    <h5>المواصفات الفنية:</h5>
                                    <div class="content-area">
                                        <?php echo $product['specifications']; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($product['gallery'])): ?>
                                <div class="mb-4">
                                    <h5>معرض الصور:</h5>
                                    <div class="row">
                                        <?php
                                        $gallery = json_decode($product['gallery'], true);
                                        if (is_array($gallery)) {
                                            foreach ($gallery as $image) {
                                                echo '<div class="col-md-4 mb-3">';
                                                echo '<a href="' . SITE_URL . '/uploads/products/' . $image . '" data-lightbox="product-gallery" data-title="' . htmlspecialchars($product['name']) . '">';
                                                echo '<img src="' . SITE_URL . '/uploads/products/' . $image . '" alt="Gallery Image" class="img-fluid rounded">';
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
                                    <h5 class="mb-0">معلومات المنتج</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered">
                                        <tbody>
                                            <tr>
                                                <th width="40%">المعرف</th>
                                                <td><?php echo $product['id']; ?></td>
                                            </tr>
                                            <tr>
                                                <th>رمز المنتج (SKU)</th>
                                                <td><?php echo htmlspecialchars($product['sku']); ?></td>
                                            </tr>
                                            <tr>
                                                <th>السعر</th>
                                                <td>
                                                    <?php if ($product['on_sale'] == 1 && !empty($product['sale_price'])): ?>
                                                        <span class="text-decoration-line-through text-muted">$<?php echo number_format($product['price'], 2); ?></span>
                                                        <span class="text-danger fw-bold">$<?php echo number_format($product['sale_price'], 2); ?></span>
                                                    <?php else: ?>
                                                        <span>$<?php echo number_format($product['price'], 2); ?></span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>الكمية المتوفرة</th>
                                                <td>
                                                    <?php if ($product['stock_quantity'] <= 0): ?>
                                                        <span class="text-danger"><?php echo $product['stock_quantity']; ?></span>
                                                    <?php elseif ($product['stock_quantity'] <= 5): ?>
                                                        <span class="text-warning"><?php echo $product['stock_quantity']; ?></span>
                                                    <?php else: ?>
                                                        <span class="text-success"><?php echo $product['stock_quantity']; ?></span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>التصنيف</th>
                                                <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                                            </tr>
                                            <tr>
                                                <th>الحالة</th>
                                                <td>
                                                    <?php
                                                    $statusClass = '';
                                                    $statusText = '';
                                                    switch ($product['status']) {
                                                        case 'active':
                                                            $statusClass = 'bg-success';
                                                            $statusText = 'نشط';
                                                            break;
                                                        case 'inactive':
                                                            $statusClass = 'bg-secondary';
                                                            $statusText = 'غير نشط';
                                                            break;
                                                        case 'out_of_stock':
                                                            $statusClass = 'bg-danger';
                                                            $statusText = 'نفذت الكمية';
                                                            break;
                                                        default:
                                                            $statusClass = 'bg-secondary';
                                                            $statusText = $product['status'];
                                                    }
                                                    ?>
                                                    <span class="badge <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>منتج مميز</th>
                                                <td>
                                                    <?php if ($product['featured'] == 1): ?>
                                                        <span class="badge bg-primary">نعم</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-light text-dark">لا</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>عرض خاص</th>
                                                <td>
                                                    <?php if ($product['on_sale'] == 1): ?>
                                                        <span class="badge bg-danger">نعم</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-light text-dark">لا</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php if (!empty($product['tags'])): ?>
                                                <tr>
                                                    <th>الوسوم</th>
                                                    <td>
                                                        <?php
                                                        $tags = explode(',', $product['tags']);
                                                        foreach ($tags as $tag) {
                                                            echo '<span class="badge bg-info me-1">' . htmlspecialchars(trim($tag)) . '</span>';
                                                        }
                                                        ?>
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                            <tr>
                                                <th>تاريخ الإنشاء</th>
                                                <td><?php echo date('Y-m-d H:i', strtotime($product['created_at'])); ?></td>
                                            </tr>
                                            <tr>
                                                <th>آخر تحديث</th>
                                                <td><?php echo date('Y-m-d H:i', strtotime($product['updated_at'])); ?></td>
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
                                        <a href="index.php?action=edit&id=<?php echo $product['id']; ?>" class="btn btn-primary">
                                            <i class="fa fa-edit"></i> تعديل المنتج
                                        </a>
                                        
                                        <?php if ($product['status'] === 'active'): ?>
                                            <a href="index.php?action=change_status&id=<?php echo $product['id']; ?>&status=inactive" class="btn btn-warning">
                                                <i class="fa fa-eye-slash"></i> إلغاء تنشيط المنتج
                                            </a>
                                        <?php else: ?>
                                            <a href="index.php?action=change_status&id=<?php echo $product['id']; ?>&status=active" class="btn btn-success">
                                                <i class="fa fa-eye"></i> تنشيط المنتج
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php if ($product['featured'] == 1): ?>
                                            <a href="index.php?action=toggle_featured&id=<?php echo $product['id']; ?>" class="btn btn-info">
                                                <i class="fa fa-star-o"></i> إلغاء تمييز المنتج
                                            </a>
                                        <?php else: ?>
                                            <a href="index.php?action=toggle_featured&id=<?php echo $product['id']; ?>" class="btn btn-info">
                                                <i class="fa fa-star"></i> تمييز المنتج
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php if ($product['on_sale'] == 1): ?>
                                            <a href="index.php?action=toggle_sale&id=<?php echo $product['id']; ?>" class="btn btn-secondary">
                                                <i class="fa fa-tag"></i> إلغاء العرض الخاص
                                            </a>
                                        <?php else: ?>
                                            <a href="index.php?action=toggle_sale&id=<?php echo $product['id']; ?>" class="btn btn-danger">
                                                <i class="fa fa-tag"></i> تفعيل العرض الخاص
                                            </a>
                                        <?php endif; ?>
                                        
                                        <a href="<?php echo SITE_URL . '/product-details.php?id=' . $product['id']; ?>" class="btn btn-secondary" target="_blank">
                                            <i class="fa fa-external-link"></i> عرض في الموقع
                                        </a>
                                        
                                        <a href="index.php?action=delete&id=<?php echo $product['id']; ?>" class="btn btn-danger" onclick="return confirm('هل أنت متأكد من حذف هذا المنتج؟')">
                                            <i class="fa fa-trash"></i> حذف المنتج
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h5 class="mb-0">إدارة المخزون</h5>
                                </div>
                                <div class="card-body">
                                    <form method="post" action="index.php?action=update_stock&id=<?php echo $product['id']; ?>">
                                        <div class="form-group mb-3">
                                            <label for="stock_adjustment">تعديل المخزون</label>
                                            <div class="input-group">
                                                <select name="adjustment_type" class="form-control">
                                                    <option value="add">إضافة</option>
                                                    <option value="subtract">خصم</option>
                                                    <option value="set">تعيين</option>
                                                </select>
                                                <input type="number" name="adjustment_value" id="stock_adjustment" class="form-control" value="1" min="1" required>
                                                <button type="submit" class="btn btn-primary">تحديث</button>
                                            </div>
                                            <small class="form-text text-muted">
                                                الكمية الحالية: <?php echo $product['stock_quantity']; ?>
                                            </small>
                                        </div>
                                    </form>
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

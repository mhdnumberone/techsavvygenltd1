<?php
/**
 * قالب تعديل خدمة
 * Edit Service Template
 */
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title">تعديل الخدمة</h4>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fa fa-arrow-left"></i> العودة إلى القائمة
                    </a>
                </div>
                <div class="card-body">
                    <?php if (isset($errors) && !empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form method="post" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group mb-3">
                                    <label for="title">عنوان الخدمة <span class="text-danger">*</span></label>
                                    <input type="text" name="title" id="title" class="form-control" value="<?php echo htmlspecialchars($service['title']); ?>" required>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="short_description">وصف مختصر <span class="text-danger">*</span></label>
                                    <textarea name="short_description" id="short_description" class="form-control" rows="3" required><?php echo htmlspecialchars($service['short_description']); ?></textarea>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="description">الوصف التفصيلي</label>
                                    <textarea name="description" id="description" class="form-control editor" rows="10"><?php echo htmlspecialchars($service['description']); ?></textarea>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="features">المميزات</label>
                                    <textarea name="features" id="features" class="form-control editor" rows="8"><?php echo htmlspecialchars($service['features']); ?></textarea>
                                    <small class="form-text text-muted">أدخل مميزات الخدمة، يفضل استخدام قائمة نقطية.</small>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="service_code">رمز الخدمة <span class="text-danger">*</span></label>
                                    <input type="text" name="service_code" id="service_code" class="form-control" value="<?php echo htmlspecialchars($service['service_code']); ?>" required>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="price">السعر <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" name="price" id="price" class="form-control" value="<?php echo htmlspecialchars($service['price']); ?>" step="0.01" min="0" required>
                                        <span class="input-group-text">$</span>
                                    </div>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="discount_price">سعر العرض</label>
                                    <div class="input-group">
                                        <input type="number" name="discount_price" id="discount_price" class="form-control" value="<?php echo htmlspecialchars($service['discount_price']); ?>" step="0.01" min="0">
                                        <span class="input-group-text">$</span>
                                    </div>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="duration">مدة التنفيذ</label>
                                    <div class="input-group">
                                        <input type="number" name="duration" id="duration" class="form-control" value="<?php echo htmlspecialchars($service['duration']); ?>" min="1">
                                        <select name="duration_unit" class="form-control">
                                            <option value="hour" <?php echo $service['duration_unit'] === 'hour' ? 'selected' : ''; ?>>ساعة</option>
                                            <option value="day" <?php echo $service['duration_unit'] === 'day' ? 'selected' : ''; ?>>يوم</option>
                                            <option value="week" <?php echo $service['duration_unit'] === 'week' ? 'selected' : ''; ?>>أسبوع</option>
                                            <option value="month" <?php echo $service['duration_unit'] === 'month' ? 'selected' : ''; ?>>شهر</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="category_id">التصنيف <span class="text-danger">*</span></label>
                                    <select name="category_id" id="category_id" class="form-control" required>
                                        <option value="">-- اختر التصنيف --</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?php echo $category['id']; ?>" <?php echo $service['category_id'] == $category['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($category['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="featured_image">الصورة الرئيسية</label>
                                    <?php if (!empty($service['featured_image'])): ?>
                                        <div class="mb-2">
                                            <img src="<?php echo SITE_URL . '/uploads/services/' . $service['featured_image']; ?>" alt="<?php echo htmlspecialchars($service['title']); ?>" class="img-thumbnail" style="max-width: 200px;">
                                        </div>
                                        <div class="form-check mb-2">
                                            <input type="checkbox" name="remove_featured_image" id="remove_featured_image" class="form-check-input" value="1">
                                            <label for="remove_featured_image" class="form-check-label">إزالة الصورة الحالية</label>
                                        </div>
                                    <?php endif; ?>
                                    <input type="file" name="featured_image" id="featured_image" class="form-control" accept="image/*">
                                    <small class="form-text text-muted">الحد الأقصى لحجم الصورة: 2 ميجابايت. الأبعاد المفضلة: 800×600 بكسل.</small>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="gallery">معرض الصور</label>
                                    <?php if (!empty($service['gallery'])): ?>
                                        <div class="mb-2">
                                            <?php
                                            $gallery = json_decode($service['gallery'], true);
                                            if (is_array($gallery)) {
                                                echo '<div class="row">';
                                                foreach ($gallery as $index => $image) {
                                                    echo '<div class="col-md-6 mb-2">';
                                                    echo '<div class="position-relative">';
                                                    echo '<img src="' . SITE_URL . '/uploads/services/' . $image . '" alt="Gallery Image ' . ($index + 1) . '" class="img-thumbnail" style="max-width: 100%;">';
                                                    echo '<div class="form-check mt-1">';
                                                    echo '<input type="checkbox" name="remove_gallery[]" id="remove_gallery_' . $index . '" class="form-check-input" value="' . $image . '">';
                                                    echo '<label for="remove_gallery_' . $index . '" class="form-check-label">إزالة</label>';
                                                    echo '</div>';
                                                    echo '</div>';
                                                    echo '</div>';
                                                }
                                                echo '</div>';
                                            }
                                            ?>
                                        </div>
                                    <?php endif; ?>
                                    <input type="file" name="gallery[]" id="gallery" class="form-control" accept="image/*" multiple>
                                    <small class="form-text text-muted">يمكنك تحميل عدة صور (الحد الأقصى: 5 صور، 2 ميجابايت لكل صورة).</small>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="tags">الوسوم</label>
                                    <input type="text" name="tags" id="tags" class="form-control" value="<?php echo htmlspecialchars($service['tags']); ?>">
                                    <small class="form-text text-muted">افصل بين الوسوم بفواصل.</small>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="status">الحالة <span class="text-danger">*</span></label>
                                    <select name="status" id="status" class="form-control" required>
                                        <option value="active" <?php echo $service['status'] === 'active' ? 'selected' : ''; ?>>نشط</option>
                                        <option value="inactive" <?php echo $service['status'] === 'inactive' ? 'selected' : ''; ?>>غير نشط</option>
                                    </select>
                                </div>
                                
                                <div class="form-check mb-3">
                                    <input type="checkbox" name="featured" id="featured" class="form-check-input" value="1" <?php echo $service['featured'] == 1 ? 'checked' : ''; ?>>
                                    <label for="featured" class="form-check-label">خدمة مميزة</label>
                                </div>
                                
                                <div class="form-check mb-3">
                                    <input type="checkbox" name="on_sale" id="on_sale" class="form-check-input" value="1" <?php echo $service['on_sale'] == 1 ? 'checked' : ''; ?>>
                                    <label for="on_sale" class="form-check-label">عرض خاص</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save"></i> حفظ التغييرات
                            </button>
                            <a href="index.php" class="btn btn-secondary">
                                <i class="fa fa-times"></i> إلغاء
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // تهيئة محرر النصوص
        // Initialize text editor
        if (typeof ClassicEditor !== 'undefined') {
            ClassicEditor
                .create(document.querySelector('#description'), {
                    language: 'ar',
                    toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|', 'outdent', 'indent', '|', 'blockQuote', 'insertTable', 'mediaEmbed', 'undo', 'redo']
                })
                .catch(error => {
                    console.error(error);
                });
                
            ClassicEditor
                .create(document.querySelector('#features'), {
                    language: 'ar',
                    toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|', 'outdent', 'indent', '|', 'blockQuote', 'insertTable', 'undo', 'redo']
                })
                .catch(error => {
                    console.error(error);
                });
        }
        
        // تهيئة مدخل الوسوم
        // Initialize tags input
        if (typeof Tagify !== 'undefined') {
            new Tagify(document.querySelector('#tags'));
        }
    });
</script>

<?php
/**
 * قالب تعديل إنجاز
 * Edit Achievement Template
 */
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title">تعديل الإنجاز</h4>
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
                                    <label for="title">عنوان الإنجاز <span class="text-danger">*</span></label>
                                    <input type="text" name="title" id="title" class="form-control" value="<?php echo htmlspecialchars($achievement['title']); ?>" required>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="description">وصف الإنجاز <span class="text-danger">*</span></label>
                                    <textarea name="description" id="description" class="form-control" rows="5" required><?php echo htmlspecialchars($achievement['description']); ?></textarea>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="content">محتوى الإنجاز</label>
                                    <textarea name="content" id="content" class="form-control editor" rows="10"><?php echo htmlspecialchars($achievement['content']); ?></textarea>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="client_name">اسم العميل</label>
                                    <input type="text" name="client_name" id="client_name" class="form-control" value="<?php echo htmlspecialchars($achievement['client_name']); ?>">
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="completion_date">تاريخ الإنجاز</label>
                                    <input type="date" name="completion_date" id="completion_date" class="form-control" value="<?php echo htmlspecialchars($achievement['completion_date']); ?>">
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="category">التصنيف <span class="text-danger">*</span></label>
                                    <select name="category" id="category" class="form-control" required>
                                        <option value="">-- اختر التصنيف --</option>
                                        <option value="web_development" <?php echo $achievement['category'] === 'web_development' ? 'selected' : ''; ?>>تطوير الويب</option>
                                        <option value="mobile_apps" <?php echo $achievement['category'] === 'mobile_apps' ? 'selected' : ''; ?>>تطبيقات الجوال</option>
                                        <option value="desktop_apps" <?php echo $achievement['category'] === 'desktop_apps' ? 'selected' : ''; ?>>تطبيقات سطح المكتب</option>
                                        <option value="ui_ux" <?php echo $achievement['category'] === 'ui_ux' ? 'selected' : ''; ?>>تصميم واجهات المستخدم</option>
                                        <option value="consulting" <?php echo $achievement['category'] === 'consulting' ? 'selected' : ''; ?>>استشارات تقنية</option>
                                        <option value="other" <?php echo $achievement['category'] === 'other' ? 'selected' : ''; ?>>أخرى</option>
                                    </select>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="featured_image">الصورة الرئيسية</label>
                                    <?php if (!empty($achievement['featured_image'])): ?>
                                        <div class="mb-2">
                                            <img src="<?php echo SITE_URL . '/uploads/achievements/' . $achievement['featured_image']; ?>" alt="<?php echo htmlspecialchars($achievement['title']); ?>" class="img-thumbnail" style="max-width: 200px;">
                                        </div>
                                        <div class="form-check mb-2">
                                            <input type="checkbox" name="remove_featured_image" id="remove_featured_image" class="form-check-input" value="1">
                                            <label for="remove_featured_image" class="form-check-label">إزالة الصورة الحالية</label>
                                        </div>
                                    <?php endif; ?>
                                    <input type="file" name="featured_image" id="featured_image" class="form-control" accept="image/*">
                                    <small class="form-text text-muted">الحد الأقصى لحجم الصورة: 2 ميجابايت. الأبعاد المفضلة: 1200×800 بكسل.</small>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="gallery">معرض الصور</label>
                                    <?php if (!empty($achievement['gallery'])): ?>
                                        <div class="mb-2">
                                            <?php
                                            $gallery = json_decode($achievement['gallery'], true);
                                            if (is_array($gallery)) {
                                                echo '<div class="row">';
                                                foreach ($gallery as $index => $image) {
                                                    echo '<div class="col-md-6 mb-2">';
                                                    echo '<div class="position-relative">';
                                                    echo '<img src="' . SITE_URL . '/uploads/achievements/' . $image . '" alt="Gallery Image ' . ($index + 1) . '" class="img-thumbnail" style="max-width: 100%;">';
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
                                    <input type="text" name="tags" id="tags" class="form-control" value="<?php echo htmlspecialchars($achievement['tags']); ?>">
                                    <small class="form-text text-muted">افصل بين الوسوم بفواصل.</small>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="status">الحالة <span class="text-danger">*</span></label>
                                    <select name="status" id="status" class="form-control" required>
                                        <option value="published" <?php echo $achievement['status'] === 'published' ? 'selected' : ''; ?>>منشور</option>
                                        <option value="draft" <?php echo $achievement['status'] === 'draft' ? 'selected' : ''; ?>>مسودة</option>
                                    </select>
                                </div>
                                
                                <div class="form-check mb-3">
                                    <input type="checkbox" name="featured" id="featured" class="form-check-input" value="1" <?php echo $achievement['featured'] == 1 ? 'checked' : ''; ?>>
                                    <label for="featured" class="form-check-label">إنجاز مميز</label>
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
                .create(document.querySelector('#content'), {
                    language: 'ar',
                    toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|', 'outdent', 'indent', '|', 'blockQuote', 'insertTable', 'mediaEmbed', 'undo', 'redo']
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

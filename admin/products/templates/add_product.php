<?php
/**
 * قالب إضافة منتج جديد
 * Add Product Template
 */
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title">إضافة منتج جديد</h4>
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
                                    <label for="title">عنوان المنتج <span class="text-danger">*</span></label>
                                    <input type="text" name="title" id="title" class="form-control" value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>" required>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="short_description">وصف مختصر <span class="text-danger">*</span></label>
                                    <textarea name="short_description" id="short_description" class="form-control" rows="3" required><?php echo isset($_POST['short_description']) ? htmlspecialchars($_POST['short_description']) : ''; ?></textarea>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="description">الوصف التفصيلي</label>
                                    <textarea name="description" id="description" class="form-control editor" rows="10"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="specifications">المواصفات</label>
                                    <textarea name="specifications" id="specifications" class="form-control editor" rows="8"><?php echo isset($_POST['specifications']) ? htmlspecialchars($_POST['specifications']) : ''; ?></textarea>
                                    <small class="form-text text-muted">أدخل مواصفات المنتج، يفضل استخدام قائمة نقطية.</small>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="product_code">رمز المنتج <span class="text-danger">*</span></label>
                                    <input type="text" name="product_code" id="product_code" class="form-control" value="<?php echo isset($_POST['product_code']) ? htmlspecialchars($_POST['product_code']) : ''; ?>" required>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="price">السعر <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" name="price" id="price" class="form-control" value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : ''; ?>" step="0.01" min="0" required>
                                        <span class="input-group-text">$</span>
                                    </div>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="discount_price">سعر العرض</label>
                                    <div class="input-group">
                                        <input type="number" name="discount_price" id="discount_price" class="form-control" value="<?php echo isset($_POST['discount_price']) ? htmlspecialchars($_POST['discount_price']) : ''; ?>" step="0.01" min="0">
                                        <span class="input-group-text">$</span>
                                    </div>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="stock_quantity">الكمية المتوفرة <span class="text-danger">*</span></label>
                                    <input type="number" name="stock_quantity" id="stock_quantity" class="form-control" value="<?php echo isset($_POST['stock_quantity']) ? htmlspecialchars($_POST['stock_quantity']) : ''; ?>" min="0" required>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="category_id">التصنيف <span class="text-danger">*</span></label>
                                    <select name="category_id" id="category_id" class="form-control" required>
                                        <option value="">-- اختر التصنيف --</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?php echo $category['id']; ?>" <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($category['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="featured_image">الصورة الرئيسية <span class="text-danger">*</span></label>
                                    <input type="file" name="featured_image" id="featured_image" class="form-control" accept="image/*" required>
                                    <small class="form-text text-muted">الحد الأقصى لحجم الصورة: 2 ميجابايت. الأبعاد المفضلة: 800×600 بكسل.</small>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="gallery">معرض الصور</label>
                                    <input type="file" name="gallery[]" id="gallery" class="form-control" accept="image/*" multiple>
                                    <small class="form-text text-muted">يمكنك تحميل عدة صور (الحد الأقصى: 5 صور، 2 ميجابايت لكل صورة).</small>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="tags">الوسوم</label>
                                    <input type="text" name="tags" id="tags" class="form-control" value="<?php echo isset($_POST['tags']) ? htmlspecialchars($_POST['tags']) : ''; ?>">
                                    <small class="form-text text-muted">افصل بين الوسوم بفواصل.</small>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="status">الحالة <span class="text-danger">*</span></label>
                                    <select name="status" id="status" class="form-control" required>
                                        <option value="active" <?php echo (!isset($_POST['status']) || $_POST['status'] === 'active') ? 'selected' : ''; ?>>نشط</option>
                                        <option value="inactive" <?php echo (isset($_POST['status']) && $_POST['status'] === 'inactive') ? 'selected' : ''; ?>>غير نشط</option>
                                        <option value="out_of_stock" <?php echo (isset($_POST['status']) && $_POST['status'] === 'out_of_stock') ? 'selected' : ''; ?>>نفذت الكمية</option>
                                    </select>
                                </div>
                                
                                <div class="form-check mb-3">
                                    <input type="checkbox" name="featured" id="featured" class="form-check-input" value="1" <?php echo (isset($_POST['featured']) && $_POST['featured'] == 1) ? 'checked' : ''; ?>>
                                    <label for="featured" class="form-check-label">منتج مميز</label>
                                </div>
                                
                                <div class="form-check mb-3">
                                    <input type="checkbox" name="on_sale" id="on_sale" class="form-check-input" value="1" <?php echo (isset($_POST['on_sale']) && $_POST['on_sale'] == 1) ? 'checked' : ''; ?>>
                                    <label for="on_sale" class="form-check-label">عرض خاص</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save"></i> حفظ المنتج
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
                .create(document.querySelector('#specifications'), {
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
        
        // التحقق من سعر العرض
        // Validate discount price
        const priceField = document.getElementById('price');
        const discountPriceField = document.getElementById('discount_price');
        
        function validateDiscountPrice() {
            const price = parseFloat(priceField.value);
            const discountPrice = parseFloat(discountPriceField.value);
            
            if (discountPrice && price && discountPrice >= price) {
                discountPriceField.setCustomValidity('يجب أن يكون سعر العرض أقل من السعر الأصلي');
            } else {
                discountPriceField.setCustomValidity('');
            }
        }
        
        priceField.addEventListener('change', validateDiscountPrice);
        discountPriceField.addEventListener('change', validateDiscountPrice);
        
        // توليد رمز المنتج تلقائيًا
        // Auto-generate product code
        const titleField = document.getElementById('title');
        const productCodeField = document.getElementById('product_code');
        
        titleField.addEventListener('blur', function() {
            if (productCodeField.value === '') {
                const title = titleField.value.trim();
                if (title) {
                    // استخراج الأحرف الأولى من كل كلمة وتحويلها إلى أحرف كبيرة
                    // Extract first letters from each word and convert to uppercase
                    const words = title.split(' ');
                    let code = '';
                    
                    for (let i = 0; i < Math.min(words.length, 3); i++) {
                        if (words[i].length > 0) {
                            code += words[i].charAt(0).toUpperCase();
                        }
                    }
                    
                    // إضافة رقم عشوائي
                    // Add random number
                    code += Math.floor(1000 + Math.random() * 9000);
                    
                    productCodeField.value = code;
                }
            }
        });
    });
</script>

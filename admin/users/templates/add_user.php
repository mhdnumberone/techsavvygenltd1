<?php
/**
 * قالب إضافة مستخدم جديد
 * Add User Template
 */
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title">إضافة مستخدم جديد</h4>
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
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="first_name">الاسم الأول <span class="text-danger">*</span></label>
                                    <input type="text" name="first_name" id="first_name" class="form-control" value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>" required>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="last_name">الاسم الأخير <span class="text-danger">*</span></label>
                                    <input type="text" name="last_name" id="last_name" class="form-control" value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>" required>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="email">البريد الإلكتروني <span class="text-danger">*</span></label>
                                    <input type="email" name="email" id="email" class="form-control" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="phone">رقم الهاتف</label>
                                    <input type="text" name="phone" id="phone" class="form-control" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="password">كلمة المرور <span class="text-danger">*</span></label>
                                    <input type="password" name="password" id="password" class="form-control" required>
                                    <small class="form-text text-muted">يجب أن تحتوي كلمة المرور على 8 أحرف على الأقل، وتتضمن أحرفًا كبيرة وصغيرة وأرقامًا ورموزًا.</small>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="confirm_password">تأكيد كلمة المرور <span class="text-danger">*</span></label>
                                    <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="role">الدور <span class="text-danger">*</span></label>
                                    <select name="role" id="role" class="form-control" required>
                                        <option value="">-- اختر الدور --</option>
                                        <option value="admin" <?php echo (isset($_POST['role']) && $_POST['role'] === 'admin') ? 'selected' : ''; ?>>مدير</option>
                                        <option value="manager" <?php echo (isset($_POST['role']) && $_POST['role'] === 'manager') ? 'selected' : ''; ?>>مشرف</option>
                                        <option value="editor" <?php echo (isset($_POST['role']) && $_POST['role'] === 'editor') ? 'selected' : ''; ?>>محرر</option>
                                        <option value="customer" <?php echo (isset($_POST['role']) && $_POST['role'] === 'customer') ? 'selected' : ''; ?>>عميل</option>
                                    </select>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="status">الحالة <span class="text-danger">*</span></label>
                                    <select name="status" id="status" class="form-control" required>
                                        <option value="active" <?php echo (!isset($_POST['status']) || $_POST['status'] === 'active') ? 'selected' : ''; ?>>نشط</option>
                                        <option value="inactive" <?php echo (isset($_POST['status']) && $_POST['status'] === 'inactive') ? 'selected' : ''; ?>>غير نشط</option>
                                        <option value="pending" <?php echo (isset($_POST['status']) && $_POST['status'] === 'pending') ? 'selected' : ''; ?>>قيد الانتظار</option>
                                        <option value="blocked" <?php echo (isset($_POST['status']) && $_POST['status'] === 'blocked') ? 'selected' : ''; ?>>محظور</option>
                                    </select>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="profile_image">صورة الملف الشخصي</label>
                                    <input type="file" name="profile_image" id="profile_image" class="form-control" accept="image/*">
                                    <small class="form-text text-muted">الحد الأقصى لحجم الصورة: 2 ميجابايت. الأبعاد المفضلة: 300×300 بكسل.</small>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="address">العنوان</label>
                                    <textarea name="address" id="address" class="form-control" rows="3"><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="bio">نبذة تعريفية</label>
                                    <textarea name="bio" id="bio" class="form-control" rows="4"><?php echo isset($_POST['bio']) ? htmlspecialchars($_POST['bio']) : ''; ?></textarea>
                                </div>
                                
                                <div class="form-check mb-3">
                                    <input type="checkbox" name="send_welcome_email" id="send_welcome_email" class="form-check-input" value="1" <?php echo (isset($_POST['send_welcome_email']) && $_POST['send_welcome_email'] == 1) ? 'checked' : ''; ?> checked>
                                    <label for="send_welcome_email" class="form-check-label">إرسال بريد إلكتروني ترحيبي</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save"></i> حفظ المستخدم
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
        // التحقق من تطابق كلمة المرور
        // Check password match
        const passwordField = document.getElementById('password');
        const confirmPasswordField = document.getElementById('confirm_password');
        
        function checkPasswordMatch() {
            if (confirmPasswordField.value !== passwordField.value) {
                confirmPasswordField.setCustomValidity('كلمات المرور غير متطابقة');
            } else {
                confirmPasswordField.setCustomValidity('');
            }
        }
        
        passwordField.addEventListener('change', checkPasswordMatch);
        confirmPasswordField.addEventListener('keyup', checkPasswordMatch);
        
        // التحقق من قوة كلمة المرور
        // Check password strength
        passwordField.addEventListener('keyup', function() {
            const password = this.value;
            let strength = 0;
            
            // التحقق من طول كلمة المرور
            if (password.length >= 8) {
                strength += 1;
            }
            
            // التحقق من وجود أحرف كبيرة وصغيرة
            if (password.match(/[a-z]/) && password.match(/[A-Z]/)) {
                strength += 1;
            }
            
            // التحقق من وجود أرقام
            if (password.match(/\d/)) {
                strength += 1;
            }
            
            // التحقق من وجود رموز خاصة
            if (password.match(/[^a-zA-Z\d]/)) {
                strength += 1;
            }
            
            // تحديث حالة قوة كلمة المرور
            const strengthIndicator = document.createElement('div');
            strengthIndicator.id = 'password-strength';
            
            let strengthText = '';
            let strengthClass = '';
            
            switch (strength) {
                case 0:
                case 1:
                    strengthText = 'ضعيفة';
                    strengthClass = 'text-danger';
                    break;
                case 2:
                    strengthText = 'متوسطة';
                    strengthClass = 'text-warning';
                    break;
                case 3:
                    strengthText = 'جيدة';
                    strengthClass = 'text-info';
                    break;
                case 4:
                    strengthText = 'قوية';
                    strengthClass = 'text-success';
                    break;
            }
            
            const existingIndicator = document.getElementById('password-strength');
            if (existingIndicator) {
                existingIndicator.className = strengthClass;
                existingIndicator.textContent = 'قوة كلمة المرور: ' + strengthText;
            } else {
                strengthIndicator.className = strengthClass;
                strengthIndicator.textContent = 'قوة كلمة المرور: ' + strengthText;
                this.parentNode.appendChild(strengthIndicator);
            }
        });
    });
</script>

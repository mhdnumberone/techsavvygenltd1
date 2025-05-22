<?php
/**
 * قالب تعديل مستخدم
 * Edit User Template
 */
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title">تعديل المستخدم</h4>
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
                                    <input type="text" name="first_name" id="first_name" class="form-control" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="last_name">الاسم الأخير <span class="text-danger">*</span></label>
                                    <input type="text" name="last_name" id="last_name" class="form-control" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="email">البريد الإلكتروني <span class="text-danger">*</span></label>
                                    <input type="email" name="email" id="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="phone">رقم الهاتف</label>
                                    <input type="text" name="phone" id="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone']); ?>">
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="password">كلمة المرور الجديدة</label>
                                    <input type="password" name="password" id="password" class="form-control">
                                    <small class="form-text text-muted">اتركها فارغة إذا كنت لا ترغب في تغيير كلمة المرور.</small>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="confirm_password">تأكيد كلمة المرور الجديدة</label>
                                    <input type="password" name="confirm_password" id="confirm_password" class="form-control">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="role">الدور <span class="text-danger">*</span></label>
                                    <select name="role" id="role" class="form-control" required>
                                        <option value="">-- اختر الدور --</option>
                                        <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>مدير</option>
                                        <option value="manager" <?php echo $user['role'] === 'manager' ? 'selected' : ''; ?>>مشرف</option>
                                        <option value="editor" <?php echo $user['role'] === 'editor' ? 'selected' : ''; ?>>محرر</option>
                                        <option value="customer" <?php echo $user['role'] === 'customer' ? 'selected' : ''; ?>>عميل</option>
                                    </select>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="status">الحالة <span class="text-danger">*</span></label>
                                    <select name="status" id="status" class="form-control" required>
                                        <option value="active" <?php echo $user['status'] === 'active' ? 'selected' : ''; ?>>نشط</option>
                                        <option value="inactive" <?php echo $user['status'] === 'inactive' ? 'selected' : ''; ?>>غير نشط</option>
                                        <option value="pending" <?php echo $user['status'] === 'pending' ? 'selected' : ''; ?>>قيد الانتظار</option>
                                        <option value="blocked" <?php echo $user['status'] === 'blocked' ? 'selected' : ''; ?>>محظور</option>
                                    </select>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="profile_image">صورة الملف الشخصي</label>
                                    <?php if (!empty($user['profile_image'])): ?>
                                        <div class="mb-2">
                                            <img src="<?php echo SITE_URL . '/uploads/users/' . $user['profile_image']; ?>" alt="<?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>" class="img-thumbnail" style="max-width: 150px;">
                                        </div>
                                        <div class="form-check mb-2">
                                            <input type="checkbox" name="remove_profile_image" id="remove_profile_image" class="form-check-input" value="1">
                                            <label for="remove_profile_image" class="form-check-label">إزالة الصورة الحالية</label>
                                        </div>
                                    <?php endif; ?>
                                    <input type="file" name="profile_image" id="profile_image" class="form-control" accept="image/*">
                                    <small class="form-text text-muted">الحد الأقصى لحجم الصورة: 2 ميجابايت. الأبعاد المفضلة: 300×300 بكسل.</small>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="address">العنوان</label>
                                    <textarea name="address" id="address" class="form-control" rows="3"><?php echo htmlspecialchars($user['address']); ?></textarea>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="bio">نبذة تعريفية</label>
                                    <textarea name="bio" id="bio" class="form-control" rows="4"><?php echo htmlspecialchars($user['bio']); ?></textarea>
                                </div>
                                
                                <div class="form-check mb-3">
                                    <input type="checkbox" name="send_notification_email" id="send_notification_email" class="form-check-input" value="1">
                                    <label for="send_notification_email" class="form-check-label">إرسال بريد إلكتروني بالتغييرات</label>
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
            
            // إذا كانت كلمة المرور فارغة، لا تعرض مؤشر القوة
            if (password === '') {
                const existingIndicator = document.getElementById('password-strength');
                if (existingIndicator) {
                    existingIndicator.remove();
                }
                return;
            }
            
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

<?php
/**
 * ملف تذييل لوحة التحكم
 * Admin Footer File
 */
?>
        </main>
    </div>
    
    <!-- سكريبت لوحة التحكم -->
    <!-- Admin Script -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // تبديل الشريط الجانبي
        // Toggle sidebar
        const sidebarToggle = document.getElementById('sidebarToggle');
        const adminContainer = document.querySelector('.admin-container');
        
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function() {
                adminContainer.classList.toggle('sidebar-collapsed');
                
                // حفظ حالة الشريط الجانبي في التخزين المحلي
                // Save sidebar state in local storage
                const isSidebarCollapsed = adminContainer.classList.contains('sidebar-collapsed');
                localStorage.setItem('sidebarCollapsed', isSidebarCollapsed);
            });
        }
        
        // استعادة حالة الشريط الجانبي من التخزين المحلي
        // Restore sidebar state from local storage
        const savedSidebarState = localStorage.getItem('sidebarCollapsed');
        if (savedSidebarState === 'true') {
            adminContainer.classList.add('sidebar-collapsed');
        }
        
        // تهيئة التلميحات
        // Initialize tooltips
        $('[data-toggle="tooltip"]').tooltip();
        
        // تهيئة النوافذ المنبثقة
        // Initialize popovers
        $('[data-toggle="popover"]').popover();
        
        // تأكيد الحذف
        // Confirm delete
        const deleteButtons = document.querySelectorAll('.btn-delete');
        deleteButtons.forEach(function(button) {
            button.addEventListener('click', function(e) {
                if (!confirm('هل أنت متأكد من رغبتك في الحذف؟')) {
                    e.preventDefault();
                }
            });
        });
        
        // تحديث الإشعارات
        // Update notifications
        function updateNotifications() {
            fetch('<?php echo SITE_URL; ?>/admin/notifications/get-unread.php')
                .then(response => response.json())
                .then(data => {
                    const notificationBadge = document.querySelector('.admin-notifications .badge');
                    if (data.count > 0) {
                        if (notificationBadge) {
                            notificationBadge.textContent = data.count;
                            notificationBadge.style.display = 'inline-block';
                        } else {
                            const newBadge = document.createElement('span');
                            newBadge.className = 'badge badge-danger';
                            newBadge.textContent = data.count;
                            document.querySelector('.admin-notifications .btn').appendChild(newBadge);
                        }
                    } else {
                        if (notificationBadge) {
                            notificationBadge.style.display = 'none';
                        }
                    }
                })
                .catch(error => console.error('Error updating notifications:', error));
        }
        
        // تحديث الإشعارات كل دقيقة
        // Update notifications every minute
        setInterval(updateNotifications, 60000);
        
        // تهيئة محرر النصوص المتقدم إذا كان موجوداً
        // Initialize rich text editor if exists
        if (typeof ClassicEditor !== 'undefined' && document.querySelector('.rich-editor')) {
            ClassicEditor
                .create(document.querySelector('.rich-editor'), {
                    language: 'ar',
                    toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|', 'indent', 'outdent', '|', 'imageUpload', 'blockQuote', 'insertTable', 'mediaEmbed', 'undo', 'redo']
                })
                .catch(error => {
                    console.error(error);
                });
        }
        
        // تهيئة مختار التاريخ إذا كان موجوداً
        // Initialize date picker if exists
        if (typeof $.fn.datepicker !== 'undefined' && document.querySelector('.datepicker')) {
            $('.datepicker').datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true,
                language: 'ar',
                rtl: true
            });
        }
        
        // تهيئة مختار الوقت إذا كان موجوداً
        // Initialize time picker if exists
        if (typeof $.fn.timepicker !== 'undefined' && document.querySelector('.timepicker')) {
            $('.timepicker').timepicker({
                showMeridian: false,
                minuteStep: 5
            });
        }
        
        // تهيئة مختار الملفات المتعددة إذا كان موجوداً
        // Initialize multiple file selector if exists
        if (document.querySelector('.custom-file-input')) {
            document.querySelectorAll('.custom-file-input').forEach(function(input) {
                input.addEventListener('change', function(e) {
                    const fileName = Array.from(this.files)
                        .map(file => file.name)
                        .join(', ');
                    
                    const label = this.nextElementSibling;
                    label.textContent = fileName || 'اختر الملفات';
                });
            });
        }
    });
    </script>
</body>
</html>

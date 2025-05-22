/**
 * الملفات الرئيسية للجافاسكريبت
 * Main JavaScript Files
 *
 * @package TechSavvyGenLtd
 * @version 1.0.0
 */

// ===== المتغيرات العامة =====
// ===== Global Variables =====
const SITE_URL = window.location.origin;
const IS_RTL = document.documentElement.dir === 'rtl';

// ===== وظائف الجاهزية =====
// ===== Ready Functions =====
document.addEventListener('DOMContentLoaded', function() {
    initNavigation();
    initSliders();
    initCounters();
    initProductGallery();
    initQuantityInputs();
    initFilterAccordions();
    initTooltips();
    initFormValidation();
    initAjaxForms();
    initLazyLoading();
    initBackToTop();
});

// ===== وظائف التنقل =====
// ===== Navigation Functions =====
function initNavigation() {
    // التنقل المتجاوب
    // Responsive navigation
    const menuToggle = document.querySelector('.navbar-toggler');
    if (menuToggle) {
        menuToggle.addEventListener('click', function() {
            const navbarCollapse = document.querySelector('.navbar-collapse');
            navbarCollapse.classList.toggle('show');
        });
    }

    // القوائم المنسدلة
    // Dropdown menus
    const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
    dropdownToggles.forEach(function(toggle) {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            const parent = this.parentElement;
            parent.classList.toggle('show');
            const dropdownMenu = parent.querySelector('.dropdown-menu');
            dropdownMenu.classList.toggle('show');
        });
    });

    // إغلاق القوائم المنسدلة عند النقر خارجها
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        const dropdowns = document.querySelectorAll('.dropdown.show');
        dropdowns.forEach(function(dropdown) {
            if (!dropdown.contains(e.target)) {
                dropdown.classList.remove('show');
                const dropdownMenu = dropdown.querySelector('.dropdown-menu');
                if (dropdownMenu) {
                    dropdownMenu.classList.remove('show');
                }
            }
        });
    });

    // تثبيت الرأس عند التمرير
    // Sticky header on scroll
    const header = document.querySelector('.header');
    if (header) {
        const headerOffset = header.offsetTop;
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > headerOffset) {
                header.classList.add('sticky');
            } else {
                header.classList.remove('sticky');
            }
        });
    }
}

// ===== وظائف السلايدر =====
// ===== Slider Functions =====
function initSliders() {
    // سلايدر البانر الرئيسي
    // Main banner slider
    const mainBanner = document.querySelector('.main-banner-slider');
    if (mainBanner) {
        // يمكن استخدام مكتبة Swiper أو Slick هنا
        // Can use Swiper or Slick library here
        // هذا مثال بسيط للتنفيذ
        // This is a simple implementation example
        const slides = mainBanner.querySelectorAll('.slide');
        let currentSlide = 0;
        
        function showSlide(index) {
            slides.forEach(function(slide, i) {
                slide.style.display = i === index ? 'block' : 'none';
            });
        }
        
        function nextSlide() {
            currentSlide = (currentSlide + 1) % slides.length;
            showSlide(currentSlide);
        }
        
        function prevSlide() {
            currentSlide = (currentSlide - 1 + slides.length) % slides.length;
            showSlide(currentSlide);
        }
        
        // أزرار التنقل
        // Navigation buttons
        const nextButton = mainBanner.querySelector('.slider-next');
        const prevButton = mainBanner.querySelector('.slider-prev');
        
        if (nextButton) {
            nextButton.addEventListener('click', nextSlide);
        }
        
        if (prevButton) {
            prevButton.addEventListener('click', prevSlide);
        }
        
        // التشغيل التلقائي
        // Auto play
        setInterval(nextSlide, 5000);
        
        // عرض الشريحة الأولى
        // Show first slide
        showSlide(0);
    }
    
    // سلايدر الشهادات
    // Testimonials slider
    const testimonialsSlider = document.querySelector('.testimonials-slider');
    if (testimonialsSlider) {
        // تنفيذ مشابه للسلايدر الرئيسي
        // Similar implementation as main slider
    }
    
    // سلايدر الشركاء
    // Partners slider
    const partnersSlider = document.querySelector('.partners-slider');
    if (partnersSlider) {
        // تنفيذ مشابه للسلايدر الرئيسي
        // Similar implementation as main slider
    }
}

// ===== وظائف العدادات =====
// ===== Counter Functions =====
function initCounters() {
    const counters = document.querySelectorAll('.statistic-number');
    
    function startCounting() {
        counters.forEach(function(counter) {
            const target = parseInt(counter.getAttribute('data-target'));
            const duration = 2000; // مدة العد بالمللي ثانية
            const step = target / (duration / 16); // 16ms لكل إطار تقريبًا
            let current = 0;
            
            const updateCounter = function() {
                current += step;
                if (current < target) {
                    counter.textContent = Math.floor(current);
                    requestAnimationFrame(updateCounter);
                } else {
                    counter.textContent = target;
                }
            };
            
            updateCounter();
        });
    }
    
    // بدء العد عند ظهور العنصر في نطاق الرؤية
    // Start counting when element is in viewport
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                startCounting();
                observer.unobserve(entry.target);
            }
        });
    });
    
    if (counters.length > 0) {
        observer.observe(document.querySelector('.statistics-section'));
    }
}

// ===== وظائف معرض المنتج =====
// ===== Product Gallery Functions =====
function initProductGallery() {
    const mainImage = document.querySelector('.product-main-image img');
    const thumbnails = document.querySelectorAll('.product-thumbnail');
    
    if (mainImage && thumbnails.length > 0) {
        thumbnails.forEach(function(thumbnail) {
            thumbnail.addEventListener('click', function() {
                // تحديث الصورة الرئيسية
                // Update main image
                const imgSrc = this.querySelector('img').getAttribute('src');
                mainImage.setAttribute('src', imgSrc);
                
                // تحديث الفئة النشطة
                // Update active class
                thumbnails.forEach(function(thumb) {
                    thumb.classList.remove('active');
                });
                this.classList.add('active');
            });
        });
    }
}

// ===== وظائف مدخلات الكمية =====
// ===== Quantity Input Functions =====
function initQuantityInputs() {
    const quantityContainers = document.querySelectorAll('.quantity-input');
    
    quantityContainers.forEach(function(container) {
        const input = container.querySelector('input');
        const increaseBtn = container.querySelector('.quantity-increase');
        const decreaseBtn = container.querySelector('.quantity-decrease');
        
        if (input && increaseBtn && decreaseBtn) {
            increaseBtn.addEventListener('click', function() {
                let value = parseInt(input.value);
                value = isNaN(value) ? 0 : value;
                value++;
                input.value = value;
                
                // تشغيل حدث التغيير
                // Trigger change event
                const event = new Event('change');
                input.dispatchEvent(event);
            });
            
            decreaseBtn.addEventListener('click', function() {
                let value = parseInt(input.value);
                value = isNaN(value) ? 0 : value;
                value = Math.max(1, value - 1);
                input.value = value;
                
                // تشغيل حدث التغيير
                // Trigger change event
                const event = new Event('change');
                input.dispatchEvent(event);
            });
            
            input.addEventListener('change', function() {
                let value = parseInt(this.value);
                value = isNaN(value) ? 1 : value;
                value = Math.max(1, value);
                this.value = value;
                
                // يمكن إضافة وظائف إضافية هنا مثل تحديث السعر الإجمالي
                // Can add additional functions here like updating total price
                updateCartTotals();
            });
        }
    });
}

// ===== وظائف تحديث السلة =====
// ===== Cart Update Functions =====
function updateCartTotals() {
    const cartItems = document.querySelectorAll('.cart-item');
    let subtotal = 0;
    
    cartItems.forEach(function(item) {
        const price = parseFloat(item.querySelector('.cart-price').getAttribute('data-price'));
        const quantity = parseInt(item.querySelector('.cart-quantity input').value);
        const total = price * quantity;
        
        // تحديث إجمالي العنصر
        // Update item total
        const itemTotal = item.querySelector('.cart-total');
        if (itemTotal) {
            itemTotal.textContent = total.toFixed(2);
        }
        
        subtotal += total;
    });
    
    // تحديث المجموع الفرعي
    // Update subtotal
    const subtotalElement = document.querySelector('.cart-summary-subtotal');
    if (subtotalElement) {
        subtotalElement.textContent = subtotal.toFixed(2);
    }
    
    // حساب الضريبة
    // Calculate tax
    const taxRate = parseFloat(document.querySelector('.cart-summary-tax').getAttribute('data-rate') || 0);
    const taxAmount = subtotal * (taxRate / 100);
    const taxElement = document.querySelector('.cart-summary-tax-amount');
    if (taxElement) {
        taxElement.textContent = taxAmount.toFixed(2);
    }
    
    // حساب الشحن
    // Calculate shipping
    const shippingElement = document.querySelector('.cart-summary-shipping-amount');
    let shippingAmount = 0;
    
    if (shippingElement) {
        const freeShippingMin = parseFloat(shippingElement.getAttribute('data-free-min') || 0);
        const shippingCost = parseFloat(shippingElement.getAttribute('data-cost') || 0);
        
        shippingAmount = subtotal >= freeShippingMin ? 0 : shippingCost;
        shippingElement.textContent = shippingAmount.toFixed(2);
    }
    
    // حساب الإجمالي
    // Calculate total
    const totalElement = document.querySelector('.cart-summary-total-amount');
    if (totalElement) {
        const total = subtotal + taxAmount + shippingAmount;
        totalElement.textContent = total.toFixed(2);
    }
}

// ===== وظائف الأكورديون للفلاتر =====
// ===== Filter Accordion Functions =====
function initFilterAccordions() {
    const accordionToggles = document.querySelectorAll('.filter-accordion-toggle');
    
    accordionToggles.forEach(function(toggle) {
        toggle.addEventListener('click', function() {
            const parent = this.parentElement;
            parent.classList.toggle('open');
            
            const content = this.nextElementSibling;
            if (content.style.maxHeight) {
                content.style.maxHeight = null;
            } else {
                content.style.maxHeight = content.scrollHeight + 'px';
            }
        });
    });
}

// ===== وظائف التلميحات =====
// ===== Tooltip Functions =====
function initTooltips() {
    const tooltips = document.querySelectorAll('[data-tooltip]');
    
    tooltips.forEach(function(tooltip) {
        tooltip.addEventListener('mouseenter', function() {
            const text = this.getAttribute('data-tooltip');
            
            // إنشاء عنصر التلميح
            // Create tooltip element
            const tooltipElement = document.createElement('div');
            tooltipElement.className = 'tooltip';
            tooltipElement.textContent = text;
            document.body.appendChild(tooltipElement);
            
            // تحديد موضع التلميح
            // Position tooltip
            const rect = this.getBoundingClientRect();
            const tooltipRect = tooltipElement.getBoundingClientRect();
            
            tooltipElement.style.top = (rect.top - tooltipRect.height - 10) + 'px';
            tooltipElement.style.left = (rect.left + (rect.width / 2) - (tooltipRect.width / 2)) + 'px';
            
            // إضافة فئة لإظهار التلميح
            // Add class to show tooltip
            setTimeout(function() {
                tooltipElement.classList.add('show');
            }, 10);
            
            // تخزين مرجع للتلميح
            // Store reference to tooltip
            this.tooltipElement = tooltipElement;
        });
        
        tooltip.addEventListener('mouseleave', function() {
            if (this.tooltipElement) {
                this.tooltipElement.classList.remove('show');
                
                // إزالة عنصر التلميح بعد انتهاء الرسوم المتحركة
                // Remove tooltip element after animation
                setTimeout(() => {
                    if (this.tooltipElement && this.tooltipElement.parentNode) {
                        this.tooltipElement.parentNode.removeChild(this.tooltipElement);
                        this.tooltipElement = null;
                    }
                }, 300);
            }
        });
    });
}

// ===== وظائف التحقق من صحة النموذج =====
// ===== Form Validation Functions =====
function initFormValidation() {
    const forms = document.querySelectorAll('form[data-validate]');
    
    forms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            let isValid = true;
            
            // التحقق من الحقول المطلوبة
            // Check required fields
            const requiredFields = form.querySelectorAll('[required]');
            requiredFields.forEach(function(field) {
                if (!field.value.trim()) {
                    isValid = false;
                    showFieldError(field, 'هذا الحقل مطلوب');
                } else {
                    clearFieldError(field);
                }
            });
            
            // التحقق من البريد الإلكتروني
            // Check email fields
            const emailFields = form.querySelectorAll('[type="email"]');
            emailFields.forEach(function(field) {
                if (field.value.trim() && !isValidEmail(field.value)) {
                    isValid = false;
                    showFieldError(field, 'يرجى إدخال بريد إلكتروني صحيح');
                }
            });
            
            // التحقق من تطابق كلمة المرور
            // Check password match
            const password = form.querySelector('[name="password"]');
            const confirmPassword = form.querySelector('[name="confirm_password"]');
            if (password && confirmPassword && password.value !== confirmPassword.value) {
                isValid = false;
                showFieldError(confirmPassword, 'كلمات المرور غير متطابقة');
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
        
        // التحقق من الحقول عند تغييرها
        // Validate fields on change
        const fields = form.querySelectorAll('input, select, textarea');
        fields.forEach(function(field) {
            field.addEventListener('blur', function() {
                if (field.hasAttribute('required') && !field.value.trim()) {
                    showFieldError(field, 'هذا الحقل مطلوب');
                } else if (field.type === 'email' && field.value.trim() && !isValidEmail(field.value)) {
                    showFieldError(field, 'يرجى إدخال بريد إلكتروني صحيح');
                } else {
                    clearFieldError(field);
                }
            });
        });
    });
    
    function showFieldError(field, message) {
        // إزالة رسائل الخطأ السابقة
        // Remove previous error messages
        clearFieldError(field);
        
        // إضافة فئة الخطأ
        // Add error class
        field.classList.add('is-invalid');
        
        // إنشاء رسالة الخطأ
        // Create error message
        const errorElement = document.createElement('div');
        errorElement.className = 'invalid-feedback';
        errorElement.textContent = message;
        
        // إضافة رسالة الخطأ بعد الحقل
        // Add error message after field
        field.parentNode.appendChild(errorElement);
    }
    
    function clearFieldError(field) {
        field.classList.remove('is-invalid');
        
        const errorElement = field.parentNode.querySelector('.invalid-feedback');
        if (errorElement) {
            errorElement.parentNode.removeChild(errorElement);
        }
    }
    
    function isValidEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }
}

// ===== وظائف النماذج بتقنية AJAX =====
// ===== AJAX Form Functions =====
function initAjaxForms() {
    const ajaxForms = document.querySelectorAll('form[data-ajax]');
    
    ajaxForms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // إظهار مؤشر التحميل
            // Show loading indicator
            const submitButton = form.querySelector('[type="submit"]');
            const originalButtonText = submitButton.textContent;
            submitButton.disabled = true;
            submitButton.textContent = 'جاري الإرسال...';
            
            // جمع بيانات النموذج
            // Collect form data
            const formData = new FormData(form);
            
            // إرسال الطلب
            // Send request
            fetch(form.action, {
                method: form.method || 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // إعادة تمكين الزر
                // Re-enable button
                submitButton.disabled = false;
                submitButton.textContent = originalButtonText;
                
                if (data.success) {
                    // عرض رسالة النجاح
                    // Show success message
                    showFormMessage(form, data.message || 'تم إرسال النموذج بنجاح', 'success');
                    
                    // إعادة تعيين النموذج
                    // Reset form
                    form.reset();
                } else {
                    // عرض رسالة الخطأ
                    // Show error message
                    showFormMessage(form, data.message || 'حدث خطأ أثناء إرسال النموذج', 'danger');
                }
            })
            .catch(error => {
                // إعادة تمكين الزر
                // Re-enable button
                submitButton.disabled = false;
                submitButton.textContent = originalButtonText;
                
                // عرض رسالة الخطأ
                // Show error message
                showFormMessage(form, 'حدث خطأ أثناء إرسال النموذج', 'danger');
                console.error('Form submission error:', error);
            });
        });
    });
    
    function showFormMessage(form, message, type) {
        // إزالة الرسائل السابقة
        // Remove previous messages
        const previousMessages = form.querySelectorAll('.alert');
        previousMessages.forEach(function(msg) {
            msg.parentNode.removeChild(msg);
        });
        
        // إنشاء عنصر الرسالة
        // Create message element
        const messageElement = document.createElement('div');
        messageElement.className = `alert alert-${type}`;
        messageElement.textContent = message;
        
        // إضافة الرسالة في بداية النموذج
        // Add message at the beginning of the form
        form.prepend(messageElement);
        
        // تمرير إلى الرسالة
        // Scroll to message
        messageElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
        
        // إزالة الرسالة بعد فترة
        // Remove message after a period
        if (type === 'success') {
            setTimeout(function() {
                messageElement.parentNode.removeChild(messageElement);
            }, 5000);
        }
    }
}

// ===== وظائف التحميل الكسول =====
// ===== Lazy Loading Functions =====
function initLazyLoading() {
    if ('IntersectionObserver' in window) {
        const lazyImages = document.querySelectorAll('img[data-src]');
        
        const imageObserver = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.getAttribute('data-src');
                    img.removeAttribute('data-src');
                    imageObserver.unobserve(img);
                }
            });
        });
        
        lazyImages.forEach(function(image) {
            imageObserver.observe(image);
        });
    } else {
        // Fallback for browsers that don't support IntersectionObserver
        const lazyImages = document.querySelectorAll('img[data-src]');
        
        function lazyLoad() {
            lazyImages.forEach(function(img) {
                if (img.getBoundingClientRect().top <= window.innerHeight && img.getBoundingClientRect().bottom >= 0) {
                    img.src = img.getAttribute('data-src');
                    img.removeAttribute('data-src');
                }
            });
        }
        
        // Load initial images
        lazyLoad();
        
        // Add scroll event
        window.addEventListener('scroll', lazyLoad);
        window.addEventListener('resize', lazyLoad);
    }
}

// ===== وظيفة العودة للأعلى =====
// ===== Back to Top Function =====
function initBackToTop() {
    const backToTopButton = document.querySelector('.back-to-top');
    
    if (backToTopButton) {
        // إظهار/إخفاء الزر عند التمرير
        // Show/hide button on scroll
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                backToTopButton.classList.add('show');
            } else {
                backToTopButton.classList.remove('show');
            }
        });
        
        // التمرير للأعلى عند النقر
        // Scroll to top on click
        backToTopButton.addEventListener('click', function(e) {
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
}

// ===== وظائف مساعدة =====
// ===== Helper Functions =====

// تنسيق العملة
// Format currency
function formatCurrency(amount, currencySymbol = '$') {
    return currencySymbol + parseFloat(amount).toFixed(2);
}

// الحصول على معلمات URL
// Get URL parameters
function getUrlParameter(name) {
    name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
    const regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
    const results = regex.exec(location.search);
    return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
}

// إرسال طلب AJAX
// Send AJAX request
function ajaxRequest(url, method = 'GET', data = null) {
    return new Promise((resolve, reject) => {
        const xhr = new XMLHttpRequest();
        xhr.open(method, url, true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        
        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 300) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    resolve(response);
                } catch (e) {
                    resolve(xhr.responseText);
                }
            } else {
                reject({
                    status: xhr.status,
                    statusText: xhr.statusText
                });
            }
        };
        
        xhr.onerror = function() {
            reject({
                status: xhr.status,
                statusText: xhr.statusText
            });
        };
        
        if (data) {
            xhr.send(JSON.stringify(data));
        } else {
            xhr.send();
        }
    });
}

// تخزين البيانات في التخزين المحلي
// Store data in local storage
function storeLocalData(key, data) {
    localStorage.setItem(key, JSON.stringify(data));
}

// استرجاع البيانات من التخزين المحلي
// Retrieve data from local storage
function getLocalData(key) {
    const data = localStorage.getItem(key);
    return data ? JSON.parse(data) : null;
}

// إنشاء معرف فريد
// Generate unique ID
function generateUniqueId() {
    return 'id_' + Math.random().toString(36).substr(2, 9);
}

// تحويل التاريخ إلى تنسيق مقروء
// Format date to readable format
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString();
}

// ===== وظائف لوحة التحكم =====
// ===== Admin Dashboard Functions =====
function initAdminDashboard() {
    // تبديل القائمة الجانبية
    // Toggle sidebar
    const sidebarToggle = document.querySelector('.admin-sidebar-toggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            const sidebar = document.querySelector('.admin-sidebar');
            sidebar.classList.toggle('show');
        });
    }
    
    // تبديل القوائم الفرعية
    // Toggle submenus
    const menuItems = document.querySelectorAll('.admin-menu-item');
    menuItems.forEach(function(item) {
        const link = item.querySelector('.admin-menu-link');
        const submenu = item.querySelector('.admin-submenu');
        
        if (link && submenu) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                item.classList.toggle('open');
            });
        }
    });
    
    // تبديل القوائم المنسدلة
    // Toggle dropdowns
    const dropdownToggles = document.querySelectorAll('.admin-dropdown-toggle');
    dropdownToggles.forEach(function(toggle) {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const dropdown = this.nextElementSibling;
            dropdown.classList.toggle('show');
        });
    });
    
    // إغلاق القوائم المنسدلة عند النقر خارجها
    // Close dropdowns when clicking outside
    document.addEventListener('click', function() {
        const dropdowns = document.querySelectorAll('.admin-dropdown.show');
        dropdowns.forEach(function(dropdown) {
            dropdown.classList.remove('show');
        });
    });
    
    // تهيئة الرسوم البيانية
    // Initialize charts
    initAdminCharts();
    
    // تهيئة محرر المحتوى
    // Initialize content editor
    initContentEditor();
    
    // تهيئة مدير الملفات
    // Initialize file manager
    initFileManager();
}

// تهيئة الرسوم البيانية
// Initialize charts
function initAdminCharts() {
    // يمكن استخدام مكتبة Chart.js هنا
    // Can use Chart.js library here
    console.log('Admin charts initialized');
}

// تهيئة محرر المحتوى
// Initialize content editor
function initContentEditor() {
    // يمكن استخدام مكتبة TinyMCE أو CKEditor هنا
    // Can use TinyMCE or CKEditor library here
    console.log('Content editor initialized');
}

// تهيئة مدير الملفات
// Initialize file manager
function initFileManager() {
    // تنفيذ مدير الملفات
    // Implement file manager
    console.log('File manager initialized');
}

// ===== تهيئة الصفحة عند التحميل =====
// ===== Initialize page on load =====
window.addEventListener('load', function() {
    // تهيئة لوحة التحكم إذا كانت موجودة
    // Initialize admin dashboard if present
    if (document.querySelector('.admin-layout')) {
        initAdminDashboard();
    }
    
    // إخفاء مؤشر التحميل
    // Hide loading indicator
    const loader = document.querySelector('.page-loader');
    if (loader) {
        loader.classList.add('loaded');
        setTimeout(function() {
            loader.style.display = 'none';
        }, 500);
    }
});

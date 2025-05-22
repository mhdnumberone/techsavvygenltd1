<?php
/**
 * الثوابت المستخدمة في المشروع
 * Constants used in the project
 */

// حالات المستخدم - User statuses
define('USER_STATUS_ACTIVE', 'active');
define('USER_STATUS_INACTIVE', 'inactive');
define('USER_STATUS_BANNED', 'banned');

// أدوار المستخدم - User roles
define('USER_ROLE_ADMIN', 'admin');
define('USER_ROLE_STAFF', 'staff');
define('USER_ROLE_CUSTOMER', 'customer');

// حالات المنتج - Product statuses
define('PRODUCT_STATUS_ACTIVE', 'active');
define('PRODUCT_STATUS_INACTIVE', 'inactive');
define('PRODUCT_STATUS_OUT_OF_STOCK', 'out_of_stock');

// حالات الخدمة - Service statuses
define('SERVICE_STATUS_ACTIVE', 'active');
define('SERVICE_STATUS_INACTIVE', 'inactive');

// حالات الطلب - Order statuses
define('ORDER_STATUS_PENDING', 'pending');
define('ORDER_STATUS_PROCESSING', 'processing');
define('ORDER_STATUS_COMPLETED', 'completed');
define('ORDER_STATUS_CANCELLED', 'cancelled');
define('ORDER_STATUS_REFUNDED', 'refunded');

// حالات الدفع - Payment statuses
define('PAYMENT_STATUS_PENDING', 'pending');
define('PAYMENT_STATUS_COMPLETED', 'completed');
define('PAYMENT_STATUS_FAILED', 'failed');
define('PAYMENT_STATUS_REFUNDED', 'refunded');

// طرق الدفع - Payment methods
define('PAYMENT_METHOD_STRIPE', 'stripe');
define('PAYMENT_METHOD_PAYPAL', 'paypal');
define('PAYMENT_METHOD_BANK_TRANSFER', 'bank_transfer');

// حالات الفاتورة - Invoice statuses
define('INVOICE_STATUS_PAID', 'paid');
define('INVOICE_STATUS_UNPAID', 'unpaid');
define('INVOICE_STATUS_CANCELLED', 'cancelled');

// حالات المراجعة - Review statuses
define('REVIEW_STATUS_PENDING', 'pending');
define('REVIEW_STATUS_APPROVED', 'approved');
define('REVIEW_STATUS_REJECTED', 'rejected');

// حالات تذكرة الدعم - Support ticket statuses
define('TICKET_STATUS_OPEN', 'open');
define('TICKET_STATUS_IN_PROGRESS', 'in_progress');
define('TICKET_STATUS_CLOSED', 'closed');
define('TICKET_STATUS_ANSWERED', 'answered');

// أولويات تذكرة الدعم - Support ticket priorities
define('TICKET_PRIORITY_LOW', 'low');
define('TICKET_PRIORITY_MEDIUM', 'medium');
define('TICKET_PRIORITY_HIGH', 'high');

// أنواع العناصر - Item types
define('ITEM_TYPE_PRODUCT', 'product');
define('ITEM_TYPE_SERVICE', 'service');
define('ITEM_TYPE_CUSTOM_SERVICE', 'custom_service');

// أنواع الخصم - Discount types
define('DISCOUNT_TYPE_PERCENTAGE', 'percentage');
define('DISCOUNT_TYPE_FIXED', 'fixed');

// حالات العرض - Offer statuses
define('OFFER_STATUS_ACTIVE', 'active');
define('OFFER_STATUS_INACTIVE', 'inactive');
define('OFFER_STATUS_EXPIRED', 'expired');

// حالات الخدمة المخصصة - Custom service statuses
define('CUSTOM_SERVICE_STATUS_PENDING', 'pending');
define('CUSTOM_SERVICE_STATUS_PAID', 'paid');
define('CUSTOM_SERVICE_STATUS_EXPIRED', 'expired');
define('CUSTOM_SERVICE_STATUS_CANCELLED', 'cancelled');

// اللغات المدعومة - Supported languages
define('LANGUAGE_ARABIC', 'ar');
define('LANGUAGE_ENGLISH', 'en');

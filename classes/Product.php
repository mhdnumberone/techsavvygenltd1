<?php
/**
 * فئة المنتج
 * Product class
 */

class Product {
    private $db;

    /**
     * إنشاء كائن المنتج
     * Create product object
     */
    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * إضافة منتج جديد
     * Add new product
     */
    public function add($productData) {
        // التحقق من وجود اسم المنتج
        // Check if product name already exists
        if ($this->db->exists('products', 'name_' . getCurrentLanguage(), $productData['name_' . getCurrentLanguage()])) {
            throw new Exception(translate('product_name_already_exists'));
        }

        // إنشاء slug للمنتج
        // Create product slug
        $productData['slug'] = createSlug($productData['name_en']);
        
        // التحقق من وجود slug
        // Check if slug already exists
        if ($this->db->exists('products', 'slug', $productData['slug'])) {
            $productData['slug'] = $productData['slug'] . '-' . time();
        }

        // إضافة تاريخ الإنشاء
        // Add creation date
        $productData['created_at'] = date('Y-m-d H:i:s');

        // إدراج المنتج في قاعدة البيانات
        // Insert product into database
        $productId = $this->db->insert('products', $productData);

        // تسجيل نشاط إضافة المنتج
        // Log product addition activity
        logActivity($_SESSION['user_id'], 'add_product', 'Added new product: ' . $productData['name_en']);

        return $productId;
    }

    /**
     * تحديث منتج
     * Update product
     */
    public function update($productId, $productData) {
        // التحقق من وجود المنتج
        // Check if product exists
        $product = $this->getById($productId);
        
        if (!$product) {
            throw new Exception(translate('product_not_found'));
        }

        // التحقق من وجود اسم المنتج
        // Check if product name already exists
        if (isset($productData['name_' . getCurrentLanguage()]) && 
            $this->db->exists('products', 'name_' . getCurrentLanguage(), $productData['name_' . getCurrentLanguage()], $productId)) {
            throw new Exception(translate('product_name_already_exists'));
        }

        // إنشاء slug جديد إذا تم تغيير الاسم
        // Create new slug if name changed
        if (isset($productData['name_en']) && $productData['name_en'] !== $product['name_en']) {
            $productData['slug'] = createSlug($productData['name_en']);
            
            // التحقق من وجود slug
            // Check if slug already exists
            if ($this->db->exists('products', 'slug', $productData['slug'], $productId)) {
                $productData['slug'] = $productData['slug'] . '-' . time();
            }
        }

        // إضافة تاريخ التحديث
        // Add update date
        $productData['updated_at'] = date('Y-m-d H:i:s');

        // تحديث المنتج في قاعدة البيانات
        // Update product in database
        $result = $this->db->update('products', $productData, 'id = :id', [':id' => $productId]);

        // تسجيل نشاط تحديث المنتج
        // Log product update activity
        logActivity($_SESSION['user_id'], 'update_product', 'Updated product: ' . $product['name_en']);

        return $result;
    }

    /**
     * حذف منتج
     * Delete product
     */
    public function delete($productId) {
        // التحقق من وجود المنتج
        // Check if product exists
        $product = $this->getById($productId);
        
        if (!$product) {
            throw new Exception(translate('product_not_found'));
        }

        // حذف صور المنتج
        // Delete product images
        $this->db->delete('product_images', 'product_id = :product_id', [':product_id' => $productId]);

        // حذف المنتج من قاعدة البيانات
        // Delete product from database
        $result = $this->db->delete('products', 'id = :id', [':id' => $productId]);

        // تسجيل نشاط حذف المنتج
        // Log product deletion activity
        logActivity($_SESSION['user_id'], 'delete_product', 'Deleted product: ' . $product['name_en']);

        return $result;
    }

    /**
     * الحصول على منتج بواسطة المعرف
     * Get product by ID
     */
    public function getById($productId) {
        $product = $this->db->getRow("SELECT * FROM products WHERE id = :id", [':id' => $productId]);
        
        if (!$product) {
            return false;
        }

        // الحصول على صور المنتج
        // Get product images
        $product['images'] = $this->getProductImages($productId);

        // الحصول على تصنيف المنتج
        // Get product category
        if ($product['category_id']) {
            $product['category'] = $this->db->getRow("SELECT * FROM categories WHERE id = :id", [':id' => $product['category_id']]);
        } else {
            $product['category'] = null;
        }

        return $product;
    }

    /**
     * الحصول على منتج بواسطة الـ slug
     * Get product by slug
     */
    public function getBySlug($slug) {
        $product = $this->db->getRow("SELECT * FROM products WHERE slug = :slug", [':slug' => $slug]);
        
        if (!$product) {
            return false;
        }

        // الحصول على صور المنتج
        // Get product images
        $product['images'] = $this->getProductImages($product['id']);

        // الحصول على تصنيف المنتج
        // Get product category
        if ($product['category_id']) {
            $product['category'] = $this->db->getRow("SELECT * FROM categories WHERE id = :id", [':id' => $product['category_id']]);
        } else {
            $product['category'] = null;
        }

        return $product;
    }

    /**
     * الحصول على قائمة المنتجات
     * Get products list
     */
    public function getProducts($page = 1, $perPage = ITEMS_PER_PAGE, $search = '', $categoryId = null, $status = null, $featured = null) {
        $where = '';
        $params = [];

        if (!empty($search)) {
            $where .= "(name_ar LIKE :search OR name_en LIKE :search OR description_ar LIKE :search OR description_en LIKE :search)";
            $params[':search'] = "%{$search}%";
        }

        if ($categoryId !== null) {
            if (!empty($where)) {
                $where .= " AND ";
            }
            $where .= "category_id = :category_id";
            $params[':category_id'] = $categoryId;
        }

        if ($status !== null) {
            if (!empty($where)) {
                $where .= " AND ";
            }
            $where .= "status = :status";
            $params[':status'] = $status;
        }

        if ($featured !== null) {
            if (!empty($where)) {
                $where .= " AND ";
            }
            $where .= "is_featured = :featured";
            $params[':featured'] = $featured ? 1 : 0;
        }

        $result = $this->db->getPaginated('products', $page, $perPage, $where, $params, 'created_at DESC');

        // الحصول على صور المنتجات
        // Get product images
        foreach ($result['data'] as &$product) {
            $product['images'] = $this->getProductImages($product['id']);
            
            // الحصول على تصنيف المنتج
            // Get product category
            if ($product['category_id']) {
                $product['category'] = $this->db->getRow("SELECT * FROM categories WHERE id = :id", [':id' => $product['category_id']]);
            } else {
                $product['category'] = null;
            }
        }

        return $result;
    }

    /**
     * الحصول على المنتجات المميزة
     * Get featured products
     */
    public function getFeaturedProducts($limit = 6) {
        $products = $this->db->getRows("SELECT * FROM products WHERE is_featured = 1 AND status = :status ORDER BY created_at DESC LIMIT :limit", [
            ':status' => PRODUCT_STATUS_ACTIVE,
            ':limit' => $limit
        ]);

        // الحصول على صور المنتجات
        // Get product images
        foreach ($products as &$product) {
            $product['images'] = $this->getProductImages($product['id']);
            
            // الحصول على تصنيف المنتج
            // Get product category
            if ($product['category_id']) {
                $product['category'] = $this->db->getRow("SELECT * FROM categories WHERE id = :id", [':id' => $product['category_id']]);
            } else {
                $product['category'] = null;
            }
        }

        return $products;
    }

    /**
     * الحصول على المنتجات الأحدث
     * Get latest products
     */
    public function getLatestProducts($limit = 8) {
        $products = $this->db->getRows("SELECT * FROM products WHERE status = :status ORDER BY created_at DESC LIMIT :limit", [
            ':status' => PRODUCT_STATUS_ACTIVE,
            ':limit' => $limit
        ]);

        // الحصول على صور المنتجات
        // Get product images
        foreach ($products as &$product) {
            $product['images'] = $this->getProductImages($product['id']);
            
            // الحصول على تصنيف المنتج
            // Get product category
            if ($product['category_id']) {
                $product['category'] = $this->db->getRow("SELECT * FROM categories WHERE id = :id", [':id' => $product['category_id']]);
            } else {
                $product['category'] = null;
            }
        }

        return $products;
    }

    /**
     * الحصول على المنتجات ذات الصلة
     * Get related products
     */
    public function getRelatedProducts($productId, $limit = 4) {
        $product = $this->getById($productId);
        
        if (!$product) {
            return [];
        }

        $where = "id != :product_id AND status = :status";
        $params = [
            ':product_id' => $productId,
            ':status' => PRODUCT_STATUS_ACTIVE
        ];

        if ($product['category_id']) {
            $where .= " AND category_id = :category_id";
            $params[':category_id'] = $product['category_id'];
        }

        $products = $this->db->getRows("SELECT * FROM products WHERE {$where} ORDER BY RAND() LIMIT :limit", array_merge($params, [':limit' => $limit]));

        // الحصول على صور المنتجات
        // Get product images
        foreach ($products as &$product) {
            $product['images'] = $this->getProductImages($product['id']);
            
            // الحصول على تصنيف المنتج
            // Get product category
            if ($product['category_id']) {
                $product['category'] = $this->db->getRow("SELECT * FROM categories WHERE id = :id", [':id' => $product['category_id']]);
            } else {
                $product['category'] = null;
            }
        }

        return $products;
    }

    /**
     * إضافة صورة للمنتج
     * Add product image
     */
    public function addImage($productId, $imagePath, $sortOrder = 0) {
        // التحقق من وجود المنتج
        // Check if product exists
        $product = $this->getById($productId);
        
        if (!$product) {
            throw new Exception(translate('product_not_found'));
        }

        // إدراج الصورة في قاعدة البيانات
        // Insert image into database
        $imageId = $this->db->insert('product_images', [
            'product_id' => $productId,
            'image_path' => $imagePath,
            'sort_order' => $sortOrder,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // تحديث الصورة الرئيسية للمنتج إذا كانت هذه هي الصورة الأولى
        // Update product featured image if this is the first image
        $images = $this->getProductImages($productId);
        if (count($images) === 1) {
            $this->db->update('products', ['featured_image' => $imagePath], 'id = :id', [':id' => $productId]);
        }

        return $imageId;
    }

    /**
     * حذف صورة المنتج
     * Delete product image
     */
    public function deleteImage($imageId) {
        // الحصول على بيانات الصورة
        // Get image data
        $image = $this->db->getRow("SELECT * FROM product_images WHERE id = :id", [':id' => $imageId]);
        
        if (!$image) {
            throw new Exception(translate('image_not_found'));
        }

        // حذف الصورة من قاعدة البيانات
        // Delete image from database
        $result = $this->db->delete('product_images', 'id = :id', [':id' => $imageId]);

        // تحديث الصورة الرئيسية للمنتج إذا كانت هذه هي الصورة الرئيسية
        // Update product featured image if this was the featured image
        $product = $this->getById($image['product_id']);
        if ($product && $product['featured_image'] === $image['image_path']) {
            $images = $this->getProductImages($image['product_id']);
            if (count($images) > 0) {
                $this->db->update('products', ['featured_image' => $images[0]['image_path']], 'id = :id', [':id' => $image['product_id']]);
            } else {
                $this->db->update('products', ['featured_image' => ''], 'id = :id', [':id' => $image['product_id']]);
            }
        }

        // حذف ملف الصورة من الخادم
        // Delete image file from server
        if (file_exists(UPLOAD_DIR . $image['image_path'])) {
            unlink(UPLOAD_DIR . $image['image_path']);
        }

        return $result;
    }

    /**
     * الحصول على صور المنتج
     * Get product images
     */
    public function getProductImages($productId) {
        return $this->db->getRows("SELECT * FROM product_images WHERE product_id = :product_id ORDER BY sort_order ASC", [':product_id' => $productId]);
    }

    /**
     * تحديث ترتيب الصور
     * Update image sort order
     */
    public function updateImageSortOrder($imageId, $sortOrder) {
        return $this->db->update('product_images', ['sort_order' => $sortOrder], 'id = :id', [':id' => $imageId]);
    }

    /**
     * تحديث حالة المنتج
     * Update product status
     */
    public function updateStatus($productId, $status) {
        // التحقق من وجود المنتج
        // Check if product exists
        $product = $this->getById($productId);
        
        if (!$product) {
            throw new Exception(translate('product_not_found'));
        }

        // تحديث حالة المنتج
        // Update product status
        $result = $this->db->update('products', ['status' => $status], 'id = :id', [':id' => $productId]);

        // تسجيل نشاط تحديث حالة المنتج
        // Log product status update activity
        logActivity($_SESSION['user_id'], 'update_product_status', 'Updated product status: ' . $product['name_en'] . ' to ' . $status);

        return $result;
    }

    /**
     * تحديث خاصية المنتج المميز
     * Update product featured status
     */
    public function updateFeatured($productId, $featured) {
        // التحقق من وجود المنتج
        // Check if product exists
        $product = $this->getById($productId);
        
        if (!$product) {
            throw new Exception(translate('product_not_found'));
        }

        // تحديث خاصية المنتج المميز
        // Update product featured status
        $result = $this->db->update('products', ['is_featured' => $featured ? 1 : 0], 'id = :id', [':id' => $productId]);

        // تسجيل نشاط تحديث خاصية المنتج المميز
        // Log product featured status update activity
        $action = $featured ? 'Featured' : 'Unfeatured';
        logActivity($_SESSION['user_id'], 'update_product_featured', $action . ' product: ' . $product['name_en']);

        return $result;
    }

    /**
     * البحث عن المنتجات
     * Search products
     */
    public function searchProducts($query, $page = 1, $perPage = ITEMS_PER_PAGE) {
        $where = "(name_ar LIKE :query OR name_en LIKE :query OR description_ar LIKE :query OR description_en LIKE :query) AND status = :status";
        $params = [
            ':query' => "%{$query}%",
            ':status' => PRODUCT_STATUS_ACTIVE
        ];

        $result = $this->db->getPaginated('products', $page, $perPage, $where, $params, 'created_at DESC');

        // الحصول على صور المنتجات
        // Get product images
        foreach ($result['data'] as &$product) {
            $product['images'] = $this->getProductImages($product['id']);
            
            // الحصول على تصنيف المنتج
            // Get product category
            if ($product['category_id']) {
                $product['category'] = $this->db->getRow("SELECT * FROM categories WHERE id = :id", [':id' => $product['category_id']]);
            } else {
                $product['category'] = null;
            }
        }

        return $result;
    }

    /**
     * تحديث المخزون
     * Update stock
     */
    public function updateStock($productId, $quantity, $operation = 'add') {
        // التحقق من وجود المنتج
        // Check if product exists
        $product = $this->getById($productId);
        
        if (!$product) {
            throw new Exception(translate('product_not_found'));
        }

        // حساب المخزون الجديد
        // Calculate new stock
        $currentStock = $product['stock'];
        $newStock = $operation === 'add' ? $currentStock + $quantity : $currentStock - $quantity;

        if ($newStock < 0) {
            $newStock = 0;
        }

        // تحديث حالة المنتج إذا نفد المخزون
        // Update product status if out of stock
        $status = $product['status'];
        if ($newStock === 0 && $status === PRODUCT_STATUS_ACTIVE) {
            $status = PRODUCT_STATUS_OUT_OF_STOCK;
        } elseif ($newStock > 0 && $status === PRODUCT_STATUS_OUT_OF_STOCK) {
            $status = PRODUCT_STATUS_ACTIVE;
        }

        // تحديث المخزون وحالة المنتج
        // Update stock and product status
        $result = $this->db->update('products', [
            'stock' => $newStock,
            'status' => $status
        ], 'id = :id', [':id' => $productId]);

        // تسجيل نشاط تحديث المخزون
        // Log stock update activity
        $action = $operation === 'add' ? 'Added' : 'Subtracted';
        logActivity($_SESSION['user_id'], 'update_product_stock', $action . ' ' . $quantity . ' items to/from product: ' . $product['name_en'] . '. New stock: ' . $newStock);

        return $result;
    }
}

<?php
/**
 * فئة قاعدة البيانات
 * Database class
 */

class Database {
    private $connection;
    private static $instance;

    /**
     * إنشاء اتصال بقاعدة البيانات
     * Create database connection
     */
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, PDO_OPTIONS);
        } catch (PDOException $e) {
            // في بيئة الإنتاج، يجب تسجيل الخطأ بدلاً من عرضه
            // In a production environment, the error should be logged instead of displayed
            error_log("Database Connection Error: " . $e->getMessage());
            die("An error occurred while connecting to the database. Please try again later.");
        }
    }

    /**
     * الحصول على نسخة من الفئة (Singleton pattern)
     * Get instance of the class (Singleton pattern)
     */
    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * الحصول على اتصال قاعدة البيانات
     * Get database connection
     */
    public function getConnection() {
        return $this->connection;
    }

    /**
     * تنفيذ استعلام
     * Execute query
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Query Error: " . $e->getMessage() . " - SQL: " . $sql);
            throw new Exception("Database query error: " . $e->getMessage());
        }
    }

    /**
     * الحصول على صف واحد
     * Get single row
     */
    public function getRow($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * الحصول على جميع الصفوف
     * Get all rows
     */
    public function getRows($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * الحصول على قيمة واحدة
     * Get single value
     */
    public function getValue($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchColumn();
    }

    /**
     * إدراج بيانات
     * Insert data
     */
    public function insert($table, $data) {
        $fields = array_keys($data);
        $placeholders = array_map(function($field) {
            return ':' . $field;
        }, $fields);
        
        $sql = "INSERT INTO {$table} (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
        
        $this->query($sql, $data);
        return $this->connection->lastInsertId();
    }

    /**
     * تحديث بيانات
     * Update data
     */
    public function update($table, $data, $where, $whereParams = []) {
        $fields = array_keys($data);
        $setClause = array_map(function($field) {
            return $field . ' = :' . $field;
        }, $fields);
        
        $sql = "UPDATE {$table} SET " . implode(', ', $setClause) . " WHERE {$where}";
        
        $params = array_merge($data, $whereParams);
        $this->query($sql, $params);
        return $this->connection->rowCount();
    }

    /**
     * حذف بيانات
     * Delete data
     */
    public function delete($table, $where, $params = []) {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        $this->query($sql, $params);
        return $this->connection->rowCount();
    }

    /**
     * بدء المعاملة
     * Begin transaction
     */
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }

    /**
     * تأكيد المعاملة
     * Commit transaction
     */
    public function commit() {
        return $this->connection->commit();
    }

    /**
     * التراجع عن المعاملة
     * Rollback transaction
     */
    public function rollback() {
        return $this->connection->rollBack();
    }

    /**
     * الحصول على عدد الصفوف المتأثرة
     * Get affected rows count
     */
    public function rowCount() {
        return $this->connection->rowCount();
    }

    /**
     * التحقق من وجود قيمة
     * Check if value exists
     */
    public function exists($table, $field, $value, $excludeId = null) {
        $sql = "SELECT COUNT(*) FROM {$table} WHERE {$field} = :value";
        $params = [':value' => $value];
        
        if ($excludeId !== null) {
            $sql .= " AND id != :id";
            $params[':id'] = $excludeId;
        }
        
        return (int) $this->getValue($sql, $params) > 0;
    }

    /**
     * الحصول على عدد الصفوف
     * Get row count
     */
    public function count($table, $where = '', $params = []) {
        $sql = "SELECT COUNT(*) FROM {$table}";
        
        if (!empty($where)) {
            $sql .= " WHERE {$where}";
        }
        
        return (int) $this->getValue($sql, $params);
    }

    /**
     * الحصول على صفوف مع ترقيم الصفحات
     * Get rows with pagination
     */
    public function getPaginated($table, $page = 1, $perPage = ITEMS_PER_PAGE, $where = '', $params = [], $orderBy = 'id DESC') {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT * FROM {$table}";
        
        if (!empty($where)) {
            $sql .= " WHERE {$where}";
        }
        
        if (!empty($orderBy)) {
            $sql .= " ORDER BY {$orderBy}";
        }
        
        $sql .= " LIMIT {$perPage} OFFSET {$offset}";
        
        $rows = $this->getRows($sql, $params);
        
        // الحصول على إجمالي عدد الصفوف
        // Get total number of rows
        $totalRows = $this->count($table, $where, $params);
        $totalPages = ceil($totalRows / $perPage);
        
        return [
            'data' => $rows,
            'pagination' => [
                'total' => $totalRows,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => $totalPages
            ]
        ];
    }

    /**
     * تنظيف الاتصال
     * Clean up connection
     */
    public function __destruct() {
        $this->connection = null;
    }
}

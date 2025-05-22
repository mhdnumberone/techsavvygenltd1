<?php
/**
 * ملف تهيئة قاعدة البيانات
 * Database initialization file
 */

// تضمين ملفات الإعدادات
// Include configuration files
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../config/constants.php';

// تهيئة الاتصال بقاعدة البيانات
// Initialize database connection
$db = Database::getInstance();

// قراءة ملف SQL
// Read SQL file
$sqlFile = file_get_contents('techsavvygenltd.sql');

// تقسيم الملف إلى أوامر SQL منفصلة
// Split file into separate SQL commands
$sqlCommands = explode(';', $sqlFile);

// تنفيذ كل أمر SQL
// Execute each SQL command
$success = true;
$errors = [];

foreach ($sqlCommands as $command) {
    $command = trim($command);
    
    if (empty($command)) {
        continue;
    }
    
    try {
        $result = $db->query($command);
        
        if (!$result) {
            $success = false;
            $errors[] = "Error executing SQL command: " . $command;
        }
    } catch (Exception $e) {
        $success = false;
        $errors[] = "Exception: " . $e->getMessage() . " in command: " . $command;
    }
}

// عرض النتيجة
// Display result
if ($success) {
    echo "Database initialized successfully!";
} else {
    echo "Error initializing database:<br>";
    echo implode("<br>", $errors);
}
?>

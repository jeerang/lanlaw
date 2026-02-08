<?php
/**
 * Database Configuration
 * ระบบงานเอกสารสำนักงานทนายความ
 */

// Load local configuration (contains sensitive credentials)
$localConfigPath = __DIR__ . '/config.local.php';
if (file_exists($localConfigPath)) {
    require_once $localConfigPath;
} else {
    // Fallback defaults (for development only - create config.local.php for production)
    if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
    if (!defined('DB_NAME')) define('DB_NAME', 'lanlaw_db');
    if (!defined('DB_USER')) define('DB_USER', 'root');
    if (!defined('DB_PASS')) define('DB_PASS', '');
    if (!defined('DB_CHARSET')) define('DB_CHARSET', 'utf8mb4');
    if (!defined('ENVIRONMENT')) define('ENVIRONMENT', 'development');
}

/**
 * Database Connection Class
 */
class Database {
    private static $instance = null;
    private $conn;
    
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
            ];
            
            $this->conn = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }
    
    /**
     * Get database instance (Singleton)
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Get PDO connection
     */
    public function getConnection() {
        return $this->conn;
    }
    
    /**
     * Prevent cloning
     */
    private function __clone() {}
    
    /**
     * Prevent unserialization
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

/**
 * Get database connection (helper function)
 */
function getDB() {
    return Database::getInstance()->getConnection();
}

/**
 * Test database connection
 */
function testConnection() {
    try {
        $db = getDB();
        $stmt = $db->query("SELECT 1");
        return [
            'status' => true,
            'message' => 'เชื่อมต่อฐานข้อมูลสำเร็จ'
        ];
    } catch (Exception $e) {
        return [
            'status' => false,
            'message' => 'ไม่สามารถเชื่อมต่อฐานข้อมูลได้: ' . $e->getMessage()
        ];
    }
}
?>

<?php
/**
 * Database Connection & Query Helper
 */

class Database {
    private static $instance = null;
    private $conn;
    
    private $host;
    private $user;
    private $pass;
    private $dbname;
    private $port;
    
    private function __construct() {
        $this->host = DB_HOST;
        $this->user = DB_USER;
        $this->pass = DB_PASS;
        $this->dbname = DB_NAME;
        $this->port = DB_PORT;
        
        $this->connect();
    }
    
    private function connect() {
        $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->dbname, $this->port);
        
        if ($this->conn->connect_error) {
            // Try to connect without database first (for setup)
            $this->conn = new mysqli($this->host, $this->user, $this->pass, null, $this->port);
            if ($this->conn->connect_error) {
                die("Database connection failed: " . $this->conn->connect_error);
            }
        }
        
        $this->conn->set_charset("utf8mb4");
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->conn;
    }
    
    /**
     * Run a query with optional parameters (prepared statement)
     */
    public function query($sql, $params = []) {
        if (empty($params)) {
            $result = $this->conn->query($sql);
            if ($result === false) {
                throw new Exception("Query error: " . $this->conn->error . " [SQL: $sql]");
            }
            return $result;
        }
        
        $stmt = $this->conn->prepare($sql);
        if ($stmt === false) {
            throw new Exception("Prepare error: " . $this->conn->error . " [SQL: $sql]");
        }
        
        if (!empty($params)) {
            $types = '';
            $values = [];
            foreach ($params as $param) {
                if (is_int($param)) {
                    $types .= 'i';
                } elseif (is_float($param)) {
                    $types .= 'd';
                } elseif (is_null($param)) {
                    $types .= 's'; // null treated as string
                } else {
                    $types .= 's';
                }
                $values[] = $param;
            }
            $stmt->bind_param($types, ...$values);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        
        return $result;
    }
    
    /**
     * Get a single row as associative array
     */
    public function getRow($sql, $params = []) {
        $result = $this->query($sql, $params);
        if ($result instanceof mysqli_result) {
            return $result->fetch_assoc();
        }
        return null;
    }
    
    /**
     * Get all rows as array of associative arrays
     */
    public function getRows($sql, $params = []) {
        $result = $this->query($sql, $params);
        if ($result instanceof mysqli_result) {
            return $result->fetch_all(MYSQLI_ASSOC);
        }
        return [];
    }
    
    /**
     * Get a single value (first column of first row)
     */
    public function getValue($sql, $params = []) {
        $row = $this->getRow($sql, $params);
        if ($row) {
            return reset($row);
        }
        return null;
    }
    
    /**
     * Insert a row and return the insert ID
     */
    public function insert($sql, $params = []) {
        $this->query($sql, $params);
        return $this->conn->insert_id;
    }
    
    /**
     * Update/Delete and return affected rows
     */
    public function execute($sql, $params = []) {
        if (empty($params)) {
            $this->conn->query($sql);
        } else {
            $stmt = $this->conn->prepare($sql);
            if ($stmt === false) {
                throw new Exception("Prepare error: " . $this->conn->error);
            }
            
            $types = '';
            $values = [];
            foreach ($params as $param) {
                if (is_int($param)) $types .= 'i';
                elseif (is_float($param)) $types .= 'd';
                else $types .= 's';
                $values[] = $param;
            }
            $stmt->bind_param($types, ...$values);
            $stmt->execute();
            $stmt->close();
        }
        
        return $this->conn->affected_rows;
    }
    
    /**
     * Check if database exists
     */
    public function databaseExists() {
        $result = $this->conn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$this->dbname'");
        return $result && $result->num_rows > 0;
    }
    
    /**
     * Create database if not exists
     */
    public function createDatabase() {
        $this->conn->query("CREATE DATABASE IF NOT EXISTS `$this->dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $this->conn->select_db($this->dbname);
    }
    
    /**
     * Escape string for safe query
     */
    public function escape($string) {
        return $this->conn->real_escape_string($string);
    }
    
    /**
     * Close connection
     */
    public function close() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}

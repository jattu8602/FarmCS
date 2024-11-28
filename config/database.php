<?php
class Database {
    private $host = 'localhost';
    private $db_name = 'farmcs';
    private $username = 'root';
    private $password = '';
    private $conn;

    public function connect() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                'mysql:host=' . $this->host . ';charset=utf8',
                $this->username,
                $this->password,
                array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
            );
            
            // Create database if not exists
            $this->conn->exec("CREATE DATABASE IF NOT EXISTS $this->db_name");
            $this->conn->exec("USE $this->db_name");
            
            // Create users table with all necessary fields
            $this->conn->exec("CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(100) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                first_name VARCHAR(50),
                last_name VARCHAR(50),
                state VARCHAR(50),
                district VARCHAR(50),
                farm_type VARCHAR(20),
                farm_size DECIMAL(10,2),
                profile_image VARCHAR(255),
                role VARCHAR(20) DEFAULT 'user',
                is_active TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                last_login TIMESTAMP NULL,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            // Create uploads directory if it doesn't exist
            $uploadsDir = dirname(__DIR__) . '/uploads/profile_images';
            if (!file_exists($uploadsDir)) {
                mkdir($uploadsDir, 0777, true);
            }

            return $this->conn;
        } catch(PDOException $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            throw new Exception("Database connection failed");
        }
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

    public function query($sql, $params = []) {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Query Error: " . $e->getMessage() . " SQL: " . $sql);
            throw new Exception("Query Error: " . $e->getMessage());
        }
    }

    public function fetch($sql, $params = []) {
        try {
            $stmt = $this->query($sql, $params);
            $result = $stmt->fetch();
            if ($result === false) {
                error_log("No results found for query: " . $sql . " with params: " . print_r($params, true));
            }
            return $result;
        } catch (Exception $e) {
            error_log("Fetch Error: " . $e->getMessage());
            throw new Exception("Fetch Error: " . $e->getMessage());
        }
    }

    public function fetchAll($sql, $params = []) {
        try {
            $stmt = $this->query($sql, $params);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("FetchAll Error: " . $e->getMessage());
            throw new Exception("FetchAll Error: " . $e->getMessage());
        }
    }

    public function update($table, $data, $where, $whereParams = []) {
        try {
            $fields = array_map(function($field) {
                return "$field = ?";
            }, array_keys($data));
            
            $sql = "UPDATE $table SET " . implode(', ', $fields) . " WHERE $where";
            
            return $this->query($sql, array_merge(array_values($data), $whereParams));
        } catch (Exception $e) {
            error_log("Update Error: " . $e->getMessage());
            throw new Exception("Update Error: " . $e->getMessage());
        }
    }

    public function lastInsertId() {
        return $this->conn->lastInsertId();
    }
}
?>

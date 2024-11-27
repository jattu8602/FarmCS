<?php
class Database {
    private $host = 'localhost';
    private $db_name = 'farmcs';
    private $username = 'root';
    private $password = '';
    private $conn = null;

    public function connect() {
        try {
            // First connect without database to create it if needed
            $tempConn = new PDO("mysql:host=$this->host", $this->username, $this->password);
            $tempConn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Create database if it doesn't exist
            $tempConn->exec("CREATE DATABASE IF NOT EXISTS `$this->db_name`");
            $tempConn = null;

            // Now connect to the specific database
            $this->conn = new PDO(
                "mysql:host=$this->host;dbname=$this->db_name;charset=utf8mb4",
                $this->username,
                $this->password,
                array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                )
            );

            // Create tables
            $this->createTables();
            
            // Create test user if no users exist
            $this->createTestUser();

            return $this->conn;
        } catch(PDOException $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }

    private function createTables() {
        try {
            // Create users table
            $this->conn->exec("CREATE TABLE IF NOT EXISTS `users` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `email` VARCHAR(255) NOT NULL UNIQUE,
                `password_hash` VARCHAR(255) NOT NULL,
                `first_name` VARCHAR(100),
                `last_name` VARCHAR(100),
                `state` VARCHAR(100),
                `district` VARCHAR(100),
                `farm_type` VARCHAR(100),
                `farm_size` DECIMAL(10,2),
                `role` VARCHAR(20) DEFAULT 'user',
                `session_token` VARCHAR(64),
                `is_active` TINYINT(1) DEFAULT 1,
                `last_login` DATETIME NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_email (email),
                INDEX idx_session_token (session_token)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        } catch(PDOException $e) {
            error_log("Table Creation Error: " . $e->getMessage());
            throw new Exception("Failed to create database tables: " . $e->getMessage());
        }
    }

    private function createTestUser() {
        try {
            // Check if any users exist
            $stmt = $this->conn->query("SELECT COUNT(*) as count FROM users");
            $result = $stmt->fetch();

            if ($result['count'] == 0) {
                // Create test user with hashed password
                $hashedPassword = password_hash('password123', PASSWORD_DEFAULT);
                $stmt = $this->conn->prepare("
                    INSERT INTO users (
                        email, 
                        password_hash, 
                        first_name, 
                        last_name, 
                        state, 
                        district, 
                        farm_type, 
                        farm_size,
                        role
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");

                $stmt->execute([
                    'test@example.com',
                    $hashedPassword,
                    'Test',
                    'User',
                    'Test State',
                    'Test District',
                    'Crop',
                    10,
                    'admin'
                ]);

                error_log("Test user created successfully");
            }
        } catch(PDOException $e) {
            error_log("Test User Creation Error: " . $e->getMessage());
            // Don't throw exception for test user creation
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

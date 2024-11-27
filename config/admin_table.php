<?php
require_once 'db_config.php';

// Create admin table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS admin_credentials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    // Check if admin account exists
    $checkAdmin = "SELECT * FROM admin_credentials LIMIT 1";
    $result = $conn->query($checkAdmin);
    
    if ($result->num_rows == 0) {
        // Insert default admin account
        $defaultUsername = "admin";
        $defaultPassword = password_hash("admin123", PASSWORD_DEFAULT);
        
        $insertDefault = "INSERT INTO admin_credentials (username, password) VALUES (?, ?)";
        $stmt = $conn->prepare($insertDefault);
        $stmt->bind_param("ss", $defaultUsername, $defaultPassword);
        
        if ($stmt->execute()) {
            echo "Default admin account created successfully!";
        } else {
            echo "Error creating default admin account: " . $stmt->error;
        }
        $stmt->close();
    }
} else {
    echo "Error creating admin table: " . $conn->error;
}
?>

<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/db_config.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Database connection successful!<br>";

// Check if farmcs database exists
$result = $conn->query("SHOW DATABASES LIKE 'farmcs'");
if ($result->num_rows > 0) {
    echo "Database 'farmcs' exists!<br>";
    
    // Select the database
    $conn->select_db('farmcs');
    
    // Check if users table exists
    $result = $conn->query("SHOW TABLES LIKE 'users'");
    if ($result->num_rows > 0) {
        echo "Table 'users' exists!<br>";
        
        // Count users
        $result = $conn->query("SELECT COUNT(*) as count FROM users");
        $count = $result->fetch_assoc()['count'];
        echo "Number of users in database: " . $count;
    } else {
        echo "Table 'users' does not exist!<br>";
        echo "Creating tables...<br>";
        
        // Read and execute schema.sql
        $schema = file_get_contents('database/schema.sql');
        if ($schema === false) {
            die("Could not read schema.sql");
        }
        
        if ($conn->multi_query($schema)) {
            do {
                // Store first result set
                if ($result = $conn->store_result()) {
                    $result->free();
                }
                // Check if there are more results
            } while ($conn->more_results() && $conn->next_result());
            
            echo "Database schema created successfully!";
        } else {
            echo "Error creating schema: " . $conn->error;
        }
    }
} else {
    echo "Database 'farmcs' does not exist!";
}

$conn->close();
?>

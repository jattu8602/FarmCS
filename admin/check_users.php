<?php
require_once '../config/db_config.php';

$query = "SELECT * FROM users";
$result = $conn->query($query);

if ($result) {
    echo "Total users found: " . $result->num_rows . "\n";
    while ($row = $result->fetch_assoc()) {
        echo "ID: " . $row['id'] . ", Email: " . $row['email'] . "\n";
    }
} else {
    echo "Error executing query: " . $conn->error;
}
?>

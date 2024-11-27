<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

require_once '../config/db_config.php';

// Query to get all users
$query = "SELECT first_name, last_name, email, state, district, farm_type, farm_size, role, created_at FROM users ORDER BY created_at DESC";
$result = $conn->query($query);

if ($result) {
    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="farmcs_users_' . date('Y-m-d') . '.csv"');
    
    // Create output stream
    $output = fopen('php://output', 'w');
    
    // Add UTF-8 BOM for Excel to properly display special characters
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Add headers
    fputcsv($output, [
        'First Name',
        'Last Name',
        'Email',
        'State',
        'District',
        'Farm Type',
        'Farm Size (acres)',
        'Role',
        'Registration Date'
    ]);
    
    // Add data rows
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['first_name'],
            $row['last_name'],
            $row['email'],
            $row['state'],
            $row['district'],
            $row['farm_type'],
            $row['farm_size'],
            $row['role'],
            date('Y-m-d H:i:s', strtotime($row['created_at']))
        ]);
    }
    
    fclose($output);
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error fetching users']);
}

$conn->close();
?>

<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    die("Unauthorized access");
}

require_once '../config/db_config.php';

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="farmcs_users_' . date('Y-m-d') . '.csv"');

// Create output stream
$output = fopen('php://output', 'w');

// Add UTF-8 BOM for proper Excel display
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Add CSV headers
fputcsv($output, array(
    'ID',
    'First Name',
    'Last Name',
    'Email',
    'State',
    'District',
    'Farm Type',
    'Farm Size (acres)',
    'Registration Date'
));

// Get all users
$query = "SELECT id, firstName, lastName, email, state, district, farmType, farmSize, created_at 
          FROM users ORDER BY created_at DESC";
$result = $conn->query($query);

// Add data to CSV
while ($row = $result->fetch_assoc()) {
    fputcsv($output, array(
        $row['id'],
        $row['firstName'],
        $row['lastName'],
        $row['email'],
        $row['state'],
        $row['district'],
        $row['farmType'],
        $row['farmSize'],
        date('Y-m-d H:i:s', strtotime($row['created_at']))
    ));
}

// Close the output stream
fclose($output);
exit();
?>

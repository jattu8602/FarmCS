<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Enable error logging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '../logs/php_errors.log');

require_once('../config/Database.php');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Initialize response array
$response = array(
    'success' => false,
    'message' => '',
    'data' => null
);

try {
    // Check request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Get JSON input
    $json = file_get_contents('php://input');
    error_log("Login attempt - Raw input: " . $json);
    
    $data = json_decode($json, true);

    // Validate JSON data
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON format: ' . json_last_error_msg());
    }

    // Validate required fields
    if (!isset($data['email']) || !isset($data['password'])) {
        throw new Exception('Email and password are required');
    }

    $email = filter_var(trim($data['email']), FILTER_SANITIZE_EMAIL);
    $password = $data['password'];

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }

    // Initialize database connection
    $database = new Database();
    $db = $database->connect();

    // Prepare the select statement
    $stmt = $db->prepare('
        SELECT 
            id, 
            email, 
            password_hash, 
            first_name, 
            last_name,
            state,
            district,
            farm_type,
            farm_size,
            created_at,
            role 
        FROM users 
        WHERE email = ? AND is_active = 1
    ');

    if (!$stmt) {
        error_log("Failed to prepare login statement for email: " . $email);
        throw new Exception('Database error: Failed to prepare statement');
    }

    // Execute the statement
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Log login attempt
    error_log("Login attempt for email: " . $email . " - User found: " . ($user ? 'Yes' : 'No'));

    // Verify user exists and password is correct
    if (!$user || !password_verify($password, $user['password_hash'])) {
        error_log("Login failed for email: " . $email . " - " . ($user ? 'Invalid password' : 'User not found'));
        throw new Exception('Invalid email or password');
    }

    // Remove sensitive data
    unset($user['password_hash']);

    // Update last login time
    $updateStmt = $db->prepare('UPDATE users SET last_login = NOW() WHERE id = ?');
    $updateStmt->execute([$user['id']]);

    // Prepare user data for response
    $userData = [
        'email' => $user['email'],
        'firstName' => $user['first_name'],
        'lastName' => $user['last_name'],
        'state' => $user['state'],
        'district' => $user['district'],
        'farmType' => $user['farm_type'],
        'farmSize' => $user['farm_size'],
        'joinDate' => $user['created_at'],
        'role' => $user['role']
    ];

    // Set success response
    $response['success'] = true;
    $response['message'] = 'Login successful';
    $response['data'] = $userData;

    error_log("Login successful for email: " . $email);

} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>

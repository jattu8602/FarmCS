<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

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
    $data = json_decode($json, true);

    // Validate JSON data
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON format');
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
            role 
        FROM users 
        WHERE email = ? AND is_active = 1
    ');

    if (!$stmt) {
        throw new Exception('Database error: Failed to prepare statement');
    }

    // Execute the statement
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // For debugging
    error_log("Login attempt for email: " . $email);
    if ($user) {
        error_log("User found, verifying password");
    } else {
        error_log("No user found with email: " . $email);
    }

    // Verify user exists and password is correct
    if (!$user || !password_verify($password, $user['password_hash'])) {
        // Use a generic error message to prevent user enumeration
        throw new Exception('Invalid email or password');
    }

    // Remove sensitive data before sending response
    unset($user['password_hash']);

    // Generate session token
    $sessionToken = bin2hex(random_bytes(32));
    
    // Update user's session token in database
    $updateStmt = $db->prepare('UPDATE users SET session_token = ?, last_login = NOW() WHERE id = ?');
    $updateStmt->execute([$sessionToken, $user['id']]);

    // Add session token to user data
    $user['session_token'] = $sessionToken;

    // Set successful response
    $response['success'] = true;
    $response['message'] = 'Login successful';
    $response['data'] = $user;

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
    
    // Log error (but don't expose it to client)
    error_log("Login error: " . $e->getMessage());
    
    // If it's a PDO error, log it but return a generic message
    if ($e instanceof PDOException) {
        $response['message'] = 'A database error occurred. Please try again later.';
    }
} finally {
    // Clear database connection
    $db = null;
}

// Send response
echo json_encode($response);

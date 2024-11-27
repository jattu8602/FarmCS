<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once('../config/Database.php');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

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
    // Get POST data
    $json = file_get_contents('php://input');
    error_log("Received data: " . $json); // Log received data
    
    $data = json_decode($json, true);

    // Validate JSON data
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON format: ' . json_last_error_msg());
    }

    // Validate required fields
    if (empty($data['firstName']) || empty($data['lastName']) || empty($data['email']) || 
        empty($data['password']) || empty($data['state']) || empty($data['district']) || 
        empty($data['farmType']) || empty($data['farmSize'])) {
        throw new Exception('All fields are required');
    }

    // Validate email format
    $email = filter_var(trim($data['email']), FILTER_SANITIZE_EMAIL);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }

    // Initialize database connection
    $database = new Database();
    $db = $database->connect();

    // Check if email already exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        throw new Exception('Email already exists');
    }

    // Hash password
    $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

    // Prepare SQL statement
    $sql = "
        INSERT INTO users (
            first_name, 
            last_name, 
            email, 
            password_hash, 
            state, 
            district, 
            farm_type, 
            farm_size,
            role,
            is_active,
            created_at,
            updated_at
        ) VALUES (
            :firstName,
            :lastName,
            :email,
            :password,
            :state,
            :district,
            :farmType,
            :farmSize,
            'user',
            1,
            NOW(),
            NOW()
        )
    ";

    // Insert new user with named parameters
    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':firstName' => $data['firstName'],
        ':lastName' => $data['lastName'],
        ':email' => $email,
        ':password' => $hashedPassword,
        ':state' => $data['state'],
        ':district' => $data['district'],
        ':farmType' => $data['farmType'],
        ':farmSize' => $data['farmSize']
    ]);

    // Get the new user's ID
    $userId = $db->lastInsertId();

    // Return user data (excluding password)
    $response['success'] = true;
    $response['message'] = 'Account created successfully';
    $response['data'] = [
        'id' => $userId,
        'first_name' => $data['firstName'],
        'last_name' => $data['lastName'],
        'email' => $email,
        'state' => $data['state'],
        'district' => $data['district'],
        'farm_type' => $data['farmType'],
        'farm_size' => $data['farmSize'],
        'role' => 'user'
    ];

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
    
    // Log error (but don't expose it to client)
    error_log("Signup error: " . $e->getMessage());
    
    // If it's a PDO error, log it with details
    if ($e instanceof PDOException) {
        error_log("Database error details: " . $e->getMessage());
        $response['message'] = 'A database error occurred. Please try again later.';
    }
    
    http_response_code(400);
} finally {
    // Clear database connection
    $db = null;
}

// Send response
echo json_encode($response);

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
    // Get POST data
    $json = file_get_contents('php://input');
    error_log("Signup attempt - Raw input: " . $json);
    
    $data = json_decode($json, true);

    // Validate JSON data
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON format: ' . json_last_error_msg());
    }

    // Validate required fields
    $requiredFields = ['firstName', 'lastName', 'email', 'password', 'state', 'district', 'farmType', 'farmSize'];
    $missingFields = [];
    foreach ($requiredFields as $field) {
        if (empty($data[$field])) {
            $missingFields[] = $field;
        }
    }
    
    if (!empty($missingFields)) {
        error_log("Missing required fields: " . implode(', ', $missingFields));
        throw new Exception('Required fields missing: ' . implode(', ', $missingFields));
    }

    // Validate email format
    $email = filter_var(trim($data['email']), FILTER_SANITIZE_EMAIL);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        error_log("Invalid email format: " . $email);
        throw new Exception('Invalid email format');
    }

    // Validate farm size is numeric and positive
    if (!is_numeric($data['farmSize']) || $data['farmSize'] <= 0) {
        error_log("Invalid farm size: " . $data['farmSize']);
        throw new Exception('Farm size must be a positive number');
    }

    // Initialize database connection
    $database = new Database();
    $db = $database->connect();

    // Check if email already exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        error_log("Email already exists: " . $email);
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
            created_at
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
            NOW()
        )
    ";

    // Execute insert statement
    $stmt = $db->prepare($sql);
    $success = $stmt->execute([
        ':firstName' => $data['firstName'],
        ':lastName' => $data['lastName'],
        ':email' => $email,
        ':password' => $hashedPassword,
        ':state' => $data['state'],
        ':district' => $data['district'],
        ':farmType' => $data['farmType'],
        ':farmSize' => $data['farmSize']
    ]);

    if ($success) {
        // Prepare user data for response
        $userData = [
            'email' => $email,
            'firstName' => $data['firstName'],
            'lastName' => $data['lastName'],
            'state' => $data['state'],
            'district' => $data['district'],
            'farmType' => $data['farmType'],
            'farmSize' => $data['farmSize']
        ];

        $response['success'] = true;
        $response['message'] = 'Account created successfully';
        $response['data'] = $userData;

        error_log("User created successfully: " . $email);
    } else {
        throw new Exception('Failed to create account');
    }

} catch (Exception $e) {
    error_log("Signup error: " . $e->getMessage());
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>

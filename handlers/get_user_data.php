<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-User-Email');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

require_once('../config/Database.php');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Get email from request header
    $headers = getallheaders();
    $email = isset($headers['X-User-Email']) ? $headers['X-User-Email'] : null;

    if (!$email) {
        throw new Exception('User email not provided');
    }

    // Initialize database connection
    $database = new Database();
    $db = $database->connect();

    // Prepare and execute query
    $stmt = $db->prepare('
        SELECT 
            email,
            first_name,
            last_name,
            state,
            district,
            farm_type,
            farm_size,
            profile_image,
            created_at,
            last_login
        FROM users 
        WHERE email = ? 
        AND is_active = 1
    ');

    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception('User not found');
    }

    // Format the response data
    $response = [
        'success' => true,
        'userData' => [
            'email' => $user['email'],
            'firstName' => $user['first_name'],
            'lastName' => $user['last_name'],
            'state' => $user['state'],
            'district' => $user['district'],
            'farmType' => $user['farm_type'],
            'farmSize' => $user['farm_size'],
            'profileImage' => $user['profile_image'],
            'joinDate' => $user['created_at'],
            'lastLogin' => $user['last_login']
        ]
    ];

    // Send response with gzip compression if supported
    if (extension_loaded('zlib') && !ini_get('zlib.output_compression')) {
        ob_start('ob_gzhandler');
    }
    echo json_encode($response);
    exit();

} catch (Exception $e) {
    http_response_code(400);
    error_log("Error in get_user_data.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

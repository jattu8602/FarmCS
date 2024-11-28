<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-User-Email');

require_once('../config/Database.php');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    $headers = getallheaders();
    $email = isset($headers['X-User-Email']) ? $headers['X-User-Email'] : null;

    if (!$email) {
        throw new Exception('User email not provided');
    }

    $database = new Database();
    $db = $database->connect();

    // Validate user exists
    $stmt = $db->prepare('SELECT id FROM users WHERE email = ? AND is_active = 1');
    $stmt->execute([$email]);
    if (!$stmt->fetch()) {
        throw new Exception('User not found');
    }

    // Handle file upload
    $profileImage = null;
    if (isset($_FILES['profileImage']) && $_FILES['profileImage']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['profileImage'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        
        if (!in_array($file['type'], $allowedTypes)) {
            throw new Exception('Invalid file type. Only JPG, PNG and GIF allowed.');
        }
        
        if ($file['size'] > 5000000) { // 5MB limit
            throw new Exception('File too large. Maximum size is 5MB.');
        }
        
        $uploadDir = '../uploads/profile_images/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = uniqid() . '_' . time() . '.' . $extension;
        $targetPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            // Delete old profile image if exists
            $stmt = $db->prepare('SELECT profile_image FROM users WHERE email = ?');
            $stmt->execute([$email]);
            $oldImage = $stmt->fetchColumn();
            
            if ($oldImage && file_exists($uploadDir . $oldImage)) {
                unlink($uploadDir . $oldImage);
            }
            
            $profileImage = $fileName;
        } else {
            throw new Exception('Failed to upload image.');
        }
    }

    // Validate and sanitize input data
    $firstName = isset($_POST['firstName']) ? trim($_POST['firstName']) : null;
    $lastName = isset($_POST['lastName']) ? trim($_POST['lastName']) : null;
    $state = isset($_POST['state']) ? trim($_POST['state']) : null;
    $district = isset($_POST['district']) ? trim($_POST['district']) : null;
    $farmType = isset($_POST['farmType']) ? trim($_POST['farmType']) : null;
    $farmSize = isset($_POST['farmSize']) ? floatval($_POST['farmSize']) : null;

    // Validation
    if ($firstName && strlen($firstName) > 50) {
        throw new Exception('First name too long (max 50 characters)');
    }
    if ($lastName && strlen($lastName) > 50) {
        throw new Exception('Last name too long (max 50 characters)');
    }
    if ($farmSize && ($farmSize <= 0 || $farmSize > 10000)) {
        throw new Exception('Invalid farm size (must be between 0 and 10000)');
    }
    
    // Build update query dynamically
    $updateFields = [];
    $params = [];
    
    if ($firstName !== null) {
        $updateFields[] = 'first_name = ?';
        $params[] = $firstName;
    }
    if ($lastName !== null) {
        $updateFields[] = 'last_name = ?';
        $params[] = $lastName;
    }
    if ($state !== null) {
        $updateFields[] = 'state = ?';
        $params[] = $state;
    }
    if ($district !== null) {
        $updateFields[] = 'district = ?';
        $params[] = $district;
    }
    if ($farmType !== null) {
        $updateFields[] = 'farm_type = ?';
        $params[] = $farmType;
    }
    if ($farmSize !== null) {
        $updateFields[] = 'farm_size = ?';
        $params[] = $farmSize;
    }
    if ($profileImage !== null) {
        $updateFields[] = 'profile_image = ?';
        $params[] = $profileImage;
    }
    
    if (empty($updateFields)) {
        throw new Exception('No fields to update');
    }
    
    // Add email to params for WHERE clause
    $params[] = $email;
    
    $sql = 'UPDATE users SET ' . implode(', ', $updateFields) . ', updated_at = CURRENT_TIMESTAMP WHERE email = ?';
    $stmt = $db->prepare($sql);
    
    if ($stmt->execute($params)) {
        // Fetch updated user data
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
        ');
        
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'message' => 'Profile updated successfully',
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
        ]);
    } else {
        throw new Exception('Failed to update profile');
    }
    
} catch (Exception $e) {
    error_log("Error in update_profile.php: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

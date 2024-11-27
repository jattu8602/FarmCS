<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}

require_once '../config/db_config.php';

// Get total number of users
$userCountQuery = "SELECT COUNT(*) as total FROM users";
$result = $conn->query($userCountQuery);
$totalUsers = $result->fetch_assoc()['total'];

// Get all users
$recentUsersQuery = "SELECT id, email, first_name, last_name, state, district, farm_type, farm_size, role, created_at FROM users ORDER BY created_at DESC";
$recentUsers = $conn->query($recentUsersQuery);

// Debug information
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!$recentUsers) {
    die("Error in query: " . $conn->error);
}

// Get current admin username
$adminUsername = $_SESSION['admin_username'] ?? 'Admin';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - FarmCS</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #2c3e50;
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
        }
        .admin-info {
            color: #ecf0f1;
            margin-right: 20px;
        }
        .nav-links {
            display: flex;
            gap: 20px;
            align-items: center;
        }
        .nav-links a {
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        .nav-links a:hover {
            background-color: #34495e;
        }
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        .stat-card {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .stat-card h2 {
            color: #2c3e50;
            margin-top: 0;
            margin-bottom: 10px;
        }
        .stat-value {
            font-size: 2em;
            color: #27ae60;
            margin: 10px 0;
        }
        .recent-users {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .recent-users h2 {
            color: #2c3e50;
            margin-top: 0;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f8f9fa;
            color: #2c3e50;
            font-weight: 600;
        }
        tr:hover {
            background-color: #f8f9fa;
        }
        .logout-btn {
            background-color: #e74c3c;
        }
        .settings-btn {
            background-color: #3498db;
        }
        .export-btn {
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 4px;
            transition: background-color 0.3s;
            display: inline-block;
            margin-left: 10px;
        }
        .export-excel {
            background-color: #27ae60;
        }
        .export-excel:hover {
            background-color: #219a52;
        }
        .export-csv {
            background-color: #3498db;
        }
        .export-csv:hover {
            background-color: #2980b9;
        }
        .empty-message {
            text-align: center;
            padding: 20px;
            color: #7f8c8d;
            font-style: italic;
        }
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.85em;
            font-weight: 500;
        }
        .badge-admin {
            background-color: #e74c3c;
            color: white;
        }
        .badge-user {
            background-color: #3498db;
            color: white;
        }
        .delete-btn {
            background-color: #e74c3c;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9em;
            transition: background-color 0.3s;
            display: inline-block;
        }
        .delete-btn:hover {
            background-color: #c0392b;
        }
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            border-radius: 4px;
            color: white;
            opacity: 0;
            transition: opacity 0.3s;
            z-index: 1000;
        }
        .toast.success {
            background-color: #27ae60;
        }
        .toast.error {
            background-color: #e74c3c;
        }
        .toast.show {
            opacity: 1;
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }
        .modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        .modal-buttons {
            margin-top: 20px;
            display: flex;
            justify-content: center;
            gap: 10px;
        }
        .modal-btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
        }
        .confirm-delete {
            background-color: #e74c3c;
            color: white;
        }
        .cancel-delete {
            background-color: #95a5a6;
            color: white;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>FarmCS Admin Dashboard</h1>
        <div class="nav-links">
            <span class="admin-info">Welcome, <?php echo htmlspecialchars($adminUsername); ?></span>
            <a href="change_credentials.php" class="settings-btn">Change Credentials</a>
            <a href="export_excel.php" class="export-btn export-excel">Export Excel</a>
            <a href="export_csv.php" class="export-btn export-csv">Export CSV</a>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <div class="container">
        <div class="stats-container">
            <div class="stat-card">
                <h2>Total Users</h2>
                <div class="stat-value"><?php echo $totalUsers; ?></div>
                <p>Registered users in the system</p>
            </div>
        </div>

        <div class="recent-users">
            <h2>Recent Registrations</h2>
            <?php if ($recentUsers && $recentUsers->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>State</th>
                            <th>District</th>
                            <th>Farm Type</th>
                            <th>Farm Size</th>
                            <th>Role</th>
                            <th>Registered On</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        while($user = $recentUsers->fetch_assoc()): 
                            // Debug information
                            $userId = $user['id'] ?? 'No ID';
                            $userEmail = $user['email'] ?? 'No Email';
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($userEmail); ?></td>
                                <td><?php echo htmlspecialchars($user['state']); ?></td>
                                <td><?php echo htmlspecialchars($user['district']); ?></td>
                                <td><?php echo htmlspecialchars($user['farm_type']); ?></td>
                                <td><?php echo htmlspecialchars($user['farm_size']); ?> acres</td>
                                <td>
                                    <span class="badge <?php echo $user['role'] === 'admin' ? 'badge-admin' : 'badge-user'; ?>">
                                        <?php echo htmlspecialchars($user['role']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <?php if ($userId !== 'No ID'): ?>
                                        <button class="delete-btn" onclick="confirmDelete(<?php echo $userId; ?>, '<?php echo htmlspecialchars($userEmail); ?>')">
                                            Delete
                                        </button>
                                    <?php else: ?>
                                        <span style="color: red;">Invalid User ID</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-message">No users registered yet.</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Toast notification -->
    <div id="toast" class="toast"></div>

    <!-- Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <h3>Confirm Deletion</h3>
            <p>Are you sure you want to delete the user <span id="deleteUserEmail"></span>?</p>
            <div class="modal-buttons">
                <button class="modal-btn cancel-delete" onclick="closeModal()">Cancel</button>
                <button class="modal-btn confirm-delete" onclick="deleteUser()">Delete</button>
            </div>
        </div>
    </div>

    <script>
        let userToDelete = null;
        let userEmailToDelete = null;

        function showToast(message, type) {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.className = `toast ${type}`;
            toast.classList.add('show');
            
            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }

        function confirmDelete(userId, userEmail) {
            userToDelete = userId;
            userEmailToDelete = userEmail;
            document.getElementById('deleteUserEmail').textContent = userEmail;
            document.getElementById('deleteModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('deleteModal').style.display = 'none';
            userToDelete = null;
            userEmailToDelete = null;
        }

        function deleteUser() {
            if (!userToDelete) return;

            const formData = new FormData();
            formData.append('user_id', userToDelete);

            fetch('delete_user.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('User deleted successfully', 'success');
                    // Find and remove the row
                    const rows = document.querySelectorAll('tr');
                    for (let row of rows) {
                        if (row.querySelector(`button[onclick*="${userToDelete}"]`)) {
                            row.remove();
                            break;
                        }
                    }
                    // Update total users count
                    const statValue = document.querySelector('.stat-value');
                    const currentCount = parseInt(statValue.textContent);
                    statValue.textContent = currentCount - 1;
                } else {
                    showToast(data.message || 'Error deleting user', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error deleting user', 'error');
            })
            .finally(() => {
                closeModal();
            });
        }
    </script>
</body>
</html>

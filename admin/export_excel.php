<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

require_once '../config/db_config.php';

// Check if vendor directory exists and autoload.php is accessible
try {
    require_once 'C:/Users/abhis/Desktop/FarmCS Website/vendor/autoload.php';
} catch (Exception $e) {
    error_log("Error loading PhpSpreadsheet: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error loading export functionality']);
    exit;
}

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

// Query to get all users
$query = "SELECT first_name, last_name, email, state, district, farm_type, farm_size, role, created_at FROM users ORDER BY created_at DESC";
$result = $conn->query($query);

if ($result) {
    try {
        // Create new Spreadsheet object
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set headers
        $headers = [
            'First Name',
            'Last Name',
            'Email',
            'State',
            'District',
            'Farm Type',
            'Farm Size (acres)',
            'Role',
            'Registration Date'
        ];
        
        foreach ($headers as $index => $header) {
            $column = chr(65 + $index); // Convert number to letter (A, B, C, etc.)
            $sheet->setCellValue($column . '1', $header);
            
            // Style the header
            $sheet->getStyle($column . '1')->getFont()->setBold(true);
            $sheet->getStyle($column . '1')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFE0E0E0');
        }
        
        // Add data rows
        $row = 2;
        while ($data = $result->fetch_assoc()) {
            $sheet->setCellValue('A' . $row, $data['first_name']);
            $sheet->setCellValue('B' . $row, $data['last_name']);
            $sheet->setCellValue('C' . $row, $data['email']);
            $sheet->setCellValue('D' . $row, $data['state']);
            $sheet->setCellValue('E' . $row, $data['district']);
            $sheet->setCellValue('F' . $row, $data['farm_type']);
            $sheet->setCellValue('G' . $row, $data['farm_size']);
            $sheet->setCellValue('H' . $row, $data['role']);
            $sheet->setCellValue('I' . $row, date('Y-m-d H:i:s', strtotime($data['created_at'])));
            $row++;
        }
        
        // Auto-size columns
        foreach (range('A', 'I') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
        
        // Create writer and output file
        $writer = new Xlsx($spreadsheet);
        
        // Set headers for download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="farmcs_users_' . date('Y-m-d') . '.xlsx"');
        header('Cache-Control: max-age=0');
        
        // Output file
        $writer->save('php://output');
    } catch (Exception $e) {
        error_log("Error generating export: " . $e->getMessage());
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Error generating export']);
        exit;
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error fetching users']);
}

$conn->close();
?>

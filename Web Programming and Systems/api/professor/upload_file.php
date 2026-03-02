<?php

require_once '../../includes/init.php';

if (!User::isLoggedIn() || User::getCurrentUserRole() !== 'professor') {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Δεν έχετε πρόσβαση σε αυτό το API endpoint'
    ]);
    exit;
}

error_log("Upload request received. FILES data: " . print_r($_FILES, true));

if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    error_log("File upload error: " . (isset($_FILES['file']) ? $_FILES['file']['error'] : "No file sent"));
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Δεν έχει σταλεί αρχείο ή υπήρξε σφάλμα κατά το ανέβασμα'
    ]);
    exit;
}

$allowedTypes = [
    'application/pdf', 
    'application/msword', 
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
];
$fileType = $_FILES['file']['type'];

error_log("File type: " . $fileType);

if (!in_array($fileType, $allowedTypes)) {
    error_log("Invalid file type: " . $fileType);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Ο τύπος του αρχείου δεν είναι αποδεκτός. Επιτρέπονται μόνο αρχεία PDF, DOC και DOCX.'
    ]);
    exit;
}

$maxSize = 10 * 1024 * 1024;

if ($_FILES['file']['size'] > $maxSize) {
    error_log("File too large: " . $_FILES['file']['size'] . " bytes");
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Το μέγεθος του αρχείου είναι πολύ μεγάλο. Μέγιστο επιτρεπόμενο μέγεθος: 10MB.'
    ]);
    exit;
}

$uploadDir = '../../uploads/thesis_descriptions/';
$absoluteUploadDir = __DIR__ . '/../../uploads/thesis_descriptions/';

if (!file_exists($absoluteUploadDir)) {
    if (!mkdir($absoluteUploadDir, 0777, true)) {
        error_log("Failed to create directory: " . $absoluteUploadDir);
    } else {
        chmod($absoluteUploadDir, 0777);
    }
}

$fileExtension = '';
switch ($fileType) {
    case 'application/pdf':
        $fileExtension = '.pdf';
        break;
    case 'application/msword':
        $fileExtension = '.doc';
        break;
    case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
        $fileExtension = '.docx';
        break;
    default:
        $fileExtension = '.pdf';
}

$fileName = uniqid('thesis_') . '_' . time() . $fileExtension;
$filePath = $absoluteUploadDir . $fileName;

if (move_uploaded_file($_FILES['file']['tmp_name'], $filePath)) {
    error_log("File uploaded successfully to: " . $filePath);
    
    $relativePath = 'uploads/thesis_descriptions/' . $fileName;
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Το αρχείο ανέβηκε με επιτυχία',
        'data' => [
            'file_name' => $fileName,
            'file_path' => $relativePath
        ]
    ]);
    error_log("Response sent: " . json_encode([
        'success' => true,
        'file_path' => $relativePath
    ]));
    exit;
} else {
    $errorMessage = 'Σφάλμα κατά το ανέβασμα του αρχείου';
    error_log("File upload failed: " . $errorMessage . " - Path: " . $filePath);
    
    if (!is_writable($absoluteUploadDir)) {
        $errorMessage .= ' (Ο φάκελος δεν είναι εγγράψιμος)';
        error_log("Directory not writable: " . $absoluteUploadDir);
    }
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => $errorMessage
    ]);
    exit;
}

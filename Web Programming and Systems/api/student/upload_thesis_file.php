<?php

require_once '../../includes/init.php';

header('Content-Type: application/json');

if (!User::isLoggedIn() || User::getCurrentUserRole() !== 'student') {
    echo json_encode([
        'success' => false,
        'message' => 'Δεν έχετε δικαίωμα πρόσβασης σε αυτό το API.'
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Μη έγκυρη μέθοδος αιτήματος.'
    ]);
    exit;
}

if (!isset($_FILES['file']) || !isset($_POST['thesis_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Λείπουν απαραίτητα δεδομένα.'
    ]);
    exit;
}

$thesis_id = (int)$_POST['thesis_id'];
$description = isset($_POST['description']) ? trim($_POST['description']) : '';
$file_type = isset($_POST['file_type']) ? trim($_POST['file_type']) : 'final';

$valid_file_types = ['draft', 'final', 'presentation', 'code', 'data'];
if (!in_array($file_type, $valid_file_types)) {
    $file_type = 'draft';
}

$db = new Database();
$pdo = $db->getConnection();

$user_id = User::getCurrentUserId();

$stmt = $pdo->prepare("
    SELECT ta.id
    FROM thesis_assignments ta 
    JOIN students s ON ta.student_id = s.id
    WHERE ta.id = :thesis_id AND s.user_id = :user_id AND ta.status = 'examination'
");
$stmt->bindParam(':thesis_id', $thesis_id, PDO::PARAM_INT);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$thesis = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$thesis) {
    echo json_encode([
        'success' => false,
        'message' => 'Δεν βρέθηκε έγκυρη διπλωματική εργασία ή δεν έχετε δικαίωμα πρόσβασης.'
    ]);
    exit;
}

$file = $_FILES['file'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    $errorMessage = 'Σφάλμα κατά το ανέβασμα του αρχείου.';
    switch ($file['error']) {
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            $errorMessage = 'Το αρχείο είναι πολύ μεγάλο.';
            break;
        case UPLOAD_ERR_PARTIAL:
            $errorMessage = 'Το αρχείο ανέβηκε μόνο εν μέρει.';
            break;
        case UPLOAD_ERR_NO_FILE:
            $errorMessage = 'Δεν επιλέχθηκε αρχείο.';
            break;
    }
    
    echo json_encode([
        'success' => false,
        'message' => $errorMessage
    ]);
    exit;
}

$maxFileSize = 20 * 1024 * 1024;
if ($file['size'] > $maxFileSize) {
    echo json_encode([
        'success' => false,
        'message' => 'Το αρχείο είναι πολύ μεγάλο. Μέγιστο επιτρεπτό μέγεθος: 20MB.'
    ]);
    exit;
}

$allowedMimeTypes = [
    'draft' => [
        'application/pdf', 
        'application/msword', 
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/octet-stream'
    ],
    'final' => [
        'application/pdf', 
        'application/msword', 
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/octet-stream'
    ],
    'presentation' => [
        'application/pdf', 
        'application/vnd.ms-powerpoint', 
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'application/octet-stream'
    ],
    'code' => [
        'application/zip', 
        'application/x-rar-compressed', 
        'application/x-7z-compressed', 
        'application/x-tar', 
        'application/gzip',
        'application/octet-stream'
    ],
    'data' => [
        'text/csv', 
        'application/vnd.ms-excel', 
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 
        'application/zip', 
        'application/x-rar-compressed', 
        'application/x-7z-compressed',
        'application/octet-stream'
    ]
];

$fileName = $file['name'];
$fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

error_log("Upload attempt: file_type=$file_type, file_extension=$fileExt, file_name=$fileName");

$allowedExtensions = [
    'draft' => ['pdf', 'doc', 'docx'],
    'final' => ['pdf', 'doc', 'docx'],
    'presentation' => ['pdf', 'ppt', 'pptx'],
    'code' => ['zip', 'rar', '7z', 'tar', 'gz'],
    'data' => ['csv', 'xls', 'xlsx', 'zip', 'rar', '7z']
];

$isAllowed = false;
foreach ($allowedExtensions[$file_type] as $ext) {
    if (strcasecmp($fileExt, $ext) === 0) {
        $isAllowed = true;
        break;
    }
}

if (!$isAllowed) {
    error_log("File rejected: file_type=$file_type, file_extension=$fileExt, file_name=$fileName");
    echo json_encode([
        'success' => false,
        'message' => 'Μη επιτρεπτός τύπος αρχείου για την επιλεγμένη κατηγορία.'
    ]);
    exit;
}

$uploadDir = '../../uploads/thesis_files/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$fileName = $file['name'];
$fileExt = pathinfo($fileName, PATHINFO_EXTENSION);
$uniqueFileName = uniqid('thesis_') . '_' . time() . '.' . $fileExt;
$filePath = $uniqueFileName;
$fullPath = $uploadDir . $uniqueFileName;

if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
    echo json_encode([
        'success' => false,
        'message' => 'Σφάλμα κατά την αποθήκευση του αρχείου.'
    ]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO thesis_files (thesis_assignment_id, file_name, file_path, file_type, description)
        VALUES (:thesis_id, :file_name, :file_path, :file_type, :description)
    ");
    $stmt->bindParam(':thesis_id', $thesis_id, PDO::PARAM_INT);
    $stmt->bindParam(':file_name', $fileName, PDO::PARAM_STR);
    $stmt->bindParam(':file_path', $filePath, PDO::PARAM_STR);
    $stmt->bindParam(':file_type', $file_type, PDO::PARAM_STR);
    $stmt->bindParam(':description', $description, PDO::PARAM_STR);
    $stmt->execute();
    
    if (tableExists($pdo, 'thesis_timeline')) {
        $action = "Ανέβασμα πρόχειρου κειμένου: " . $fileName;
        $stmt = $pdo->prepare("
            INSERT INTO thesis_timeline (thesis_assignment_id, action, created_by, user_role)
            VALUES (:thesis_id, :action, :user_id, 'student')
        ");
        $stmt->bindParam(':thesis_id', $thesis_id, PDO::PARAM_INT);
        $stmt->bindParam(':action', $action, PDO::PARAM_STR);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Το αρχείο ανέβηκε με επιτυχία.',
        'file_name' => $fileName,
        'file_path' => $filePath
    ]);
    
} catch (PDOException $e) {
    if (file_exists($fullPath)) {
        unlink($fullPath);
    }
    
    echo json_encode([
        'success' => false,
        'message' => 'Σφάλμα κατά την αποθήκευση των πληροφοριών του αρχείου: ' . $e->getMessage()
    ]);
}

function tableExists($pdo, $table) {
    try {
        $result = $pdo->query("SHOW TABLES LIKE '{$table}'");
        return $result->rowCount() > 0;
    } catch (Exception $e) {
        return false;
    }
}

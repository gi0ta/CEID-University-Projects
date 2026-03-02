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

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['file_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Λείπει το ID του αρχείου.'
    ]);
    exit;
}

$file_id = (int)$data['file_id'];

$db = new Database();
$pdo = $db->getConnection();

$user_id = User::getCurrentUserId();

$stmt = $pdo->prepare("
    SELECT tf.*, ta.id as thesis_id, ta.status
    FROM thesis_files tf
    JOIN thesis_assignments ta ON tf.thesis_assignment_id = ta.id
    JOIN students s ON ta.student_id = s.id
    WHERE tf.id = :file_id AND s.user_id = :user_id
");
$stmt->bindParam(':file_id', $file_id, PDO::PARAM_INT);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$file = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$file) {
    echo json_encode([
        'success' => false,
        'message' => 'Το αρχείο δεν βρέθηκε ή δεν έχετε δικαίωμα πρόσβασης.'
    ]);
    exit;
}

if ($file['status'] !== 'examination') {
    echo json_encode([
        'success' => false,
        'message' => 'Μπορείτε να διαγράψετε αρχεία μόνο όταν η διπλωματική είναι σε κατάσταση "Υπό Εξέταση".'
    ]);
    exit;
}

$uploadDir = '../../uploads/thesis_files/';
$filePath = $uploadDir . $file['file_path'];

if (file_exists($filePath)) {
    if (!unlink($filePath)) {
        echo json_encode([
            'success' => false,
            'message' => 'Σφάλμα κατά τη διαγραφή του αρχείου από το σύστημα αρχείων.'
        ]);
        exit;
    }
}

try {
    $stmt = $pdo->prepare("DELETE FROM thesis_files WHERE id = :file_id");
    $stmt->bindParam(':file_id', $file_id, PDO::PARAM_INT);
    $stmt->execute();
    
    if (tableExists($pdo, 'thesis_timeline')) {
        $action = "Διαγραφή αρχείου: " . $file['file_name'];
        $stmt = $pdo->prepare("
            INSERT INTO thesis_timeline (thesis_assignment_id, action, created_by, user_role)
            VALUES (:thesis_id, :action, :user_id, 'student')
        ");
        $stmt->bindParam(':thesis_id', $file['thesis_id'], PDO::PARAM_INT);
        $stmt->bindParam(':action', $action, PDO::PARAM_STR);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Το αρχείο διαγράφηκε με επιτυχία.'
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Σφάλμα κατά τη διαγραφή του αρχείου από τη βάση δεδομένων: ' . $e->getMessage()
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

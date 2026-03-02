<?php

file_put_contents('/tmp/thesis_grading_debug.log', date('Y-m-d H:i:s') . " - API called\n", FILE_APPEND);

require_once '../../includes/init.php';
require_once '../../includes/config/database.php';
require_once '../../includes/classes/User.php';

if (!User::isLoggedIn() || User::getCurrentUserRole() !== 'professor') {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Δεν έχετε δικαίωμα πρόσβασης'
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Μη έγκυρη μέθοδος αιτήματος'
    ]);
    exit;
}

$assignment_id = isset($_POST['assignment_id']) ? intval($_POST['assignment_id']) : 0;

file_put_contents('/tmp/thesis_grading_debug.log', date('Y-m-d H:i:s') . " - Assignment ID: {$assignment_id}\n", FILE_APPEND);
file_put_contents('/tmp/thesis_grading_debug.log', date('Y-m-d H:i:s') . " - POST data: " . print_r($_POST, true) . "\n", FILE_APPEND);

if ($assignment_id <= 0) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Μη έγκυρο ID ανάθεσης διπλωματικής'
    ]);
    exit;
}

try {
    $pdo = getDbConnection();
    
    $user_id = User::getCurrentUserId();
    $stmt = $pdo->prepare("
        SELECT p.id FROM professors p 
        WHERE p.user_id = :user_id
    ");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $professor = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$professor) {
        throw new Exception('Δεν βρέθηκαν στοιχεία καθηγητή');
    }
    
    $professor_id = $professor['id'];
    
    $stmt = $pdo->prepare("
        SELECT ta.*, tf.file_type 
        FROM thesis_assignments ta
        LEFT JOIN thesis_files tf ON ta.id = tf.thesis_assignment_id AND tf.file_type = 'final'
        WHERE ta.id = :assignment_id AND ta.supervisor_id = :professor_id AND ta.status = 'examination'
    ");
    $stmt->bindParam(':assignment_id', $assignment_id);
    $stmt->bindParam(':professor_id', $professor_id);
    $stmt->execute();
    $assignment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$assignment) {
        throw new Exception('Δεν βρέθηκε η ανάθεση διπλωματικής ή δεν είστε ο επιβλέπων καθηγητής');
    }
    
    if (!$assignment['file_type']) {
        throw new Exception('Δεν έχει αναρτηθεί το τελικό αρχείο της διπλωματικής');
    }
    
    $stmt = $pdo->prepare("
        UPDATE thesis_assignments 
        SET grading_enabled = 1 
        WHERE id = :assignment_id
    ");
    $stmt->bindParam(':assignment_id', $assignment_id);
    $stmt->execute();
    
    echo json_encode([
        'success' => true,
        'message' => 'Η βαθμολόγηση ενεργοποιήθηκε με επιτυχία'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

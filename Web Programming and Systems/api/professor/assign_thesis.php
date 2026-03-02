<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../includes/init.php';

if (!User::isLoggedIn() || User::getCurrentUserRoleType() !== 'professor') {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Δεν έχετε δικαίωμα πρόσβασης σε αυτό το API.'
    ]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Μη έγκυρη μέθοδος αιτήματος.'
    ]);
    exit();
}

if (!isset($_POST['topic_id']) || !isset($_POST['student_id'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Λείπουν απαραίτητα πεδία.'
    ]);
    exit();
}

$topicId = intval($_POST['topic_id']);
$studentId = intval($_POST['student_id']);

$db = new Database();
$pdo = $db->getConnection();

$userId = User::getCurrentUserId();
$stmt = $pdo->prepare("SELECT id FROM professors WHERE user_id = :user_id");
$stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
$stmt->execute();
$professor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$professor) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Δεν βρέθηκε ο καθηγητής.'
    ]);
    exit();
}

$professorId = $professor['id'];

$stmt = $pdo->prepare("
    SELECT id, is_assigned 
    FROM thesis_topics 
    WHERE id = :topic_id AND professor_id = :professor_id
");
$stmt->bindParam(':topic_id', $topicId, PDO::PARAM_INT);
$stmt->bindParam(':professor_id', $professorId, PDO::PARAM_INT);
$stmt->execute();
$topic = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$topic) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Το θέμα δεν βρέθηκε ή δεν σας ανήκει.'
    ]);
    exit();
}

if ($topic['is_assigned'] == 1) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Το θέμα έχει ήδη ανατεθεί σε φοιτητή.'
    ]);
    exit();
}

$stmt = $pdo->prepare("SELECT id FROM students WHERE id = :student_id");
$stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
$stmt->execute();
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Ο φοιτητής δεν βρέθηκε.'
    ]);
    exit();
}

$stmt = $pdo->prepare("
    SELECT id 
    FROM thesis_assignments 
    WHERE student_id = :student_id AND status != 'cancelled'
");
$stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
$stmt->execute();
$existingAssignment = $stmt->fetch(PDO::FETCH_ASSOC);

if ($existingAssignment) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Ο φοιτητής έχει ήδη ανατεθεί σε άλλο θέμα διπλωματικής.'
    ]);
    exit();
}

try {
    $pdo->beginTransaction();
    
    $stmt = $pdo->prepare("
        INSERT INTO thesis_assignments (
            thesis_topic_id, 
            student_id, 
            supervisor_id, 
            assignment_date, 
            status
        ) VALUES (
            :thesis_topic_id, 
            :student_id, 
            :supervisor_id, 
            CURRENT_DATE(), 
            'pending'
        )
    ");
    $stmt->bindParam(':thesis_topic_id', $topicId, PDO::PARAM_INT);
    $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
    $stmt->bindParam(':supervisor_id', $professorId, PDO::PARAM_INT);
    $stmt->execute();
    
    $assignmentId = $pdo->lastInsertId();
    
    $stmt = $pdo->prepare("
        UPDATE thesis_topics 
        SET is_assigned = 1 
        WHERE id = :topic_id
    ");
    $stmt->bindParam(':topic_id', $topicId, PDO::PARAM_INT);
    $stmt->execute();
    
    $pdo->commit();
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO status_changes (
                thesis_assignment_id,
                old_status,
                new_status,
                changed_at,
                changed_by,
                notes
            ) VALUES (
                :thesis_assignment_id,
                NULL, -- Πρώτη καταχώρηση, δεν υπάρχει προηγούμενη κατάσταση
                'pending',
                CURRENT_TIMESTAMP(),
                :changed_by,
                'Αρχική ανάθεση θέματος σε φοιτητή'
            )
        ");
        $stmt->bindParam(':thesis_assignment_id', $assignmentId, PDO::PARAM_INT);
        $stmt->bindParam(':changed_by', $professorId, PDO::PARAM_INT);
        $stmt->execute();
    } catch (Exception $e) {
        error_log('Σφάλμα κατά την καταγραφή της αλλαγής status: ' . $e->getMessage());
    }
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Το θέμα ανατέθηκε με επιτυχία στον φοιτητή.',
        'data' => [
            'assignment_id' => $assignmentId
        ]
    ]);
    exit();
    
} catch (Exception $e) {
    $pdo->rollBack();
    
    error_log('Σφάλμα κατά την ανάθεση θέματος: ' . $e->getMessage());
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Σφάλμα κατά την ανάθεση θέματος: ' . $e->getMessage(),
        'debug' => [
            'error_message' => $e->getMessage(),
            'error_code' => $e->getCode(),
            'error_file' => $e->getFile(),
            'error_line' => $e->getLine(),
            'topic_id' => $topicId,
            'student_id' => $studentId,
            'professor_id' => $professorId
        ]
    ]);
    exit();
}

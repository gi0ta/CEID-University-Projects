<?php

require_once '../../includes/init.php';

if (!User::isLoggedIn() || User::getCurrentUserRole() !== 'student') {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Μη εξουσιοδοτημένη πρόσβαση'
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Μη έγκυρη μέθοδος αιτήματος'
    ]);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['invitation_id']) || empty($data['invitation_id'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Δεν παρέχθηκε το ID της πρόσκλησης'
    ]);
    exit;
}

$invitationId = $data['invitation_id'];

$userId = User::getCurrentUserId();
$db = new Database();
$pdo = $db->getConnection();

$stmt = $pdo->prepare("SELECT id FROM students WHERE user_id = :user_id");
$stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
$stmt->execute();
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Δεν βρέθηκε ο φοιτητής'
    ]);
    exit;
}

$studentId = $student['id'];

$stmt = $pdo->prepare("
    SELECT 
        ci.id,
        ci.status,
        ci.thesis_assignment_id,
        ta.student_id
    FROM 
        committee_invitations ci
    JOIN 
        thesis_assignments ta ON ci.thesis_assignment_id = ta.id
    WHERE 
        ci.id = :invitation_id AND
        ta.student_id = :student_id
");
$stmt->bindParam(':invitation_id', $invitationId, PDO::PARAM_INT);
$stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
$stmt->execute();
$invitation = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$invitation) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Δεν βρέθηκε η πρόσκληση ή δεν έχετε δικαίωμα να την ακυρώσετε'
    ]);
    exit;
}

if ($invitation['status'] !== 'pending') {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Η πρόσκληση δεν μπορεί να ακυρωθεί γιατί δεν είναι σε κατάσταση αναμονής'
    ]);
    exit;
}

try {
    $pdo->beginTransaction();
    
    $stmt = $pdo->prepare("DELETE FROM committee_invitations WHERE id = :invitation_id");
    $stmt->bindParam(':invitation_id', $invitationId, PDO::PARAM_INT);
    $stmt->execute();
    
    $pdo->commit();
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Η πρόσκληση ακυρώθηκε με επιτυχία'
    ]);
    
} catch (Exception $e) {
    $pdo->rollBack();
    
    error_log('Error cancelling invitation: ' . $e->getMessage());
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Σφάλμα κατά την ακύρωση της πρόσκλησης'
    ]);
}

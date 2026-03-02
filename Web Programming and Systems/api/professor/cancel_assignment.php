<?php
require_once '../../includes/init.php';

if (!User::isLoggedIn() || User::getCurrentUserRole() !== 'professor') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Δεν έχετε δικαίωμα πρόσβασης σε αυτή τη σελίδα.']);
    exit;
}

$db = new Database();
$pdo = $db->getConnection();

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['thesis_id']) || !isset($data['reason'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Λείπουν απαραίτητα δεδομένα.']);
    exit;
}

$thesisId = $data['thesis_id'];
$reason = $data['reason'];
$professorId = User::getCurrentUserId();

try {
    $stmt = $pdo->prepare("SELECT supervisor_id, status, assignment_date FROM thesis_assignments WHERE id = :thesis_id");
    $stmt->bindParam(':thesis_id', $thesisId, PDO::PARAM_INT);
    $stmt->execute();
    $thesis = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$thesis) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Η διπλωματική εργασία δεν βρέθηκε.']);
        exit;
    }
    
    if ($thesis['supervisor_id'] != $professorId) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Δεν είστε ο επιβλέπων καθηγητής αυτής της διπλωματικής.']);
        exit;
    }
    
    $status = $thesis['status'];
    
    if ($status !== 'pending') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Δεν μπορείτε να ακυρώσετε μια διπλωματική που δεν είναι υπό ανάθεση.']);
        exit;
    }
    
    $pdo->beginTransaction();
    
    $stmt = $pdo->prepare("UPDATE thesis_assignments SET status = 'cancelled', cancellation_date = NOW(), cancellation_reason = :reason WHERE id = :thesis_id");
    $stmt->bindParam(':thesis_id', $thesisId, PDO::PARAM_INT);
    $stmt->bindParam(':reason', $reason, PDO::PARAM_STR);
    $stmt->execute();
    
    $stmt = $pdo->prepare("INSERT INTO status_changes (thesis_assignment_id, changed_by, new_status, notes) VALUES (:thesis_id, :professor_id, 'cancelled', :reason)");
    $stmt->bindParam(':thesis_id', $thesisId, PDO::PARAM_INT);
    $stmt->bindParam(':professor_id', $professorId, PDO::PARAM_INT);
    $stmt->bindParam(':reason', $reason, PDO::PARAM_STR);
    $stmt->execute();
    
    $stmt = $pdo->prepare("DELETE FROM committee_invitations WHERE thesis_assignment_id = :thesis_id");
    $stmt->bindParam(':thesis_id', $thesisId, PDO::PARAM_INT);
    $stmt->execute();
    
    $stmt = $pdo->prepare("DELETE FROM committee_members WHERE thesis_assignment_id = :thesis_id");
    $stmt->bindParam(':thesis_id', $thesisId, PDO::PARAM_INT);
    $stmt->execute();
    
    $pdo->commit();
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Η ανάθεση ακυρώθηκε επιτυχώς.']);
    
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    error_log("API Error: Failed to cancel thesis assignment. Error: " . $e->getMessage());
    
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Σφάλμα κατά την ακύρωση της διπλωματικής. Παρακαλώ δοκιμάστε ξανά.']);
}
?>

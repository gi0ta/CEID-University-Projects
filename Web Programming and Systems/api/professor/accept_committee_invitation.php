<?php

require_once '../../includes/init.php';

if (!User::isLoggedIn() || User::getCurrentUserRole() !== 'professor') {
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

$stmt = $pdo->prepare("SELECT id FROM professors WHERE user_id = :user_id");
$stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
$stmt->execute();
$professor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$professor) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Δεν βρέθηκε ο καθηγητής'
    ]);
    exit;
}

$professorId = $professor['id'];

$stmt = $pdo->prepare("
    SELECT 
        ci.id,
        ci.status,
        ci.thesis_assignment_id,
        ci.invited_professor_id
    FROM 
        committee_invitations ci
    WHERE 
        ci.id = :invitation_id AND
        ci.invited_professor_id = :professor_id
");
$stmt->bindParam(':invitation_id', $invitationId, PDO::PARAM_INT);
$stmt->bindParam(':professor_id', $professorId, PDO::PARAM_INT);
$stmt->execute();
$invitation = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$invitation) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Δεν βρέθηκε η πρόσκληση ή δεν απευθύνεται σε εσάς'
    ]);
    exit;
}

if ($invitation['status'] !== 'pending') {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Η πρόσκληση δεν μπορεί να γίνει αποδεκτή γιατί δεν είναι σε κατάσταση αναμονής'
    ]);
    exit;
}

$thesisAssignmentId = $invitation['thesis_assignment_id'];

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

file_put_contents('/tmp/accept_invitation_debug.log', "Script started at " . date('Y-m-d H:i:s') . " for invitation ID: $invitationId\n", FILE_APPEND);

try {
    $pdo->beginTransaction();
    file_put_contents('/tmp/accept_invitation_debug.log', "Transaction started\n", FILE_APPEND);
    
    file_put_contents('/tmp/accept_invitation_debug.log', "Updating invitation status to accepted\n", FILE_APPEND);
    $stmt = $pdo->prepare("
        UPDATE 
            committee_invitations 
        SET 
            status = 'accepted', 
            response_date = NOW() 
        WHERE 
            id = :invitation_id
    ");
    $stmt->bindParam(':invitation_id', $invitationId, PDO::PARAM_INT);
    $result = $stmt->execute();
    if (!$result) {
        file_put_contents('/tmp/accept_invitation_debug.log', "Error updating invitation status: " . json_encode($stmt->errorInfo()) . "\n", FILE_APPEND);
    } else {
        file_put_contents('/tmp/accept_invitation_debug.log', "Invitation status updated successfully\n", FILE_APPEND);
    }
    
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) AS count
        FROM 
            committee_members
        WHERE 
            thesis_assignment_id = :thesis_assignment_id AND
            status = 'accepted'
    ");
    $stmt->bindParam(':thesis_assignment_id', $thesisAssignmentId, PDO::PARAM_INT);
    $stmt->execute();
    $existingMembersCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($existingMembersCount >= 2) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Η τριμελής επιτροπή για αυτή τη διπλωματική έχει ήδη συμπληρωθεί.'
        ]);
        exit;
    }
    
    file_put_contents('/tmp/accept_invitation_debug.log', "Adding professor to committee. thesis_assignment_id: $thesisAssignmentId, professor_id: $professorId\n", FILE_APPEND);
    $stmt = $pdo->prepare("
        INSERT INTO committee_members (
            thesis_assignment_id, 
            professor_id, 
            status, 
            invitation_date, 
            response_date
        ) VALUES (
            :thesis_assignment_id, 
            :professor_id, 
            'accepted', 
            NOW(), 
            NOW()
        )
    ");
    $stmt->bindParam(':thesis_assignment_id', $thesisAssignmentId, PDO::PARAM_INT);
    $stmt->bindParam(':professor_id', $professorId, PDO::PARAM_INT);
    $result = $stmt->execute();
    if (!$result) {
        file_put_contents('/tmp/accept_invitation_debug.log', "Error adding professor to committee: " . json_encode($stmt->errorInfo()) . "\n", FILE_APPEND);
    } else {
        file_put_contents('/tmp/accept_invitation_debug.log', "Professor added to committee successfully\n", FILE_APPEND);
    }
    
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) AS count
        FROM 
            committee_members
        WHERE 
            thesis_assignment_id = :thesis_assignment_id AND
            status = 'accepted'
    ");
    $stmt->bindParam(':thesis_assignment_id', $thesisAssignmentId, PDO::PARAM_INT);
    $stmt->execute();
    $acceptedMembersCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($acceptedMembersCount >= 2) {
        $stmt = $pdo->prepare("
            UPDATE thesis_assignments 
            SET status = 'active' 
            WHERE id = :thesis_assignment_id AND status = 'pending'
        ");
        $stmt->bindParam(':thesis_assignment_id', $thesisAssignmentId, PDO::PARAM_INT);
        $stmt->execute();
        
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
                'pending', 
                'active', 
                NOW(), 
                :professor_id, 
                'Αυτόματη ενεργοποίηση μετά τη συμπλήρωση της τριμελούς επιτροπής'
            )
        ");
        $stmt->bindParam(':thesis_assignment_id', $thesisAssignmentId, PDO::PARAM_INT);
        $stmt->bindParam(':professor_id', $professorId, PDO::PARAM_INT);
        $stmt->execute();
        
        $stmt = $pdo->prepare("
            UPDATE committee_invitations 
            SET status = 'declined', response_date = NOW()
            WHERE thesis_assignment_id = :thesis_assignment_id AND status = 'pending'
        ");
        $stmt->bindParam(':thesis_assignment_id', $thesisAssignmentId, PDO::PARAM_INT);
        $stmt->execute();
    }
    
    file_put_contents('/tmp/accept_invitation_debug.log', "Committing transaction\n", FILE_APPEND);
    $pdo->commit();
    file_put_contents('/tmp/accept_invitation_debug.log', "Transaction committed successfully\n", FILE_APPEND);

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Η πρόσκληση έγινε αποδεκτή με επιτυχία.',
        'committee_completed' => ($acceptedMembersCount >= 2)
    ]);
    file_put_contents('/tmp/accept_invitation_debug.log', "Success response sent\n", FILE_APPEND);
    exit;
} catch (Exception $e) {
    file_put_contents('/tmp/accept_invitation_debug.log', "Error occurred: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n", FILE_APPEND);
    $pdo->rollBack();
    file_put_contents('/tmp/accept_invitation_debug.log', "Transaction rolled back\n", FILE_APPEND);

    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Σφάλμα κατά την αποδοχή της πρόσκλησης: ' . $e->getMessage()
    ]);
    file_put_contents('/tmp/accept_invitation_debug.log', "Error response sent\n", FILE_APPEND);
    exit;
}

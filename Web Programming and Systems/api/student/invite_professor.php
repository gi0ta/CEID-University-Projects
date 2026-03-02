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

$professorId = null;

if (isset($_POST['professor_id']) && !empty($_POST['professor_id'])) {
    $professorId = $_POST['professor_id'];
} else {
    $jsonData = json_decode(file_get_contents('php://input'), true);
    if (isset($jsonData['professor_id']) && !empty($jsonData['professor_id'])) {
        $professorId = $jsonData['professor_id'];
    }
}

if ($professorId === null) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Δεν παρέχθηκε το ID του καθηγητή'
    ]);
    exit;
}

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
        ta.id,
        ta.status,
        tt.professor_id AS supervisor_id
    FROM 
        thesis_assignments ta
    JOIN 
        thesis_topics tt ON ta.thesis_topic_id = tt.id
    WHERE 
        ta.student_id = :student_id AND 
        ta.status = 'pending'
    ORDER BY 
        ta.assignment_date DESC 
    LIMIT 1
");
$stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
$stmt->execute();
$thesisAssignment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$thesisAssignment) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Δεν βρέθηκε ενεργή διπλωματική εργασία σε κατάσταση "pending"'
    ]);
    exit;
}

$thesisAssignmentId = $thesisAssignment['id'];
$supervisorId = $thesisAssignment['supervisor_id'];

if ($professorId == $supervisorId) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Δεν μπορείτε να προσκαλέσετε τον επιβλέποντα καθηγητή ως μέλος της επιτροπής'
    ]);
    exit;
}

$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) AS count
    FROM 
        committee_members
    WHERE 
        thesis_assignment_id = :thesis_assignment_id AND
        professor_id = :professor_id
");
$stmt->bindParam(':thesis_assignment_id', $thesisAssignmentId, PDO::PARAM_INT);
$stmt->bindParam(':professor_id', $professorId, PDO::PARAM_INT);
$stmt->execute();
$memberCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

if ($memberCount > 0) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Ο καθηγητής είναι ήδη μέλος της επιτροπής'
    ]);
    exit;
}

$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) AS count
    FROM 
        committee_invitations
    WHERE 
        thesis_assignment_id = :thesis_assignment_id AND
        invited_professor_id = :professor_id AND
        status = 'pending'
");
$stmt->bindParam(':thesis_assignment_id', $thesisAssignmentId, PDO::PARAM_INT);
$stmt->bindParam(':professor_id', $professorId, PDO::PARAM_INT);
$stmt->execute();
$invitationCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

if ($invitationCount > 0) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Υπάρχει ήδη εκκρεμής πρόσκληση για αυτόν τον καθηγητή'
    ]);
    exit;
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

$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) AS count
    FROM 
        committee_invitations
    WHERE 
        thesis_assignment_id = :thesis_assignment_id AND
        status = 'pending'
");
$stmt->bindParam(':thesis_assignment_id', $thesisAssignmentId, PDO::PARAM_INT);
$stmt->execute();
$pendingInvitationsCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

if ($acceptedMembersCount >= 2) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Η επιτροπή έχει ήδη συμπληρωθεί με 2 μέλη'
    ]);
    exit;
}

if ($pendingInvitationsCount >= 5) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Έχετε ήδη πολλές εκκρεμείς προσκλήσεις. Περιμένετε να απαντηθούν πρώτα.'
    ]);
    exit;
}

try {
    $pdo->beginTransaction();
    
    $stmt = $pdo->prepare("
        INSERT INTO committee_invitations (
            thesis_assignment_id, 
            inviting_professor_id, 
            invited_professor_id, 
            status, 
            invitation_date
        ) VALUES (
            :thesis_assignment_id, 
            :inviting_professor_id, 
            :invited_professor_id, 
            'pending', 
            NOW()
        )
    ");
    $stmt->bindParam(':thesis_assignment_id', $thesisAssignmentId, PDO::PARAM_INT);
    $stmt->bindParam(':inviting_professor_id', $supervisorId, PDO::PARAM_INT);
    $stmt->bindParam(':invited_professor_id', $professorId, PDO::PARAM_INT);
    $stmt->execute();
    
    $invitationId = $pdo->lastInsertId();
    
    $pdo->commit();
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Η πρόσκληση στάλθηκε με επιτυχία',
        'invitation_id' => $invitationId
    ]);
    
} catch (Exception $e) {
    $pdo->rollBack();
    
    error_log('Error sending invitation: ' . $e->getMessage());
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Σφάλμα κατά την αποστολή της πρόσκλησης'
    ]);
}

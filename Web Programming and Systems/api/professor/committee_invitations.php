<?php

require_once '../../includes/init.php';

header('Content-Type: application/json');

if (!User::isLoggedIn() || User::getCurrentUserRole() !== 'professor') {
    echo json_encode(['success' => false, 'message' => 'Μη εξουσιοδοτημένη πρόσβαση. Παρακαλώ συνδεθείτε ως καθηγητής.']);
    exit();
}

$userId = User::getCurrentUserId();
$db = new Database();
$pdo = $db->getConnection();

$stmt = $pdo->prepare("SELECT id FROM professors WHERE user_id = :user_id");
$stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
$stmt->execute();
$professor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$professor) {
    echo json_encode(['success' => false, 'message' => 'Δεν βρέθηκε ο καθηγητής']);
    exit();
}

$professorId = $professor['id'];
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                ci.id as invitation_id,
                ci.thesis_assignment_id,
                ci.status,
                ci.invitation_date,
                ci.response_date,
                ta.title,
                CONCAT(u_student.first_name, ' ', u_student.last_name) AS student_name,
                CONCAT(u_supervisor.first_name, ' ', u_supervisor.last_name) AS inviting_professor_name
            FROM 
                committee_invitations ci
            JOIN 
                thesis_assignments ta ON ci.thesis_assignment_id = ta.id
            JOIN 
                students s ON ta.student_id = s.id
            JOIN 
                users u_student ON s.user_id = u_student.id
            JOIN 
                professors p_supervisor ON ta.supervisor_id = p_supervisor.id
            JOIN 
                users u_supervisor ON p_supervisor.user_id = u_supervisor.id
            WHERE 
                ci.invited_professor_id = :professor_id AND
                ci.status = 'pending'
            ORDER BY 
                ci.invitation_date DESC
        ");
        $stmt->bindParam(':professor_id', $professorId, PDO::PARAM_INT);
        $stmt->execute();
        $pendingInvitations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $stmt = $pdo->prepare("
            SELECT 
                ci.id as invitation_id,
                ci.thesis_assignment_id,
                ci.status,
                ci.invitation_date,
                ci.response_date,
                ta.title,
                CONCAT(u_student.first_name, ' ', u_student.last_name) AS student_name,
                CONCAT(u_supervisor.first_name, ' ', u_supervisor.last_name) AS inviting_professor_name
            FROM 
                committee_invitations ci
            JOIN 
                thesis_assignments ta ON ci.thesis_assignment_id = ta.id
            JOIN 
                students s ON ta.student_id = s.id
            JOIN 
                users u_student ON s.user_id = u_student.id
            JOIN 
                professors p_supervisor ON ta.supervisor_id = p_supervisor.id
            JOIN 
                users u_supervisor ON p_supervisor.user_id = u_supervisor.id
            WHERE 
                ci.invited_professor_id = :professor_id AND
                ci.status IN ('accepted', 'rejected')
            ORDER BY 
                ci.response_date DESC
            LIMIT 10
        ");
        $stmt->bindParam(':professor_id', $professorId, PDO::PARAM_INT);
        $stmt->execute();
        $historyInvitations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $formattedInvitations = [];
        foreach ($pendingInvitations as $invitation) {
            $formattedInvitations[] = [
                'invitation_id' => $invitation['invitation_id'],
                'thesis_assignment_id' => $invitation['thesis_assignment_id'],
                'thesis_title' => $invitation['title'],
                'student_name' => explode(' ', $invitation['student_name'])[0],
                'student_surname' => explode(' ', $invitation['student_name'], 2)[1] ?? '',
                'inviting_professor_name' => explode(' ', $invitation['inviting_professor_name'])[0],
                'inviting_professor_surname' => explode(' ', $invitation['inviting_professor_name'], 2)[1] ?? '',
                'invitation_date' => $invitation['invitation_date'],
                'status' => $invitation['status']
            ];
        }
        
        echo json_encode(['success' => true, 'invitations' => $formattedInvitations]);
        
    } catch (PDOException $e) {
        error_log("API Error: Failed to fetch committee invitations for professor_id: $professorId. Error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Σφάλμα κατά την ανάκτηση των προσκλήσεων. Παρακαλώ δοκιμάστε ξανά.']);
    }

} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['invitation_id']) || !isset($data['action'])) {
        echo json_encode(['success' => false, 'message' => 'Λείπουν απαραίτητες παράμετροι (invitation_id, action).']);
        exit();
    }

    $invitationId = filter_var($data['invitation_id'], FILTER_SANITIZE_NUMBER_INT);
    $action = filter_var($data['action'], FILTER_SANITIZE_STRING);

    if ($action !== 'accept' && $action !== 'decline') {
        echo json_encode(['success' => false, 'message' => 'Μη έγκυρη ενέργεια.']);
        exit();
    }

    try {
        $checkSql = "SELECT id FROM committee_invitations WHERE id = :invitation_id AND invited_professor_id = :professor_id AND status = 'pending'";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->bindParam(':invitation_id', $invitationId, PDO::PARAM_INT);
        $checkStmt->bindParam(':professor_id', $professorId, PDO::PARAM_INT);
        $checkStmt->execute();
        
        if ($checkStmt->rowCount() === 0) {
            echo json_encode(['success' => false, 'message' => 'Η πρόσκληση δεν βρέθηκε, δεν σας ανήκει ή έχει ήδη απαντηθεί.']);
            exit();
        }
        
        if ($action === 'accept') {
            require_once __DIR__ . '/accept_committee_invitation.php';
        } else {
            require_once __DIR__ . '/reject_committee_invitation.php';
        }
        
    } catch (PDOException $e) {
        error_log("API Error: Failed to process invitation response for invitation_id: $invitationId by professor_id: $professorId. Error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Σφάλμα κατά την επεξεργασία της απάντησης. Παρακαλώ δοκιμάστε ξανά.']);
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>

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
        ci.thesis_assignment_id,
        ci.status,
        ci.invitation_date,
        ci.response_date,
        tt.title,
        CONCAT(u_student.first_name, ' ', u_student.last_name) AS student_name,
        CONCAT(u_supervisor.first_name, ' ', u_supervisor.last_name) AS supervisor_name
    FROM 
        committee_invitations ci
    JOIN 
        thesis_assignments ta ON ci.thesis_assignment_id = ta.id
    JOIN 
        thesis_topics tt ON ta.thesis_topic_id = tt.id
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
        ci.id,
        ci.thesis_assignment_id,
        ci.status,
        ci.invitation_date,
        ci.response_date,
        tt.title,
        CONCAT(u_student.first_name, ' ', u_student.last_name) AS student_name,
        CONCAT(u_supervisor.first_name, ' ', u_supervisor.last_name) AS supervisor_name
    FROM 
        committee_invitations ci
    JOIN 
        thesis_assignments ta ON ci.thesis_assignment_id = ta.id
    JOIN 
        thesis_topics tt ON ta.thesis_topic_id = tt.id
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
        ci.status IN ('accepted', 'declined')
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
        'invitation_id' => $invitation['id'],
        'thesis_assignment_id' => $invitation['thesis_assignment_id'],
        'thesis_title' => $invitation['title'],
        'student_name' => explode(' ', $invitation['student_name'])[0],
        'student_surname' => explode(' ', $invitation['student_name'], 2)[1] ?? '',
        'inviting_professor_name' => explode(' ', $invitation['supervisor_name'])[0],
        'inviting_professor_surname' => explode(' ', $invitation['supervisor_name'], 2)[1] ?? '',
        'invitation_date' => $invitation['invitation_date'],
        'status' => $invitation['status']
    ];
}

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'invitations' => $formattedInvitations
]);
?>

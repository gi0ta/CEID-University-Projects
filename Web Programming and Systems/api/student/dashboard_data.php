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

$dashboardData = [
    'active_thesis' => null,
    'notifications' => []
];

$stmt = $pdo->prepare("
    SELECT 
        ta.id AS assignment_id,
        tt.title,
        tt.summary,
        p.id AS professor_id,
        CONCAT(u.first_name, ' ', u.last_name) AS professor_name,
        ta.status,
        ta.assignment_date
    FROM 
        thesis_assignments ta
    JOIN 
        thesis_topics tt ON ta.thesis_topic_id = tt.id
    JOIN 
        professors p ON tt.professor_id = p.id
    JOIN 
        users u ON p.user_id = u.id
    WHERE 
        ta.student_id = :student_id AND
        ta.status IN ('pending', 'active', 'examination', 'completed', 'cancelled')
    ORDER BY 
        ta.assignment_date DESC
    LIMIT 1
");
$stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
$stmt->execute();
$activeThesis = $stmt->fetch(PDO::FETCH_ASSOC);

if ($activeThesis) {
    $stmt = $pdo->prepare("
        SELECT 
            cm.id,
            p.id AS professor_id,
            CONCAT(u.first_name, ' ', u.last_name) AS professor_name,
            cm.status
        FROM 
            committee_members cm
        JOIN 
            professors p ON cm.professor_id = p.id
        JOIN 
            users u ON p.user_id = u.id
        WHERE 
            cm.thesis_assignment_id = :assignment_id
    ");
    $stmt->bindParam(':assignment_id', $activeThesis['assignment_id'], PDO::PARAM_INT);
    $stmt->execute();
    $committeeMembers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt = $pdo->prepare("
        SELECT 
            ci.id,
            p.id AS professor_id,
            CONCAT(u.first_name, ' ', u.last_name) AS professor_name,
            ci.status,
            ci.invitation_date
        FROM 
            committee_invitations ci
        JOIN 
            professors p ON ci.invited_professor_id = p.id
        JOIN 
            users u ON p.user_id = u.id
        WHERE 
            ci.thesis_assignment_id = :assignment_id AND
            ci.status = 'pending'
    ");
    $stmt->bindParam(':assignment_id', $activeThesis['assignment_id'], PDO::PARAM_INT);
    $stmt->execute();
    $pendingInvitations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $allCommitteeMembers = $committeeMembers;
    foreach ($pendingInvitations as $invitation) {
        $allCommitteeMembers[] = $invitation;
    }
    
    $activeThesis['committee_members'] = $allCommitteeMembers;
    
    $dashboardData['active_thesis'] = $activeThesis;
}


header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'data' => $dashboardData
]);

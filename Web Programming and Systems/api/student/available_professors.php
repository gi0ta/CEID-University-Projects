<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../../includes/init.php';

try {
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

$stmt = $pdo->prepare("
    SELECT 
        id, 
        status
    FROM 
        thesis_assignments 
    WHERE 
        student_id = :student_id AND 
        status = 'pending'
    ORDER BY 
        assignment_date DESC 
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

$stmt = $pdo->prepare("
    SELECT 
        tt.professor_id AS supervisor_id
    FROM 
        thesis_assignments ta
    JOIN 
        thesis_topics tt ON ta.thesis_topic_id = tt.id
    WHERE 
        ta.id = :thesis_assignment_id
");
$stmt->bindParam(':thesis_assignment_id', $thesisAssignmentId, PDO::PARAM_INT);
$stmt->execute();
$supervisor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$supervisor) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Δεν βρέθηκε ο επιβλέπων καθηγητής'
    ]);
    exit;
}

$supervisorId = $supervisor['supervisor_id'];

try {
    $stmt = $pdo->prepare("
        SELECT 
            professor_id
        FROM 
            committee_members
        WHERE 
            thesis_assignment_id = :thesis_assignment_id
        
        UNION
        
        SELECT 
            invited_professor_id AS professor_id
        FROM 
            committee_invitations
        WHERE 
            thesis_assignment_id = :thesis_assignment_id AND
            status = 'pending'
    ");
    $stmt->bindParam(':thesis_assignment_id', $thesisAssignmentId, PDO::PARAM_INT);
    $stmt->execute();
    $excludedProfessors = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    $excludedProfessors = [];
    error_log('Error fetching excluded professors: ' . $e->getMessage());
}

if (isset($supervisorId)) {
    $excludedProfessors[] = $supervisorId;
}

if (!empty($excludedProfessors)) {
    $placeholders = implode(',', array_fill(0, count($excludedProfessors), '?'));
} else {
    $placeholders = "''";
}

$sql = "
    SELECT 
        p.id,
        CONCAT(u.first_name, ' ', u.last_name) AS name
    FROM 
        professors p
    JOIN 
        users u ON p.user_id = u.id
    WHERE 
        u.role = 'professor' AND
        p.id NOT IN ($placeholders)
    ORDER BY 
        u.last_name, u.first_name
";

$stmt = $pdo->prepare($sql);
foreach ($excludedProfessors as $index => $id) {
    $stmt->bindValue($index + 1, $id, PDO::PARAM_INT);
}
$stmt->execute();
$availableProfessors = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');

if (empty($availableProfessors)) {
    $stmt = $pdo->prepare("
        SELECT 
            p.id,
            CONCAT(u.first_name, ' ', u.last_name) AS name
        FROM 
            professors p
        JOIN 
            users u ON p.user_id = u.id
        WHERE 
            u.role = 'professor'
        ORDER BY 
            u.last_name, u.first_name
    ");
    $stmt->execute();
    $availableProfessors = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

echo json_encode([
    'success' => true,
    'professors' => $availableProfessors
]);

} catch (Exception $e) {
    error_log('Error in available_professors.php: ' . $e->getMessage());
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Σφάλμα κατά την ανάκτηση των καθηγητών',
        'error' => $e->getMessage()
    ]);
}

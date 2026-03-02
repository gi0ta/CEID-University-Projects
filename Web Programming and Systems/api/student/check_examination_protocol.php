<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../../includes/config/config.php';
require_once '../../includes/config/database.php';
require_once '../../includes/classes/User.php';
require_once '../../includes/functions/auth_functions.php';

header('Content-Type: application/json');

startSecureSession();

if (!User::isLoggedIn() || User::getCurrentUserRole() !== 'student') {
    echo json_encode([
        'success' => false,
        'message' => 'Δεν έχετε πρόσβαση σε αυτή τη λειτουργία.',
        'has_protocol' => false
    ]);
    exit;
}

$user_id = User::getCurrentUserId();
$db = getDbConnection();

$student_stmt = $db->prepare("
    SELECT s.id 
    FROM students s 
    WHERE s.user_id = ?
");
$student_stmt->bindParam(1, $user_id, PDO::PARAM_INT);
$student_stmt->execute();
$student = $student_stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    echo json_encode([
        'success' => false,
        'message' => 'Δεν βρέθηκε ο φοιτητής.',
        'has_protocol' => false
    ]);
    exit;
}

$student_id = $student['id'];

$thesis_stmt = $db->prepare("
    SELECT 
        ta.id,
        ta.examination_protocol_generated
    FROM 
        thesis_assignments ta
    WHERE 
        ta.student_id = ? AND 
        (ta.status = 'examination' OR ta.status = 'completed') AND
        ta.examination_protocol_generated = 1
    ORDER BY 
        ta.id DESC
    LIMIT 1
");
$thesis_stmt->bindParam(1, $student_id, PDO::PARAM_INT);
$thesis_stmt->execute();
$thesis = $thesis_stmt->fetch(PDO::FETCH_ASSOC);

$has_protocol = ($thesis !== false && $thesis['examination_protocol_generated'] == 1);

echo json_encode([
    'success' => true,
    'has_protocol' => $has_protocol,
    'thesis_id' => $has_protocol ? $thesis['id'] : null,
    'debug' => [
        'thesis_found' => ($thesis !== false),
        'thesis_status' => $thesis ? $thesis['examination_protocol_generated'] : null,
        'thesis_data' => $thesis
    ]
]);
exit;

<?php

require_once '../../includes/config/config.php';
require_once '../../includes/config/database.php';
require_once '../../includes/classes/User.php';
require_once '../../includes/functions/auth_functions.php';

header('Content-Type: application/json');

startSecureSession();

if (!User::isLoggedIn() || User::getCurrentUserRole() !== 'professor') {
    echo json_encode([
        'success' => false,
        'message' => 'Δεν έχετε πρόσβαση σε αυτή τη λειτουργία.'
    ]);
    exit;
}

if (!isset($_GET['thesis_id']) || !is_numeric($_GET['thesis_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Δεν έχει οριστεί έγκυρο ID διπλωματικής εργασίας.'
    ]);
    exit;
}

$thesis_id = intval($_GET['thesis_id']);

$db = getDbConnection();

$user_id = User::getCurrentUserId();
$professor_stmt = $db->prepare("
    SELECT p.id 
    FROM professors p 
    WHERE p.user_id = ?
");
$professor_stmt->bindParam(1, $user_id, PDO::PARAM_INT);
$professor_stmt->execute();
$professor = $professor_stmt->fetch(PDO::FETCH_ASSOC);

if (!$professor) {
    echo json_encode([
        'success' => false,
        'message' => 'Δεν βρέθηκε ο καθηγητής.'
    ]);
    exit;
}

$professor_id = $professor['id'];

$thesis_check_stmt = $db->prepare("
    SELECT ta.id, ta.examination_protocol_generated
    FROM thesis_assignments ta
    WHERE ta.id = ? AND ta.supervisor_id = ?
");
$thesis_check_stmt->bindParam(1, $thesis_id, PDO::PARAM_INT);
$thesis_check_stmt->bindParam(2, $professor_id, PDO::PARAM_INT);
$thesis_check_stmt->execute();
$thesis_info = $thesis_check_stmt->fetch(PDO::FETCH_ASSOC);

if (!$thesis_info) {
    echo json_encode([
        'success' => false,
        'message' => 'Δεν βρέθηκε διπλωματική εργασία με αυτό το ID που να επιβλέπετε.'
    ]);
    exit;
}

if ($thesis_info['examination_protocol_generated'] != 1) {
    echo json_encode([
        'success' => false,
        'message' => 'Δεν έχει δημιουργηθεί ακόμα το πρωτόκολλο εξέτασης για αυτή τη διπλωματική εργασία.'
    ]);
    exit;
}

$protocol_stmt = $db->prepare("
    SELECT 
        ep.protocol_html,
        ep.protocol_number,
        ep.protocol_date
    FROM 
        examination_protocols ep
    WHERE 
        ep.thesis_assignment_id = ?
    ORDER BY 
        ep.id DESC
    LIMIT 1
");
$protocol_stmt->bindParam(1, $thesis_id, PDO::PARAM_INT);
$protocol_stmt->execute();
$protocol = $protocol_stmt->fetch(PDO::FETCH_ASSOC);

if (!$protocol) {
    echo json_encode([
        'success' => false,
        'message' => 'Δεν βρέθηκε το πρωτόκολλο εξέτασης για αυτή τη διπλωματική εργασία.'
    ]);
    exit;
}

echo json_encode([
    'success' => true,
    'protocol' => [
        'html' => $protocol['protocol_html'],
        'number' => $protocol['protocol_number'],
        'date' => $protocol['protocol_date']
    ]
]);
exit;

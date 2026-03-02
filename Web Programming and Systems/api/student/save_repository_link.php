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
        'message' => 'Δεν έχετε πρόσβαση σε αυτή τη λειτουργία.'
    ]);
    exit;
}

if (!isset($_POST['thesis_id']) || !is_numeric($_POST['thesis_id']) || !isset($_POST['repository_link']) || empty($_POST['repository_link'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Λείπουν απαραίτητα δεδομένα.'
    ]);
    exit;
}

$thesis_id = intval($_POST['thesis_id']);
$repository_link = trim($_POST['repository_link']);

if (!filter_var($repository_link, FILTER_VALIDATE_URL)) {
    echo json_encode([
        'success' => false,
        'message' => 'Ο σύνδεσμος που εισάγατε δεν είναι έγκυρο URL.'
    ]);
    exit;
}

$db = getDbConnection();

$user_id = User::getCurrentUserId();
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
        'message' => 'Δεν βρέθηκε ο φοιτητής.'
    ]);
    exit;
}

$student_id = $student['id'];

$thesis_check_stmt = $db->prepare("
    SELECT ta.id, ta.status, ta.examination_protocol_generated, ta.repository_link
    FROM thesis_assignments ta
    WHERE ta.id = ? AND ta.student_id = ?
");
$thesis_check_stmt->bindParam(1, $thesis_id, PDO::PARAM_INT);
$thesis_check_stmt->bindParam(2, $student_id, PDO::PARAM_INT);
$thesis_check_stmt->execute();
$thesis_info = $thesis_check_stmt->fetch(PDO::FETCH_ASSOC);

if (!$thesis_info) {
    echo json_encode([
        'success' => false,
        'message' => 'Δεν βρέθηκε διπλωματική εργασία με αυτό το ID που να ανήκει σε εσάς.'
    ]);
    exit;
}

if ($thesis_info['examination_protocol_generated'] != 1) {
    echo json_encode([
        'success' => false,
        'message' => 'Δεν έχει δημιουργηθεί ακόμα το πρωτόκολλο εξέτασης για τη διπλωματική σας εργασία.'
    ]);
    exit;
}

try {
    $update_stmt = $db->prepare("
        UPDATE thesis_assignments 
        SET repository_link = ?
        WHERE id = ?
    ");
    $update_stmt->bindParam(1, $repository_link, PDO::PARAM_STR);
    $update_stmt->bindParam(2, $thesis_id, PDO::PARAM_INT);
    $update_stmt->execute();
    
    echo json_encode([
        'success' => true,
        'message' => 'Ο σύνδεσμος αποθετηρίου αποθηκεύτηκε με επιτυχία.'
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Σφάλμα κατά την αποθήκευση του συνδέσμου: ' . $e->getMessage()
    ]);
}

exit;

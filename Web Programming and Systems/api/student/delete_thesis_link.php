<?php

require_once '../../includes/init.php';

header('Content-Type: application/json');

if (!User::isLoggedIn() || User::getCurrentUserRole() !== 'student') {
    echo json_encode([
        'success' => false,
        'message' => 'Δεν έχετε δικαίωμα πρόσβασης σε αυτό το API.'
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Μη έγκυρη μέθοδος αιτήματος.'
    ]);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['link_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Λείπει το ID του συνδέσμου.'
    ]);
    exit;
}

$link_id = (int)$data['link_id'];

$db = new Database();
$pdo = $db->getConnection();

$user_id = User::getCurrentUserId();

$stmt = $pdo->prepare("
    SELECT tel.*, ta.id as thesis_id, ta.status
    FROM thesis_external_links tel
    JOIN thesis_assignments ta ON tel.thesis_assignment_id = ta.id
    JOIN students s ON ta.student_id = s.id
    WHERE tel.id = :link_id AND s.user_id = :user_id
");
$stmt->bindParam(':link_id', $link_id, PDO::PARAM_INT);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$link = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$link) {
    echo json_encode([
        'success' => false,
        'message' => 'Ο σύνδεσμος δεν βρέθηκε ή δεν έχετε δικαίωμα πρόσβασης.'
    ]);
    exit;
}

if ($link['status'] !== 'examination') {
    echo json_encode([
        'success' => false,
        'message' => 'Μπορείτε να διαγράψετε συνδέσμους μόνο όταν η διπλωματική είναι σε κατάσταση "Υπό Εξέταση".'
    ]);
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM thesis_external_links WHERE id = :link_id");
    $stmt->bindParam(':link_id', $link_id, PDO::PARAM_INT);
    $stmt->execute();
    
    if (tableExists($pdo, 'thesis_timeline')) {
        $action = "Διαγραφή συνδέσμου: " . $link['link_title'];
        $stmt = $pdo->prepare("
            INSERT INTO thesis_timeline (thesis_assignment_id, action, created_by, user_role)
            VALUES (:thesis_id, :action, :user_id, 'student')
        ");
        $stmt->bindParam(':thesis_id', $link['thesis_id'], PDO::PARAM_INT);
        $stmt->bindParam(':action', $action, PDO::PARAM_STR);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Ο σύνδεσμος διαγράφηκε με επιτυχία.'
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Σφάλμα κατά τη διαγραφή του συνδέσμου από τη βάση δεδομένων: ' . $e->getMessage()
    ]);
}

function tableExists($pdo, $table) {
    try {
        $result = $pdo->query("SHOW TABLES LIKE '{$table}'");
        return $result->rowCount() > 0;
    } catch (Exception $e) {
        return false;
    }
}

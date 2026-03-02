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

if (!isset($data['thesis_id']) || !isset($data['title']) || !isset($data['url'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Λείπουν απαραίτητα δεδομένα.'
    ]);
    exit;
}

$thesis_id = (int)$data['thesis_id'];
$title = trim($data['title']);
$url = trim($data['url']);
$description = isset($data['description']) ? trim($data['description']) : '';

if (!filter_var($url, FILTER_VALIDATE_URL)) {
    echo json_encode([
        'success' => false,
        'message' => 'Το URL δεν είναι έγκυρο.'
    ]);
    exit;
}

$db = new Database();
$pdo = $db->getConnection();

$user_id = User::getCurrentUserId();

$stmt = $pdo->prepare("
    SELECT ta.id
    FROM thesis_assignments ta 
    JOIN students s ON ta.student_id = s.id
    WHERE ta.id = :thesis_id AND s.user_id = :user_id AND ta.status = 'examination'
");
$stmt->bindParam(':thesis_id', $thesis_id, PDO::PARAM_INT);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$thesis = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$thesis) {
    echo json_encode([
        'success' => false,
        'message' => 'Δεν βρέθηκε έγκυρη διπλωματική εργασία ή δεν έχετε δικαίωμα πρόσβασης.'
    ]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO thesis_external_links (thesis_assignment_id, link_title, link_url, link_description)
        VALUES (:thesis_id, :title, :url, :description)
    ");
    $stmt->bindParam(':thesis_id', $thesis_id, PDO::PARAM_INT);
    $stmt->bindParam(':title', $title, PDO::PARAM_STR);
    $stmt->bindParam(':url', $url, PDO::PARAM_STR);
    $stmt->bindParam(':description', $description, PDO::PARAM_STR);
    $stmt->execute();
    
    $link_id = $pdo->lastInsertId();
    
    if (tableExists($pdo, 'thesis_timeline')) {
        $action = "Προσθήκη συνδέσμου: " . $title;
        $stmt = $pdo->prepare("
            INSERT INTO thesis_timeline (thesis_assignment_id, action, created_by, user_role)
            VALUES (:thesis_id, :action, :user_id, 'student')
        ");
        $stmt->bindParam(':thesis_id', $thesis_id, PDO::PARAM_INT);
        $stmt->bindParam(':action', $action, PDO::PARAM_STR);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Ο σύνδεσμος προστέθηκε με επιτυχία.',
        'link_id' => $link_id
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Σφάλμα κατά την αποθήκευση του συνδέσμου: ' . $e->getMessage()
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

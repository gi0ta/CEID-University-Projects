<?php

require_once '../../includes/init.php';

header('Content-Type: application/json');

if (!User::isLoggedIn() || User::getCurrentUserRole() !== 'student') {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Δεν έχετε δικαίωμα πρόσβασης σε αυτό το API endpoint.'
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Μη έγκυρη μέθοδος αιτήματος.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['thesis_id']) || !is_numeric($data['thesis_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Λείπει το ID της διπλωματικής εργασίας.']);
    exit;
}

$db = new Database();
$pdo = $db->getConnection();

$thesis_id = intval($data['thesis_id']);
$user_id = User::getCurrentUserId();

$stmt = $pdo->prepare("
    SELECT ta.id, ta.status 
    FROM thesis_assignments ta
    JOIN students s ON ta.student_id = s.id
    WHERE ta.id = :thesis_id AND s.user_id = :user_id
");
$stmt->bindParam(':thesis_id', $thesis_id, PDO::PARAM_INT);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$thesis = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$thesis) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Δεν έχετε δικαίωμα να διαχειριστείτε αυτή τη διπλωματική εργασία.']);
    exit;
}

if ($thesis['status'] !== 'examination') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Η διπλωματική εργασία δεν είναι σε κατάσταση εξέτασης.']);
    exit;
}

if (!isset($data['presentationDate']) || empty($data['presentationDate']) || 
    !isset($data['presentationTime']) || empty($data['presentationTime']) || 
    !isset($data['presentationType']) || empty($data['presentationType'])) {
    
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Λείπουν απαραίτητα πεδία (ημερομηνία, ώρα ή τύπος παρουσίασης).']);
    exit;
}

$valid_types = ['physical', 'online', 'hybrid'];
if (!in_array($data['presentationType'], $valid_types)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Μη έγκυρος τύπος παρουσίασης.']);
    exit;
}

$presentation_type = $data['presentationType'];
$location = null;
$online_link = null;

if ($presentation_type === 'physical' || $presentation_type === 'hybrid') {
    if (!isset($data['location']) || empty($data['location'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Η αίθουσα παρουσίασης είναι υποχρεωτική για παρουσιάσεις δια ζώσης ή υβριδικές.']);
        exit;
    }
    $location = $data['location'];
}

if ($presentation_type === 'online' || $presentation_type === 'hybrid') {
    if (!isset($data['onlineLink']) || empty($data['onlineLink'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Ο σύνδεσμος είναι υποχρεωτικός για διαδικτυακές ή υβριδικές παρουσιάσεις.']);
        exit;
    }
    
    if (!filter_var($data['onlineLink'], FILTER_VALIDATE_URL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Μη έγκυρος σύνδεσμος.']);
        exit;
    }
    
    $online_link = $data['onlineLink'];
}

$presentation_datetime = $data['presentationDate'] . ' ' . $data['presentationTime'] . ':00';

$current_datetime = date('Y-m-d H:i:s');
if (strtotime($presentation_datetime) <= strtotime($current_datetime)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Η ημερομηνία και ώρα παρουσίασης πρέπει να είναι στο μέλλον.']);
    exit;
}

try {
    $pdo->beginTransaction();
    
    $stmt = $pdo->prepare("
        SELECT id FROM thesis_presentations 
        WHERE thesis_assignment_id = :thesis_id
    ");
    $stmt->bindParam(':thesis_id', $thesis_id, PDO::PARAM_INT);
    $stmt->execute();
    $existing_presentation = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existing_presentation) {
        $stmt = $pdo->prepare("
            UPDATE thesis_presentations 
            SET presentation_date = :presentation_date,
                presentation_type = :presentation_type,
                location = :location,
                online_link = :online_link,
                updated_at = NOW()
            WHERE id = :id
        ");
        $stmt->bindParam(':id', $existing_presentation['id'], PDO::PARAM_INT);
        $stmt->bindParam(':presentation_date', $presentation_datetime);
        $stmt->bindParam(':presentation_type', $presentation_type);
        $stmt->bindParam(':location', $location);
        $stmt->bindParam(':online_link', $online_link);
        $stmt->execute();
        
        $message = 'Τα στοιχεία παρουσίασης ενημερώθηκαν επιτυχώς.';
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO thesis_presentations 
            (thesis_assignment_id, presentation_date, presentation_type, location, online_link, created_at, updated_at)
            VALUES 
            (:thesis_id, :presentation_date, :presentation_type, :location, :online_link, NOW(), NOW())
        ");
        $stmt->bindParam(':thesis_id', $thesis_id, PDO::PARAM_INT);
        $stmt->bindParam(':presentation_date', $presentation_datetime);
        $stmt->bindParam(':presentation_type', $presentation_type);
        $stmt->bindParam(':location', $location);
        $stmt->bindParam(':online_link', $online_link);
        $stmt->execute();
        
        $presentation_id = $pdo->lastInsertId();
        $message = 'Τα στοιχεία παρουσίασης αποθηκεύτηκαν επιτυχώς.';
    }
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => $message
    ]);
    
} catch (PDOException $e) {
    $pdo->rollBack();
    
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Σφάλμα κατά την αποθήκευση: ' . $e->getMessage()
    ]);
}

<?php

require_once '../../includes/init.php';

if (!User::isLoggedIn() || User::getCurrentUserRole() !== 'professor') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Δεν επιτρέπεται η πρόσβαση']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Μη επιτρεπτή μέθοδος']);
    exit;
}

if (!isset($_GET['thesis_id']) || empty($_GET['thesis_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Απαιτείται το ID της διπλωματικής εργασίας']);
    exit;
}

$thesis_id = intval($_GET['thesis_id']);

$db = new Database();
$pdo = $db->getConnection();

try {
    $user_id = User::getCurrentUserId();
    $stmt = $pdo->prepare("SELECT id FROM professors WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $professor = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$professor) {
        throw new Exception('Δεν βρέθηκε ο καθηγητής');
    }
    
    $professor_id = $professor['id'];
    
    $stmt = $pdo->prepare("
        SELECT id, note_text, created_at
        FROM professor_notes
        WHERE thesis_assignment_id = :thesis_id 
        AND professor_id = :professor_id
        ORDER BY created_at DESC
    ");
    $stmt->bindParam(':thesis_id', $thesis_id, PDO::PARAM_INT);
    $stmt->bindParam(':professor_id', $professor_id, PDO::PARAM_INT);
    $stmt->execute();
    $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'notes' => $notes
    ]);
    
} catch (Exception $e) {
    error_log('Error in get_notes.php: ' . $e->getMessage());
    
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Σφάλμα κατά την ανάκτηση των σημειώσεων: ' . $e->getMessage()]);
}

<?php

require_once '../../includes/init.php';

if (!User::isLoggedIn() || User::getCurrentUserRole() !== 'professor') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Δεν επιτρέπεται η πρόσβαση']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Μη επιτρεπτή μέθοδος']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['thesis_id']) || !isset($data['note_text']) || empty(trim($data['note_text']))) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Ελλιπή δεδομένα']);
    exit;
}

$thesis_id = intval($data['thesis_id']);
$note_text = trim($data['note_text']);

if (mb_strlen($note_text) > 300) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Η σημείωση δεν μπορεί να υπερβαίνει τους 300 χαρακτήρες']);
    exit;
}

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
        SELECT ta.* 
        FROM thesis_assignments ta
        WHERE ta.id = :thesis_id AND ta.status = 'active'
    ");
    $stmt->bindParam(':thesis_id', $thesis_id, PDO::PARAM_INT);
    $stmt->execute();
    $thesis = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$thesis) {
        throw new Exception('Δεν βρέθηκε ενεργή διπλωματική εργασία με το συγκεκριμένο ID');
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO professor_notes (thesis_assignment_id, professor_id, note_text, is_private)
        VALUES (:thesis_id, :professor_id, :note_text, 1)
    ");
    $stmt->bindParam(':thesis_id', $thesis_id, PDO::PARAM_INT);
    $stmt->bindParam(':professor_id', $professor_id, PDO::PARAM_INT);
    $stmt->bindParam(':note_text', $note_text, PDO::PARAM_STR);
    $stmt->execute();
    
    $note_id = $pdo->lastInsertId();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Η σημείωση προστέθηκε επιτυχώς',
        'note' => [
            'id' => $note_id,
            'text' => $note_text,
            'created_at' => date('Y-m-d H:i:s')
        ]
    ]);
    
} catch (Exception $e) {
    error_log('Error in add_note.php: ' . $e->getMessage());
    
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Σφάλμα κατά την προσθήκη της σημείωσης: ' . $e->getMessage()]);
}

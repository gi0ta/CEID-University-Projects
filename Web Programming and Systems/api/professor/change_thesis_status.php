<?php

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/tmp/php_errors.log');

header('Content-Type: application/json; charset=UTF-8');

try {
    require_once '../../includes/init.php';
    
    if (!User::isLoggedIn() || User::getCurrentUserRole() !== 'professor') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Δεν επιτρέπεται η πρόσβαση']);
        exit;
    }
} catch (Exception $e) {
    error_log("API Error (change_thesis_status.php): " . $e->getMessage() . "\nStack trace: " . $e->getTraceAsString());
    
    http_response_code(500);
    
    echo json_encode(['success' => false, 'message' => 'Σφάλμα κατά την επεξεργασία του αιτήματος']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Επιτρέπεται μόνο η μέθοδος POST']);
    exit;
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!isset($data['thesis_id']) || empty($data['thesis_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Λείπει το αναγνωριστικό της διπλωματικής εργασίας']);
    exit;
}

$thesis_id = intval($data['thesis_id']);

$professor_id = User::getCurrentUserId();

try {
    $db = new Database();
    $pdo = $db->getConnection();
    
    if (!$pdo) {
        throw new Exception("Failed to get database connection");
    }
} catch (Exception $e) {
    error_log("Database connection error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Σφάλμα σύνδεσης με τη βάση δεδομένων']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id, supervisor_id, status FROM thesis_assignments WHERE id = :thesis_id");
    if (!$stmt) {
        error_log("SQL prepare error: " . print_r($pdo->errorInfo(), true));
        throw new Exception("Database prepare error");
    }
    
    $stmt->bindParam(':thesis_id', $thesis_id, PDO::PARAM_INT);
    if (!$stmt->execute()) {
        error_log("SQL execute error: " . print_r($stmt->errorInfo(), true));
        throw new Exception("Database execute error");
    }
    
    $thesis = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$thesis) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Η διπλωματική εργασία δεν βρέθηκε']);
        exit;
    }
    
    if ($thesis['supervisor_id'] != $professor_id || $thesis['status'] !== 'active') {
        http_response_code(403);
        echo json_encode([
            'success' => false, 
            'message' => 'Δεν έχετε δικαίωμα να αλλάξετε την κατάσταση αυτής της διπλωματικής ή η διπλωματική δεν είναι ενεργή'
        ]);
        exit;
    }
    
    $stmt = $pdo->prepare("UPDATE thesis_assignments SET status = 'examination' WHERE id = :thesis_id");
    if (!$stmt) {
        error_log("SQL prepare error (update): " . print_r($pdo->errorInfo(), true));
        throw new Exception("Database prepare error during update");
    }
    
    $stmt->bindParam(':thesis_id', $thesis_id, PDO::PARAM_INT);
    $result = $stmt->execute();
    
    if (!$result) {
        error_log("SQL execute error (update): " . print_r($stmt->errorInfo(), true));
        throw new Exception("Database execute error during update");
    }
    
    $old_status = 'active';
    $new_status = 'examination';
    $notes = "Η διπλωματική είναι έτοιμη για εξέταση";
    
    $current_user_id = User::getCurrentUserId();
    
    $stmt = $pdo->prepare("INSERT INTO status_changes (thesis_assignment_id, old_status, new_status, changed_at, changed_by, notes) VALUES (:thesis_id, :old_status, :new_status, NOW(), :changed_by, :notes)");
    if (!$stmt) {
        error_log("SQL prepare error (status_changes): " . print_r($pdo->errorInfo(), true));
        throw new Exception("Database prepare error during status change recording");
    }
    
    $stmt->bindParam(':thesis_id', $thesis_id, PDO::PARAM_INT);
    $stmt->bindParam(':old_status', $old_status, PDO::PARAM_STR);
    $stmt->bindParam(':new_status', $new_status, PDO::PARAM_STR);
    $stmt->bindParam(':changed_by', $current_user_id, PDO::PARAM_INT);
    $stmt->bindParam(':notes', $notes, PDO::PARAM_STR);
    
    if (!$stmt->execute()) {
        error_log("SQL execute error (status_changes): " . print_r($stmt->errorInfo(), true));
        throw new Exception("Database execute error during status change recording");
    }
    
    $response = [
        'success' => true, 
        'message' => 'Η κατάσταση της διπλωματικής άλλαξε επιτυχώς σε "Υπό Εξέταση"'
    ];
    echo json_encode($response);
    
} catch (PDOException $e) {
    http_response_code(500);
    $error_response = ['success' => false, 'message' => 'Σφάλμα βάσης δεδομένων: ' . $e->getMessage()];
    echo json_encode($error_response);
    exit;
} catch (Exception $e) {
    http_response_code(500);
    $error_response = ['success' => false, 'message' => 'Σφάλμα: ' . $e->getMessage()];
    echo json_encode($error_response);
    exit;
}
?>

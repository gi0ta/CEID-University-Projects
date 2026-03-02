<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Μη επιτρεπτή μέθοδος. Χρησιμοποιήστε POST.']);
    exit;
}

require_once '../../includes/config/config.php';
require_once '../../includes/config/database.php';
require_once '../../includes/init.php';

if (!User::isLoggedIn() || !User::hasRole('secretary')) {
    header('HTTP/1.1 403 Forbidden');
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Δεν έχετε δικαίωμα πρόσβασης σε αυτό το API.']);
    exit;
}

$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

if (!isset($data['thesis_id']) || !isset($data['gs_number']) || !isset($data['gs_year'])) {
    header('HTTP/1.1 400 Bad Request');
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Λείπουν απαραίτητα πεδία.']);
    exit;
}

$thesis_id = intval($data['thesis_id']);
$gs_number = trim($data['gs_number']);
$gs_year = trim($data['gs_year']);

if ($thesis_id <= 0) {
    header('HTTP/1.1 400 Bad Request');
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Μη έγκυρο ID διπλωματικής εργασίας.']);
    exit;
}

if (empty($gs_number)) {
    header('HTTP/1.1 400 Bad Request');
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Ο Αριθμός Πρωτοκόλλου ΓΣ δεν μπορεί να είναι κενός.']);
    exit;
}

if (empty($gs_year)) {
    header('HTTP/1.1 400 Bad Request');
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Το Έτος ΓΣ δεν μπορεί να είναι κενό.']);
    exit;
}

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    $stmt = $conn->prepare("
        SELECT id, status 
        FROM thesis_assignments 
        WHERE id = :thesis_id
    ");
    $stmt->bindParam(':thesis_id', $thesis_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $thesis = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$thesis) {
        header('HTTP/1.1 404 Not Found');
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Η διπλωματική εργασία δεν βρέθηκε.']);
        exit;
    }
    
    if ($thesis['status'] !== 'active') {
        header('HTTP/1.1 400 Bad Request');
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Μπορείτε να ενημερώσετε τα στοιχεία του ΑΠ της ΓΣ μόνο για ενεργές διπλωματικές εργασίες.']);
        exit;
    }
    
    $stmt = $conn->prepare("
        UPDATE thesis_assignments 
        SET gs_number = :gs_number, gs_year = :gs_year 
        WHERE id = :thesis_id
    ");
    $stmt->bindParam(':gs_number', $gs_number, PDO::PARAM_STR);
    $stmt->bindParam(':gs_year', $gs_year, PDO::PARAM_INT);
    $stmt->bindParam(':thesis_id', $thesis_id, PDO::PARAM_INT);
    $stmt->execute();
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Τα στοιχεία του ΑΠ της ΓΣ ενημερώθηκαν με επιτυχία.']);
    
} catch (PDOException $e) {
    header('HTTP/1.1 500 Internal Server Error');
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Προέκυψε σφάλμα κατά την ενημέρωση των στοιχείων: ' . $e->getMessage()]);
    exit;
}

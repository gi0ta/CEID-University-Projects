<?php

require_once '../../config/config.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';
require_once '../../includes/authentication.php';

if (!isSecretary()) {
    http_response_code(403);
    echo json_encode(['error' => 'Δεν έχετε δικαίωμα πρόσβασης σε αυτή τη λειτουργία.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Μη επιτρεπτή μέθοδος αιτήματος.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$thesis_id = isset($data['thesis_id']) ? (int)$data['thesis_id'] : 0;

if ($thesis_id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Μη έγκυρο ID διπλωματικής εργασίας.']);
    exit;
}

try {
    $stmt = $conn->prepare("
        SELECT ta.id, ta.status, ta.final_grade, ta.nemertis_url
        FROM thesis_assignments ta
        WHERE ta.id = :thesis_id AND ta.status = 'examination'
    ");
    $stmt->bindParam(':thesis_id', $thesis_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $thesis = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$thesis) {
        http_response_code(404);
        echo json_encode(['error' => 'Η διπλωματική εργασία δεν βρέθηκε ή δεν είναι σε κατάσταση εξέτασης.']);
        exit;
    }
    
    if (empty($thesis['final_grade'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Δεν έχει καταχωρηθεί βαθμός για τη διπλωματική εργασία.']);
        exit;
    }
    
    if (empty($thesis['nemertis_url'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Δεν έχει αναρτηθεί σύνδεσμος προς το Νημερτής από το φοιτητή.']);
        exit;
    }
    
    $stmt = $conn->prepare("
        UPDATE thesis_assignments 
        SET status = 'completed', completion_date = CURRENT_DATE() 
        WHERE id = :thesis_id
    ");
    $stmt->bindParam(':thesis_id', $thesis_id, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Η διπλωματική εργασία ολοκληρώθηκε επιτυχώς και χαρακτηρίστηκε ως περατωμένη.'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Προέκυψε σφάλμα κατά την ενημέρωση της κατάστασης.']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Προέκυψε σφάλμα κατά την επεξεργασία του αιτήματος: ' . $e->getMessage()]);
}
?>

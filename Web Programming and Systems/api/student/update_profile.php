<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../../includes/init.php';

$response = [
    'success' => false,
    'message' => '',
    'error' => ''
];

if (!User::isLoggedIn() || User::getCurrentUserRole() !== 'student') {
    $response['error'] = 'Δεν έχετε δικαίωμα πρόσβασης σε αυτή τη λειτουργία.';
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['error'] = 'Μη έγκυρη μέθοδος αιτήματος.';
    echo json_encode($response);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['email']) || !isset($data['street']) || !isset($data['street_number']) || 
    !isset($data['city']) || !isset($data['postcode']) || !isset($data['mobile_phone'])) {
    $response['error'] = 'Ελλιπή δεδομένα. Παρακαλώ συμπληρώστε όλα τα υποχρεωτικά πεδία.';
    echo json_encode($response);
    exit;
}

$email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
$street = trim($data['street']);
$street_number = trim($data['street_number']);
$city = trim($data['city']);
$postcode = trim($data['postcode']);
$mobile_phone = trim($data['mobile_phone']);
$home_phone = trim($data['home_phone'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $response['error'] = 'Μη έγκυρη διεύθυνση email.';
    echo json_encode($response);
    exit;
}

$phoneRegex = '/^[0-9]{10}$/';
if (!preg_match($phoneRegex, $mobile_phone)) {
    $response['error'] = 'Μη έγκυρο κινητό τηλέφωνο. Πρέπει να είναι 10 ψηφία.';
    echo json_encode($response);
    exit;
}

if ($home_phone !== '' && !preg_match($phoneRegex, $home_phone)) {
    $response['error'] = 'Μη έγκυρο σταθερό τηλέφωνο. Αν το συμπληρώσετε, πρέπει να είναι 10 ψηφία.';
    echo json_encode($response);
    exit;
}

try {
    $db = new Database();
    $pdo = $db->getConnection();
    
    $user_id = User::getCurrentUserId();
    
    $pdo->beginTransaction();
    
    $stmt = $pdo->prepare("UPDATE users SET email = :email WHERE id = :user_id");
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM students WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $exists = $stmt->fetchColumn() > 0;
    
    if ($exists) {
        $stmt = $pdo->prepare("
            UPDATE students 
            SET street = :street, street_number = :street_number, city = :city, postcode = :postcode,
                mobile_phone = :mobile_phone, home_phone = :home_phone 
            WHERE user_id = :user_id
        ");
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO students (user_id, street, street_number, city, postcode, mobile_phone, home_phone) 
            VALUES (:user_id, :street, :street_number, :city, :postcode, :mobile_phone, :home_phone)
        ");
    }
    
    $stmt->bindParam(':street', $street, PDO::PARAM_STR);
    $stmt->bindParam(':street_number', $street_number, PDO::PARAM_STR);
    $stmt->bindParam(':city', $city, PDO::PARAM_STR);
    $stmt->bindParam(':postcode', $postcode, PDO::PARAM_STR);
    $stmt->bindParam(':mobile_phone', $mobile_phone, PDO::PARAM_STR);
    $stmt->bindParam(':home_phone', $home_phone, PDO::PARAM_STR);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $pdo->commit();
    
    $response['success'] = true;
    $response['message'] = 'Τα στοιχεία σας ενημερώθηκαν με επιτυχία!';
    
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    error_log('Error updating student profile: ' . $e->getMessage());
    
    $response['error'] = 'Προέκυψε σφάλμα κατά την ενημέρωση των στοιχείων σας. Παρακαλώ δοκιμάστε ξανά αργότερα.';
}

header('Content-Type: application/json');
echo json_encode($response);

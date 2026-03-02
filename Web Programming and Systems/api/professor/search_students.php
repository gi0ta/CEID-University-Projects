<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../includes/init.php';


if (!isset($_GET['term']) || empty($_GET['term'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Δεν έχει οριστεί όρος αναζήτησης.'
    ]);
    exit();
}

$searchTerm = trim($_GET['term']);

if (strlen($searchTerm) < 3) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Ο όρος αναζήτησης πρέπει να είναι τουλάχιστον 3 χαρακτήρες.'
    ]);
    exit();
}

$db = new Database();
$pdo = $db->getConnection();

$searchTerm = "%$searchTerm%";
$stmt = $pdo->prepare("
    SELECT 
        s.id,
        s.registration_number,
        CONCAT(u.first_name, ' ', u.last_name) AS fullname,
        u.email
    FROM 
        students s
    JOIN 
        users u ON s.user_id = u.id
    LEFT JOIN
        thesis_assignments ta ON s.id = ta.student_id
    WHERE 
        ta.id IS NULL AND
        (
            s.registration_number LIKE ? OR
            u.first_name LIKE ? OR
            u.last_name LIKE ? OR
            CONCAT(u.first_name, ' ', u.last_name) LIKE ?
        )
    ORDER BY 
        u.last_name, u.first_name
    LIMIT 20
");
$stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'data' => $students
]);
exit();

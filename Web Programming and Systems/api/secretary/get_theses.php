<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../../includes/init.php';

$response = [
    'success' => false,
    'message' => '',
    'error' => '',
    'data' => [
        'theses' => [],
        'counts' => [
            'active' => 0,
            'examination' => 0,
            'total' => 0
        ]
    ]
];

if (!User::isLoggedIn() || User::getCurrentUserRole() !== 'secretary') {
    $response['error'] = 'Δεν έχετε δικαίωμα πρόσβασης σε αυτή τη λειτουργία.';
    echo json_encode($response);
    exit;
}

try {
    $db = new Database();
    $pdo = $db->getConnection();
    
    $status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
    
    $active_count_stmt = $pdo->prepare("SELECT COUNT(*) FROM thesis_assignments WHERE status = 'active'");
    $active_count_stmt->execute();
    $response['data']['counts']['active'] = (int)$active_count_stmt->fetchColumn();
    
    $exam_count_stmt = $pdo->prepare("SELECT COUNT(*) FROM thesis_assignments WHERE status = 'examination'");
    $exam_count_stmt->execute();
    $response['data']['counts']['examination'] = (int)$exam_count_stmt->fetchColumn();
    
    $response['data']['counts']['total'] = $response['data']['counts']['active'] + $response['data']['counts']['examination'];
    
    $sql = "SELECT 
                ta.id,
                tt.title,
                ta.status,
                ta.assignment_date,
                CONCAT(su.first_name, ' ', su.last_name) as student_name,
                s.registration_number as student_reg_number,
                CONCAT(pu.first_name, ' ', pu.last_name) as supervisor_name,
                tt.summary,
                DATEDIFF(CURRENT_DATE, ta.assignment_date) as days_since_assignment
            FROM 
                thesis_assignments ta
            JOIN 
                thesis_topics tt ON ta.thesis_topic_id = tt.id
            JOIN 
                students s ON ta.student_id = s.id
            JOIN 
                users su ON s.user_id = su.id
            JOIN 
                professors p ON ta.supervisor_id = p.id
            JOIN 
                users pu ON p.user_id = pu.id
            WHERE 
                ta.status IN ('active', 'examination')";
    
    $params = [];
    if ($status_filter !== 'all') {
        $sql .= " AND ta.status = :status";
        $params[':status'] = $status_filter;
    }
    
    $sql .= " ORDER BY ta.assignment_date DESC";
    
    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    
    $theses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($theses as &$thesis) {
        $committee_sql = "SELECT 
                            cm.id, 
                            cm.status as member_status,
                            CONCAT(u.first_name, ' ', u.last_name) as professor_name
                          FROM 
                            committee_members cm
                          JOIN 
                            professors p ON cm.professor_id = p.id
                          JOIN 
                            users u ON p.user_id = u.id
                          WHERE 
                            cm.thesis_assignment_id = ?";
        $committee_stmt = $pdo->prepare($committee_sql);
        $committee_stmt->bindParam(1, $thesis['id'], PDO::PARAM_INT);
        $committee_stmt->execute();
        $thesis['committee_members'] = $committee_stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    $response['data']['theses'] = $theses;
    $response['success'] = true;
    
} catch (PDOException $e) {
    error_log('Error fetching theses: ' . $e->getMessage());
    
    $response['error'] = 'Προέκυψε σφάλμα κατά την ανάκτηση των διπλωματικών εργασιών.';
}

header('Content-Type: application/json');
echo json_encode($response);

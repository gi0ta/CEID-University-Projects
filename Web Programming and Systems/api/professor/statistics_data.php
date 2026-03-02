<?php

require_once '../../includes/init.php';

if (!User::isLoggedIn() || User::getCurrentUserRole() !== 'professor') {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Μη εξουσιοδοτημένη πρόσβαση'
    ]);
    exit;
}

$userId = User::getCurrentUserId();
$professorId = null;

try {
    $db = getDbConnection();
    $stmt = $db->prepare("SELECT id FROM professors WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        $professorId = $result['id'];
    } else {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Δεν βρέθηκε ο διδάσκων'
        ]);
        exit;
    }
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Σφάλμα βάσης δεδομένων: ' . $e->getMessage()
    ]);
    exit;
}

$statistics = [
    'supervised' => [
        'count' => 0,
        'avg_duration' => 0,
        'avg_grade' => 0
    ],
    'committee_member' => [
        'count' => 0,
        'avg_duration' => 0,
        'avg_grade' => 0
    ]
];

try {
    $stmt = $db->prepare("
        SELECT 
            COUNT(*) as total_count,
            AVG(DATEDIFF(ta.completion_date, ta.assignment_date)) as avg_duration,
            AVG(ta.final_grade) as avg_grade
        FROM 
            thesis_assignments ta
        JOIN 
            thesis_topics tt ON ta.thesis_topic_id = tt.id
        WHERE 
            tt.professor_id = :professor_id
            AND ta.status = 'completed'
            AND ta.completion_date IS NOT NULL
            AND ta.final_grade IS NOT NULL
    ");
    $stmt->bindParam(':professor_id', $professorId, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        $statistics['supervised']['count'] = (int)$result['total_count'];
        $statistics['supervised']['avg_duration'] = $result['avg_duration'] ? round($result['avg_duration'] / 30, 1) : 0;
        $statistics['supervised']['avg_grade'] = $result['avg_grade'] ? round($result['avg_grade'], 1) : 0;
    }
    
    $stmt = $db->prepare("
        SELECT 
            COUNT(DISTINCT ta.id) as total_count,
            AVG(DATEDIFF(ta.completion_date, ta.assignment_date)) as avg_duration,
            AVG(ta.final_grade) as avg_grade
        FROM 
            thesis_assignments ta
        JOIN 
            committee_members cm ON ta.id = cm.thesis_assignment_id
        WHERE 
            cm.professor_id = :professor_id
            AND cm.status = 'accepted'
            AND ta.status = 'completed'
            AND ta.completion_date IS NOT NULL
            AND ta.final_grade IS NOT NULL
    ");
    $stmt->bindParam(':professor_id', $professorId, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        $statistics['committee_member']['count'] = (int)$result['total_count'];
        $statistics['committee_member']['avg_duration'] = $result['avg_duration'] ? round($result['avg_duration'] / 30, 1) : 0;
        $statistics['committee_member']['avg_grade'] = $result['avg_grade'] ? round($result['avg_grade'], 1) : 0;
    }
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'data' => $statistics
    ]);
    
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Σφάλμα βάσης δεδομένων: ' . $e->getMessage()
    ]);
    exit;
}
?>

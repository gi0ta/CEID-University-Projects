<?php

require_once '../../includes/init.php';

if (!User::isLoggedIn() || User::getCurrentUserRole() !== 'professor') {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Δεν έχετε πρόσβαση σε αυτό το API endpoint'
    ]);
    exit;
}

$userId = User::getCurrentUserId();
$db = new Database();
$pdo = $db->getConnection();

$userId = User::getCurrentUserId();

$stmt = $pdo->prepare("SELECT id FROM professors WHERE user_id = :user_id");
$stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
$stmt->execute();
$professor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$professor) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Δεν βρέθηκε ο καθηγητής'
    ]);
    exit;
}

$professorId = $professor['id'];

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        getThesisTopics($pdo, $professorId);
        break;
    case 'POST':
        $request_data = json_decode(file_get_contents('php://input'), true);

        if (empty($request_data)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Δεν παρέχονται δεδομένα.']);
            exit;
        }

        if (isset($request_data['topic_id']) && !empty($request_data['topic_id'])) {
            updateThesisTopic($pdo, $professorId, $request_data);
        } else {
            createThesisTopic($pdo, $professorId, $request_data);
        }
        break;
    case 'DELETE':
        deleteThesisTopic($pdo, $professorId);
        break;
    default:
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Μη υποστηριζόμενη μέθοδος'
        ]);
        exit;
}

function getThesisTopics($db, $professorId) {
    try {
        $stmt = $db->prepare("
            SELECT id, title, summary, description_file, created_at, updated_at, is_assigned
            FROM thesis_topics 
            WHERE professor_id = :professor_id AND is_assigned = 0
            ORDER BY created_at DESC
        ");
        $stmt->bindParam(':professor_id', $professorId, PDO::PARAM_INT);
        $stmt->execute();
        $topics = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'data' => $topics
        ]);
        exit;
    } catch (PDOException $e) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Σφάλμα κατά την ανάκτηση των θεμάτων: ' . $e->getMessage()
        ]);
        exit;
    }
}

function createThesisTopic($db, $professorId, $request_data) {
    $raw_input = file_get_contents('php://input');
    error_log("Received data: " . $raw_input);
    
    $data = json_decode($raw_input, true);
    
    if (!isset($request_data['title']) || empty(trim($request_data['title']))) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Ο τίτλος είναι απαραίτητος']);
        exit;
    }
    $title = trim($request_data['title']);

    if (!isset($request_data['summary']) || empty(trim($request_data['summary']))) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Η σύνοψη είναι απαραίτητη']);
        exit;
    }
    $summary = trim($request_data['summary']);

    $descriptionFile = isset($request_data['description_file']) && !empty($request_data['description_file']) ? $request_data['description_file'] : null;

    try {
        $stmt_check_title = $db->prepare("SELECT COUNT(*) FROM thesis_topics WHERE title = :title AND professor_id = :professor_id");
        $stmt_check_title->bindParam(':title', $title, PDO::PARAM_STR);
        $stmt_check_title->bindParam(':professor_id', $professorId, PDO::PARAM_INT);
        $stmt_check_title->execute();
        $count = $stmt_check_title->fetchColumn();

        if ($count > 0) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Ένα θέμα με αυτόν τον τίτλο υπάρχει ήδη για εσάς.']);
            exit;
        }

        $stmt_insert = $db->prepare("
            INSERT INTO thesis_topics (professor_id, title, summary, description_file, created_at, updated_at, is_assigned)
            VALUES (:professor_id, :title, :summary, :description_file, NOW(), NOW(), 0)
        ");
        
        $stmt_insert->bindParam(':professor_id', $professorId, PDO::PARAM_INT);
        $stmt_insert->bindParam(':title', $title, PDO::PARAM_STR);
        $stmt_insert->bindParam(':summary', $summary, PDO::PARAM_STR);
        $stmt_insert->bindParam(':description_file', $descriptionFile, PDO::PARAM_STR);
        
        if (!$stmt_insert->execute()) {
            $errorInfo = $stmt_insert->errorInfo();
            throw new PDOException("Query execution failed: " . $errorInfo[2]);
        }
        
        $topicId = $db->lastInsertId();
        
        $stmt_fetch = $db->prepare("SELECT id, title, summary, description_file, created_at, updated_at, is_assigned FROM thesis_topics WHERE id = :id");
        $stmt_fetch->bindParam(':id', $topicId, PDO::PARAM_INT);
        $stmt_fetch->execute();
        $topic = $stmt_fetch->fetch(PDO::FETCH_ASSOC);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Το θέμα δημιουργήθηκε με επιτυχία',
            'data' => $topic
        ]);
        exit;
    } catch (PDOException $e) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Σφάλμα κατά τη δημιουργία του θέματος: ' . $e->getMessage()
        ]);
        exit;
    }
}

function updateThesisTopic($db, $professorId, $request_data) {
    if (!isset($request_data['topic_id']) || empty($request_data['topic_id'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Το ID του θέματος είναι απαραίτητο για την ενημέρωση.']);
        exit;
    }
    $topicId = filter_var($request_data['topic_id'], FILTER_VALIDATE_INT);
    if ($topicId === false) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Μη έγκυρο ID θέματος.']);
        exit;
    }

    if (!isset($request_data['title']) || trim($request_data['title']) === '') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Ο τίτλος είναι απαραίτητος.']);
        exit;
    }
    $title = trim($request_data['title']);

    if (!isset($request_data['summary']) || trim($request_data['summary']) === '') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Η σύνοψη είναι απαραίτητη.']);
        exit;
    }
    $summary = trim($request_data['summary']);

    $descriptionFile = isset($request_data['description_file']) ? $request_data['description_file'] : null; 

    try {
        $stmt_check = $db->prepare("SELECT id, is_assigned FROM thesis_topics WHERE id = :id AND professor_id = :professor_id");
        $stmt_check->bindParam(':id', $topicId, PDO::PARAM_INT);
        $stmt_check->bindParam(':professor_id', $professorId, PDO::PARAM_INT);
        $stmt_check->execute();
        $existing_topic = $stmt_check->fetch(PDO::FETCH_ASSOC);

        if (!$existing_topic) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Το θέμα δεν βρέθηκε ή δεν έχετε δικαίωμα να το τροποποιήσετε.']);
            exit;
        }

        if ($existing_topic['is_assigned'] == 1) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Το θέμα έχει ήδη ανατεθεί και δεν μπορεί να τροποποιηθεί.']);
            exit;
        }
        
        $stmt_title_check = $db->prepare("SELECT COUNT(*) FROM thesis_topics WHERE title = :title AND professor_id = :professor_id AND id != :current_topic_id");
        $stmt_title_check->bindParam(':title', $title, PDO::PARAM_STR);
        $stmt_title_check->bindParam(':professor_id', $professorId, PDO::PARAM_INT);
        $stmt_title_check->bindParam(':current_topic_id', $topicId, PDO::PARAM_INT);
        $stmt_title_check->execute();
        if ($stmt_title_check->fetchColumn() > 0) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Ένα άλλο θέμα με αυτόν τον τίτλο υπάρχει ήδη για εσάς.']);
            exit;
        }

        $sql_parts = ["title = :title", "summary = :summary", "updated_at = NOW()"];
        $params = [
            ':title' => $title,
            ':summary' => $summary,
            ':id' => $topicId,
            ':professor_id' => $professorId
        ];

        if (array_key_exists('description_file', $request_data)) {
            $sql_parts[] = "description_file = :description_file";
            $params[':description_file'] = $request_data['description_file'];
        }

        $sql = "UPDATE thesis_topics SET " . implode(", ", $sql_parts) . " WHERE id = :id AND professor_id = :professor_id";
        
        $stmt_update = $db->prepare($sql);
        $stmt_update->execute($params);

        $stmt_fetch = $db->prepare("SELECT id, title, summary, description_file, created_at, updated_at, is_assigned FROM thesis_topics WHERE id = :id");
        $stmt_fetch->bindParam(':id', $topicId, PDO::PARAM_INT);
        $stmt_fetch->execute();
        $updated_topic = $stmt_fetch->fetch(PDO::FETCH_ASSOC);

        if ($updated_topic) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Το θέμα ενημερώθηκε με επιτυχία.', 'data' => $updated_topic]);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Σφάλμα κατά την ανάκτηση του ενημερωμένου θέματος.']);
        }
        exit;

    } catch (PDOException $e) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Σφάλμα κατά την ενημέρωση του θέματος: ' . $e->getMessage()]);
        exit;
    }
}

function deleteThesisTopic($db, $professorId) {
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Δεν έχει οριστεί το ID του θέματος'
        ]);
        exit;
    }
    
    $topicId = intval($_GET['id']);
    
    try {
        $stmt = $db->prepare("
            SELECT id, is_assigned 
            FROM thesis_topics 
            WHERE id = :id AND professor_id = :professor_id
        ");
        $stmt->bindParam(':id', $topicId, PDO::PARAM_INT);
        $stmt->bindParam(':professor_id', $professorId, PDO::PARAM_INT);
        $stmt->execute();
        $topic = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$topic) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Το θέμα δεν βρέθηκε ή δεν έχετε δικαίωμα να το διαγράψετε'
            ]);
            exit;
        }
        
        if ($topic['is_assigned']) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Το θέμα έχει ήδη ανατεθεί και δεν μπορεί να διαγραφεί'
            ]);
            exit;
        }
        
        $stmt = $db->prepare("DELETE FROM thesis_topics WHERE id = :id");
        $stmt->bindParam(':id', $topicId, PDO::PARAM_INT);
        $stmt->execute();
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Το θέμα διαγράφηκε με επιτυχία'
        ]);
        exit;
    } catch (PDOException $e) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Σφάλμα κατά τη διαγραφή του θέματος: ' . $e->getMessage()
        ]);
        exit;
    }
}

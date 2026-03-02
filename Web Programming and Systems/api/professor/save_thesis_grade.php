<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);

function handleError($errno, $errstr, $errfile, $errline) {
    global $response;
    $response['success'] = false;
    $response['message'] = 'Σφάλμα συστήματος: ' . $errstr;
    echo json_encode($response);
    exit;
}

function handleException($exception) {
    global $response;
    $response['success'] = false;
    $response['message'] = 'Σφάλμα εξαίρεσης: ' . $exception->getMessage();
    echo json_encode($response);
    exit;
}

set_error_handler('handleError');
set_exception_handler('handleException');

header('Content-Type: application/json; charset=UTF-8');


require_once '../../includes/config/config.php';
require_once '../../includes/config/database.php';
require_once '../../includes/classes/User.php';
require_once '../../includes/functions/auth_functions.php';

startSecureSession();

$response = [
    'success' => false,
    'message' => 'Σφάλμα κατά την αποθήκευση της βαθμολογίας.'
];

if (!User::isLoggedIn() || User::getCurrentUserRole() !== 'professor') {
    $response['message'] = 'Δεν έχετε δικαίωμα πρόσβασης.';
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Μη έγκυρη μέθοδος αιτήματος.';
    echo json_encode($response);
    exit;
}

$thesis_id = isset($_POST['thesis_id']) ? intval($_POST['thesis_id']) : 0;
$professor_id = isset($_POST['professor_id']) ? intval($_POST['professor_id']) : 0;
$final_grade = isset($_POST['final_grade']) ? floatval($_POST['final_grade']) : null;

$thesis_assignment_id = $thesis_id;


$subject_understanding = isset($_POST['quality_score']) ? floatval($_POST['quality_score']) : null;
$problem_solving = isset($_POST['time_score']) ? floatval($_POST['time_score']) : null;
$writing_quality = isset($_POST['document_score']) ? floatval($_POST['document_score']) : null;
$presentation = isset($_POST['presentation_score']) ? floatval($_POST['presentation_score']) : null;

$calculated_final_grade = ($subject_understanding * 0.6) + ($problem_solving * 0.15) + ($writing_quality * 0.15) + ($presentation * 0.1);
$calculated_final_grade = round($calculated_final_grade, 1);

if (abs($calculated_final_grade - $final_grade) > 0.1) {
    $response['message'] = 'Σφάλμα στον υπολογισμό του τελικού βαθμού. Παρακαλώ δοκιμάστε ξανά.';
    echo json_encode($response);
    exit;
}

$grade = $calculated_final_grade;

if ($thesis_id === 0 || $grade === null || $grade < 0 || $grade > 10) {
    $response['message'] = 'Παρακαλώ συμπληρώστε όλα τα υποχρεωτικά πεδία με έγκυρες τιμές.';
    echo json_encode($response);
    exit;
}

$db = getDbConnection();

$user_id = User::getCurrentUserId();

$professor_sql = "SELECT id FROM professors WHERE user_id = ?";
$professor_stmt = $db->prepare($professor_sql);
if (!$professor_stmt) {
    $response['message'] = 'Σφάλμα προετοιμασίας ερωτήματος ανάκτησης καθηγητή.';
    echo json_encode($response);
    exit;
}
$professor_stmt->bindParam(1, $user_id, PDO::PARAM_INT);
if (!$professor_stmt->execute()) {
    $response['message'] = 'Σφάλμα εκτέλεσης ερωτήματος ανάκτησης καθηγητή.';
    echo json_encode($response);
    exit;
}
$professor = $professor_stmt->fetch(PDO::FETCH_ASSOC);
if (!$professor) {
    $response['message'] = 'Δεν βρέθηκε ο καθηγητής.';
    echo json_encode($response);
    exit;
}
$professorId = $professor['id'];

error_log("User ID: $user_id, Professor ID: $professorId");

$check_sql = "
    SELECT ta.id, ta.supervisor_id, ta.status, ta.grading_enabled 
    FROM thesis_assignments ta
    WHERE ta.id = ? AND ta.status = 'examination'
    AND (ta.supervisor_id = ? OR EXISTS (
        SELECT 1 FROM committee_members cm 
        WHERE cm.thesis_assignment_id = ta.id AND cm.professor_id = ? AND cm.status = 'accepted'
    ))
";
$check_stmt = $db->prepare($check_sql);
if (!$check_stmt) {
    $response['message'] = 'Σφάλμα προετοιμασίας ερωτήματος ελέγχου δικαιωμάτων.';
    echo json_encode($response);
    exit;
}
$check_stmt->bindParam(1, $thesis_assignment_id, PDO::PARAM_INT);
$check_stmt->bindParam(2, $professorId, PDO::PARAM_INT);
$check_stmt->bindParam(3, $professorId, PDO::PARAM_INT);
if (!$check_stmt->execute()) {
    $response['message'] = 'Σφάλμα εκτέλεσης ερωτήματος ελέγχου δικαιωμάτων.';
    echo json_encode($response);
    exit;
}
$thesis = $check_stmt->fetch(PDO::FETCH_ASSOC);

if (!$thesis) {
    $response['message'] = 'Δεν βρέθηκε η διπλωματική εργασία ή δεν έχετε δικαίωμα βαθμολόγησης.';
    echo json_encode($response);
    exit;
}

if (!isset($thesis['grading_enabled']) || $thesis['grading_enabled'] != 1) {
    $response['message'] = 'Η βαθμολόγηση δεν έχει ενεργοποιηθεί για αυτή τη διπλωματική εργασία.';
    echo json_encode($response);
    exit;
}

$db->beginTransaction();

try {
    $check_grades_sql = "SELECT id FROM thesis_grades WHERE thesis_assignment_id = ? AND professor_id = ?";
    $check_grades_stmt = $db->prepare($check_grades_sql);
    
    error_log("Checking if professor $professorId has already graded thesis $thesis_assignment_id");
    if (!$check_grades_stmt) {
        throw new Exception('Σφάλμα προετοιμασίας ερωτήματος ελέγχου βαθμολογίας.');
    }
    $check_grades_stmt->bindParam(1, $thesis_assignment_id, PDO::PARAM_INT);
    $check_grades_stmt->bindParam(2, $professorId, PDO::PARAM_INT);
    if (!$check_grades_stmt->execute()) {
        throw new Exception('Σφάλμα εκτέλεσης ερωτήματος ελέγχου βαθμολογίας.');
    }
    $existing_grade = $check_grades_stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existing_grade) {
        $update_grades_sql = "
            UPDATE thesis_grades 
            SET subject_understanding = ?, problem_solving = ?, writing_quality = ?, presentation = ?, final_grade = ?, grading_date = NOW() 
            WHERE thesis_assignment_id = ? AND professor_id = ?
        ";
        $update_grades_stmt = $db->prepare($update_grades_sql);
        if (!$update_grades_stmt) {
            throw new Exception('Σφάλμα προετοιμασίας ερωτήματος ενημέρωσης βαθμολογίας.');
        }
        $update_grades_stmt->bindParam(1, $subject_understanding, PDO::PARAM_STR);
        $update_grades_stmt->bindParam(2, $problem_solving, PDO::PARAM_STR);
        $update_grades_stmt->bindParam(3, $writing_quality, PDO::PARAM_STR);
        $update_grades_stmt->bindParam(4, $presentation, PDO::PARAM_STR);
        $update_grades_stmt->bindParam(5, $grade, PDO::PARAM_STR);
        $update_grades_stmt->bindParam(6, $thesis_assignment_id, PDO::PARAM_INT);
        $update_grades_stmt->bindParam(7, $professorId, PDO::PARAM_INT);
        if (!$update_grades_stmt->execute()) {
            throw new Exception('Σφάλμα εκτέλεσης ερωτήματος ενημέρωσης βαθμολογίας.');
        }
    } else {
        $insert_grades_sql = "
            INSERT INTO thesis_grades (thesis_assignment_id, professor_id, subject_understanding, problem_solving, writing_quality, presentation, final_grade, grading_date) 
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ";
        $insert_grades_stmt = $db->prepare($insert_grades_sql);
        if (!$insert_grades_stmt) {
            throw new Exception('Σφάλμα προετοιμασίας ερωτήματος εισαγωγής βαθμολογίας.');
        }
        $insert_grades_stmt->bindParam(1, $thesis_assignment_id, PDO::PARAM_INT);
        $insert_grades_stmt->bindParam(2, $professorId, PDO::PARAM_INT);
        $insert_grades_stmt->bindParam(3, $subject_understanding, PDO::PARAM_STR);
        $insert_grades_stmt->bindParam(4, $problem_solving, PDO::PARAM_STR);
        $insert_grades_stmt->bindParam(5, $writing_quality, PDO::PARAM_STR);
        $insert_grades_stmt->bindParam(6, $presentation, PDO::PARAM_STR);
        $insert_grades_stmt->bindParam(7, $grade, PDO::PARAM_STR);
        if (!$insert_grades_stmt->execute()) {
            throw new Exception('Σφάλμα εκτέλεσης ερωτήματος εισαγωγής βαθμολογίας.');
        }
    }
    
    $avg_grade_sql = "
        SELECT AVG(final_grade) as avg_grade
        FROM thesis_grades
        WHERE thesis_assignment_id = ?
    ";
    $avg_grade_stmt = $db->prepare($avg_grade_sql);
    if (!$avg_grade_stmt) {
        throw new Exception('Σφάλμα προετοιμασίας ερωτήματος υπολογισμού μέσου όρου.');
    }
    $avg_grade_stmt->bindParam(1, $thesis_assignment_id, PDO::PARAM_INT);
    if (!$avg_grade_stmt->execute()) {
        throw new Exception('Σφάλμα εκτέλεσης ερωτήματος υπολογισμού μέσου όρου.');
    }
    $avg_grade_result = $avg_grade_stmt->fetch(PDO::FETCH_ASSOC);
    $avg_grade = round(floatval($avg_grade_result['avg_grade']), 1);
    
    $committee_count_sql = "
        SELECT COUNT(*) as total_members
        FROM committee_members
        WHERE thesis_assignment_id = ? AND status = 'accepted'
    ";
    $committee_count_stmt = $db->prepare($committee_count_sql);
    if (!$committee_count_stmt) {
        throw new Exception('Σφάλμα προετοιμασίας ερωτήματος μέτρησης μελών επιτροπής.');
    }
    $committee_count_stmt->bindParam(1, $thesis_assignment_id, PDO::PARAM_INT);
    if (!$committee_count_stmt->execute()) {
        throw new Exception('Σφάλμα εκτέλεσης ερωτήματος μέτρησης μελών επιτροπής.');
    }
    $committee_count_result = $committee_count_stmt->fetch(PDO::FETCH_ASSOC);
    $total_committee_members = intval($committee_count_result['total_members']) + 1;
    
    $graded_count_sql = "
        SELECT COUNT(*) as graded_members
        FROM thesis_grades
        WHERE thesis_assignment_id = ?
    ";
    $graded_count_stmt = $db->prepare($graded_count_sql);
    if (!$graded_count_stmt) {
        throw new Exception('Σφάλμα προετοιμασίας ερωτήματος μέτρησης βαθμολογημένων μελών.');
    }
    $graded_count_stmt->bindParam(1, $thesis_assignment_id, PDO::PARAM_INT);
    if (!$graded_count_stmt->execute()) {
        throw new Exception('Σφάλμα εκτέλεσης ερωτήματος μέτρησης βαθμολογημένων μελών.');
    }
    $graded_count_result = $graded_count_stmt->fetch(PDO::FETCH_ASSOC);
    $graded_members = intval($graded_count_result['graded_members']);
    
    if ($graded_members >= $total_committee_members) {
        $update_sql = "
            UPDATE thesis_assignments 
            SET final_grade = ? 
            WHERE id = ?
        ";
        $update_stmt = $db->prepare($update_sql);
        if (!$update_stmt) {
            throw new Exception('Σφάλμα προετοιμασίας ερωτήματος ενημέρωσης τελικού βαθμού.');
        }
        $update_stmt->bindParam(1, $avg_grade, PDO::PARAM_STR);
        $update_stmt->bindParam(2, $thesis_assignment_id, PDO::PARAM_INT);
        if (!$update_stmt->execute()) {
            throw new Exception('Σφάλμα εκτέλεσης ερωτήματος ενημέρωσης τελικού βαθμού.');
        }
    }
    

    
    $db->commit();
    
    $response['success'] = true;
    $response['message'] = 'Η βαθμολογία αποθηκεύτηκε με επιτυχία.';
    
} catch (Exception $e) {
    $db->rollBack();
    
    $response['message'] = 'Σφάλμα κατά την αποθήκευση της βαθμολογίας: ' . $e->getMessage();
}

echo json_encode($response);
exit;

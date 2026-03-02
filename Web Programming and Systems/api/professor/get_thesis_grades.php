<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../../includes/config/config.php';
require_once '../../includes/config/database.php';
require_once '../../includes/classes/User.php';
require_once '../../includes/functions/auth_functions.php';

startSecureSession();

if (!User::isLoggedIn() || User::getCurrentUserRole() !== 'professor') {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Δεν έχετε δικαίωμα πρόσβασης σε αυτό το API.'
    ]);
    exit;
}

if (!isset($_GET['thesis_id']) || !is_numeric($_GET['thesis_id'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Δεν έχει οριστεί έγκυρο ID διπλωματικής εργασίας.'
    ]);
    exit;
}

$thesis_assignment_id = intval($_GET['thesis_id']);

$db = getDbConnection();

$user_id = User::getCurrentUserId();
$professor_sql = "SELECT id FROM professors WHERE user_id = ?";
$professor_stmt = $db->prepare($professor_sql);
$professor_stmt->bindParam(1, $user_id, PDO::PARAM_INT);
$professor_stmt->execute();
$professor = $professor_stmt->fetch(PDO::FETCH_ASSOC);

if (!$professor) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Δεν βρέθηκε ο καθηγητής.'
    ]);
    exit;
}

$professor_id = $professor['id'];

$access_sql = "
    SELECT 1 
    FROM thesis_assignments ta
    WHERE ta.id = ? AND (
        ta.supervisor_id = ? OR 
        EXISTS (
            SELECT 1 
            FROM committee_members cm 
            WHERE cm.thesis_assignment_id = ta.id 
            AND cm.professor_id = ?
            AND cm.status = 'accepted'
        )
    )
";

$access_stmt = $db->prepare($access_sql);
$access_stmt->bindParam(1, $thesis_assignment_id, PDO::PARAM_INT);
$access_stmt->bindParam(2, $professor_id, PDO::PARAM_INT);
$access_stmt->bindParam(3, $professor_id, PDO::PARAM_INT);
$access_stmt->execute();

if (!$access_stmt->fetch()) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Δεν έχετε δικαίωμα πρόσβασης στις βαθμολογίες αυτής της διπλωματικής εργασίας.'
    ]);
    exit;
}

$grades_sql = "
    SELECT 
        tg.id,
        tg.professor_id,
        CONCAT(u.first_name, ' ', u.last_name) AS professor_name,
        tg.subject_understanding,
        tg.problem_solving,
        tg.writing_quality,
        tg.presentation,
        tg.final_grade,
        tg.grading_date,
        tg.comments,
        CASE 
            WHEN p.id = ta.supervisor_id THEN 'supervisor'
            ELSE 'committee_member'
        END AS role
    FROM 
        thesis_grades tg
    JOIN 
        professors p ON tg.professor_id = p.id
    JOIN 
        users u ON p.user_id = u.id
    JOIN
        thesis_assignments ta ON tg.thesis_assignment_id = ta.id
    WHERE 
        tg.thesis_assignment_id = ?
    ORDER BY
        tg.grading_date DESC
";

$grades_stmt = $db->prepare($grades_sql);
$grades_stmt->bindParam(1, $thesis_assignment_id, PDO::PARAM_INT);
$grades_stmt->execute();
$grades = $grades_stmt->fetchAll(PDO::FETCH_ASSOC);

$final_grade_sql = "
    SELECT final_grade, supervisor_id, grading_enabled, status 
    FROM thesis_assignments 
    WHERE id = ?
";
$final_grade_stmt = $db->prepare($final_grade_sql);
$final_grade_stmt->bindParam(1, $thesis_assignment_id, PDO::PARAM_INT);
$final_grade_stmt->execute();
$thesis_info = $final_grade_stmt->fetch(PDO::FETCH_ASSOC);
$final_grade = $thesis_info ? $thesis_info['final_grade'] : null;

$committee_sql = "
    SELECT 
        COUNT(cm.professor_id) AS total_members
    FROM 
        committee_members cm
    WHERE 
        cm.thesis_assignment_id = ? 
        AND cm.status = 'accepted'
";
$committee_stmt = $db->prepare($committee_sql);
$committee_stmt->bindParam(1, $thesis_assignment_id, PDO::PARAM_INT);
$committee_stmt->execute();
$committee_info = $committee_stmt->fetch(PDO::FETCH_ASSOC);

$graded_members_sql = "
    SELECT COUNT(DISTINCT tg.professor_id) AS graded_members
    FROM thesis_grades tg
    WHERE tg.thesis_assignment_id = ?
";
$graded_members_stmt = $db->prepare($graded_members_sql);
$graded_members_stmt->bindParam(1, $thesis_assignment_id, PDO::PARAM_INT);
$graded_members_stmt->execute();
$graded_info = $graded_members_stmt->fetch(PDO::FETCH_ASSOC);

$supervisor_graded = false;
foreach ($grades as $grade) {
    if ($grade['professor_id'] == $thesis_info['supervisor_id']) {
        $supervisor_graded = true;
        break;
    }
}

$total_required = $committee_info['total_members'] + 1;
$total_graded = $graded_info['graded_members'];

$is_supervisor = ($professor_id == $thesis_info['supervisor_id']);

$has_graded = false;
foreach ($grades as $grade) {
    if ($grade['professor_id'] == $professor_id) {
        $has_graded = true;
        break;
    }
}

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'grades' => $grades,
    'final_grade' => $final_grade,
    'grading_status' => [
        'total_required' => $total_required,
        'total_graded' => $total_graded,
        'all_graded' => ($total_graded >= $total_required),
        'debug_info' => [
            'committee_members' => $committee_info['total_members'],
            'graded_members' => $graded_info['graded_members'],
            'supervisor_graded' => $supervisor_graded
        ],
        'is_supervisor' => $is_supervisor,
        'has_graded' => $has_graded,
        'grading_enabled' => (bool)$thesis_info['grading_enabled'],
        'thesis_status' => $thesis_info['status']
    ]
]);
exit;

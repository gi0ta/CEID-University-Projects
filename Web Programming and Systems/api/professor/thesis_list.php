<?php

function jsonErrorHandler($errno, $errstr, $errfile, $errline) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'PHP Error: ' . $errstr,
        'error_details' => [
            'file' => $errfile,
            'line' => $errline,
            'type' => $errno
        ]
    ]);
    exit;
}
set_error_handler('jsonErrorHandler');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {

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

function tableExists($pdo, $tableName) {
    try {
        $result = $pdo->query("SHOW TABLES LIKE '$tableName'")->rowCount() > 0;
        return $result;
    } catch (PDOException $e) {
        return false;
    }
}

function columnExists($pdo, $tableName, $columnName) {
    try {
        $result = $pdo->query("SHOW COLUMNS FROM `$tableName` LIKE '$columnName'")->rowCount() > 0;
        return $result;
    } catch (PDOException $e) {
        return false;
    }
}

$tables = [
    'thesis_assignments',
    'thesis_topics',
    'students',
    'users',
    'professors',
    'committee_members',
    'thesis_files',
    'examination_protocols',
    'status_changes'
];

$missingTables = [];
foreach ($tables as $table) {
    if (!tableExists($pdo, $table)) {
        $missingTables[] = $table;
    }
}

$missingColumns = [];

if (tableExists($pdo, 'thesis_files')) {
    if (!columnExists($pdo, 'thesis_files', 'file_path')) {
        $missingColumns[] = 'thesis_files.file_path';
    }
    if (!columnExists($pdo, 'thesis_files', 'thesis_assignment_id')) {
        $missingColumns[] = 'thesis_files.thesis_assignment_id';
    }
    if (!columnExists($pdo, 'thesis_files', 'file_type')) {
        $missingColumns[] = 'thesis_files.file_type';
    }
}

if (tableExists($pdo, 'examination_protocols')) {
    if (!columnExists($pdo, 'examination_protocols', 'protocol_pdf')) {
        $missingColumns[] = 'examination_protocols.protocol_pdf';
    }
    if (!columnExists($pdo, 'examination_protocols', 'thesis_assignment_id')) {
        $missingColumns[] = 'examination_protocols.thesis_assignment_id';
    }
}

if (!empty($missingTables) || !empty($missingColumns)) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Πρόβλημα με τη δομή της βάσης δεδομένων',
        'missing_tables' => $missingTables,
        'missing_columns' => $missingColumns
    ]);
    exit;
}

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

$status = isset($_GET['status']) ? $_GET['status'] : null;
$role = isset($_GET['role']) ? $_GET['role'] : null;
$format = isset($_GET['format']) ? $_GET['format'] : null;

$sql = "
    SELECT 
        ta.id AS assignment_id,
        tt.title AS thesis_title,
        tt.summary AS thesis_summary,
        ta.status,
        ta.supervisor_id,
        CASE 
            WHEN ta.supervisor_id = ? THEN 'supervisor'
            ELSE 'committee_member'
        END AS professor_role,
        s.registration_number AS student_am,
        CONCAT(u.first_name, ' ', u.last_name) AS student_name,
        ta.assignment_date AS created_at,
        ta.deadline_date AS updated_at,
        ta.final_grade AS grade,
        (SELECT file_path FROM thesis_files WHERE thesis_assignment_id = ta.id AND file_type = 'final' LIMIT 1) AS final_report_file,
        (SELECT protocol_pdf FROM examination_protocols WHERE thesis_assignment_id = ta.id LIMIT 1) AS grade_protocol_file
    FROM 
        thesis_assignments ta
    JOIN 
        thesis_topics tt ON ta.thesis_topic_id = tt.id
    JOIN 
        students s ON ta.student_id = s.id
    JOIN 
        users u ON s.user_id = u.id
";

$whereConditions = [];
$params = [$professorId];

$whereConditions[] = "(ta.supervisor_id = ? OR EXISTS (SELECT 1 FROM committee_members cm WHERE cm.thesis_assignment_id = ta.id AND cm.professor_id = ?))"; 
$params[] = $professorId;
$params[] = $professorId;

if ($status) {
    $whereConditions[] = "ta.status = ?";
    $params[] = $status;
}

if ($role) {
    if ($role === 'supervisor') {
        $whereConditions[] = "ta.supervisor_id = ?";
        $params[] = $professorId;
    } else if ($role === 'committee_member') {
        $whereConditions[] = "EXISTS (SELECT 1 FROM committee_members cm WHERE cm.thesis_assignment_id = ta.id AND cm.professor_id = ?)";
        $params[] = $professorId;
    }
}

$whereClause = "WHERE " . implode(" AND ", $whereConditions);

$sql .= " " . $whereClause;

$sql .= " ORDER BY ta.assignment_date DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$theses = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($theses as &$thesis) {
    $stmt = $pdo->prepare("
        SELECT 
            p.id AS professor_id,
            CONCAT(u.first_name, ' ', u.last_name) AS professor_name
        FROM 
            professors p
        JOIN 
            users u ON p.user_id = u.id
        WHERE 
            p.id = :supervisor_id
    ");
    $stmt->bindParam(':supervisor_id', $thesis['supervisor_id'], PDO::PARAM_INT);
    $stmt->execute();
    $supervisor = $stmt->fetch(PDO::FETCH_ASSOC);
    $thesis['supervisor'] = $supervisor;

    $stmt = $pdo->prepare("
        SELECT 
            p.id AS professor_id,
            CONCAT(u.first_name, ' ', u.last_name) AS professor_name
        FROM 
            committee_members cm
        JOIN 
            professors p ON cm.professor_id = p.id
        JOIN 
            users u ON p.user_id = u.id
        WHERE 
            cm.thesis_assignment_id = :assignment_id
    ");
    $stmt->bindParam(':assignment_id', $thesis['assignment_id'], PDO::PARAM_INT);
    $stmt->execute();
    $committee = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $thesis['committee_members'] = $committee;

    $stmt = $pdo->prepare("
        SELECT 
            new_status AS status,
            changed_at AS created_at,
            notes
        FROM 
            status_changes
        WHERE 
            thesis_assignment_id = :assignment_id
        ORDER BY 
            changed_at DESC
    ");
    $stmt->bindParam(':assignment_id', $thesis['assignment_id'], PDO::PARAM_INT);
    $stmt->execute();
    $timeline = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $thesis['timeline'] = $timeline;
}

if ($format) {
    switch ($format) {
        case 'csv':
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="thesis_list.csv"');
            
            $output = fopen('php://output', 'w');
            
            fputcsv($output, ['ID', 'Τίτλος', 'Φοιτητής', 'Κατάσταση', 'Ρόλος Καθηγητή', 'Ημερομηνία Δημιουργίας', 'Βαθμός']);
            
            foreach ($theses as $thesis) {
                fputcsv($output, [
                    $thesis['assignment_id'],
                    $thesis['thesis_title'],
                    $thesis['student_name'] . ' (' . $thesis['student_am'] . ')',
                    $thesis['status'],
                    $thesis['professor_role'],
                    $thesis['created_at'],
                    $thesis['grade'] ? $thesis['grade'] : 'N/A'
                ]);
            }
            
            fclose($output);
            exit;
            
        case 'json':
            header('Content-Type: application/json; charset=utf-8');
            header('Content-Disposition: attachment; filename="thesis_list.json"');
            echo json_encode($theses, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            exit;
            
        default:
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Μη υποστηριζόμενη μορφή εξαγωγής'
            ]);
            exit;
    }
}

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'data' => $theses,
    'database_info' => [
        'tables_checked' => $tables,
        'missing_tables' => $missingTables,
        'missing_columns' => $missingColumns
    ]
]);
exit;

} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Exception: ' . $e->getMessage(),
        'error_details' => [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]
    ]);
    exit;
}

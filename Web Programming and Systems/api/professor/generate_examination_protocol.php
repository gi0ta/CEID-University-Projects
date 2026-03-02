<?php
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/opt/lampp/logs/php_error_log');
error_reporting(E_ALL);


function debug_log($message) {
    error_log("[DEBUG] " . $message);
}

debug_log("[Έναρξη] Φόρτωση απαραίτητων αρχείων");

try {
    require_once '../../includes/config/config.php';
    debug_log("config.php loaded");
    
    require_once '../../includes/config/database.php';
    debug_log("database.php loaded");
    
    require_once '../../includes/classes/User.php';
    debug_log("User.php loaded");
    
    require_once '../../includes/functions/auth_functions.php';
    debug_log("auth_functions.php loaded");
    
    $db = getDbConnection();
    debug_log("Database connection established");
} catch (Exception $e) {
    debug_log("[ΣΦΑΛΜΑ] Φόρτωση αρχείων: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Σφάλμα κατά τη φόρτωση των απαραίτητων αρχείων.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

header('Content-Type: application/json');
debug_log("Header set");

try {
    debug_log("Starting secure session");
    startSecureSession();
    debug_log("Session started");
} catch (Exception $e) {
    debug_log("[ΣΦΑΛΜΑ] Έναρξη session: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Σφάλμα κατά την έναρξη του session.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    debug_log("Getting user ID");
    $user_id = User::getCurrentUserId();
    debug_log("User ID: " . $user_id);
    
    debug_log("Getting professor ID");
    $professor_stmt = $db->prepare("
        SELECT p.id 
        FROM professors p 
        WHERE p.user_id = ?
    ");
    $professor_stmt->bindParam(1, $user_id, PDO::PARAM_INT);
    $professor_stmt->execute();
    $professor = $professor_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$professor) {
        throw new Exception("Δεν βρέθηκε καθηγητής με το συγκεκριμένο ID χρήστη.");
    }
    
    $professor_id = $professor['id'];
    debug_log("Professor ID: " . $professor_id);
} catch (Exception $e) {
    debug_log("[ΣΦΑΛΜΑ] Λήψη ID χρήστη: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Σφάλμα κατά την ανάκτηση των στοιχείων χρήστη.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$is_existing = false;

if (!User::isLoggedIn() || User::getCurrentUserRole() !== 'professor') {
    echo json_encode([
        'success' => false,
        'message' => 'Δεν έχετε πρόσβαση σε αυτή τη λειτουργία.'
    ]);
    exit;
}

$thesis_id = isset($_POST['thesis_id']) ? intval($_POST['thesis_id']) : 0;
if (!$thesis_id) {
    echo json_encode([
        'success' => false,
        'message' => 'Δεν έχει οριστεί έγκυρο ID διπλωματικής εργασίας.'
    ]);
    exit;
}

$thesis_id = intval($_POST['thesis_id']);

$db = getDbConnection();

$user_id = User::getCurrentUserId();

$professor_stmt = $db->prepare("
    SELECT p.id, u.first_name, u.last_name 
    FROM professors p 
    JOIN users u ON p.user_id = u.id
    WHERE p.user_id = ?
");
$professor_stmt->bindParam(1, $user_id, PDO::PARAM_INT);
$professor_stmt->execute();
$professor = $professor_stmt->fetch(PDO::FETCH_ASSOC);

if (!$professor) {
    echo json_encode([
        'success' => false,
        'message' => 'Δεν βρέθηκε ο καθηγητής.'
    ]);
    exit;
}

$professor_id = $professor['id'];

$supervisor_check_stmt = $db->prepare("
    SELECT ta.id, ta.supervisor_id, ta.status, ta.grading_enabled
    FROM thesis_assignments ta
    WHERE ta.id = ? AND ta.supervisor_id = ?
");
$supervisor_check_stmt->bindParam(1, $thesis_id, PDO::PARAM_INT);
$supervisor_check_stmt->bindParam(2, $professor_id, PDO::PARAM_INT);
$supervisor_check_stmt->execute();
$thesis_info = $supervisor_check_stmt->fetch(PDO::FETCH_ASSOC);

if (!$thesis_info) {
    echo json_encode([
        'success' => false,
        'message' => 'Δεν είστε ο επιβλέπων καθηγητής αυτής της διπλωματικής εργασίας.'
    ]);
    exit;
}

if ($thesis_info['status'] !== 'examination' || $thesis_info['grading_enabled'] !== 1) {
    echo json_encode([
        'success' => false,
        'message' => 'Η διπλωματική εργασία δεν είναι σε κατάσταση εξέτασης ή η βαθμολόγηση δεν είναι ενεργοποιημένη.'
    ]);
    exit;
}

$protocol_check_stmt = $db->prepare("
    SELECT id, protocol_html, protocol_number, protocol_date FROM examination_protocols WHERE thesis_assignment_id = ?
");
$protocol_check_stmt->bindParam(1, $thesis_id, PDO::PARAM_INT);
$protocol_check_stmt->execute();

if ($protocol_check_stmt->rowCount() > 0) {
    $existing_protocol = $protocol_check_stmt->fetch(PDO::FETCH_ASSOC);
    $is_existing = true;
    
    echo json_encode([
        'success' => true,
        'message' => 'Το πρωτόκολλο εξέτασης έχει ήδη δημιουργηθεί και ανακτήθηκε με επιτυχία.',
        'protocol_html' => $existing_protocol['protocol_html'],
        'protocol_number' => $existing_protocol['protocol_number'],
        'protocol_date' => $existing_protocol['protocol_date'],
        'is_existing' => true
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$committee_check_stmt = $db->prepare("
    SELECT 
        COUNT(cm.professor_id) AS total_members,
        (SELECT COUNT(DISTINCT tg.professor_id) FROM thesis_grades tg WHERE tg.thesis_assignment_id = ?) AS graded_members
    FROM 
        committee_members cm
    WHERE 
        cm.thesis_assignment_id = ? 
        AND cm.status = 'accepted'
");
$committee_check_stmt->bindParam(1, $thesis_id, PDO::PARAM_INT);
$committee_check_stmt->bindParam(2, $thesis_id, PDO::PARAM_INT);
$committee_check_stmt->execute();
$committee_info = $committee_check_stmt->fetch(PDO::FETCH_ASSOC);

$supervisor_graded_stmt = $db->prepare("
    SELECT COUNT(*) AS supervisor_graded
    FROM thesis_grades
    WHERE thesis_assignment_id = ? AND professor_id = ?
");
$supervisor_graded_stmt->bindParam(1, $thesis_id, PDO::PARAM_INT);
$supervisor_graded_stmt->bindParam(2, $professor_id, PDO::PARAM_INT);
$supervisor_graded_stmt->execute();
$supervisor_graded = $supervisor_graded_stmt->fetch(PDO::FETCH_ASSOC);

$total_required_graders = $committee_info['total_members'] + 1;

if ($committee_info['graded_members'] < $total_required_graders || $supervisor_graded['supervisor_graded'] < 1) {
    echo json_encode([
        'success' => false,
        'message' => 'Δεν έχουν βαθμολογήσει όλα τα μέλη της επιτροπής και ο επιβλέπων.'
    ]);
    exit;
}

$thesis_stmt = $db->prepare("
    SELECT 
        ta.id,
        tt.title AS thesis_title,
        COALESCE(tp.presentation_date, ta.completion_date) AS presentation_date,
        s.registration_number AS student_am,
        su.first_name AS student_first_name,
        su.last_name AS student_last_name,
        p.id AS supervisor_id,
        'Καθηγητής' AS supervisor_title,
        pu.first_name AS supervisor_first_name,
        pu.last_name AS supervisor_last_name,
        'Τμήμα Μηχανικών Ηλεκτρονικών Υπολογιστών και Πληροφορικής, Πολυτεχνική Σχολή, Πανεπιστήμιο Πατρών' AS department_name
    FROM 
        thesis_assignments ta
        JOIN thesis_topics tt ON ta.thesis_topic_id = tt.id
        LEFT JOIN thesis_presentations tp ON ta.id = tp.thesis_assignment_id
        JOIN students s ON ta.student_id = s.id
        JOIN users su ON s.user_id = su.id
        JOIN professors p ON ta.supervisor_id = p.id
        JOIN users pu ON p.user_id = pu.id
    WHERE 
        ta.id = ?
");

$thesis_stmt->bindParam(1, $thesis_id, PDO::PARAM_INT);
$thesis_stmt->execute();
$thesis_details = $thesis_stmt->fetch(PDO::FETCH_ASSOC);

$committee_members_stmt = $db->prepare("
    SELECT 
        p.id,
        u.first_name,
        u.last_name,
        p.specialty AS title,
        p.specialty
    FROM 
        committee_members cm
    JOIN 
        professors p ON cm.professor_id = p.id
    JOIN
        users u ON p.user_id = u.id
    WHERE 
        cm.thesis_assignment_id = ? AND cm.status = 'accepted'
    ORDER BY 
        u.last_name, u.first_name
");

$committee_members_stmt->bindParam(1, $thesis_id, PDO::PARAM_INT);
$committee_members_stmt->execute();
$committee_members = $committee_members_stmt->fetchAll(PDO::FETCH_ASSOC);

$grades_stmt = $db->prepare("
    SELECT 
        tg.professor_id,
        tg.subject_understanding,
        tg.problem_solving,
        tg.literature_review,
        tg.methodology,
        tg.implementation,
        tg.innovation,
        tg.writing_quality,
        tg.presentation,
        tg.final_grade,
        tg.comments,
        tg.grading_date,
        u.first_name,
        u.last_name,
        p.specialty AS title,
        CASE 
            WHEN p.id = ta.supervisor_id THEN 'Επιβλέπων'
            ELSE 'Μέλος επιτροπής'
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
        role DESC, u.last_name, u.first_name
");
$grades_stmt->bindParam(1, $thesis_id, PDO::PARAM_INT);

$grades_stmt->execute();
$grades = $grades_stmt->fetchAll(PDO::FETCH_ASSOC);

$total_grade = 0;
$count = 0;
foreach ($grades as $grade) {
    $total_grade += $grade['final_grade'];
    $count++;
}
$average_grade = $count > 0 ? round($total_grade / $count, 1) : 0;

$protocol_number = 'PROT-' . date('Y') . '-' . sprintf('%03d', $thesis_id);

function getDayName($dayNumber) {
    $days = [
        1 => 'Δευτέρα',
        2 => 'Τρίτη',
        3 => 'Τετάρτη',
        4 => 'Πέμπτη',
        5 => 'Παρασκευή',
        6 => 'Σάββατο',
        7 => 'Κυριακή'
    ];
    return $days[$dayNumber] ?? '';
}

$committee_member_names = [];
$supervisor_name = "";

$supervisor_stmt = $db->prepare("SELECT p.id, u.first_name, u.last_name, p.specialty AS title FROM thesis_assignments ta JOIN professors p ON ta.supervisor_id = p.id JOIN users u ON p.user_id = u.id WHERE ta.id = ?");
$supervisor_stmt->bindParam(1, $thesis_id, PDO::PARAM_INT);
$supervisor_stmt->execute();
$supervisor = $supervisor_stmt->fetch(PDO::FETCH_ASSOC);

if ($supervisor) {
    $supervisor_name = htmlspecialchars($supervisor['title'] . ' ' . $supervisor['first_name'] . ' ' . $supervisor['last_name']);
    $committee_member_names[0] = $supervisor_name;
}

$i = 1;
debug_log("Committee members count: " . (is_array($committee_members) ? count($committee_members) : "not an array"));

if (is_array($committee_members) && !empty($committee_members)) {
    foreach ($committee_members as $member) {
        if (!isset($member['id']) || !isset($member['title']) || !isset($member['first_name']) || !isset($member['last_name'])) {
            debug_log("Missing required fields in committee member: " . json_encode($member));
            continue;
        }
        
        if ($supervisor && isset($supervisor['id']) && $member['id'] == $supervisor['id']) {
            debug_log("Skipping supervisor in committee members: " . $member['id']);
            continue;
        }
        $committee_member_names[$i] = htmlspecialchars($member['title'] . ' ' . $member['first_name'] . ' ' . $member['last_name']);
        $i++;
    }
}

$protocol_html = '<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Πρωτόκολλο Εξέτασης Διπλωματικής Εργασίας</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 12pt;
            line-height: 1.5;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .university-logo {
            width: 100px;
            height: auto;
        }
        .university-name {
            font-size: 16pt;
            font-weight: bold;
            margin: 10px 0;
        }
        .department-name {
            font-size: 14pt;
            margin-bottom: 20px;
        }
        .protocol-title {
            font-size: 14pt;
            font-weight: bold;
            margin-top: 20px;
        }
        .protocol-number {
            margin-top: 10px;
            font-style: italic;
        }
        .student-name {
            margin: 20px 0;
            text-align: center;
        }
        .meeting-details, .committee-members, .thesis-title, .grades-section, .final-grade, .comments {
            margin: 20px 0;
            text-align: justify;
        }
        .grade-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .grade-table th, .grade-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .grade-table th {
            background-color: #f2f2f2;
        }
        .signature-section {
            margin-top: 50px;
        }
        .signature-row {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }
        .signature-block {
            width: 30%;
            text-align: center;
        }
        .signature-line {
            border-top: 1px solid #000;
            margin-top: 40px;
            padding-top: 5px;
        }
        .signature-role {
            font-style: italic;
        }
        .footer {
            margin-top: 50px;
            font-size: 10pt;
            text-align: center;
            color: #666;
        }
        @media print {
            .no-print {
                display: none;
            }
            body {
                padding: 0;
            }
            .container {
                border: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
    <div class="header">
        <div class="university-name">ΠΑΝΕΠΙΣΤΗΜΙΟ ΠΑΤΡΩΝ</div>
        <div class="department-name">ΤΜΗΜΑ ΜΗΧΑΝΙΚΩΝ Η/Υ ΚΑΙ ΠΛΗΡΟΦΟΡΙΚΗΣ</div>
        <div class="protocol-number">Αριθμός Πρωτοκόλλου: ' . htmlspecialchars($protocol_number) . '</div>
        <div class="protocol-number">Ημερομηνία: ' . date('d/m/Y') . '</div>
        <div class="protocol-title">ΠΡΩΤΟΚΟΛΛΟ ΕΞΕΤΑΣΗΣ</div>
        <div class="protocol-title">ΓΙΑ ΤΗΝ ΠΑΡΟΥΣΙΑΣΗ ΚΑΙ ΚΡΙΣΗ ΤΗΣ ΔΙΠΛΩΜΑΤΙΚΗΣ ΕΡΓΑΣΙΑΣ</div>
    </div>
    
    <div class="student-name">
        του/της φοιτητή/φοιτήτριας<br>
        κ. ' . htmlspecialchars($thesis_details['student_first_name'] . ' ' . $thesis_details['student_last_name']) . ' (A.M.: ' . htmlspecialchars($thesis_details['student_am']) . ')
    </div>
    
    <div class="meeting-details">
        Η συνεδρίαση πραγματοποιήθηκε στην αίθουσα ........................, στις
        ' . date('d/m/Y', strtotime($thesis_details['presentation_date'])) . ', ημέρα ' . getDayName(date('N', strtotime($thesis_details['presentation_date']))) . ' και ώρα .................
    </div>
    
    <div class="committee-members">
        Στην συνεδρίαση είναι παρόντα τα μέλη της Τριμελούς επιτροπής, κ.κ.:<br>
        1. ' . ($committee_member_names[0] ?? '......................................') . ',<br>
        2. ' . ($committee_member_names[1] ?? '......................................') . ',<br>
        3. ' . ($committee_member_names[2] ?? '......................................') . ',<br>
        οι οποίοι ορίσθηκαν από την Συνέλευση του ΤΜΗΥΠ, στην συνεδρίαση της με αριθμό................
    </div>
    
    <div class="thesis-title">
        Ο/Η φοιτητής/φοιτήτρια
        κ. ' . htmlspecialchars($thesis_details["student_first_name"] . ' ' . $thesis_details["student_last_name"]) . ' ανέπτυξε το θέμα
        της Διπλωματικής του/της Εργασίας, με τίτλο
        «' . htmlspecialchars($thesis_details["thesis_title"]) . '»
    </div>
    
    <div class="section">
        <p>Στην συνέχεια υποβλήθηκαν ερωτήσεις στον υποψήφιο από τα μέλη της Τριμελούς Επιτροπής.</p>
        <p>Μετά το τέλος της ανάπτυξης της εργασίας του και των ερωτήσεων, ο υποψήφιος αποχωρεί.</p>
        <p>Τα μέλη της Τριμελούς Επιτροπής, ψηφίζουν υπέρ της εγκρίσεως της Διπλωματικής Εργασίας.</p>
    </div>
    
    <div class="section">
        <p>Τα μέλη της Τριμελούς Επιτροπής, απονέμουν την παρακάτω βαθμολογία:</p>
        
        <table>
            <tr>
                <th>ΟΝΟΜΑΤΕΠΩΝΥΜΟ</th>
                <th>ΙΔΙΟΤΗΤΑ</th>
                <th>ΒΑΘΜΟΣ</th>
            </tr>';

foreach ($grades as $grade) {
    $protocol_html .= '<tr>
                <td>' . htmlspecialchars($grade['first_name'] . ' ' . $grade['last_name']) . '</td>
                <td>' . htmlspecialchars($grade['role']) . '</td>
                <td>' . htmlspecialchars($grade['final_grade']) . '</td>
            </tr>';
}

$protocol_html .= '</table>
        
        <p>Μετά την έγκριση και την απονομή του βαθμού ' . $average_grade . ', η Τριμελής Επιτροπή, προτείνει να
        προχωρήσει στην διαδικασία για να ανακηρύξει τον κ. ' . htmlspecialchars($thesis_details["student_first_name"] . ' ' . $thesis_details["student_last_name"]) . ', σε
        διπλωματούχο του Προγράμματος Σπουδών του «ΤΜΗΜΑΤΟΣ ΜΗΧΑΝΙΚΩΝ, ΗΛΕΚΤΡΟΝΙΚΩΝ
        ΥΠΟΛΟΓΙΣΤΩΝ ΚΑΙ ΠΛΗΡΟΦΟΡΙΚΗΣ ΠΑΝΕΠΙΣΤΗΜΙΟΥ ΠΑΤΡΩΝ» και να του απονέμει το Δίπλωμα
        Μηχανικού Η/Υ το οποίο αναγνωρίζεται ως Ενιαίος Τίτλος Σπουδών Μεταπτυχιακού Επιπέδου.</p>
    </div>';

$protocol_html .= '
    
    <div class="signature-section">';

$protocol_html .= '
        <p>ΤΑ ΜΕΛΗ ΤΗΣ ΤΡΙΜΕΛΟΥΣ ΕΠΙΤΡΟΠΗΣ</p>
        
        <div class="signature-row">
            <div class="signature-block">
                <div class="signature-line">' . ($committee_member_names[0] ?? '') . '</div>
                <div class="signature-role">Επιβλέπων</div>
            </div>
            
            <div class="signature-block">
                <div class="signature-line">' . ($committee_member_names[1] ?? '') . '</div>
                <div class="signature-role">Μέλος</div>
            </div>
            
            <div class="signature-block">
                <div class="signature-line">' . ($committee_member_names[2] ?? '') . '</div>
                <div class="signature-role">Μέλος</div>
            </div>
        </div>';

$protocol_html .= '</div>
    
    <div class="footer">
        <p>Το παρόν πρωτόκολλο εξέτασης δημιουργήθηκε αυτόματα από το σύστημα διαχείρισης διπλωματικών εργασιών.</p>
        <p>Ημερομηνία δημιουργίας: ' . date('d/m/Y') . '</p>
    </div>
    
    <div class="no-print" style="margin-top: 30px; text-align: center;">
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 16px; cursor: pointer;">Εκτύπωση Πρωτοκόλλου</button>
    </div>
</body>
</html>';


$insert_protocol_stmt = $db->prepare("INSERT INTO examination_protocols (thesis_assignment_id, protocol_html, protocol_date, protocol_number, created_by) VALUES (?, ?, NOW(), ?, ?)");
$insert_protocol_stmt->bindParam(1, $thesis_id, PDO::PARAM_INT);
$insert_protocol_stmt->bindParam(2, $protocol_html, PDO::PARAM_STR);
$insert_protocol_stmt->bindParam(3, $protocol_number, PDO::PARAM_STR);
$insert_protocol_stmt->bindParam(4, $user_id, PDO::PARAM_INT);

try {
    debug_log("Executing insert protocol statement");
    $insert_protocol_stmt->execute();
    $protocol_id = $db->lastInsertId();
    debug_log("Protocol inserted with ID: " . $protocol_id);
} catch (PDOException $e) {
    debug_log("[ΣΦΑΛΜΑ] Εισαγωγή πρωτοκόλλου: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Σφάλμα κατά την αποθήκευση του πρωτοκόλλου: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$update_thesis_stmt = $db->prepare("UPDATE thesis_assignments SET examination_protocol_generated = 1 WHERE id = ?");
$update_thesis_stmt->bindParam(1, $thesis_id, PDO::PARAM_INT);
$update_thesis_stmt->execute();

try {
    debug_log("Preparing JSON response");
    $response = [
        'success' => true,
        'message' => 'Το πρωτόκολλο εξέτασης δημιουργήθηκε με επιτυχία.',
        'protocol_html' => $protocol_html,
        'protocol_number' => $protocol_number,
        'protocol_date' => date('Y-m-d'),
        'is_existing' => $is_existing
    ];
    debug_log("JSON response structure created");
    
    $json_output = json_encode($response, JSON_UNESCAPED_UNICODE);
    if ($json_output === false) {
        throw new Exception("JSON encoding failed: " . json_last_error_msg());
    }
    
    debug_log("JSON encoded successfully");
    echo $json_output;
    debug_log("Response sent successfully");
    exit;
} catch (Exception $e) {
    debug_log("[ΣΦΑΛΜΑ] JSON response: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Σφάλμα κατά την επιστροφή του πρωτοκόλλου: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

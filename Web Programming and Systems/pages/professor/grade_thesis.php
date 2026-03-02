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
    header('Location: ../../index.php');
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: thesis_assignments.php');
    exit;
}

$thesis_id = intval($_GET['id']);

$db = getDbConnection();

$user_id = User::getCurrentUserId();
$professor_stmt = $db->prepare("
    SELECT p.id, p.first_name, p.last_name 
    FROM professors p 
    WHERE p.user_id = ?
");
$professor_stmt->bindParam(1, $user_id, PDO::PARAM_INT);
$professor_stmt->execute();
$professor = $professor_stmt->fetch(PDO::FETCH_ASSOC);

if (!$professor) {
    header('Location: ../../index.php');
    exit;
}

$professor_id = $professor['id'];

$thesis_stmt = $db->prepare("
    SELECT 
        ta.id,
        ta.thesis_topic_id,
        ta.student_id,
        ta.supervisor_id,
        ta.status,
        ta.grading_enabled,
        tt.title AS thesis_title,
        s.first_name AS student_first_name,
        s.last_name AS student_last_name,
        p.first_name AS supervisor_first_name,
        p.last_name AS supervisor_last_name,
        CASE 
            WHEN ta.supervisor_id = ? THEN 'supervisor'
            WHEN EXISTS (
                SELECT 1 FROM committee_members cm 
                WHERE cm.thesis_assignment_id = ta.id AND cm.professor_id = ? AND cm.status = 'accepted'
            ) THEN 'committee'
            ELSE 'none'
        END AS professor_role
    FROM 
        thesis_assignments ta
    JOIN 
        thesis_topics tt ON ta.thesis_topic_id = tt.id
    JOIN 
        students s ON ta.student_id = s.id
    JOIN 
        professors p ON ta.supervisor_id = p.id
    WHERE 
        ta.id = ? AND 
        ta.status = 'examination' AND
        ta.grading_enabled = 1 AND
        (
            ta.supervisor_id = ? OR 
            EXISTS (
                SELECT 1 FROM committee_members cm 
                WHERE cm.thesis_assignment_id = ta.id AND cm.professor_id = ? AND cm.status = 'accepted'
            )
        )
");
$thesis_stmt->bindParam(1, $professor_id, PDO::PARAM_INT);
$thesis_stmt->bindParam(2, $professor_id, PDO::PARAM_INT);
$thesis_stmt->bindParam(3, $thesis_id, PDO::PARAM_INT);
$thesis_stmt->bindParam(4, $professor_id, PDO::PARAM_INT);
$thesis_stmt->bindParam(5, $professor_id, PDO::PARAM_INT);
$thesis_stmt->execute();
$thesis = $thesis_stmt->fetch(PDO::FETCH_ASSOC);

if (!$thesis) {
    header('Location: thesis_assignments.php');
    exit;
}

$grade_stmt = $db->prepare("
    SELECT * FROM thesis_grades 
    WHERE thesis_assignment_id = ? AND professor_id = ?
");
$grade_stmt->bindParam(1, $thesis_id, PDO::PARAM_INT);
$grade_stmt->bindParam(2, $professor_id, PDO::PARAM_INT);
$grade_stmt->execute();
$existing_grade = $grade_stmt->fetch(PDO::FETCH_ASSOC);

$all_grades_stmt = $db->prepare("
    SELECT 
        tg.*,
        p.first_name,
        p.last_name,
        CASE 
            WHEN p.id = ta.supervisor_id THEN 'Επιβλέπων'
            ELSE 'Μέλος επιτροπής'
        END AS role
    FROM 
        thesis_grades tg
    JOIN 
        professors p ON tg.professor_id = p.id
    JOIN 
        thesis_assignments ta ON tg.thesis_assignment_id = ta.id
    WHERE 
        tg.thesis_assignment_id = ?
    ORDER BY 
        tg.grading_date DESC
");
$all_grades_stmt->bindParam(1, $thesis_id, PDO::PARAM_INT);
$all_grades_stmt->execute();
$all_grades = $all_grades_stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = 'Βαθμολόγηση Διπλωματικής Εργασίας';

include '../../templates/header.php';
?>

<main class="container mt-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="dashboard.php">Αρχική</a></li>
            <li class="breadcrumb-item"><a href="thesis_assignments.php">Διπλωματικές Εργασίες</a></li>
            <li class="breadcrumb-item"><a href="thesis_details.php?id=<?php echo $thesis_id; ?>">Λεπτομέρειες Διπλωματικής</a></li>
            <li class="breadcrumb-item active" aria-current="page">Βαθμολόγηση</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-12">
            <h1><?php echo $page_title; ?></h1>
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Στοιχεία Διπλωματικής Εργασίας</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h4><?php echo htmlspecialchars($thesis['thesis_title']); ?></h4>
                            <p><strong>Φοιτητής/τρια:</strong> <?php echo htmlspecialchars($thesis['student_first_name'] . ' ' . $thesis['student_last_name']); ?></p>
                            <p><strong>Επιβλέπων/ουσα:</strong> <?php echo htmlspecialchars($thesis['supervisor_first_name'] . ' ' . $thesis['supervisor_last_name']); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Εδώ θα προστεθεί η φόρμα βαθμολόγησης και η προβολή των βαθμών -->
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Η σελίδα βαθμολόγησης είναι υπό κατασκευή. Θα προστεθεί σύντομα η φόρμα βαθμολόγησης με τα κριτήρια που προβλέπονται στον κανονισμό ΔΕ του ΤΜΗΥΠ.
            </div>

        </div>
    </div>
</main>

<?php include '../../templates/footer.php'; ?>

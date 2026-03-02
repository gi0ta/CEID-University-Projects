<?php
require_once __DIR__ . '/../../includes/init.php';

if (!User::isLoggedIn() || User::getCurrentUserRoleType() !== 'professor') {
    header("Location: " . SITE_URL . "/pages/auth/login.php");
    exit();
}

if (!isset($_GET['topic_id']) || empty($_GET['topic_id'])) {
    header("Location: " . SITE_URL . "/pages/professor/assign_topic_list.php");
    exit();
}

$topicId = intval($_GET['topic_id']);

$db = new Database();
$pdo = $db->getConnection();

$userId = User::getCurrentUserId();
$stmt = $pdo->prepare("SELECT id FROM professors WHERE user_id = :user_id");
$stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
$stmt->execute();
$professor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$professor) {
    header("Location: " . SITE_URL . "/pages/professor/dashboard.php");
    exit();
}

$professorId = $professor['id'];

$stmt = $pdo->prepare("
    SELECT id, title, summary, is_assigned 
    FROM thesis_topics 
    WHERE id = :topic_id AND professor_id = :professor_id
");
$stmt->bindParam(':topic_id', $topicId, PDO::PARAM_INT);
$stmt->bindParam(':professor_id', $professorId, PDO::PARAM_INT);
$stmt->execute();
$topic = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$topic || $topic['is_assigned'] == 1) {
    $_SESSION['flash_message'] = [
        'type' => 'danger',
        'message' => 'Το θέμα δεν βρέθηκε, δεν σας ανήκει ή έχει ήδη ανατεθεί.'
    ];
    header("Location: " . SITE_URL . "/pages/professor/assign_topic_list.php");
    exit();
}

$pageTitle = "Ανάθεση Θέματος σε Φοιτητή";

include_once __DIR__ . '/../../templates/header.php';

?>

<div class="container mt-4">
    <h2><?php echo $pageTitle; ?></h2>
    
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Στοιχεία Επιλεγμένου Θέματος</h5>
                </div>
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($topic['title']); ?></h5>
                    <p class="card-text"><?php echo htmlspecialchars($topic['summary']); ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Αναζήτηση Φοιτητή</h5>
                </div>
                <div class="card-body">
                    <form id="student-search-form">
                        <div class="mb-3">
                            <label for="search-term" class="form-label">Αναζήτηση με ΑΜ ή Ονοματεπώνυμο</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="search-term" placeholder="Εισάγετε ΑΜ ή ονοματεπώνυμο φοιτητή">
                                <button class="btn btn-primary" type="submit" id="search-button">
                                    <i class="bi bi-search"></i> Αναζήτηση
                                </button>
                            </div>
                            <div class="form-text">Εισάγετε τουλάχιστον 3 χαρακτήρες για αναζήτηση.</div>
                        </div>
                    </form>
                    
                    <div id="search-results" class="mt-4">
                        <!-- Εδώ θα εμφανιστούν τα αποτελέσματα αναζήτησης -->
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Επιλεγμένος Φοιτητής</h5>
                </div>
                <div class="card-body">
                    <div id="selected-student">
                        <p class="text-muted">Δεν έχει επιλεγεί φοιτητής ακόμα. Χρησιμοποιήστε την αναζήτηση για να βρείτε και να επιλέξετε φοιτητή.</p>
                    </div>
                    
                    <form id="assign-form" class="mt-4" style="display: none;">
                        <input type="hidden" id="topic-id" value="<?php echo $topicId; ?>">
                        <input type="hidden" id="student-id" value="">
                        
                        <div class="d-flex justify-content-between">
                            <a href="<?php echo SITE_URL; ?>/pages/professor/assign_topic_list.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Επιστροφή
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-circle"></i> Ανάθεση Θέματος
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include_once __DIR__ . '/../../templates/footer.php';
?>

<!-- Φόρτωση του JavaScript για την ανάθεση θέματος σε φοιτητή -->
<script src="<?php echo SITE_URL; ?>/assets/js/professor/assign_student_to_topic.js"></script>

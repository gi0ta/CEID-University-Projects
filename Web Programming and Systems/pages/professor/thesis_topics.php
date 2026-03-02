<?php

require_once '../../includes/config/config.php';
require_once '../../includes/config/database.php';
require_once '../../includes/classes/User.php';
require_once '../../includes/functions/auth_functions.php';

startSecureSession();

if (!User::isLoggedIn() || User::getCurrentUserRole() !== 'professor') {
    header('Location: ' . SITE_URL . '/pages/auth/login.php');
    exit;
}

$db = getDbConnection();

$user_id = User::getCurrentUserId();
$stmt = $db->prepare("
    SELECT p.* FROM professors p 
    WHERE p.user_id = :user_id
");
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$professor = $stmt->fetch(PDO::FETCH_ASSOC);

$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

$query = "SELECT * FROM thesis_topics WHERE professor_id = :professor_id";

if ($filter === 'available') {
    $query .= " AND is_assigned = 0";
} elseif ($filter === 'assigned') {
    $query .= " AND is_assigned = 1";
}

if ($sort === 'newest') {
    $query .= " ORDER BY created_at DESC";
} elseif ($sort === 'oldest') {
    $query .= " ORDER BY created_at ASC";
} elseif ($sort === 'title') {
    $query .= " ORDER BY title ASC";
}

$stmt = $db->prepare($query);
$stmt->bindParam(':professor_id', $professor['id']);
$stmt->execute();
$thesis_topics = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_POST['delete_topic']) && isset($_POST['topic_id']) && isset($_POST['csrf_token'])) {
    if (verifyCsrfToken($_POST['csrf_token'])) {
        $topic_id = $_POST['topic_id'];
        
        $stmt = $db->prepare("
            SELECT * FROM thesis_topics 
            WHERE id = :topic_id AND professor_id = :professor_id
        ");
        $stmt->bindParam(':topic_id', $topic_id);
        $stmt->bindParam(':professor_id', $professor['id']);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $topic = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($topic['is_assigned']) {
                setFlashMessage('error', 'Δεν μπορείτε να διαγράψετε ένα θέμα που έχει ανατεθεί.');
            } else {
                $stmt = $db->prepare("DELETE FROM thesis_topics WHERE id = :topic_id");
                $stmt->bindParam(':topic_id', $topic_id);
                
                if ($stmt->execute()) {
                    if (!empty($topic['description_file'])) {
                        $file_path = '../../uploads/thesis_files/' . $topic['description_file'];
                        if (file_exists($file_path)) {
                            unlink($file_path);
                        }
                    }
                    
                    setFlashMessage('success', 'Το θέμα διαγράφηκε με επιτυχία.');
                    
                    header('Location: ' . $_SERVER['PHP_SELF'] . '?filter=' . $filter . '&sort=' . $sort);
                    exit;
                } else {
                    setFlashMessage('error', 'Παρουσιάστηκε σφάλμα κατά τη διαγραφή του θέματος.');
                }
            }
        } else {
            setFlashMessage('error', 'Το θέμα δεν βρέθηκε ή δεν έχετε δικαίωμα να το διαγράψετε.');
        }
    } else {
        setFlashMessage('error', 'Μη έγκυρο αίτημα. Παρακαλώ προσπαθήστε ξανά.');
    }
}

$csrfToken = generateCsrfToken();

$pageTitle = 'Διαχείριση Θεμάτων Διπλωματικών';
?>

<?php include '../../templates/header.php'; ?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2><?php echo $pageTitle; ?></h2>
                <a href="add_thesis_topic.php" class="btn btn-primary">
                    <i class="fas fa-plus-circle me-2"></i>Προσθήκη Νέου Θέματος
                </a>
            </div>
            <hr>
        </div>
    </div>
    
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="btn-group" role="group" aria-label="Φίλτρα">
                                <a href="?filter=all&sort=<?php echo $sort; ?>" class="btn btn-outline-primary <?php echo $filter === 'all' ? 'active' : ''; ?>">
                                    Όλα
                                </a>
                                <a href="?filter=available&sort=<?php echo $sort; ?>" class="btn btn-outline-primary <?php echo $filter === 'available' ? 'active' : ''; ?>">
                                    Διαθέσιμα
                                </a>
                                <a href="?filter=assigned&sort=<?php echo $sort; ?>" class="btn btn-outline-primary <?php echo $filter === 'assigned' ? 'active' : ''; ?>">
                                    Ανατεθειμένα
                                </a>

                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex justify-content-end">
                                <div class="dropdown">
                                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="sortDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-sort me-2"></i>Ταξινόμηση
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="sortDropdown">
                                        <li>
                                            <a class="dropdown-item <?php echo $sort === 'newest' ? 'active' : ''; ?>" href="?filter=<?php echo $filter; ?>&sort=newest">
                                                Νεότερα πρώτα
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item <?php echo $sort === 'oldest' ? 'active' : ''; ?>" href="?filter=<?php echo $filter; ?>&sort=oldest">
                                                Παλαιότερα πρώτα
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item <?php echo $sort === 'title' ? 'active' : ''; ?>" href="?filter=<?php echo $filter; ?>&sort=title">
                                                Αλφαβητικά κατά τίτλο
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12">
            <?php displayFlashMessages(); ?>
            
            <?php if (empty($thesis_topics)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>Δεν βρέθηκαν θέματα διπλωματικών που να ταιριάζουν με τα κριτήρια αναζήτησης.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead class="table-primary">
                            <tr>
                                <th>Τίτλος</th>
                                <th>Ημερομηνία Δημιουργίας</th>
                                <th>Κατάσταση</th>
                                <th>Περιγραφή</th>
                                <th>Ενέργειες</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($thesis_topics as $topic): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($topic['title']); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($topic['created_at'])); ?></td>
                                    <td>
                                        <?php if ($topic['is_assigned']): ?>
                                            <span class="badge bg-success">Ανατεθειμένο</span>
                                        <?php else: ?>
                                            <span class="badge bg-info">Διαθέσιμο</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($topic['description_file'])): ?>
                                            <a href="<?php echo SITE_URL; ?>/uploads/thesis_files/<?php echo $topic['description_file']; ?>" target="_blank" class="btn btn-sm btn-outline-info">
                                                <i class="fas fa-file-alt me-1"></i>Προβολή
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">Χωρίς αρχείο</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="view_thesis_topic.php?id=<?php echo $topic['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="edit_thesis_topic.php?id=<?php echo $topic['id']; ?>" class="btn btn-sm btn-outline-secondary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if (!$topic['is_assigned']): ?>
                                                <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $topic['id']; ?>">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <!-- Modal για διαγραφή θέματος -->
                                        <div class="modal fade" id="deleteModal<?php echo $topic['id']; ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?php echo $topic['id']; ?>" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="deleteModalLabel<?php echo $topic['id']; ?>">Διαγραφή Θέματος</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>Είστε βέβαιοι ότι θέλετε να διαγράψετε το θέμα:</p>
                                                        <p class="fw-bold"><?php echo htmlspecialchars($topic['title']); ?></p>
                                                        <p class="text-danger">Η ενέργεια αυτή δεν μπορεί να αναιρεθεί.</p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Ακύρωση</button>
                                                        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?filter=' . $filter . '&sort=' . $sort; ?>">
                                                            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                                            <input type="hidden" name="topic_id" value="<?php echo $topic['id']; ?>">
                                                            <button type="submit" name="delete_topic" class="btn btn-danger">Διαγραφή</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-md-12">
            <a href="dashboard.php" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-2"></i>Επιστροφή στο Dashboard
            </a>
        </div>
    </div>
</div>

<?php include '../../templates/footer.php'; ?>

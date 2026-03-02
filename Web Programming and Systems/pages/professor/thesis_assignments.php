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

$query = "
    SELECT ta.*, tt.title, tt.id as thesis_id, s.registration_number, u.first_name, u.last_name, u.email
    FROM thesis_assignments ta
    JOIN thesis_topics tt ON ta.thesis_topic_id = tt.id
    JOIN students s ON ta.student_id = s.id
    JOIN users u ON s.user_id = u.id
    WHERE ta.supervisor_id = :professor_id
";

if ($filter === 'pending') {
    $query .= " AND ta.status = 'pending'";
} elseif ($filter === 'active') {
    $query .= " AND ta.status = 'active'";
} elseif ($filter === 'examination') {
    $query .= " AND ta.status = 'examination'";
} elseif ($filter === 'completed') {
    $query .= " AND ta.status = 'completed'";
} elseif ($filter === 'cancelled') {
    $query .= " AND ta.status = 'cancelled'";
}

if ($sort === 'newest') {
    $query .= " ORDER BY ta.assignment_date DESC";
} elseif ($sort === 'oldest') {
    $query .= " ORDER BY ta.assignment_date ASC";
} elseif ($sort === 'student') {
    $query .= " ORDER BY u.last_name ASC, u.first_name ASC";
} elseif ($sort === 'title') {
    $query .= " ORDER BY tt.title ASC";
}

$stmt = $db->prepare($query);
$stmt->bindParam(':professor_id', $professor['id']);
$stmt->execute();
$thesis_assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Διαχείριση Αναθέσεων Διπλωματικών';
?>

<?php include '../../templates/header.php'; ?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2><?php echo $pageTitle; ?></h2>
                <a href="dashboard.php" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left me-2"></i>Επιστροφή στο Dashboard
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
                                    Όλες
                                </a>
                                <a href="?filter=pending&sort=<?php echo $sort; ?>" class="btn btn-outline-warning <?php echo $filter === 'pending' ? 'active' : ''; ?>">
                                    Εκκρεμείς
                                </a>
                                <a href="?filter=active&sort=<?php echo $sort; ?>" class="btn btn-outline-primary <?php echo $filter === 'active' ? 'active' : ''; ?>">
                                    Ενεργές
                                </a>
                                <a href="?filter=examination&sort=<?php echo $sort; ?>" class="btn btn-outline-info <?php echo $filter === 'examination' ? 'active' : ''; ?>">
                                    Εξέταση
                                </a>
                                <a href="?filter=completed&sort=<?php echo $sort; ?>" class="btn btn-outline-success <?php echo $filter === 'completed' ? 'active' : ''; ?>">
                                    Ολοκληρωμένες
                                </a>
                                <a href="?filter=cancelled&sort=<?php echo $sort; ?>" class="btn btn-outline-danger <?php echo $filter === 'cancelled' ? 'active' : ''; ?>">
                                    Ακυρωμένες
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
                                                Νεότερες πρώτα
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item <?php echo $sort === 'oldest' ? 'active' : ''; ?>" href="?filter=<?php echo $filter; ?>&sort=oldest">
                                                Παλαιότερες πρώτα
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item <?php echo $sort === 'student' ? 'active' : ''; ?>" href="?filter=<?php echo $filter; ?>&sort=student">
                                                Αλφαβητικά κατά φοιτητή
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
            
            <?php if (empty($thesis_assignments)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>Δεν βρέθηκαν αναθέσεις διπλωματικών που να ταιριάζουν με τα κριτήρια αναζήτησης.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead class="table-primary">
                            <tr>
                                <th>Φοιτητής</th>
                                <th>Θέμα</th>
                                <th>Ημερομηνία Ανάθεσης</th>
                                <th>Κατάσταση</th>
                                <th>Προθεσμία</th>
                                <th>Ενέργειες</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($thesis_assignments as $assignment): ?>
                                <tr>
                                    <td>
                                        <div>
                                            <strong><?php echo htmlspecialchars($assignment['first_name'] . ' ' . $assignment['last_name']); ?></strong>
                                        </div>
                                        <div class="text-muted small">
                                            <?php echo htmlspecialchars($assignment['registration_number']); ?>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($assignment['title']); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($assignment['assignment_date'])); ?></td>
                                    <td>
                                        <?php
                                        $status_class = '';
                                        $status_text = '';
                                        switch ($assignment['status']) {
                                            case 'pending':
                                                $status_class = 'bg-warning';
                                                $status_text = 'Εκκρεμεί';
                                                break;
                                            case 'active':
                                                $status_class = 'bg-primary';
                                                $status_text = 'Ενεργή';
                                                break;
                                            case 'examination':
                                                $status_class = 'bg-info';
                                                $status_text = 'Εξέταση';
                                                break;
                                            case 'completed':
                                                $status_class = 'bg-success';
                                                $status_text = 'Ολοκληρωμένη';
                                                break;
                                            case 'cancelled':
                                                $status_class = 'bg-danger';
                                                $status_text = 'Ακυρωμένη';
                                                break;
                                        }
                                        ?>
                                        <span class="badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                    </td>
                                    <td>
                                        <?php if (!empty($assignment['deadline_date'])): ?>
                                            <?php 
                                            $deadline = new DateTime($assignment['deadline_date']);
                                            $now = new DateTime();
                                            $interval = $now->diff($deadline);
                                            $days_left = $interval->days;
                                            $is_past = $now > $deadline;
                                            
                                            $deadline_class = '';
                                            if ($is_past) {
                                                $deadline_class = 'text-danger';
                                            } elseif ($days_left <= 30) {
                                                $deadline_class = 'text-warning';
                                            }
                                            ?>
                                            <span class="<?php echo $deadline_class; ?>">
                                                <?php echo date('d/m/Y', strtotime($assignment['deadline_date'])); ?>
                                                <?php if ($is_past): ?>
                                                    <br><small>Έχει παρέλθει</small>
                                                <?php elseif ($days_left <= 30): ?>
                                                    <br><small>Απομένουν <?php echo $days_left; ?> ημέρες</small>
                                                <?php endif; ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">Δεν έχει οριστεί</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="thesis_details.php?id=<?php echo $assignment['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="edit_thesis_assignment.php?id=<?php echo $assignment['id']; ?>" class="btn btn-sm btn-outline-secondary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="thesis_files.php?assignment_id=<?php echo $assignment['id']; ?>" class="btn btn-sm btn-outline-info">
                                                <i class="fas fa-file-alt"></i>
                                            </a>

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
</div>

<?php include '../../templates/footer.php'; ?>

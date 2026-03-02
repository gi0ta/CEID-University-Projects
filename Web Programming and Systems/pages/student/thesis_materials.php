<?php

require_once '../../includes/init.php';

if (!User::isLoggedIn() || User::getCurrentUserRole() !== 'student') {
    header('Location: ' . SITE_URL . '/pages/auth/login.php');
    exit;
}

$user_id = User::getCurrentUserId();
$thesis_id = isset($_GET['thesis_id']) ? (int)$_GET['thesis_id'] : 0;

$db = new Database();
$pdo = $db->getConnection();


$stmt = $pdo->prepare("
    SELECT ta.*, tt.title, tt.summary, s.id as student_id, 
           u.first_name as supervisor_first_name, u.last_name as supervisor_last_name
    FROM thesis_assignments ta 
    JOIN thesis_topics tt ON ta.thesis_topic_id = tt.id
    JOIN students s ON ta.student_id = s.id
    JOIN professors p ON ta.supervisor_id = p.id
    JOIN users u ON p.user_id = u.id
    WHERE ta.id = :thesis_id AND s.user_id = :user_id
");
$stmt->bindParam(':thesis_id', $thesis_id, PDO::PARAM_INT);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$thesis = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$thesis) {
    header('Location: ' . SITE_URL . '/pages/student/dashboard.php');
    exit;
}

if ($thesis['status'] !== 'examination') {
    header('Location: ' . SITE_URL . '/pages/student/dashboard.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT * FROM thesis_files 
    WHERE thesis_assignment_id = :thesis_id 
    ORDER BY upload_date DESC
");
$stmt->bindParam(':thesis_id', $thesis_id, PDO::PARAM_INT);
$stmt->execute();
$files = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("
    SELECT * FROM thesis_external_links 
    WHERE thesis_assignment_id = :thesis_id 
    ORDER BY created_at DESC
");
$stmt->bindParam(':thesis_id', $thesis_id, PDO::PARAM_INT);
$stmt->execute();
$links = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("
    SELECT * FROM thesis_presentations 
    WHERE thesis_assignment_id = :thesis_id 
");
$stmt->bindParam(':thesis_id', $thesis_id, PDO::PARAM_INT);
$stmt->execute();
$presentation = $stmt->fetch(PDO::FETCH_ASSOC);

$pageTitle = 'Διαχείριση Υλικού Διπλωματικής';

$additionalScripts = '<script src="' . SITE_URL . '/assets/js/student/thesis_materials.js"></script>';
?>

<?php include '../../templates/header.php'; ?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2>Διαχείριση Υλικού Διπλωματικής Εργασίας</h2>
            <h4><?php echo htmlspecialchars($thesis['title']); ?></h4>
            <p class="lead"><?php echo htmlspecialchars($thesis['summary']); ?></p>
            <hr>
        </div>
    </div>
    
    <!-- Μηνύματα επιτυχίας/αποτυχίας -->
    <div id="alertMessages"></div>
    
    <!-- Τμήμα για ανέβασμα κειμένου διπλωματικής -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Ανάρτηση Κειμένου Διπλωματικής</h5>
        </div>
        <div class="card-body">
            <form id="draftUploadForm" enctype="multipart/form-data">
                <input type="hidden" name="thesis_id" value="<?php echo $thesis_id; ?>">
                <div class="mb-3">
                    <label for="fileType" class="form-label">Τύπος Αρχείου</label>
                    <select class="form-select" id="fileType" name="file_type" required>
                        <option value="draft">Πρόχειρο</option>
                        <option value="final">Τελικό Κείμενο</option>
                        <option value="presentation">Παρουσίαση</option>
                        <option value="code">Κώδικας</option>
                        <option value="data">Δεδομένα</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="draftDescription" class="form-label">Περιγραφή</label>
                    <textarea class="form-control" id="draftDescription" name="description" rows="2" placeholder="Προαιρετική περιγραφή του αρχείου"></textarea>
                </div>
                <div class="mb-3">
                    <label for="draftFile" class="form-label">Επιλέξτε αρχείο (PDF, DOC, DOCX)</label>
                    <input type="file" class="form-control" id="draftFile" name="file" accept=".pdf,.doc,.docx,.ppt,.pptx,.zip,.rar,.7z" required>
                    <div class="form-text">Μέγιστο μέγεθος: 20MB</div>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-upload"></i> Ανάρτηση Αρχείου
                </button>
            </form>
        </div>
    </div>
    
    <!-- Τμήμα για προσθήκη συνδέσμων -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Προσθήκη Εξωτερικού Συνδέσμου</h5>
        </div>
        <div class="card-body">
            <form id="linkAddForm">
                <input type="hidden" name="thesis_id" value="<?php echo $thesis_id; ?>">
                <div class="mb-3">
                    <label for="linkTitle" class="form-label">Τίτλος Συνδέσμου</label>
                    <input type="text" class="form-control" id="linkTitle" name="title" required>
                </div>
                <div class="mb-3">
                    <label for="linkDescription" class="form-label">Περιγραφή</label>
                    <textarea class="form-control" id="linkDescription" name="description" rows="2" placeholder="Προαιρετική περιγραφή του συνδέσμου"></textarea>
                </div>
                <div class="mb-3">
                    <label for="linkUrl" class="form-label">URL Συνδέσμου</label>
                    <input type="url" class="form-control" id="linkUrl" name="url" placeholder="https://..." required>
                </div>
                <button type="submit" class="btn btn-secondary">
                    <i class="fas fa-link"></i> Προσθήκη Συνδέσμου
                </button>
            </form>
        </div>
    </div>
    
    <!-- Λίστα ανεβασμένων αρχείων -->
    <div class="card mb-4">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0">Ανεβασμένα Αρχεία</h5>
        </div>
        <div class="card-body">
            <?php if (count($files) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Όνομα Αρχείου</th>
                                <th>Τύπος</th>
                                <th>Περιγραφή</th>
                                <th>Ημερομηνία</th>
                                <th>Ενέργειες</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($files as $file): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($file['file_name']); ?></td>
                                <td>
                                    <?php 
                                    switch($file['file_type']) {
                                        case 'draft': echo '<span class="badge bg-secondary">Πρόχειρο</span>'; break;
                                        case 'final': echo '<span class="badge bg-secondary">Τελικό</span>'; break;
                                        case 'presentation': echo '<span class="badge bg-secondary">Παρουσίαση</span>'; break;
                                        case 'code': echo '<span class="badge bg-secondary">Κώδικας</span>'; break;
                                        case 'data': echo '<span class="badge bg-secondary">Δεδομένα</span>'; break;
                                        default: echo '<span class="badge bg-secondary">Άλλο</span>';
                                    }
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($file['description'] ?? ''); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($file['upload_date'])); ?></td>
                                <td>
                                    <a href="<?php echo SITE_URL . '/uploads/thesis_files/' . $file['file_path']; ?>" class="btn btn-sm btn-secondary" target="_blank">
                                        <i class="fas fa-download"></i> Λήψη
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger delete-file" data-id="<?php echo $file['id']; ?>">
                                        <i class="fas fa-trash"></i> Διαγραφή
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    Δεν έχετε αναρτήσει ακόμα αρχεία για τη διπλωματική σας εργασία.
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Λίστα συνδέσμων -->
    <div class="card mb-4">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0">Σύνδεσμοι Εξωτερικού Υλικού</h5>
        </div>
        <div class="card-body">
            <?php if (count($links) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Τίτλος</th>
                                <th>Περιγραφή</th>
                                <th>URL</th>
                                <th>Ημερομηνία</th>
                                <th>Ενέργειες</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($links as $link): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($link['link_title']); ?></td>
                                <td><?php echo htmlspecialchars($link['link_description'] ?? ''); ?></td>
                                <td>
                                    <a href="<?php echo htmlspecialchars($link['link_url']); ?>" target="_blank">
                                        <?php echo htmlspecialchars(substr($link['link_url'], 0, 50) . (strlen($link['link_url']) > 50 ? '...' : '')); ?>
                                    </a>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($link['created_at'])); ?></td>
                                <td>
                                    <a href="<?php echo htmlspecialchars($link['link_url']); ?>" class="btn btn-sm btn-secondary" target="_blank">
                                        <i class="fas fa-external-link-alt"></i> Άνοιγμα
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger delete-link" data-id="<?php echo $link['id']; ?>">
                                        <i class="fas fa-trash"></i> Διαγραφή
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    Δεν έχετε προσθέσει ακόμα συνδέσμους για τη διπλωματική σας εργασία.
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Τμήμα για διαχείριση παρουσίασης διπλωματικής -->
    <div class="card mb-4">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">Προγραμματισμός Παρουσίασης Διπλωματικής</h5>
        </div>
        <div class="card-body">
            <?php if ($presentation): ?>
                <!-- Προβολή στοιχείων παρουσίασης -->
                <div id="presentationView">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <h5>Στοιχεία Παρουσίασης</h5>
                            <hr>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Ημερομηνία & Ώρα:</strong> <?php echo date('d/m/Y H:i', strtotime($presentation['presentation_date'])); ?></p>
                            <p><strong>Τύπος Παρουσίασης:</strong> 
                                <?php 
                                    switch($presentation['presentation_type']) {
                                        case 'physical': echo 'Δια ζώσης'; break;
                                        case 'online': echo 'Διαδικτυακά'; break;
                                        case 'hybrid': echo 'Υβριδικά'; break;
                                        default: echo 'Μη ορισμένος';
                                    }
                                ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <?php if ($presentation['location']): ?>
                                <p><strong>Αίθουσα:</strong> <?php echo htmlspecialchars($presentation['location']); ?></p>
                            <?php endif; ?>
                            <?php if ($presentation['online_link']): ?>
                                <p><strong>Σύνδεσμος:</strong> 
                                    <a href="<?php echo htmlspecialchars($presentation['online_link']); ?>" target="_blank">
                                        <?php echo htmlspecialchars(substr($presentation['online_link'], 0, 50) . (strlen($presentation['online_link']) > 50 ? '...' : '')); ?>
                                    </a>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <button type="button" id="editPresentationBtn" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Επεξεργασία
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Φόρμα επεξεργασίας παρουσίασης (αρχικά κρυμμένη) -->
                <div id="presentationForm" style="display: none;">
                    <form id="thesisPresentationForm">
                        <input type="hidden" name="thesis_id" value="<?php echo $thesis_id; ?>">
                        <input type="hidden" name="presentation_id" value="<?php echo $presentation['id']; ?>">
                        
                        <div class="mb-3">
                            <label for="presentationDate" class="form-label">Ημερομηνία Παρουσίασης</label>
                            <input type="date" class="form-control" id="presentationDate" name="presentationDate" value="<?php echo date('Y-m-d', strtotime($presentation['presentation_date'])); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="presentationTime" class="form-label">Ώρα Παρουσίασης</label>
                            <input type="time" class="form-control" id="presentationTime" name="presentationTime" value="<?php echo date('H:i', strtotime($presentation['presentation_date'])); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="presentationType" class="form-label">Τύπος Παρουσίασης</label>
                            <select class="form-select" id="presentationType" name="presentationType" required>
                                <option value="physical" <?php echo ($presentation['presentation_type'] === 'physical') ? 'selected' : ''; ?>>Δια ζώσης</option>
                                <option value="online" <?php echo ($presentation['presentation_type'] === 'online') ? 'selected' : ''; ?>>Διαδικτυακά</option>
                                <option value="hybrid" <?php echo ($presentation['presentation_type'] === 'hybrid') ? 'selected' : ''; ?>>Υβριδικά</option>
                            </select>
                        </div>
                        
                        <div id="locationField" class="mb-3" <?php echo ($presentation['presentation_type'] === 'online') ? 'style="display: none;"' : ''; ?>>
                            <label for="location" class="form-label">Αίθουσα Παρουσίασης</label>
                            <input type="text" class="form-control" id="location" name="location" value="<?php echo htmlspecialchars($presentation['location'] ?? ''); ?>" <?php echo ($presentation['presentation_type'] === 'physical' || $presentation['presentation_type'] === 'hybrid') ? 'required' : ''; ?>>
                        </div>
                        
                        <div id="onlineLinkField" class="mb-3" <?php echo ($presentation['presentation_type'] === 'physical') ? 'style="display: none;"' : ''; ?>>
                            <label for="onlineLink" class="form-label">Σύνδεσμος Διαδικτυακής Παρουσίασης</label>
                            <input type="url" class="form-control" id="onlineLink" name="onlineLink" value="<?php echo htmlspecialchars($presentation['online_link'] ?? ''); ?>" <?php echo ($presentation['presentation_type'] === 'online' || $presentation['presentation_type'] === 'hybrid') ? 'required' : ''; ?>>
                        </div>
                        
                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Αποθήκευση
                            </button>
                            <button type="button" id="cancelEditBtn" class="btn btn-secondary ms-2">
                                <i class="fas fa-times"></i> Ακύρωση
                            </button>
                        </div>
                    </form>
                </div>
            <?php else: ?>
                <!-- Φόρμα καταχώρησης παρουσίασης -->
                <div id="presentationForm">
                    <div class="alert alert-info mb-4">
                        <i class="fas fa-info-circle"></i> Καταχωρήστε τις πληροφορίες για την παρουσίαση της διπλωματικής σας εργασίας.
                    </div>
                    
                    <form id="thesisPresentationForm">
                        <input type="hidden" name="thesis_id" value="<?php echo $thesis_id; ?>">
                        
                        <div class="mb-3">
                            <label for="presentationDate" class="form-label">Ημερομηνία Παρουσίασης</label>
                            <input type="date" class="form-control" id="presentationDate" name="presentationDate" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="presentationTime" class="form-label">Ώρα Παρουσίασης</label>
                            <input type="time" class="form-control" id="presentationTime" name="presentationTime" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="presentationType" class="form-label">Τύπος Παρουσίασης</label>
                            <select class="form-select" id="presentationType" name="presentationType" required>
                                <option value="" selected disabled>Επιλέξτε τύπο παρουσίασης</option>
                                <option value="physical">Δια ζώσης</option>
                                <option value="online">Διαδικτυακά</option>
                                <option value="hybrid">Υβριδικά</option>
                            </select>
                        </div>
                        
                        <div id="locationField" class="mb-3" style="display: none;">
                            <label for="location" class="form-label">Αίθουσα Παρουσίασης</label>
                            <input type="text" class="form-control" id="location" name="location">
                        </div>
                        
                        <div id="onlineLinkField" class="mb-3" style="display: none;">
                            <label for="onlineLink" class="form-label">Σύνδεσμος Διαδικτυακής Παρουσίασης</label>
                            <input type="url" class="form-control" id="onlineLink" name="onlineLink" placeholder="π.χ. https://zoom.us/j/123456789">
                        </div>
                        
                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Αποθήκευση
                            </button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Κουμπί επιστροφής -->
    <div class="mt-4">
        <a href="dashboard.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Επιστροφή στο Dashboard
        </a>
    </div>
</div>

<?php include '../../templates/footer.php'; ?>

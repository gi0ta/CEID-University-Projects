<?php

require_once '../../includes/init.php';

if (!User::isLoggedIn() || User::getCurrentUserRole() !== 'student') {
    header('Location: ../../index.php');
    exit;
}

$user_id = User::getCurrentUserId();

$db = new Database();
$pdo = $db->getConnection();
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bindParam(1, $user_id, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$db = new Database();
$pdo = $db->getConnection();

$stmt = $pdo->prepare("
    SELECT 
        u.email,
        s.registration_number,
        s.father_name,
        s.semester,
        s.street,
        s.street_number,
        s.city,
        s.postcode,
        s.mobile_phone,
        s.home_phone
    FROM 
        users u
    LEFT JOIN 
        students s ON u.id = s.user_id
    WHERE 
        u.id = ?
");
$stmt->bindParam(1, $user_id, PDO::PARAM_INT);
$stmt->execute();
$profile = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$profile) {
    $profile = [
        'email' => $user['email'] ?? '',
        'registration_number' => '',
        'father_name' => '',
        'semester' => 1,
        'street' => '',
        'street_number' => '',
        'city' => '',
        'postcode' => '',
        'mobile_phone' => '',
        'home_phone' => ''
    ];
}

$pageTitle = 'Επεξεργασία Προφίλ';

include '../../templates/header.php';
?>

<!-- Περιεχόμενο σελίδας -->
<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h4><i class="fas fa-user-edit"></i> Επεξεργασία Στοιχείων Επικοινωνίας</h4>
                </div>
                <div class="card-body">
                    <div id="alert-container"></div>
                    
                    <form id="edit-profile-form">
                        <!-- Προσωπικά Στοιχεία -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h5>Προσωπικά Στοιχεία</h5>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Αριθμός Μητρώου</label>
                                        <p class="form-control-plaintext"><?php echo htmlspecialchars($profile['registration_number']); ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Εξάμηνο</label>
                                        <p class="form-control-plaintext"><?php echo htmlspecialchars($profile['semester']); ?></p>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Όνομα Πατρός</label>
                                    <p class="form-control-plaintext"><?php echo htmlspecialchars($profile['father_name']); ?></p>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Επικοινωνίας</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($profile['email']); ?>" required>
                                    <div class="form-text">Το email σας χρησιμοποιείται για επικοινωνία</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Στοιχεία Επικοινωνίας -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h5>Στοιχεία Επικοινωνίας</h5>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-8">
                                        <label for="street" class="form-label">Οδός</label>
                                        <input type="text" class="form-control" id="street" name="street" value="<?php echo htmlspecialchars($profile['street']); ?>" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="street_number" class="form-label">Αριθμός</label>
                                        <input type="text" class="form-control" id="street_number" name="street_number" value="<?php echo htmlspecialchars($profile['street_number']); ?>" required>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-8">
                                        <label for="city" class="form-label">Πόλη</label>
                                        <input type="text" class="form-control" id="city" name="city" value="<?php echo htmlspecialchars($profile['city']); ?>" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="postcode" class="form-label">Ταχ. Κώδικας</label>
                                        <input type="text" class="form-control" id="postcode" name="postcode" value="<?php echo htmlspecialchars($profile['postcode']); ?>" required>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="mobile_phone" class="form-label">Κινητό Τηλέφωνο</label>
                                        <input type="tel" class="form-control" id="mobile_phone" name="mobile_phone" value="<?php echo htmlspecialchars($profile['mobile_phone']); ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="home_phone" class="form-label">Σταθερό Τηλέφωνο</label>
                                        <input type="tel" class="form-control" id="home_phone" name="home_phone" value="<?php echo htmlspecialchars($profile['home_phone']); ?>">
                                        <div class="form-text">Προαιρετικό</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Αποθήκευση</button>
                            <a href="dashboard.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Επιστροφή</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="../../assets/js/student/edit_profile.js"></script>

<?php
include '../../templates/footer.php';
?>

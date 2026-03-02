<?php

require_once '../../includes/init.php';

if (!User::isLoggedIn() || User::getCurrentUserRole() !== 'student') {
    header('Location: ' . SITE_URL . '/pages/auth/login.php');
    exit;
}

$user_id = User::getCurrentUserId();

$db = new Database();
$pdo = $db->getConnection();
$stmt = $pdo->prepare("SELECT id FROM students WHERE user_id = :user_id");
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    header('Location: dashboard.php');
    exit;
}

$student_id = $student['id'];

$stmt = $pdo->prepare("
    SELECT 
        ta.id,
        tt.title,
        CONCAT(u.first_name, ' ', u.last_name) AS supervisor_name
    FROM 
        thesis_assignments ta
    JOIN 
        thesis_topics tt ON ta.thesis_topic_id = tt.id
    JOIN 
        professors p ON tt.professor_id = p.id
    JOIN 
        users u ON p.user_id = u.id
    WHERE 
        ta.student_id = :student_id AND 
        ta.status = 'pending'
    ORDER BY 
        ta.assignment_date DESC 
    LIMIT 1
");
$stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
$stmt->execute();
$thesis = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$thesis) {
    header('Location: dashboard.php');
    exit;
}

$pageTitle = 'Πρόσκληση Καθηγητή στην Επιτροπή';
include '../../templates/header.php';
?>

<div class="container mt-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="dashboard.php">Αρχική</a></li>
            <li class="breadcrumb-item active" aria-current="page">Πρόσκληση Καθηγητή</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Πρόσκληση Καθηγητή στην Τριμελή Επιτροπή</h5>
                </div>
                <div class="card-body">
                    <p>Επιλέξτε έναν καθηγητή για να τον προσκαλέσετε στην τριμελή επιτροπή της διπλωματικής σας εργασίας:</p>
                    <p><strong>Τίτλος Διπλωματικής:</strong> <?php echo htmlspecialchars($thesis['title']); ?></p>
                    <p><strong>Επιβλέπων:</strong> <?php echo htmlspecialchars($thesis['supervisor_name']); ?></p>
                    
                    <form id="inviteProfessorForm" class="mt-4">
                        <div class="mb-3">
                            <label for="professorSelect" class="form-label">Επιλέξτε Καθηγητή</label>
                            <select class="form-select" id="professorSelect" required>
                                <option value="" selected disabled>Επιλέξτε...</option>
                                <!-- Οι καθηγητές θα φορτωθούν δυναμικά με JavaScript -->
                            </select>
                            <div id="professorSelectError" class="invalid-feedback">
                                Παρακαλώ επιλέξτε έναν καθηγητή.
                            </div>
                        </div>
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                            <a href="dashboard.php" class="btn btn-secondary me-md-2">Ακύρωση</a>
                            <button type="submit" class="btn btn-primary" id="sendInvitationBtn">Αποστολή Πρόσκλησης</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadAvailableProfessors();
    
    document.getElementById('inviteProfessorForm').addEventListener('submit', function(event) {
        event.preventDefault();
        sendInvitation();
    });
});

function loadAvailableProfessors() {
    const selectElement = document.getElementById('professorSelect');
    if (!selectElement) return;
    
    while (selectElement.options.length > 1) {
        selectElement.remove(1);
    }
    
    const loadingOption = document.createElement('option');
    loadingOption.textContent = 'Φόρτωση καθηγητών...';
    loadingOption.disabled = true;
    selectElement.appendChild(loadingOption);
    

    
    fetch('../../api/student/available_professors.php', {
        method: 'GET',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        console.log('Απάντηση API:', response.status, response.statusText);
        return response.json();
    })
    .then(data => {
        console.log('Δεδομένα API:', data);
        
        selectElement.removeChild(loadingOption);
        
        if (data.success) {
            if (data.professors && data.professors.length > 0) {
                data.professors.forEach(professor => {
                    const option = document.createElement('option');
                    option.value = professor.id;
                    option.textContent = professor.name;
                    selectElement.appendChild(option);
                });
            } else {
                const noOption = document.createElement('option');
                noOption.textContent = 'Δεν υπάρχουν διαθέσιμοι καθηγητές';
                noOption.disabled = true;
                selectElement.appendChild(noOption);
            }
        } else {
            console.error('Σφάλμα κατά τη φόρτωση των καθηγητών:', data.message);
            const errorOption = document.createElement('option');
            errorOption.textContent = 'Σφάλμα φόρτωσης καθηγητών';
            errorOption.disabled = true;
            selectElement.appendChild(errorOption);
        }
    })
    .catch(error => {
        console.error('Σφάλμα κατά την επικοινωνία με τον server:', error);
        
        if (selectElement.contains(loadingOption)) {
            selectElement.removeChild(loadingOption);
        }
        
        const errorOption = document.createElement('option');
        errorOption.textContent = 'Σφάλμα επικοινωνίας με τον server';
        errorOption.disabled = true;
        selectElement.appendChild(errorOption);
    });
}

function sendInvitation() {
    const professorSelect = document.getElementById('professorSelect');
    const professorId = professorSelect.value;
    const errorElement = document.getElementById('professorSelectError');
    
    if (!professorId) {
        professorSelect.classList.add('is-invalid');
        return;
    }
    
    professorSelect.classList.remove('is-invalid');
    
    const sendButton = document.getElementById('sendInvitationBtn');
    sendButton.disabled = true;
    sendButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Αποστολή...';
    

    
    fetch('../../api/student/invite_professor.php', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: `professor_id=${professorId}`
    })
    .then(response => {
        console.log('Απάντηση API:', response.status, response.statusText);
        return response.json();
    })
    .then(data => {
        console.log('Δεδομένα API:', data);
        
        if (data.success) {
            alert('Η πρόσκληση στάλθηκε με επιτυχία!');
            window.location.href = 'dashboard.php';
        } else {
            alert(`Σφάλμα: ${data.message}`);
            sendButton.disabled = false;
            sendButton.innerHTML = 'Αποστολή Πρόσκλησης';
            
            if (data.debug) {
                console.log('Λεπτομέρειες σφάλματος:', data.debug);
            }
        }
    })
    .catch(error => {
        console.error('Σφάλμα κατά την επικοινωνία με τον server:', error);
        alert('Παρουσιάστηκε σφάλμα κατά την αποστολή της πρόσκλησης.');
        sendButton.disabled = false;
        sendButton.innerHTML = 'Αποστολή Πρόσκλησης';
    });
}
</script>

<?php include '../../templates/footer.php'; ?>

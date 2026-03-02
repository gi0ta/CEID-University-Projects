<?php

$host = 'localhost';
$dbname = 'diploma_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $json_data = file_get_contents('datafromusidasceid.json');
    
    if ($json_data === false) {
        throw new Exception("Δεν ήταν δυνατή η ανάγνωση του αρχείου JSON");
    }
    
    $data = json_decode($json_data, true);
    
    if ($data === null) {
        throw new Exception("Δεν ήταν δυνατή η αποκωδικοποίηση των δεδομένων JSON");
    }
    
    if (!isset($data['students']) || !isset($data['professors'])) {
        throw new Exception("Τα δεδομένα δεν έχουν την αναμενόμενη δομή");
    }
    
    $pdo->beginTransaction();
    
    $count_professors = 0;
    foreach ($data['professors'] as $professor) {
        $username = strtolower(transliterateName($professor['name']) . '.' . transliterateName($professor['surname']));
        $email = $professor['email'];
        $password = password_hash('password123', PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            $stmt = $pdo->prepare("INSERT INTO users (username, password, email, role, first_name, last_name) VALUES (?, ?, ?, 'professor', ?, ?)");
            $stmt->execute([$username, $password, $email, $professor['name'], $professor['surname']]);
            $user_id = $pdo->lastInsertId();
            
            $stmt = $pdo->prepare("INSERT INTO professors (user_id, department, university, specialty, topic, landline_phone, mobile_phone) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $user_id,
                $professor['department'],
                $professor['university'],
                '',
                $professor['topic'],
                $professor['landline'],
                $professor['mobile']
            ]);
            
            $count_professors++;
        }
        
        if ($count_professors >= 10) {
            break;
        }
    }
    
    $count_students = 0;
    foreach ($data['students'] as $student) {
        $username = 'st' . $student['student_number'];
        $email = $student['email'];
        $password = password_hash('password123', PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            $stmt = $pdo->prepare("INSERT INTO users (username, password, email, role, first_name, last_name) VALUES (?, ?, ?, 'student', ?, ?)");
            $stmt->execute([$username, $password, $email, $student['name'], $student['surname']]);
            $user_id = $pdo->lastInsertId();
            
            $stmt = $pdo->prepare("INSERT INTO students (user_id, registration_number, father_name, semester, street, street_number, city, postcode, mobile_phone, home_phone) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $user_id,
                $student['student_number'],
                $student['father_name'],
                rand(7, 12),
                $student['street'],
                $student['number'],
                $student['city'],
                $student['postcode'],
                $student['mobile_telephone'],
                $student['landline_telephone']
            ]);
            
            $count_students++;
        }
        
        if ($count_students >= 20) {
            break;
        }
    }
    
    $stmt = $pdo->prepare("SELECT id FROM users WHERE role = 'secretary'");
    $stmt->execute();
    $secretary = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$secretary) {
        $stmt = $pdo->prepare("INSERT INTO users (username, password, email, role, first_name, last_name) VALUES (?, ?, ?, 'secretary', ?, ?)");
        $stmt->execute(['secretary', password_hash('password123', PASSWORD_DEFAULT), 'secretary@ceid.upatras.gr', 'Γραμματεία', 'ΤΜΗΥΠ']);
    }
    
    $thesis_topics = [
        ['title' => 'Ανάπτυξη συστήματος αναγνώρισης προσώπου με χρήση βαθιάς μάθησης', 'summary' => 'Σκοπός της διπλωματικής είναι η ανάπτυξη ενός συστήματος αναγνώρισης προσώπου με χρήση τεχνικών βαθιάς μάθησης και συνελικτικών νευρωνικών δικτύων.'],
        ['title' => 'Εφαρμογή μηχανικής μάθησης για την πρόβλεψη τιμών μετοχών', 'summary' => 'Η διπλωματική στοχεύει στην ανάπτυξη ενός συστήματος πρόβλεψης τιμών μετοχών με χρήση αλγορίθμων μηχανικής μάθησης και ανάλυσης χρονοσειρών.'],
        ['title' => 'Σχεδιασμός και ανάπτυξη NoSQL βάσης δεδομένων για Big Data', 'summary' => 'Αντικείμενο της διπλωματικής είναι ο σχεδιασμός και η ανάπτυξη μιας NoSQL βάσης δεδομένων για την αποθήκευση και επεξεργασία μεγάλου όγκου δεδομένων.'],
        ['title' => 'Βελτιστοποίηση ερωτημάτων SQL σε σχεσιακές βάσεις δεδομένων', 'summary' => 'Η διπλωματική εστιάζει στη μελέτη και εφαρμογή τεχνικών βελτιστοποίησης ερωτημάτων SQL για την αύξηση της απόδοσης σχεσιακών βάσεων δεδομένων.'],
        ['title' => 'Ανάπτυξη συστήματος ασφαλούς επικοινωνίας με χρήση κρυπτογραφίας', 'summary' => 'Σκοπός της διπλωματικής είναι η ανάπτυξη ενός συστήματος ασφαλούς επικοινωνίας με χρήση σύγχρονων κρυπτογραφικών αλγορίθμων.'],
        ['title' => 'Μελέτη και υλοποίηση πρωτοκόλλων δρομολόγησης σε ασύρματα δίκτυα αισθητήρων', 'summary' => 'Η διπλωματική στοχεύει στη μελέτη και υλοποίηση πρωτοκόλλων δρομολόγησης για την αποδοτική επικοινωνία σε ασύρματα δίκτυα αισθητήρων.'],
        ['title' => 'Ανάπτυξη εφαρμογής για την παρακολούθηση και διαχείριση πόρων σε περιβάλλον cloud', 'summary' => 'Αντικείμενο της διπλωματικής είναι η ανάπτυξη μιας εφαρμογής για την παρακολούθηση και διαχείριση πόρων σε περιβάλλον υπολογιστικού νέφους.'],
        ['title' => 'Μελέτη και υλοποίηση τεχνικών εικονικοποίησης για βελτίωση της απόδοσης συστημάτων', 'summary' => 'Η διπλωματική εστιάζει στη μελέτη και υλοποίηση τεχνικών εικονικοποίησης για τη βελτίωση της απόδοσης υπολογιστικών συστημάτων.'],
        ['title' => 'Ανάπτυξη αλγορίθμων για την επίλυση προβλημάτων βελτιστοποίησης σε γραφήματα', 'summary' => 'Σκοπός της διπλωματικής είναι η ανάπτυξη και αξιολόγηση αλγορίθμων για την επίλυση προβλημάτων βελτιστοποίησης σε γραφήματα.'],
        ['title' => 'Μελέτη και υλοποίηση παράλληλων αλγορίθμων για επεξεργασία μεγάλου όγκου δεδομένων', 'summary' => 'Η διπλωματική στοχεύει στη μελέτη και υλοποίηση παράλληλων αλγορίθμων για την αποδοτική επεξεργασία μεγάλου όγκου δεδομένων.']
    ];
    
    $stmt = $pdo->prepare("SELECT id FROM professors ORDER BY id LIMIT 10");
    $stmt->execute();
    $professor_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($thesis_topics as $index => $topic) {
        $professor_id = $professor_ids[$index % count($professor_ids)];
        
        $stmt = $pdo->prepare("INSERT INTO thesis_topics (professor_id, title, summary, is_assigned) VALUES (?, ?, ?, FALSE)");
        $stmt->execute([$professor_id, $topic['title'], $topic['summary']]);
    }
    
    $stmt = $pdo->prepare("SELECT id FROM students ORDER BY id LIMIT 10");
    $stmt->execute();
    $student_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $stmt = $pdo->prepare("SELECT id, professor_id FROM thesis_topics ORDER BY id LIMIT 6");
    $stmt->execute();
    $thesis_topics = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $statuses = ['pending', 'active', 'examination', 'completed', 'cancelled', 'active'];
    
    foreach ($thesis_topics as $index => $topic) {
        $student_id = $student_ids[$index];
        $supervisor_id = $topic['professor_id'];
        $status = $statuses[$index];
        
        $assignment_date = date('Y-m-d', strtotime('-' . rand(1, 12) . ' months'));
        $completion_date = ($status == 'completed') ? date('Y-m-d', strtotime('-' . rand(1, 30) . ' days')) : null;
        
        $gs_number = ($status != 'pending') ? rand(100, 200) : null;
        $gs_year = ($status != 'pending') ? date('Y') : null;
        
        $final_grade = ($status == 'completed') ? rand(5, 10) : null;
        
        $repository_link = ($status == 'completed') ? 'https://nemertes.library.upatras.gr/thesis/' . rand(10000, 99999) : null;
        
        $stmt = $pdo->prepare("INSERT INTO thesis_assignments (thesis_topic_id, student_id, supervisor_id, status, assignment_date, completion_date, gs_number, gs_year, final_grade, repository_link) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $topic['id'],
            $student_id,
            $supervisor_id,
            $status,
            $assignment_date,
            $completion_date,
            $gs_number,
            $gs_year,
            $final_grade,
            $repository_link
        ]);
        
        $thesis_assignment_id = $pdo->lastInsertId();
        
        $stmt = $pdo->prepare("UPDATE thesis_topics SET is_assigned = TRUE WHERE id = ?");
        $stmt->execute([$topic['id']]);
        
        if ($status != 'pending') {
            $available_professors = array_filter($professor_ids, function($id) use ($supervisor_id) {
                return $id != $supervisor_id;
            });
            shuffle($available_professors);
            $committee_members = array_slice($available_professors, 0, 2);
            
            foreach ($committee_members as $professor_id) {
                $invitation_date = date('Y-m-d', strtotime($assignment_date . ' +1 day'));
                $response_date = date('Y-m-d', strtotime($invitation_date . ' +' . rand(1, 5) . ' days'));
                $member_status = 'accepted';
                
                $stmt = $pdo->prepare("INSERT INTO committee_members (thesis_assignment_id, professor_id, invitation_date, response_date, status) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$thesis_assignment_id, $professor_id, $invitation_date, $response_date, $member_status]);
            }
        }
        
        $stmt = $pdo->prepare("INSERT INTO status_changes (thesis_assignment_id, old_status, new_status, changed_by, notes) VALUES (?, NULL, 'pending', ?, 'Αρχική ανάθεση θέματος')");
        $stmt->execute([$thesis_assignment_id, $supervisor_id]);
        
        if ($status != 'pending') {
            $stmt = $pdo->prepare("INSERT INTO status_changes (thesis_assignment_id, old_status, new_status, changed_by, notes) VALUES (?, 'pending', 'active', ?, 'Ενεργοποίηση διπλωματικής μετά την αποδοχή από την τριμελή')");
            $stmt->execute([$thesis_assignment_id, $supervisor_id]);
        }
        
        if ($status == 'examination' || $status == 'completed') {
            $stmt = $pdo->prepare("INSERT INTO status_changes (thesis_assignment_id, old_status, new_status, changed_by, notes) VALUES (?, 'active', 'examination', ?, 'Η διπλωματική είναι έτοιμη για εξέταση')");
            $stmt->execute([$thesis_assignment_id, $supervisor_id]);
        }
        
        if ($status == 'completed') {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE role = 'secretary' LIMIT 1");
            $stmt->execute();
            $secretary_id = $stmt->fetchColumn();
            
            $stmt = $pdo->prepare("INSERT INTO status_changes (thesis_assignment_id, old_status, new_status, changed_by, notes) VALUES (?, 'examination', 'completed', ?, 'Η διπλωματική ολοκληρώθηκε επιτυχώς')");
            $stmt->execute([$thesis_assignment_id, $secretary_id]);
            
            foreach ([$supervisor_id, $committee_members[0], $committee_members[1]] as $grader_id) {
                $subject_understanding = rand(8, 10);
                $problem_solving = rand(8, 10);
                $implementation = rand(8, 10);
                $presentation = rand(8, 10);
                $grade = round(($subject_understanding + $problem_solving + $implementation + $presentation) / 4, 1);
                
                $stmt = $pdo->prepare("INSERT INTO thesis_grades (thesis_assignment_id, professor_id, subject_understanding, problem_solving, implementation, presentation, final_grade) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$thesis_assignment_id, $grader_id, $subject_understanding, $problem_solving, $implementation, $presentation, $grade]);
            }
        }
        
        if ($status == 'cancelled') {
            $stmt = $pdo->prepare("INSERT INTO status_changes (thesis_assignment_id, old_status, new_status, changed_by, notes) VALUES (?, 'active', 'cancelled', ?, 'Ακύρωση διπλωματικής λόγω μη προόδου του φοιτητή')");
            $stmt->execute([$thesis_assignment_id, $supervisor_id]);
            
            $stmt = $pdo->prepare("UPDATE thesis_assignments SET cancellation_date = ?, cancellation_reason = 'Μη πρόοδος του φοιτητή' WHERE id = ?");
            $stmt->execute([date('Y-m-d', strtotime('-' . rand(1, 30) . ' days')), $thesis_assignment_id]);
        }
        
        if ($status == 'examination' || $status == 'completed') {
            $presentation_date = ($status == 'completed') ? date('Y-m-d H:i:s', strtotime($completion_date . ' -1 day')) : date('Y-m-d H:i:s', strtotime('+' . rand(7, 30) . ' days'));
            $presentation_type = (rand(0, 1) == 0) ? 'physical' : 'online';
            $location = ($presentation_type == 'physical') ? 'Αίθουσα Β' . rand(1, 5) : null;
            $online_link = ($presentation_type == 'online') ? 'https://upatras-gr.zoom.us/j/' . rand(10000000000, 99999999999) : null;
            
            $stmt = $pdo->prepare("INSERT INTO thesis_presentations (thesis_assignment_id, presentation_date, location, online_link, presentation_type) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$thesis_assignment_id, $presentation_date, $location, $online_link, $presentation_type]);
            
            $stmt = $pdo->prepare("SELECT u.first_name, u.last_name FROM users u JOIN students s ON u.id = s.user_id WHERE s.id = ?");
            $stmt->execute([$student_id]);
            $student = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $stmt = $pdo->prepare("SELECT u.first_name, u.last_name FROM users u JOIN professors p ON u.id = p.user_id WHERE p.id = ?");
            $stmt->execute([$supervisor_id]);
            $supervisor = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $stmt = $pdo->prepare("SELECT title FROM thesis_topics WHERE id = ?");
            $stmt->execute([$topic['id']]);
            $thesis_title = $stmt->fetchColumn();
            
            $announcement_text = "Σας προσκαλούμε στην παρουσίαση της διπλωματικής εργασίας ";
            $announcement_text .= "του/της " . $student['first_name'] . " " . $student['last_name'] . " ";
            $announcement_text .= "με θέμα \"" . $thesis_title . "\". ";
            
            if ($presentation_type == 'physical') {
                $announcement_text .= "Η παρουσίαση θα πραγματοποιηθεί στις " . date('d/m/Y H:i', strtotime($presentation_date)) . " στην " . $location . ".";
            } else {
                $announcement_text .= "Η παρουσίαση θα πραγματοποιηθεί διαδικτυακά στις " . date('d/m/Y H:i', strtotime($presentation_date)) . " μέσω του συνδέσμου: " . $online_link . ".";
            }
            
            $stmt = $pdo->prepare("INSERT INTO public_presentation_announcements (thesis_assignment_id, title, student_name, supervisor_name, presentation_date, location, online_link, announcement_text) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $thesis_assignment_id,
                $thesis_title,
                $student['first_name'] . " " . $student['last_name'],
                $supervisor['first_name'] . " " . $supervisor['last_name'],
                $presentation_date,
                $location,
                $online_link,
                $announcement_text
            ]);
        }
    }
    
    $pdo->commit();
    
    echo "Η εισαγωγή των δεδομένων ολοκληρώθηκε με επιτυχία!\n";
    echo "Εισήχθησαν $count_professors καθηγητές και $count_students φοιτητές.\n";
    echo "Δημιουργήθηκαν " . count($thesis_topics) . " θέματα διπλωματικών και 6 αναθέσεις σε διάφορες καταστάσεις.\n";
    
} catch (Exception $e) {
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    
    echo "Σφάλμα: " . $e->getMessage() . "\n";
}

function transliterateName($name) {
    $greek = array('α', 'β', 'γ', 'δ', 'ε', 'ζ', 'η', 'θ', 'ι', 'κ', 'λ', 'μ', 'ν', 'ξ', 'ο', 'π', 'ρ', 'σ', 'τ', 'υ', 'φ', 'χ', 'ψ', 'ω', 'ά', 'έ', 'ή', 'ί', 'ό', 'ύ', 'ώ', 'ϊ', 'ϋ', 'ΐ', 'ΰ', 'ς', 'Α', 'Β', 'Γ', 'Δ', 'Ε', 'Ζ', 'Η', 'Θ', 'Ι', 'Κ', 'Λ', 'Μ', 'Ν', 'Ξ', 'Ο', 'Π', 'Ρ', 'Σ', 'Τ', 'Υ', 'Φ', 'Χ', 'Ψ', 'Ω', 'Ά', 'Έ', 'Ή', 'Ί', 'Ό', 'Ύ', 'Ώ', 'Ϊ', 'Ϋ');
    $latin = array('a', 'b', 'g', 'd', 'e', 'z', 'i', 'th', 'i', 'k', 'l', 'm', 'n', 'x', 'o', 'p', 'r', 's', 't', 'y', 'f', 'ch', 'ps', 'o', 'a', 'e', 'i', 'i', 'o', 'y', 'o', 'i', 'y', 'i', 'y', 's', 'A', 'B', 'G', 'D', 'E', 'Z', 'I', 'TH', 'I', 'K', 'L', 'M', 'N', 'X', 'O', 'P', 'R', 'S', 'T', 'Y', 'F', 'CH', 'PS', 'O', 'A', 'E', 'I', 'I', 'O', 'Y', 'O', 'I', 'Y');
    
    return str_replace($greek, $latin, strtolower($name));
}
?>

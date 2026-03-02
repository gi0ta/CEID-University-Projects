
document.addEventListener('DOMContentLoaded', function() {
    const searchForm = document.getElementById('student-search-form');
    const searchTerm = document.getElementById('search-term');
    const searchResults = document.getElementById('search-results');
    const selectedStudent = document.getElementById('selected-student');
    const assignForm = document.getElementById('assign-form');
    const studentIdInput = document.getElementById('student-id');
    
    searchForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const term = searchTerm.value.trim();
        
        if (term.length < 3) {
            searchResults.innerHTML = `
                <div class="alert alert-warning" role="alert">
                    Παρακαλώ εισάγετε τουλάχιστον 3 χαρακτήρες για αναζήτηση.
                </div>
            `;
            return;
        }
        
        searchResults.innerHTML = `
            <div class="d-flex justify-content-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Φόρτωση...</span>
                </div>
            </div>
        `;
        
        fetch(window.location.origin + '/web_php/api/professor/search_students.php?term=' + encodeURIComponent(term), {
            method: 'GET',
            credentials: 'include',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Σφάλμα δικτύου κατά την επικοινωνία με τον διακομιστή.');
                }
                return response.json();
            })
            .then(data => {
                if (!data.success) {
                    throw new Error(data.message || 'Σφάλμα κατά την αναζήτηση φοιτητών.');
                }
                
                displaySearchResults(data.data);
            })
            .catch(error => {
                searchResults.innerHTML = `
                    <div class="alert alert-danger" role="alert">
                        <h4 class="alert-heading">Σφάλμα!</h4>
                        <p>${error.message}</p>
                        <hr>
                        <p class="mb-0">Παρακαλώ προσπαθήστε ξανά αργότερα ή επικοινωνήστε με τον διαχειριστή.</p>
                    </div>
                `;
            });
    });
    
    assignForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const topicId = document.getElementById('topic-id').value;
        const studentId = studentIdInput.value;
        
        if (!topicId || !studentId) {
            alert('Παρακαλώ επιλέξτε φοιτητή για την ανάθεση.');
            return;
        }
        
        const submitButton = this.querySelector('button[type="submit"]');
        const originalButtonText = submitButton.innerHTML;
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Υποβολή...';
        
        const formData = new FormData();
        formData.append('topic_id', topicId);
        formData.append('student_id', studentId);
        
        fetch(window.location.origin + '/web_php/api/professor/assign_thesis.php', {
            method: 'POST',
            body: formData
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Σφάλμα δικτύου κατά την επικοινωνία με τον διακομιστή.');
                }
                return response.json();
            })
            .then(data => {
                if (!data.success) {
                    throw new Error(data.message || 'Σφάλμα κατά την ανάθεση θέματος.');
                }
                
                window.location.href = window.location.origin + '/web_php/pages/professor/assign_topic_list.php?success=1&message=' + encodeURIComponent('Το θέμα ανατέθηκε με επιτυχία στον φοιτητή.');
            })
            .catch(error => {
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonText;
                
                alert('Σφάλμα: ' + error.message);
            });
    });
    
    function displaySearchResults(students) {
        if (students.length === 0) {
            searchResults.innerHTML = `
                <div class="alert alert-info" role="alert">
                    <h4 class="alert-heading">Δεν βρέθηκαν φοιτητές!</h4>
                    <p>Δεν βρέθηκαν φοιτητές που να ταιριάζουν με τα κριτήρια αναζήτησης.</p>
                    <hr>
                    <p class="mb-0">Δοκιμάστε με διαφορετικούς όρους αναζήτησης.</p>
                </div>
            `;
            return;
        }
        
        let html = `
            <h4>Αποτελέσματα Αναζήτησης</h4>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ΑΜ</th>
                            <th>Ονοματεπώνυμο</th>
                            <th>Email</th>
                            <th>Ενέργειες</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        
        students.forEach(student => {
            html += `
                <tr>
                    <td>${escapeHtml(student.registration_number)}</td>
                    <td>${escapeHtml(student.fullname)}</td>
                    <td>${escapeHtml(student.email)}</td>
                    <td>
                        <button class="btn btn-sm btn-primary select-student" data-student-id="${student.id}" data-student-name="${escapeHtml(student.fullname)}" data-student-am="${escapeHtml(student.registration_number)}">
                            <i class="bi bi-check-circle"></i> Επιλογή
                        </button>
                    </td>
                </tr>
            `;
        });
        
        html += `
                    </tbody>
                </table>
            </div>
        `;
        
        searchResults.innerHTML = html;
        
        const selectButtons = document.querySelectorAll('.select-student');
        selectButtons.forEach(button => {
            button.addEventListener('click', function() {
                const studentId = this.getAttribute('data-student-id');
                const studentName = this.getAttribute('data-student-name');
                const studentAM = this.getAttribute('data-student-am');
                
                selectedStudent.innerHTML = `
                    <div class="alert alert-success" role="alert">
                        <h4 class="alert-heading">Επιλεγμένος Φοιτητής</h4>
                        <p><strong>ΑΜ:</strong> ${escapeHtml(studentAM)}</p>
                        <p><strong>Ονοματεπώνυμο:</strong> ${escapeHtml(studentName)}</p>
                    </div>
                `;
                
                studentIdInput.value = studentId;
                
                assignForm.style.display = 'block';
                
                assignForm.scrollIntoView({ behavior: 'smooth' });
            });
        });
    }
    
    function escapeHtml(text) {
        if (!text) return '';
        
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }
});

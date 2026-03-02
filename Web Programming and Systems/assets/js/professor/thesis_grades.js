
document.addEventListener('DOMContentLoaded', function() {
    console.log('Thesis grades script loaded');
    
    const gradesContainer = document.getElementById('grades-container');
    const gradesLoading = document.getElementById('grades-loading');
    const gradesError = document.getElementById('grades-error');
    const gradesErrorMessage = document.getElementById('grades-error-message');
    
    let thesisId = null;
    let gradingStatus = null;
    
    if (gradesContainer && gradesLoading) {
        const urlParams = new URLSearchParams(window.location.search);
        thesisId = urlParams.get('id');
        
        if (thesisId) {
            loadThesisGrades(thesisId);
            
            setInterval(() => {
                loadThesisGrades(thesisId, true);
            }, 120000);
        } else {
            showError('Δεν βρέθηκε το ID της διπλωματικής εργασίας.');
        }
    }
    
    function loadThesisGrades(thesisId, silent = false) {
        console.log('Loading thesis grades for ID:', thesisId);
        
        if (!silent) {
            gradesLoading.classList.remove('d-none');
            gradesContainer.classList.add('d-none');
            gradesError.classList.add('d-none');
        }
        
        fetch(`../../api/professor/get_thesis_grades.php?thesis_id=${thesisId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Grades data received:', data);
                
                if (data.success) {
                    gradingStatus = data.grading_status;
                    
                    if (!silent) {
                        gradesLoading.classList.add('d-none');
                    }
                    
                    if (data.grades && data.grades.length > 0) {
                        displayGrades(data.grades, data.final_grade, data.grading_status);
                    } else if (!silent) {
                        gradesContainer.innerHTML = '<div class="alert alert-info"><i class="bi bi-info-circle me-2"></i>Δεν έχουν καταχωρηθεί ακόμα βαθμολογίες για αυτή τη διπλωματική εργασία.</div>';
                        gradesContainer.classList.remove('d-none');
                    }
                } else if (!silent) {
                    showError(data.message || 'Σφάλμα κατά τη φόρτωση των βαθμολογιών.');
                }
            })
            .catch(error => {
                console.error('Error loading grades:', error);
                if (!silent) {
                    showError('Σφάλμα κατά την επικοινωνία με τον διακομιστή.');
                }
            });
    }
    
    function displayGrades(grades, finalGrade, status) {
        let html = `
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Καθηγητής</th>
                            <th>Ρόλος</th>
                            <th>Κατανόηση Αντικειμένου (60%)</th>
                            <th>Επίλυση Προβλήματος (15%)</th>
                            <th>Ποιότητα Κειμένου (15%)</th>
                            <th>Παρουσίαση (10%)</th>
                            <th>Τελικός Βαθμός</th>
                            <th>Ημερομηνία</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        
        grades.forEach(grade => {
            const role = grade.role === 'supervisor' ? 'Επιβλέπων/ουσα' : 'Μέλος Επιτροπής';
            const roleClass = grade.role === 'supervisor' ? 'bg-primary' : 'bg-info';
            
            html += `
                <tr>
                    <td>${escapeHtml(grade.professor_name)}</td>
                    <td><span class="badge ${roleClass}">${role}</span></td>
                    <td>${grade.subject_understanding}</td>
                    <td>${grade.problem_solving}</td>
                    <td>${grade.writing_quality}</td>
                    <td>${grade.presentation}</td>
                    <td><strong>${grade.final_grade}</strong></td>
                    <td>${formatDate(grade.grading_date)}</td>
                </tr>
            `;
            
            if (grade.comments && grade.comments.trim() !== '') {
                html += `
                    <tr class="table-light">
                        <td colspan="8">
                            <strong>Σχόλια:</strong> ${escapeHtml(grade.comments)}
                        </td>
                    </tr>
                `;
            }
        });
        
        html += `
                    </tbody>
                </table>
            </div>
        `;
        
        if (finalGrade !== null) {
            html += `
                <div class="alert alert-success mt-3">
                    <h5 class="mb-0"><i class="bi bi-award me-2"></i>Τελικός Βαθμός Διπλωματικής: <strong>${finalGrade}</strong></h5>
                </div>
            `;
        }
        
        if (status && status.all_graded && status.is_supervisor && status.thesis_status === 'examination') {
            html += `
                <div class="alert alert-info mt-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0"><i class="bi bi-check-circle me-2"></i>Όλα τα μέλη της επιτροπής έχουν υποβάλει βαθμολογία</h5>
                            <p class="mb-0 mt-2">Μπορείτε να δημιουργήσετε το πρακτικό εξέτασης της διπλωματικής εργασίας.</p>
                        </div>
                        <button id="generate-protocol-btn" class="btn btn-primary">
                            <i class="bi bi-file-earmark-text me-2"></i>Δημιουργία Πρακτικού
                        </button>
                    </div>
                </div>
            `;
        }
        
        gradesContainer.innerHTML = html;
        gradesContainer.classList.remove('d-none');
        
        if (status && status.all_graded && status.is_supervisor) {
            const generateProtocolBtn = document.getElementById('generate-protocol-btn');
            if (generateProtocolBtn) {
                generateProtocolBtn.addEventListener('click', function() {
                    generateExaminationProtocol(thesisId);
                });
            }
        }
    }
    
    function showError(message) {
        gradesLoading.classList.add('d-none');
        gradesContainer.classList.add('d-none');
        gradesErrorMessage.textContent = message;
        gradesError.classList.remove('d-none');
    }
    
    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('el-GR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }
    
    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }
    
    function updateGradingButton(status) {
        const saveGradeBtn = document.getElementById('save-grade-btn');
        const gradingForm = document.getElementById('thesis-grading-form');
        const gradingFormContainer = document.getElementById('grading-form-container');
        
        if (!saveGradeBtn || !gradingForm || !gradingFormContainer || !status) {
            return;
        }
        
        const gradingStatusSection = document.getElementById('grading-status-section');
        if (gradingStatusSection) {
            console.log('Found grading-status-section. Skipping DOM manipulation.');
            return;
        }
        
    }
    
    function generateExaminationProtocol(thesisId) {
        const gradesContainer = document.getElementById('grades-container');
        const loadingDiv = document.createElement('div');
        loadingDiv.id = 'protocol-loading';
        loadingDiv.className = 'alert alert-info';
        loadingDiv.innerHTML = '<i class="bi bi-hourglass-split me-2"></i> Δημιουργία πρωτοκόλλου εξέτασης...';
        gradesContainer.appendChild(loadingDiv);
        
        const generateProtocolBtn = document.getElementById('generate-protocol-btn');
        if (generateProtocolBtn) {
            generateProtocolBtn.disabled = true;
            generateProtocolBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i> Παρακαλώ περιμένετε...';
        }
        
        const formData = new FormData();
        formData.append('thesis_id', thesisId);
        
        console.log('Sending request to generate protocol for thesis ID:', thesisId);
        fetch('../../api/professor/generate_examination_protocol.php', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(response => {
            console.log('Response status:', response.status);
            console.log('Response headers:', response.headers);
            
            return response.text().then(text => {
                console.log('Raw response:', text);
                if (!text) {
                    throw new Error('Empty response from server');
                }
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('JSON parse error:', e);
                    throw new Error('Invalid JSON response: ' + text);
                }
            });
        })
        .then(data => {
            const loadingElement = document.getElementById('protocol-loading');
            if (loadingElement) {
                loadingElement.remove();
            }
            
            if (generateProtocolBtn) {
                generateProtocolBtn.disabled = false;
                generateProtocolBtn.innerHTML = '<i class="bi bi-file-earmark-text me-2"></i> Δημιουργία Πρωτοκόλλου Εξέτασης';
            }
            
            if (data.success) {
                const protocolWindow = window.open('', '_blank');
                protocolWindow.document.write(data.protocol_html);
                protocolWindow.document.close();
                
                const successDiv = document.createElement('div');
                successDiv.className = 'alert alert-success';
                successDiv.innerHTML = `<i class="bi bi-check-circle me-2"></i> ${data.message}`;
                gradesContainer.appendChild(successDiv);
                
                setTimeout(() => {
                    successDiv.remove();
                }, 5000);
            } else {
                const errorDiv = document.createElement('div');
                errorDiv.className = 'alert alert-danger';
                errorDiv.innerHTML = `<i class="bi bi-exclamation-triangle me-2"></i> ${data.message}`;
                gradesContainer.appendChild(errorDiv);
                
                setTimeout(() => {
                    errorDiv.remove();
                }, 5000);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            
            const loadingElement = document.getElementById('protocol-loading');
            if (loadingElement) {
                loadingElement.remove();
            }
            
            if (generateProtocolBtn) {
                generateProtocolBtn.disabled = false;
                generateProtocolBtn.innerHTML = '<i class="bi bi-file-earmark-text me-2"></i> Δημιουργία Πρωτοκόλλου Εξέτασης';
            }
            
            const errorDiv = document.createElement('div');
            errorDiv.className = 'alert alert-danger';
            errorDiv.innerHTML = `<i class="bi bi-exclamation-triangle me-2"></i> Σφάλμα κατά τη δημιουργία του πρωτοκόλλου: ${error.message}`;
            gradesContainer.appendChild(errorDiv);
            
            setTimeout(() => {
                errorDiv.remove();
            }, 5000);
        });
    }
});

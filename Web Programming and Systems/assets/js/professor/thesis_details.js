
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const thesisId = urlParams.get('id');
    
    if (!thesisId) {
        showError('Δεν βρέθηκε ID διπλωματικής εργασίας στο URL.');
        return;
    }
    
    loadThesisDetails(thesisId);
});

function loadThesisDetails(thesisId) {
    const siteUrl = window.location.origin + '/web_php';
    const url = `${siteUrl}/api/professor/thesis_list.php`;
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const thesis = data.data.find(t => String(t.assignment_id) === String(thesisId));
                
                if (thesis) {
                    displayThesisDetails(thesis);
                } else {
                    showError(`Δεν βρέθηκε διπλωματική εργασία με ID: ${thesisId}`);
                }
            } else {
                showError(data.message || 'Σφάλμα κατά τη φόρτωση των λεπτομερειών της διπλωματικής εργασίας.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('Σφάλμα κατά τη φόρτωση των λεπτομερειών της διπλωματικής εργασίας. Παρακαλώ δοκιμάστε ξανά αργότερα.');
        });
}

function displayThesisDetails(thesis) {
    document.getElementById('thesis-title').textContent = thesis.thesis_title;
    
    let statusText = '';
    let statusBadgeClass = 'bg-primary';
    
    switch(thesis.status) {
        case 'pending':
            statusText = 'Υπό ανάθεση';
            statusBadgeClass = 'bg-warning text-dark';
            break;
        case 'active':
            statusText = 'Ενεργή';
            statusBadgeClass = 'bg-success';
            break;
        case 'completed':
            statusText = 'Περατωμένη';
            statusBadgeClass = 'bg-info text-dark';
            break;
        case 'rejected':
            statusText = 'Απορριφθείσα';
            statusBadgeClass = 'bg-danger';
            break;
        case 'canceled':
            statusText = 'Ακυρωμένη';
            statusBadgeClass = 'bg-danger';
            break;
        case 'examination':
            statusText = 'Υπό εξέταση';
            statusBadgeClass = 'bg-primary';
            break;
        default:
            statusText = thesis.status;
    }
    
    const roleText = thesis.professor_role === 'supervisor' ? 'Επιβλέπων' : 'Μέλος τριμελούς';
    
    let detailsHtml = `
        <div class="row mb-4">
            <div class="col-md-8">
                <h3>Βασικές Πληροφορίες</h3>
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="card-title mb-0">${thesis.thesis_title}</h4>
                            <span class="badge ${statusBadgeClass}">${statusText}</span>
                        </div>
                        <p class="card-text">${thesis.thesis_summary}</p>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Φοιτητής:</strong> ${thesis.student_name} (${thesis.student_am})</p>
                                <p><strong>Ρόλος Καθηγητή:</strong> ${roleText}</p>
                                <p><strong>Ημερομηνία Ανάθεσης:</strong> ${new Date(thesis.created_at).toLocaleDateString('el-GR')}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Επιβλέπων:</strong> ${thesis.supervisor ? thesis.supervisor.professor_name : 'Μη διαθέσιμο'}</p>
                                <p><strong>Προθεσμία:</strong> ${thesis.updated_at ? new Date(thesis.updated_at).toLocaleDateString('el-GR') : 'Μη διαθέσιμο'}</p>
                                ${thesis.grade ? `<p><strong>Βαθμός:</strong> ${thesis.grade}</p>` : ''}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <h3>Μέλη Τριμελούς</h3>
                <div class="card">
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">
                                <strong>Επιβλέπων:</strong><br>
                                ${thesis.supervisor ? thesis.supervisor.professor_name : 'Μη διαθέσιμο'}
                            </li>
                            ${thesis.committee_members && thesis.committee_members.length > 0 ? 
                                thesis.committee_members.map(member => `
                                    <li class="list-group-item">
                                        <strong>Μέλος:</strong><br>
                                        ${member.professor_name}
                                    </li>
                                `).join('') : 
                                '<li class="list-group-item">Δεν έχουν οριστεί μέλη τριμελούς</li>'
                            }
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mb-4">
            <div class="col-md-8">
                <h3>Χρονολόγιο Ενεργειών</h3>
                <div class="card">
                    <div class="card-body">
                        <div class="timeline">
                            ${thesis.timeline && thesis.timeline.length > 0 ? 
                                thesis.timeline.map((event, index) => `
                                    <div class="timeline-item">
                                        <div class="timeline-marker"></div>
                                        <div class="timeline-content">
                                            <h4 class="timeline-title">
                                                ${getStatusText(event.status)}
                                                <span class="badge bg-secondary">${new Date(event.created_at).toLocaleDateString('el-GR')}</span>
                                            </h4>
                                            <p>${event.notes || 'Χωρίς σχόλια'}</p>
                                        </div>
                                    </div>
                                `).join('') : 
                                '<p>Δεν υπάρχουν καταγεγραμμένες ενέργειες</p>'
                            }
                        </div>
                    </div>
                </div>
            </div>
            
            ${(thesis.status === 'completed' || thesis.status === 'examination') ? `
                <div class="col-md-4">
                    <h3>Αρχεία</h3>
                    <div class="card">
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                ${thesis.final_report_file ? `
                                    <li class="list-group-item">
                                        <a href="${siteUrl}/${thesis.final_report_file}" target="_blank" class="btn btn-sm btn-outline-primary w-100">
                                            <i class="bi bi-file-earmark-pdf"></i> Τελικό Κείμενο Διπλωματικής
                                        </a>
                                    </li>
                                ` : ''}
                                
                                ${thesis.grade_protocol_file ? `
                                    <li class="list-group-item">
                                        <a href="${siteUrl}/${thesis.grade_protocol_file}" target="_blank" class="btn btn-sm btn-outline-primary w-100">
                                            <i class="bi bi-file-earmark-pdf"></i> Πρακτικό Βαθμολόγησης
                                        </a>
                                    </li>
                                ` : ''}
                            </ul>
                        </div>
                    </div>
                </div>
            ` : ''}
        </div>
    `;
    
    const detailsContent = document.getElementById('thesis-details-content');
    detailsContent.innerHTML = detailsHtml;
    detailsContent.classList.remove('d-none');
    
    document.getElementById('thesis-loading').classList.add('d-none');
    
    addTimelineStyles();
}

function getStatusText(status) {
    switch(status) {
        case 'pending':
            return 'Υπό ανάθεση';
        case 'active':
            return 'Ενεργή';
        case 'completed':
            return 'Περατωμένη';
        case 'rejected':
            return 'Απορριφθείσα';
        case 'canceled':
            return 'Ακυρωμένη';
        case 'examination':
            return 'Υπό εξέταση';
        default:
            return status;
    }
}

function addTimelineStyles() {
    if (document.getElementById('timeline-styles')) {
        return;
    }
    
    const style = document.createElement('style');
    style.id = 'timeline-styles';
    style.textContent = `
        .timeline {
            position: relative;
            padding: 1rem;
            margin: 0 auto;
        }
        
        .timeline::before {
            content: '';
            position: absolute;
            height: 100%;
            border: 1px solid #dee2e6;
            left: 1rem;
            top: 0;
        }
        
        .timeline-item {
            position: relative;
            margin-left: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .timeline-marker {
            position: absolute;
            width: 15px;
            height: 15px;
            border-radius: 50%;
            background-color: #007bff;
            left: -2.25rem;
            top: 0.25rem;
        }
        
        .timeline-content {
            padding: 0.5rem;
            border-left: 2px solid #007bff;
        }
        
        .timeline-title {
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
        }
    `;
    
    document.head.appendChild(style);
}


function showError(message) {
    document.getElementById('thesis-loading').classList.add('d-none');
    
    const errorElement = document.getElementById('thesis-error');
    errorElement.textContent = message;
    errorElement.classList.remove('d-none');
}

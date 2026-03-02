
document.addEventListener('DOMContentLoaded', function() {
    loadThesisList();
    
    document.getElementById('filter-form').addEventListener('submit', function(event) {
        event.preventDefault();
        loadThesisList();
    });
    
    document.getElementById('export-csv').addEventListener('click', function() {
        exportThesisList('csv');
    });
    
    document.getElementById('export-json').addEventListener('click', function() {
        exportThesisList('json');
    });
});

function loadThesisList() {
    const statusFilter = document.getElementById('status-filter').value;
    const roleFilter = document.getElementById('role-filter').value;
    
    const siteUrl = window.location.origin + '/web_php';
    let url = siteUrl + '/api/professor/thesis_list.php';
    const params = [];
    
    if (statusFilter) {
        params.push(`status=${statusFilter}`);
    }
    
    if (roleFilter) {
        params.push(`role=${roleFilter}`);
    }
    
    if (params.length > 0) {
        url += '?' + params.join('&');
    }
    
    document.getElementById('thesis-list-container').innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Φόρτωση...</span>
            </div>
            <p class="mt-2">Φόρτωση διπλωματικών εργασιών...</p>
        </div>
    `;
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayThesisList(data.data);
            } else {
                document.getElementById('thesis-list-container').innerHTML = `
                    <div class="alert alert-danger" role="alert">
                        ${data.message || 'Σφάλμα κατά τη φόρτωση των διπλωματικών εργασιών.'}
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('thesis-list-container').innerHTML = `
                <div class="alert alert-danger" role="alert">
                    Σφάλμα κατά τη φόρτωση των διπλωματικών εργασιών. Παρακαλώ δοκιμάστε ξανά αργότερα.
                </div>
            `;
        });
}

function displayThesisList(theses) {
    const container = document.getElementById('thesis-list-container');
    
    if (theses.length === 0) {
        container.innerHTML = `
            <div class="alert alert-info" role="alert">
                Δεν βρέθηκαν διπλωματικές εργασίες με τα επιλεγμένα φίλτρα.
            </div>
        `;
        return;
    }
    
    let html = '<div class="row">';
    
    theses.forEach(thesis => {
        let cardClass = 'border-primary';
        let statusBadgeClass = 'bg-primary';
        
        switch(thesis.status) {
            case 'pending':
                cardClass = 'border-warning';
                statusBadgeClass = 'bg-warning text-dark';
                break;
            case 'active':
                cardClass = 'border-success';
                statusBadgeClass = 'bg-success';
                break;
            case 'completed':
                cardClass = 'border-info';
                statusBadgeClass = 'bg-info text-dark';
                break;
            case 'rejected':
            case 'canceled':
                cardClass = 'border-danger';
                statusBadgeClass = 'bg-danger';
                break;
            case 'examination':
                cardClass = 'border-primary';
                statusBadgeClass = 'bg-primary';
                break;
        }
        
        let statusText = '';
        switch(thesis.status) {
            case 'pending':
                statusText = 'Υπό ανάθεση';
                break;
            case 'active':
                statusText = 'Ενεργή';
                break;
            case 'completed':
                statusText = 'Περατωμένη';
                break;
            case 'rejected':
                statusText = 'Απορριφθείσα';
                break;
            case 'canceled':
                statusText = 'Ακυρωμένη';
                break;
            case 'examination':
                statusText = 'Υπό εξέταση';
                break;
            default:
                statusText = thesis.status;
        }
        
        let roleText = thesis.professor_role === 'supervisor' ? 'Επιβλέπων' : 'Μέλος τριμελούς';
        
        html += `
            <div class="col-md-6 mb-4">
                <div class="card ${cardClass}">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">${thesis.thesis_title}</h5>
                        <span class="badge ${statusBadgeClass}">${statusText}</span>
                    </div>
                    <div class="card-body">
                        <p class="card-text">${thesis.thesis_summary.substring(0, 150)}${thesis.thesis_summary.length > 150 ? '...' : ''}</p>
                        <p class="card-text"><strong>Φοιτητής:</strong> ${thesis.student_name} (${thesis.student_am})</p>
                        <p class="card-text"><strong>Ρόλος:</strong> ${roleText}</p>
                        <p class="card-text"><strong>Ημερομηνία ανάθεσης:</strong> ${new Date(thesis.created_at).toLocaleDateString('el-GR')}</p>
                        ${thesis.grade ? `<p class="card-text"><strong>Βαθμός:</strong> ${thesis.grade}</p>` : ''}
                        <a href="thesis_details.php?id=${thesis.assignment_id}" class="btn btn-primary">Προβολή Λεπτομερειών</a>
                    </div>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    container.innerHTML = html;
}

function showThesisDetails(thesisId, theses) {
    const thesis = theses.find(t => String(t.assignment_id) === String(thesisId));
    
    console.log('Searching for thesis with ID:', thesisId, 'Type:', typeof thesisId);
    console.log('Available thesis IDs:', theses.map(t => ({ id: t.assignment_id, type: typeof t.assignment_id })));
    
    if (!thesis) {
        console.error('Thesis not found:', thesisId);
        return;
    }
    
    let statusText = '';
    let statusBadgeClass = '';
    switch (thesis.status) {
        case 'pending':
            statusText = 'Υπό ανάθεση';
            statusBadgeClass = 'bg-warning text-dark';
            break;
        case 'active':
            statusText = 'Ενεργή';
            statusBadgeClass = 'bg-success';
            break;
        case 'examination':
            statusText = 'Σε εξέταση';
            statusBadgeClass = 'bg-primary';
            break;
        case 'completed':
            statusText = 'Περατωμένη';
            statusBadgeClass = 'bg-info';
            break;
        case 'cancelled':
            statusText = 'Ακυρωμένη';
            statusBadgeClass = 'bg-danger';
            break;
        default:
            statusText = thesis.status;
            statusBadgeClass = 'bg-secondary';
    }
    
    const isSupervisor = thesis.professor_role === 'supervisor';
    
    let timelineHtml = '';
    if (thesis.timeline && thesis.timeline.length > 0) {
        thesis.timeline.forEach(item => {
            let itemStatusText = '';
            switch (item.status) {
                case 'pending':
                    itemStatusText = 'Υπό ανάθεση';
                    break;
                case 'active':
                    itemStatusText = 'Ενεργή';
                    break;
                case 'examination':
                    itemStatusText = 'Σε εξέταση';
                    break;
                case 'completed':
                    itemStatusText = 'Περατωμένη';
                    break;
                case 'cancelled':
                    itemStatusText = 'Ακυρωμένη';
                    break;
                default:
                    itemStatusText = item.status;
            }
            
            timelineHtml += `
                <div class="timeline-item">
                    <div class="timeline-date">${new Date(item.created_at).toLocaleString('el-GR')}</div>
                    <div class="timeline-content">
                        <strong>Αλλαγή κατάστασης: ${itemStatusText}</strong>
                        ${item.notes ? `<p>${item.notes}</p>` : ''}
                    </div>
                </div>
            `;
        });
    } else {
        timelineHtml = '<p>Δεν υπάρχουν καταγεγραμμένες ενέργειες.</p>';
    }
    
    let committeeHtml = '';
    if (thesis.committee_members && thesis.committee_members.length > 0) {
        thesis.committee_members.forEach(member => {
            committeeHtml += `
                <div class="committee-member">
                    <i class="bi bi-person"></i> ${member.professor_name}
                </div>
            `;
        });
    } else {
        committeeHtml = '<p>Δεν έχουν οριστεί μέλη τριμελούς επιτροπής.</p>';
    }
    
    let actionsHtml = '';
    
    if (thesis.status === 'pending') {
        if (isSupervisor) {
            actionsHtml = `
                <div class="thesis-actions mb-4">
                    <h5>Διαθέσιμες Ενέργειες</h5>
                    <div class="d-flex flex-wrap gap-2">
                        <button class="btn btn-success" onclick="changeThesisStatus('${thesis.assignment_id}', 'active')">
                            <i class="bi bi-check-circle"></i> Ενεργοποίηση
                        </button>
                        <button class="btn btn-danger" onclick="cancelThesisAssignment('${thesis.assignment_id}', '${thesis.status}', '${thesis.assignment_date}')">
                            <i class="bi bi-x-circle"></i> Ακύρωση Ανάθεσης
                        </button>
                    </div>
                </div>
                
                <div class="thesis-invitations mb-4">
                    <h5>Προσκλήσεις Μελών Επιτροπής</h5>
                    <div id="invitations-list-${thesis.assignment_id}">
                        <p class="text-muted">Φόρτωση προσκλήσεων...</p>
                    </div>
                    <div class="mt-3">
                        <button class="btn btn-outline-primary" onclick="showInvitationForm('${thesis.assignment_id}')">
                            <i class="bi bi-plus-circle"></i> Προσθήκη Μέλους
                        </button>
                    </div>
                </div>
            `;
        } else {
            actionsHtml = `
                <div class="thesis-actions mb-4">
                    <h5>Διαθέσιμες Ενέργειες</h5>
                    <div class="d-flex flex-wrap gap-2">
                        <button class="btn btn-success" onclick="acceptInvitation('${thesis.assignment_id}')">
                            <i class="bi bi-check-circle"></i> Αποδοχή Πρόσκλησης
                        </button>
                        <button class="btn btn-danger" onclick="rejectInvitation('${thesis.assignment_id}')">
                            <i class="bi bi-x-circle"></i> Απόρριψη Πρόσκλησης
                        </button>
                    </div>
                </div>
            `;
        }
    }
    
    else if (thesis.status === 'active') {
        if (isSupervisor) {
            actionsHtml = `
                <div class="thesis-actions mb-4">
                    <h5>Διαθέσιμες Ενέργειες</h5>
                    <div class="d-flex flex-wrap gap-2">
                        <button class="btn btn-primary" onclick="changeThesisStatus('${thesis.assignment_id}', 'examination')">
                            <i class="bi bi-arrow-right-circle"></i> Μετάβαση σε Εξέταση
                        </button>
                        <button class="btn btn-danger" onclick="cancelThesisAssignment('${thesis.assignment_id}', '${thesis.status}', '${thesis.assignment_date}')">
                            <i class="bi bi-x-circle"></i> Ακύρωση Διπλωματικής
                        </button>
                    </div>
                </div>
            `;
        }
        
        actionsHtml += `
            <div class="thesis-notes mb-4">
                <h5>Προσωπικές Σημειώσεις</h5>
                <div class="card">
                    <div class="card-body">
                        <div class="mb-3">
                            <textarea id="thesis-notes-${thesis.assignment_id}" class="form-control" rows="3" placeholder="Προσθέστε τις σημειώσεις σας εδώ...">${thesis.personal_notes || ''}</textarea>
                        </div>
                        <button class="btn btn-outline-primary" onclick="saveThesisNotes('${thesis.assignment_id}')">
                            <i class="bi bi-save"></i> Αποθήκευση Σημειώσεων
                        </button>
                    </div>
                </div>
            </div>
        `;
    }
    
    else if (thesis.status === 'examination') {
        if (isSupervisor) {
            actionsHtml = `
                <div class="thesis-actions mb-4">
                    <h5>Διαθέσιμες Ενέργειες</h5>
                    <div class="d-flex flex-wrap gap-2">
                        <button class="btn btn-primary" onclick="showGradingForm('${thesis.assignment_id}')">
                            <i class="bi bi-award"></i> Βαθμολόγηση
                        </button>
                        <button class="btn btn-outline-primary" onclick="createPresentationAnnouncement('${thesis.assignment_id}')">
                            <i class="bi bi-megaphone"></i> Δημιουργία Ανακοίνωσης Παρουσίασης
                        </button>
                    </div>
                </div>
                
                <div class="thesis-draft mb-4">
                    <h5>Προσχέδιο Κειμένου</h5>
                    <div class="card">
                        <div class="card-body">
                            ${thesis.draft_text_file ? `
                                <a href="${thesis.draft_text_file}" target="_blank" class="btn btn-outline-primary">
                                    <i class="bi bi-file-earmark-text"></i> Προβολή Προσχεδίου
                                </a>
                            ` : '<p class="text-muted">Δεν έχει υποβληθεί προσχέδιο κειμένου.</p>'}
                        </div>
                    </div>
                </div>
            `;
        } else {
            actionsHtml = `
                <div class="thesis-actions mb-4">
                    <h5>Διαθέσιμες Ενέργειες</h5>
                    <div class="d-flex flex-wrap gap-2">
                        <button class="btn btn-primary" onclick="showCommitteeFeedbackForm('${thesis.assignment_id}')">
                            <i class="bi bi-chat-dots"></i> Προσθήκη Σχολίων Επιτροπής
                        </button>
                    </div>
                </div>
                
                <div class="thesis-draft mb-4">
                    <h5>Προσχέδιο Κειμένου</h5>
                    <div class="card">
                        <div class="card-body">
                            ${thesis.draft_text_file ? `
                                <a href="${thesis.draft_text_file}" target="_blank" class="btn btn-outline-primary">
                                    <i class="bi bi-file-earmark-text"></i> Προβολή Προσχεδίου
                                </a>
                            ` : '<p class="text-muted">Δεν έχει υποβληθεί προσχέδιο κειμένου.</p>'}
                        </div>
                    </div>
                </div>
            `;
        }
    }
    
    const detailsHtml = `
        <div class="thesis-detail-section">
            <h5>Βασικές Πληροφορίες</h5>
            <div class="row">
                <div class="col-md-8">
                    <p><strong>Τίτλος:</strong> ${thesis.thesis_title}</p>
                    <p><strong>Περίληψη:</strong> ${thesis.thesis_summary || 'Δεν υπάρχει περίληψη.'}</p>
                    <p><strong>Φοιτητής:</strong> ${thesis.student_name} (${thesis.student_am})</p>
                    <p><strong>Κατάσταση:</strong> <span class="badge ${statusBadgeClass}">${statusText}</span></p>
                    <p><strong>Ημερομηνία Δημιουργίας:</strong> ${new Date(thesis.created_at).toLocaleDateString('el-GR')}</p>
                    <p><strong>Τελευταία Ενημέρωση:</strong> ${new Date(thesis.updated_at).toLocaleDateString('el-GR')}</p>
                </div>
                <div class="col-md-4">
                    ${thesis.status === 'completed' ? `
                        <div class="card">
                            <div class="card-body text-center">
                                <h5 class="card-title">Βαθμός</h5>
                                <p class="display-4">${thesis.grade || 'N/A'}</p>
                            </div>
                        </div>
                    ` : ''}
                </div>
            </div>
        </div>
        
        ${actionsHtml}
        
        <div class="thesis-detail-section">
            <h5>Τριμελής Επιτροπή</h5>
            <p><strong>Επιβλέπων:</strong> ${thesis.supervisor ? thesis.supervisor.professor_name : 'Δεν έχει οριστεί'}</p>
            <p><strong>Μέλη Τριμελούς:</strong></p>
            <div class="ms-3">
                ${committeeHtml}
            </div>
        </div>
        
        ${thesis.status === 'completed' || thesis.status === 'examination' ? `
            <div class="thesis-detail-section">
                <h5>Αρχεία</h5>
                <div class="row">
                    ${thesis.status === 'completed' ? `
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-body">
                                <h6 class="card-title">Τελικό Κείμενο</h6>
                                ${thesis.final_report_file ? `
                                    <a href="${thesis.final_report_file}" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-file-earmark-pdf"></i> Προβολή
                                    </a>
                                ` : 'Δεν υπάρχει διαθέσιμο αρχείο.'}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-body">
                                <h6 class="card-title">Πρακτικό Βαθμολόγησης</h6>
                                ${thesis.grade_protocol_file ? `
                                    <a href="${thesis.grade_protocol_file}" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-file-earmark-pdf"></i> Προβολή
                                    </a>
                                ` : 'Δεν υπάρχει διαθέσιμο αρχείο.'}
                            </div>
                        </div>
                    </div>
                    ` : ''}
                    ${thesis.status === 'examination' && thesis.draft_text_file ? `
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-body">
                                <h6 class="card-title">Προσχέδιο Κειμένου</h6>
                                <a href="${thesis.draft_text_file}" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-file-earmark-text"></i> Προβολή
                                </a>
                            </div>
                        </div>
                    </div>
                    ` : ''}
                </div>
            </div>
        ` : ''}
        
        <div class="thesis-detail-section">
            <h5>Χρονολόγιο Ενεργειών</h5>
            <div class="timeline">
                ${timelineHtml}
            </div>
        </div>
    `;
    
    document.getElementById('thesis-details-content').innerHTML = detailsHtml;
    
    document.getElementById('thesis-details-modal-label').textContent = `Διπλωματική: ${thesis.thesis_title}`;
    
    try {
        if (typeof bootstrap !== 'undefined') {
            const modal = new bootstrap.Modal(document.getElementById('thesis-details-modal'));
            modal.show();
        } else {
            const modalElement = document.getElementById('thesis-details-modal');
            modalElement.classList.add('show');
            modalElement.style.display = 'block';
            document.body.classList.add('modal-open');
            
            const backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show';
            document.body.appendChild(backdrop);
            
            console.log('Bootstrap object not found, using alternative modal display method');
        }
    } catch (error) {
        console.error('Error showing thesis details modal:', error);
    }
}



function exportThesisList(format) {
    const statusFilter = document.getElementById('status-filter').value;
    const roleFilter = document.getElementById('role-filter').value;
    
    const siteUrl = window.location.origin + '/web_php';
    let url = `${siteUrl}/api/professor/thesis_list.php?format=${format}`;
    
    if (statusFilter) {
        url += `&status=${statusFilter}`;
    }
    
    if (roleFilter) {
        url += `&role=${roleFilter}`;
    }
    
    window.open(url, '_blank');
}

function cancelThesisAssignment(thesisId) {
    if (!confirm('Είστε βέβαιοι ότι θέλετε να ακυρώσετε την ανάθεση αυτής της διπλωματικής;')) {
        return;
    }
    
    const siteUrl = window.location.origin + '/web_php';
    const url = `${siteUrl}/api/professor/cancel_assignment.php`;
    
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            thesis_id: thesisId,
            reason: 'Ακύρωση από τον επιβλέποντα καθηγητή'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Η ανάθεση ακυρώθηκε επιτυχώς.');
            const modal = bootstrap.Modal.getInstance(document.getElementById('thesis-details-modal'));
            modal.hide();
            loadThesisList();
        } else {
            alert(`Σφάλμα: ${data.message || 'Δεν ήταν δυνατή η ακύρωση της ανάθεσης.'}`);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Σφάλμα κατά την ακύρωση της ανάθεσης. Παρακαλώ δοκιμάστε ξανά αργότερα.');
    });
}

function showCancellationForm(thesisId) {
    const modalHtml = `
        <div class="modal fade" id="cancellation-modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Ακύρωση Διπλωματικής</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="cancellation-form">
                            <div class="mb-3">
                                <label for="cancellation-reason" class="form-label">Αιτιολογία Ακύρωσης</label>
                                <textarea id="cancellation-reason" class="form-control" rows="3" required></textarea>
                                <div class="form-text">Παρακαλώ εξηγήστε τους λόγους ακύρωσης της διπλωματικής.</div>
                            </div>
                            <input type="hidden" id="thesis-id-to-cancel" value="${thesisId}">
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Ακύρωση</button>
                        <button type="button" class="btn btn-danger" onclick="submitCancellation()">Ακύρωση Διπλωματικής</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    if (!document.getElementById('cancellation-modal')) {
        document.body.insertAdjacentHTML('beforeend', modalHtml);
    } else {
        document.getElementById('thesis-id-to-cancel').value = thesisId;
    }
    
    const modal = new bootstrap.Modal(document.getElementById('cancellation-modal'));
    modal.show();
}

function submitCancellation() {
    const thesisId = document.getElementById('thesis-id-to-cancel').value;
    const reason = document.getElementById('cancellation-reason').value.trim();
    
    if (!reason) {
        alert('Παρακαλώ συμπληρώστε την αιτιολογία ακύρωσης.');
        return;
    }
    
    const siteUrl = window.location.origin + '/web_php';
    const url = `${siteUrl}/api/professor/cancel_assignment.php`;
    
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            thesis_id: thesisId,
            reason: reason
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Η διπλωματική ακυρώθηκε επιτυχώς.');
            
            const cancellationModal = bootstrap.Modal.getInstance(document.getElementById('cancellation-modal'));
            cancellationModal.hide();
            
            const detailsModal = bootstrap.Modal.getInstance(document.getElementById('thesis-details-modal'));
            detailsModal.hide();
            
            loadThesisList();
        } else {
            alert(`Σφάλμα: ${data.message || 'Δεν ήταν δυνατή η ακύρωση της διπλωματικής.'}`);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Σφάλμα κατά την ακύρωση της διπλωματικής. Παρακαλώ δοκιμάστε ξανά αργότερα.');
    });
}

function acceptInvitation(thesisId, invitationId) {
    if (!confirm('Είστε βέβαιοι ότι θέλετε να αποδεχτείτε την πρόσκληση συμμετοχής στην τριμελή επιτροπή;')) {
        return;
    }
    
    const siteUrl = window.location.origin + '/web_php';
    const url = `${siteUrl}/api/professor/committee_invitations.php`;
    
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            action: 'accept',
            invitation_id: invitationId,
            thesis_id: thesisId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Η πρόσκληση έγινε αποδεκτή επιτυχώς.');
            const modal = bootstrap.Modal.getInstance(document.getElementById('thesis-details-modal'));
            modal.hide();
            loadThesisList();
        } else {
            alert(`Σφάλμα: ${data.message || 'Δεν ήταν δυνατή η αποδοχή της πρόσκλησης.'}`);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Σφάλμα κατά την αποδοχή της πρόσκλησης. Παρακαλώ δοκιμάστε ξανά αργότερα.');
    });
}

function rejectInvitation(thesisId, invitationId) {
    if (!confirm('Είστε βέβαιοι ότι θέλετε να απορρίψετε την πρόσκληση συμμετοχής στην τριμελή επιτροπή;')) {
        return;
    }
    
    const siteUrl = window.location.origin + '/web_php';
    const url = `${siteUrl}/api/professor/committee_invitations.php`;
    
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            action: 'reject',
            invitation_id: invitationId,
            thesis_id: thesisId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Η πρόσκληση απορρίφθηκε επιτυχώς.');
            const modal = bootstrap.Modal.getInstance(document.getElementById('thesis-details-modal'));
            modal.hide();
            loadThesisList();
        } else {
            alert(`Σφάλμα: ${data.message || 'Δεν ήταν δυνατή η απόρριψη της πρόσκλησης.'}`);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Σφάλμα κατά την απόρριψη της πρόσκλησης. Παρακαλώ δοκιμάστε ξανά αργότερα.');
    });
}

function showNotesForm(thesisId, currentNotes = '') {
    const modalHtml = `
        <div class="modal fade" id="notes-modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Σημειώσεις Διπλωματικής</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="notes-form">
                            <div class="mb-3">
                                <label for="thesis-notes" class="form-label">Προσωπικές Σημειώσεις</label>
                                <textarea id="thesis-notes" class="form-control" rows="5">${currentNotes}</textarea>
                                <div class="form-text">Οι σημειώσεις είναι ορατές μόνο σε εσάς.</div>
                            </div>
                            <input type="hidden" id="thesis-id-for-notes" value="${thesisId}">
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Ακύρωση</button>
                        <button type="button" class="btn btn-primary" onclick="saveNotes()">Αποθήκευση</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    if (!document.getElementById('notes-modal')) {
        document.body.insertAdjacentHTML('beforeend', modalHtml);
    } else {
        document.getElementById('thesis-id-for-notes').value = thesisId;
        document.getElementById('thesis-notes').value = currentNotes;
    }
    
    const modal = new bootstrap.Modal(document.getElementById('notes-modal'));
    modal.show();
}

function saveNotes() {
    const thesisId = document.getElementById('thesis-id-for-notes').value;
    const notes = document.getElementById('thesis-notes').value.trim();
    
    const siteUrl = window.location.origin + '/web_php';
    const url = `${siteUrl}/api/professor/thesis_notes.php`;
    
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            thesis_id: thesisId,
            notes: notes
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Οι σημειώσεις αποθηκεύτηκαν επιτυχώς.');
            
            const notesModal = bootstrap.Modal.getInstance(document.getElementById('notes-modal'));
            notesModal.hide();
            
            const notesSection = document.getElementById('thesis-notes-section');
            if (notesSection) {
                if (notes) {
                    notesSection.innerHTML = `
                        <div class="thesis-detail-section">
                            <h5>Προσωπικές Σημειώσεις</h5>
                            <p>${notes.replace(/\n/g, '<br>')}</p>
                            <button class="btn btn-sm btn-outline-primary" onclick="showNotesForm('${thesisId}', '${notes.replace(/'/g, "\\'")}')">\n                                <i class="bi bi-pencil"></i> Επεξεργασία\n                            </button>\n                        </div>
                    `;
                } else {
                    notesSection.innerHTML = '';
                }
            }
        } else {
            alert(`Σφάλμα: ${data.message || 'Δεν ήταν δυνατή η αποθήκευση των σημειώσεων.'}`);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Σφάλμα κατά την αποθήκευση των σημειώσεων. Παρακαλώ δοκιμάστε ξανά αργότερα.');
    });
}

function changeThesisStatus(thesisId, newStatus) {
    if (!confirm(`Είστε βέβαιοι ότι θέλετε να αλλάξετε την κατάσταση της διπλωματικής σε "${newStatus}";`)) {
        return;
    }
    
    const siteUrl = window.location.origin + '/web_php';
    const url = `${siteUrl}/api/professor/change_thesis_status.php`;
    
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            thesis_id: thesisId,
            new_status: newStatus
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Η κατάσταση της διπλωματικής άλλαξε επιτυχώς.');
            const modal = bootstrap.Modal.getInstance(document.getElementById('thesis-details-modal'));
            modal.hide();
            loadThesisList();
        } else {
            alert(`Σφάλμα: ${data.message || 'Δεν ήταν δυνατή η αλλαγή της κατάστασης.'}`);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Σφάλμα κατά την αλλαγή της κατάστασης. Παρακαλώ δοκιμάστε ξανά αργότερα.');
    });
}

function cancelThesisAssignment(thesisId, status, assignmentDate) {
    if (status === 'active') {
        const assignmentDateObj = new Date(assignmentDate);
        const currentDate = new Date();
        const twoYearsInMs = 2 * 365 * 24 * 60 * 60 * 1000;
        
        if (currentDate - assignmentDateObj >= twoYearsInMs) {
            if (confirm('Είστε βέβαιοι ότι θέλετε να ακυρώσετε την ανάθεση της διπλωματικής;')) {
                submitCancellation(thesisId, 'από Διδάσκοντα');
            }
        } else {
            alert('Δεν μπορείτε να ακυρώσετε την ανάθεση διότι δεν έχουν περάσει 2 έτη από την ανάθεση.');
        }
    } else if (status === 'pending') {
        showCancellationForm(thesisId);
    } else {
        alert('Δεν μπορείτε να ακυρώσετε μια διπλωματική σε αυτή την κατάσταση.');
    }
}


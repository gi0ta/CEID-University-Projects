
document.addEventListener('DOMContentLoaded', function() {
    loadDashboardData();
});

function loadDashboardData() {
    fetch('../../api/student/dashboard_data.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                updateDashboardUI(data.data);
            } else {
                console.error('Error loading dashboard data:', data.message);
            }
        })
        .catch(error => {
            console.error('Error fetching dashboard data:', error);
        });
}



function updateDashboardUI(data) {
    console.log('Dashboard data loaded:', data);
    
    if (data.active_thesis) {
        const thesisElement = document.getElementById('active-thesis');
        if (thesisElement) {
            let thesisContent = `
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Ενεργή Διπλωματική Εργασία</h5>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">${data.active_thesis.title}</h5>
                        <p class="card-text">${data.active_thesis.summary || ''}</p>
                        <p><strong>Επιβλέπων:</strong> ${data.active_thesis.professor_name}</p>
                        <p><strong>Κατάσταση:</strong> ${getStatusText(data.active_thesis.status)}</p>
                        <p><strong>Ημερομηνία Ανάθεσης:</strong> ${formatDate(data.active_thesis.assignment_date)}</p>
            `;
            
            if (data.active_thesis.status === 'pending') {
                thesisContent += `
                    <!-- Τμήμα διαχείρισης μελών επιτροπής -->
                    <div id="committee-management" class="mt-3">
                        <hr>
                        <h5>Διαχείριση Τριμελούς Επιτροπής</h5>
                        <p>Για να ενεργοποιηθεί η διπλωματική σας εργασία, πρέπει να συμπληρωθεί η τριμελής επιτροπή με την αποδοχή δύο καθηγητών.</p>
                        
                        ${renderCommitteeMembers(data.active_thesis.committee_members)}
                        
                        <button id="invite-professor-btn" class="btn btn-primary mt-3">
                            <i class="fas fa-user-plus me-2"></i>Πρόσκληση Καθηγητή
                        </button>
                    </div>
                `;
            } else if (data.active_thesis.status === 'examination') {
                thesisContent += renderCommitteeMembers(data.active_thesis.committee_members);
                const siteUrl = window.location.origin + '/web_php';
                console.log('Thesis assignment ID:', data.active_thesis.assignment_id);
                thesisContent += `
                    <div class="mt-3">
                        <a href="${siteUrl}/pages/student/thesis_materials.php?thesis_id=${data.active_thesis.assignment_id}" id="manage-thesis-materials-btn" data-thesis-id="${data.active_thesis.assignment_id}" class="btn btn-info">
                            <i class="fas fa-file-upload me-2"></i>Διαχείριση Υλικού Διπλωματικής
                        </a>
                        <p class="text-muted small mt-2">Αναρτήστε το πρόχειρο κείμενο της διπλωματικής και συνδέσμους προς άλλο υλικό</p>
                    </div>
                `;
                
                fetch('../../api/student/check_examination_protocol.php')
                    .then(response => response.json())
                    .then(protocolData => {
                        if (protocolData.success && protocolData.has_protocol) {
                            const protocolBtn = document.createElement('div');
                            protocolBtn.className = 'mt-2';
                            protocolBtn.innerHTML = `
                                <a href="${siteUrl}/pages/student/examination_protocol.php?thesis_id=${protocolData.thesis_id}" class="btn btn-secondary">
                                    <i class="fas fa-file-alt me-2"></i>Πρωτόκολλο Εξέτασης
                                </a>
                            `;
                            
                            const materialsBtnContainer = document.querySelector('#manage-thesis-materials-btn').closest('div');
                            materialsBtnContainer.parentNode.insertBefore(protocolBtn, materialsBtnContainer.nextSibling);
                        }
                    })
                    .catch(error => {
                        console.error('Error checking examination protocol:', error);
                    });
            } else if (data.active_thesis.status === 'completed') {
                thesisContent += renderCommitteeMembers(data.active_thesis.committee_members);
                
                if (data.active_thesis.final_grade) {
                    thesisContent += `<p><strong>Βαθμός:</strong> ${data.active_thesis.final_grade}</p>`;
                }
                
                if (data.active_thesis.completion_date) {
                    thesisContent += `<p><strong>Ημερομηνία Ολοκλήρωσης:</strong> ${formatDate(data.active_thesis.completion_date)}</p>`;
                }
                
                const siteUrl = window.location.origin + '/web_php';
                thesisContent += `
                    <div class="mt-3">
                        <a href="${siteUrl}/pages/student/examination_protocol.php?thesis_id=${data.active_thesis.assignment_id}" class="btn btn-info">
                            <i class="fas fa-file-alt me-2"></i>Προβολή Πρακτικού Εξέτασης
                        </a>
                    </div>
                `;
                
                if (data.active_thesis.repository_link) {
                    thesisContent += `
                        <div class="mt-3">
                            <a href="${data.active_thesis.repository_link}" target="_blank" class="btn btn-secondary">
                                <i class="fas fa-external-link-alt me-2"></i>Σύνδεσμος Νημερτής
                            </a>
                        </div>
                    `;
                }
            } else {
                thesisContent += renderCommitteeMembers(data.active_thesis.committee_members);
            }
            
            thesisContent += `
                    </div>
                </div>
            `;
            
            thesisElement.innerHTML = thesisContent;
            
            if (data.active_thesis) {
                setTimeout(function() {
                    if (data.active_thesis.status === 'examination') {
                        console.log('Calling setupThesisMaterialsManagement with delay');
                        setupThesisMaterialsManagement();
                    } else if (data.active_thesis.status === 'pending') {
                        console.log('Setting up invite professor button');
                        const inviteProfessorBtn = document.getElementById('invite-professor-btn');
                        if (inviteProfessorBtn) {
                            console.log('Found invite professor button, adding event listener');
                            inviteProfessorBtn.addEventListener('click', function() {
                                console.log('Invite professor button clicked');
                                const siteUrl = window.location.origin + '/web_php';
                                console.log('Navigating to:', `${siteUrl}/pages/student/invite_professor.php`);
                                window.location.href = `${siteUrl}/pages/student/invite_professor.php`;
                            });
                        } else {
                            console.log('Invite professor button not found');
                        }
                        
                        console.log('Setting up cancel invitation buttons');
                        const cancelButtons = document.querySelectorAll('.cancel-invitation');
                        cancelButtons.forEach(button => {
                            console.log('Found cancel button with ID:', button.getAttribute('data-id'));
                            button.addEventListener('click', function(event) {
                                event.preventDefault();
                                const invitationId = this.getAttribute('data-id');
                                console.log('Cancel button clicked for invitation ID:', invitationId);
                                cancelInvitation(invitationId);
                            });
                        });
                    }
                }, 100);
            }
        }
    }
}


function getStatusText(status) {
    const statusMap = {
        'pending': 'Υπό ανάθεση',
        'active': 'Σε Εξέλιξη',
        'examination': 'Σε Εξέταση',
        'completed': 'Ολοκληρωμένη',
        'cancelled': 'Ακυρωμένη'
    };
    
    return statusMap[status] || status;
}

function formatDate(dateString) {
    if (!dateString) return '';
    
    const date = new Date(dateString);
    return date.toLocaleDateString('el-GR');
}
function renderCommitteeMembers(committeeMembers) {
    if (!committeeMembers || committeeMembers.length === 0) {
        return '<p><strong>Μέλη Επιτροπής:</strong> Δεν έχουν οριστεί ακόμα</p>';
    }
    
    let html = '<div class="mt-3"><strong>Μέλη Επιτροπής:</strong>';
    html += '<ul class="list-group list-group-flush mt-2">';
    
    committeeMembers.forEach(member => {
        let statusBadge = '';
        let actionButton = '';
        
        if (member.status === 'pending') {
            statusBadge = '<span class="badge bg-warning ms-2">Σε αναμονή</span>';
            actionButton = `<button class="btn btn-sm btn-outline-danger cancel-invitation" data-id="${member.id}">Ακύρωση</button>`;
        } else if (member.status === 'accepted') {
            statusBadge = '<span class="badge bg-success ms-2">Αποδέχτηκε</span>';
        } else if (member.status === 'rejected') {
            statusBadge = '<span class="badge bg-danger ms-2">Απέρριψε</span>';
        }
        
        html += `<li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>${member.professor_name} ${statusBadge}</div>
                    <div>${actionButton}</div>
                </li>`;
    });
    
    html += '</ul></div>';
    return html;
}

function setupCommitteeManagement() {
}

function setupThesisMaterialsManagement() {
    console.log('Setting up thesis materials management');
    
    const manageThesisMaterialsBtn = document.getElementById('manage-thesis-materials-btn');
    
    if (manageThesisMaterialsBtn) {
        console.log('Found manage thesis materials button');
        manageThesisMaterialsBtn.addEventListener('click', function() {
            console.log('Thesis materials button clicked');
            const thesisId = this.getAttribute('data-thesis-id');
            console.log('Thesis ID:', thesisId);
            if (thesisId) {
                const siteUrl = window.location.origin + '/web_php';
                console.log('Navigating to:', `${siteUrl}/pages/student/thesis_materials.php?thesis_id=${thesisId}`);
                window.location.href = `${siteUrl}/pages/student/thesis_materials.php?thesis_id=${thesisId}`;
            } else {
                console.error('No thesis ID found!');
            }
        });
    } else {
        console.log('Manage thesis materials button not found');
    }
}


function cancelInvitation(invitationId) {
    if (!confirm('Είστε σίγουροι ότι θέλετε να ακυρώσετε αυτή την πρόσκληση;')) {
        return;
    }
    
    fetch('../../api/student/cancel_invitation.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            invitation_id: invitationId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadDashboardData();
            showAlert('success', 'Η πρόσκληση ακυρώθηκε με επιτυχία!');
        } else {
            showAlert('danger', 'Σφάλμα: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error cancelling invitation:', error);
        showAlert('danger', 'Σφάλμα κατά την ακύρωση της πρόσκλησης');
    });
}

function showAlert(type, message) {
    const alertContainer = document.createElement('div');
    alertContainer.className = `alert alert-${type} alert-dismissible fade show`;
    alertContainer.setAttribute('role', 'alert');
    alertContainer.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    const container = document.querySelector('.container');
    container.insertBefore(alertContainer, container.firstChild);
    
    setTimeout(() => {
        const bsAlert = new bootstrap.Alert(alertContainer);
        bsAlert.close();
    }, 5000);
}

function getStatusText(status) {
    const statusMap = {
        'pending': 'Υπό ανάθεση',
        'active': 'Σε Εξέλιξη',
        'examination': 'Σε Εξέταση',
        'completed': 'Ολοκληρωμένη',
        'cancelled': 'Ακυρωμένη'
    };
    
    return statusMap[status] || status;
}

function formatDate(dateString) {
    if (!dateString) return '';
    
    const date = new Date(dateString);
    return date.toLocaleDateString('el-GR');
}

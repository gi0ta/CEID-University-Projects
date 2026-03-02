


function showAlert(type, message) {
    const alertContainer = document.getElementById('alertContainer');
    
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show`;
    alert.role = 'alert';
    alert.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Κλείσιμο"></button>
    `;
    
    alertContainer.appendChild(alert);
    
    setTimeout(() => {
        alert.classList.remove('show');
        setTimeout(() => {
            alert.remove();
        }, 150);
    }, 5000);
}

document.addEventListener('DOMContentLoaded', function() {
    let SITE_URL;
    const metaTag = document.querySelector('meta[name="site-url"]') || document.querySelector('meta[name="site_url"]');
    
    if (metaTag) {
        SITE_URL = metaTag.getAttribute('content');
        console.log('Found SITE_URL from meta tag:', SITE_URL);
    } else {
        const pathParts = window.location.pathname.split('/');
        const webPhpIndex = pathParts.indexOf('web_php');
        
        if (webPhpIndex !== -1) {
            const basePath = pathParts.slice(0, webPhpIndex + 1).join('/');
            SITE_URL = window.location.origin + basePath;
        } else {
            SITE_URL = window.location.origin + '/web_php';
        }
        console.log('Using calculated SITE_URL:', SITE_URL);
    }

    const statusFilter = document.getElementById('status');
    const thesesTable = document.getElementById('theses-table');
    const thesesTableBody = document.getElementById('theses-table-body');
    const thesesContainer = document.getElementById('theses-container');
    const loadingSpinner = document.getElementById('loading-spinner');
    const activeCountBadge = document.getElementById('active-count');
    const examinationCountBadge = document.getElementById('examination-count');
    const modalContainer = document.getElementById('modal-container');

    loadTheses();

    if (statusFilter) {
        statusFilter.addEventListener('change', function() {
            loadTheses();
        });
    }

    function loadTheses() {
        if (loadingSpinner) {
            loadingSpinner.classList.remove('d-none');
        }
        
        const status = statusFilter ? statusFilter.value : 'all';
        
        fetch(`${SITE_URL}/api/secretary/get_theses.php?status=${status}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (loadingSpinner) {
                    loadingSpinner.classList.add('d-none');
                }
                
                if (data.success) {
                    if (activeCountBadge) {
                        activeCountBadge.textContent = data.data.counts.active;
                    }
                    if (examinationCountBadge) {
                        examinationCountBadge.textContent = data.data.counts.examination;
                    }
                    
                    renderTheses(data.data.theses);
                } else {
                    showError(data.error || 'Προέκυψε σφάλμα κατά την ανάκτηση των διπλωματικών εργασιών.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                
                if (loadingSpinner) {
                    loadingSpinner.classList.add('d-none');
                }
                
                showError('Προέκυψε σφάλμα επικοινωνίας με τον server.');
            });
    }

    function renderTheses(theses) {
        if (thesesTableBody) {
            thesesTableBody.innerHTML = '';
        }
        
        if (theses.length === 0) {
            if (thesesContainer) {
                thesesContainer.innerHTML = '<div class="alert alert-info">Δεν βρέθηκαν διπλωματικές εργασίες με τα επιλεγμένα κριτήρια.</div>';
            }
            if (thesesTable) {
                thesesTable.classList.add('d-none');
            }
            return;
        }
        
        if (thesesTable) {
            thesesTable.classList.remove('d-none');
        }
        
        theses.forEach(thesis => {
            if (thesesTableBody) {
                const row = document.createElement('tr');
                
                const titleCell = document.createElement('td');
                titleCell.textContent = thesis.title;
                row.appendChild(titleCell);
                
                const studentCell = document.createElement('td');
                studentCell.textContent = thesis.student_name;
                row.appendChild(studentCell);
                
                const supervisorCell = document.createElement('td');
                supervisorCell.textContent = thesis.supervisor_name;
                row.appendChild(supervisorCell);
                
                const statusCell = document.createElement('td');
                const statusBadge = document.createElement('span');
                
                if (thesis.status === 'active') {
                    statusBadge.className = 'badge bg-success';
                    statusBadge.textContent = 'Ενεργή';
                } else if (thesis.status === 'examination') {
                    statusBadge.className = 'badge bg-warning text-dark';
                    statusBadge.textContent = 'Υπό Εξέταση';
                } else if (thesis.status === 'completed') {
                    statusBadge.className = 'badge bg-primary';
                    statusBadge.textContent = 'Ολοκληρωμένη';
                } else if (thesis.status === 'rejected') {
                    statusBadge.className = 'badge bg-danger';
                    statusBadge.textContent = 'Απορριφθείσα';
                } else {
                    statusBadge.className = 'badge bg-secondary';
                    statusBadge.textContent = thesis.status;
                }
                
                statusCell.appendChild(statusBadge);
                row.appendChild(statusCell);
                
                const dateCell = document.createElement('td');
                const assignmentDate = new Date(thesis.assignment_date);
                dateCell.textContent = assignmentDate.toLocaleDateString('el-GR');
                row.appendChild(dateCell);
                
                const durationCell = document.createElement('td');
                durationCell.textContent = `${thesis.days_since_assignment} ημέρες`;
                row.appendChild(durationCell);
                
                const actionsCell = document.createElement('td');
                actionsCell.className = 'text-center';
                
                if (thesis.status === 'active') {
                    const actionButton = document.createElement('a');
                    actionButton.href = `${SITE_URL}/pages/secretary/thesis_gs_protocol.php?id=${thesis.id}`;
                    actionButton.className = 'btn btn-success btn-sm';
                    actionButton.innerHTML = '<i class="fas fa-edit"></i>';
                    actionButton.title = 'Καταχώρηση ΑΠ ΓΣ';
                    actionsCell.appendChild(actionButton);
                } else if (thesis.status === 'examination') {
                    const actionButton = document.createElement('a');
                    actionButton.href = `${SITE_URL}/pages/secretary/thesis_examination.php?id=${thesis.id}`;
                    actionButton.className = 'btn btn-warning btn-sm';
                    actionButton.innerHTML = '<i class="fas fa-file-alt"></i>';
                    actionButton.title = 'Διαχείριση Διπλωματικής Υπό Εξέταση';
                    actionsCell.appendChild(actionButton);
                }
                
                row.appendChild(actionsCell);
                thesesTableBody.appendChild(row);
            }
        });
    }

    function showError(message) {
        if (thesesContainer) {
            thesesContainer.innerHTML = `<div class="alert alert-danger">${message}</div>`;
        }
    }
});

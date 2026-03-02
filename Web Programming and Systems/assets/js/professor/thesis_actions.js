
function cancelThesisAssignment(thesisId, status, assignmentDate) {
    if (status === 'pending') {
        showCancellationForm(thesisId);
    } else if (status === 'active') {
        const assignmentDateObj = new Date(assignmentDate);
        const currentDate = new Date();
        const twoYearsInMs = 2 * 365 * 24 * 60 * 60 * 1000;
        
        if (currentDate - assignmentDateObj >= twoYearsInMs) {
            if (confirm('Είστε βέβαιοι ότι θέλετε να ακυρώσετε την ανάθεση της διπλωματικής εργασίας;')) {
                submitCancellation(thesisId, 'από Διδάσκοντα');
            }
        } else {
            showCancellationForm(thesisId);
        }
    } else {
        alert('Η ακύρωση δεν είναι δυνατή για διπλωματικές σε αυτή την κατάσταση.');
    }
}

function showCancellationForm(thesisId) {
    const modalHtml = `
        <div class="modal fade" id="cancellation-modal" tabindex="-1" aria-labelledby="cancellation-modal-label" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="cancellation-modal-label">Ακύρωση Ανάθεσης Διπλωματικής</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="cancellation-form">
                            <div class="mb-3">
                                <label for="cancellation-reason" class="form-label">Λόγος Ακύρωσης</label>
                                <textarea class="form-control" id="cancellation-reason" rows="3" required></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Ακύρωση</button>
                        <button type="button" class="btn btn-danger" id="submit-cancellation">Υποβολή</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    if (!document.getElementById('cancellation-modal')) {
        document.body.insertAdjacentHTML('beforeend', modalHtml);
    }
    
    const modal = new bootstrap.Modal(document.getElementById('cancellation-modal'));
    modal.show();
    
    document.getElementById('submit-cancellation').addEventListener('click', function() {
        const reason = document.getElementById('cancellation-reason').value.trim();
        
        if (reason) {
            submitCancellation(thesisId, reason);
            modal.hide();
        } else {
            alert('Παρακαλώ εισάγετε έναν λόγο ακύρωσης.');
        }
    });
}

function submitCancellation(thesisId, reason) {
    const data = {
        thesis_id: thesisId,
        reason: reason
    };
    
    fetch('/api/professor/cancel_assignment.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Η ανάθεση της διπλωματικής εργασίας ακυρώθηκε επιτυχώς.');
            loadThesisList();
            const detailsModal = bootstrap.Modal.getInstance(document.getElementById('thesis-details-modal'));
            if (detailsModal) {
                detailsModal.hide();
            }
        } else {
            alert(`Σφάλμα κατά την ακύρωση: ${data.message}`);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Προέκυψε σφάλμα κατά την επικοινωνία με τον διακομιστή.');
    });
}

function changeThesisStatus(thesisId, newStatus) {
    if (!confirm(`Είστε βέβαιοι ότι θέλετε να αλλάξετε την κατάσταση της διπλωματικής εργασίας σε "${getStatusText(newStatus)}";`)) {
        return;
    }
    
    const data = {
        thesis_id: thesisId,
        new_status: newStatus
    };
    
    fetch('/api/professor/change_thesis_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`Η κατάσταση της διπλωματικής εργασίας άλλαξε επιτυχώς σε "${getStatusText(newStatus)}".`);
            loadThesisList();
            const detailsModal = bootstrap.Modal.getInstance(document.getElementById('thesis-details-modal'));
            if (detailsModal) {
                detailsModal.hide();
            }
        } else {
            alert(`Σφάλμα κατά την αλλαγή κατάστασης: ${data.message}`);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Προέκυψε σφάλμα κατά την επικοινωνία με τον διακομιστή.');
    });
}

function getStatusText(status) {
    const statusMap = {
        'pending': 'Υπό ανάθεση',
        'active': 'Ενεργή',
        'examination': 'Σε εξέταση',
        'completed': 'Περατωμένη',
        'cancelled': 'Ακυρωμένη'
    };
    
    return statusMap[status] || status;
}

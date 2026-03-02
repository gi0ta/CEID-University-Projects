
document.addEventListener('DOMContentLoaded', function() {
    setupDraftUploadForm();
    setupLinkAddForm();
    setupDeleteButtons();
    setupThesisPresentationForm();
});

function setupDraftUploadForm() {
    const draftUploadForm = document.getElementById('draftUploadForm');
    if (!draftUploadForm) return;
    
    const fileTypeSelect = document.getElementById('fileType');
    if (fileTypeSelect) {
        fileTypeSelect.addEventListener('change', function() {
            updateAcceptedFileTypes(this.value);
        });
        updateAcceptedFileTypes(fileTypeSelect.value);
    }
    
    draftUploadForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const fileInput = document.getElementById('draftFile');
        if (!fileInput.files.length) {
            showAlert('Παρακαλώ επιλέξτε ένα αρχείο για ανέβασμα.', 'danger');
            return;
        }
        
        const file = fileInput.files[0];
        const maxSize = 20 * 1024 * 1024;
        
        if (file.size > maxSize) {
            showAlert('Το αρχείο είναι πολύ μεγάλο. Μέγιστο επιτρεπτό μέγεθος: 20MB.', 'danger');
            return;
        }
        
        const fileType = document.getElementById('fileType').value;
        const isValidFileType = validateFileType(file, fileType);
        
        if (!isValidFileType) {
            showAlert('Μη επιτρεπτός τύπος αρχείου για την επιλεγμένη κατηγορία.', 'danger');
            return;
        }
        
        const formData = new FormData(draftUploadForm);
        
        formData.append('file_type', fileType);
        
        console.log('Sending file to API...');
        fetch('/web_php/api/student/upload_thesis_file.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('Το αρχείο ανέβηκε με επιτυχία!', 'success');
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                showAlert('Σφάλμα: ' + data.message, 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Παρουσιάστηκε σφάλμα κατά την αποστολή του αρχείου.', 'danger');
        });
    });
}

function setupLinkAddForm() {
    const linkAddForm = document.getElementById('linkAddForm');
    if (!linkAddForm) return;
    
    linkAddForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const urlInput = document.getElementById('linkUrl');
        if (!isValidUrl(urlInput.value)) {
            showAlert('Παρακαλώ εισάγετε ένα έγκυρο URL.', 'danger');
            return;
        }
        
        const formData = new FormData(linkAddForm);
        const linkData = {
            thesis_id: formData.get('thesis_id'),
            title: formData.get('title'),
            description: formData.get('description'),
            url: formData.get('url')
        };
        
        console.log('Προσθήκη συνδέσμου:', linkData);
        fetch('/web_php/api/student/add_thesis_link.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(linkData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('Ο σύνδεσμος προστέθηκε με επιτυχία!', 'success');
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                showAlert('Σφάλμα: ' + data.message, 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Παρουσιάστηκε σφάλμα κατά την προσθήκη του συνδέσμου.', 'danger');
        });
    });
}

function setupDeleteButtons() {
    const deleteFileButtons = document.querySelectorAll('.delete-file');
    deleteFileButtons.forEach(button => {
        button.addEventListener('click', function() {
            const fileId = this.getAttribute('data-id');
            if (confirm('Είστε βέβαιοι ότι θέλετε να διαγράψετε αυτό το αρχείο;')) {
                deleteThesisFile(fileId);
            }
        });
    });
    
    const deleteLinkButtons = document.querySelectorAll('.delete-link');
    deleteLinkButtons.forEach(button => {
        button.addEventListener('click', function() {
            const linkId = this.getAttribute('data-id');
            if (confirm('Είστε βέβαιοι ότι θέλετε να διαγράψετε αυτόν τον σύνδεσμο;')) {
                deleteThesisLink(linkId);
            }
        });
    });
}

function deleteThesisFile(fileId) {
    console.log('Deleting file with ID:', fileId);
    fetch('/web_php/api/student/delete_thesis_file.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ file_id: fileId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Το αρχείο διαγράφηκε με επιτυχία!', 'success');
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showAlert('Σφάλμα: ' + data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Παρουσιάστηκε σφάλμα κατά τη διαγραφή του αρχείου.', 'danger');
    });
}

function deleteThesisLink(linkId) {
    console.log('Διαγραφή συνδέσμου με ID:', linkId);
    fetch('/web_php/api/student/delete_thesis_link.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ link_id: linkId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Ο σύνδεσμος διαγράφηκε με επιτυχία!', 'success');
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showAlert('Σφάλμα: ' + data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Παρουσιάστηκε σφάλμα κατά τη διαγραφή του συνδέσμου.', 'danger');
    });
}

function updateAcceptedFileTypes(fileType) {
    const fileInput = document.getElementById('draftFile');
    const fileLabel = fileInput.previousElementSibling;
    
    if (!fileInput || !fileLabel) return;
    
    switch (fileType) {
        case 'draft':
            fileInput.setAttribute('accept', '.pdf,.doc,.docx');
            fileLabel.textContent = 'Επιλέξτε αρχείο (PDF, DOC, DOCX)';
            break;
        case 'final':
            fileInput.setAttribute('accept', '.pdf,.doc,.docx');
            fileLabel.textContent = 'Επιλέξτε αρχείο (PDF, DOC, DOCX)';
            break;
        case 'presentation':
            fileInput.setAttribute('accept', '.pdf,.ppt,.pptx');
            fileLabel.textContent = 'Επιλέξτε αρχείο (PDF, PPT, PPTX)';
            break;
        case 'code':
            fileInput.setAttribute('accept', '.zip,.rar,.7z,.tar,.gz');
            fileLabel.textContent = 'Επιλέξτε αρχείο (ZIP, RAR, 7Z, TAR, GZ)';
            break;
        case 'data':
            fileInput.setAttribute('accept', '.csv,.xls,.xlsx,.zip,.rar,.7z');
            fileLabel.textContent = 'Επιλέξτε αρχείο (CSV, XLS, XLSX, ZIP, RAR, 7Z)';
            break;
        default:
            fileInput.setAttribute('accept', '.pdf,.doc,.docx');
            fileLabel.textContent = 'Επιλέξτε αρχείο (PDF, DOC, DOCX)';
    }
}

function validateFileType(file, fileType) {
    const fileName = file.name;
    const fileExtension = fileName.substring(fileName.lastIndexOf('.') + 1).toLowerCase();
    
    console.log('Validating file:', fileName, 'with extension:', fileExtension, 'for type:', fileType);
    
    const allowedExtensions = {
        'draft': ['pdf', 'doc', 'docx'],
        'final': ['pdf', 'doc', 'docx'],
        'presentation': ['pdf', 'ppt', 'pptx'],
        'code': ['zip', 'rar', '7z', 'tar', 'gz'],
        'data': ['csv', 'xls', 'xlsx', 'zip', 'rar', '7z']
    };
    
    if (!allowedExtensions[fileType]) {
        console.error('Unknown file type:', fileType);
        return false;
    }
    
    const result = allowedExtensions[fileType].some(ext => ext.toLowerCase() === fileExtension.toLowerCase());
    console.log('Validation result:', result ? 'valid' : 'invalid');
    return result;
}

function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.role = 'alert';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    const alertContainer = document.getElementById('alertMessages');
    alertContainer.appendChild(alertDiv);
    
    setTimeout(() => {
        const bsAlert = new bootstrap.Alert(alertDiv);
        bsAlert.close();
    }, 5000);
}

function isValidUrl(url) {
    try {
        new URL(url);
        return true;
    } catch (e) {
        return false;
    }
}

function setupThesisPresentationForm() {
    const thesisPresentationForm = document.getElementById('thesisPresentationForm');
    if (!thesisPresentationForm) return;
    
    const presentationTypeSelect = document.getElementById('presentationType');
    if (presentationTypeSelect) {
        presentationTypeSelect.addEventListener('change', function() {
            updatePresentationFields(this.value);
        });
        
        if (presentationTypeSelect.value) {
            updatePresentationFields(presentationTypeSelect.value);
        }
    }
    
    const editPresentationBtn = document.getElementById('editPresentationBtn');
    if (editPresentationBtn) {
        editPresentationBtn.addEventListener('click', function() {
            document.getElementById('presentationView').style.display = 'none';
            document.getElementById('presentationForm').style.display = 'block';
        });
    }
    
    const cancelEditBtn = document.getElementById('cancelEditBtn');
    if (cancelEditBtn) {
        cancelEditBtn.addEventListener('click', function() {
            document.getElementById('presentationForm').style.display = 'none';
            document.getElementById('presentationView').style.display = 'block';
        });
    }
    
    thesisPresentationForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            thesis_id: document.querySelector('input[name="thesis_id"]').value,
            presentationDate: document.getElementById('presentationDate').value,
            presentationTime: document.getElementById('presentationTime').value,
            presentationType: document.getElementById('presentationType').value
        };
        
        const presentationIdInput = document.querySelector('input[name="presentation_id"]');
        if (presentationIdInput) {
            formData.presentation_id = presentationIdInput.value;
        }
        
        if (formData.presentationType === 'physical' || formData.presentationType === 'hybrid') {
            formData.location = document.getElementById('location').value;
            if (!formData.location.trim()) {
                showAlert('Παρακαλώ συμπληρώστε την αίθουσα παρουσίασης.', 'danger');
                return;
            }
        }
        
        if (formData.presentationType === 'online' || formData.presentationType === 'hybrid') {
            formData.onlineLink = document.getElementById('onlineLink').value;
            if (!formData.onlineLink.trim()) {
                showAlert('Παρακαλώ συμπληρώστε τον σύνδεσμο διαδικτυακής παρουσίασης.', 'danger');
                return;
            }
            
            if (!isValidUrl(formData.onlineLink)) {
                showAlert('Παρακαλώ εισάγετε έναν έγκυρο σύνδεσμο.', 'danger');
                return;
            }
        }
        
        const presentationDateTime = new Date(`${formData.presentationDate}T${formData.presentationTime}:00`);
        const now = new Date();
        
        if (presentationDateTime <= now) {
            showAlert('Η ημερομηνία και ώρα παρουσίασης πρέπει να είναι στο μέλλον.', 'danger');
            return;
        }
        
        fetch('/web_php/api/student/save_thesis_presentation.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert(data.message, 'success');
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                showAlert('Σφάλμα: ' + data.message, 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Παρουσιάστηκε σφάλμα κατά την αποθήκευση των στοιχείων παρουσίασης.', 'danger');
        });
    });
}

function updatePresentationFields(presentationType) {
    const locationField = document.getElementById('locationField');
    const onlineLinkField = document.getElementById('onlineLinkField');
    const locationInput = document.getElementById('location');
    const onlineLinkInput = document.getElementById('onlineLink');
    
    locationField.style.display = 'none';
    onlineLinkField.style.display = 'none';
    locationInput.required = false;
    onlineLinkInput.required = false;
    
    switch (presentationType) {
        case 'physical':
            locationField.style.display = 'block';
            locationInput.required = true;
            break;
        case 'online':
            onlineLinkField.style.display = 'block';
            onlineLinkInput.required = true;
            break;
        case 'hybrid':
            locationField.style.display = 'block';
            onlineLinkField.style.display = 'block';
            locationInput.required = true;
            onlineLinkInput.required = true;
            break;
    }
}

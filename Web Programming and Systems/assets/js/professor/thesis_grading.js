
console.log('Thesis grading script loaded');

const enableGradingBtn = document.getElementById('enableGradingBtn');
if (enableGradingBtn) {
    console.log('Enable grading button found immediately:', enableGradingBtn);
    addGradingButtonListener(enableGradingBtn);
}

document.addEventListener('DOMContentLoaded', function() {
    console.log('Thesis grading script - DOM Content Loaded');
    initGradingButton();
});

window.addEventListener('load', function() {
    console.log('Thesis grading script - Window Load');
    initGradingButton();
});

function addGradingButtonListener(button) {
    button.addEventListener('click', function(e) {
        e.preventDefault();
        console.log('Enable grading button clicked');
        const assignmentId = this.getAttribute('data-assignment-id');
        console.log('Assignment ID:', assignmentId);
        enableThesisGrading(assignmentId);
    });
    console.log('Click event listener added to grading button');
}

function initGradingButton() {
    const enableGradingBtn = document.getElementById('enableGradingBtn');
    console.log('Enable grading button found:', enableGradingBtn);
    
    if (enableGradingBtn) {
        addGradingButtonListener(enableGradingBtn);
    }
}

function enableThesisGrading(assignmentId) {
    console.log('Enabling thesis grading for assignment ID:', assignmentId);
    
    const messageContainer = document.getElementById('gradingMessageContainer');
    if (messageContainer) {
        messageContainer.style.display = 'none';
    }
    
    const enableGradingBtn = document.getElementById('enableGradingBtn');
    if (enableGradingBtn) {
        enableGradingBtn.disabled = true;
        enableGradingBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Παρακαλώ περιμένετε...';
    }
    
    const formData = new FormData();
    formData.append('assignment_id', assignmentId);
    
    console.log('Sending request to API...');
    const xhr = new XMLHttpRequest();
    const apiUrl = '/web_php/api/professor/enable_thesis_grading.php';
    console.log('API URL:', apiUrl);
    
    xhr.open('POST', apiUrl, true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            console.log('XHR response received, status:', xhr.status);
            console.log('Response text:', xhr.responseText);
            
            if (xhr.status === 200) {
                try {
                    const data = JSON.parse(xhr.responseText);
                    console.log('Parsed response data:', data);
                    
                    if (data.success) {
                        if (messageContainer) {
                            messageContainer.className = 'alert alert-success';
                            messageContainer.innerHTML = '<i class="fas fa-check-circle"></i> ' + data.message;
                            messageContainer.style.display = 'block';
                        }
                        
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    } else {
                        if (messageContainer) {
                            messageContainer.className = 'alert alert-danger';
                            messageContainer.innerHTML = '<i class="fas fa-exclamation-triangle"></i> ' + data.message;
                            messageContainer.style.display = 'block';
                        }
                        
                        if (enableGradingBtn) {
                            enableGradingBtn.disabled = false;
                            enableGradingBtn.innerHTML = '<i class="fas fa-unlock"></i> Ενεργοποίηση Βαθμολόγησης';
                        }
                    }
                } catch (e) {
                    console.error('Error parsing JSON response:', e);
                    if (messageContainer) {
                        messageContainer.className = 'alert alert-danger';
                        messageContainer.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Σφάλμα κατά την επεξεργασία της απάντησης.';
                        messageContainer.style.display = 'block';
                    }
                    
                    if (enableGradingBtn) {
                        enableGradingBtn.disabled = false;
                        enableGradingBtn.innerHTML = '<i class="fas fa-unlock"></i> Ενεργοποίηση Βαθμολόγησης';
                    }
                }
            } else {
                console.error('HTTP error, status:', xhr.status);
                if (messageContainer) {
                    messageContainer.className = 'alert alert-danger';
                    messageContainer.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Σφάλμα κατά την επικοινωνία με τον διακομιστή.';
                    messageContainer.style.display = 'block';
                }
                
                if (enableGradingBtn) {
                    enableGradingBtn.disabled = false;
                    enableGradingBtn.innerHTML = '<i class="fas fa-unlock"></i> Ενεργοποίηση Βαθμολόγησης';
                }
            }
        }
    };
    
    xhr.send(formData);
}

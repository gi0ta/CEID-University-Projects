
function escapeHtml(text) {
    if (!text) return '';
    
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    
    return text.toString().replace(/[&<>"']/g, function(m) { return map[m]; });
}

let currentTopics = [];

document.addEventListener('DOMContentLoaded', function() {
    const newTopicForm = document.getElementById('new-topic-form');
    if (newTopicForm) {
        newTopicForm.addEventListener('submit', handleNewTopicSubmit);
    }
    
    loadThesisTopics();
});

function loadThesisTopics() {
    const topicsContainer = document.getElementById('topics-container');
    const noTopicsElement = document.getElementById('no-topics');
    const topicsList = document.getElementById('topics-list');
    if (!topicsContainer || !noTopicsElement || !topicsList) return;
    
    fetch(window.location.origin + '/web_php/api/professor/thesis_topics.php', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json'
        },
        credentials: 'include'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.data && data.data.length > 0) {
            if (topicsContainer && noTopicsElement) {
                topicsContainer.style.display = 'block';
                noTopicsElement.style.display = 'none';
            }
            
            topicsList.innerHTML = '';
            
            currentTopics = data.data;
            displayThesisTopics(data.data);
        } else {
            if (topicsContainer && noTopicsElement) {
                topicsContainer.style.display = 'none';
                noTopicsElement.style.display = 'block';
            }
        }
    })
    .catch(error => {
        console.error('Σφάλμα κατά τη φόρτωση των θεμάτων:', error);
        if (topicsContainer && noTopicsElement) {
            if (topicsContainer && noTopicsElement) {
                topicsContainer.style.display = 'none';
                noTopicsElement.style.display = 'block';
            }
        }
    });
}

function editTopic(topicId) {
    const topicToEdit = currentTopics.find(topic => topic.id === topicId);
    if (!topicToEdit) {
        console.error('Topic not found for editing:', topicId);
        alert('Δεν βρέθηκε το θέμα για επεξεργασία.');
        return;
    }

    const form = document.getElementById('new-topic-form');
    form.title.value = topicToEdit.title;
    form.summary.value = topicToEdit.summary || ''; 
    document.getElementById('topicId').value = topicToEdit.id;

    const submitBtn = document.getElementById('submitThesisTopicBtn');
    submitBtn.innerHTML = '<i class="fas fa-edit me-2"></i> Ενημέρωση Θέματος';

    const descriptionFileContainer = document.getElementById('current-description-file-container');
    const descriptionFileInput = form.elements['description_file']; 

    if (topicToEdit.description_file) {
        descriptionFileContainer.innerHTML = `
            <p class="mb-1">Τρέχον αρχείο περιγραφής: 
                <a href="../../${escapeHtml(topicToEdit.description_file)}" target="_blank">
                    ${escapeHtml(topicToEdit.description_file.split('/').pop())}
                </a>
            </p>
            <small class="text-muted">Για να το αλλάξετε, απλά επιλέξτε ένα νέο αρχείο παραπάνω. Αν δεν επιλέξετε νέο αρχείο, θα παραμείνει το τρέχον.</small>
        `;
        descriptionFileContainer.style.display = 'block';
    } else {
        descriptionFileContainer.innerHTML = '';
        descriptionFileContainer.style.display = 'none';
    }
    
    if (descriptionFileInput) { 
        descriptionFileInput.value = '';
    }

    form.scrollIntoView({ behavior: 'smooth', block: 'start' });
    form.title.focus();
}

function displayThesisTopics(topics) {
    const topicsList = document.getElementById('topics-list');
    if (!topicsList) return;
    
    topicsList.innerHTML = '';
    
    topics.forEach(topic => {
        const row = document.createElement('tr');
        
        const createdDate = new Date(topic.created_at);
        const formattedDate = createdDate.toLocaleDateString('el-GR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
        
        row.innerHTML = `
            <td>${escapeHtml(topic.title)}</td>
            <td>${escapeHtml(topic.summary || '-')}</td>
            <td>${formattedDate}</td>
            <td>
                ${topic.description_file ? 
                    `<a href="../../${escapeHtml(topic.description_file)}" target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-file-pdf"></i> Προβολή
                    </a>` : 
                    '<span class="text-muted">-</span>'
                }
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-outline-primary me-1" onclick="editTopic(${topic.id})">
                    <i class="fas fa-edit"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteTopic(${topic.id})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        
        topicsList.appendChild(row);
    });
}

function handleNewTopicSubmit(event) {
    event.preventDefault();
    
    const form = event.target;
    const title = form.title.value.trim();
    const summary = form.summary.value.trim(); 
    const descriptionFile = form.description_file.files[0];
    const topicId = document.getElementById('topicId').value;

    if (!title || !summary) { 
        alert('Παρακαλώ συμπληρώστε τον τίτλο και τη σύνοψη του θέματος.');
        if (!title) form.title.focus();
        else if (!summary) form.summary.focus();
        return;
    }
    
    const action = topicId ? 'Ενημέρωση' : 'Δημιουργία';
    console.log(`${action} θέματος...`);
    console.log('Τίτλος:', title);
    console.log('Σύνοψη:', summary);
    console.log('Αρχείο:', descriptionFile ? descriptionFile.name : 'Δεν υπάρχει');
    if (topicId) {
        console.log('Topic ID:', topicId);
    }

    if (descriptionFile) {
        console.log('Ανέβασμα αρχείου περιγραφής...');
        uploadDescriptionFile(descriptionFile)
            .then(fileData => {
                console.log('Επιτυχής μεταφόρτωση αρχείου:', fileData);
                if (topicId) {
                    updateThesisTopic(topicId, title, summary, fileData.file_path);
                } else {
                    createThesisTopic(title, summary, fileData.file_path);
                }
            })
            .catch(error => {
                console.error('Σφάλμα κατά το ανέβασμα του αρχείου:', error);
                alert('Σφάλμα κατά το ανέβασμα του αρχείου: ' + error.message);
            });
    } else {
        if (topicId) {
            updateThesisTopic(topicId, title, summary, null);
        } else {
            createThesisTopic(title, summary, null);
        }
    }
}

function uploadDescriptionFile(file) {
    return new Promise((resolve, reject) => {
        const formData = new FormData();
        formData.append('file', file);
        
        if (file.size > 10 * 1024 * 1024) {
            reject(new Error('Το αρχείο είναι πολύ μεγάλο. Μέγιστο μέγεθος: 10MB'));
            return;
        }
        
        const allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        if (!allowedTypes.includes(file.type)) {
            reject(new Error('Μη επιτρεπτός τύπος αρχείου. Επιτρέπονται μόνο PDF, DOC και DOCX.'));
            return;
        }
        
        const apiUrl = window.location.origin + '/web_php/api/professor/upload_file.php';
        
        fetch(apiUrl, {
            method: 'POST',
            body: formData,
            credentials: 'include'
        })
        .then(response => {
            if (!response.ok) {
                return response.text().then(text => {
                    try {
                        const errorData = JSON.parse(text);
                        throw new Error(errorData.message || 'Σφάλμα δικτύου: ' + response.status);
                    } catch (e) {
                        throw new Error('Σφάλμα δικτύου: ' + response.status + ' - ' + text);
                    }
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                resolve(data.data);
            } else {
                reject(new Error(data.message || 'Σφάλμα κατά το ανέβασμα του αρχείου'));
            }
        })
        .catch(error => {
            console.error('Σφάλμα κατά το ανέβασμα:', error);
            reject(error);
        });
    });
}

function createThesisTopic(title, summary, descriptionFile) {
    console.log('createThesisTopic κλήθηκε με παραμέτρους:', { title, summary, descriptionFile });
    
    const topicData = {
        title: title,
        summary: summary,
        description_file: descriptionFile
    };
    
    console.log('topicData που θα σταλεί:', topicData);
    
    fetch(window.location.origin + '/web_php/api/professor/thesis_topics.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(topicData),
        credentials: 'include'
    })
    .then(response => {
        console.log('API response status:', response.status);
        if (!response.ok) {
            return response.json().then(data => {
                throw new Error(data.message || `Σφάλμα δικτύου: ${response.status}`);
            }).catch(() => {
                throw new Error(`Σφάλμα δικτύου: ${response.status}`);
            });
        }
        return response.json();
    })
    .then(data => {
        console.log('API response parsed data:', data);
        if (data.success) {
            console.log('Επιτυχής δημιουργία θέματος:', data.data);
            alert('Το θέμα δημιουργήθηκε με επιτυχία!');
            
            resetTopicForm();
            
            console.log('Κλήση loadThesisTopics μετά από insert');
            loadThesisTopics();
        } else {
            console.error('Σφάλμα από το API:', data.message);
            alert('Σφάλμα: ' + (data.message || 'Σφάλμα κατά τη δημιουργία του θέματος'));
        }
    })
    .catch(error => {
        console.error('Σφάλμα κατά τη δημιουργία του θέματος:', error);
        alert('Σφάλμα κατά τη δημιουργία του θέματος: ' + error.message);
    });
}

function updateThesisTopic(topicId, title, summary, filePath) {
    console.log('updateThesisTopic κλήθηκε με παραμέτρους:', { topicId, title, summary, filePath });
    const topicData = {
        topic_id: topicId,
        title: title,
        summary: summary,
    };

    if (filePath) {
        topicData.description_file = filePath; 
    }

    console.log('topicData για ενημέρωση που θα σταλεί:', topicData);

    fetch(window.location.origin + '/web_php/api/professor/thesis_topics.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(topicData),
        credentials: 'include'
    })
    .then(response => {
        console.log('API (update) response status:', response.status);
        if (!response.ok) {
            return response.json().then(data => {
                throw new Error(data.message || `Σφάλμα δικτύου: ${response.status}`);
            }).catch(() => { 
                throw new Error(`Σφάλμα δικτύου: ${response.status}`);
            });
        }
        return response.json();
    })
    .then(data => {
        console.log('API (update) response parsed data:', data);
        if (data.success) {
            alert('Το θέμα ενημερώθηκε με επιτυχία!');
            resetTopicForm(); 
            loadThesisTopics(); 
        } else {
            console.error('Σφάλμα από το API (update):', data.message);
            alert('Σφάλμα: ' + (data.message || 'Σφάλμα κατά την ενημέρωση του θέματος.'));
        }
    })
    .catch(error => {
        console.error('Σφάλμα κατά την ενημέρωση του θέματος:', error);
        alert('Σφάλμα κατά την ενημέρωση του θέματος: ' + error.message);
    });
}

function resetTopicForm() {
    const form = document.getElementById('new-topic-form');
    if (form) {
        form.reset();
    }
    document.getElementById('topicId').value = '';

    const submitBtn = document.getElementById('submitThesisTopicBtn');
    if (submitBtn) {
        submitBtn.innerHTML = '<i class="fas fa-save me-2"></i> Αποθήκευση Θέματος';
    }

    const descriptionFileContainer = document.getElementById('current-description-file-container');
    if (descriptionFileContainer) {
        descriptionFileContainer.innerHTML = '';
        descriptionFileContainer.style.display = 'none';
    }
    const titleInput = document.getElementById('title');
    if (titleInput) {
        titleInput.focus();
    }
}
function deleteTopic(topicId) {
    if (!confirm('Είστε βέβαιοι ότι θέλετε να διαγράψετε αυτό το θέμα;')) {
        return;
    }
    
    fetch(window.location.origin + '/web_php/api/professor/thesis_topics.php?id=' + topicId, {
        method: 'DELETE'
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Σφάλμα δικτύου κατά την απάντηση.');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            alert('Το θέμα διαγράφηκε με επιτυχία!');
            
            loadThesisTopics();
        } else {
            alert('Σφάλμα: ' + (data.message || 'Σφάλμα κατά τη διαγραφή του θέματος'));
        }
    })
    .catch(error => {
        console.error('Σφάλμα κατά τη διαγραφή του θέματος:', error);
        alert('Σφάλμα κατά τη διαγραφή του θέματος: ' + error.message);
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


document.addEventListener('DOMContentLoaded', function() {
    loadAvailableTopics();
});

function loadAvailableTopics() {
    const topicsListContainer = document.getElementById('thesis-topics-list');
    
    fetch(window.location.origin + '/web_php/api/professor/thesis_topics.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Σφάλμα δικτύου κατά την επικοινωνία με τον διακομιστή.');
            }
            return response.json();
        })
        .then(data => {
            if (!data.success) {
                throw new Error(data.message || 'Σφάλμα κατά τη λήψη των θεμάτων.');
            }
            
            displayTopics(data.data, topicsListContainer);
        })
        .catch(error => {
            topicsListContainer.innerHTML = `
                <div class="alert alert-danger" role="alert">
                    <h4 class="alert-heading">Σφάλμα!</h4>
                    <p>${error.message}</p>
                    <hr>
                    <p class="mb-0">Παρακαλώ προσπαθήστε ξανά αργότερα ή επικοινωνήστε με τον διαχειριστή.</p>
                </div>
            `;
        });
}

function displayTopics(topics, container) {
    if (topics.length === 0) {
        container.innerHTML = `
            <div class="alert alert-info" role="alert">
                <p>Δεν βρέθηκαν θέματα προς ανάθεση.</p>
            </div>
        `;
        return;
    }
    
    let html = '<div class="row row-cols-1 row-cols-md-2 g-4">';
    
    topics.forEach(topic => {
        const createdDate = new Date(topic.created_at);
        const formattedDate = createdDate.toLocaleDateString('el-GR', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        
        html += `
            <div class="col">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">${escapeHtml(topic.title)}</h5>
                        <p class="card-text">${escapeHtml(topic.summary)}</p>
                    </div>
                    <div class="card-footer bg-transparent">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">Δημιουργήθηκε: ${formattedDate}</small>
                            <a href="assign_student_to_topic.php?topic_id=${topic.id}" class="btn btn-primary">
                                <i class="bi bi-person-plus-fill"></i> Ανάθεση σε Φοιτητή
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    container.innerHTML = html;
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


document.addEventListener('DOMContentLoaded', function() {
    loadInvitations();
});

async function loadInvitations() {
    const invitationsListDiv = document.getElementById('invitations-list');
    const loadingInvitations = document.getElementById('loading-invitations');
    const noInvitationsDiv = document.getElementById('no-invitations');

    if (!invitationsListDiv) {
        return;
    }

    try {
        if (loadingInvitations) {
            loadingInvitations.style.display = 'block';
        }
        
        if (noInvitationsDiv) {
            noInvitationsDiv.style.display = 'none';
        }

        const currentContent = invitationsListDiv.innerHTML;
        const ulElement = invitationsListDiv.querySelector('ul');
        if (ulElement) {
            ulElement.remove();
        }

        const fetchUrlLoad = `${SITE_URL}/api/professor/get_invitations.php`;
        
        try {
            const response = await fetch(fetchUrlLoad, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'include'
            });
            
            if (loadingInvitations) {
                loadingInvitations.style.display = 'none';
            }
            
            if (!response.ok) {
                if (noInvitationsDiv) {
                    noInvitationsDiv.style.display = 'block';
                }
                return;
            }
            
            let result;
            try {
                result = await response.json();
            } catch (jsonError) {
                if (noInvitationsDiv) {
                    noInvitationsDiv.style.display = 'block';
                }
                return;
            }
            
            if (result && result.success && result.invitations && result.invitations.length > 0) {
                if (noInvitationsDiv) {
                    noInvitationsDiv.style.display = 'none';
                }
                const ul = document.createElement('ul');
                ul.className = 'list-group';

                result.invitations.forEach(invitation => {
                    const li = document.createElement('li');
                    li.className = 'list-group-item d-flex justify-content-between align-items-center flex-wrap';
                    li.innerHTML = `
                        <div>
                            <h5 class="mb-1">Πρόσκληση για τη διπλωματική: ${escapeHTML(invitation.thesis_title)}</h5>
                            <p class="mb-1">Φοιτητής/τρια: ${escapeHTML(invitation.student_name)} ${escapeHTML(invitation.student_surname)}</p>
                            <p class="mb-1">Από τον καθηγητή: ${escapeHTML(invitation.inviting_professor_name)} ${escapeHTML(invitation.inviting_professor_surname)}</p>
                            <small>Ημερομηνία Πρόσκλησης: ${new Date(invitation.invitation_date).toLocaleDateString('el-GR')}</small>
                        </div>
                        <div class="mt-2 mt-md-0">
                            <button class="btn btn-success btn-sm me-2 action-btn" data-invitation-id="${invitation.invitation_id}" data-action="accept">
                                <i class="fas fa-check"></i> Αποδοχή
                            </button>
                            <button class="btn btn-danger btn-sm action-btn" data-invitation-id="${invitation.invitation_id}" data-action="decline">
                                <i class="fas fa-times"></i> Απόρριψη
                            </button>
                        </div>
                    `;
                    ul.appendChild(li);
                });
                invitationsListDiv.appendChild(ul);
                addEventListenersToButtons();
            } else {
                if (noInvitationsDiv) {
                    noInvitationsDiv.style.display = 'block';
                }
            }
        } catch (fetchError) {
            if (loadingInvitations) {
                loadingInvitations.style.display = 'none';
            }
            if (noInvitationsDiv) {
                noInvitationsDiv.style.display = 'block';
            }
        }
    } catch (error) {
        if (loadingInvitations) {
            loadingInvitations.style.display = 'none';
        }
        if (noInvitationsDiv) {
            noInvitationsDiv.style.display = 'block';
        }
    }
}

function addEventListenersToButtons() {
    const actionButtons = document.querySelectorAll('.action-btn');
    actionButtons.forEach(button => {
        button.addEventListener('click', function() {
            const invitationId = this.dataset.invitationId;
            const action = this.dataset.action;
            handleInvitationResponse(invitationId, action, this);
        });
    });
}

async function handleInvitationResponse(invitationId, action, buttonElement) {
    const originalButtonText = buttonElement.innerHTML;
    buttonElement.disabled = true;
    buttonElement.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Περιμένετε...';

    const parentLi = buttonElement.closest('li');
    const siblingButton = parentLi.querySelector(`.action-btn[data-action="${action === 'accept' ? 'decline' : 'accept'}"]`);
    if (siblingButton) {
        siblingButton.disabled = true;
    }
    
    try {
        console.log(`Αποστολή αιτήματος ${action} για την πρόσκληση ${invitationId}`);
        const fetchUrl = `${SITE_URL}/api/professor/${action === 'accept' ? 'accept' : 'reject'}_committee_invitation.php`;
        const response = await fetch(fetchUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            credentials: 'include',
            body: JSON.stringify({ invitation_id: invitationId })
        });

        console.log(`API Response Status: ${response.status}`);
        const responseData = await response.json();
        console.log('API Response Data:', responseData);
        
        if (!response.ok) {
            console.error(`Σφάλμα API: ${response.status} - ${responseData.message || 'Άγνωστο σφάλμα'}`);
        }
        
        if (responseData && responseData.message) {
            alert(responseData.message);
        }

        await loadInvitations();
        
    } catch (error) {
        console.error('Σφάλμα κατά την επεξεργασία της πρόσκλησης:', error);
        alert('Παρουσιάστηκε σφάλμα κατά την επεξεργασία της πρόσκλησης.');
        await loadInvitations();
    }
}

function escapeHTML(str) {
    if (str === null || str === undefined) return '';
    return String(str).replace(/[&<>'"/]/g, function (s) {
        return {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;',
            '/': '&#x2F;'
        }[s];
    });
}


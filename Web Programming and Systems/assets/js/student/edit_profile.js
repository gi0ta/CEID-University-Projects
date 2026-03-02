
document.addEventListener('DOMContentLoaded', function() {
    let SITE_URL;
    const metaTag = document.querySelector('meta[name="site-url"]');
    
    if (metaTag) {
        SITE_URL = metaTag.getAttribute('content');
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
    
    const editProfileForm = document.getElementById('edit-profile-form');
    const alertContainer = document.getElementById('alert-container');
    
    if (editProfileForm) {
        editProfileForm.addEventListener('submit', function(event) {
            event.preventDefault();
            
            const formData = {
                email: document.getElementById('email').value,
                street: document.getElementById('street').value,
                street_number: document.getElementById('street_number').value,
                city: document.getElementById('city').value,
                postcode: document.getElementById('postcode').value,
                mobile_phone: document.getElementById('mobile_phone').value,
                home_phone: document.getElementById('home_phone').value
            };
            
            if (!validateForm(formData)) {
                return;
            }
            
            updateProfile(formData);
        });
    }
    
    function validateForm(formData) {
        alertContainer.innerHTML = '';
        
        if (!formData.email || !formData.street || !formData.street_number || !formData.city || !formData.postcode || !formData.mobile_phone) {
            showAlert('Παρακαλώ συμπληρώστε όλα τα υποχρεωτικά πεδία.', 'danger');
            return false;
        }
        
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(formData.email)) {
            showAlert('Παρακαλώ εισάγετε ένα έγκυρο email.', 'danger');
            return false;
        }
        
        const phoneRegex = /^[0-9]{10}$/;
        if (!phoneRegex.test(formData.mobile_phone)) {
            showAlert('Παρακαλώ εισάγετε ένα έγκυρο κινητό τηλέφωνο (10 ψηφία).', 'danger');
            return false;
        }
        
        if (formData.home_phone && !phoneRegex.test(formData.home_phone)) {
            showAlert('Παρακαλώ εισάγετε ένα έγκυρο σταθερό τηλέφωνο (10 ψηφία).', 'danger');
            return false;
        }
        
        return true;
    }
    
    function updateProfile(formData) {
        showAlert('Γίνεται αποθήκευση των αλλαγών...', 'info');
        
        fetch(`${SITE_URL}/api/student/update_profile.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        })
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            alertContainer.innerHTML = '';
            
            if (data.success) {
                showAlert(data.message || 'Οι αλλαγές αποθηκεύτηκαν με επιτυχία!', 'success');
            } else {
                showAlert(data.error || 'Προέκυψε σφάλμα κατά την αποθήκευση των αλλαγών.', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Προέκυψε σφάλμα επικοινωνίας με τον server.', 'danger');
        });
    }
    
    function showAlert(message, type) {
        const alert = document.createElement('div');
        alert.className = `alert alert-${type} alert-dismissible fade show`;
        alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        alertContainer.innerHTML = '';
        alertContainer.appendChild(alert);
    }
});

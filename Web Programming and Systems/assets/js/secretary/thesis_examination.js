
document.addEventListener('DOMContentLoaded', function() {
    console.log('Thesis examination page loaded');

    const completeButton = document.getElementById('complete-thesis-btn');
    
    if (completeButton) {
        completeButton.addEventListener('click', function(e) {
            const finalGradeElement = document.getElementById('final-grade');
            const nemertisUrlElement = document.getElementById('nemertis-url');
            
            if (!finalGradeElement || !finalGradeElement.textContent || finalGradeElement.textContent.includes('Δεν έχει καταχωρηθεί')) {
                e.preventDefault();
                alert('Δεν μπορείτε να ολοκληρώσετε τη διπλωματική εργασία χωρίς βαθμό.');
                return false;
            }
            
            if (!nemertisUrlElement || !nemertisUrlElement.textContent || nemertisUrlElement.textContent.includes('Δεν έχει αναρτηθεί')) {
                e.preventDefault();
                alert('Δεν μπορείτε να ολοκληρώσετε τη διπλωματική εργασία χωρίς σύνδεσμο Νημερτής.');
                return false;
            }
            
            if (!confirm('Είστε βέβαιοι ότι θέλετε να ολοκληρώσετε τη διπλωματική εργασία; Η ενέργεια αυτή δεν μπορεί να αναιρεθεί.')) {
                e.preventDefault();
                return false;
            }
        });
    }

    const thesisStatusElement = document.getElementById('thesis-status');
    if (thesisStatusElement && thesisStatusElement.textContent.includes('Περατωμένη') && completeButton) {
        completeButton.disabled = true;
        completeButton.classList.add('disabled');
        completeButton.title = 'Η διπλωματική εργασία έχει ήδη ολοκληρωθεί';
    }
});

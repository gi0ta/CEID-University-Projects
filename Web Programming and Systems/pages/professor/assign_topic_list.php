<?php
require_once __DIR__ . '/../../includes/init.php';

if (!User::isLoggedIn() || User::getCurrentUserRoleType() !== 'professor') {
    header("Location: " . SITE_URL . "/pages/auth/login.php");
    exit();
}

$pageTitle = "Επιλογή Θέματος προς Ανάθεση";

include_once __DIR__ . '/../../templates/header.php';

?>

<div class="container mt-4">
    <h2><?php echo $pageTitle; ?></h2>
    <p>Επιλέξτε ένα από τα παρακάτω διαθέσιμα θέματα για να το αναθέσετε σε φοιτητή.</p>

    <div id="thesis-topics-list">
        <!-- Η λίστα των θεμάτων θα φορτωθεί εδώ μέσω JavaScript -->
    </div>

    <?php /* TODO:
        1. Fetch available (unassigned) thesis topics created by this professor.
        2. Display them as a list or cards.
        3. Each item should be clickable and lead to a new page: assign_student_to_topic.php?topic_id=XYZ
        4. No edit/delete functionality here, just selection.
    */ ?>
</div>

<?php
include_once __DIR__ . '/../../templates/footer.php';
?>

<!-- Φόρτωση του JavaScript για τη λίστα θεμάτων -->
<script src="<?php echo SITE_URL; ?>/assets/js/professor/assign_topic_list.js"></script>

    </main>
    
    <footer class="bg-light py-4 mt-auto">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0">&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?> - Πανεπιστήμιο Πατρών</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">Τμήμα Μηχανικών Η/Υ και Πληροφορικής</p>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
    
    <!-- Additional page-specific scripts -->
    <?php if (isset($additionalScripts)) echo $additionalScripts; ?>
</body>
</html>

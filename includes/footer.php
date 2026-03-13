<?php
/**
 * Footer Include
 * Include at the bottom of every page
 */
?>
    </div> <!-- closing main container from header because footer was ending up inside it -->
    </main>

    <footer>
        © 2026 Event RSVP System. All rights reserved.
    </footer>

    <script>
        // Close dismissible alerts
        document.querySelectorAll('.alert-close, .s-close').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                if (this.closest('.alert')) {
                    this.closest('.alert').style.display = 'none';
                } else if (this.closest('.success-banner')) {
                    this.closest('.success-banner').classList.remove('show');
                }
            });
        });

        // Optional confirmation links/buttons
        document.querySelectorAll('[data-confirm]').forEach(item => {
            item.addEventListener('click', function(e) {
                const message = this.getAttribute('data-confirm') || 'Are you sure?';
                if (!confirm(message)) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>

<?php
/**
 * Footer Include
 * Include at the bottom of every page
 */
?>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2026 Event RSVP System. All rights reserved.</p>
        </div>
    </footer>

    <script>
        // Close dismissible alerts
        document.querySelectorAll('.alert-close').forEach(btn => {
            btn.addEventListener('click', function() {
                this.parentElement.style.display = 'none';
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

        // Simple form validation
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const inputs = this.querySelectorAll('input[required], select[required]');
                let firstInvalid = null;
                inputs.forEach(input => {
                    if (input.value.trim() === '') {
                        if (!firstInvalid) {
                            firstInvalid = input;
                        }
                        input.style.borderColor = 'var(--color-error)';
                    } else {
                        input.style.borderColor = '';
                    }
                });

                if (firstInvalid) {
                    e.preventDefault();
                    firstInvalid.focus();
                }
            });
        });
    </script>
</body>
</html>

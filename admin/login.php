<?php

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

if (is_admin_logged_in()) {
    app_redirect('admin/index.php');
}

$page_title = 'Admin Login';
$show_nav = false;

$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$username = trim($_POST['username'] ?? '');
	$password = $_POST['password'] ?? '';

	if ($username === '' || $password === '') {
		$error = 'Please enter both username and password.';
	} else {
		$sql = 'SELECT id, username, password_hash FROM admins WHERE username = :username LIMIT 1';
		$stmt = $pdo->prepare($sql);
		$stmt->execute(['username' => $username]);
		$admin = $stmt->fetch();

		if ($admin && password_verify($password, $admin['password_hash'])) {
			$_SESSION['admin_id'] = $admin['id'];
			$_SESSION['admin_username'] = $admin['username'];

            app_redirect('admin/index.php');
		} else {
			$error = 'Invalid username or password.';
		}
	}
}

$main_class = 'main-login';
$main_container_class = 'main-wrap-login';

require_once __DIR__ . '/../includes/header.php';
?>

<div class="card-wrap">
    <div class="card">
        <div class="card-accent"></div>
        <div class="card-body">
            <div class="card-header">
                <span class="lock-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                </span>
                <h2 class="card-title">Admin Login</h2>
                <p class="card-sub">Enter your credentials to continue</p>
            </div>

            <?php if ($error !== ''): ?>
                <div class="error-box" style="display: block;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="field">
                    <label for="username">Username</label>
                    <div class="input-wrap">
                        <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" placeholder="admin" required>
                    </div>
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <div class="input-wrap">
                        <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                        </svg>
                        <input type="password" id="password" name="password" class="has-toggle" placeholder="••••••••" required>
                        <button type="button" class="toggle-btn" id="togglePassword">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-full">Login</button>
            </form>

            <p class="foot-note">Not an Admin? <a href="<?php echo app_path('public/rsvp.php'); ?>">Submit an RSVP</a></p>
        </div>
    </div>
</div>

<script>
    document.getElementById('togglePassword').addEventListener('click', function(e) {
        e.preventDefault();
        const input = document.getElementById('password');
        const btn = this;
        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';
        
        // Update icon - eye vs eye-off
        btn.innerHTML = isPassword ? 
            '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>' :
            '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>';
    });
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

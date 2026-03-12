<?php

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

if (is_admin_logged_in()) {
    app_redirect('admin/index.php');
}

$page_title = 'Admin Login';
$is_admin_page = true;
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

require_once __DIR__ . '/../includes/header.php';
?>

<div class="card card-narrow">
    <div class="card-header">
        <h2>Admin Login</h2>
    </div>
    <div class="card-body">
        <?php if ($error !== ''): ?>
            <div class="alert alert-error">
                <span><?php echo htmlspecialchars($error); ?></span>
                <button class="alert-close">×</button>
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="form-plain">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="btn-primary">Login</button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

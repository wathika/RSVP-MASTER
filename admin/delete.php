<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
require_admin_login();

$page_title = 'Delete RSVP';
$is_admin_page = true;

$rsvp_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($rsvp_id === 0) {
    app_redirect('admin/index.php');
}

// Fetch RSVP with guest information for confirmation
$sql = 'SELECT 
            rsvps.id AS rsvp_id,
            rsvps.guest_id,
            guests.name,
            guests.email
        FROM rsvps
        INNER JOIN guests ON rsvps.guest_id = guests.id
        WHERE rsvps.id = :rsvp_id
        LIMIT 1';

$stmt = $pdo->prepare($sql);
$stmt->execute(['rsvp_id' => $rsvp_id]);
$rsvp = $stmt->fetch();

if (!$rsvp) {
    app_redirect('admin/index.php');
}

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    try {
        // Delete guest record (CASCADE will delete the RSVP automatically)
        $delete_sql = 'DELETE FROM guests WHERE id = :guest_id';
        $delete_stmt = $pdo->prepare($delete_sql);
        $delete_stmt->execute(['guest_id' => $rsvp['guest_id']]);

        app_redirect('admin/index.php?deleted=1');
    } catch (PDOException $e) {
        $error = 'An error occurred while deleting the RSVP.';
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<a class="back-link" href="<?php echo app_path('admin/index.php'); ?>">← Back to Dashboard</a>

<h1 class="page-title">Delete RSVP</h1>

<?php if (isset($error)): ?>
    <div class="alert alert-error">
        <span><?php echo htmlspecialchars($error); ?></span>
        <button class="alert-close">×</button>
    </div>
<?php endif; ?>

<div class="alert alert-error" style="border-left: 4px solid var(--color-error);">
    <strong>Warning:</strong> You are about to delete the following RSVP. This action cannot be undone.
</div>

<div class="card">
    <div class="card-header">
        <h3>RSVP Information</h3>
    </div>
    <div class="card-body">
        <div class="info-row">
            <span class="info-label">Name:</span>
            <span class="info-value"><?php echo htmlspecialchars($rsvp['name']); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Email:</span>
            <span class="info-value"><?php echo htmlspecialchars($rsvp['email']); ?></span>
        </div>
    </div>
</div>

<form method="POST" action="">
    <input type="hidden" name="confirm_delete" value="1">
    <div class="btn-group">
        <button type="submit" class="btn-danger">Confirm Delete</button>
        <a href="<?php echo app_path('admin/index.php'); ?>" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

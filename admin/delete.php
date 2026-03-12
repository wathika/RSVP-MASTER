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
            rsvps.dietary_preference,
            rsvps.seat_number,
            guests.name,
            guests.email,
            guests.phone
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

// Helper function to compute initials
function get_initials($name) {
    $parts = array_filter(explode(' ', trim($name)));
    return strtoupper(implode('', array_map(fn($w) => $w[0] ?? '', $parts)));
}

// Helper function for dietary badge class
function get_dietary_badge_class($pref) {
    $pref_lower = strtolower(str_replace('-', '', $pref));
    if ($pref_lower === 'none') return 'badge-none';
    if ($pref_lower === 'halal') return 'badge-halal';
    if ($pref_lower === 'vegetarian') return 'badge-vegetarian';
    if ($pref_lower === 'vegan') return 'badge-vegan';
    if ($pref_lower === 'glutenfree') return 'badge-glutenfree';
    return 'badge-none';
}

// Handle deletion
$error = '';
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

<a class="back-link" href="<?php echo app_path('admin/index.php'); ?>">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <line x1="19" y1="12" x2="5" y2="12"></line>
        <polyline points="12 19 5 12 12 5"></polyline>
    </svg>
    Back to Dashboard
</a>

<div class="page-header">
    <h1 class="page-title">Delete RSVP</h1>
    <p class="page-sub">This action cannot be undone</p>
</div>

<?php if ($error !== ''): ?>
    <div class="error-box" style="display: block; margin-bottom: 24px;">
        <?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>

<div class="warning-banner">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3.05h16.94a2 2 0 0 0 1.71-3.05L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
        <line x1="12" y1="9" x2="12" y2="13"></line>
        <line x1="12" y1="17" x2="12.01" y2="17"></line>
    </svg>
    <div>
        <strong>Warning:</strong> Deleting this RSVP will permanently remove the guest record and all associated data. This action cannot be undone.
    </div>
</div>

<!-- Guest Info Card with Rose Accent -->
<div class="card">
    <div class="card-accent-rose"></div>
    <div class="card-body">
        <div class="card-section-title">RSVP Information</div>
        
        <div class="guest-row">
            <span class="avatar"><?php echo get_initials($rsvp['name']); ?></span>
            <div>
                <div class="g-name"><?php echo htmlspecialchars($rsvp['name']); ?></div>
                <div class="g-email"><?php echo htmlspecialchars($rsvp['email']); ?></div>
            </div>
        </div>

        <div class="info-table">
            <div class="info-row">
                <span class="info-label">Phone</span>
                <span class="info-value"><?php echo htmlspecialchars($rsvp['phone']); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Dietary Preference</span>
                <span class="badge <?php echo get_dietary_badge_class($rsvp['dietary_preference']); ?>">
                    <?php echo htmlspecialchars($rsvp['dietary_preference']); ?>
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Seat Assignment</span>
                <span class="info-value">
                    <?php if ($rsvp['seat_number'] !== null): ?>
                        Seat <?php echo htmlspecialchars($rsvp['seat_number']); ?>
                    <?php else: ?>
                        Not assigned
                    <?php endif; ?>
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Deletion Form -->
<form method="POST" action="">
    <input type="hidden" name="confirm_delete" value="1">
    
    <div class="form-actions" style="justify-content: flex-start; gap: 12px;">
        <a href="<?php echo app_path('admin/index.php'); ?>" class="btn btn-outline">Cancel</a>
        <button type="submit" class="btn btn-rose">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="3 6 5 6 21 6"></polyline>
                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                <line x1="10" y1="11" x2="10" y2="17"></line>
                <line x1="14" y1="11" x2="14" y2="17"></line>
            </svg>
            Delete RSVP
        </button>
    </div>
</form>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

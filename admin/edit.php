<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
require_admin_login();

$page_title = 'Edit RSVP';
$is_admin_page = true;

$success = '';
$error = '';
$rsvp_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($rsvp_id === 0) {
    app_redirect('admin/index.php');
}

// Fetch RSVP with guest information
$sql = 'SELECT 
            rsvps.id AS rsvp_id,
            rsvps.guest_id,
            rsvps.seat_number,
            rsvps.dietary_preference,
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $dietary_preference = trim($_POST['dietary_preference'] ?? '');

    // Server-side validation
    if ($name === '' || $email === '' || $phone === '' || $dietary_preference === '') {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } else {
        try {
            $pdo->beginTransaction();

            // Update guest information
            $sql_guest = 'UPDATE guests SET name = :name, email = :email, phone = :phone WHERE id = :guest_id';
            $stmt_guest = $pdo->prepare($sql_guest);
            $stmt_guest->execute([
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'guest_id' => $rsvp['guest_id']
            ]);

            // Update RSVP dietary preference
            $sql_rsvp = 'UPDATE rsvps SET dietary_preference = :dietary_preference WHERE id = :rsvp_id';
            $stmt_rsvp = $pdo->prepare($sql_rsvp);
            $stmt_rsvp->execute([
                'dietary_preference' => $dietary_preference,
                'rsvp_id' => $rsvp_id
            ]);

            $pdo->commit();

            $success = 'RSVP updated successfully.';
            
            // Update local data
            $rsvp['name'] = $name;
            $rsvp['email'] = $email;
            $rsvp['phone'] = $phone;
            $rsvp['dietary_preference'] = $dietary_preference;

        } catch (PDOException $e) {
            $pdo->rollBack();

            // Check for duplicate email (unique constraint violation)
            if ($e->getCode() == 23000) {
                $error = 'This email is already in use by another guest.';
            } else {
                $error = 'An error occurred. Please try again.';
            }
        }
    }
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

require_once __DIR__ . '/../includes/header.php';
?>

<div class="main-wrap">
    <a class="back-link" href="<?php echo app_path('admin/index.php'); ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="19" y1="12" x2="5" y2="12"></line>
            <polyline points="12 19 5 12 12 5"></polyline>
        </svg>
        Back to Dashboard
    </a>

    <div class="page-header">
        <h1 class="page-title">Edit RSVP</h1>
        <p class="page-sub">Update guest information and preferences</p>
    </div>

    <?php if ($success !== ''): ?>
        <div class="success-banner show">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
            <div>
                <div class="s-title">Changes saved successfully</div>
                <div class="s-sub">The guest information has been updated.</div>
            </div>
            <button class="s-close">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <div class="error-box" style="display: block;">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <!-- Guest Info Card -->
    <div class="guest-card">
        <span class="avatar"><?php echo get_initials($rsvp['name']); ?></span>
        <div style="flex: 1;">
            <div class="g-name"><?php echo htmlspecialchars($rsvp['name']); ?></div>
            <div class="g-email"><?php echo htmlspecialchars($rsvp['email']); ?></div>
        </div>
        <div style="text-align: right;">
            <?php if ($rsvp['seat_number'] !== null): ?>
                <span class="seat-pill">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M6 4h12v5H6V4zm0 10h12v6H6v-6z"></path>
                    </svg>
                    <span class="seat-text">Seat <?php echo htmlspecialchars($rsvp['seat_number']); ?></span>
                </span>
            <?php else: ?>
                <span class="no-seat">Not assigned</span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Edit Form -->
    <div class="card">
        <div class="card-accent"></div>
        <div class="card-body">
            <form method="POST" action="">
                <div class="field">
                    <label for="name">Name <span class="req">*</span></label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($rsvp['name']); ?>" placeholder="Full name" required>
                </div>

                <div class="field">
                    <label for="email">Email <span class="req">*</span></label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($rsvp['email']); ?>" placeholder="guest@example.com" required>
                </div>

                <div class="field">
                    <label for="phone">Phone <span class="req">*</span></label>
                    <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($rsvp['phone']); ?>" placeholder="+1 (555) 000-0000" required>
                </div>

                <div class="field">
                    <label for="dietary_preference">Dietary Preference <span class="req">*</span></label>
                    <select id="dietary_preference" name="dietary_preference" required>
                        <option value="">-- Select an option --</option>
                        <option value="None" <?php echo $rsvp['dietary_preference'] === 'None' ? 'selected' : ''; ?>>None</option>
                        <option value="Vegetarian" <?php echo $rsvp['dietary_preference'] === 'Vegetarian' ? 'selected' : ''; ?>>Vegetarian</option>
                        <option value="Vegan" <?php echo $rsvp['dietary_preference'] === 'Vegan' ? 'selected' : ''; ?>>Vegan</option>
                        <option value="Halal" <?php echo $rsvp['dietary_preference'] === 'Halal' ? 'selected' : ''; ?>>Halal</option>
                        <option value="Gluten-Free" <?php echo $rsvp['dietary_preference'] === 'Gluten-Free' ? 'selected' : ''; ?>>Gluten-Free</option>
                        <option value="Other" <?php echo $rsvp['dietary_preference'] === 'Other' ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>

                <div class="form-actions">
                    <a href="<?php echo app_path('admin/index.php'); ?>" class="btn btn-outline">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

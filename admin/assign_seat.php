<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
require_admin_login();

$page_title = 'Assign Seat';
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

// Helper function to compute initials
function get_initials($name) {
    $parts = array_filter(explode(' ', trim($name)));
    return strtoupper(implode('', array_map(fn($w) => $w[0] ?? '', $parts)));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $seat_number = trim($_POST['seat_number'] ?? '');

    // Convert empty string to NULL for unassigned
    $new_seat = ($seat_number === '' || $seat_number === 'unassigned') ? null : (int)$seat_number;

    // If selection is same as current value, do not write to DB
    if ($new_seat === ($rsvp['seat_number'] !== null ? (int)$rsvp['seat_number'] : null)) {
        $success = 'No changes made. Seat remains ' . ($new_seat === null ? 'unassigned.' : ('Seat ' . $new_seat . '.'));
    } elseif ($new_seat !== null) {
        // Validate seat number range
        if ($new_seat < 1 || $new_seat > 20) {
            $error = 'Seat number must be between 1 and 20.';
        } else {
            // Check if seat is already taken by another RSVP
            $check_sql = 'SELECT id FROM rsvps WHERE seat_number = :seat_number AND id != :rsvp_id LIMIT 1';
            $check_stmt = $pdo->prepare($check_sql);
            $check_stmt->execute([
                'seat_number' => $new_seat,
                'rsvp_id' => $rsvp_id
            ]);
            $existing = $check_stmt->fetch();

            if ($existing) {
                $error = 'Seat number ' . $new_seat . ' is already assigned to another guest.';
            } else {
                // Update seat number
                $update_sql = 'UPDATE rsvps SET seat_number = :seat_number WHERE id = :rsvp_id';
                $update_stmt = $pdo->prepare($update_sql);
                $update_stmt->execute([
                    'seat_number' => $new_seat,
                    'rsvp_id' => $rsvp_id
                ]);

                $success = 'Seat number ' . $new_seat . ' assigned successfully.';
                $rsvp['seat_number'] = $new_seat;
            }
        }
    } else {
        // Unassign seat (set to NULL)
        $update_sql = 'UPDATE rsvps SET seat_number = NULL WHERE id = :rsvp_id';
        $update_stmt = $pdo->prepare($update_sql);
        $update_stmt->execute(['rsvp_id' => $rsvp_id]);

        $success = 'Seat number unassigned successfully.';
        $rsvp['seat_number'] = null;
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
        <h1 class="page-title">Assign Seat</h1>
        <p class="page-sub">Select a seat number for this guest</p>
    </div>

    <?php if ($success !== ''): ?>
        <div class="success-banner show">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
            <div>
                <div class="s-title">Seat update</div>
                <div class="s-sub"><?php echo htmlspecialchars($success); ?></div>
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

    <!-- Guest Information Card -->
    <div class="guest-card">
        <span class="avatar"><?php echo get_initials($rsvp['name']); ?></span>
        <div>
            <div class="g-name"><?php echo htmlspecialchars($rsvp['name']); ?></div>
            <div class="g-email"><?php echo htmlspecialchars($rsvp['email']); ?></div>
        </div>
    </div>

    <div class="info-table">
        <div class="info-row">
            <span class="info-label">Dietary Preference</span>
            <span class="badge <?php echo get_dietary_badge_class($rsvp['dietary_preference']); ?>">
                <?php echo htmlspecialchars($rsvp['dietary_preference']); ?>
            </span>
        </div>
        <div class="info-row">
            <span class="info-label">Current Seat</span>
            <span class="info-value">
                <?php if ($rsvp['seat_number'] !== null): ?>
                    Seat <?php echo htmlspecialchars($rsvp['seat_number']); ?>
                <?php else: ?>
                    <em>Not assigned</em>
                <?php endif; ?>
            </span>
        </div>
    </div>

    <!-- Seat Assignment Form -->
    <div class="card">
        <div class="card-accent"></div>
        <div class="card-body">
            <form method="POST" action="" id="assignForm">
                <div class="preview">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                    </svg>
                    <span id="previewText">Select a seat number</span>
                </div>

                <div class="field">
                    <label for="seatSelect">Select Seat <span class="req">*</span></label>
                    <select id="seatSelect" name="seat_number" required>
                        <option value="">-- Choose a seat --</option>
                        <option value="unassigned" <?php echo $rsvp['seat_number'] === null ? 'selected' : ''; ?>>Unassigned</option>
                        <?php for ($i = 1; $i <= 20; $i++): ?>
                            <option value="<?php echo $i; ?>" <?php echo $rsvp['seat_number'] == $i ? 'selected' : ''; ?>>
                                Seat <?php echo $i; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="form-actions">
                    <a href="<?php echo app_path('admin/index.php'); ?>" class="btn btn-outline">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        Update Seat
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const seatSelect = document.getElementById('seatSelect');
    const preview = document.querySelector('.preview');
    const previewText = document.getElementById('previewText');
    const guestName = '<?php echo addslashes($rsvp['name']); ?>';

    if (seatSelect) {
        seatSelect.addEventListener('change', function() {
            if (this.value && this.value !== '') {
                let text;
                if (this.value === 'unassigned') {
                    text = guestName + ' will be unassigned';
                } else {
                    text = guestName + ' will be assigned to Seat ' + this.value;
                }
                previewText.textContent = text;
                preview.classList.add('show');
            } else {
                preview.classList.remove('show');
            }
        });

        // Trigger preview if a seat is already selected
        if (seatSelect.value) {
            seatSelect.dispatchEvent(new Event('change'));
        }
    }
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

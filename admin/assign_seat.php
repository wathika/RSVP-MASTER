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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $seat_number = trim($_POST['seat_number'] ?? '');

    // Convert empty string to NULL for unassigned
    $new_seat = ($seat_number === '' || $seat_number === 'unassigned') ? null : (int)$seat_number;

    if ($new_seat !== null) {
        // Validate seat number range
        if ($new_seat < 1 || $new_seat > 50) {
            $error = 'Seat number must be between 1 and 50.';
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

require_once __DIR__ . '/../includes/header.php';
?>

<a class="back-link" href="<?php echo app_path('admin/index.php'); ?>">← Back to Dashboard</a>

<h1 class="page-title">Assign Seat Number</h1>

<div class="card">
    <div class="card-header">
        <h3>Guest Information</h3>
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
        <div class="info-row">
            <span class="info-label">Dietary Preference:</span>
            <span class="info-value"><?php echo htmlspecialchars($rsvp['dietary_preference']); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Current Seat:</span>
            <span class="info-value"><?php echo $rsvp['seat_number'] !== null ? htmlspecialchars($rsvp['seat_number']) : 'Not assigned'; ?></span>
        </div>
    </div>
</div>

<?php if ($success !== ''): ?>
    <div class="alert alert-success">
        <span><?php echo htmlspecialchars($success); ?></span>
        <button class="alert-close">×</button>
    </div>
<?php endif; ?>

<?php if ($error !== ''): ?>
    <div class="alert alert-error">
        <span><?php echo htmlspecialchars($error); ?></span>
        <button class="alert-close">×</button>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3>Assign or Update Seat</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="" class="form-plain">
            <div class="form-group">
                <label for="seat_number">Seat Number:</label>
                <select id="seat_number" name="seat_number" required>
                    <option value="unassigned" <?php echo $rsvp['seat_number'] === null ? 'selected' : ''; ?>>Unassigned</option>
                    <?php for ($i = 1; $i <= 20; $i++): ?>
                        <option value="<?php echo $i; ?>" <?php echo $rsvp['seat_number'] == $i ? 'selected' : ''; ?>>
                            Seat <?php echo $i; ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>

            <button type="submit" class="btn-primary">Update Seat</button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

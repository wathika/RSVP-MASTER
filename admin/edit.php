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

require_once __DIR__ . '/../includes/header.php';
?>

<a class="back-link" href="<?php echo app_path('admin/index.php'); ?>">← Back to Dashboard</a>

<h1 class="page-title">Edit RSVP</h1>

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

<form method="POST" action="">
    <div class="form-group">
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($rsvp['name']); ?>" required>
    </div>

    <div class="form-group">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($rsvp['email']); ?>" required>
    </div>

    <div class="form-group">
        <label for="phone">Phone:</label>
        <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($rsvp['phone']); ?>" required>
    </div>

    <div class="form-group">
        <label for="dietary_preference">Dietary Preference:</label>
        <select id="dietary_preference" name="dietary_preference" required>
            <option value="">-- Select --</option>
            <option value="None" <?php echo $rsvp['dietary_preference'] === 'None' ? 'selected' : ''; ?>>None</option>
            <option value="Vegetarian" <?php echo $rsvp['dietary_preference'] === 'Vegetarian' ? 'selected' : ''; ?>>Vegetarian</option>
            <option value="Vegan" <?php echo $rsvp['dietary_preference'] === 'Vegan' ? 'selected' : ''; ?>>Vegan</option>
            <option value="Halal" <?php echo $rsvp['dietary_preference'] === 'Halal' ? 'selected' : ''; ?>>Halal</option>
            <option value="Gluten-Free" <?php echo $rsvp['dietary_preference'] === 'Gluten-Free' ? 'selected' : ''; ?>>Gluten-Free</option>
            <option value="Other" <?php echo $rsvp['dietary_preference'] === 'Other' ? 'selected' : ''; ?>>Other</option>
        </select>
    </div>

    <div class="form-group">
        <p><strong>Seat Number:</strong> <?php echo $rsvp['seat_number'] !== null ? htmlspecialchars($rsvp['seat_number']) : 'Not assigned'; ?></p>
    </div>

    <button type="submit" class="btn-primary">Update RSVP</button>
</form>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

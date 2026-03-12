<?php

require_once __DIR__ . '/../config/db.php';

$page_title = 'Submit RSVP';
$is_public_page = true;
require_once __DIR__ . '/../includes/header.php';

$success = '';
$error = '';
$name = '';
$email = '';
$phone = '';
$dietary_preference = '';

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

            // Insert guest
            $sql_guest = 'INSERT INTO guests (name, email, phone) VALUES (:name, :email, :phone)';
            $stmt_guest = $pdo->prepare($sql_guest);
            $stmt_guest->execute([
                'name' => $name,
                'email' => $email,
                'phone' => $phone
            ]);

            $guest_id = $pdo->lastInsertId();

            // Insert RSVP with NULL seat_number
            $sql_rsvp = 'INSERT INTO rsvps (guest_id, dietary_preference, seat_number) VALUES (:guest_id, :dietary_preference, NULL)';
            $stmt_rsvp = $pdo->prepare($sql_rsvp);
            $stmt_rsvp->execute([
                'guest_id' => $guest_id,
                'dietary_preference' => $dietary_preference
            ]);

            $pdo->commit();

            $success = 'RSVP submitted successfully! Thank you.';
            
            // Clear form fields on success
            $name = '';
            $email = '';
            $phone = '';
            $dietary_preference = '';

        } catch (PDOException $e) {
            $pdo->rollBack();

            // Check for duplicate email (unique constraint violation)
            if ($e->getCode() == 23000) {
                $error = 'This email has already been used to submit an RSVP.';
            } else {
                $error = 'An error occurred. Please try again.';
            }
        }
    }
}

?>
<h1 class="page-title">Event RSVP Form</h1>
<p class="page-subtitle">Please submit one RSVP per guest.</p>

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
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
    </div>

    <div class="form-group">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
    </div>

    <div class="form-group">
        <label for="phone">Phone:</label>
        <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>" required>
    </div>

    <div class="form-group">
        <label for="dietary_preference">Dietary Preference:</label>
        <select id="dietary_preference" name="dietary_preference" required>
            <option value="">-- Select --</option>
            <option value="None" <?php echo $dietary_preference === 'None' ? 'selected' : ''; ?>>None</option>
            <option value="Vegetarian" <?php echo $dietary_preference === 'Vegetarian' ? 'selected' : ''; ?>>Vegetarian</option>
            <option value="Vegan" <?php echo $dietary_preference === 'Vegan' ? 'selected' : ''; ?>>Vegan</option>
            <option value="Halal" <?php echo $dietary_preference === 'Halal' ? 'selected' : ''; ?>>Halal</option>
            <option value="Gluten-Free" <?php echo $dietary_preference === 'Gluten-Free' ? 'selected' : ''; ?>>Gluten-Free</option>
            <option value="Other" <?php echo $dietary_preference === 'Other' ? 'selected' : ''; ?>>Other</option>
        </select>
    </div>

    <button type="submit" class="btn-primary">Submit RSVP</button>
</form>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

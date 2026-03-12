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

$main_container_class = 'main-wrap-rsvp';

?>

<div class="hero">
    <span class="hero-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="4" width="18" height="16" rx="2" ry="2"></rect>
            <line x1="9" y1="9" x2="15" y2="9"></line>
            <line x1="9" y1="15" x2="15" y2="15"></line>
        </svg>
    </span>
    <h1>Event RSVP Form</h1>
    <p>Please submit one RSVP per guest.</p>
</div>

<?php if ($success !== ''): ?>
    <div class="success-banner show">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="20 6 9 17 4 12"></polyline>
        </svg>
        <div>
            <div class="s-title">RSVP submitted successfully!</div>
            <div class="s-sub">Thank you for your response. We look forward to seeing you.</div>
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

<div class="card">
    <div class="card-accent"></div>
    <div class="card-body">
        <form method="POST" action="">
            <div class="field">
                <label for="name">Name <span class="req">*</span></label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" placeholder="Full name" required>
            </div>

            <div class="field">
                <label for="email">Email <span class="req">*</span></label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" placeholder="your@email.com" required>
            </div>

            <div class="field">
                <label for="phone">Phone <span class="req">*</span></label>
                <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>" placeholder="+1 (555) 000-0000" required>
            </div>

            <div class="field">
                <label for="dietary_preference">Dietary Preference <span class="req">*</span></label>
                <select id="dietary_preference" name="dietary_preference" required>
                    <option value="">-- Select an option --</option>
                    <option value="None" <?php echo $dietary_preference === 'None' ? 'selected' : ''; ?>>None</option>
                    <option value="Vegetarian" <?php echo $dietary_preference === 'Vegetarian' ? 'selected' : ''; ?>>Vegetarian</option>
                    <option value="Vegan" <?php echo $dietary_preference === 'Vegan' ? 'selected' : ''; ?>>Vegan</option>
                    <option value="Halal" <?php echo $dietary_preference === 'Halal' ? 'selected' : ''; ?>>Halal</option>
                    <option value="Gluten-Free" <?php echo $dietary_preference === 'Gluten-Free' ? 'selected' : ''; ?>>Gluten-Free</option>
                    <option value="Other" <?php echo $dietary_preference === 'Other' ? 'selected' : ''; ?>>Other</option>
                </select>
            </div>

            <button type="submit" class="btn-submit">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 11.5L9 16.5l5 5M20 3a3 3 0 0 0-3-3H7a3 3 0 0 0-3 3v14a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3v-4m5 2l-3-3m0 0l-3 3"/>
                </svg>
                Submit RSVP
            </button>
        </form>

        <p class="foot-note">Having trouble? <a href="<?php echo app_path('admin/login.php'); ?>">Contact admin</a></p>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

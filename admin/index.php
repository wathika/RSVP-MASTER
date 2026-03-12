<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
require_admin_login();

$page_title = 'Dashboard';
$is_admin_page = true;
$main_container_class = 'container container-dashboard';
require_once __DIR__ . '/../includes/header.php';

// Fetch all RSVPs with guest information
$sql = 'SELECT 
            rsvps.id AS rsvp_id,
            guests.id AS guest_id,
            guests.name,
            guests.email,
            guests.phone,
            rsvps.dietary_preference,
            rsvps.seat_number,
            rsvps.created_at
        FROM rsvps
        INNER JOIN guests ON rsvps.guest_id = guests.id
        ORDER BY rsvps.created_at DESC';

$stmt = $pdo->query($sql);
$rsvps = $stmt->fetchAll();
?>

<h1 class="page-title">Admin Dashboard</h1>
<p class="page-subtitle">Welcome, <strong><?php echo htmlspecialchars($_SESSION['admin_username']); ?></strong>.</p>

<?php if (isset($_GET['deleted'])): ?>
    <div class="alert alert-success">
        <span>RSVP deleted successfully.</span>
        <button class="alert-close">×</button>
    </div>
<?php endif; ?>

<h2>All RSVPs (<?php echo count($rsvps); ?>)</h2>

<?php if (count($rsvps) > 0): ?>
    <div class="table-wrap table-card">
        <table class="dashboard-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Dietary Preference</th>
                    <th>Seat Number</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rsvps as $rsvp): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($rsvp['name']); ?></td>
                        <td><?php echo htmlspecialchars($rsvp['email']); ?></td>
                        <td><?php echo htmlspecialchars($rsvp['phone']); ?></td>
                        <td><?php echo htmlspecialchars($rsvp['dietary_preference']); ?></td>
                        <td><?php echo $rsvp['seat_number'] !== null ? htmlspecialchars($rsvp['seat_number']) : 'Not assigned'; ?></td>
                        <td>
                            <div class="action-links">
                                <a href="<?php echo app_path('admin/assign_seat.php'); ?>?id=<?php echo $rsvp['rsvp_id']; ?>">Assign Seat</a>
                                <a href="<?php echo app_path('admin/edit.php'); ?>?id=<?php echo $rsvp['rsvp_id']; ?>">Edit</a>
                                <a href="<?php echo app_path('admin/delete.php'); ?>?id=<?php echo $rsvp['rsvp_id']; ?>" data-confirm="Delete this RSVP?">Delete</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <p class="text-muted">No RSVPs found.</p>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
require_admin_login();

$username = $_SESSION['admin_username'] ?? 'Admin';

$page_title = 'Dashboard';
$is_admin_page = true;
// $main_container_class = 'main-wrap-dashboard';
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

// Helper function to compute initials from name
function get_initials($name) {
    $parts = array_filter(explode(' ', trim($name)));
    return strtoupper(implode('', array_map(fn($w) => $w[0] ?? '', $parts)));
}

// Compute stats
$total_rsvps = count($rsvps);
$seated_count = count(array_filter($rsvps, fn($r) => $r['seat_number'] !== null));
$unassigned_count = $total_rsvps - $seated_count;
$dietary_count = count(array_filter($rsvps, fn($r) => $r['dietary_preference'] !== 'None'));

// Helper function to map dietary preference to badge class
function get_dietary_badge_class($pref) {
    $pref_lower = strtolower(str_replace('-', '', $pref));
    if ($pref_lower === 'none') return 'badge-none';
    if ($pref_lower === 'halal') return 'badge-halal';
    if ($pref_lower === 'vegetarian') return 'badge-vegetarian';
    if ($pref_lower === 'vegan') return 'badge-vegan';
    if ($pref_lower === 'glutenfree') return 'badge-glutenfree';
    return 'badge-none';
}
?>

<div class="main-wrap-dashboard">
    <div class="page-header">
        <h1 class="page-title">Dashboard</h1>
        <p class="page-sub">Welcome, <strong><?php echo htmlspecialchars($username); ?></strong></p>
    </div>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="success-banner show">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
            <div>
                <div class="s-title">RSVP deleted successfully</div>
                <div class="s-sub">The guest record has been removed from the system.</div>
            </div>
            <button class="s-close">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
    <?php endif; ?>

    <!-- Stats Grid -->
    <div class="stats">
        <div class="stat-card">
            <div class="stat-icon violet">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="10" cy="7" r="4"></circle>
                    <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
            </div>
            <div>
                <div class="stat-value"><?php echo $total_rsvps; ?></div>
                <div class="stat-label">Total RSVPs</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon emerald">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"></path>
                    <polyline points="10 16.5 7.5 14 6 15.5"></polyline>
                    <polyline points="10 16.5 16.5 10"></polyline>
                </svg>
            </div>
            <div>
                <div class="stat-value"><?php echo $seated_count; ?></div>
                <div class="stat-label">Seated</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon amber">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path>
                    <polyline points="13 2 13 9 20 9"></polyline>
                    <line x1="9" y1="13" x2="15" y2="13"></line>
                    <line x1="9" y1="17" x2="15" y2="17"></line>
                </svg>
            </div>
            <div>
                <div class="stat-value"><?php echo $unassigned_count; ?></div>
                <div class="stat-label">Unassigned</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon rose">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path>
                </svg>
            </div>
            <div>
                <div class="stat-value"><?php echo $dietary_count; ?></div>
                <div class="stat-label">Dietary Requests</div>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="table-card">
        <div class="toolbar">
            <div class="toolbar-left">
                <div class="tbl-title">All RSVPs</div>
                <div class="tbl-count"><?php echo $total_rsvps; ?> guests</div>
            </div>
            <div class="search-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"></circle>
                    <path d="m21 21-4.35-4.35"></path>
                </svg>
                <input type="text" id="searchInput" placeholder="Search by name or email...">
            </div>
        </div>

        <?php if (count($rsvps) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Guest</th>
                        <th>Phone</th>
                        <th>Dietary</th>
                        <th>Seat</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="guestTable">
                    <?php foreach ($rsvps as $rsvp): ?>
                        <tr data-name="<?php echo strtolower($rsvp['name']); ?>" data-email="<?php echo strtolower($rsvp['email']); ?>">
                            <td>
                                <div class="guest-cell">
                                    <span class="avatar"><?php echo get_initials($rsvp['name']); ?></span>
                                    <div>
                                        <div class="guest-name"><?php echo htmlspecialchars($rsvp['name']); ?></div>
                                        <div class="guest-email"><?php echo htmlspecialchars($rsvp['email']); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($rsvp['phone']); ?></td>
                            <td>
                                <span class="badge <?php echo get_dietary_badge_class($rsvp['dietary_preference']); ?>">
                                    <?php echo htmlspecialchars($rsvp['dietary_preference']); ?>
                                </span>
                            </td>
                            <td>
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
                            </td>
                            <td>
                                <div class="actions">
                                    <a href="<?php echo app_path('admin/assign_seat.php'); ?>?id=<?php echo $rsvp['rsvp_id']; ?>" class="btn-assign">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M12 5v14M5 12h14"></path>
                                        </svg>
                                        Assign
                                    </a>
                                    <div class="dropdown">
                                        <button type="button" class="btn-more" aria-label="More actions">
                                            <span class="btn-more-glyph" aria-hidden="true">&#8942;</span>
                                        </button>
                                        <div class="dropdown-menu">
                                            <a href="<?php echo app_path('admin/edit.php'); ?>?id=<?php echo $rsvp['rsvp_id']; ?>" class="dropdown-item">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                                </svg>
                                                Edit
                                            </a>
                                            <a href="<?php echo app_path('admin/delete.php'); ?>?id=<?php echo $rsvp['rsvp_id']; ?>" class="dropdown-item danger" data-confirm="Delete this RSVP permanently?">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <polyline points="3 6 5 6 21 6"></polyline>
                                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                    <line x1="10" y1="11" x2="10" y2="17"></line>
                                                    <line x1="14" y1="11" x2="14" y2="17"></line>
                                                </svg>
                                                Delete
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-row">
                <tr>
                    <td colspan="5">No RSVPs found. Start by submitting the first RSVP.</td>
                </tr>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    // Search filter functionality
    const searchInput = document.getElementById('searchInput');
    const guestTable = document.getElementById('guestTable');
    
    if (searchInput && guestTable) {
        searchInput.addEventListener('input', function() {
            const term = this.value.toLowerCase();
            const rows = guestTable.querySelectorAll('tr');
            
            rows.forEach(row => {
                const name = row.getAttribute('data-name') || '';
                const email = row.getAttribute('data-email') || '';
                const match = name.includes(term) || email.includes(term);
                row.style.display = match ? '' : 'none';
            });
        });
    }

    // Dropdown menu toggle
    document.querySelectorAll('.btn-more').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const dropdown = this.closest('.dropdown');
            const menu = dropdown.querySelector('.dropdown-menu');
            
            // Close other dropdowns
            document.querySelectorAll('.dropdown-menu.open').forEach(m => {
                if (m !== menu) m.classList.remove('open');
            });
            
            // Toggle this menu
            menu.classList.toggle('open');
        });
    });

    // Close dropdowns on outside click
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.dropdown')) {
            document.querySelectorAll('.dropdown-menu.open').forEach(menu => {
                menu.classList.remove('open');
            });
        }
    });
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

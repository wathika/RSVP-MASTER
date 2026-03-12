<?php
/**
 * Header Include
 * Include at the top of every page after session/auth checks
 */

require_once __DIR__ . '/../config/app.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' - Event RSVP' : 'Event RSVP'; ?></title>
    <link rel="stylesheet" href="<?php echo app_path('public/style.css'); ?>">
</head>
<body>
    <header>
        <div class="nav-inner">
            <a class="brand" href="<?php echo app_path('public/rsvp.php'); ?>">
                <span class="brand-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="16" rx="2"/>
                        <line x1="9" y1="9" x2="15" y2="9"/>
                        <line x1="9" y1="15" x2="15" y2="15"/>
                    </svg>
                </span>
                <span class="brand-name">Event RSVP</span>
            </a>
            
            <?php if (!isset($show_nav) || $show_nav): ?>
                <nav class="nav-right">
                    <?php if (isset($is_admin_page) && $is_admin_page): ?>
                        <div class="online-dot">
                            <span class="dot"></span>
                            <strong><?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Admin'); ?></strong>
                        </div>
                        <a href="<?php echo app_path('admin/logout.php'); ?>" class="btn-ghost">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                                <polyline points="16 17 21 12 16 7"/>
                                <line x1="21" y1="12" x2="9" y2="12"/>
                            </svg>
                            Logout
                        </a>
                    <?php elseif (isset($is_public_page) && $is_public_page): ?>
                        <a href="<?php echo app_path('public/rsvp.php'); ?>" class="btn-ghost">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                                <polyline points="2 12 12 17 22 12"/>
                                <polyline points="2 17 12 22 22 17"/>
                            </svg>
                            Submit RSVP
                        </a>
                        <a href="<?php echo app_path('admin/login.php'); ?>" class="btn-ghost">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="1"/>
                                <path d="M12 1v6m0 6v4"/>
                                <path d="M4.22 4.22l4.24 4.24m2.12 5.08l4.24 4.24"/>
                                <path d="M1 12h6m6 0h4"/>
                                <path d="M4.22 19.78l4.24-4.24m5.08-2.12l4.24-4.24"/>
                                <path d="M12 17v6m0-16l-8.84 8.84"/>
                                <path d="M20.84 3.16L12 11.84"/>
                            </svg>
                            Admin
                        </a>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>
        </div>
    </header>

    <main<?php echo isset($main_class) ? ' class="' . htmlspecialchars($main_class) . '"' : ''; ?>>
        <div class="<?php echo isset($main_container_class) ? htmlspecialchars($main_container_class) : 'container'; ?>">

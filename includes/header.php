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
        <div class="container container-wide">
            <a class="brand" href="<?php echo app_path('public/rsvp.php'); ?>"><span class="brand-text">RSVP-MASTER</span></a>
            <?php if (!isset($show_nav) || $show_nav): ?>
                <nav>
                    <?php if (isset($is_admin_page) && $is_admin_page): ?>
                        <a href="<?php echo app_path('admin/index.php'); ?>">Dashboard</a>
                        <a href="<?php echo app_path('admin/logout.php'); ?>">Logout</a>
                    <?php elseif (isset($is_public_page) && $is_public_page): ?>
                        <a href="<?php echo app_path('public/rsvp.php'); ?>">Submit RSVP</a>
                        <a href="<?php echo app_path('admin/login.php'); ?>">Admin</a>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>
        </div>
    </header>

    <main>
        <div class="<?php echo isset($main_container_class) ? htmlspecialchars($main_container_class) : 'container'; ?>">

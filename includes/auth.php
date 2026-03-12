<?php

require_once __DIR__ . '/../config/app.php';

if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

function is_admin_logged_in(): bool
{
	return isset($_SESSION['admin_id']) && isset($_SESSION['admin_username']);
}

function require_admin_login(): void
{
	if (!is_admin_logged_in()) {
		app_redirect('admin/login.php');
	}
}

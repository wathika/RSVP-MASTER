<?php

// Set this once based on deployment location.
// Example:
// - If project is at http://localhost/RSVP-MASTER -> '/RSVP-MASTER'
// - If project is at domain root (/) -> ''
define('APP_BASE_PATH', '/RSVP-MASTER');

function app_path(string $path = ''): string
{
    $base = rtrim(APP_BASE_PATH, '/');
    $normalizedPath = '/' . ltrim($path, '/');

    return $base . $normalizedPath;
}

function app_redirect(string $path): void
{
    header('Location: ' . app_path($path));
    exit;
}

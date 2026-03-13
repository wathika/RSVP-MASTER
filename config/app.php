<?php

// Auto-detect the base path by comparing the app root against the server document root.
// Works regardless of deployment location (subfolder or domain root).
$_app_root = str_replace('\\', '/', (string) realpath(__DIR__ . '/..'));
$_doc_root = str_replace('\\', '/', rtrim((string) ($_SERVER['DOCUMENT_ROOT'] ?? ''), '/\\'));
define('APP_BASE_PATH', $_doc_root !== '' ? str_replace($_doc_root, '', $_app_root) : '');

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

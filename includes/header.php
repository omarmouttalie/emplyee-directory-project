<?php

declare(strict_types=1);

$pageTitle = $pageTitle ?? 'Employee Directory';
$activePage = $activePage ?? 'directory';
$showNavigation = $showNavigation ?? true;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <div class="site-shell">
        <header class="site-header">
            <div class="container nav-bar">
                <a class="brand" href="<?= isAdminLoggedIn() ? 'index.php' : 'login.php' ?>">
                    <span class="brand-mark">ED</span>
                    <span>
                        <strong>Northstar Staff Hub</strong>
                        <small>Secure internal employee directory</small>
                    </span>
                </a>
                <?php if ($showNavigation && isAdminLoggedIn()): ?>
                    <div class="header-actions">
                        <nav class="nav-links">
                            <a href="index.php" class="<?= $activePage === 'directory' ? 'is-active' : '' ?>">Directory</a>
                            <a href="admin.php" class="<?= $activePage === 'admin' ? 'is-active' : '' ?>">Admin</a>
                        </nav>
                        <div class="session-chip">
                            <span><?= e(currentAdminUsername()) ?></span>
                            <a href="logout.php">Logout</a>
                        </div>
                    </div>
                <?php elseif (!$showNavigation): ?>
                    <span class="nav-lockup">Authorized staff access only</span>
                <?php endif; ?>
            </div>
        </header>
        <main>

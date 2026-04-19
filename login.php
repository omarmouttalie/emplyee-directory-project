<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

if (isAdminLoggedIn()) {
    redirect('index.php');
}

$error = null;
$status = (string) ($_GET['status'] ?? '');
$redirectTo = redirectTarget((string) ($_GET['redirect'] ?? 'index.php'));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = (string) ($_POST['username'] ?? '');
    $password = (string) ($_POST['password'] ?? '');
    $redirectTo = redirectTarget((string) ($_POST['redirect'] ?? 'index.php'));

    if (loginAdmin($username, $password)) {
        redirect($redirectTo);
    }

    $error = 'Incorrect username or password.';
}

$pageTitle = 'Admin Login';
$activePage = 'login';
$showNavigation = false;

require __DIR__ . '/includes/header.php';
?>

<section class="login-section">
    <div class="container login-layout">
        <div class="login-copy glass-panel">
            <span class="eyebrow">Admin Access</span>
            <h1>Enter the private directory.</h1>
            <p>
                This employee directory is now protected behind an admin session so staff records,
                contact details, and profile management are not available to the public.
            </p>
            <div class="security-list">
                <div class="security-item">
                    <strong>Directory locked</strong>
                    <span>Search, profiles, and CRUD screens require login.</span>
                </div>
                <div class="security-item">
                    <strong>Session based</strong>
                    <span>Admins can sign in and use the logout action in the header.</span>
                </div>
                <div class="security-item">
                    <strong>Glassmorphism UI</strong>
                    <span>Dark blue and black styling gives the app a more modern portfolio feel.</span>
                </div>
            </div>
        </div>

        <div class="login-card glass-panel">
            <div class="panel-heading">
                <span class="eyebrow">Sign In</span>
                <h2>Administrator login</h2>
                <p>Use your admin credentials to view and manage employee data.</p>
            </div>

            <?php if ($status === 'logged_out'): ?>
                <div class="alert alert-success">You have been logged out successfully.</div>
            <?php endif; ?>

            <?php if ($error !== null): ?>
                <div class="alert alert-error"><?= e($error) ?></div>
            <?php endif; ?>

            <form method="post" class="employee-form login-form">
                <input type="hidden" name="redirect" value="<?= e($redirectTo) ?>">

                <div class="field">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Enter admin username" required autofocus>
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter password" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Login</button>
            </form>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>

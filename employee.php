<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

requireAdminLogin();

$employeeId = (int) ($_GET['id'] ?? 0);
$employee = $employeeId > 0 ? fetchEmployeeById($employeeId) : null;

if ($employee === null) {
    http_response_code(404);
}

$pageTitle = $employee ? $employee['full_name'] . ' | Employee Profile' : 'Employee Not Found';
$activePage = 'directory';

require __DIR__ . '/includes/header.php';
?>

<section class="section">
    <div class="container">
        <?php if ($employee === null): ?>
            <div class="empty-state glass-panel">
                <h1>Employee not found</h1>
                <p>The profile you requested does not exist or may have been removed.</p>
                <a class="btn btn-primary" href="index.php">Back to directory</a>
            </div>
        <?php else: ?>
            <article class="profile-card glass-panel">
                <div class="profile-sidebar">
                    <img
                        src="<?= e(employeePhoto($employee['photo_url'], $employee['full_name'])) ?>"
                        alt="<?= e($employee['full_name']) ?>"
                        class="profile-photo"
                    >
                    <span class="pill"><?= e($employee['department_name']) ?></span>
                    <h1><?= e($employee['full_name']) ?></h1>
                    <p class="employee-role profile-role"><?= e($employee['position']) ?></p>
                </div>
                <div class="profile-content">
                    <div class="detail-grid">
                        <div class="detail-card">
                            <span>Email</span>
                            <a href="mailto:<?= e($employee['email']) ?>"><?= e($employee['email']) ?></a>
                        </div>
                        <div class="detail-card">
                            <span>Phone</span>
                            <a href="tel:<?= e($employee['phone']) ?>"><?= e($employee['phone']) ?></a>
                        </div>
                        <div class="detail-card">
                            <span>Department</span>
                            <strong><?= e($employee['department_name']) ?></strong>
                        </div>
                        <div class="detail-card">
                            <span>Hire Date</span>
                            <strong><?= e(date('F j, Y', strtotime((string) $employee['hire_date']))) ?></strong>
                        </div>
                    </div>
                    <div class="profile-actions">
                        <a class="btn btn-primary" href="index.php">Back to directory</a>
                        <a class="btn btn-secondary" href="admin.php?edit=<?= (int) $employee['id'] ?>#employee-form-panel">Edit profile</a>
                    </div>
                </div>
            </article>
        <?php endif; ?>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>

<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

requireAdminLogin();

$search = trim((string) ($_GET['search'] ?? ''));
$departmentId = isset($_GET['department']) && $_GET['department'] !== '' ? (int) $_GET['department'] : null;
$departments = fetchDepartments();
$employees = fetchEmployees($search, $departmentId);

$pageTitle = 'Employee Directory';
$activePage = 'directory';

require __DIR__ . '/includes/header.php';
?>

<section class="hero">
    <div class="container hero-grid">
        <div class="glass-panel hero-panel">
            <span class="eyebrow">Protected Staff Directory</span>
            <h1>Private employee records in a darker, sharper workspace.</h1>
            <p class="hero-copy">
                Search internal staff by name, department, or role and review private contact details
                only after signing in as an administrator.
            </p>
        </div>
        <div class="hero-card glass-panel">
            <div class="hero-stat">
                <strong><?= count($employees) ?></strong>
                <span>Secured staff profiles</span>
            </div>
            <div class="hero-stat">
                <strong><?= count($departments) ?></strong>
                <span>Tracked departments</span>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <form class="filter-panel glass-panel" method="get" data-filter-form>
            <div class="field">
                <label for="search">Search by name, role, or department</label>
                <input
                    type="search"
                    id="search"
                    name="search"
                    value="<?= e($search) ?>"
                    placeholder="Try 'Olivia', 'Operations', or 'Designer'"
                >
            </div>
            <div class="field">
                <label for="department">Department</label>
                <select id="department" name="department" data-auto-submit>
                    <option value="">All departments</option>
                    <?php foreach ($departments as $department): ?>
                        <option value="<?= (int) $department['id'] ?>" <?= $departmentId === (int) $department['id'] ? 'selected' : '' ?>>
                            <?= e($department['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field actions">
                <label class="desktop-only">Actions</label>
                <div class="button-row">
                    <button type="submit" class="btn btn-primary">Search</button>
                    <a class="btn btn-secondary" href="index.php">Reset</a>
                </div>
            </div>
        </form>

        <?php if ($employees === []): ?>
            <div class="empty-state glass-panel">
                <h2>No employees matched your search.</h2>
                <p>Try a different keyword or clear the department filter to see more staff members.</p>
            </div>
        <?php else: ?>
            <div class="card-grid">
                <?php foreach ($employees as $employee): ?>
                    <article class="employee-card glass-panel">
                        <img
                            src="<?= e(employeePhoto($employee['photo_url'], $employee['full_name'])) ?>"
                            alt="<?= e($employee['full_name']) ?>"
                            class="employee-card-photo"
                        >
                        <div class="employee-card-body">
                            <span class="pill"><?= e($employee['department_name']) ?></span>
                            <h2><?= e($employee['full_name']) ?></h2>
                            <p class="employee-role"><?= e($employee['position']) ?></p>
                            <ul class="meta-list">
                                <li><a href="mailto:<?= e($employee['email']) ?>"><?= e($employee['email']) ?></a></li>
                                <li><a href="tel:<?= e($employee['phone']) ?>"><?= e($employee['phone']) ?></a></li>
                            </ul>
                            <a class="text-link" href="employee.php?id=<?= (int) $employee['id'] ?>">View profile</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>

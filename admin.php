<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

requireAdminLogin();

$errors = [];
$statusMessage = null;
$removePhotoChecked = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'save') {
        $employeeId = (int) ($_POST['employee_id'] ?? 0);
        $formData = normalizeEmployeeData($_POST);
        $formData['photo_url'] = trim((string) ($_POST['current_photo'] ?? ''));
        $removePhotoChecked = isset($_POST['remove_photo']);
        $errors = validateEmployeeData($formData);

        if ($errors === []) {
            try {
                $formData['photo_url'] = uploadedEmployeePhotoPath(
                    $_FILES['photo_file'] ?? null,
                    $formData['full_name'],
                    $formData['photo_url'],
                    $removePhotoChecked
                );

                if ($employeeId > 0) {
                    updateEmployee($employeeId, $formData);
                    redirect('admin.php?status=updated');
                }

                createEmployee($formData);
                redirect('admin.php?status=created');
            } catch (RuntimeException | PDOException $exception) {
                $errors[] = $exception instanceof PDOException && $exception->getCode() === '23000'
                    ? 'That email address is already assigned to another employee.'
                    : $exception->getMessage();
            }
        }
    }

    if ($action === 'delete') {
        $employeeId = (int) ($_POST['employee_id'] ?? 0);

        if ($employeeId > 0) {
            try {
                deleteEmployee($employeeId);
                redirect('admin.php?status=deleted');
            } catch (PDOException $exception) {
                $errors[] = 'Unable to delete this employee right now. Please try again.';
            }
        }
    }
}

$status = (string) ($_GET['status'] ?? '');
$editId = (int) ($_GET['edit'] ?? 0);
$departments = fetchDepartments();
$employees = fetchEmployees();
$employeeToEdit = $editId > 0 ? fetchEmployeeById($editId) : null;

$defaultForm = [
    'full_name' => '',
    'email' => '',
    'phone' => '',
    'position' => '',
    'department_id' => '',
    'photo_url' => '',
    'hire_date' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $errors !== []) {
    $formValues = array_merge($defaultForm, normalizeEmployeeData($_POST));
    $formValues['photo_url'] = trim((string) ($_POST['current_photo'] ?? ''));
    $formEmployeeId = (int) ($_POST['employee_id'] ?? 0);
} elseif ($employeeToEdit !== null) {
    $formValues = [
        'full_name' => $employeeToEdit['full_name'],
        'email' => $employeeToEdit['email'],
        'phone' => $employeeToEdit['phone'],
        'position' => $employeeToEdit['position'],
        'department_id' => (string) $employeeToEdit['department_id'],
        'photo_url' => $employeeToEdit['photo_url'],
        'hire_date' => $employeeToEdit['hire_date'],
    ];
    $formEmployeeId = (int) $employeeToEdit['id'];
} else {
    $formValues = $defaultForm;
    $formEmployeeId = 0;
}

if ($status === 'created') {
    $statusMessage = 'Employee added successfully.';
} elseif ($status === 'updated') {
    $statusMessage = 'Employee record updated successfully.';
} elseif ($status === 'deleted') {
    $statusMessage = 'Employee record deleted successfully.';
}

$pageTitle = 'Admin | Employee Directory';
$activePage = 'admin';

require __DIR__ . '/includes/header.php';
?>

<section class="section admin-section">
    <div class="container admin-grid">
        <div class="panel glass-panel form-panel" id="employee-form-panel">
            <div class="panel-heading">
                <span class="eyebrow">Admin Workspace</span>
                <h1><?= $formEmployeeId > 0 ? 'Edit employee' : 'Add a new employee' ?></h1>
                <p>Manage secured employee records, profile details, and department assignments from one place.</p>
            </div>

            <?php if ($statusMessage !== null): ?>
                <div class="alert alert-success"><?= e($statusMessage) ?></div>
            <?php endif; ?>

            <?php if ($errors !== []): ?>
                <div class="alert alert-error">
                    <strong>Please fix the following:</strong>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= e($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="post" class="employee-form" data-admin-form enctype="multipart/form-data">
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="employee_id" value="<?= $formEmployeeId ?>">
                <input type="hidden" name="current_photo" value="<?= e((string) $formValues['photo_url']) ?>">

                <div class="form-grid">
                    <div class="field">
                        <label for="full_name">Full name</label>
                        <input type="text" id="full_name" name="full_name" value="<?= e((string) $formValues['full_name']) ?>" required>
                    </div>
                    <div class="field">
                        <label for="position">Position</label>
                        <input type="text" id="position" name="position" value="<?= e((string) $formValues['position']) ?>" required>
                    </div>
                    <div class="field">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?= e((string) $formValues['email']) ?>" required>
                    </div>
                    <div class="field">
                        <label for="phone">Phone</label>
                        <input type="text" id="phone" name="phone" value="<?= e((string) $formValues['phone']) ?>" required>
                    </div>
                    <div class="field">
                        <label for="department_id">Department</label>
                        <select id="department_id" name="department_id" required>
                            <option value="">Select a department</option>
                            <?php foreach ($departments as $department): ?>
                                <option
                                    value="<?= (int) $department['id'] ?>"
                                    <?= (string) $formValues['department_id'] === (string) $department['id'] ? 'selected' : '' ?>
                                >
                                    <?= e($department['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field">
                        <label for="hire_date">Hire date</label>
                        <input type="date" id="hire_date" name="hire_date" value="<?= e((string) $formValues['hire_date']) ?>" required>
                    </div>
                    <div class="field field-full">
                        <label for="photo_file">Profile photo</label>
                        <div class="file-upload-shell">
                            <input
                                type="file"
                                id="photo_file"
                                name="photo_file"
                                accept=".jpg,.jpeg,.png,.gif,.webp,image/jpeg,image/png,image/gif,image/webp"
                                data-photo-file
                            >
                            <p class="file-hint">Upload a JPG, PNG, GIF, or WebP image up to 5 MB. Files are stored locally in this project.</p>
                        </div>
                    </div>
                    <?php if ((string) $formValues['photo_url'] !== ''): ?>
                        <div class="field field-full">
                            <label class="checkbox-row">
                                <input type="checkbox" name="remove_photo" value="1" <?= $removePhotoChecked ? 'checked' : '' ?> data-remove-photo>
                                <span>Remove the current photo and use the default avatar instead</span>
                            </label>
                        </div>
                    <?php endif; ?>
                    <div class="field field-full">
                        <div class="helper-strip">
                            <span>Upload preview updates instantly before saving.</span>
                            <span><?= $formEmployeeId > 0 ? 'Replacing a photo will automatically remove the old uploaded file.' : 'Add a polished headshot for a more complete employee card.' ?></span>
                        </div>
                    </div>
                </div>

                <div class="photo-preview" data-photo-preview>
                    <?php
                    $savedPhotoSource = (string) $formValues['photo_url'] !== '' ? employeePhoto((string) $formValues['photo_url'], (string) ($formValues['full_name'] ?: 'Employee')) : '';
                    $defaultAvatarSource = employeePhoto('', (string) ($formValues['full_name'] ?: 'Employee'));
                    $previewSource = $removePhotoChecked ? $defaultAvatarSource : ($savedPhotoSource !== '' ? $savedPhotoSource : $defaultAvatarSource);
                    ?>
                    <img
                        src="<?= e($previewSource) ?>"
                        alt="Employee preview"
                        data-default-avatar="<?= e($defaultAvatarSource) ?>"
                        data-saved-photo="<?= e($savedPhotoSource) ?>"
                    >
                    <div>
                        <strong>Live profile preview</strong>
                        <p data-photo-caption><?= (string) $formValues['photo_url'] !== '' && !$removePhotoChecked ? 'Current local or saved image shown below. Upload a new file to replace it.' : 'No uploaded image yet. A generated avatar will be used until you add one.' ?></p>
                    </div>
                </div>

                <div class="button-row">
                    <button type="submit" class="btn btn-primary"><?= $formEmployeeId > 0 ? 'Update employee' : 'Add employee' ?></button>
                    <a class="btn btn-secondary" href="admin.php">Clear form</a>
                </div>
            </form>
        </div>

        <div class="panel glass-panel list-panel">
            <div class="panel-heading">
                <span class="eyebrow">Current Team</span>
                <h2>Employee records</h2>
            </div>

            <div class="table-wrapper">
                <table class="employee-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Department</th>
                            <th>Position</th>
                            <th>Email</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($employees as $employee): ?>
                            <tr>
                                <td>
                                    <strong><?= e($employee['full_name']) ?></strong>
                                    <span><?= e($employee['phone']) ?></span>
                                </td>
                                <td><?= e($employee['department_name']) ?></td>
                                <td><?= e($employee['position']) ?></td>
                                <td><a href="mailto:<?= e($employee['email']) ?>"><?= e($employee['email']) ?></a></td>
                                <td>
                                    <div class="table-actions">
                                        <a class="text-link" href="admin.php?edit=<?= (int) $employee['id'] ?>#employee-form-panel">Edit</a>
                                        <a class="text-link" href="employee.php?id=<?= (int) $employee['id'] ?>">View</a>
                                        <form method="post" data-delete-form>
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="employee_id" value="<?= (int) $employee['id'] ?>">
                                            <button type="submit" class="text-button" data-employee-name="<?= e($employee['full_name']) ?>">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>

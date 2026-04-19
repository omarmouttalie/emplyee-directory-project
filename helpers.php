<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function isAdminLoggedIn(): bool
{
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function currentAdminUsername(): ?string
{
    return isAdminLoggedIn() ? (string) ($_SESSION['admin_username'] ?? ADMIN_USERNAME) : null;
}

function loginAdmin(string $username, string $password): bool
{
    $isValid = hash_equals(ADMIN_USERNAME, trim($username)) && hash_equals(ADMIN_PASSWORD, $password);

    if (!$isValid) {
        return false;
    }

    session_regenerate_id(true);
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['admin_username'] = ADMIN_USERNAME;

    return true;
}

function logoutAdmin(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }

    session_destroy();
}

function redirectTarget(?string $target): string
{
    $target = trim((string) $target);

    if ($target === '' || preg_match('/^https?:\/\//i', $target) === 1) {
        return 'index.php';
    }

    return $target;
}

function requireAdminLogin(): void
{
    if (isAdminLoggedIn()) {
        return;
    }

    $currentUri = (string) ($_SERVER['REQUEST_URI'] ?? 'index.php');
    redirect('login.php?redirect=' . rawurlencode($currentUri));
}

function normalizeEmployeeData(array $input): array
{
    return [
        'full_name' => trim((string) ($input['full_name'] ?? '')),
        'email' => trim((string) ($input['email'] ?? '')),
        'phone' => trim((string) ($input['phone'] ?? '')),
        'position' => trim((string) ($input['position'] ?? '')),
        'department_id' => (int) ($input['department_id'] ?? 0),
        'photo_url' => trim((string) ($input['photo_url'] ?? '')),
        'hire_date' => trim((string) ($input['hire_date'] ?? '')),
    ];
}

function validateEmployeeData(array $data): array
{
    $errors = [];

    if ($data['full_name'] === '') {
        $errors[] = 'Full name is required.';
    }

    if ($data['email'] === '' || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'A valid email address is required.';
    }

    if ($data['phone'] === '') {
        $errors[] = 'Phone number is required.';
    }

    if ($data['position'] === '') {
        $errors[] = 'Position is required.';
    }

    if ($data['department_id'] <= 0) {
        $errors[] = 'Please choose a department.';
    }

    if ($data['hire_date'] === '') {
        $errors[] = 'Hire date is required.';
    }

    return $errors;
}

function employeePhotoUploadDirectory(): string
{
    return __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'employees';
}

function employeePhotoWebPath(string $filename): string
{
    return 'uploads/employees/' . $filename;
}

function ensureEmployeePhotoUploadDirectory(): void
{
    $directory = employeePhotoUploadDirectory();

    if (is_dir($directory)) {
        return;
    }

    if (!mkdir($directory, 0775, true) && !is_dir($directory)) {
        throw new RuntimeException('Unable to create the employee upload folder.');
    }
}

function isLocalEmployeePhoto(?string $photoPath): bool
{
    return str_starts_with(str_replace('\\', '/', trim((string) $photoPath)), 'uploads/employees/');
}

function deleteLocalEmployeePhoto(?string $photoPath): void
{
    $photoPath = trim((string) $photoPath);

    if ($photoPath === '' || !isLocalEmployeePhoto($photoPath)) {
        return;
    }

    $absolutePath = __DIR__ . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $photoPath);

    if (is_file($absolutePath)) {
        unlink($absolutePath);
    }
}

function slugify(string $value): string
{
    $slug = strtolower(trim($value));
    $slug = preg_replace('/[^a-z0-9]+/i', '-', $slug);
    $slug = trim((string) $slug, '-');

    return $slug !== '' ? $slug : 'employee';
}

function employeeInitials(string $name): string
{
    $parts = preg_split('/\s+/', trim($name)) ?: [];
    $initials = '';

    foreach ($parts as $part) {
        if ($part === '') {
            continue;
        }

        $initials .= strtoupper(substr($part, 0, 1));

        if (strlen($initials) === 2) {
            break;
        }
    }

    return $initials !== '' ? $initials : 'ED';
}

function defaultEmployeeAvatar(string $name): string
{
    $initials = htmlspecialchars(employeeInitials($name), ENT_QUOTES | ENT_XML1, 'UTF-8');
    $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 240 240" role="img" aria-label="Employee avatar">
  <defs>
    <linearGradient id="bg" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#1d4ed8" />
      <stop offset="100%" stop-color="#020617" />
    </linearGradient>
  </defs>
  <rect width="240" height="240" rx="36" fill="url(#bg)" />
  <circle cx="198" cy="48" r="34" fill="rgba(125,211,252,0.18)" />
  <circle cx="52" cy="194" r="52" fill="rgba(59,130,246,0.22)" />
  <text x="50%" y="54%" dominant-baseline="middle" text-anchor="middle" fill="#e0f2fe" font-family="Arial, sans-serif" font-size="88" font-weight="700">$initials</text>
</svg>
SVG;

    return 'data:image/svg+xml;charset=UTF-8,' . rawurlencode($svg);
}

function uploadedEmployeePhotoPath(?array $file, string $employeeName, ?string $existingPhoto = null, bool $removeExisting = false): string
{
    $existingPhoto = trim((string) $existingPhoto);

    if ($file === null || !isset($file['error'])) {
        if ($removeExisting) {
            deleteLocalEmployeePhoto($existingPhoto);

            return '';
        }

        return $existingPhoto;
    }

    if ((int) $file['error'] === UPLOAD_ERR_NO_FILE) {
        if ($removeExisting) {
            deleteLocalEmployeePhoto($existingPhoto);

            return '';
        }

        return $existingPhoto;
    }

    if ((int) $file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Photo upload failed. Please try again with a valid image.');
    }

    if ((int) ($file['size'] ?? 0) > 5 * 1024 * 1024) {
        throw new RuntimeException('Photo must be 5 MB or smaller.');
    }

    $tmpName = (string) ($file['tmp_name'] ?? '');
    $imageInfo = @getimagesize($tmpName);

    if ($imageInfo === false) {
        throw new RuntimeException('Please upload a valid JPG, PNG, GIF, or WebP image.');
    }

    $extensionMap = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
    ];

    $mimeType = (string) ($imageInfo['mime'] ?? '');

    if (!isset($extensionMap[$mimeType])) {
        throw new RuntimeException('Only JPG, PNG, GIF, and WebP images are allowed.');
    }

    ensureEmployeePhotoUploadDirectory();

    $filename = sprintf(
        '%s-%s.%s',
        slugify($employeeName),
        bin2hex(random_bytes(6)),
        $extensionMap[$mimeType]
    );
    $relativePath = employeePhotoWebPath($filename);
    $destination = employeePhotoUploadDirectory() . DIRECTORY_SEPARATOR . $filename;

    if (!move_uploaded_file($tmpName, $destination)) {
        throw new RuntimeException('Unable to save the uploaded photo.');
    }

    if ($existingPhoto !== '' && $existingPhoto !== $relativePath) {
        deleteLocalEmployeePhoto($existingPhoto);
    }

    return $relativePath;
}

function fetchDepartments(): array
{
    $statement = db()->query('SELECT id, name FROM departments ORDER BY name');

    return $statement->fetchAll();
}

function fetchDepartmentMap(): array
{
    $departments = fetchDepartments();
    $map = [];

    foreach ($departments as $department) {
        $map[(int) $department['id']] = $department['name'];
    }

    return $map;
}

function fetchEmployees(string $search = '', ?int $departmentId = null): array
{
    $sql = <<<SQL
        SELECT
            employees.id,
            employees.full_name,
            employees.email,
            employees.phone,
            employees.position,
            employees.photo_url,
            employees.hire_date,
            employees.department_id,
            departments.name AS department_name
        FROM employees
        INNER JOIN departments ON departments.id = employees.department_id
        WHERE 1 = 1
    SQL;

    $params = [];

    if ($search !== '') {
        $sql .= ' AND (employees.full_name LIKE :search OR employees.position LIKE :search OR departments.name LIKE :search)';
        $params['search'] = '%' . $search . '%';
    }

    if (!empty($departmentId)) {
        $sql .= ' AND employees.department_id = :department_id';
        $params['department_id'] = $departmentId;
    }

    $sql .= ' ORDER BY employees.full_name';

    $statement = db()->prepare($sql);
    $statement->execute($params);

    return $statement->fetchAll();
}

function fetchEmployeeById(int $employeeId): ?array
{
    $sql = <<<SQL
        SELECT
            employees.id,
            employees.full_name,
            employees.email,
            employees.phone,
            employees.position,
            employees.photo_url,
            employees.hire_date,
            employees.department_id,
            departments.name AS department_name
        FROM employees
        INNER JOIN departments ON departments.id = employees.department_id
        WHERE employees.id = :id
        LIMIT 1
    SQL;

    $statement = db()->prepare($sql);
    $statement->execute(['id' => $employeeId]);
    $employee = $statement->fetch();

    return $employee ?: null;
}

function createEmployee(array $data): void
{
    $sql = <<<SQL
        INSERT INTO employees (full_name, email, phone, position, department_id, photo_url, hire_date)
        VALUES (:full_name, :email, :phone, :position, :department_id, :photo_url, :hire_date)
    SQL;

    $statement = db()->prepare($sql);
    $statement->execute($data);
}

function updateEmployee(int $employeeId, array $data): void
{
    $data['id'] = $employeeId;

    $sql = <<<SQL
        UPDATE employees
        SET
            full_name = :full_name,
            email = :email,
            phone = :phone,
            position = :position,
            department_id = :department_id,
            photo_url = :photo_url,
            hire_date = :hire_date
        WHERE id = :id
    SQL;

    $statement = db()->prepare($sql);
    $statement->execute($data);
}

function deleteEmployee(int $employeeId): void
{
    $employee = fetchEmployeeById($employeeId);
    $statement = db()->prepare('DELETE FROM employees WHERE id = :id');
    $statement->execute(['id' => $employeeId]);

    if ($employee !== null) {
        deleteLocalEmployeePhoto($employee['photo_url'] ?? null);
    }
}

function redirect(string $location): never
{
    header('Location: ' . $location);
    exit;
}

function employeePhoto(?string $photoUrl, string $name): string
{
    if ($photoUrl !== null && trim($photoUrl) !== '') {
        return $photoUrl;
    }

    return defaultEmployeeAvatar($name);
}

USE employee_directory;

-- List employees with their department names
SELECT
    employees.id,
    employees.full_name,
    employees.email,
    employees.phone,
    employees.position,
    departments.name AS department_name,
    employees.hire_date
FROM employees
INNER JOIN departments ON departments.id = employees.department_id
ORDER BY employees.full_name;

-- Search employees by name or department
SELECT
    employees.full_name,
    employees.position,
    departments.name AS department_name
FROM employees
INNER JOIN departments ON departments.id = employees.department_id
WHERE employees.full_name LIKE '%Olivia%'
   OR departments.name LIKE '%Operations%';

-- Insert a new employee
INSERT INTO employees (full_name, email, phone, position, department_id, photo_url, hire_date)
VALUES ('Jordan Lee', 'jordan.lee@northstar.local', '(555) 120-1010', 'Support Lead', 4, '', '2024-10-01');

-- Update an employee record
UPDATE employees
SET position = 'Senior Product Designer'
WHERE id = 2;

-- Delete an employee record
DELETE FROM employees
WHERE id = 7;

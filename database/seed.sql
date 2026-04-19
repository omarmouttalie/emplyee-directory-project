USE employee_directory;

INSERT INTO departments (name) VALUES
    ('Finance'),
    ('Human Resources'),
    ('Marketing'),
    ('Operations'),
    ('Product'),
    ('Sales')
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO employees (full_name, email, phone, position, department_id, photo_url, hire_date) VALUES
    ('Olivia Bennett', 'olivia.bennett@northstar.local', '(555) 120-1001', 'Operations Manager', 4, 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=600&q=80', '2021-03-15'),
    ('Marcus Reed', 'marcus.reed@northstar.local', '(555) 120-1002', 'Product Designer', 5, 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=600&q=80', '2022-07-11'),
    ('Priya Shah', 'priya.shah@northstar.local', '(555) 120-1003', 'HR Specialist', 2, 'https://images.unsplash.com/photo-1488426862026-3ee34a7d66df?auto=format&fit=crop&w=600&q=80', '2020-09-21'),
    ('Daniel Kim', 'daniel.kim@northstar.local', '(555) 120-1004', 'Account Executive', 6, 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?auto=format&fit=crop&w=600&q=80', '2023-01-09'),
    ('Ava Martinez', 'ava.martinez@northstar.local', '(555) 120-1005', 'Financial Analyst', 1, 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?auto=format&fit=crop&w=600&q=80', '2019-05-28'),
    ('Noah Carter', 'noah.carter@northstar.local', '(555) 120-1006', 'Marketing Coordinator', 3, 'https://images.unsplash.com/photo-1504593811423-6dd665756598?auto=format&fit=crop&w=600&q=80', '2024-02-12')
ON DUPLICATE KEY UPDATE
    full_name = VALUES(full_name),
    phone = VALUES(phone),
    position = VALUES(position),
    department_id = VALUES(department_id),
    photo_url = VALUES(photo_url),
    hire_date = VALUES(hire_date);

CREATE DATABASE IF NOT EXISTS employee_directory;
USE employee_directory;

CREATE TABLE IF NOT EXISTS departments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS employees (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(30) NOT NULL,
    position VARCHAR(100) NOT NULL,
    department_id INT UNSIGNED NOT NULL,
    photo_url VARCHAR(255) NULL,
    hire_date DATE NOT NULL,
    CONSTRAINT fk_employees_department
        FOREIGN KEY (department_id) REFERENCES departments(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
);

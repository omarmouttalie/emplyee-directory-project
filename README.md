# Employee Directory

A small-business employee directory built with PHP, MySQL, HTML, CSS, and JavaScript.

## Features

- Session-based admin login protecting all employee data
- Private employee directory with search and department filtering
- Individual employee profile page
- Admin page to add, edit, and delete employee records
- Relational database structure linking employees to departments
- Ready-to-import SQL schema, seed data, and example queries

## Project Files

- `index.php` - protected employee directory
- `login.php` - admin sign-in screen
- `logout.php` - ends the admin session
- `employee.php` - profile page for a single employee
- `admin.php` - CRUD interface for employee records
- `config.php` and `helpers.php` - database connection and shared query logic
- `database/schema.sql` - database and table creation
- `database/seed.sql` - starter departments and employees
- `database/queries.sql` - example SQL operations

## Setup

1. Create the database and tables by importing `database/schema.sql`.
2. Import `database/seed.sql` for sample data.
3. Make sure MySQL is running in XAMPP.
4. Update `config.php` if your MySQL credentials differ from the default XAMPP setup.
5. Open `http://localhost/employee-directory_ChatGpt/login.php`.

## Optional Upgrade Ideas

- Move admin login to a database-backed users table with hashed passwords
- Export employee lists to CSV
- Upload employee photos instead of storing remote image URLs

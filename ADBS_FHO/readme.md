# Human Resources Management Information System (HRMIS)

A comprehensive PHP-based HRMIS designed to manage employee records, generate reports, and track activities, compliant with CSC Form 212 (Revised 2025) data requirements.

## Features

### Functional Features
1.  **Add Employee Records**: 
    *   Complete CSC Form 212 data entry with validation.
    *   **Sticky Forms**: Preserves entered data if submission fails, preventing re-entry.
    *   **Smart ID Generation**: Automatically handles ID generation for tables without auto-increment (e.g., Trainings, Skills).
2.  **Modify Records**: 
    *   Update employee details (Personal, Family, Education, Work, etc.).
    *   Role-based restrictions ensure users can only edit allowed fields.
3.  **Search & View**: 
    *   Advanced search by Name/ID.
    *   Detailed employee profile view.
4.  **Reports**:
    *   Demographic Summaries (Gender, Civil Status, Department).
    *   Recent Trainings List.
    *   Export to Excel functionality.
5.  **Delete Records**: Secure deletion with role-based access (Admin/HR only).

### Non-Functional Features
1.  **Reliability (ACID)**: Transactions ensure data integrity across multiple tables (`employees`, `service_records`, `assignments`, etc.).
2.  **Security**:
    *   **SQL Injection Protection**: All queries use Prepared Statements.
    *   **RBAC (Role-Based Access Control)**:
        *   **Admin**: Full access.
        *   **HR Staff**: Manage employees.
        *   **Employee**: Restricted access. Can only view their own profile. "Add Employee" features are disabled and hidden.
3.  **Auditability**:
    *   **Activity Logs**: Database triggers and application-level logging record all critical actions.

## Tech Stack
*   **Backend**: PHP (Native)
*   **Database**: MySQL
*   **Frontend**: HTML5, CSS3, Bootstrap 5
*   **Server**: Apache (XAMPP)

## Installation & Setup

1.  **Database Setup**:
    *   Open phpMyAdmin (usually `http://localhost/phpmyadmin`).
    *   Create a new database named `hrm_project_finals`.
    *   Import the file `hrm_project_finals_2.sql` located in the root folder.
    *   (Optional) Import `form_data_dump/create_triggers.sql` to enable database-level logging triggers.

2.  **Configuration**:
    *   Verify database credentials in `classes/conn.php`.
    *   Default settings:
        *   Host: `localhost`
        *   User: `root`
        *   Pass: `root123` (or empty `` depending on your XAMPP setup)
        *   DB: `hrm_project_finals`

3.  **Create Admin User**:
    *   The database comes with empty users. To create the initial Admin account:
    *   Open your browser and navigate to: `http://localhost/ADBS_FHO/setup_admin.php`
    *   This will create a user:
        *   **Username**: `admin`
        *   **Password**: `admin123`
    *   **IMPORTANT**: Delete `setup_admin.php` after creating the user to secure the system.

4.  **Login**:
    *   Go to `http://localhost/ADBS_FHO/index.php` and log in with the admin credentials.

## Folder Structure
*   `assets/`: CSS, JS, Images.
*   `classes/`: PHP Classes (`User`, `Logger`, `conn`).
*   `config/`: Configuration files (`auth.php`).
*   `includes/`: UI components (Header, Sidebar, Footer).
*   `operations/`: Backend logic for CRUD (Add, Edit, Delete, Export).
*   `views/`: Frontend pages (Forms, Lists, Reports).
*   `form_data_dump/`: SQL dumps and trigger scripts.

## Roles & Permissions
*   **Admin (Role ID 1)**: 
    *   Full access to all modules.
    *   Can Add, Edit, Delete employees.
    *   View Activity Logs and Reports.
*   **HR Staff (Role ID 2)**: 
    *   Can Add, Edit, Delete employees.
    *   View Reports.
*   **Employee (Role ID 3)**: 
    *   **Dashboard**: "Add Employee" is hidden. Replaced with "My Profile".
    *   **Access**: Restricted to viewing their own data only.
    *   **Protection**: Redirected if attempting to access unauthorized pages.

## License
This project is for educational purposes.

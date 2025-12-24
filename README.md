# Dolphin CRM - INFO2180 Project 2

A professional Customer Relationship Management (CRM) system built with PHP, MySQL, JavaScript, jQuery, and AJAX. This project demonstrates full-stack web development with user authentication, CRUD operations, and role-based access control.

## Features

### ✅ User Management
- **User Login** — Secure session-based authentication with password verification
- **Add Users** — Admin-only feature to create new users with password validation
  - Passwords must be at least 8 characters with uppercase, lowercase, and digit
  - Passwords are hashed using PHP's `password_hash()`
- **View Users** — Admin-only page displaying all system users in a table
  - Shows: Full name, email, role, created date
  - Role-based access control (Admin/Member)

### ✅ Contact Management
- View all contacts in a responsive list
- Add new contacts (Client or Lead type)
- View detailed contact information
- Delete contacts
- Notes system for tracking contact interactions

### ✅ Session Management
- Secure user sessions with role-based access
- Session-based authentication
- Admin-only protected pages

## Technology Stack

| Component | Technology |
|-----------|-----------|
| Backend | PHP 7.0+ with MySQLi |
| Database | MySQL 5.7+ / MariaDB |
| Frontend | HTML5, CSS3, JavaScript |
| Libraries | jQuery 3.6.0, AJAX |
| Architecture | RESTful API with JSON responses |

## Project Structure

```
INFO2180-PROJECT2/
├── index.html                    # Entry point (redirects to login)
├── config.php                    # Database configuration
├── login.php                     # Login page with authentication
├── users.php                     # Admin-only users list page
├── new_user.php                  # Admin-only new user form
├── logout.php                    # Session cleanup and logout
├── hash_gen.php                  # Utility to generate password hashes
├── schema.sql                    # Database schema (MySQL-compatible)
├── api/
│   ├── get_users.php            # Get all users (admin-only)
│   ├── add_user.php             # Create new user (admin-only)
│   ├── get_contacts.php         # Get all contacts
│   ├── add_contact.php          # Create new contact
│   ├── get_contact.php          # Get single contact with notes
│   ├── delete_contact.php       # Delete contact
│   └── add_note.php             # Add note to contact
├── css/
│   └── style.css                # Main stylesheet
└── js/
    ├── login.js                 # Login form handling
    └── new_user.js              # New user form handling
```

## Setup Instructions

### 1. Prerequisites
- XAMPP (Apache 2.4+, PHP 7.0+, MySQL 5.7+) or equivalent
- Modern web browser (Chrome, Firefox, Safari, Edge)

### 2. Database Setup

The database is created automatically during import. Run this command in PowerShell:

```powershell
& 'C:\xampp\mysql\bin\mysql.exe' -u root dolphin_crm -e "SOURCE c:/xampp/htdocs/INFO2180-PROJECT2/schema.sql;"
```

Or, create manually in phpMyAdmin:
1. Create database: `dolphin_crm`
2. Import `schema.sql` via phpMyAdmin SQL tab

### 3. Configuration

Edit `config.php` if needed (defaults work for XAMPP):
```php
define('DB_HOST', 'localhost');    // MySQL host
define('DB_USER', 'root');         // MySQL username
define('DB_PASSWORD', '');         // MySQL password (XAMPP default: empty)
define('DB_NAME', 'dolphin_crm');  // Database name
```

### 4. Start Application

1. Start XAMPP (Apache & MySQL services)
2. Navigate to: `http://localhost/INFO2180-PROJECT2/`
3. You'll be redirected to the login page

## Default Credentials

**Admin User:**
- Email: `admin@project2.com`
- Password: `password123`

## Usage Guide

### Logging In
1. Open `http://localhost/INFO2180-PROJECT2/login.php`
2. Enter email and password
3. On successful login, you'll see the dashboard

### Admin Features

#### Adding Users
1. Click "New User" or go to `new_user.php`
2. Fill in the form:
   - First Name (required)
   - Last Name (required)
   - Email (required, must be unique)
   - Password (required, must meet complexity rules)
   - Role (Admin or Member)
3. Password must contain:
   - At least 8 characters
   - At least one uppercase letter
   - At least one lowercase letter
   - At least one digit
4. Click "Save"

#### Viewing Users
1. Go to `http://localhost/INFO2180-PROJECT2/users.php` (admin-only)
2. Table displays all users with:
   - Full Name
   - Email address
   - Role (color-coded: Admin=blue, Member=orange)
   - Created date and time

### Contact Management

#### Adding Contacts
1. Click "Add Contact" in sidebar
2. Fill required fields (First Name, Last Name, Email, Type)
3. Optional fields: Title, Phone, Company
4. Select type: Client or Lead
5. Click "Add Contact"

#### Viewing Contacts
1. Contacts automatically load in the list
2. Click "View Details" to see full information and notes

#### Managing Notes
1. Open contact details
2. View note history
3. Add new notes in the comment field
4. Click "Add Note"

#### Deleting Contacts
1. Click "Delete" on any contact
2. Confirm deletion

## API Endpoints

All endpoints require authentication via `$_SESSION['user_id']`. Some endpoints require admin role.

### Users Endpoints

#### GET `/api/get_users.php`
**Access:** Admin only  
**Returns:** List of all users

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "fullname": "Admin User",
      "email": "admin@project2.com",
      "role": "Admin",
      "created_at": "2025-12-22 10:00:00"
    }
  ]
}
```

#### POST `/api/add_user.php`
**Access:** Admin only  
**Parameters:**
- `firstname` (string, required)
- `lastname` (string, required)
- `email` (string, required, unique)
- `password` (string, required)
  - Must be 8+ chars with uppercase, lowercase, digit
- `role` (string, required: 'Admin' or 'Member')

**Response:**
```json
{
  "success": true,
  "id": 2,
  "message": "User created successfully"
}
```

### Contact Endpoints

#### GET `/api/get_contacts.php`
**Access:** Authenticated users  
**Returns:** All contacts

#### POST `/api/add_contact.php`
**Access:** Authenticated users  
**Parameters:**
- `firstname` (required)
- `lastname` (required)
- `email` (required, unique)
- `title` (optional)
- `telephone` (optional)
- `company` (optional)
- `type` (required: 'Client' or 'Lead')

#### GET `/api/get_contact.php?id={id}`
**Access:** Authenticated users  
**Returns:** Single contact with notes

#### POST `/api/delete_contact.php`
**Access:** Authenticated users  
**Parameters:** `id`

#### POST `/api/add_note.php`
**Access:** Authenticated users  
**Parameters:**
- `contact_id`
- `comment`

## Database Schema

### Users Table
```sql
CREATE TABLE Users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    firstname VARCHAR(50) NOT NULL,
    lastname VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('Admin','Member') NOT NULL DEFAULT 'Member',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Contacts Table
```sql
CREATE TABLE Contacts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(10),
    firstname VARCHAR(50) NOT NULL,
    lastname VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    telephone VARCHAR(20),
    company VARCHAR(100),
    type ENUM('Client','Lead') NOT NULL,
    assigned_to INT REFERENCES Users(id),
    created_by INT REFERENCES Users(id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### Notes Table
```sql
CREATE TABLE Notes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    contact_id INT NOT NULL REFERENCES Contacts(id) ON DELETE CASCADE,
    comment TEXT NOT NULL,
    created_by INT REFERENCES Users(id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

## Security Features

✅ **Password Hashing** — Uses PHP's `password_hash()` with bcrypt  
✅ **Session-Based Auth** — Secure user identification  
✅ **Prepared Statements** — SQL injection prevention  
✅ **Input Validation** — Both client and server-side  
✅ **Role-Based Access** — Admin-only protected features  
✅ **Input Sanitization** — HTML escaping and data filtering  

## File Descriptions

| File | Purpose |
|------|---------|
| `config.php` | DB connection & session management |
| `login.php` | Authentication page & logic |
| `users.php` | Admin-only user list page |
| `new_user.php` | Admin user creation form |
| `logout.php` | Session cleanup |
| `api/get_users.php` | API to fetch all users |
| `api/add_user.php` | API to create users (admin-only) |
| `css/style.css` | Responsive styling |
| `js/login.js` | Login form interactions |
| `js/new_user.js` | User form validation & AJAX |

## Troubleshooting

### Connection Error on Login
**Problem:** "Connection failed"  
**Solution:**
- Verify MySQL is running in XAMPP
- Check `DB_HOST`, `DB_USER`, `DB_PASSWORD`, `DB_NAME` in `config.php`
- Ensure database `dolphin_crm` exists
- Run: `& 'C:\xampp\mysql\bin\mysql.exe' -u root -e "SHOW DATABASES;"`

### Login Fails with Valid Credentials
**Problem:** "Invalid email or password"  
**Solution:**
- Check that admin user exists: `SELECT * FROM Users;`
- Verify password: `admin@project2.com` / `password123`
- Admin user is auto-inserted via `schema.sql`

### Users Page Shows "Forbidden"
**Problem:** Can't access `users.php` as admin  
**Solution:**
- Ensure you're logged in as Admin role
- Check `$_SESSION['user_role']` is set to 'Admin'
- Verify your user record has `role = 'Admin'` in database

### Password Validation Fails
**Problem:** "Password must be at least 8 characters..."  
**Solution:**
- Ensure password meets requirements:
  - Minimum 8 characters
  - At least one uppercase letter (A-Z)
  - At least one lowercase letter (a-z)
  - At least one digit (0-9)
- Example valid password: `MyPassword123`

### AJAX Requests Fail
**Problem:** Forms don't submit or errors in console  
**Solution:**
- Open browser console (F12)
- Check API endpoint URLs in Network tab
- Verify API files exist in `api/` folder
- Ensure you're logged in (check session)

## Advanced Features (Future Enhancements)

- [ ] Contact search and filtering
- [ ] Edit contact functionality
- [ ] Export contacts to CSV
- [ ] Contact assignment to users
- [ ] Email notifications
- [ ] Advanced user permissions
- [ ] Contact activity timeline
- [ ] User profile management
- [ ] Password change functionality
- [ ] Account settings page

## Browser Compatibility

| Browser | Support |
|---------|---------|
| Chrome 90+ | ✅ Full support |
| Firefox 88+ | ✅ Full support |
| Safari 14+ | ✅ Full support |
| Edge 90+ | ✅ Full support |
| IE 11 | ⚠️ Limited support |

## Performance Notes

- Database uses InnoDB engine for ACID compliance
- Indexes on foreign keys for faster queries
- UTF-8MB4 charset for international characters
- Prepared statements to prevent SQL injection
- AJAX for seamless user experience without page reloads

## Maintenance

### Regular Tasks
- Monitor database size with `SELECT table_schema, ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) FROM information_schema.tables WHERE table_schema='dolphin_crm' GROUP BY table_schema;`
- Backup database regularly: `mysqldump -u root dolphin_crm > backup.sql`
- Review user logs for suspicious activity
- Keep MySQL and PHP updated

### Creating Additional Admin Users

Use the `new_user.php` form while logged in as Admin, or SQL:

```sql
INSERT INTO Users (firstname, lastname, email, password, role) VALUES 
('John', 'Doe', 'john@example.com', PASSWORD_HASH('SecurePass123'), 'Admin');
```

(Use `password_hash()` in PHP or a tool to generate the bcrypt hash)

## Support & Documentation

- **README.md** — This file with full documentation
- **Code Comments** — Implementation details throughout PHP files
- **API Responses** — All endpoints return JSON with success/error status
- **Browser Console** — (F12) Shows AJAX request details for debugging

## License

Educational Use Only - INFO2180 Project 2

## Author

Created for INFO2180 Course Project 2 (Dec 2025)

---

**Last Updated:** December 22, 2025  
**Version:** 1.0.0
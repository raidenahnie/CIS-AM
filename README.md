# � CIS-AM - Curriculum Implementation System: Attendance Monitoring

<p align="center">
  <strong>Official Attendance Tracking System for the Department of Education - Cavite Province</strong><br>
  <em>Curriculum Implementation Division</em>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-11.x-red?style=flat-square&logo=laravel" alt="Laravel">
  <img src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat-square&logo=php" alt="PHP">
  <img src="https://img.shields.io/badge/Tailwind-CSS-38B2AC?style=flat-square&logo=tailwind-css" alt="Tailwind CSS">
  <img src="https://img.shields.io/badge/DepEd-Cavite-blue?style=flat-square" alt="DepEd Cavite">
  <img src="https://img.shields.io/badge/License-MIT-green?style=flat-square" alt="License">
</p>

---

## 📋 Table of Contents

- [About](#-about)
- [Key Features](#-key-features)
- [Technology Stack](#-technology-stack)
- [System Requirements](#-system-requirements)
- [Installation](#-installation)
- [Configuration](#-configuration)
- [Usage](#-usage)
- [Features Documentation](#-features-documentation)
- [API Endpoints](#-api-endpoints)
- [Security Features](#-security-features)
- [Testing](#-testing)
- [Contributing](#-contributing)
- [License](#-license)

---

## 🎯 About

**CIS-AM (Curriculum Implementation System - Attendance Monitoring)** is a specialized web-based attendance management system developed for the **Department of Education - Cavite Province**, specifically designed for use by the **Curriculum Implementation Division**.

This system provides accurate, GPS-verified attendance tracking for DepEd Cavite personnel, ensuring accountability and efficient workforce management across multiple school locations and division offices throughout Cavite Province.

### 🏛️ Government Agency Information
- **Agency:** Department of Education (DepEd)
- **Location:** Cavite Province, Philippines
- **Division:** Curriculum Implementation Division
- **Purpose:** Personnel attendance monitoring and management

### 🌟 Designed For:
- DepEd Cavite Curriculum Implementation Division staff
- Multi-location school site monitoring
- Division office personnel tracking
- Field work and school visit verification
- GPS-verified attendance for accountability

---

## 🚀 Key Features

### 👤 **Employee Features**
- ✅ **GPS-Based Check-In/Out** - Location-verified attendance with radius checking
- ✅ **Manual Location Entry** - Secure backup option with access code protection
- ✅ **Break Time Tracking** - Start/end break with automatic duration calculation
- ✅ **Real-Time Dashboard** - View current status, work hours, and attendance history
- ✅ **Multiple Workplace Support** - Manage assignments across different locations
- ✅ **Attendance History** - Complete activity logs and historical records

### 👨‍💼 **Administrative Features**
- 📊 **Comprehensive Dashboard** - Real-time statistics and monitoring
- 📈 **Advanced Reporting** - Weekly, monthly, and custom date range reports
- 👥 **User Management** - Full CRUD operations with role-based access
- 🏢 **Workplace Management** - Manage multiple locations with GPS coordinates
- 📥 **Data Export** - CSV/Excel export for reports and records
- 🔐 **System Settings** - Configurable parameters and access codes
- 📝 **Activity Logging** - Complete audit trail of all administrative actions
- 🔄 **Bulk Operations** - Efficient management of multiple records

### 🔒 **Security Features**
- 🛡️ **Single Device Login** - Automatic session invalidation on new device login
- 🔑 **Password Reset** - Secure email-based password recovery
- 🔐 **Admin Verification** - Password confirmation for sensitive operations
- 📋 **Activity Auditing** - Complete tracking of all system changes
- 🚫 **Session Management** - Automatic logout on concurrent login detection

### 📊 **Reporting & Analytics**
- 📅 **Flexible Date Ranges** - Weekly, monthly, or custom period reports
- 🎯 **Late Arrival Tracking** - Automatic detection and calculation
- ⏱️ **Work Hours Calculation** - Accurate time tracking with break deduction
- 📈 **Attendance Statistics** - Present/absent rates, average hours, trends
- 🔍 **Advanced Filtering** - By employee, workplace, date range, and status
- 💾 **Export Capabilities** - Download reports in CSV/Excel format

---

## 🛠 Technology Stack

### Backend
- **Framework:** Laravel 11.x
- **PHP Version:** 8.2+
- **Database:** SQLite (configurable for MySQL/PostgreSQL)
- **Authentication:** Laravel built-in authentication
- **Session Management:** Database-driven sessions

### Frontend
- **CSS Framework:** Tailwind CSS 3.4+
- **JavaScript:** Vanilla JS with modern ES6+
- **Build Tool:** Vite 5.x
- **Icons:** SVG-based custom icons
- **UI Pattern:** Responsive, mobile-first design

### Key Packages
- **maatwebsite/excel:** Excel import/export functionality
- **phpoffice/phpspreadsheet:** Spreadsheet manipulation
- **laravel/tinker:** Powerful REPL for Laravel
- **spatie/laravel-ignition:** Beautiful error pages

---

## 💻 System Requirements

- **PHP:** 8.2 or higher
- **Composer:** 2.x
- **Node.js:** 18.x or higher
- **NPM/Yarn:** Latest version
- **Web Server:** Apache/Nginx (XAMPP/WAMP/MAMP for local development)
- **Database:** SQLite/MySQL 5.7+/PostgreSQL 9.6+
- **Browser:** Modern browser with GPS/geolocation support

---

## 📦 Installation

### 1. Clone the Repository
```bash
git clone https://github.com/raidenahnie/CIS-AM.git
cd cis-am
```

### 2. Install PHP Dependencies
```bash
composer install
```

### 3. Install Node Dependencies
```bash
npm install
```

### 4. Environment Configuration
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 5. Database Setup
```bash
# Run migrations
php artisan migrate

# (Optional) Seed database with sample data
php artisan db:seed
```

### 6. Build Frontend Assets
```bash
# Development build
npm run dev

# Production build
npm run build
```

### 7. Start Development Server
```bash
php artisan serve
```

The application will be available at `http://localhost:8000`

---

## ⚙️ Configuration

### Database Configuration

#### SQLite (Default)
```env
DB_CONNECTION=sqlite
# Database file created automatically at database/database.sqlite
```

#### MySQL
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cis_am
DB_USERNAME=root
DB_PASSWORD=your_password
```

### Application Settings
```env
APP_NAME="CIS-AM - DepEd Cavite"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_TIMEZONE=Asia/Manila  # Philippine Standard Time
```

### Session Configuration
```env
SESSION_DRIVER=database
SESSION_LIFETIME=120  # Minutes
```

### Mail Configuration
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_FROM_ADDRESS=noreply@deped-cavite.gov.ph
MAIL_FROM_NAME="DepEd Cavite - CIS-AM"
```

---

## 📖 Usage

### For Employees

#### 1. **Login**
- Navigate to the login page
- Enter your email and password
- Click "Sign In"

#### 2. **Check In**
- Select your workplace from the dashboard
- Click "Check In" button
- Allow location access when prompted
- System verifies you're within the workplace radius

#### 3. **Break Management**
- Click "Start Break" when taking a break
- Click "End Break" when returning to work
- Break duration is automatically calculated

#### 4. **Check Out**
- Click "Check Out" when ending your work day
- System calculates total hours worked (minus break time)

#### 5. **View History**
- Navigate to "Attendance History" section
- View detailed logs of all activities
- Filter by date range

### For Administrators

#### 1. **Dashboard Overview**
- View real-time statistics (check-ins, late arrivals, etc.)
- Monitor current employee status
- Quick access to all management functions

#### 2. **User Management**
- Add new employees with role assignment
- Edit user information and permissions
- Deactivate/activate user accounts
- Bulk operations for efficiency

#### 3. **Workplace Management**
- Create new workplace locations
- Set GPS coordinates and check-in radius
- Assign users to workplaces
- View workplace statistics

#### 4. **Reports**
- Generate weekly/monthly attendance reports
- Filter by employee, workplace, or date range
- Export to CSV/Excel format
- View attendance statistics and trends

#### 5. **System Settings**
- Update manual entry access code
- Configure admin account settings
- Manage system parameters

---

## 📚 Features Documentation

Detailed documentation for specific features:

- [Attendance Monitoring Feature](ATTENDANCE_MONITORING_FEATURE.md)
- [Reports System](REPORTS_FEATURE.md)
- [Session Management](SESSION_MANAGEMENT_FEATURE.md)
- [Manual Entry Code](MANUAL_ENTRY_CODE_FEATURE.md)
- [Workplace Management](WORKPLACE_IMPROVEMENTS.md)
- [Bulk Operations](BULK_OPERATIONS_SUMMARY.md)
- [System Settings](SYSTEM_SETTINGS_UPDATES.md)

---

## 🔌 API Endpoints

### Authentication
```
POST   /login              # User login
POST   /logout             # User logout
POST   /password/reset     # Password reset
```

### Employee Dashboard
```
GET    /api/user-stats/{userId}              # User statistics
GET    /api/attendance-history/{userId}      # Attendance history
GET    /api/attendance-logs/{userId}         # Activity logs
GET    /api/user-workplace/{userId}          # Primary workplace
GET    /api/user-workplaces/{userId}         # All assigned workplaces
GET    /api/current-status/{userId}          # Current work status
POST   /api/checkin                          # Check in
POST   /api/perform-action                   # Check out/break
POST   /api/save-workplace                   # Save workplace
POST   /api/set-primary-workplace            # Set primary location
GET    /api/manual-entry-code                # Get access code
```

### Admin Dashboard
```
GET    /admin/dashboard                      # Admin dashboard
GET    /admin/users                          # User list
POST   /admin/users                          # Create user
PUT    /admin/users/{id}                     # Update user
DELETE /admin/users/{id}                     # Delete user
GET    /admin/workplaces                     # Workplace list
POST   /admin/workplaces                     # Create workplace
GET    /admin/attendance-stats               # Attendance statistics
```

### Reports
```
GET    /admin/reports/attendance             # Attendance reports
GET    /admin/reports/individual/{user}      # Individual report
GET    /admin/reports/export                 # Export report
GET    /admin/reports/summary-stats          # Summary statistics
```

---

## 🔐 Security Features

### Authentication & Authorization
- ✅ Role-based access control (Admin/Employee)
- ✅ Middleware protection on all routes
- ✅ CSRF token validation
- ✅ SQL injection prevention via Eloquent ORM

### Session Security
- ✅ Single device login enforcement
- ✅ Automatic session invalidation
- ✅ Session ID regeneration on login
- ✅ Secure session storage in database

### Data Protection
- ✅ Password hashing with bcrypt
- ✅ Secure password reset flow
- ✅ Admin password verification for sensitive actions
- ✅ Input validation and sanitization

### Activity Auditing
- ✅ Complete admin action logging
- ✅ User activity tracking
- ✅ Login/logout event recording
- ✅ System change audit trail

---

## 🧪 Testing

### Run PHPUnit Tests
```bash
php artisan test
```

### Feature Testing
The system includes comprehensive test coverage for:
- Authentication flows
- Attendance operations
- Administrative functions
- Report generation
- User management

### Manual Testing Guides
- [System Settings Testing](TESTING_GUIDE_SYSTEM_SETTINGS.md)
- [Session Management Testing](TESTING_SESSION_MANAGEMENT.md)

---

## 🗺️ Roadmap

### Upcoming Features
- [ ] Mobile application (iOS/Android)
- [ ] Biometric authentication support
- [ ] Advanced analytics dashboard
- [ ] Notifications system (email/SMS)
- [ ] Multi-language support
- [ ] Dark mode theme
- [ ] Calendar view for attendance
- [ ] Department/team management
- [ ] Shift scheduling
- [ ] Overtime calculation
- [ ] Leave management integration

---

## 🤝 Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

### Coding Standards
- Follow PSR-12 coding standards
- Write meaningful commit messages
- Add tests for new features
- Update documentation as needed

---

## 📄 License

This project is open-sourced software licensed under the [MIT license](LICENSE).

---

## 👨‍💻 Development Team

- **Developed For:** Department of Education - Cavite Province
- **Division:** Curriculum Implementation Division
- **Developer:** raidenahnie
- **Repository:** [github.com/raidenahnie/CIS-AM](https://github.com/raidenahnie/CIS-AM)

---

## 📞 Support

For support, feature requests, or bug reports:
- 🐛 [Open an Issue](https://github.com/raidenahnie/CIS-AM/issues)
- 📧 Email: curriculum.implementation@deped-cavite.gov.ph
- 📖 [Documentation](https://github.com/raidenahnie/CIS-AM/wiki)
- 🏛️ Office: DepEd Cavite - Curriculum Implementation Division

---

## 🙏 Acknowledgments

Built with:
- [Laravel](https://laravel.com) - The PHP Framework for Web Artisans
- [Tailwind CSS](https://tailwindcss.com) - A utility-first CSS framework
- [Vite](https://vitejs.dev) - Next Generation Frontend Tooling
- [PHPSpreadsheet](https://phpspreadsheet.readthedocs.io) - Excel library for PHP

---

<p align="center">
  <strong>Department of Education - Cavite Province</strong><br>
  <em>Curriculum Implementation Division</em>
</p>

<p align="center">
  <sub>Last Updated: October 2025</sub>
</p>

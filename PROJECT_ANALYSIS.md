# eseCRM - Project Analysis & Documentation

## 📋 Project Overview

**Project Name:** eseCRM  
**Type:** Laravel-based Customer Relationship Management System  
**Framework:** Laravel 10.32.1  
**PHP Version:** ^8.1  
**Environment:** Asia/Kolkata Timezone

---

## 🏗️ Architecture & Tech Stack

### Backend
- **Framework:** Laravel 10.32.1
- **Language:** PHP 8.1+
- **ORM:** Eloquent
- **Authentication:** Laravel Sanctum (API tokens)
- **CORS:** Enabled

### Frontend
- **Build Tool:** Vite 4.0.0
- **HTTP Client:** Axios 1.6.1
- **Module Type:** ES Modules

### External Services
- **Firebase:** 
  - `kreait/firebase-php` (^7.13)
  - `kreait/laravel-firebase` (^5.10)
  - Admin SDK credentials stored in: `storage/esecrm-firebase-adminsdk-35a6t-5892446bc0.json`

### Additional Libraries
- **PDF Generation:** barryvdh/laravel-dompdf (^3.1)
- **HTTP Client:** Symfony HTTP Client & Mailer
- **Email:** Symfony Mailer (^6.4)
- **Testing:** PHPUnit (^10.1), Mockery

---

## 📁 Project Structure

### Core Application Directories

#### `/app` - Application Logic
```
app/
├── Console/
│   ├── Kernel.php          # Command scheduling
│   └── Commands/           # Custom CLI commands
├── Exceptions/
│   └── Handler.php         # Global exception handling
├── Http/
│   ├── Kernel.php          # HTTP middleware configuration
│   ├── Controllers/        # API/Web controllers
│   └── Middleware/         # Custom middleware
├── Jobs/
│   └── SendScheduledEmailJob.php  # Queued email jobs
├── Mail/
│   ├── CustomMailable.php
│   ├── ScheduledEmail.php
├── Models/                 # Eloquent models (see Models section)
└── Providers/              # Service providers
```

#### `/config` - Configuration Files
- `app.php` - Application name, environment, timezone (Asia/Kolkata)
- `auth.php` - Authentication configuration
- `database.php` - Database connections (MySQL default)
- `mail.php` - SMTP/Mailgun configuration
- `firebase.php` - Firebase integration settings
- `cors.php` - CORS configuration
- `queue.php` - Job queue configuration
- `sanctum.php` - API token configuration

#### `/database` - Database Layer
```
database/
├── migrations/    # Schema migration files
├── factories/     # Model factories for testing
└── seeders/       # Database seeders
```

#### `/routes` - Route Definitions
- `api.php` - RESTful API endpoints (prefix: `/api/v1`)
- `web.php` - Web routes
- `console.php` - Console commands
- `channels.php` - Broadcasting channels

#### `/public` - Public Assets
```
public/
├── assets/        # CSS, JS, images
├── firebase/      # Firebase SDK files
├── whatsapp/      # WhatsApp integration files
├── privacy-policy.html
└── robots.txt
```

#### `/resources` - Front-end Resources
```
resources/
└── views/         # Blade template files
```

#### `/storage` - Runtime Data
```
storage/
├── esecrm-firebase-adminsdk-35a6t-5892446bc0.json
├── app/           # Application storage (file uploads)
├── framework/     # Framework cache/sessions
└── logs/          # Application logs
```

#### `/tests` - Test Suite
```
tests/
├── Feature/       # Feature tests
├── Unit/          # Unit tests
├── TestCase.php   # Base test class
└── CreatesApplication.php
```

---

## 🗄️ Database Models

### Core Business Models

| Model | Purpose | Key Fields |
|-------|---------|-----------|
| **User** | System users/employees | name, mob, email, password |
| **Leads** | Sales prospects | cid, uid, name, company, email, mob, gstno, location, purpose, status, position, industry, website |
| **Clients** | Customer accounts | - |
| **Companies** | Company details | - |
| **Contacts** | Contact information | - |
| **Projects** | Client projects | - |
| **Tasks** | Work tasks | - |
| **Task_comments** | Task discussions | - |
| **Task_working_hours** | Time tracking | - |

### Sales/Commerce Models

| Model | Purpose |
|-------|---------|
| **Proposals** | Sales proposals |
| **Proposal_items** | Line items in proposals |
| **Proposal_signatures** | Digital signatures |
| **Invoices** | Customer invoices |
| **Invoice_items** | Line items in invoices |
| **Contracts** | Service/purchase contracts |
| **Recoveries** | Payment recovery tracking |

### CRM Features Models

| Model | Purpose |
|-------|---------|
| **LeadAssigns** | Lead assignment tracking |
| **Lead_comments** | Lead discussion notes |
| **Activities** | User/system activities |
| **Attendances** | Employee attendance |
| **Holidays** | Company holidays |
| **Todo_lists** | Task management |
| **Roles** | User role management |

### System Models

| Model | Purpose | Notes |
|-------|---------|-------|
| **ScheduledEmail** | Email scheduling | Runs daily at 09:00 |
| **EmailTemplate** | Email templates | Custom email designs |
| **SmtpSettings** | SMTP configuration | Dynamic mail settings |
| **NotificationHistory** | Notification logs | System notifications |
| **Fcmregs** | Firebase Cloud Messaging | Push notification registrations |
| **Eselicenses** | License management | Software licensing |

---

## 🛣️ API Endpoints

### Authentication & Auth Management
```
GET /api/v1/user              # Get current authenticated user (requires: auth:sanctum)
```

### Core API Routes (Prefix: `/api/v1`)

#### FCM & Notifications
```
GET  /registerfcm             # Register device for push notifications
GET  /send-notification       # Send push notification
```

#### User Management
```
GET  /check-login             # Verify login status
```

#### Business Operations
```
POST /enquiry                 # Submit inquiry/lead
GET  /attendance              # Get/post attendance records
```

---

## 🎯 Controllers

### Main Controllers

| Controller | Responsibility |
|-----------|-----------------|
| **ApiController** | Core API endpoints (FCM, notifications, login, inquiries, attendance) |
| **AuthController** | User authentication & authorization |
| **UserController** | User management & operations |
| **LeadController** | Lead/prospect management |
| **NewLeadController** | New lead creation & handling |
| **ClientController** | Client account management |
| **TaskController** | Task creation & tracking |
| **FCMController** | Firebase Cloud Messaging operations |
| **EmailController** | Email operations & templates |
| **SettingController** | System settings management |
| **SchedulerTestController** | Scheduled task testing |
| **AjaxController** | AJAX request handlers |
| **resController** | Resource responses |
| **HomeController** | Dashboard/home page |
| **Controller** | Base controller (parent class) |

---

## 📧 Email System

### Key Features
- **SMTP Integration:** Configurable mail settings
- **Scheduled Emails:** Daily scheduling at 09:00 IST
- **Email Templates:** Custom email design system
- **Mailable Classes:**
  - `CustomMailable.php` - Generic email
  - `ScheduledEmail.php` - Scheduled email campaigns
- **Mail Driver:** SMTP (default: Mailgun)

### Scheduled Jobs
- **SendScheduledEmailJob** - Handles scheduled email dispatch
- **Command:** `app:send-scheduled-emails` (runs daily at 09:00)

---

## 🔐 Security Features

### Authentication
- **Method:** Laravel Sanctum (API tokens)
- **Middleware Groups:**
  - `web` - CSRF protection, sessions, cookies
  - `api` - Rate throttling, token authentication

### CORS
- Enabled and configurable in `config/cors.php`

### Middleware Stack
- Request size validation
- String trimming
- Empty string to null conversion
- CSRF token verification

---

## 🚀 Running & Development

### Scripts

```json
"dev": "vite"              // Start development server
"build": "vite build"      // Build for production
```

### Artisan Commands

```bash
php artisan key:generate           # Generate APP_KEY
php artisan serve                  # Start development server
php artisan migrate                # Run migrations
php artisan db:seed                # Seed database
php artisan app:send-scheduled-emails  # Trigger email scheduler
```

### Scheduled Tasks
- **Email Scheduler:** Runs daily at 09:00 IST via Laravel Scheduler

---

## 🔧 Configuration Management

### Environment Variables (`.env`)
```
APP_NAME=eseCRM
APP_ENV=production
APP_DEBUG=true
APP_URL=https://esecrm.com
APP_TIMEZONE=Asia/Kolkata

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=esecrm
DB_USERNAME=root
DB_PASSWORD=

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_USERNAME=
MAIL_PASSWORD=

FIREBASE_CREDENTIALS=storage/esecrm-firebase-adminsdk-35a6t-5892446bc0.json
```

---

## 📊 Key Features

### Lead Management
- Lead creation and assignment
- Lead status tracking
- Lead comments and notes
- Multiple contact fields (email, mobile, WhatsApp)
- Industry and company categorization
- GST number tracking
- Language preferences
- Custom tags

### Sales Pipeline
- Proposals with line items
- Digital signatures
- Invoice generation (PDF via DOMPDF)
- Contract management
- Recovery tracking

### Notification System
- Firebase Cloud Messaging (FCM)
- Push notifications
- Notification history tracking
- Device registration

### Time & Attendance
- Employee attendance tracking
- Holiday management
- Task time logging (task_working_hours)
- Activity tracking

### Task Management
- Task creation and assignment
- Task comments/discussions
- Time tracking per task
- Todo lists

---

## 🧪 Testing Infrastructure

- **Framework:** PHPUnit 10.1
- **Mocking:** Mockery
- **Faker:** FakerPHP (for test data)
- **Test Locations:**
  - `tests/Feature/` - Integration tests
  - `tests/Unit/` - Unit tests

---

## 📦 Dependencies Summary

### Production Dependencies
- Laravel Framework 10.32.1
- Firebase SDK & Laravel Firebase
- Guzzle HTTP Client
- Symfony HTTP Client & Mailer
- DOMPDF (PDF generation)
- Laravel Sanctum (API auth)
- Laravel Tinker (REPL)

### Development Dependencies
- PHPUnit, Mockery (Testing)
- FakerPHP (Test data)
- Laravel Sail (Docker)
- Laravel Pint (Code formatting)
- Spatie Ignition (Error handling)

---

## 🎨 Code Standards

- **PSR-4 Autoloading:** App files auto-loaded from `app/` directory
- **Code Style:** Laravel conventions with Pint formatting
- **Database:** Auto-optimized autoloading
- **Stability:** Minimum stable version enforced

---

## 🔍 Project Status & Notes

✅ **Fully Functional CRM System** with:
- Complete lead management pipeline
- Email scheduling & notifications
- Firebase integration for push notifications
- RESTful API architecture
- Multi-user support with role management
- Comprehensive activity tracking
- Invoice & proposal generation

🚀 **Production Ready** - Hosted at https://esecrm.com

---

## 📝 Summary

**eseCRM** is a comprehensive Laravel-based CRM system designed for managing sales leads, clients, projects, and business operations. It features:

- Modern Laravel 10 architecture with Eloquent ORM
- Firebase integration for mobile push notifications
- Scheduled email system with cron scheduling
- RESTful API with Sanctum authentication
- Complete sales pipeline (proposals → invoices → contracts)
- Time tracking and attendance management
- Multi-tier user roles and permissions
- Notification system with history tracking

The project is production-ready, hosted on `esecrm.com`, and configured for the Asia/Kolkata timezone with comprehensive CRM functionality for B2B sales operations.

---

*Generated: January 22, 2026*

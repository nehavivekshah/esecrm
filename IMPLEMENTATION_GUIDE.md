# eseCRM Implementation Guide

## ✅ Completed Implementations

### 1. **Database Schema & Migrations**
Created comprehensive migrations for:
- ✅ Clients table (customer accounts)
- ✅ Invoices & Invoice Items (billing)
- ✅ Proposals & Proposal Items (sales)
- ✅ Projects (client projects)
- ✅ Lead Assignments & Comments (lead management)
- ✅ Attendances (employee tracking)
- ✅ Email Templates (email system)
- ✅ Activities (audit logging)
- ✅ FCM Registrations (push notifications)
- ✅ Scheduled Emails (email scheduling)
- ✅ Roles (user permissions)
- ✅ Users table enhancement (added mobile, role, company fields)
- ✅ Leads table enhancement (added all CRM fields)

**Location:** `/database/migrations/2024_01_22_*`

### 2. **Eloquent Models with Relationships**
All models now include:
- ✅ Type-hinted relationships
- ✅ Property casting
- ✅ Query scopes
- ✅ Helper methods

**Models Updated:**
- User (added role, company, relationships)
- Companies (clients, projects, invoices, users)
- Clients (company, invoices, proposals, projects)
- Invoices (client, company, items, calculateTotal)
- Invoice_items (invoice, auto-calculate totals)
- Proposals (client, creator, items, signatures)
- Proposal_items (proposal, auto-calculate)
- Projects (client, company, tasks)
- Task (project, assignee, comments, workingHours)
- Task_comments (task, user)
- Task_working_hours (task, user)
- Attendances (user)
- Roles (users)
- Activity (user, audit logging)
- Fcmregs (user, FCM device registration)
- ScheduledEmail (email scheduling)
- Leads (company, creator, assignedUser, assignments, comments)
- LeadAssigns (lead, user)
- Lead_comments (lead, user)
- EmailTemplate (template rendering with variables)

**Location:** `/app/Models/*`

### 3. **Authentication API Endpoints**

#### Public Endpoints
```
POST /api/v1/auth/register
POST /api/v1/auth/login
GET  /api/v1/check-login
```

#### Protected Endpoints (Requires Auth Token)
```
GET  /api/v1/auth/user
PUT  /api/v1/auth/profile
POST /api/v1/auth/logout
```

**Features:**
- ✅ Email & Mobile validation
- ✅ Password hashing & verification
- ✅ Sanctum token generation
- ✅ Profile update with password change
- ✅ Account activation status

**Controller:** [AuthController](app/Http/Controllers/AuthController.php)

### 4. **Lead Management API (NEW)**

#### CRUD Operations
```
GET    /api/v1/leads                      # List with filters & pagination
POST   /api/v1/leads                      # Create lead
GET    /api/v1/leads/{id}                 # Get single lead
PUT    /api/v1/leads/{id}                 # Update lead
DELETE /api/v1/leads/{id}                 # Delete lead
```

#### Lead-Specific Operations
```
POST   /api/v1/leads/{id}/assign          # Assign lead to user
POST   /api/v1/leads/{id}/comments        # Add comment
GET    /api/v1/leads/{id}/comments        # Get all comments
GET    /api/v1/leads/statistics           # Lead KPI dashboard
```

**Features:**
- ✅ Advanced filtering (status, assigned_to, company, search)
- ✅ Pagination (default 15 per page)
- ✅ Activity audit logging
- ✅ Lead assignment with priority & deadline
- ✅ Comment threading
- ✅ Lead statistics (totals, values, conversion rates)
- ✅ Relationship eager loading

**Controller:** [LeadApiController](app/Http/Controllers/LeadApiController.php)

#### Query Examples

**Filter by status:**
```
GET /api/v1/leads?status=qualified
```

**Filter by assigned user:**
```
GET /api/v1/leads?assigned_to=5
```

**Search by name/email:**
```
GET /api/v1/leads?search=john
```

**Get statistics:**
```
GET /api/v1/leads/statistics
```

Response:
```json
{
  "total_leads": 150,
  "new_leads": 45,
  "qualified_leads": 30,
  "negotiating_leads": 25,
  "won_leads": 40,
  "lost_leads": 10,
  "total_values": 500000,
  "avg_lead_value": 3333.33
}
```

### 5. **API Routes Configuration**
**Location:** [/routes/api.php](routes/api.php)

Routes organized by feature:
- Authentication (public + protected)
- Lead management (protected)
- FCM & notifications (protected)
- Business operations (protected)

---

## 🚀 Next Steps to Implement

### Phase 2: Complete CRUD Operations
- [ ] Client management API (CrudController)
- [ ] Invoice management API
- [ ] Proposal management API
- [ ] Project management API
- [ ] Task management API

### Phase 3: Advanced Features
- [ ] Email template system
- [ ] Scheduled email queue
- [ ] PDF invoice generation
- [ ] Activity audit trail
- [ ] Role-based access control (RBAC)

### Phase 4: Frontend/Dashboard
- [ ] Vue.js/React components
- [ ] Dashboard views
- [ ] Lead pipeline visualization
- [ ] Reports & analytics

---

## 📋 Database Setup Instructions

### 1. Configure .env
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=esecrm
DB_USERNAME=root
DB_PASSWORD=
```

### 2. Run Migrations
```bash
php artisan migrate
```

### 3. Seed Sample Data (Optional)
```bash
php artisan db:seed
```

---

## 🔐 API Authentication

All protected endpoints require an API token in the header:

```
Authorization: Bearer {token}
```

### Get Token (After Login)
```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "user@example.com",
    "password": "password123"
  }'
```

Response:
```json
{
  "message": "Login successful",
  "user": { ... },
  "token": "1|abcdef..."
}
```

### Use Token
```bash
curl -H "Authorization: Bearer 1|abcdef..." \
  http://localhost:8000/api/v1/leads
```

---

## 🛠️ Development Commands

### Start Development Server
```bash
php artisan serve
```

### Run Migrations
```bash
php artisan migrate
php artisan migrate:refresh  # Reset & re-run all
```

### Clear Cache
```bash
php artisan cache:clear
php artisan config:clear
```

### Check Routes
```bash
php artisan route:list
```

### Generate API Documentation
```bash
php artisan scribe:generate
```

---

## 📊 Database Relations Map

```
Companies (1) ──── (M) Users
    │                    │
    ├── (1)─(M) Clients   └── (1)─(M) Leads
    │              │              │
    │              ├── (1)─(M) Invoices
    │              │              
    │              ├── (1)─(M) Proposals
    │              │
    │              └── (1)─(M) Projects ─── (1)─(M) Tasks
    │
    └── (1)─(M) Invoices

Leads (1) ──── (M) LeadAssigns ──── (1) Users
Leads (1) ──── (M) Lead_comments ──── (1) Users

Users (1) ──── (M) Attendances
Users (1) ──── (M) Fcmregs
```

---

## 📧 Email System (To Implement)

### Template Variables
```php
{{lead_name}}
{{lead_email}}
{{company_name}}
{{contact_person}}
{{due_date}}
```

### Create Template
```bash
php artisan make:model EmailTemplate -m
```

---

## 🔍 API Response Format

All endpoints follow consistent response format:

**Success (200):**
```json
{
  "message": "Action successful",
  "data": { ... }
}
```

**Created (201):**
```json
{
  "message": "Resource created",
  "data": { ... }
}
```

**Error (422):**
```json
{
  "message": "Validation failed",
  "errors": {
    "field": ["Error message"]
  }
}
```

**Error (404):**
```json
{
  "message": "Resource not found"
}
```

---

## 🔒 Security Checklist

- ✅ Sanctum API tokens
- ✅ CORS configured
- ✅ Input validation
- ✅ Password hashing
- ✅ Activity logging
- ⚠️ Role-based access (TODO)
- ⚠️ Rate limiting (TODO)
- ⚠️ API versioning (TODO)

---

## 📚 File Structure

```
app/Models/
├── Activity.php              ✅ Activity audit
├── Attendances.php           ✅ Attendance tracking
├── Clients.php               ✅ Client management
├── Companies.php             ✅ Company management
├── EmailTemplate.php         ✅ Email templates
├── Fcmregs.php              ✅ FCM device registration
├── Invoice_items.php        ✅ Invoice line items
├── Invoices.php             ✅ Invoice management
├── Lead_comments.php        ✅ Lead comments
├── LeadAssigns.php          ✅ Lead assignments
├── Leads.php                ✅ Lead management
├── Projects.php             ✅ Project management
├── Proposal_items.php       ✅ Proposal line items
├── Proposal_signatures.php  ✅ Digital signatures
├── Proposals.php            ✅ Proposal management
├── Roles.php                ✅ Role management
├── ScheduledEmail.php       ✅ Scheduled emails
├── Task_comments.php        ✅ Task comments
├── Task_working_hours.php   ✅ Time tracking
├── Task.php                 ✅ Task management
└── User.php                 ✅ User management

app/Http/Controllers/
├── ApiController.php        ✅ FCM & notifications
├── AuthController.php       ✅ Authentication
├── LeadApiController.php    ✅ Lead CRUD (NEW)
├── ClientController.php     (TODO)
├── InvoiceController.php    (TODO)
├── ProposalController.php   (TODO)
└── TaskController.php       (TODO)

routes/
└── api.php                  ✅ API routes updated

database/migrations/
├── 2024_01_22_000001 ─ Clients table
├── 2024_01_22_000002 ─ Invoices table
├── 2024_01_22_000003 ─ Invoice items table
├── 2024_01_22_000004 ─ Proposals table
├── 2024_01_22_000005 ─ Proposal items table
├── 2024_01_22_000006 ─ Email templates table
├── 2024_01_22_000007 ─ Lead assigns table
├── 2024_01_22_000008 ─ Lead comments table
├── 2024_01_22_000009 ─ Projects table
├── 2024_01_22_000010 ─ Attendances table
├── 2024_01_22_000011 ─ Roles table
├── 2024_01_22_000012 ─ Activities table
├── 2024_01_22_000013 ─ FCM registrations table
├── 2024_01_22_000014 ─ Scheduled emails table
├── 2024_01_22_000015 ─ Update users table
└── 2024_01_22_000016 ─ Update leads table
```

---

## 💡 Tips for Developers

1. **Always use type-hinted relationships** for IDE autocomplete
2. **Use query scopes** for common filters
3. **Eager load relations** to prevent N+1 queries
4. **Log all critical actions** via Activity model
5. **Validate input** on both frontend and backend
6. **Use decimal data type** for monetary values
7. **Always check auth()->id()** before creating records

---

## 📞 Support

For implementation details, check:
- Individual model documentation in comments
- API examples in controllers
- Route definitions in `/routes/api.php`
- Migration files for database schema

---

*Last Updated: January 22, 2026*
*Status: Phase 1 Complete - Core API Foundation Ready*

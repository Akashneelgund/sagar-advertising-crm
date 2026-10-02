# SAGAR ADVERTISING — Enterprise CRM & Quotation Management System

> **"Your Brand. Our Passion."**  
> Custom production-ready enterprise CRM, dynamic quotation engine, customer relationship management, and bulk marketing suite built specifically for **SAGAR ADVERTISING**, Hubballi, Karnataka.

---

## 1. Brand & Business Profile
- **Company**: SAGAR ADVERTISING
- **Proprietors**: Vageesh H Hugar (9611620862) & Sagar V Hugar (8904184867)
- **Headquarters**: #18096, "Shanti Kunj", Akkasaligar Oni, Old-Hubballi, HUBBALLI – 580 024
- **Official Email**: sagaradvertising7@gmail.com
- **GSTIN**: 29AVPH4223R1ZV
- **MSME Registration**: UDYAM-KR-13-0061429
- **Brand Palette**:
  - **Sagar Orange**: `#FF5500` / `#FF6B00` (CTAs, key KPI highlights, active navigation, accents)
  - **Obsidian Dark**: `#121417` / `#171A1F` (Sidebar, dark table headers, typography)
  - **Crisp White & Light Slate**: `#FFFFFF` / `#F4F6F9` (Cards, surfaces, tables)
  - **Brand Emblem**: Vector SA Monogram with dynamic curvature and typography.

---

## 2. Technology Stack & Architecture
- **Backend**: Pure, modular, object-oriented PHP 8.2+ architecture (No heavy framework overhead; 100% portable on XAMPP, Apache, Nginx, or cPanel shared hosting).
- **Database**: MySQL 8.0+ / MariaDB 10.4+ using strict PDO parameterized statements, transactions, foreign keys, and indexes.
- **Frontend**: HTML5, custom CSS design system (`brand.css`, `components.css`), Bootstrap 5, Chart.js for data visualization, Vanilla JavaScript AJAX & Fetch API.
- **PDF & Document Engine**: Print-optimized A4 letterhead invoice layout with real-time browser preview, downloadable and printable outputs.
- **Excel Engine**: Stream parser supporting `.csv`, `.xlsx`, `.xls` with multi-step column mapping and error logging.
- **Mail Engine**: Native SMTP engine with SSL/TLS support, auto-PDF attachment, campaign queue processor, and simulation mode.

---

## 3. Deployment & Live Hosting

### 🚀 Deploy to Render (1-Click)
Deploy the full stack (PHP 8.2 + Apache + MariaDB + Demo Data) directly to Render in one click:

[![Deploy to Render](https://render.com/images/deploy-to-render-button.svg)](https://render.com/deploy?repo=https://github.com/Akashneelgund/sagar-advertising-crm)

- **Runtime**: Docker (Apache + PHP 8.2 + MariaDB)
- **Zero Config**: On first launch, the container automatically initializes MariaDB and imports `DATABASE_SETUP.sql`.
- **Health Check**: `/login` (automatically verified by Render).

---

## 4. Local Quick Start & Installation

### Prerequisites
- PHP 8.1 or higher (PDO and cURL extensions enabled)
- MySQL 8.0+ or MariaDB 10.4+ (such as XAMPP)

### Step 1: Database Setup
Make sure MySQL is running, then run the migration and seeder script:
```bash
php database/migrate.php
```
*Alternatively, you can import `DATABASE_SETUP.sql` directly into phpMyAdmin or MySQL CLI:*
```bash
mysql -u root -p sagar_advertising_crm < DATABASE_SETUP.sql
```

### Step 2: Start Web Server
You can host the project in Apache/htdocs or run PHP's built-in web server directly from the project directory:
```bash
php -S localhost:8000
```
Open **`http://localhost:8000`** in your browser.

---

## 5. Default Login Credentials

| Role | Username / Email | Password | Permissions Scope |
| :--- | :--- | :--- | :--- |
| **Admin** | `admin` or `admin@sagaradvertising.com` | `Admin@123` | Full system access, settings, backups, employee management, financial margins |
| **Manager** | `manager` or `manager@sagaradvertising.com` | `Manager@123` | Quotations, customers, campaigns, reports, services catalog |
| **Employee** | `sales` or `sales@sagaradvertising.com` | `Sales@123` | Create and view assigned customers and quotations |

*(Quick-login buttons are also provided directly on the login screen for 1-click testing).*

---

## 6. Core Feature Modules

### 5.1 Dynamic Quotation Builder (`/quotations/builder`)
- **Split-Screen Interface**: Left-side editor pane and right-side live A4 document preview sheet that synchronizes instantly on every keystroke.
- **Multi-Row Line Items**: Add, duplicate, delete, and reorder services.
- **Real-Time Calculation Engine**:
  - Size/dimensions (e.g. `12 x 8 ft`), quantity, unit, actual base price, commission %.
  - **Mode 1 (Markup)**: $\text{Selling Price} = \text{Actual} + (\text{Actual} \times \text{Commission}\%)$.
  - **Mode 2 (Margin)**: $\text{Selling Price} = \frac{\text{Actual}}{1 - \text{Commission}\%}$.
  - Dynamically computes Subtotal, Discount, Taxable Amount, GST (18%), Transportation, Installation, and Round Off to Grand Total.
- **Automated Quotation Numbering**: e.g., `SA/QTN/2026/0001` configured with auto-incrementing prefix.

### 5.2 Branded A4 PDF & Direct Email Dispatch (`/quotations/view`)
- High-resolution, printable A4 quotation matching the Sagar Advertising corporate identity with header, vector logo, GSTIN, MSME, customer address, line items table, bank details, and signature box.
- One-click **"Send Quotation Email"** modal: Automatically generates the proposal document, attaches it, and populates template placeholders (`{{customer_name}}`, `{{quotation_number}}`, `{{grand_total}}`, `{{quotation_web_url}}`).
- Public web view link (`/quote/view/{token}`) tracks when the client opens the link and updates the quotation status to **Viewed**.

### 5.3 360° Customer Relationship Management (`/customers`)
- Complete customer profiles with customer codes (`SA-CUST-1001`), GSTIN, WhatsApp, type, source, and assigned sales executive.
- 360° Customer Overview displays:
  - Quotation history with statuses and total monetary value
  - Scheduled follow-ups (Calls, meetings, WhatsApp) with today/overdue indicators
  - Customer notes log
  - Email interaction history

### 5.4 Multi-Step Excel / CSV Import & Export (`/excel/import`)
- **Step 1**: Upload `.xlsx`, `.xls`, or `.csv` file.
- **Step 2**: Preview extracted headers and sample rows.
- **Step 3**: Interactive column mapping interface with smart auto-detection.
- **Step 4**: Validation against duplicate phone numbers, required fields, and email formats.
- **Step 5**: Comprehensive import summary (`Imported: 240, Skipped: 12, Errors: 8`) with downloadable error logs.
- Filtered data export for customers and quotations in CSV or Excel XML format.

### 5.5 Bulk Email Campaigns & Queue Manager (`/campaigns`)
- Festival wishes (Diwali, Ugadi, New Year), promotional offers, and customer appreciation notes.
- Rich HTML editor with personalization variables: `{{customer_name}}`, `{{company_name}}`, `{{city}}`, `{{phone}}`, `{{unsubscribe_url}}`.
- Queue-based rate-limited sending (chunks of 20-50) with start, pause, resume, and cancel controls.
- Automatic unsubscribe link generation and compliance checking (`marketing_opt_in`).

### 5.6 Executive Dashboard & Profit Analytics (`/dashboard` & `/reports/commission`)
- Real-time KPI counters: Total Quotation Value, Closed Sales, Active Clients, Due Follow-ups.
- Chart.js visual analytics: Monthly sales trends, quotation status distribution, top services revenue.
- **Quotation Profit / Commission Dashboard**: Shows Total Actual Cost, Total Commission, Total Selling Value, Average Margin %, Commission by Service, and Commission by Sales Representative.

### 5.7 Audit Trail & Database Backup (`/logs` & `/settings`)
- Activity log tracking user, module, record ID, IP address, and timestamp.
- Database backup manager: One-click export of SQL dumps with instant download.

---

## 6. Directory Structure
```
sagar-advertising-crm/
├── config/
│   ├── config.php              # App constants, Indian Rupee formatting, CSRF
│   ├── database.php            # PDO Singleton connection manager
│   └── permissions.php         # RBAC permission evaluation
├── database/
│   ├── schema.sql              # Normalized DDL schema tables
│   ├── migrate.php             # Automated migration & seeder runner
│   └── DATABASE_SETUP.sql      # Complete SQL database dump
├── backend/
│   ├── core/
│   │   ├── App.php             # Central route dispatcher
│   │   ├── Controller.php      # Base controller (JSON, CSRF, auth)
│   │   ├── Model.php           # Base PDO query builder
│   │   ├── Session.php         # Secure session wrapper & rate limiter
│   │   └── View.php            # Layout view renderer
│   ├── controllers/
│   │   ├── AuthController.php
│   │   ├── DashboardController.php
│   │   ├── CustomerController.php
│   │   ├── QuotationController.php
│   │   ├── ServiceController.php
│   │   ├── CampaignController.php
│   │   ├── ExcelController.php
│   │   ├── ReportController.php
│   │   ├── EmployeeController.php
│   │   ├── SettingsController.php
│   │   ├── PublicController.php
│   │   └── ApiController.php
│   └── services/
│       ├── QuotationCalculator.php # Commission math & GST engine
│       ├── PdfGenerator.php        # Printable A4 quotation generator
│       ├── ExcelHandler.php        # CSV / Excel import/export
│       ├── MailerService.php       # SMTP client & queue processor
│       └── AuditLogger.php         # Activity audit logger
├── views/
│   ├── layouts/
│   │   ├── header.php          # Dark sidebar & global search
│   │   └── footer.php          # Modals & script initialization
│   ├── auth/login.php          # Split-screen branded login
│   ├── dashboard/index.php     # Executive dashboard
│   ├── customers/              # Customer CRUD & 360 overview
│   ├── quotations/             # List & split-screen live builder
│   ├── services/index.php      # Services catalog
│   ├── campaigns/              # Campaign builder, queue, templates
│   ├── excel/                  # Import wizard & export center
│   ├── reports/                # Sales reports & profit dashboard
│   ├── employees/index.php     # RBAC & staff management
│   ├── settings/index.php      # Profile, SMTP, backups
│   └── logs/index.php          # Audit logs
├── assets/
│   ├── css/
│   │   ├── brand.css           # Sagar Advertising visual identity
│   │   └── components.css      # Tables, builder, timeline, wizard
│   ├── js/
│   │   ├── app.js              # AJAX, CSRF, global search dropdown
│   │   ├── quotation-builder.js # Dynamic line items & live preview
│   │   ├── excel-import.js     # Column mapper & validation
│   │   ├── campaign-queue.js   # Queue monitor & worker
│   │   └── charts.js           # Chart.js graphs
│   └── images/
│       ├── logo.svg            # Crisp vector SA / Sagar Advertising logo
│       └── brand-visual.svg    # Modern advertising illustration
├── tests/
│   ├── test_calculations.php   # Unit math & DB assertion tests
│   └── test_http_endpoints.php # End-to-end HTTP integration tests
├── storage/                    # PDFs, SQL backups, mail logs
├── uploads/                    # File uploads
├── index.php                   # Front controller
└── README.md
```

---

## 7. Verification & Testing Checklist

| Test Item | Verification Status | Command / Test Route |
| :--- | :--- | :--- |
| **Syntax & Linter** | 100% Passed (0 errors) | `php -l` on all PHP files |
| **Calculation Engine** | 100% Passed | `php tests/test_calculations.php` |
| **Database & Seeder** | 22 Clients, 16 Quotes, 11 Services | `php database/migrate.php` |
| **HTTP Endpoints** | 17/17 Endpoints Passed | `php tests/test_http_endpoints.php` |
| **Authentication & RBAC** | Verified | Tested Admin, Manager, Sales roles |
| **A4 PDF / Document** | Verified | Generates branded proposal with logo & GSTIN |
| **Real-time Live Sync** | Verified | Instant preview update on keystroke |

# Daily Reporting System

A dedicated hotel operations web application designed to streamline daily operational reporting, eliminate inconsistent pasted list prefixes (from email, WhatsApp, notes), provide a reliable 5-tab wizard, and produce pixel-perfect, editable Microsoft Word (`.docx`) and high-fidelity PDF (`.pdf`) documents.

## Technology Stack
- **Framework:** Laravel 13.x
- **PHP:** 8.3+ (Tested on PHP 8.3.30)
- **Frontend / Reactivity:** Livewire 4.x & Alpine.js
- **Styling:** Tailwind CSS 4.x (Deep teal `#0F766E` palette)
- **Database:** MySQL 8 / SQLite for local development & testing
- **Document Exporters:** PHPWord (`.docx`) and DomPDF (`.pdf`)

## Key Features & Capabilities
- **5-Step Report Wizard:**
  1. *General Information:* Structured inputs for Today's Overview and Forecast for Tomorrow (occupancy %, rooms, out-of-order room numbers & reasons, guest breakdown, arrivals/departures, property tours, MOD).
  2. *Guest Experience & Concern:* Unrestricted narrative textarea preserving raw paragraph formatting.
  3. *Departmental Activities:* Inputs for all 6 official departments (Front Office, Housekeeping, Food & Beverage Service, Food & Beverage Product, Engineering, Accounting) with automated list-prefix normalization and sentence capitalization.
  4. *Operational Updates:* Structured bullet list for property-wide operational changes.
  5. *Activities and Works Plan:* Forward-looking project and maintenance tasks.
- **Smart Paste Normalizer:** Client-side zero-click paste cleaner strips messy bullet markers (`• ● ○ ◦ ▪ ▫ ‣ ⁃ - *`), numbering, checkmarks, and emojis while capitalizing sentence starters and preserving embedded numbers.
- **Auto-Save & Tab Isolation:** Real-time debounced auto-save with visual indicator and beforeunload safeguards.
- **Multi-Property Security:** Strict hotel isolation policies ensuring users only view and edit reports from their assigned property, while Corporate Admins have global audit access.
- **Consolidated Preview & Multi-Format Export:** Document-fidelity web preview, instant `.docx` download with native Word bullet styles, and pixel-perfect `.pdf` generation.

## System Requirements & Prerequisites
- PHP 8.3 or higher (with `pdo`, `mbstring`, `openssl`, `xml`, `zip`, `gd`, `bcmath` extensions)
- Composer 2.4+
- Node.js 20+ and npm

## Setup & Local Installation

1. **Clone & Install Dependencies**
   ```bash
   composer install
   npm install
   ```

2. **Environment Configuration**
   ```bash
   copy .env.example .env
   php artisan key:generate
   ```

3. **Database Configuration**
   By default, local SQLite is configured (`database/database.sqlite`).
   To switch to MySQL:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=daily_report
   DB_USERNAME=root
   DB_PASSWORD=
   ```

4. **Run Migrations & Seeders**
   ```bash
   php artisan migrate:fresh --seed
   ```
   *Seeds the default hotel ("Komaneka at Bisma") and demo accounts.*

5. **Build Assets**
   ```bash
   npm run build
   # Or for development with hot reload:
   npm run dev
   ```

6. **Run Test Suite**
   ```bash
   php artisan test
   ```

## Demo Credentials
- **Admin Manager (Global Property Audit):**
  - Email: `admin@hotel.com`
  - Password: `password`
- **Report Officer (Property Assigned):**
  - Email: `staff@hotel.com`
  - Password: `password`

## Project Documentation & Operational Guides
Detailed specifications and production documentation are maintained in the [`plan/`](plan/) directory:
- [Implementation Tasks & Milestones](plan/TASKS.md)
- [PRD (Product Requirements Document)](plan/PRD.md)
- [Design Specification](plan/DESIGN.md)
- [Architecture](plan/ARCHITECTURE.md)
- [Normalization Rules](plan/docs/normalization-rules.md)
- [Official Report Template](plan/docs/report-template.md)
- [Report Wizard Workflow](plan/docs/report-wizard-workflow.md)
- [Production Environment Guide](plan/docs/production-environment-guide.md)
- [Database & File Storage Backup Guide](plan/docs/backup-guide.md)
- [Production Deployment Guide](plan/docs/deployment-guide.md)

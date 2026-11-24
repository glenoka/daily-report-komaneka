# Daily Reporting System

A dedicated hotel operations web application designed to streamline daily operational reporting, eliminate inconsistent pasted list prefixes (from email, WhatsApp, notes), provide a reliable 5-tab wizard, and produce pixel-perfect, editable Microsoft Word (`.docx`) and high-fidelity PDF (`.pdf`) documents.

## Technology Stack
- **Framework:** Laravel 13.x
- **PHP:** 8.3+ (Tested on PHP 8.3.30)
- **Frontend / Reactivity:** Livewire 4.x & Alpine.js
- **Styling:** Tailwind CSS 4.x (Deep teal `#0F766E` palette)
- **Database:** MySQL 8 / SQLite for local development & testing
- **Document Exporters:** PHPWord (`.docx`) and DomPDF (`.pdf`) (Milestone 6)

## System Requirements & Prerequisites
- PHP 8.3 or higher (with `pdo`, `mbstring`, `openssl`, `xml`, `zip` extensions)
- Composer 2.4+
- Node.js 18+ and npm

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
- **Admin Manager:**
  - Email: `admin@hotel.com`
  - Password: `password`
- **Report Officer:**
  - Email: `staff@hotel.com`
  - Password: `password`

## Project Documentation
Detailed specifications are maintained in the [`plan/`](plan/) directory:
- [PRD (Product Requirements Document)](plan/PRD.md)
- [Design Specification](plan/DESIGN.md)
- [Architecture](plan/ARCHITECTURE.md)
- [Normalization Rules](plan/docs/normalization-rules.md)
- [Official Report Template](plan/docs/report-template.md)
- [Report Wizard Workflow](plan/docs/report-wizard-workflow.md)
- [Implementation Tasks](plan/TASKS.md)

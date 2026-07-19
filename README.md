# Hamada Global Jaya - Fleet & Attendance Management System

![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-005C84?style=for-the-badge&logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-563D7C?style=for-the-badge&logo=bootstrap&logoColor=white)

A comprehensive, enterprise-grade backend system for managing logistics fleets and driver attendance. This system powers both the Web Administrative Dashboard and the Mobile App (Driver API) for PT Hamada Global Jaya.

## Key Features

*   **Driver Management & Attendance:** Secure mobile API for driver login, profile management, and daily attendance tracking. Includes Account Lockout protections.
*   **Preventive Fleet Maintenance:**
    *   Dynamic tracking of vehicle health (Odometer reading and Expiry dates).
    *   Automated calculation of component degradation.
*   **Smart Alerts & Scheduling:**
    *   Automatic generation of `OVERDUE`, `CRITICAL`, and `WARNING` alerts for components needing replacement.
    *   Auto-generation of Maintenance Schedules based on real-time vehicle metrics.
*   **Fleet Auditing & Certificates:**
    *   Digital Roadworthiness Certificates for healthy vehicles.
    *   QR Code verification system for real-time validity checks.
*   **Web Cron Integration:** Custom-built secure webhook endpoint for triggering scheduled tasks on Shared Hosting environments without CLI access.

## Technology Stack

*   **Framework:** Laravel 10.x
*   **Database:** MySQL
*   **Frontend UI:** Blade Templates + Bootstrap 5
*   **Authentication:** Laravel Sanctum (API) + Session (Web)
*   **Exports & Reports:** Maatwebsite Excel, PDF Generation

## ⚙️ Installation & Setup

1.  **Clone the repository:**
    ```bash
    git clone https://github.com/rohandev19/Absen-Driver.git
    cd absen_backend
    ```

2.  **Install dependencies:**
    ```bash
    composer install
    npm install && npm run build
    ```

3.  **Environment Setup:**
    Copy `.env.example` to `.env` and configure your database credentials.
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```
    
    *Crucial Security Step:* Set up your secret token for the Web Cron in your `.env`:
    ```env
    MAINTENANCE_URL_TOKEN=YourVerySecretTokenHere2026!
    ```

4.  **Database Migration & Seeding:**
    ```bash
    php artisan migrate --seed
    ```

5.  **Storage Link:**
    ```bash
    php artisan storage:link
    ```

## Background Jobs & Web Cron (Shared Hosting)

For environments where daemon queue workers or OS-level Cron jobs are not available (e.g., cPanel Shared Hosting), this system utilizes a secure Web Cron endpoint.

1.  Register an account on [cron-job.org](https://cron-job.org/) (or similar).
2.  Create a cron job that hits the following URL every 15 minutes:
    `https://yourdomain.com/api/cron/run-schedules?token=YourVerySecretTokenHere2026!`
3.  The backend will securely verify the token against the `.env` file (`MAINTENANCE_URL_TOKEN`) using timing-attack resistant hashes before executing `php artisan schedule:run`.

## Security Features

*   **API Rate Limiting:** Global IP throttling.
*   **Web Account Lockout:** Login attempts are rate-limited by `Email + IP`. 5 failed attempts result in a 5-minute lockout.
*   **Session Fixation Protection:** Session IDs are strictly regenerated upon successful authentication.
*   **Fail-Safe Web Cron:** The scheduler webhook fails closed. If `MAINTENANCE_URL_TOKEN` is empty or missing, access is completely blocked.

## CLI Commands Reference

For VPS or local development, you can run maintenance tasks manually:

*   `php artisan maintenance:generate-alerts` - Scans vehicles and generates component alerts.
*   `php artisan maintenance:generate-schedules` - Drafts maintenance schedules based on critical components.
*   `php artisan maintenance:check-sim` - Checks driver license expiry statuses.

---
*Developed for internal logistics operations. Proprietary & Confidential.*

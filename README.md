 # VoteSecure

 AI-Integrated Election System for secure, auditable voting (PHP + MySQL)

 ## Overview

 VoteSecure is a lightweight, self-hosted election/voting web application built with plain PHP and MySQL. It includes voter registration, ballot casting, results publication, basic audit logging, and optional AI analytics utilities (admin-facing).

 ## Features

 - Voter portal with ballot casting and viewing past votes
 - Admin dashboard for managing elections, candidates, and voters
 - Audit logging of key actions
 - Results publication and simple analytics
 - Demo utilities for resetting passwords and uploading avatars

 ## Tech stack

 - PHP 7.4+ (or compatible)
 - MySQL / MariaDB
 - Frontend: simple HTML/CSS/JS (no build step)
 - Designed to run on XAMPP / WAMP / any PHP+MySQL environment

 ## Prerequisites

 - PHP with PDO MySQL support
 - MySQL or MariaDB
 - Web server (Apache or built-in PHP server)

 ## Quick install (local / XAMPP)

 1. Clone or copy this repository into your web server document root (e.g. `c:/xampp/htdocs/votesecure`).
 2. Import the SQL schema located in the `sql/` folder into your database (use phpMyAdmin or `mysql` CLI). See `sql/` for schema and seed data.
 3. Configure the database connection in `config/database.php` (or `src/database.php` depending on your environment).
 4. Update `APP_URL` and `APP_VERSION` in your environment or config if needed. Ensure `public/uploads/avatars` is writable by the web server.
 5. Start Apache & MySQL (if using XAMPP). Open your browser at the configured `APP_URL` (for example: `http://localhost/votesecure`).

 ## Project structure (high level)

 - `views/` — UI templates for admin and voter portals
 - `src/` — PHP controllers, models and services
 - `public/` — Static assets (css, js, uploads)
 - `config/` — Database configuration
 - `sql/` — Database schema and seed scripts
 - `admin/`, `voter/` — Example entry points for the web UI

 ## Admin / Demo utilities

 - `scripts/reset_demo_passwords.php` — helper for demo environments

 ## Development notes

 - No build tooling required; edit PHP/HTML/CSS and reload.
 - Keep backups of the database before running scripts that modify data.

 ## Contributing

 Contributions, bug reports and improvements are welcome. Open issues or PRs describing changes and reasoning.

 ## License

 This repository does not include a license file. Add a `LICENSE` if you want to publish under an open-source license (MIT, Apache-2.0, etc.).

 ## Questions

 If you want, I can add a sample `.env` example, guidance for production deployment, or a simple Dockerfile—tell me which and I will add it.
>>>>>>> 4b4892c0a36933c726154fb629a76e5be16d9c40

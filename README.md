# CBT System

A production-oriented Computer Based Testing platform built with Laravel 12, Blade, Eloquent and a dark-green UI system.

## Included
- Role-based access: Admin, Teacher and Student
- Secure session authentication and registration
- Admin user management
- Exam creation, scheduling, publishing and deletion
- Question bank with single-choice questions and weighted scoring
- Timed examinations with server-side deadline enforcement and client countdown
- Automatic submission at timeout
- One recorded attempt per student/exam
- Detailed result review and teacher/admin result reporting
- Responsive, accessible Blade interface
- MySQL production configuration
- Database migrations and demo seeding

## Requirements
- PHP 8.2+
- Composer 2+
- MySQL 8+ or compatible MariaDB
- PHP extensions required by Laravel 12

## Setup
```bash
composer install
cp .env.example .env
php artisan key:generate
# configure DB_* in .env
php artisan migrate --seed
php artisan serve
```

Seed accounts use the password `ChangeMe123!` and should be changed immediately in a real deployment:
- admin@cbt.local — admin
- teacher@cbt.local — teacher
- student@cbt.local — student

## Production
Set `APP_ENV=production`, `APP_DEBUG=false`, configure a real database, point the web server document root to `public/`, and run:
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
```
Use HTTPS, secure environment secrets, database backups, log rotation and a process supervisor appropriate to your hosting platform.

## Architecture
`app/Http/Controllers` contains application workflows; `app/Models` contains domain persistence; `app/Http/Middleware/RoleMiddleware.php` enforces role boundaries; migrations define relational integrity; Blade views provide the UI.

## Security notes
- Passwords are hashed by Laravel's `hashed` model cast.
- Authentication sessions are regenerated on login and invalidated on logout.
- Forms use Laravel CSRF protection.
- Authorization is enforced in routes and ownership checks.
- Exam deadlines are checked on the server, not only by JavaScript.
- Submitted answers and scores are persisted transactionally.

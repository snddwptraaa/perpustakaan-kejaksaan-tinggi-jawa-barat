# Production Deployment

## Minimum services

- PHP 8.4 with `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, and `fileinfo`
- MySQL 8.0+ or MariaDB 10.6+
- Nginx or Apache with HTTPS
- Cron and a process supervisor when asynchronous queues are enabled
- Node.js is only required during build, not at runtime

## Release checklist

1. Back up the database and `storage/app/public` outside the web root; test a restore periodically.
2. Install with `composer install --no-dev --classmap-authoritative` and `npm ci && npm run build`.
3. Configure `.env`: `APP_ENV=production`, `APP_DEBUG=false`, HTTPS `APP_URL`, database, mail, cache, session, and `VISITOR_RETENTION_DAYS` based on the approved policy.
4. Run `php artisan migrate --force` and `php artisan storage:link`.
5. Run `php artisan optimize` and ensure `storage` plus `bootstrap/cache` are writable by the PHP user.
6. Configure cron: `* * * * * php /path/to/artisan schedule:run`.
7. Point the web server document root to `public/`; never expose the repository root or `.env`.
8. Check `/up` for process liveness and `/ready` for database/storage readiness.

## Rollback and backup

Use an atomic release directory or immutable image. Keep the previous application release and a database backup made before migration. File and database backups must be encrypted, access controlled, retained according to policy, and restored in a non-production environment as a regular drill.

The visitor pruning schedule is disabled when `VISITOR_RETENTION_DAYS=0`. Only enable it after the organization approves the retention duration and a backup/restore process exists.

## Accepted risks and policy decisions

- The Content Security Policy currently permits `unsafe-inline` and `unsafe-eval` for scripts because the installed Alpine/Livewire frontend relies on runtime-evaluated expressions and inline bootstrapping. Blade output remains escaped by default, but this CSP is defense-in-depth rather than a complete XSS boundary. Replacing it requires a tested nonce/hash migration for all framework-generated scripts.
- `VISITOR_RETENTION_DAYS=0` means visitor personal data is retained indefinitely. This is a policy default, not a recommended retention period. Production owners must approve a finite duration or explicitly accept indefinite retention, and record that decision outside the application repository.

At larger data volumes, review the substring searches on borrower and visitor names. Queries using `LIKE '%term%'` cannot use ordinary B-tree indexes efficiently; use an appropriate full-text/search strategy before these tables reach operationally significant size.

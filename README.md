# Leaderboard Web Application (Mid Level)

Laravel 12 (i decided to use 12 ver as of stablizing for now), Inertia.js, Vue 3 (used instead of react and i got access to use vue 3), Tailwind CSS, and MySQL admin application for managing users, point transactions, and cached leaderboard rankings.

## Features

- Admin login with middleware-protected routes.
- User CRUD with search, pagination, detail view, and point history.
- Point transaction CRUD for Earn and Deduct actions.
- Total points never go below zero.
- Updating or deleting transactions recalculates affected user totals.
- Leaderboard filters: Today, This Week, This Month, All Time.
- Shared rank ties using SQL `RANK()`.
- Dashboard with total users, total points, and top 10 users.
- CSV leaderboard export.
- Audit logs for user and point changes.
- Service layer for business logic.
- Form Request validation.
- Cached leaderboard queries with automatic invalidation.

## Requirements

- PHP 8.2+
- Composer
- Node.js 20.19+ or 22.12+
- MySQL 8+

The code uses SQL window functions for ranking, so use a database version that supports `RANK()`. 
(I researched that window functions via stackoverflow and tried to understand it.)

## Setup

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Configure your database in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=leaderboard
DB_USERNAME=root
DB_PASSWORD=
```

Run migrations and seed sample data:

```bash
php artisan migrate --seed
```

Seeded admin login:

```text
## admin acc
Email: admin@example.com
Password: password

## user acc (to test authorization)
Email: kyaw@example.com
Password: password
```

Run the app:

```bash
npm run dev
php artisan serve
```

Open `http://127.0.0.1:8000`.

## Architecture

Controllers are now clean. They validate input, call services, and return Inertia responses or redirects.

- `App\Services\UserService` handles user create, update, delete, audit logging, and leaderboard cache invalidation.
- `App\Services\PointTransactionService` owns all point balance rules and recalculates totals inside database transactions.
- `App\Services\LeaderboardService` builds efficient ranking queries, caches results, exports CSV rows, and clears leaderboard cache keys.
- `App\Services\AuditLogService` records admin actions.
- Form Requests keep validation outside controllers.
- `AdminMiddleware` protects all admin routes.

## Point Rules

Each point transaction stores:

- `user_id`
- `points`
- `action_type`: `Earn` or `Deduct`
- optional `description`
- `created_by`

For performance, `users.total_points` stores the all-time balance. Every point mutation locks the affected user row, calculates the signed delta, prevents negative totals, applies the change, records an audit log, and clears leaderboard cache.

Update and delete behavior:

- Update reverses the old transaction effect and applies the new effect.
- If the transaction moves to another user, both users are locked and recalculated safely.
- Delete reverses the transaction effect.

## Leaderboard Ranking

All Time uses `users.total_points`.

Today, This Week, and This Month use the net points from transactions created in that period:

```sql
SUM(CASE WHEN action_type = 'Earn' THEN points ELSE -points END)
```

Ranks are calculated with SQL `RANK() OVER (ORDER BY points DESC)`, so ties share the same rank:

```text
1 Kyaw Kyaw   120
2 Su Su Hlaing     100
2 Khine Khine 100
4 Zaw Zaw    90
```

## Caching Strategy

Leaderboard pages and top-user dashboard data are cached for 10 minutes through Laravel Cache.

The service stores generated leaderboard cache keys in a small cache index. Whenever a user or point transaction changes, `LeaderboardService::clearCache()` forgets those keys. This keeps reads fast while ensuring point changes immediately refresh leaderboard results.


## Performance Notes

- Leaderboard ranking is calculated in SQL, not in PHP collections.
- User totals are denormalized into `users.total_points` for fast all-time ranking.
- Point transaction queries eager-load users and creators to avoid N+1 queries.
- Indexes are added for `users.total_points`, `users.is_admin`, `point_transactions.user_id`, `point_transactions.created_at`.
- Lists are paginated.

## Useful Commands

```bash
php artisan migrate:fresh --seed
npm run build
./vendor/bin/pint
```

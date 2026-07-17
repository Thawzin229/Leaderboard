<?php

namespace App\Services;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class LeaderboardService
{
    private const CACHE_INDEX = 'leaderboard_cache_keys';

    private const CACHE_SECONDS = 600;

    public function getLeaders(string $filter = 'all', int $perPage = 15)
    {
        $page = max(1, (int) request('page', 1));
        $key = $this->cacheKey($filter, $perPage, $page);

        $this->rememberKey($key);

        return Cache::remember($key, self::CACHE_SECONDS, function () use ($filter, $perPage) {
            return $this->rankedQuery($filter)->paginate($perPage)->withQueryString();
        });
    }

    public function getTopUsers(string $filter = 'all', int $limit = 10)
    {
        $key = "leaderboard.top.{$filter}.{$limit}";
        $this->rememberKey($key);

        return Cache::remember($key, self::CACHE_SECONDS, function () use ($filter, $limit) {
            return $this->rankedQuery($filter)->limit($limit)->get()->map(fn ($item) => [
                'rank' => (int) $item->rank,
                'id' => (int) $item->id,
                'name' => $item->name,
                'email' => $item->email,
                'points' => (int) $item->points,
            ])->all();
        });
    }

    public function clearCache(): void
    {
        foreach (Cache::get(self::CACHE_INDEX, []) as $key) {
            Cache::forget($key);
        }

        Cache::forget(self::CACHE_INDEX);
    }

    private function rankedQuery(string $filter)
    {
        $subquery = $filter === 'all'
            ? $this->allTimeScores()
            : $this->periodScores($filter);

        return DB::query()
            ->fromSub($subquery, 'scores')
            ->select('scores.*')
            ->selectRaw('RANK() OVER (ORDER BY points DESC) as rank')
            ->orderByDesc('points')
            ->orderBy('name');
    }

    private function allTimeScores()
    {
        return DB::table('users')
            ->select('id', 'name', 'email')
            ->where('is_admin', false)
            ->selectRaw('total_points as points');
    }

    private function periodScores(string $filter)
    {
        [$start, $end] = $this->dateRange($filter);

        return DB::table('users')
            ->leftJoin('point_transactions', function ($join) use ($start, $end) {
                $join->on('users.id', '=', 'point_transactions.user_id')
                    ->whereBetween('point_transactions.created_at', [$start, $end]);
            })
            ->select('users.id', 'users.name', 'users.email')
            ->where('users.is_admin', false)
            ->selectRaw(
            "COALESCE(
                    SUM(
                        CASE WHEN point_transactions.action_type = 'Earn' THEN point_transactions.points ELSE -point_transactions.points END), 0) 
                        as points"
            )
            ->groupBy('users.id', 'users.name', 'users.email');
    }

    private function dateRange(string $filter)
    {
        $now = CarbonImmutable::now();

        return match ($filter) {
            'today' => [$now->startOfDay(), $now->endOfDay()],
            'week' => [$now->startOfWeek(), $now->endOfWeek()],
            'month' => [$now->startOfMonth(), $now->endOfMonth()],
            default => [$now->startOfCentury(), $now->endOfCentury()],
        };
    }

    private function cacheKey(string $filter, int $perPage, int $page): string
    {
        return "leaderboard.{$filter}.{$perPage}.{$page}";
    }

    private function rememberKey(string $key)
    {
        $keys = Cache::get(self::CACHE_INDEX, []);

        if (! in_array($key, $keys, true)) {
            $keys[] = $key;
            Cache::forever(self::CACHE_INDEX, $keys);
        }
    }
}

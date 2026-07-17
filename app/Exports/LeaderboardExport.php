<?php

namespace App\Exports;

use App\Services\LeaderboardService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class LeaderboardExport implements FromCollection, WithMapping, WithHeadings, ShouldAutoSize
{
    protected string $filter;
    protected LeaderboardService $service;

    public function __construct(string $filter = 'all')
    {
        $this->filter = $filter;
        $this->service = app(LeaderboardService::class);
    }

    public function collection()
    {
        $query = $this->getRankedQuery();
        $results = $query->get();
        return collect($results);
    }

    private function getRankedQuery()
    {
        $subquery = $this->filter === 'all'
            ? $this->allTimeScores()
            : $this->periodScores();

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
            ->selectRaw('total_points as points');
    }

    private function periodScores()
    {
        [$start, $end] = $this->getDateRange();

        return DB::table('users')
            ->leftJoin('point_transactions', function ($join) use ($start, $end) {
                $join->on('users.id', '=', 'point_transactions.user_id')
                    ->whereBetween('point_transactions.created_at', [$start, $end]);
            })
            ->select('users.id', 'users.name', 'users.email')
            ->selectRaw(
                "COALESCE(
                    SUM(
                        CASE 
                            WHEN point_transactions.action_type = 'Earn' 
                            THEN point_transactions.points 
                            ELSE -point_transactions.points 
                        END
                    ), 
                    0
                ) as points"
            )
            ->groupBy('users.id', 'users.name', 'users.email');
    }

    /**
     * Define the CSV headers
     */
    public function headings(): array
    {
        return [
            'Rank',
            'User Name',
            'Email',
            'Total Points',
        ];
    }

    public function map($row): array
    {
        return [
            (int) $row->rank,
            $row->name,
            $row->email,
            (int) ($row->points ?? 0),
        ];
    }

    private function getDateRange(): array
    {
        $now = now();
        
        return match ($this->filter) {
            'today' => [$now->startOfDay()->toDateTimeString(), $now->endOfDay()->toDateTimeString()],
            'week' => [$now->startOfWeek()->toDateTimeString(), $now->endOfWeek()->toDateTimeString()],
            'month' => [$now->startOfMonth()->toDateTimeString(), $now->endOfMonth()->toDateTimeString()],
            'year' => [$now->startOfYear()->toDateTimeString(), $now->endOfYear()->toDateTimeString()],
            default => [$now->startOfCentury()->toDateTimeString(), $now->endOfCentury()->toDateTimeString()],
        };
    }
}
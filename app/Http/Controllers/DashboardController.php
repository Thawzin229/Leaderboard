<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\LeaderboardService;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __invoke(LeaderboardService $leaderboardService)
    {
        return Inertia::render('Dashboard', [
            'stats' => [
                'totalUsers' => User::count(),
                'totalPoints' => (int) User::sum('total_points'),
            ],
            'topUsers' => $leaderboardService->getTopUsers('all', 10),
        ]);
    }
}

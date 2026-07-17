<?php

namespace App\Http\Controllers;

use App\Exports\LeaderboardExport;
use App\Http\Requests\LeaderboardRequest;
use App\Services\LeaderboardService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class LeaderboardController extends Controller
{
    private  $LeaderboardService;
    public function __construct(LeaderboardService $LeaderboardService)
    {
        $this->LeaderboardService = $LeaderboardService;
    }

    public function index(LeaderboardRequest $request)
    {
        $filter = $request->filter();

        return Inertia::render('Leaderboard/Index', [
            'leaderboard' => $this->LeaderboardService->getLeaders($filter),
            'filters' => ['filter' => $filter],
        ]);
    }

    
    public function export(Request $request)
    {
        if (!auth()->check()) {
            abort(401, 'You must be logged in to export data.');
        }
        
        if (!auth()->user()->is_admin) {
            abort(403, 'You do not have permission to export data.');
        }
        
        $filter = $request->input('filter', 'all');

        $allowedFilters = ['all', 'today', 'week', 'month', 'year'];

        if (!in_array($filter, $allowedFilters)) {
            $filter = 'all';
        }

        $fileName = "leaderboard_" . date('Y-m-d-H-i-s') . ".csv";

        return Excel::download(
            new LeaderboardExport($filter),
            $fileName,
            \Maatwebsite\Excel\Excel::CSV
        );
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class ApiDataCenterController extends Controller
{
    public function getUsers(Request $request)
    {
        $search = $request->input('search');
        $limit = $request->input('limit', 10);
        $page = $request->input('page', 1);
        $selectedIds = $request->input('selected_ids', []);

        $query = User::query();

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }


    if (!empty($selectedIds) && $page == 1) {
        $selectedItems = User::whereIn('id', $selectedIds)
            ->select('id', 'name','total_points')
            // ->where('is_admin', false)
            ->orderBy('total_points', 'desc')
            ->get();


        $restQuery = clone $query;
        if (!empty($selectedIds)) {
            $restQuery->select('id','name','total_points')
            // ->where('is_admin', false)
            ->whereNotIn('id', $selectedIds)
            ->orderBy('total_points', 'desc');
        }

        
        $restUsers = $restQuery->latest()
            ->paginate($limit, ['*'], 'page', $page);


        $allItems = $selectedItems->concat($restUsers->items());


        return response()->json([
            'data' => $allItems,
            'selected_ids' => $selectedIds,
            'next_page_url' => $restUsers->nextPageUrl(),
            'prev_page_url' => $restUsers->previousPageUrl(),
            'current_page' => $restUsers->currentPage(),
            'last_page' => $restUsers->lastPage(),
            'total' => $restUsers->total() + $selectedItems->count(),
        ]);
    }

         $users = $query->select('id', 'name','total_points')
                        // ->where('is_admin', false)
                        ->orderBy('total_points', 'desc')
                        ->latest()
                        ->paginate($limit, ['*'], 'page', $page);

        return response()->json([
                'data' => $users->items(),
                'next_page_url' => $users->nextPageUrl(),
                'prev_page_url' => $users->previousPageUrl(),
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'total' => $users->total(),
        ]);
    }
}

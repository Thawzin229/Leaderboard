<?php

namespace App\Http\Middleware;

use App\Models\PointTransaction;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function share(Request $request): array
    {
        $user = $request->user();
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user()?->only('id', 'name', 'email', 'is_admin'),
            ],
            # i passd the policy check to the frontend so that i can use it to show or hide certain features based on the user's permissions through inertia middleware.
            'can' => [
            'manage_users' => $user ? $user->can('viewAny', User::class) : false,
            'create_user' => $user ? $user->can('create', User::class) : false,
            'edit_user' => $user ? $user->can('update', $user) : false,
            'delete_user' => $user ? $user->can('delete', $user) : false,
            'manage_transactions' => $user ? $user->can('viewAny', PointTransaction::class) : false,
            'create_transaction' => $user ? $user->can('create', PointTransaction::class) : false,
            'edit_transaction' => $user ? $user->can('update', new PointTransaction()) : false,
            'delete_transaction' => $user ? $user->can('delete', new PointTransaction()) : false,
            'view_leaderboard' => true,
            'export_data' => $user ? $user->can('export', PointTransaction::class) : false,
        ],
            # flash messages are used to display info and error messages in /Layout/AppLayout.vue
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ];
    }
}

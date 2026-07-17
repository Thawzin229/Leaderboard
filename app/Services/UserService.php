<?php

namespace App\Services;

use App\Exceptions\PointBalanceException;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserService
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
        private readonly LeaderboardService $leaderboardService,
    ) {

    }

    public function getUsers(?string $search = null, int $perPage = 10)
    {
        try{
            Log::info('Getting users: ' . $search);

            return User::query()
                ->withCount('pointTransactions')
                ->when($search, function ($query, string $search) {
                    $query->where(function ($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
                })
                ->orderBy('name')
                ->paginate($perPage)
                ->withQueryString();

        }catch (\Exception $e) {
            Log::error('Error getting users: ' . $e->getMessage());
        }
    }

    public function create(array $data): User
    {
        try {
        Log::info('Creating user: ' . $data['email']);
        
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'is_admin' => (bool) ($data['is_admin'] ?? false),
        ]);
        // i store the audit log and also clear the leaderboard cache after the user is created
        $this->auditLogService->record('user.created', $user);
        $this->leaderboardService->clearCache();

        return $user;

        } catch (\Exception $e) {
            Log::error('Error creating user: ' . $e->getMessage());
        }
    }

    public function update(User $user, array $data)
    {
        try {
            Log::info('Updating user: ' . $user->email);
            
        $user->fill([
            'name' => $data['name'],
            'email' => $data['email'],
            'is_admin' => (bool) ($data['is_admin'] ?? false),
        ]);

        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->update();

        $this->auditLogService->record('user.updated', $user);
        $this->leaderboardService->clearCache();

        return $user;

        } catch (\Exception $e) {
            Log::error('Error updating user: ' . $e->getMessage());
        }
    }

    public function delete(User $user): void
    {
        try {
            Log::info('Deleting user: ' . $user->email);
            
        if (auth()->id() === $user->id) {
            throw new PointBalanceException('You cannot delete your own admin account.');
        }

        DB::transaction(function () use ($user) {
            $this->auditLogService->record('user.deleted', $user, [
                'email' => $user->email,
                'total_points' => $user->total_points,
            ]);

            $user->delete();
        });

        $this->leaderboardService->clearCache();
        } catch (\Exception $e) {
            Log::error('Error deleting user: ' . $e->getMessage());
        }
    }
}

<?php

namespace App\Services;

use App\Exceptions\PointBalanceException;
use App\Models\PointTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PointTransactionService
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
        private readonly LeaderboardService $leaderboardService,
    ) {
    }

    public function getTransactions(array $filters = [], int $perPage = 10)
    {
        try {
            Log::info('Getting point transactions: ' . json_encode($filters));

            return PointTransaction::query()
                ->with(['user:id,name,email', 'creator:id,name'])
                ->Search($filters['search'] ?? null)
                ->WithActionType($filters['action_type'] ?? null)
                ->WithUserId($filters['user_id'] ?? null)
                ->latest()
                ->paginate($perPage)
                ->withQueryString();

        } catch (\Exception $e) {
            Log::error('Error getting point transactions: ' . $e->getMessage());
        }
    }

    public function create(array $data)
    {
            Log::info('Creating point transaction: ' . json_encode($data));

            return DB::transaction(function () use ($data) {
                $user = User::query()->findOrFail($data['user_id']);
                $signedPoints = $this->signedPoints($data['action_type'], (int) $data['points']);

                $this->IsBalanceValid($user, $signedPoints);

                $transaction = PointTransaction::create([
                    'user_id' => $user->id,
                    'created_by' => auth()->id(),
                    'points' => $data['points'],
                    'action_type' => $data['action_type'],
                    'description' => $data['description'] ?? null,
                ]);

                $user->increment('total_points', $signedPoints);

                $this->auditLogService->record('points.created', $transaction, [
                    'signed_points' => $signedPoints,
                ]);
                $this->leaderboardService->clearCache();

                return $transaction->load('user');
            });
    }

    public function update(PointTransaction $transaction, array $data)
    {       
            Log::info('Updating point transaction: ' . json_encode($data));

                //**** SUDO CODE (based on my understanding of the point transaction logic) ****//

                    ## earn state |  total - old (need to remove)  + new
                    ## [ the old value should be removed first before adding the new value]

                    ## deduct state | total +  ( old - new ) [might output of  - or + val] 
                    ## [ this state always giving negaigve when the total value is insufficient to deduct ]

                    ## from deduct to earn | total + ( old + new)
                    ## [ taking the old value and adding the new value to the total points]

                    ## from earn to deduct  | total - ( old + new )
                    ## [ taking the old value and new value subtracting from the total points]

            return DB::transaction(function () use ($transaction, $data) {
                $transaction = PointTransaction::query()->findOrFail($transaction->id);
                $oldUser = User::query()->findOrFail($transaction->user_id);
                $newUserId = (int) $data['user_id'];
                $newUser = $newUserId === $oldUser->id
                    ? $oldUser
                    : User::query()->lockForUpdate()->findOrFail($newUserId);

                $oldSignedPoints = $transaction->signedPoints();
                $newSignedPoints = $this->signedPoints($data['action_type'], (int) $data['points']);

                $this->IsBalanceValid($oldUser, -$oldSignedPoints);
                if ($newUser->is($oldUser)) {
                    $this->IsBalanceValid($oldUser, -$oldSignedPoints + $newSignedPoints);
                    $oldUser->increment('total_points', -$oldSignedPoints + $newSignedPoints);
                } else {
                    $this->IsBalanceValid($newUser, $newSignedPoints);
                    // we need to remove the old point from the old user
                    $oldUser->increment('total_points', -$oldSignedPoints);
                    $newUser->increment('total_points', $newSignedPoints);
                }

                $transaction->update([
                    'user_id' => $newUser->id,
                    'points' => $data['points'],
                    'action_type' => $data['action_type'],
                    'description' => $data['description'] ?? null,
                ]);

                $this->auditLogService->record('points.updated', $transaction, [
                    'old_signed_points' => $oldSignedPoints,
                    'new_signed_points' => $newSignedPoints,
                ]);
                $this->leaderboardService->clearCache();

                return $transaction->load('user');
            });
    }

    public function delete(PointTransaction $transaction): void
    {
            Log::info('Deleting point transaction: ' . json_encode($transaction));

            DB::transaction(function () use ($transaction) {
                $transaction = PointTransaction::query()->lockForUpdate()->findOrFail($transaction->id);
                $user = User::query()->findOrFail($transaction->user_id);
                $signedPoints = $transaction->signedPoints();

                $this->IsBalanceValid($user, -$signedPoints);
                $user->increment('total_points', -$signedPoints);

                $this->auditLogService->record('points.deleted', $transaction, [
                    'signed_points' => $signedPoints,
                ]);

                $transaction->delete();
            });

            $this->leaderboardService->clearCache();
    }

    private function signedPoints(string $actionType, int $points): int
    {
        return $actionType === PointTransaction::EARN ? $points : -$points;
    }

    private function IsBalanceValid(User $user, int $point)
    {
        if ($user->total_points + $point < 0) {
            throw new PointBalanceException('Total points must never become negative.');
        }
    }
}

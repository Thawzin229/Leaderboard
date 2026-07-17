<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PointTransaction extends Model
{
    use HasFactory;

    public const EARN = 'Earn';

    public const DEDUCT = 'Deduct';

    protected $fillable = [
        'user_id',
        'created_by',
        'points',
        'action_type',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'points' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function signedPoints(): int
    {
        return $this->action_type === self::EARN ? $this->points : -$this->points;
    }

    // scope functions 
    public function scopeSearch($query, ?string $search)
    {
        if (empty($search)) {
            return $query;
        }

        return $query->where(function ($query) use ($search) {
            $query->where('description', 'like', "%{$search}%")
                ->orWhereHas('user', function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
        });
    }

    public function scopeWithActionType($query, ?string $type)
    {
        if (empty($type)) {
            return $query;
        }

        return $query->where('action_type', $type);
    }

    public function scopeWithUserId($query, ?int $userId)
    {
        if (empty($userId)) {
            return $query;
        }

        return $query->where('user_id', $userId);
    }

}

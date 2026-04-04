<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'url',
        'is_read',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Send notification to specific user IDs.
     * Skips null IDs and the currently authenticated user (no self-notifications).
     */
    public static function send(array $userIds, string $type, string $title, string $message, ?string $url = null): void
    {
        $currentId = auth()->id();
        $targets   = array_unique(array_filter($userIds, fn($id) => $id && $id !== $currentId));

        foreach ($targets as $userId) {
            static::create([
                'user_id' => $userId,
                'type'    => $type,
                'title'   => $title,
                'message' => $message,
                'url'     => $url,
            ]);
        }
    }

    /**
     * Send to all users with canApprove() = true (SPV + Super Admin).
     */
    public static function sendToApprovers(string $type, string $title, string $message, ?string $url = null): void
    {
        $ids = User::whereIn('role', ['SPV', 'Super Admin'])->pluck('id')->toArray();
        static::send($ids, $type, $title, $message, $url);
    }

    /**
     * Send to Super Admin only.
     */
    public static function sendToSuperAdmin(string $type, string $title, string $message, ?string $url = null): void
    {
        $ids = User::where('role', 'Super Admin')->pluck('id')->toArray();
        static::send($ids, $type, $title, $message, $url);
    }
}

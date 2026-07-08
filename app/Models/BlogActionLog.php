<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Blog;
use Illuminate\Support\Facades\Schema;

class BlogActionLog extends Model
{
    /**
     * Some environments use a prefixed table name (e.g. `dp_blog_action_logs`).
     * Resolve at runtime to avoid hard failures when the other name is present.
     */
    protected $table = 'dp_blog_action_logs';
    private static $resolvedTableName = null;

    public function getTable()
    {
        if (self::$resolvedTableName !== null) {
            return self::$resolvedTableName;
        }

        try {
            if (Schema::hasTable('dp_blog_action_logs')) {
                self::$resolvedTableName = 'dp_blog_action_logs';
            } elseif (Schema::hasTable('blog_action_logs')) {
                self::$resolvedTableName = 'blog_action_logs';
            } else {
                // Default (will error later, but keeps behavior consistent)
                self::$resolvedTableName = $this->table;
            }
        } catch (\Throwable $e) {
            self::$resolvedTableName = $this->table;
        }

        return self::$resolvedTableName;
    }

    protected $fillable = [
        'blog_id',
        'user_id',
        'action',
        'meta',
        'ip',
        'user_agent',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function blog()
    {
        return $this->belongsTo(Blog::class, 'blog_id');
    }

    public static function record(string $action, ?int $blogId = null, ?int $userId = null, array $meta = []): void
    {
        try {
            if ($userId === null && Auth::check()) {
                $userId = (int) Auth::id();
            }

            $req = null;
            try {
                $req = request();
            } catch (\Throwable $e) {
                $req = null;
            }

            $ip = null;
            $ua = null;
            if ($req) {
                $ip = method_exists($req, 'ip') ? $req->ip() : null;
                $ua = method_exists($req, 'userAgent') ? $req->userAgent() : null;
            }

            static::create([
                'blog_id' => $blogId,
                'user_id' => $userId,
                'action' => $action,
                'meta' => empty($meta) ? null : $meta,
                'ip' => $ip,
                'user_agent' => $ua,
            ]);
        } catch (\Throwable $e) {
            // Never break the user flow due to audit logging.
            try {
                \Log::warning('BlogActionLog record failed: ' . $e->getMessage());
            } catch (\Throwable $ignored) {
            }
        }
    }
}

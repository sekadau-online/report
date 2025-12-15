<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ShareLink extends Model
{
    /** @use HasFactory<\Database\Factories\ShareLinkFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'token',
        'name',
        'password',
        'is_active',
        'expires_at',
        'view_count',
        'last_viewed_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'expires_at' => 'datetime',
            'last_viewed_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (ShareLink $shareLink) {
            if (empty($shareLink->token)) {
                $shareLink->token = static::generateUniqueToken();
            }
        });
    }

    /**
     * Generate a unique token.
     */
    public static function generateUniqueToken(): string
    {
        do {
            $token = Str::random(32);
        } while (static::where('token', $token)->exists());

        return $token;
    }

    /**
     * Get the user that owns the share link.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to active share links.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to valid (not expired) share links.
     */
    public function scopeValid(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
                ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Check if the share link requires a password.
     */
    public function requiresPassword(): bool
    {
        return ! is_null($this->password);
    }

    /**
     * Check if the provided password is correct.
     */
    public function checkPassword(string $password): bool
    {
        if (! $this->requiresPassword()) {
            return true;
        }

        return Hash::check($password, $this->password);
    }

    /**
     * Check if the share link is valid (active and not expired).
     */
    public function isValid(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Check if the share link has expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Increment view count and update last viewed timestamp.
     */
    public function recordView(): void
    {
        $this->increment('view_count');
        $this->update(['last_viewed_at' => now()]);
    }

    /**
     * Get the full share URL.
     */
    public function getShareUrl(): string
    {
        return route('share.view', $this->token);
    }

    /**
     * Get QR code as SVG string.
     */
    public function getQrCodeSvg(int $size = 200): string
    {
        return app(\App\Services\QrCodeService::class)->generateSvg($this->getShareUrl(), $size);
    }

    /**
     * Get QR code as data URI for img src.
     */
    public function getQrCodeDataUri(int $size = 200): string
    {
        return app(\App\Services\QrCodeService::class)->generateDataUri($this->getShareUrl(), $size);
    }
}

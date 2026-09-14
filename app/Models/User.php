<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'bio',
        'is_active',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // ─────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────

    /** Articles authored by this user. */
    public function articles(): HasMany
    {
        return $this->hasMany(Article::class, 'author_id');
    }

    /** Articles approved by this user (admin/editor). */
    public function approvedArticles(): HasMany
    {
        return $this->hasMany(Article::class, 'approved_by');
    }

    /** Gallery images uploaded by this user. */
    public function galleries(): HasMany
    {
        return $this->hasMany(Gallery::class, 'uploaded_by');
    }

    // ─────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────

    /**
     * Returns the avatar URL or a UI-Avatars generated fallback.
     */
    public function avatarUrl(): string
    {
        if ($this->avatar) {
            return asset('storage/'.$this->avatar);
        }

        return 'https://ui-avatars.com/api/?name='.urlencode($this->name).'&background=6366f1&color=fff';
    }

    /**
     * Check whether this user holds the Administrator role.
     */
    public function isAdministrator(): bool
    {
        return $this->hasRole('Administrator');
    }

    /**
     * Check whether this user holds the Admin role.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('Admin');
    }
}

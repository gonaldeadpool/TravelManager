<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'menu_permissions', 'dashboard_widgets', 'avatar_path'])]
#[Hidden(['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public function avatarUrl(): ?string
    {
        if (! $this->avatar_path) {
            return null;
        }

        return route('avatar.show', ['user' => $this->id, 'v' => substr(md5($this->avatar_path), 0, 8)]);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function canAccessMenu(string $menu): bool
    {
        return $this->isAdmin() || in_array($menu, $this->menu_permissions ?? [], true);
    }

    public function hasTwoFactorEnabled(): bool
    {
        return ! is_null($this->two_factor_secret) && ! is_null($this->two_factor_confirmed_at);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'menu_permissions' => 'array',
            'dashboard_widgets' => 'array',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }
}

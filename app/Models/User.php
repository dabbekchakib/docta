<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\Role;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser, HasName
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

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
        ];
    }

    /**
     * Determine if the user is allowed to access the given panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->canAccessAdminPanel();
    }

    /**
     * Determine if the user is allowed to access the DOCTA admin panel.
     */
    public function canAccessAdminPanel(): bool
    {
        return $this->hasAnyRole(
            collect(Role::cases())
                ->filter(fn (Role $role) => $role->canAccessPanel())
                ->map(fn (Role $role) => $role->value)
                ->all()
        );
    }

    /**
     * The display name used inside Filament panels.
     */
    public function getFilamentName(): string
    {
        return $this->name;
    }

    /**
     * Determine if the user is an administrator (super_admin or admin).
     */
    public function isAdmin(): bool
    {
        return $this->hasAnyRole([Role::SuperAdmin->value, Role::Admin->value]);
    }
}

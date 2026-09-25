<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'avatar',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public const ROLE_SUPER_ADMIN = 'super_admin';
    public const ROLE_ADMIN = 'admin';
    public const ROLE_USER = 'user';

    /** Foto del usuario, si la ha subido. */
    public function getAvatarUrlAttribute(): ?string
    {
        return $this->avatar ? \Illuminate\Support\Facades\Storage::disk('public')->url($this->avatar) : null;
    }

    /** Iniciales para cuando no hay foto: "Ana Pérez" → "AP". */
    public function getInicialesAttribute(): string
    {
        $partes = preg_split('/\s+/', trim((string) $this->name)) ?: [];
        $iniciales = mb_strtoupper(mb_substr($partes[0] ?? 'U', 0, 1));

        if (! empty($partes[1])) {
            $iniciales .= mb_strtoupper(mb_substr($partes[1], 0, 1));
        }

        return $iniciales ?: 'U';
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_SUPER_ADMIN], true);
    }

    /**
     * Un admin gestiona usuarios normales y admins; solo un super_admin
     * puede gestionar (editar/borrar) a otro super_admin.
     */
    public function puedeGestionarUsuario(User $objetivo): bool
    {
        return $this->isSuperAdmin() || ! $objetivo->isSuperAdmin();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Usuario extends Authenticatable implements FilamentUser, HasName
{
    use HasFactory;

    protected $table = 'usuarios';

    protected $primaryKey = 'id_usu';

    protected $fillable = [
        'nombre_usu',
        'correo_usu',
        'password_usu',
        'estado_usu',
        'id_rol_1',
    ];

    protected $hidden = [
        'password_usu',
    ];

    protected function casts(): array
    {
        return [
            'password_usu' => 'hashed',
            'estado_usu' => 'boolean',
        ];
    }

    public function getAuthPassword(): string
    {
        return $this->password_usu;
    }

    /**
     * Determina si el usuario puede acceder al panel de Filament.
     * Un usuario debe estar activo para poder ingresar.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->estado_usu;
    }

    /**
     * Superadmin.
     * id_rol = 3
     */
    public function isSuperAdmin(): bool
    {
        return (int) $this->id_rol_1 === 3;
    }

    /**
     * Administrador.
     * id_rol = 1
     */
    public function isAdmin(): bool
    {
        return (int) $this->id_rol_1 === 1;
    }

    /**
     * Registrador.
     * id_rol = 2
     */
    public function isRegistrador(): bool
    {
        return (int) $this->id_rol_1 === 2;
    }

    /**
     * Determina si el usuario puede acceder
     * al módulo de gestión de usuarios.
     *
     * Superadmin y Administrador:
     *     SI
     *
     * Registrador:
     *     NO
     */
    public function canManageUsers(): bool
    {
        return $this->isSuperAdmin() || $this->isAdmin();
    }

    /**
     * Nombre que mostrará Filament para el usuario.
     */
    public function getFilamentName(): string
    {
        return $this->nombre_usu;
    }

    /**
     * Relación con el rol.
     */
    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'id_rol_1', 'id_rol');
    }

    /**
     * Relación con certificados.
     */
    public function certificados(): HasMany
    {
        return $this->hasMany(Certificado::class, 'id_usu_1', 'id_usu');
    }
}
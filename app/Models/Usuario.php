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

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->estado_usu;
    }

    public function getFilamentName(): string
    {
        return $this->nombre_usu;
    }

    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'id_rol_1', 'id_rol');
    }

    public function certificados(): HasMany
    {
        return $this->hasMany(Certificado::class, 'id_usu_1', 'id_usu');
    }
}

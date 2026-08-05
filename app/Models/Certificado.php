<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Certificado extends Model
{
    use HasFactory;

    protected $table = 'certificados';

    protected $primaryKey = 'id_cer';

    protected $fillable = [
        'codigo_cer',
        'nombre_per_cer',
        'apellido_per_cer',
        'carnet_per_cer',
        'docente_cer',
        'curso_cer',
        'fecha_cer',
        'estado_cer',
        'id_usu_1',
    ];

    protected function casts(): array
    {
        return [
            'fecha_cer' => 'date',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usu_1', 'id_usu');
    }

    public function nombreCompleto(): string
    {
        return trim("{$this->nombre_per_cer} {$this->apellido_per_cer}");
    }
}

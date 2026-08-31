<?php

namespace Database\Seeders;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

class UsuarioSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $rolAdmin = Rol::where('nom_rol', 'Administrador')->first();

        if (! $rolAdmin) {
            return; // Corre RolSeeder primero.
        }

        Usuario::firstOrCreate(
            ['correo_usu' => 'admin@certificadoqr.test'],
            [
                'nombre_usu' => 'Administrador General',
                'password_usu' => 'password', // se hashea solo por el cast 'hashed' del modelo
                'estado_usu' => true,
                'id_rol_1' => $rolAdmin->id_rol,
            ]
        );
    }
}

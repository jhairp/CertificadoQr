<?php

namespace Database\Seeders;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsuarioSuperadminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /*
         * ==============================================================
         * 1. VERIFICAR Y CREAR EL ROL SUPERADMINISTRADOR (id = 3)
         * ==============================================================
         */
        $rolSuperadmin = Rol::where('id_rol', 3)->first();

        if (! $rolSuperadmin) {
            // Crear el rol Superadministrador
            Rol::create([
                'id_rol' => 3,
                'nom_rol' => 'Superadministrador',
                // Si tienes más campos, agrégalos aquí
            ]);

            $this->command->info('✅ Rol Superadministrador creado (id: 3)');
        } else {
            $this->command->info('ℹ️ El rol Superadministrador ya existe (id: 3)');
        }

        /*
         * ==============================================================
         * 2. VERIFICAR Y ASIGNAR EL ROL SUPERADMIN AL USUARIO
         * ==============================================================
         */
        $email = 'admin@certificadoqr.test';
        $usuario = Usuario::where('correo_usu', $email)->first();

        if ($usuario) {
            // Actualizar el rol del usuario a Superadministrador (3)
            $usuario->id_rol_1 = 3;
            $usuario->save();

            $this->command->info("✅ Usuario '{$email}' actualizado a Superadministrador (id_rol: 3)");
        } else {
            $this->command->warn("⚠️ Usuario con correo '{$email}' no encontrado.");
            $this->command->warn("   Crea el usuario primero o verifica el correo.");
        }

        /*
         * ==============================================================
         * 3. RESULTADO FINAL
         * ==============================================================
         */
        $this->command->info('✅ Seeder ejecutado correctamente');
    }
}
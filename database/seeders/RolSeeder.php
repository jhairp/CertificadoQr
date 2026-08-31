<?php

namespace Database\Seeders;

use App\Models\Rol;
use Illuminate\Database\Seeder;

class RolSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        foreach (['Administrador', 'Registrador'] as $nombre) {
            Rol::firstOrCreate(['nom_rol' => $nombre]);
        }
    }
}

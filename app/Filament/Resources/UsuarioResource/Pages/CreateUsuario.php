<?php

namespace App\Filament\Resources\UsuarioResource\Pages;

use App\Filament\Resources\UsuarioResource;
use App\Models\Usuario;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateUsuario extends CreateRecord
{
    protected static string $resource = UsuarioResource::class;

    /**
     * Protege los datos antes de crear el usuario.
     *
     * Reglas:
     *
     * SUPERADMIN:
     * - Puede crear Administradores.
     * - Puede crear Registradores.
     * - NO puede crear otro Superadmin.
     *
     * ADMIN:
     * - Solamente puede crear Registradores.
     *
     * REGISTRADOR:
     * - No puede acceder a esta página.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();

        if (! $user) {
            abort(403);
        }

        /*
         * ==============================================================
         * SUPERADMIN
         * ==============================================================
         * Puede crear:
         * 1 = Administrador
         * 2 = Registrador
         * NO puede crear:
         * 3 = Superadmin
         */
        if ($user->isSuperAdmin()) {
            if (! in_array((int) $data['id_rol_1'], [1, 2], true)) {
                Notification::make()
                    ->danger()
                    ->title('Acción no permitida')
                    ->body('No se pueden crear nuevos usuarios con el rol Superadministrador.')
                    ->send();
                abort(403);
            }
            return $data;
        }

        /*
         * ==============================================================
         * ADMIN
         * ==============================================================
         * Un Administrador solamente puede crear Registradores.
         */
        if ($user->isAdmin()) {
            // Forzamos el rol a Registrador incluso si alguien intenta manipular la petición
            $data['id_rol_1'] = 2;
            return $data;
        }

        /*
         * ==============================================================
         * REGISTRADOR
         * ==============================================================
         * Un Registrador no puede crear usuarios.
         */
        if ($user->isRegistrador()) {
            abort(403);
        }

        /*
         * Cualquier otro caso también queda bloqueado.
         */
        abort(403);
    }
}
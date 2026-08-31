<?php

namespace App\Filament\Resources\UsuarioResource\Pages;

use App\Filament\Resources\UsuarioResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateUsuario extends CreateRecord
{
    protected static string $resource = UsuarioResource::class;

    /**
     * Deshabilita el botón "Crear y crear otro".
     */
    protected static bool $canCreateAnother = false;

    /**
     * Después de crear correctamente el usuario,
     * regresar automáticamente al listado de usuarios.
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * Protege los datos antes de crear el usuario.
     *
     * Reglas:
     *
     * SUPERADMIN:
     * - Puede crear Administradores.
     * - Puede crear Registradores.
     * - No puede crear otro Superadmin.
     *
     * ADMIN:
     * - Solamente puede crear Registradores.
     *
     * REGISTRADOR:
     * - No puede crear usuarios.
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
         */
        if ($user->isAdmin()) {
            // Un administrador solamente puede crear Registradores.
            $data['id_rol_1'] = 2;

            return $data;
        }

        /*
         * ==============================================================
         * REGISTRADOR
         * ==============================================================
         */
        if ($user->isRegistrador()) {
            abort(403);
        }

        /*
         * Cualquier otro caso queda bloqueado.
         */
        abort(403);
    }
}
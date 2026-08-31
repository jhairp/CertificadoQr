<?php

namespace App\Filament\Resources\UsuarioResource\Pages;

use App\Filament\Resources\UsuarioResource;
use App\Models\Usuario;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditUsuario extends EditRecord
{
    protected static string $resource = UsuarioResource::class;

    /**
     * Después de guardar correctamente los cambios,
     * regresar automáticamente al listado de usuarios.
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * Protege los datos antes de guardar los cambios.
     *
     * La matriz de permisos se valida nuevamente en el servidor,
     * independientemente de lo que muestre el formulario.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        /** @var Usuario|null $user */
        $user = auth()->user();

        if (! $user) {
            abort(403);
        }

        /** @var Usuario $record */
        $record = $this->record;

        /*
         * ==============================================================
         * SUPERADMIN
         * ==============================================================
         */
        if ($user->isSuperAdmin()) {

            // Superadmin editándose a sí mismo.
            if ($record->id_usu === $user->id_usu) {
                $data['id_rol_1'] = 3;

                return $data;
            }

            // Superadmin editando Administrador o Registrador.
            if ($record->isAdmin() || $record->isRegistrador()) {
                // Mantener el rol original.
                $data['id_rol_1'] = $record->id_rol_1;

                return $data;
            }

            abort(403);
        }

        /*
         * ==============================================================
         * ADMIN
         * ==============================================================
         */
        if ($user->isAdmin()) {

            // Administrador editándose a sí mismo.
            if ($record->id_usu === $user->id_usu) {
                $data['id_rol_1'] = 1;

                return $data;
            }

            // Administrador editando Registrador.
            if ($record->isRegistrador()) {
                $data['id_rol_1'] = 2;

                return $data;
            }

            Notification::make()
                ->danger()
                ->title('Acción no permitida')
                ->body('No tienes permisos para editar a este usuario.')
                ->send();

            abort(403);
        }

        /*
         * ==============================================================
         * REGISTRADOR
         * ==============================================================
         */
        if ($user->isRegistrador()) {

            // Segunda capa de seguridad.
            if ($record->id_usu === $user->id_usu) {
                $data['id_rol_1'] = 2;

                return $data;
            }

            abort(403);
        }

        /*
         * Cualquier otro caso queda bloqueado.
         */
        abort(403);
    }

    /**
     * No mostrar acciones adicionales en la cabecera.
     *
     * La eliminación física de usuarios no está permitida.
     * Las bajas se realizan mediante el estado activo/inactivo.
     */
    protected function getHeaderActions(): array
    {
        return [];
    }
}
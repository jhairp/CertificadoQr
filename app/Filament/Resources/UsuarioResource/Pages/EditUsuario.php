<?php

namespace App\Filament\Resources\UsuarioResource\Pages;

use App\Filament\Resources\UsuarioResource;
use App\Models\Usuario;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditUsuario extends EditRecord
{
    protected static string $resource = UsuarioResource::class;

    /**
     * Protege los datos antes de guardar los cambios.
     * Aquí se vuelve a validar la matriz de permisos,
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
            // Superadmin editándose a sí mismo
            if ($record->id_usu === $user->id_usu) {
                $data['id_rol_1'] = 3;
                return $data;
            }

            // Superadmin editando Admin o Registrador
            if ($record->isAdmin() || $record->isRegistrador()) {
                // Mantenemos el rol original
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
            // Admin editándose a sí mismo
            if ($record->id_usu === $user->id_usu) {
                $data['id_rol_1'] = 1;
                return $data;
            }

            // Admin editando Registrador
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
         * El Registrador no puede acceder a este módulo.
         * Esta validación queda como una segunda capa de seguridad.
         */
        if ($user->isRegistrador()) {
            // Si por alguna razón llegara a esta página, solamente podría editarse a sí mismo
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
     * Acciones disponibles en la parte superior de la página de edición.
     */
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                /*
                 * Utilizamos exactamente la misma lógica
                 * centralizada del UsuarioResource.
                 */
                ->visible(
                    fn (): bool =>
                        UsuarioResource::canDelete($this->record)
                ),
        ];
    }
}
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UsuarioResource\Pages;
use App\Models\Rol;
use App\Models\Usuario;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UsuarioResource extends Resource
{
    protected static ?string $model = Usuario::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Usuarios';

    protected static ?string $modelLabel = 'usuario';

    protected static ?string $pluralModelLabel = 'usuarios';

    /*
    |--------------------------------------------------------------------------
    | PERMISOS GENERALES DEL MÓDULO
    |--------------------------------------------------------------------------
    */

    /**
     * Superadmin y Administrador pueden ver el módulo.
     * Registrador NO puede verlo.
     */
    public static function canViewAny(): bool
    {
        return auth()->user()?->canManageUsers() ?? false;
    }

    /**
     * Superadmin y Administrador pueden crear usuarios.
     * La selección de roles se controla posteriormente en el formulario.
     */
    public static function canCreate(): bool
    {
        $user = auth()->user();
        if (! $user) return false;
        
        // Superadmin y Admin pueden crear (pero Admin solo registradores)
        return $user->isSuperAdmin() || $user->isAdmin();
    }

    /**
     * Determina quién puede editar a cada usuario.
     *
     * SUPERADMIN:
     * - Puede editar Administradores.
     * - Puede editar Registradores.
     * - Puede editarse a sí mismo.
     *
     * ADMIN:
     * - Puede editar Registradores.
     * - Puede editarse a sí mismo.
     * - NO puede editar otros Administradores.
     * - NO puede editar Superadmin.
     *
     * REGISTRADOR:
     * - Solo puede editarse a sí mismo.
     * - Pero NO tiene acceso a este módulo.
     */
    public static function canEdit($record): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        /*
         * Nadie puede editar un Superadmin desde este módulo,
         * excepto el propio Superadmin.
         */
        if ($record->isSuperAdmin()) {
            return $user->isSuperAdmin()
                && $record->id_usu === $user->id_usu;
        }

        /*
         * Superadmin puede editar Administradores
         * y Registradores.
         */
        if ($user->isSuperAdmin()) {
            return $record->isAdmin()
                || $record->isRegistrador();
        }

        /*
         * Administrador puede:
         * - editarse a sí mismo
         * - editar Registradores
         */
        if ($user->isAdmin()) {
            return $record->id_usu === $user->id_usu
                || $record->isRegistrador();
        }

        /*
         * Registrador.
         * Esta condición queda como protección adicional,
         * aunque el Registrador no puede visualizar el módulo.
         */
        if ($user->isRegistrador()) {
            return $record->id_usu === $user->id_usu;
        }

        return false;
    }

    /**
     * Determina quién puede eliminar a cada usuario.
     *
     * SUPERADMIN:
     * - Puede eliminar Administradores.
     * - Puede eliminar Registradores.
     * - NO puede eliminarse a sí mismo.
     * - NO puede eliminar otro Superadmin.
     *
     * ADMIN:
     * - Puede eliminar Registradores.
     * - NO puede eliminar Administradores.
     * - NO puede eliminar Superadmin.
     * - NO puede eliminarse a sí mismo.
     *
     * REGISTRADOR:
     * - No puede eliminar usuarios.
     */
    public static function canDelete($record): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        /*
         * Nadie puede eliminarse a sí mismo.
         */
        if ($record->id_usu === $user->id_usu) {
            return false;
        }

        /*
         * Un Superadmin no puede ser eliminado.
         */
        if ($record->isSuperAdmin()) {
            return false;
        }

        /*
         * Superadmin puede eliminar:
         * - Administradores
         * - Registradores
         */
        if ($user->isSuperAdmin()) {
            return $record->isAdmin()
                || $record->isRegistrador();
        }

        /*
         * Administrador solamente puede eliminar
         * Registradores.
         */
        if ($user->isAdmin()) {
            return $record->isRegistrador();
        }

        /*
         * Registrador no puede eliminar usuarios.
         */
        return false;
    }

    /**
     * Sobrescribimos el query para filtrar qué usuarios se muestran en la lista
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if (!$user) {
            return $query;
        }

        if ($user->isAdmin()) {
            // El admin solo ve registradores
            return $query->where('id_rol_1', 2);
        }

        // Superadmin ve todos los usuarios
        return $query;
    }

    /*
    |--------------------------------------------------------------------------
    | FORMULARIO
    |--------------------------------------------------------------------------
    */

    public static function form(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Datos del usuario')
                ->schema([

                    TextInput::make('nombre_usu')
                        ->label('Nombre completo')
                        ->required()
                        ->maxLength(100),

                    TextInput::make('correo_usu')
                        ->label('Correo electrónico')
                        ->email()
                        ->required()
                        ->maxLength(150)
                        ->unique(ignoreRecord: true),

                Select::make('id_rol_1')
                    ->label('Rol')
                    ->options(function (?Usuario $record): array {
                        $user = auth()->user();

                        if (! $user) {
                            return [];
                        }

                        /*
                        * SUPERADMIN
                        * Puede gestionar los roles de Administrador y Registrador.
                        * No mostramos Superadmin como opción para crear nuevos usuarios.
                        */
                        if ($user->isSuperAdmin()) {
                            $roles = [
                                1 => 'Administrador',
                                2 => 'Registrador',
                            ];

                            /*
                            * Si estamos editando al propio Superadmin,
                            * mostramos su rol actual para conservarlo.
                            */
                            if ($record?->id_usu === $user->id_usu && (int) $record->id_rol_1 === 3) {
                                $roles[3] = 'Superadministrador';
                            }

                            return $roles;
                        }

                        /*
                        * ADMIN
                        * Solo puede trabajar con Registradores.
                        */
                        if ($user->isAdmin()) {
                            return [
                                2 => 'Registrador',
                            ];
                        }

                        /*
                        * Registrador no debería llegar aquí,
                        * pero devolvemos vacío como protección.
                        */
                        return [];
                    })
                    ->disabled(function (?Usuario $record): bool {
                        $user = auth()->user();

                        if (! $user) {
                            return true;
                        }

                        /*
                        * El Superadmin no puede cambiar su propio rol.
                        */
                        if ($user->isSuperAdmin() && $record?->id_usu === $user->id_usu) {
                            return true;
                        }

                        /*
                        * EL ADMIN AHORA PUEDE VER EL CAMPO PERO SOLO CON LA OPCIÓN REGISTRADOR
                        * Por eso ya no está deshabilitado
                        */
                        // ELIMINAMOS ESTA LÍNEA:
                        // if ($user->isAdmin()) {
                        //     return true;
                        // }

                        return false;
                    })
                    ->required()
                    ->native(false),

                ])
                ->columns(3),

            Section::make('Acceso')
                ->schema([

                    TextInput::make('password_usu')
                        ->label('Contraseña')
                        ->password()
                        ->revealable()
                        ->minLength(8)
                        ->required(
                            fn (string $operation): bool =>
                                $operation === 'create'
                        )
                        ->dehydrated(
                            fn (?string $state): bool =>
                                filled($state)
                        )
                        ->confirmed()
                        ->helperText(
                            fn (string $operation): ?string =>
                                $operation === 'edit'
                                    ? 'Deja el campo vacío para conservar la contraseña actual.'
                                    : null
                        ),

                    TextInput::make('password_usu_confirmation')
                        ->label('Confirmar contraseña')
                        ->password()
                        ->revealable()
                        ->required(
                            fn (string $operation): bool =>
                                $operation === 'create'
                        )
                        ->dehydrated(false),

                    Toggle::make('estado_usu')
                        ->label('Usuario activo')
                        ->default(true)
                        ->helperText(
                            'Los usuarios inactivos no pueden acceder al panel.'
                        ),

                ])
                ->columns(2),

        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | TABLA
    |--------------------------------------------------------------------------
    */

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')

            ->columns([

                TextColumn::make('nombre_usu')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('correo_usu')
                    ->label('Correo')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('rol.nom_rol')
                    ->label('Rol')
                    ->badge()
                    ->sortable(),

                IconColumn::make('estado_usu')
                    ->label('Activo')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Registrado')
                    ->date('d/m/Y')
                    ->sortable(),

            ])

            ->filters([

                SelectFilter::make('id_rol_1')
                    ->label('Rol')
                    ->relationship('rol', 'nom_rol'),

                TernaryFilter::make('estado_usu')
                    ->label('Estado')
                    ->trueLabel('Activos')
                    ->falseLabel('Inactivos')
                    ->native(false),

            ])

            ->recordActions([

                /*
                 * ==========================================================
                 * ACTIVAR / DESACTIVAR
                 * ==========================================================
                 */

                Action::make('toggleEstado')
                    ->label(
                        fn (Usuario $record): string =>
                            $record->estado_usu
                                ? 'Desactivar'
                                : 'Activar'
                    )
                    ->icon(
                        fn (Usuario $record): string =>
                            $record->estado_usu
                                ? 'heroicon-o-x-circle'
                                : 'heroicon-o-check-circle'
                    )
                    ->color(
                        fn (Usuario $record): string =>
                            $record->estado_usu
                                ? 'danger'
                                : 'success'
                    )
                    ->requiresConfirmation()
                    ->visible(function (Usuario $record): bool {
                        $user = auth()->user();

                        if (! $user) {
                            return false;
                        }

                        /*
                         * Nadie puede desactivarse a sí mismo.
                         */
                        if ($record->id_usu === $user->id_usu) {
                            return false;
                        }

                        /*
                         * Superadmin no puede ser deshabilitado.
                         */
                        if ($record->isSuperAdmin()) {
                            return false;
                        }

                        /*
                         * Superadmin puede activar/desactivar
                         * Administradores y Registradores.
                         */
                        if ($user->isSuperAdmin()) {
                            return $record->isAdmin()
                                || $record->isRegistrador();
                        }

                        /*
                         * Admin solamente puede activar/desactivar
                         * Registradores.
                         */
                        if ($user->isAdmin()) {
                            return $record->isRegistrador();
                        }

                        /*
                         * Registrador no puede hacerlo.
                         */
                        return false;
                    })
                    ->action(function (Usuario $record): void {
                        $user = auth()->user();

                        if (! $user) {
                            return;
                        }

                        /*
                         * Nadie puede desactivarse a sí mismo.
                         */
                        if ($record->id_usu === $user->id_usu) {
                            Notification::make()
                                ->danger()
                                ->title('Acción no permitida')
                                ->body('No puedes desactivar tu propio usuario.')
                                ->send();
                            return;
                        }

                        /*
                         * El Superadmin no puede ser deshabilitado.
                         */
                        if ($record->isSuperAdmin()) {
                            Notification::make()
                                ->danger()
                                ->title('Acción no permitida')
                                ->body('El Superadmin no puede ser deshabilitado.')
                                ->send();
                            return;
                        }

                        /*
                         * ADMIN - Solo puede modificar Registradores.
                         */
                        if ($user->isAdmin() && ! $record->isRegistrador()) {
                            Notification::make()
                                ->danger()
                                ->title('Acción no permitida')
                                ->body('Los administradores solamente pueden activar o desactivar registradores.')
                                ->send();
                            return;
                        }

                        /*
                         * REGISTRADOR
                         */
                        if ($user->isRegistrador()) {
                            Notification::make()
                                ->danger()
                                ->title('Acción no permitida')
                                ->body('Los registradores no pueden activar ni desactivar usuarios.')
                                ->send();
                            return;
                        }

                        /*
                         * Cambio de estado.
                         */
                        $record->estado_usu = ! $record->estado_usu;
                        $record->save();

                        Notification::make()
                            ->success()
                            ->title(
                                $record->estado_usu
                                    ? 'Usuario activado'
                                    : 'Usuario desactivado'
                            )
                            ->send();
                    }),

                /*
                 * ==========================================================
                 * EDITAR
                 * ==========================================================
                 */

                EditAction::make()
                    ->visible(function (Usuario $record): bool {
                        return self::canEdit($record);
                    }),

                /*
                 * ==========================================================
                 * ELIMINAR
                 * ==========================================================
                 */

                DeleteAction::make()
                    ->visible(
                        fn (Usuario $record): bool =>
                            self::canDelete($record)
                    ),

            ]);
    }

    /*
    |--------------------------------------------------------------------------
    | PÁGINAS
    |--------------------------------------------------------------------------
    */

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsuarios::route('/'),
            'create' => Pages\CreateUsuario::route('/create'),
            'edit' => Pages\EditUsuario::route('/{record}/edit'),
        ];
    }
}
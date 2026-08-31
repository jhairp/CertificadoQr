<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UsuarioResource\Pages;
use App\Models\Usuario;
use Filament\Actions\Action;
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
     */
    public static function canCreate(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return $user->isSuperAdmin() || $user->isAdmin();
    }

    /**
     * Determina quién puede editar a cada usuario.
     */
    public static function canEdit($record): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        /*
         * Nadie puede editar un Superadmin,
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
         * Protección adicional.
         */
        if ($user->isRegistrador()) {
            return $record->id_usu === $user->id_usu;
        }

        return false;
    }

    /**
     * Filtra los usuarios que aparecen en el listado.
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if (! $user) {
            return $query;
        }

        /*
         * ADMIN:
         * Puede ver Registradores y a sí mismo.
         */
        if ($user->isAdmin()) {
            return $query->where(function (Builder $subQuery) use ($user) {
                $subQuery->where('id_rol_1', 2)
                    ->orWhere('id_usu', $user->id_usu);
            });
        }

        /*
         * SUPERADMIN:
         * Puede ver todos.
         */
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
                             * ==================================================
                             * SUPERADMIN
                             * ==================================================
                             *
                             * Puede gestionar:
                             * 1 = Administrador
                             * 2 = Registrador
                             *
                             * Si se está editando a sí mismo:
                             * 3 = Superadministrador
                             */
                            if ($user->isSuperAdmin()) {

                                $roles = [
                                    1 => 'Administrador',
                                    2 => 'Registrador',
                                ];

                                /*
                                 * Si el Superadmin se está editando
                                 * a sí mismo, mostramos su rol.
                                 */
                                if (
                                    $record?->id_usu === $user->id_usu
                                    && (int) $record->id_rol_1 === 3
                                ) {
                                    $roles[3] = 'Superadministrador';
                                }

                                return $roles;
                            }

                            /*
                             * ==================================================
                             * ADMINISTRADOR
                             * ==================================================
                             *
                             * Si el administrador se está editando
                             * a sí mismo, debe aparecer:
                             *
                             * 1 = Administrador
                             *
                             * Si está editando un Registrador:
                             *
                             * 2 = Registrador
                             */
                            if ($user->isAdmin()) {

                                /*
                                 * ADMIN EDITÁNDOSE A SÍ MISMO
                                 */
                                if (
                                    $record?->id_usu === $user->id_usu
                                    && (int) $record->id_rol_1 === 1
                                ) {
                                    return [
                                        1 => 'Administrador',
                                    ];
                                }

                                /*
                                 * ADMIN EDITANDO REGISTRADOR
                                 */
                                return [
                                    2 => 'Registrador',
                                ];
                            }

                            /*
                             * ==================================================
                             * REGISTRADOR
                             * ==================================================
                             */
                            return [];
                        })
                        ->disabled(function (?Usuario $record): bool {
                            $user = auth()->user();

                            if (! $user) {
                                return true;
                            }

                            /*
                             * SUPERADMIN EDITÁNDOSE A SÍ MISMO
                             *
                             * No puede cambiar su propio rol.
                             */
                            if (
                                $user->isSuperAdmin()
                                && $record?->id_usu === $user->id_usu
                            ) {
                                return true;
                            }

                            /*
                             * ADMINISTRADOR EDITÁNDOSE A SÍ MISMO
                             *
                             * Su rol debe permanecer como Administrador.
                             */
                            if (
                                $user->isAdmin()
                                && $record?->id_usu === $user->id_usu
                            ) {
                                return true;
                            }

                            /*
                             * ADMIN editando Registrador:
                             * puede visualizar Registrador,
                             * pero no cambiarlo a otro rol.
                             */
                            if (
                                $user->isAdmin()
                                && $record?->isRegistrador()
                            ) {
                                return true;
                            }

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
                         * ADMIN:
                         * Solo puede modificar Registradores.
                         */
                        if (
                            $user->isAdmin()
                            && ! $record->isRegistrador()
                        ) {
                            Notification::make()
                                ->danger()
                                ->title('Acción no permitida')
                                ->body(
                                    'Los administradores solamente pueden activar o desactivar registradores.'
                                )
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
                                ->body(
                                    'Los registradores no pueden activar ni desactivar usuarios.'
                                )
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
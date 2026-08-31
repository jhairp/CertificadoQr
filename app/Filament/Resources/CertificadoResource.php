<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CertificadoResource\Pages;
use App\Models\Certificado;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class CertificadoResource extends Resource
{
    protected static ?string $model = Certificado::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationLabel = 'Certificados';
    protected static ?string $modelLabel = 'certificado';
    protected static ?string $pluralModelLabel = 'certificados';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Datos del participante')->schema([
                TextInput::make('nombre_per_cer')
                    ->label('Nombres')
                    ->required()
                    ->maxLength(100)
                    ->extraInputAttributes(['style' => 'text-transform: uppercase;'])
                    ->dehydrateStateUsing(fn ($state) => mb_strtoupper($state, 'UTF-8')),
                
                TextInput::make('apellido_per_cer')
                    ->label('Apellidos')
                    ->required()
                    ->maxLength(100)
                    ->extraInputAttributes(['style' => 'text-transform: uppercase;'])
                    ->dehydrateStateUsing(fn ($state) => mb_strtoupper($state, 'UTF-8')),
                
                TextInput::make('carnet_per_cer')
                    ->label('Carnet / documento')
                    ->required()
                    ->maxLength(20)
                    ->extraInputAttributes(['style' => 'text-transform: uppercase;'])
                    ->dehydrateStateUsing(fn ($state) => mb_strtoupper($state, 'UTF-8')),
            ])->columns(3),

            Section::make('Datos del certificado')->schema([
                Select::make('curso_cer')
                    ->label('Curso')
                    ->options(config('courses.options'))
                    ->required(),
                
                TextInput::make('docente_cer')
                    ->label('Docente')
                    ->required()
                    ->maxLength(150)
                    ->extraInputAttributes(['style' => 'text-transform: uppercase;'])
                    ->dehydrateStateUsing(fn ($state) => mb_strtoupper($state, 'UTF-8')),
                
                Select::make('fecha_cer')
                    ->label('Gestión (Sustituye Fecha)')
                    ->options(function () {
                        $yearActual = (int) date('Y');
                        $gestiones = [];
                        for ($year = $yearActual + 1; $year >= $yearActual - 3; $year--) {
                            $gestiones["$year-07-01"] = "II - $year";
                            $gestiones["$year-01-01"] = "I - $year";
                        }
                        return $gestiones;
                    })
                    ->required(),
                
                Select::make('estado_cer')->label('Estado')->options([
                    'activo' => 'Activo',
                    'anulado' => 'Anulado',
                ])->default('activo')->required(),
                
                Hidden::make('id_usu_1')->default(fn (): int => auth()->user()->id_usu),
                Hidden::make('codigo_cer')->default(fn (): string => 'CERT-' . now()->format('Y') . '-' . Str::upper(Str::random(10))),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('codigo_cer')->label('Código')->searchable()->copyable(),
                TextColumn::make('nombre_per_cer')->label('Participante')->formatStateUsing(fn (Certificado $record): string => $record->nombreCompleto())->searchable(['nombre_per_cer', 'apellido_per_cer']),
                
                // NUEVO: Agregado Docente a la tabla principal
                TextColumn::make('docente_cer')->label('Docente')->searchable(),
                
                TextColumn::make('curso_cer')->label('Curso')->searchable(),
                TextColumn::make('fecha_cer')
                    ->label('Gestión')
                    ->formatStateUsing(function ($state) {
                        if (!$state) return 'N/A';
                        $mes = $state->format('m');
                        $anio = $state->format('Y');
                        return ($mes <= 6 ? 'I' : 'II') . ' - ' . $anio;
                    })
                    ->sortable(),
                
                TextColumn::make('estado_cer')->label('Estado')->badge()->color(fn (string $state): string => $state === 'activo' ? 'success' : 'danger'),
            ])
            ->filters([
                SelectFilter::make('estado_cer')->label('Estado')->options(['activo' => 'Activo', 'anulado' => 'Anulado']),
                SelectFilter::make('curso_cer')->label('Curso')->options(config('courses.options')),
            ])
            ->recordActions([
                Action::make('verificar')->label('Verificar')->icon('heroicon-o-qr-code')->url(fn (Certificado $record): string => route('certificados.verificar', $record->codigo_cer))->openUrlInNewTab(),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCertificados::route('/'),
            'create' => Pages\CreateCertificado::route('/create'),
            'edit' => Pages\EditCertificado::route('/{record}/edit'),
        ];
    }
}
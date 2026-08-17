<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CertificadoResource\Pages;
use App\Models\Certificado;

// IMPORTACIONES CORRECTAS Y UNIFICADAS PARA TU VERSIÓN DE FILAMENT
use Filament\Actions\ActionGroup;
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

// Librerías para el QR y el PDF
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Barryvdh\DomPDF\Facade\Pdf;

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
                // MENÚ DE 3 RAYAS CON LAS OPCIONES AGRUPADAS
                ActionGroup::make([
                    
                    // 1. Descargar QR
                    Action::make('descargarQR')
                        ->label('Descargar QR')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->action(function (Certificado $record) {
                            
                            // FIX APLICADO AQUÍ: Argumentos con nombre en el constructor para Endroid v5+
                            $qrCode = new QrCode(
                                data: route('certificados.verificar', $record->codigo_cer),
                                size: 200,
                                margin: 10
                            );
                            
                            $writer = new PngWriter();
                            $dataUri = $writer->write($qrCode)->getDataUri();

                            $html = '<!DOCTYPE html>
                            <html>
                            <head><style>body { margin: 0; padding: 0; }</style></head>
                            <body>
                                <div style="position: absolute; bottom: 40px; right: 40px; width: 160px; text-align: center;">
                                    <img src="' . $dataUri . '" style="width: 150px; height: 150px;" />
                                    <p style="font-family: sans-serif; font-size: 11px; margin-top: 5px; color: #333;">' . $record->codigo_cer . '</p>
                                </div>
                            </body>
                            </html>';

                            $pdf = Pdf::loadHTML($html)->setPaper('a4', 'landscape');

                            return response()->streamDownload(
                                fn () => print($pdf->output()),
                                'QR_' . $record->codigo_cer . '.pdf'
                            );
                        }),

                    // 2. Verificar Web
                    Action::make('verificar')
                        ->label('Verificar Web')
                        ->icon('heroicon-o-qr-code')
                        ->url(fn (Certificado $record): string => route('certificados.verificar', $record->codigo_cer))
                        ->openUrlInNewTab(),

                    // 3. Editar
                    EditAction::make(),
                    
                    // 4. Eliminar
                    DeleteAction::make(),
                ])
                ->icon('heroicon-m-bars-3')
                ->tooltip('Opciones'),
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
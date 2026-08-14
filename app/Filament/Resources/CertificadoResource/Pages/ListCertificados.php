<?php

namespace App\Filament\Resources\CertificadoResource\Pages;

use App\Filament\Resources\CertificadoResource;
use App\Models\Certificado;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use OpenSpout\Writer\XLSX\Writer;
use OpenSpout\Reader\XLSX\Reader;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Common\Entity\Style\Color;

class ListCertificados extends ListRecords
{
    protected static string $resource = CertificadoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('descargarPlantilla')
                ->label('Descargar Plantilla')
                ->icon('heroicon-o-table-cells')
                ->color('warning') 
                ->action(function () {
                    return Response::streamDownload(function () {
                        $writer = new Writer();
                        $writer->openToFile('php://output');
                        
                        $headerStyle = (new Style())->setFontBold()->setFontColor(Color::WHITE)->setBackgroundColor('19499C');
                        $headers = ['NOMBRE', 'APELLIDOS', 'CARNET', 'DOCENTE', 'CURSO', 'GESTION'];
                        $writer->addRow(Row::fromValues($headers, $headerStyle));

                        $ejemplo = ['JUAN PABLO', 'PEREZ GOMEZ', '1234567', 'ING ROBERTO DIAZ', 'SEGURIDAD AEREA', 'I - 2026'];
                        $writer->addRow(Row::fromValues($ejemplo));

                        $writer->close();
                    }, 'Plantilla_Certificados_TAMep.xlsx', [
                        'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    ]);
                }),

            Actions\Action::make('importarExcel')
                ->label('Importar Excel')
                ->icon('heroicon-o-arrow-up-on-square-stack')
                ->color('primary')
                ->modalHeading('Importación Estricta de Certificados')
                ->modalDescription('El sistema verificará la integridad de los datos. Se insertarán tildes automáticamente en los cursos reconocidos.')
                ->modalSubmitActionLabel('Validar e Importar')
                ->form([
                    FileUpload::make('archivo')
                        ->label('Archivo Excel (.xlsx)')
                        ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                        ->disk('public')
                        ->required(),
                ])
                // OJO AQUÍ: Inyectamos $livewire para poder disparar el SweetAlert
                ->action(function (array $data, ListRecords $livewire) {
                    $filePath = Storage::disk('public')->path($data['archivo']);
                    $reader = new Reader();
                    $reader->open($filePath);

                    $erroresArray = [];
                    $certificadosAInsertar = [];
                    
                    $carnetsExistentes = Certificado::pluck('carnet_per_cer')->toArray();
                    $carnetsEnEsteExcel = [];

                    $cursosMap = [
                        'TECNICAS DE ENSEÑANZA RECURRENTE' => 'TÉCNICAS DE ENSEÑANZA RECURRENTE',
                        'TECNICAS DE ENSEÑANZA AERONAUTICA INICIAL' => 'TÉCNICAS DE ENSEÑANZA AERONÁUTICA INICIAL',
                        'PROGRAMA DE INDUCCION TAMEP' => 'PROGRAMA DE INDUCCIÓN TAMEP'
                    ];

                    foreach ($reader->getSheetIterator() as $sheet) {
                        foreach ($sheet->getRowIterator() as $rowIndex => $row) {
                            if ($rowIndex === 1) continue; 

                            $cells = $row->toArray();
                            if (empty(trim($cells[0] ?? ''))) continue; 

                            $nombre = mb_strtoupper(preg_replace('/\s+/', ' ', trim($cells[0] ?? '')), 'UTF-8');
                            $apellidos = mb_strtoupper(preg_replace('/\s+/', ' ', trim($cells[1] ?? '')), 'UTF-8');
                            $carnet = mb_strtoupper(preg_replace('/\s+/', '', trim($cells[2] ?? '')), 'UTF-8'); 
                            $docente = mb_strtoupper(preg_replace('/\s+/', ' ', trim($cells[3] ?? '')), 'UTF-8');
                            $cursoRaw = mb_strtoupper(preg_replace('/\s+/', ' ', trim($cells[4] ?? '')), 'UTF-8');
                            $gestion = mb_strtoupper(preg_replace('/\s+/', ' ', trim($cells[5] ?? '')), 'UTF-8');

                            $erroresFila = [];

                            if (!preg_match('/^[\p{L}\s\.]+$/u', $nombre)) $erroresFila[] = "Nombres inválidos (solo letras)";
                            if (!preg_match('/^[\p{L}\s\.]+$/u', $apellidos)) $erroresFila[] = "Apellidos inválidos (solo letras)";
                            if (!preg_match('/^[\p{L}\s\.]+$/u', $docente)) $erroresFila[] = "Docente inválido (solo letras)";
                            
                            if (!preg_match('/^\d+$/', $carnet)) {
                                $erroresFila[] = "Carnet debe ser numérico";
                            } else {
                                if (in_array($carnet, $carnetsExistentes) || in_array($carnet, $carnetsEnEsteExcel)) {
                                    $erroresFila[] = "Carnet duplicado";
                                }
                                $carnetsEnEsteExcel[] = $carnet;
                            }

                            $cursoSinTildes = strtr($cursoRaw, ['Á'=>'A','É'=>'E','Í'=>'I','Ó'=>'O','Ú'=>'U','Ä'=>'A','Ë'=>'E','Ï'=>'I','Ö'=>'O','Ü'=>'U']);
                            
                            $cursoOficial = null;
                            if (array_key_exists($cursoSinTildes, $cursosMap)) {
                                $cursoOficial = $cursosMap[$cursoSinTildes]; 
                            } else {
                                $erroresFila[] = "Curso no reconocido";
                            }

                            if (!preg_match('/^(I|II)\s*-\s*20\d{2}$/', $gestion)) {
                                $erroresFila[] = "Gestión inválida (Ej: I - 2026)";
                            }

                            if (!empty($erroresFila)) {
                                $erroresArray[] = [
                                    'fila' => $rowIndex,
                                    'errores' => '• ' . implode('<br>• ', $erroresFila)
                                ];
                            } else {
                                $semestre = trim(explode('-', $gestion)[0]);
                                $year = trim(explode('-', $gestion)[1]);
                                $mes = ($semestre === 'I') ? '01' : '07';
                                $fecha_cer = "$year-$mes-01";

                                $certificadosAInsertar[] = [
                                    'nombre_per_cer' => $nombre,
                                    'apellido_per_cer' => $apellidos,
                                    'carnet_per_cer' => $carnet,
                                    'docente_cer' => $docente,
                                    'curso_cer' => $cursoOficial, 
                                    'fecha_cer' => $fecha_cer,
                                    'estado_cer' => 'activo',
                                    'codigo_cer' => 'CERT-' . now()->format('Y') . '-' . Str::upper(Str::random(10)),
                                    'id_usu_1' => auth()->user()->id_usu ?? 1,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ];
                            }
                        }
                    }

                    $reader->close();
                    Storage::disk('public')->delete($data['archivo']);

                    // ==========================================
                    // SI HAY ERRORES: MOSTRAMOS SWEETALERT
                    // ==========================================
                    if (count($erroresArray) > 0) {
                        
                        // Diseñamos el contenedor rojo escrolleable interno para el SweetAlert
                        $html = '<div style="font-size: 14px; text-align: left; color: #374151;">';
                        $html .= '<p style="margin-bottom: 12px; text-align: center;">Por seguridad, la importación ha sido abortada.</p>';
                        
                        $html .= '<div style="max-height: 220px; overflow-y: auto; background-color: #fef2f2; border: 1px solid #fca5a5; border-radius: 8px; padding: 12px;">';
                        
                        foreach ($erroresArray as $err) {
                            $html .= '<div style="margin-bottom: 8px; border-bottom: 1px solid #fecaca; padding-bottom: 8px;">';
                            $html .= '<strong style="color: #dc2626; font-size: 15px;">Fila Excel: ' . $err['fila'] . '</strong><br>';
                            $html .= '<span style="color: #991b1b; font-size: 13px;">' . $err['errores'] . '</span>';
                            $html .= '</div>';
                        }

                        $html .= '</div></div>';

                        // Disparamos el SweetAlert Rojo desde Livewire
                        $livewire->dispatch('swal', 
                            title: 'Se encontraron ' . count($erroresArray) . ' errores',
                            html: $html,
                            icon: 'error'
                        );
                        
                        return;
                    }

                    // ==========================================
                    // SI TODO ESTÁ PERFECTO: SWEETALERT DE ÉXITO
                    // ==========================================
                    if (!empty($certificadosAInsertar)) {
                        Certificado::insert($certificadosAInsertar);
                        
                        $livewire->dispatch('swal', 
                            title: 'Importación Exitosa',
                            html: 'Se han registrado <b>' . count($certificadosAInsertar) . '</b> certificados limpiamente en el sistema.',
                            icon: 'success'
                        );
                    }
                }),

            Actions\CreateAction::make()->label('Nuevo Certificado')->color('primary'),
        ];
    }
}
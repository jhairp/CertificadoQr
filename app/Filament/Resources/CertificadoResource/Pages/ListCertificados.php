<?php

namespace App\Filament\Resources\CertificadoResource\Pages;

use App\Filament\Resources\CertificadoResource;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Response;
use OpenSpout\Writer\XLSX\Writer;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Common\Entity\Style\Border;
use OpenSpout\Common\Entity\Style\BorderPart;
use OpenSpout\Common\Entity\Style\Color;

class ListCertificados extends ListRecords
{
    protected static string $resource = CertificadoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // 1. BOTÓN PARA DESCARGAR LA PLANTILLA (AHORA CON GESTION)
            Actions\Action::make('descargarPlantilla')
                ->label('Descargar Plantilla')
                ->icon('heroicon-o-table-cells')
                ->color('info')
                ->action(function () {
                    return Response::streamDownload(function () {
                        $writer = new Writer();
                        $writer->openToFile('php://output');
                        
                        $headerStyle = (new Style())
                            ->setFontBold()
                            ->setFontColor(Color::WHITE)
                            ->setBackgroundColor('19499C');

                        // NUEVAS CABECERAS CON "GESTION"
                        $headers = ['NOMBRE', 'APELLIDOS', 'CARNET', 'DOCENTE', 'CURSO', 'GESTION'];
                        $writer->addRow(Row::fromValues($headers, $headerStyle));

                        // Fila de ejemplo prellenada para guiar al usuario
                        $ejemplo = ['JUAN PABLO', 'PEREZ GOMEZ', '1234567', 'ING. ROBERTO DIAZ', 'SEGURIDAD AEREA', 'I - 2026'];
                        $writer->addRow(Row::fromValues($ejemplo));

                        $writer->close();
                    }, 'Plantilla_Certificados_TAMep.xlsx', [
                        'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    ]);
                }),
            
            Actions\Action::make('importarExcel')
                ->label('Importar Excel')
                ->icon('heroicon-o-arrow-up-on-square-stack')
                ->color('success')
                ->modalHeading('Importar Emisión Masiva')
                ->modalDescription('Asegúrate de que tu Excel contenga exactamente estas 6 columnas: NOMBRE, APELLIDOS, CARNET, DOCENTE, CURSO, GESTION.')
                ->modalSubmitActionLabel('Procesar Datos')
                ->form([
                    FileUpload::make('archivo')
                        ->label('Archivo Excel (.xlsx)')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                        ])
                        ->required(),
                ])
                ->action(function (array $data) {
                    // En el siguiente paso agregaremos la lógica que leerá este archivo
                    // y guardará todo convertido a MAYÚSCULAS en la base de datos.
                    Notification::make()
                        ->title('Archivo preparado')
                        ->body('La interfaz de importación está lista. (Lógica de lectura pendiente)')
                        ->success()
                        ->send();
                }),

            // 3. EL BOTÓN NATIVO DE CREAR (Siempre de último)
            Actions\CreateAction::make()->label('Nuevo Certificado'),
        ];
    }
}
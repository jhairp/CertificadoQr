<?php

namespace App\Filament\Resources\CertificadoResource\Pages;

use App\Filament\Resources\CertificadoResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Barryvdh\DomPDF\Facade\Pdf;

class EditCertificado extends EditRecord
{
    protected static string $resource = CertificadoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // BOTÓN DE DESCARGAR QR EN LA CABECERA
            Action::make('descargarQR')
                ->label('Descargar QR (PDF)')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(function () {
                    $record = $this->getRecord();
                    
                    $qrCode = new QrCode(route('certificados.verificar', $record->codigo_cer));
                    $qrCode->setSize(200);
                    $qrCode->setMargin(10);
                    
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

                    $pdf = Pdf::loadHTML($html)->setPaper('a4', 'portrait');

                    return response()->streamDownload(
                        fn () => print($pdf->output()),
                        'QR_' . $record->codigo_cer . '.pdf'
                    );
                }),
                
            DeleteAction::make(),
        ];
    }
}
<?php

namespace App\Filament\Pages;

use Filament\Actions;
use Illuminate\Support\Facades\Response;
use OpenSpout\Writer\XLSX\Writer;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Common\Entity\Style\Border;
use OpenSpout\Common\Entity\Style\BorderPart;
use OpenSpout\Common\Entity\Style\Color;

class Dashboard extends \Filament\Pages\Dashboard
{
    protected static ?string $navigationLabel = 'Panel de control';

    protected static ?string $title = 'Panel de control';
    
    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('downloadTemplate')
                ->label('Descargar Plantilla')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(function () {
                    return Response::streamDownload(function () {
                        $writer = new Writer();
                        $writer->openToFile('php://output');

                        // NUEVA SINTAXIS OPENSSPOUT v4 PARA BORDES
                        $border = new Border(
                            new BorderPart(Border::BOTTOM, Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID),
                            new BorderPart(Border::TOP, Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID),
                            new BorderPart(Border::LEFT, Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID),
                            new BorderPart(Border::RIGHT, Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID)
                        );

                        // Estilo del Encabezado (+ Borde)
                        $headerStyle = (new Style())
                            ->setFontBold()
                            ->setFontColor(Color::WHITE) 
                            ->setBackgroundColor('19499C')
                            ->setBorder($border);
                        
                        $headers = ['NOMBRE', 'APELLIDOS', 'CARNET', 'DOCENTE', 'CURSO', 'FECHA DE EXPEDICIÓN'];
                        $writer->addRow(Row::fromValues($headers, $headerStyle));

                        // Estilos para las filas de datos (+ Borde)
                        $styleCeleste = (new Style())->setBackgroundColor('E8F4F8')->setBorder($border);
                        $styleBlanco = (new Style())->setBackgroundColor('FFFFFF')->setBorder($border);

                        // TRUCO VITAL: Usar ' ' (un espacio) en lugar de '' (vacío)
                        $emptyData = [' ', ' ', ' ', ' ', ' ', ' '];
                        
                        for ($i = 1; $i <= 50; $i++) {
                            $currentStyle = ($i % 2 !== 0) ? $styleCeleste : $styleBlanco;
                            $writer->addRow(Row::fromValues($emptyData, $currentStyle));
                        }

                        $writer->close();
                    }, 'Plantilla_Certificados_TAMep.xlsx', [
                        'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    ]);
                }),
        ];
    }
}
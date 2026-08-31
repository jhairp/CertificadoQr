<?php

namespace App\Filament\Widgets;

use App\Models\Certificado;
use Filament\Widgets\ChartWidget;

class CertificadosChart extends ChartWidget
{
    // Título del gráfico (Sin la palabra "static")
    protected ?string $heading = 'Métricas de Emisión (Año Actual)';
    
    // Posición en el escritorio (Este sí lleva "static")
    protected static ?int $sort = 2;

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                ],
            ],
        ];
    }

    protected function getData(): array
    {
        $meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        $valores = array_fill(0, 12, 0); // Llenamos los 12 meses con 0 por defecto

        // Consultamos a la base de datos la cantidad de certificados por mes en el año actual
        $certificados = Certificado::selectRaw('MONTH(created_at) as mes, COUNT(*) as total')
            ->whereYear('created_at', date('Y'))
            ->groupBy('mes')
            ->pluck('total', 'mes');

        // Asignamos los totales al array de valores
        foreach ($certificados as $mes => $total) {
            $valores[$mes - 1] = $total; // -1 porque el array empieza en 0 (Enero)
        }

        return [
            'datasets' => [
                [
                    'label' => 'Certificados Emitidos',
                    'data' => $valores,
                    'borderColor' => '#19499C', // Azul TAM
                    'backgroundColor' => 'rgba(25, 73, 156, 0.15)', // Azul transparente para el relleno
                    'borderWidth' => 3,
                    'tension' => 0.4, // Hace que la línea sea curva y suave
                    'fill' => true,   // Rellena el espacio bajo la curva
                    'pointBackgroundColor' => '#FFCD05', // Puntos amarillos
                    'pointBorderColor' => '#ffffff',
                    'pointRadius' => 4,
                ],
            ],
            'labels' => $meses,
        ];
    }

    protected function getType(): string
    {
        return 'line'; // Usamos un gráfico de línea
    }
}
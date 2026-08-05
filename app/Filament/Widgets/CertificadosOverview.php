<?php

namespace App\Filament\Widgets;

use App\Models\Certificado;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CertificadosOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Cursos disponibles', count(config('courses.options')))
                ->description('Cursos definidos para emitir certificados')
                ->color('warning'),
            Stat::make('Certificados emitidos', Certificado::count())
                ->description('Registros totales'),
            Stat::make('Certificados activos', Certificado::where('estado_cer', 'activo')->count())
                ->description('Verificables públicamente')
                ->color('success'),
        ];
    }
}

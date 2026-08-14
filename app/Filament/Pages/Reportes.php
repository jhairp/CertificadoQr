<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Certificado;
use Livewire\WithPagination;
use Livewire\WithoutUrlPagination; // <-- FIX: Evita el 404 al cambiar de página
use Illuminate\Support\Facades\Response;
use OpenSpout\Writer\XLSX\Writer;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Common\Entity\Style\Border;
use OpenSpout\Common\Entity\Style\BorderPart;
use OpenSpout\Common\Entity\Style\Color;

class Reportes extends Page
{
    // Usamos WithoutUrlPagination para que el paginador funcione seguro dentro del modal
    use WithPagination, WithoutUrlPagination;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?string $navigationLabel = 'Reportes';
    protected ?string $heading = 'Centro de Reportes TAMep';
    protected string $view = 'filament.pages.reportes';

    public ?string $curso = '';
    public ?string $docente = '';
    public ?string $gestion = '';
    public ?string $estado = '';

    public function updatedCurso() { $this->resetPage(); }
    public function updatedDocente() { $this->resetPage(); }
    public function updatedGestion() { $this->resetPage(); }
    public function updatedEstado() { $this->resetPage(); }

    public function resetFiltros(): void
    {
        $this->curso = '';
        $this->docente = '';
        $this->gestion = '';
        $this->estado = '';
        $this->resetPage();
    }

    public function getGestionesOptionsProperty(): array
    {
        $yearActual = (int) date('Y');
        $gestiones = [];
        for ($year = $yearActual + 1; $year >= $yearActual - 3; $year--) {
            $gestiones["II-{$year}"] = "II - {$year}";
            $gestiones["I-{$year}"] = "I - {$year}";
        }
        return $gestiones;
    }

    public function getDocentesOptionsProperty(): array
    {
        return Certificado::query()->whereNotNull('docente_cer')->distinct()->pluck('docente_cer', 'docente_cer')->toArray();
    }

    public function getCertificadosQuery()
    {
        $query = Certificado::query();

        if (!empty($this->curso)) $query->where('curso_cer', $this->curso);
        if (!empty($this->docente)) $query->where('docente_cer', $this->docente);
        if (!empty($this->estado)) $query->where('estado_cer', $this->estado);
        
        if (!empty($this->gestion)) {
            [$semestre, $year] = explode('-', $this->gestion);
            if ($semestre === 'I') {
                $query->whereYear('fecha_cer', $year)->whereMonth('fecha_cer', '>=', 1)->whereMonth('fecha_cer', '<=', 6);
            } else {
                $query->whereYear('fecha_cer', $year)->whereMonth('fecha_cer', '>=', 7)->whereMonth('fecha_cer', '<=', 12);
            }
        }

        return $query->orderBy('created_at', 'desc');
    }

    public function exportarExcel()
    {
        return Response::streamDownload(function () {
            $writer = new Writer();
            $writer->openToFile('php://output');

            $border = new Border(
                new BorderPart(Border::BOTTOM, Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID),
                new BorderPart(Border::TOP, Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID),
                new BorderPart(Border::LEFT, Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID),
                new BorderPart(Border::RIGHT, Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID)
            );

            $headerStyle = (new Style())
                ->setFontBold()
                ->setFontColor(Color::WHITE)
                ->setBackgroundColor('19499C')
                ->setBorder($border);

            $headers = ['NOMBRES', 'APELLIDOS', 'CARNET', 'DOCENTE', 'CURSO', 'GESTIÓN', 'FECHA DE EXPEDICIÓN', 'ESTADO'];
            $writer->addRow(Row::fromValues($headers, $headerStyle));

            $styleCeleste = (new Style())->setBackgroundColor('E8F4F8')->setBorder($border);
            $styleAmarillo = (new Style())->setBackgroundColor('FFFDE7')->setBorder($border);

            $certificados = $this->getCertificadosQuery()->get();

            $i = 1;
            foreach ($certificados as $cert) {
                $currentStyle = ($i % 2 !== 0) ? $styleCeleste : $styleAmarillo;
                
                $gestionStr = $cert->fecha_cer ? (($cert->fecha_cer->format('m') <= 6 ? 'I' : 'II') . ' - ' . $cert->fecha_cer->format('Y')) : 'N/A';

                $rowData = [
                    $cert->nombre_per_cer,
                    $cert->apellido_per_cer,
                    $cert->carnet_per_cer,
                    $cert->docente_cer,
                    $cert->curso_cer,
                    $gestionStr,
                    $cert->created_at ? $cert->created_at->format('d/m/Y') : 'N/A',
                    strtoupper($cert->estado_cer),
                ];

                $rowData = array_map(fn($value) => empty($value) ? ' ' : $value, $rowData);
                $writer->addRow(Row::fromValues($rowData, $currentStyle));
                $i++;
            }

            if ($certificados->isEmpty()) {
                $writer->addRow(Row::fromValues(['Sin registros', ' ', ' ', ' ', ' ', ' ', ' ', ' '], $styleCeleste));
            }

            $writer->close();
        }, 'Reporte_Certificados_TAMep.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
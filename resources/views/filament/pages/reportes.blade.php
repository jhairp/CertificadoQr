<x-filament-panels::page>
    <style>
        /* CSS Seguro e inmune a las reglas de Tailwind */
        .grid-tarjetas { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem; }
        .modal-layout { display: flex; flex-direction: column; gap: 1.5rem; }
        .col-filtros { background-color: rgba(0,0,0,0.03); padding: 1.5rem; border-radius: 0.5rem; border: 1px solid #e5e7eb; }
        .col-tabla { overflow-x: auto; }
        
        @media(min-width: 1024px) {
            .modal-layout { flex-direction: row; }
            .col-filtros { width: 30%; }
            .col-tabla { width: 70%; }
        }

        /* Diseño exacto estilo Excel para la vista previa */
        .tabla-excel { width: 100%; border-collapse: collapse; text-align: left; font-size: 0.75rem; border: 1px solid #d1d5db; }
        .tabla-excel th { padding: 0.6rem; border: 1px solid #1e40af; background-color: #19499C; color: white; text-transform: uppercase; font-weight: bold; }
        .tabla-excel td { padding: 0.6rem; border: 1px solid #d1d5db; color: #111827; }
        .fila-par { background-color: #E8F4F8; }
        .fila-impar { background-color: #FFFDE7; }

        .input-filtro { width: 100%; border-radius: 0.5rem; border: 1px solid #d1d5db; padding: 0.6rem; font-size: 0.875rem; background-color: white; color: black; margin-bottom: 1rem; outline: none; }
        .input-filtro:focus { border-color: #19499C; box-shadow: 0 0 0 1px #19499C; }
        .lbl-filtro { display: block; font-size: 0.8rem; font-weight: bold; margin-bottom: 0.25rem; color: #374151; }
    </style>

    <div class="grid-tarjetas">
        <x-filament::section>
            <x-slot name="heading">
                <div style="display: flex; align-items: center; gap: 0.5rem; color: #19499C;">
                    <x-filament::icon icon="heroicon-o-document-chart-bar" style="width: 1.5rem; height: 1.5rem;" />
                    <span>Reportes Simples</span>
                </div>
            </x-slot>
            
            <p style="color: gray; font-size: 0.875rem; margin-bottom: 1.5rem;">
                Filtra certificados por curso, docente, gestión y estado. Vista previa rápida tipo Excel.
            </p>

            <x-filament::button x-on:click="$dispatch('open-modal', { id: 'modal-reporte-simple' })" style="width: 100%; justify-content: center;">
                Abrir Generador
            </x-filament::button>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">
                <div style="display: flex; align-items: center; gap: 0.5rem; color: gray;">
                    <x-filament::icon icon="heroicon-o-academic-cap" style="width: 1.5rem; height: 1.5rem;" />
                    <span>Reporte por Auditoría</span>
                </div>
            </x-slot>
            
            <p style="color: gray; font-size: 0.875rem; margin-bottom: 1.5rem;">
                Métricas de usuarios emisores y actividad.
            </p>

            <x-filament::button color="gray" style="width: 100%; justify-content: center;" disabled>
                Próximamente
            </x-filament::button>
        </x-filament::section>
    </div>

    <x-filament::modal id="modal-reporte-simple" width="7xl">
        
        <x-slot name="heading">
            Generar Reporte Simple
        </x-slot>
        <x-slot name="description">
            Aplica los filtros en la izquierda y revisa la vista previa estilo Excel en la derecha antes de descargar.
        </x-slot>

        <div class="modal-layout">
            
            <div class="col-filtros">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <span style="font-size: 0.875rem; font-weight: bold; color: #19499C; text-transform: uppercase;">Filtros</span>
                    @if($curso || $docente || $gestion || $estado)
                        <button wire:click="resetFiltros" style="font-size: 0.75rem; color: #ef4444; font-weight: bold; cursor: pointer;">Limpiar todo</button>
                    @endif
                </div>

                <div>
                    <label class="lbl-filtro">Curso</label>
                    <select wire:model.live="curso" class="input-filtro">
                        <option value="">Todos los cursos</option>
                        @foreach(config('courses.options', []) as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="lbl-filtro">Docente</label>
                    <select wire:model.live="docente" class="input-filtro">
                        <option value="">Todos los docentes</option>
                        @foreach($this->docentesOptions as $doc)
                            <option value="{{ $doc }}">{{ $doc }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="lbl-filtro">Gestión / Semestre</label>
                    <select wire:model.live="gestion" class="input-filtro">
                        <option value="">Todas las gestiones</option>
                        @foreach($this->gestionesOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="lbl-filtro">Estado</label>
                    <select wire:model.live="estado" class="input-filtro">
                        <option value="">Todos los estados</option>
                        <option value="activo">Activo</option>
                        <option value="anulado">Anulado</option>
                    </select>
                </div>
            </div>

            <div class="col-tabla">
                @php
                    $certificadosPaginados = $this->getCertificadosQuery()->paginate(5);
                @endphp

                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                    <span style="font-size: 0.875rem; font-weight: bold; color: #374151;">Vista previa de datos</span>
                    <span style="font-size: 0.75rem; background-color: #e0f2fe; color: #0369a1; padding: 0.25rem 0.6rem; border-radius: 999px; font-weight: bold;">
                        Total: {{ $certificadosPaginados->total() }} registros
                    </span>
                </div>

                <table class="tabla-excel">
                    <thead>
                        <tr>
                            <th>CÓDIGO</th>
                            <th>PARTICIPANTE</th>
                            <th>DOCENTE</th>
                            <th>CURSO</th>
                            <th style="text-align: center;">EMISIÓN</th>
                            <th style="text-align: center;">ESTADO</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($certificadosPaginados as $index => $cert)
                            <tr class="{{ $index % 2 === 0 ? 'fila-par' : 'fila-impar' }}">
                                <td style="font-family: monospace;">{{ $cert->codigo_cer }}</td>
                                <td style="font-weight: bold;">{{ $cert->nombreCompleto() }}</td>
                                <td>{{ $cert->docente_cer ?? 'N/A' }}</td>
                                <td>{{ $cert->curso_cer }}</td>
                                <td style="text-align: center;">{{ $cert->fecha_cer ? $cert->fecha_cer->format('d/m/Y') : 'N/A' }}</td>
                                <td style="text-align: center;">
                                    @if($cert->estado_cer === 'activo')
                                        <span style="background-color: #10b981; color: white; padding: 3px 6px; border-radius: 4px; font-weight: bold; font-size: 0.65rem;">ACTIVO</span>
                                    @else
                                        <span style="background-color: #ef4444; color: white; padding: 3px 6px; border-radius: 4px; font-weight: bold; font-size: 0.65rem;">ANULADO</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="padding: 2rem; text-align: center; color: gray; background-color: white;">
                                    No hay certificados que coincidan con los filtros.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div style="margin-top: 1rem;">
                    {{ $certificadosPaginados->links() }}
                </div>
            </div>
        </div>

        <x-slot name="footerActions">
            <x-filament::button wire:click="exportarExcel" color="primary" icon="heroicon-o-arrow-down-tray">
                Descargar Excel (.xlsx)
            </x-filament::button>
            
            <x-filament::button x-on:click="$dispatch('close-modal', { id: 'modal-reporte-simple' })" color="gray" variant="outline">
                Cerrar
            </x-filament::button>
        </x-slot>

    </x-filament::modal>

</x-filament-panels::page>
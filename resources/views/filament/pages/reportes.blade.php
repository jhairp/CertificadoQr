<x-filament-panels::page>
    <style>
        .grid-tarjetas { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem; }
        .modal-layout { display: flex; flex-direction: column; gap: 1.5rem; }
        
        /* FILTROS Y MODO OSCURO */
        .col-filtros { background-color: rgba(0,0,0,0.03); padding: 1.5rem; border-radius: 0.5rem; border: 1px solid #e5e7eb; }
        .dark .col-filtros { background-color: #1f2937; border-color: #374151; }
        
        .input-filtro { width: 100%; border-radius: 0.5rem; border: 1px solid #d1d5db; padding: 0.6rem; font-size: 0.875rem; background-color: white; color: black; margin-bottom: 1rem; outline: none; }
        .dark .input-filtro { background-color: #111827; border-color: #374151; color: #f3f4f6; }
        .input-filtro:focus { border-color: #19499C; box-shadow: 0 0 0 1px #19499C; }
        
        .lbl-filtro { display: block; font-size: 0.8rem; font-weight: bold; margin-bottom: 0.25rem; color: #374151; }
        .dark .lbl-filtro { color: #d1d5db; }

        .col-tabla { overflow-x: auto; }
        @media(min-width: 1024px) { .modal-layout { flex-direction: row; } .col-filtros { width: 30%; } .col-tabla { width: 70%; } }

        /* TABLA EXCEL Y MODO OSCURO */
        .tabla-excel { width: 100%; border-collapse: collapse; text-align: left; font-size: 0.75rem; border: 1px solid #d1d5db; }
        .dark .tabla-excel { border-color: #374151; }
        
        .tabla-excel th { padding: 0.6rem; border: 1px solid #1e40af; background-color: #19499C; color: white; text-transform: uppercase; font-weight: bold; }
        .tabla-excel td { padding: 0.6rem; border: 1px solid #d1d5db; color: #111827; }
        .dark .tabla-excel td { border-color: #374151; color: #e5e7eb; }
        
        .fila-par { background-color: #E8F4F8; }
        .fila-impar { background-color: #FFFFFF; }
        .dark .fila-par { background-color: #111827; }
        .dark .fila-impar { background-color: #1f2937; }

        /* ==============================================
             DOMANDO EL PAGINADOR NATIVO DE LIVEWIRE
        ============================================== */
        .tam-paginacion { margin-top: 1.5rem; border-top: 1px solid #e5e7eb; padding-top: 1rem; }
        .dark .tam-paginacion { border-color: #374151; }
        
        /* 1. Forzar el tamaño de las flechas (evita que se hagan gigantes) */
        .tam-paginacion svg { width: 1.25rem !important; height: 1.25rem !important; display: inline-block; }
        
        /* 2. Ocultar los textos "Previous" y "Next" simulando la clase sr-only de Tailwind */
        .tam-paginacion .sr-only { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border-width: 0; }
        
        /* 3. Ocultar el layout móvil tosco y forzar el layout de escritorio con los números */
        .tam-paginacion > nav > div:first-child { display: none !important; }
        .tam-paginacion > nav > div:last-child { display: flex !important; align-items: center; justify-content: space-between; width: 100%; flex-wrap: wrap; gap: 1rem; }
        
        /* 4. Estilos de página activa (Azul TAM) */
        .tam-paginacion span[aria-current="page"] > span,
        .tam-paginacion span[aria-current="page"] > button {
            background-color: #19499C !important;
            color: white !important;
            border-color: #19499C !important;
        }
    </style>

    <div class="grid-tarjetas">
        <x-filament::section>
            <x-slot name="heading">
                <div style="display: flex; align-items: center; gap: 0.5rem; color: #19499C;">
                    <x-filament::icon icon="heroicon-o-document-chart-bar" style="width: 1.5rem; height: 1.5rem;" />
                    <span>Reportes y Exportación</span>
                </div>
            </x-slot>
            <p style="color: gray; font-size: 0.875rem; margin-bottom: 1.5rem;">
                Filtra certificados por gestión y descárgalos. El código QR se oculta por seguridad.
            </p>
            <x-filament::button x-on:click="$dispatch('open-modal', { id: 'modal-reporte-simple' })" style="width: 100%; justify-content: center;">
                Generar Reporte Excel
            </x-filament::button>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">
                <div style="display: flex; align-items: center; gap: 0.5rem; color: gray;">
                    <x-filament::icon icon="heroicon-o-academic-cap" style="width: 1.5rem; height: 1.5rem;" />
                    <span>Reporte por Auditoría</span>
                </div>
            </x-slot>
            <p style="color: gray; font-size: 0.875rem; margin-bottom: 1.5rem;">Métricas de usuarios emisores y actividad.</p>
            <x-filament::button color="gray" style="width: 100%; justify-content: center;" disabled>Próximamente</x-filament::button>
        </x-filament::section>
    </div>

    <x-filament::modal id="modal-reporte-simple" width="7xl">
        <x-slot name="heading">Generar Reporte de Emisiones</x-slot>
        <div class="modal-layout">
            
            <div class="col-filtros">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <span style="font-size: 0.875rem; font-weight: bold; color: #19499C; text-transform: uppercase;">Filtros</span>
                    @if($curso || $docente || $gestion || $estado)
                        <button wire:click="resetFiltros" style="font-size: 0.75rem; color: #ef4444; font-weight: bold; cursor: pointer;">Limpiar</button>
                    @endif
                </div>
                <div><label class="lbl-filtro">Curso</label>
                    <select wire:model.live="curso" class="input-filtro">
                        <option value="">Todos los cursos</option>
                        @foreach(config('courses.options', []) as $key => $label) <option value="{{ $key }}">{{ $label }}</option> @endforeach
                    </select>
                </div>
                <div><label class="lbl-filtro">Docente</label>
                    <select wire:model.live="docente" class="input-filtro">
                        <option value="">Todos los docentes</option>
                        @foreach($this->docentesOptions as $doc) <option value="{{ $doc }}">{{ $doc }}</option> @endforeach
                    </select>
                </div>
                <div><label class="lbl-filtro">Gestión</label>
                    <select wire:model.live="gestion" class="input-filtro">
                        <option value="">Todas las gestiones</option>
                        @foreach($this->gestionesOptions as $value => $label) <option value="{{ $value }}">{{ $label }}</option> @endforeach
                    </select>
                </div>
                <div><label class="lbl-filtro">Estado</label>
                    <select wire:model.live="estado" class="input-filtro">
                        <option value="">Todos los estados</option>
                        <option value="activo">Activo</option>
                        <option value="anulado">Anulado</option>
                    </select>
                </div>
            </div>

            <div class="col-tabla">
                @php $certificadosPaginados = $this->getCertificadosQuery()->paginate(10); @endphp
                
                <table class="tabla-excel">
                    <thead>
                        <tr>
                            <th>PARTICIPANTE</th>
                            <th>CARNET (CI)</th>
                            <th>DOCENTE</th>
                            <th>CURSO</th>
                            <th style="text-align: center;">GESTIÓN</th>
                            <th style="text-align: center;">EXPEDICIÓN</th>
                            <th style="text-align: center;">ESTADO</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($certificadosPaginados as $index => $cert)
                            <tr class="{{ $index % 2 === 0 ? 'fila-par' : 'fila-impar' }}">
                                <td style="font-weight: bold;">{{ $cert->nombreCompleto() }}</td>
                                <td>{{ $cert->carnet_per_cer ?? 'N/A' }}</td>
                                <td>{{ $cert->docente_cer ?? 'N/A' }}</td>
                                <td>{{ $cert->curso_cer }}</td>
                                <td style="text-align: center; font-weight: bold;">
                                    {{ $cert->fecha_cer ? (($cert->fecha_cer->format('m') <= 6 ? 'I' : 'II') . ' - ' . $cert->fecha_cer->format('Y')) : 'N/A' }}
                                </td>
                                <td style="text-align: center;">{{ $cert->created_at ? $cert->created_at->format('d/m/Y') : 'N/A' }}</td>
                                <td style="text-align: center;">
                                    @if($cert->estado_cer === 'activo') <span style="background-color: #10b981; color: white; padding: 3px 6px; border-radius: 4px; font-weight: bold; font-size: 0.65rem;">ACTIVO</span>
                                    @else <span style="background-color: #ef4444; color: white; padding: 3px 6px; border-radius: 4px; font-weight: bold; font-size: 0.65rem;">ANULADO</span> @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" style="padding: 2rem; text-align: center; color: gray; background-color: transparent;">No hay datos que coincidan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                
                <div class="tam-paginacion">
                    {{ $certificadosPaginados->links() }}
                </div>
            </div>

        </div>

        <x-slot name="footerActions">
            <x-filament::button wire:click="exportarExcel" color="primary" icon="heroicon-o-arrow-down-tray">Descargar Excel (.xlsx)</x-filament::button>
            <x-filament::button x-on:click="$dispatch('close-modal', { id: 'modal-reporte-simple' })" color="gray" variant="outline">Cerrar</x-filament::button>
        </x-slot>
    </x-filament::modal>
</x-filament-panels::page>
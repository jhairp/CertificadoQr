<x-filament-panels::page>
    <style>
        .grid-tarjetas { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem; }
        .modal-layout { display: flex; flex-direction: column; gap: 1.5rem; }
        
        .col-filtros { background-color: rgba(0,0,0,0.03); padding: 1.5rem; border-radius: 0.5rem; border: 1px solid #e5e7eb; display: flex; flex-direction: column; }
        .dark .col-filtros { background-color: #1f2937; border-color: #374151; }
        .filtros-scroll { flex-grow: 1; }
        
        .input-filtro { width: 100%; border-radius: 0.5rem; border: 1px solid #d1d5db; padding: 0.6rem; font-size: 0.875rem; background-color: white; color: black; margin-bottom: 1rem; outline: none; }
        .dark .input-filtro { background-color: #111827; border-color: #374151; color: #f3f4f6; }
        .input-filtro:focus { border-color: #19499C; box-shadow: 0 0 0 1px #19499C; }
        
        .lbl-filtro { display: block; font-size: 0.8rem; font-weight: bold; margin-bottom: 0.25rem; color: #374151; }
        .dark .lbl-filtro { color: #d1d5db; }

        .col-tabla { overflow-x: auto; }
        @media(min-width: 1024px) { .modal-layout { flex-direction: row; } .col-filtros { width: 30%; } .col-tabla { width: 70%; } }

        .tabla-excel { width: 100%; border-collapse: collapse; text-align: left; font-size: 0.75rem; border: 1px solid #d1d5db; }
        .dark .tabla-excel { border-color: #374151; }
        
        .tabla-excel th { padding: 0.6rem; border: 1px solid #1e40af; background-color: #19499C; color: white; text-transform: uppercase; font-weight: bold; }
        .tabla-excel td { padding: 0.6rem; border: 1px solid #d1d5db; color: #111827; }
        .dark .tabla-excel td { border-color: #374151; color: #e5e7eb; }
        
        .fila-par { background-color: #E8F4F8; }
        .fila-impar { background-color: #FFFFFF; }
        .dark .fila-par { background-color: #090d16; }
        .dark .fila-impar { background-color: #111827; }

        /* ==========================================
           PAGINADOR ESTILO FILAMENT NATIVO
           ========================================== */
        .tam-pag-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 1.25rem;
            padding-top: 1rem;
            border-top: 1px solid #e5e7eb;
        }
        .dark .tam-pag-container { border-color: #374151; }

        .tam-pag-info {
            font-size: 0.875rem;
            color: #4b5563;
        }
        .dark .tam-pag-info { color: #9ca3af; }

        .tam-pag-buttons {
            display: flex;
            align-items: center;
            gap: 0.35rem;
        }

        .tam-btn-page {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 2rem;
            height: 2rem;
            padding: 0 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            border-radius: 0.375rem;
            border: 1px solid #d1d5db;
            background-color: #ffffff;
            color: #374151;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .dark .tam-btn-page {
            background-color: #1f2937;
            border-color: #4b5563;
            color: #d1d5db;
        }
        .tam-btn-page:hover:not(:disabled) { background-color: #f3f4f6; }
        .dark .tam-btn-page:hover:not(:disabled) { background-color: #374151; }

        .tam-btn-active {
            background-color: #19499C !important;
            border-color: #19499C !important;
            color: #ffffff !important;
            font-weight: 600;
        }

        .tam-btn-disabled {
            background-color: #f9fafb;
            color: #9ca3af;
            cursor: not-allowed;
        }
        .dark .tam-btn-disabled {
            background-color: #111827;
            border-color: #374151;
            color: #4b5563;
        }
    </style>

    <div class="grid-tarjetas">
        <!-- TARJETA 1 -->
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2 text-gray-900 dark:text-white">
                    <x-filament::icon icon="heroicon-o-document-chart-bar" class="w-6 h-6 text-[#19499C] dark:text-blue-400" />
                    <span>Reportes y Exportación</span>
                </div>
            </x-slot>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Filtra certificados por gestión y descárgalos. El código QR se oculta por seguridad.
            </p>
            <div style="margin-top: 1.5rem;">
                <x-filament::button x-on:click="$dispatch('open-modal', { id: 'modal-reporte-simple' })" style="width: 100%; justify-content: center;">
                    Generar Reporte Excel
                </x-filament::button>
            </div>
        </x-filament::section>

        <!-- TARJETA 2 -->
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2 text-gray-900 dark:text-white">
                    <x-filament::icon icon="heroicon-o-academic-cap" class="w-6 h-6 text-gray-500" />
                    <span>Reporte por Auditoría</span>
                </div>
            </x-slot>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Métricas de usuarios emisores y actividad.
            </p>
            <div style="margin-top: 1.5rem;">
                <x-filament::button color="gray" style="width: 100%; justify-content: center;" disabled>
                    Próximamente
                </x-filament::button>
            </div>
        </x-filament::section>
    </div>

    <!-- MODAL DE REPORTE -->
    <x-filament::modal id="modal-reporte-simple" width="7xl">
        <x-slot name="heading">Generar Reporte de Emisiones</x-slot>
        
        <div class="modal-layout">
            
            <!-- PANEL IZQUIERDO: FILTROS Y BOTONES -->
            <div class="col-filtros">
                <!-- Zona de Filtros -->
                <div class="filtros-scroll">
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-sm font-bold uppercase text-[#19499C] dark:text-blue-400">Filtros</span>
                        @if($curso || $docente || $gestion || $estado)
                            <button wire:click="resetFiltros" class="text-xs font-bold text-red-500 dark:text-red-400 cursor-pointer hover:underline focus:outline-none">Limpiar</button>
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

                <!-- Zona de Botones -->
                <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700 flex flex-col gap-3">
                    <x-filament::button wire:click="exportarExcel" color="primary" icon="heroicon-o-arrow-down-tray" class="w-full justify-center">
                        Descargar Excel (.xlsx)
                    </x-filament::button>
                    
                    <x-filament::button x-on:click="$dispatch('close-modal', { id: 'modal-reporte-simple' })" color="gray" variant="outline" class="w-full justify-center">
                        Cancelar
                    </x-filament::button>
                </div>
            </div>

            <!-- PANEL DERECHO: VISTA PREVIA (CON PAGINACIÓN) -->
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
                                <td class="font-bold">{{ $cert->nombreCompleto() }}</td>
                                <td>{{ $cert->carnet_per_cer ?? 'N/A' }}</td>
                                <td>{{ $cert->docente_cer ?? 'N/A' }}</td>
                                <td>{{ $cert->curso_cer }}</td>
                                <td class="text-center font-bold">
                                    {{ $cert->fecha_cer ? (($cert->fecha_cer->format('m') <= 6 ? 'I' : 'II') . ' - ' . $cert->fecha_cer->format('Y')) : 'N/A' }}
                                </td>
                                <td class="text-center">{{ $cert->created_at ? $cert->created_at->format('d/m/Y') : 'N/A' }}</td>
                                <td class="text-center">
                                    @if($cert->estado_cer === 'activo') 
                                        <span class="bg-emerald-500 text-white px-2 py-1 rounded text-[0.65rem] font-bold">ACTIVO</span>
                                    @else 
                                        <span class="bg-red-500 text-white px-2 py-1 rounded text-[0.65rem] font-bold">ANULADO</span> 
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="p-8 text-center text-gray-500 dark:text-gray-400 bg-transparent">No hay datos que coincidan con los filtros.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                
                <!-- REPLICA EXACTA DEL PAGINADOR NATIVO DE FILAMENT -->
                @if ($certificadosPaginados->hasPages())
                    <div class="tam-pag-container">
                        <div class="tam-pag-info">
                            Mostrando <strong style="color: inherit;">{{ $certificadosPaginados->firstItem() }}</strong>
                            a <strong style="color: inherit;">{{ $certificadosPaginados->lastItem() }}</strong>
                            de <strong style="color: inherit;">{{ $certificadosPaginados->total() }}</strong> resultados
                        </div>

                        <div class="tam-pag-buttons">
                            <!-- Botón Anterior -->
                            <button
                                wire:click="previousPage"
                                @if ($certificadosPaginados->onFirstPage()) disabled @endif
                                class="tam-btn-page {{ $certificadosPaginados->onFirstPage() ? 'tam-btn-disabled' : '' }}"
                            >
                                <svg style="width: 1rem; height: 1rem;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                                </svg>
                            </button>

                            <!-- Páginas Numeradas (Muestra la actual y 2 a los lados) -->
                            @foreach(range(1, $certificadosPaginados->lastPage()) as $i)
                                @if($i >= $certificadosPaginados->currentPage() - 2 && $i <= $certificadosPaginados->currentPage() + 2)
                                    <button
                                        wire:click="gotoPage({{ $i }})"
                                        class="tam-btn-page {{ $i == $certificadosPaginados->currentPage() ? 'tam-btn-active' : '' }}"
                                    >
                                        {{ $i }}
                                    </button>
                                @endif
                            @endforeach

                            <!-- Botón Siguiente -->
                            <button
                                wire:click="nextPage"
                                @if (!$certificadosPaginados->hasMorePages()) disabled @endif
                                class="tam-btn-page {{ !$certificadosPaginados->hasMorePages() ? 'tam-btn-disabled' : '' }}"
                            >
                                <svg style="width: 1rem; height: 1rem;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                </svg>
                            </button>
                        </div>
                    </div>
                @endif
            </div>

        </div>
    </x-filament::modal>
</x-filament-panels::page>
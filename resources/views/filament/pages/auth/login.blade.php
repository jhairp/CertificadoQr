<x-filament-panels::page.simple>
    <style>
        @media (min-width: 1024px) {
            
            /* 1. Clavamos la imagen en la mitad derecha como fondo fijo */
            body::before {
                content: "";
                position: fixed;
                top: 0;
                right: 0;
                width: 90vw;
                height: 100vh;
                background: url("{{ asset('images/fondo-tam.webp') }}") no-repeat center center;
                background-size: cover;
                border-left: 6px solid #FFCD05; /* Borde amarillo elegante */
                z-index: -1; 
            }

            /* 2. Arrancamos el contenedor de Filament y lo obligamos a ser la mitad izquierda exacta */
            .fi-simple-layout, 
            div[class*="fi-simple-layout"] {
                position: absolute !important;
                top: 0 !important;
                left: 0 !important;
                width: 50vw !important; /* Exactamente la mitad izquierda */
                min-height: 100vh !important; /* Altura completa de la pantalla */
                display: flex !important;
                flex-direction: column !important;
                align-items: center !important; /* Lo centra horizontalmente en su mitad */
                justify-content: center !important; /* Lo centra verticalmente perfecto */
                padding: 0 2rem !important;
                margin: 0 !important;
                background-color: white !important;
            }

            /* 3. Le damos un ancho elegante a la caja del formulario */
            main, .fi-simple-main {
                width: 100% !important;
                max-width: 420px !important; /* Ancho ideal para un formulario de login */
                margin: 0 !important; 
                box-shadow: none !important;
                border: none !important;
                background: transparent !important;
            }
        }
    </style>

    <form wire:submit="authenticate" class="space-y-6">
        
        {{ $this->form }}

        <x-filament::button type="submit" size="lg" class="w-full mt-4">
            Ingresar al sistema
        </x-filament::button>

    </form>
</x-filament-panels::page.simple>
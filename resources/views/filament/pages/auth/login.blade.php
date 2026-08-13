<div class="tam-login-container">
    <style>
        /* Estructura base de pantalla completa */
        .tam-login-container {
            display: flex;
            min-height: 100vh;
            width: 100vw;
            background-color: #f8fafc;
            color: #111827;
            transition: background-color 0.3s ease, color 0.3s ease;
            overflow: hidden;
        }

        .dark .tam-login-container {
            background-color: #090d16;
            color: #f9fafb;
        }

        /* Lado Izquierdo: Formulario */
        .tam-login-left {
            width: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 2rem 1.5rem;
            box-sizing: border-box;
            z-index: 10;
        }

        /* TARJETA CON BORDE QUE ENVOLVERÁ TODO EL LOGIN */
        .tam-login-box {
            width: 100%;
            max-width: 420px;
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            padding: 2.5rem 2rem;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
            transition: all 0.3s ease;
        }

        .dark .tam-login-box {
            background-color: #111827;
            border-color: #1f2937;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.4);
        }

        /* CABECERA Y TEXTOS CENTRADOS Y ELEGANTES */
        .tam-login-header {
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.75rem;
        }

        .tam-login-title {
            font-size: 1.65rem;
            font-weight: 800;
            letter-spacing: -0.025em;
            color: #0f172a;
            margin-top: 1.25rem;
            line-height: 1.25;
            text-align: center;
        }

        .dark .tam-login-title {
            color: #ffffff;
        }

        .tam-login-subtitle {
            font-size: 0.875rem;
            color: #64748b;
            margin-top: 0.5rem;
            text-align: center;
            line-height: 1.4;
        }

        .dark .tam-login-subtitle {
            color: #94a3b8;
        }

        /* Lado Derecho: Imagen */
        .tam-login-right {
            display: none;
            position: relative;
            background-color: #111827;
        }

        @media (min-width: 1024px) {
            .tam-login-left {
                width: 50%;
                padding: 3rem;
            }
            .tam-login-right {
                display: block;
                width: 50%;
            }
        }

        .tam-login-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: 90% center;
            position: absolute;
            inset: 0;
        }

        .tam-login-overlay {
            position: absolute;
            inset: 0;
            background-color: rgba(17, 24, 39, 0.25);
            transition: background-color 0.3s ease;
        }

        .dark .tam-login-overlay {
            background-color: rgba(9, 13, 22, 0.55);
        }

        .tam-login-divider {
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 6px;
            background-color: #FFCD05;
            box-shadow: 0 0 15px rgba(255, 205, 5, 0.4);
        }

        /* SEPARACIÓN GARANTIZADA ENTRE EL FORMULARIO (RECORDARME) Y EL BOTÓN */
        .tam-login-submit-container {
            margin-top: 1.75rem;
        }

        /* Estilo del botón principal */
        .tam-login-box button[type="submit"],
        .tam-login-box .fi-btn {
            background-color: #19499C !important;
            color: #ffffff !important;
            font-weight: 600;
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
            width: 100%;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 6px -1px rgba(25, 73, 156, 0.25);
            transition: background-color 0.2s ease, transform 0.1s ease;
        }

        .tam-login-box button[type="submit"]:hover,
        .tam-login-box .fi-btn:hover {
            background-color: #143b7e !important;
        }

        .tam-login-box button[type="submit"]:active,
        .tam-login-box .fi-btn:active {
            transform: scale(0.99);
        }
    </style>

    <div class="tam-login-left">
        <div class="tam-login-box">
            
            <div class="tam-login-header">
                <x-filament-panels::logo />
                
                <h2 class="tam-login-title">
                    Bienvenido a TAMep
                </h2>
                <p class="tam-login-subtitle">
                    Ingresa tus credenciales para acceder al sistema
                </p>
            </div>

            <form wire:submit="authenticate">
                {{ $this->form }}

                <div class="tam-login-submit-container" style="margin-top: 1.75rem;">
                    <x-filament::button type="submit" size="lg" class="w-full">
                        Ingresar al sistema
                    </x-filament::button>
                </div>
            </form>

        </div>
    </div>

    <div class="tam-login-right">
        <img src="{{ asset('images/fondo-tam.webp') }}" alt="Avión TAMep" class="tam-login-img">
        <div class="tam-login-overlay"></div>
        <div class="tam-login-divider"></div>
    </div>

</div>
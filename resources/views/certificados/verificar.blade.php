<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación de Certificado - TAM / CIAC</title>
    <style>
        :root { 
            --azul-principal: #19499C; 
            --azul-oscuro: #0f2c5e;
            --amarillo-tam: #FFCD05; 
            --texto-principal: #1e293b;
        }
        
        * { box-sizing: border-box; }
        
        body { 
            margin: 0; 
            min-height: 100vh; 
            display: grid; 
            place-items: center; 
            padding: 30px 16px; 
            color: var(--texto-principal); 
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; 
            position: relative;
            overflow-x: hidden;
        }

        /* Capa de fondo con la imagen difuminada (blur) */
        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url("{{ asset('images/fondo-tam.webp') }}") no-repeat center center;
            background-size: cover;
            
            filter: blur(5px);
            transform: scale(1.05);
            z-index: -1;
        }

        /* Tarjeta Principal */
        .certificate-card { 
            width: min(720px, 100%); 
            background: #ffffff; 
            border-radius: 16px; 
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35);
            border: 1px solid rgba(0, 0, 0, 0.1);
            position: relative;
            z-index: 1;
        }

        /* 1. Franja Azul Superior */
        .header-banner { 
            background: linear-gradient(135deg, var(--azul-oscuro) 0%, var(--azul-principal) 100%); 
            color: white; 
            padding: 24px 30px;
            text-align: center;
            border-bottom: 5px solid var(--amarillo-tam);
        }

        .header-banner h1 { 
            margin: 0 0 4px; 
            font-size: 22px; 
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .header-banner p { 
            margin: 0; 
            opacity: 0.88; 
            font-size: 13px;
            font-weight: 300;
        }

        /* 2. Sección de Logos y Badge Centrado */
        .brand-status-bar {
            padding: 28px 24px 16px;
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            align-items: center;
            gap: 16px;
            background: #ffffff;
        }

        .logo-box {
            display: flex;
            align-items: center;
        }

        .logo-box.left {
            justify-content: flex-start;
        }

        .logo-box.right {
            justify-content: flex-end;
        }

        .logo-tam,
        .logo-ciac {
            width: 170px;
            max-width: 100%;
            height: auto;
            max-height: 75px;
            object-fit: contain;
        }

        /* Badge de Estado Centrado */
        .badge-container {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .badge-status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 50px;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.2px;
            white-space: nowrap;
        }

        .badge-valid {
            background: #f0fdf4;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }

        .badge-invalid {
            background: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }

        /* 3. Contenido de Datos */
        .certificate-body { 
            padding: 10px 30px 32px; 
            background: #ffffff;
        }

        .data-grid { 
            display: grid; 
            grid-template-columns: 140px 1fr; 
            gap: 14px 24px; 
            background: #f8fafc;
            padding: 22px;
            border-radius: 12px;
            border: 1px solid #f1f5f9;
        }

        .data-grid dt { 
            color: #64748b; 
            font-size: 13px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
        } 

        .data-grid dd { 
            margin: 0; 
            font-weight: 600; 
            font-size: 15px;
            color: #0f172a;
        }

        .code-highlight { 
            color: var(--azul-principal); 
            font-family: 'Courier New', Courier, monospace; 
            font-size: 16px;
            font-weight: 700;
            letter-spacing: 1px;
        }

        /* Código QR */
        .qr-section {
            text-align: center;
            margin-top: 24px;
            padding-top: 18px;
            border-top: 1px solid #f1f5f9;
        }

        .qr-image { 
            display: inline-block; 
            width: 135px; 
            height: 135px;
            padding: 8px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.04);
        }

        .footer-note {
            text-align: center;
            font-size: 11px;
            color: #94a3b8;
            margin-top: 12px;
        }

        /* Responsive Móvil */
        @media (max-width: 650px) {
            .brand-status-bar {
                grid-template-columns: 1fr;
                gap: 16px;
                justify-items: center;
            }
            .logo-box.left,
            .logo-box.right {
                justify-content: center;
            }
            .data-grid {
                grid-template-columns: 1fr;
                gap: 6px 0;
            }
            .data-grid dt {
                font-size: 11px;
            }
            .data-grid dd {
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>

    <main class="certificate-card">
        
        <header class="header-banner">
            <h1>Verificación de Certificado</h1>
            <p>Sistema de Verificación e Idoneidad Documental</p>
        </header>

        <div class="brand-status-bar">
            <div class="logo-box left">
                <img src="{{ asset('images/logo-tam.png') }}" alt="Logo TAM" class="logo-tam">
            </div>

            <div class="badge-container">
                @if ($certificado && $certificado->estado_cer === 'activo')
                    <span class="badge-status badge-valid">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        Certificado Auténtico y Válido
                    </span>
                @elseif ($certificado)
                    <span class="badge-status badge-invalid">✕ Anulado</span>
                @else
                    <span class="badge-status badge-invalid">✕ No Encontrado</span>
                @endif
            </div>

            <div class="logo-box right">
                <img src="{{ asset('images/logo-ciac.png') }}" alt="Logo CIAC" class="logo-ciac">
            </div>
        </div>

        <section class="certificate-body">
            
            @if ($certificado && $certificado->estado_cer === 'activo')
                
                <dl class="data-grid">
                    <dt>Participante</dt>
                    <dd>{{ $certificado->nombreCompleto() }}</dd>

                    <dt>Curso / Título</dt>
                    <dd>{{ $certificado->curso_cer }}</dd>

                    <dt>Docente / Instructor</dt>
                    <dd>{{ $certificado->docente_cer }}</dd>

                    <dt>Fecha Emisión</dt>
                    <dd>{{ $certificado->fecha_cer->format('d/m/Y') }}</dd>

                    <dt>Código Único</dt>
                    <dd class="code-highlight">{{ $certificado->codigo_cer }}</dd>
                </dl>

                <div class="qr-section">
                    <img class="qr-image" src="{{ route('certificados.qr', $certificado->codigo_cer) }}" alt="Código QR de verificación">
                    <p class="footer-note">Documento firmado e identificado electrónicamente por TAMep / CIAC</p>
                </div>

            @elseif ($certificado)
                
                <p style="text-align: center; color: #64748b; font-size: 14px; margin-top: 10px;">
                    Este registro de certificado existía pero ha sido dado de baja en el sistema.
                </p>
                <p class="code-highlight" style="text-align: center; margin-top: 12px;">{{ $certificado->codigo_cer }}</p>

            @else
                
                <p style="text-align: center; color: #64748b; font-size: 14px; margin-top: 10px;">
                    El código escaneado no corresponde a ningún certificado registrado en nuestra base de datos oficial.
                </p>

            @endif

        </section>
    </main>

</body>
</html>
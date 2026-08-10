<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación de certificado</title>
    <style>
        :root { --azul: #19499C; --amarillo: #FFCD05; }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 24px; background: #f4f7fc; color: #172033; font-family: Arial, sans-serif; }
        .card { width: min(640px, 100%); overflow: hidden; background: #fff; border-radius: 18px; box-shadow: 0 16px 45px #19499c1f; }
        .header { padding: 28px 34px; background: var(--azul); color: white; border-bottom: 7px solid var(--amarillo); }
        .header h1 { margin: 0 0 6px; font-size: 24px; }
        .header p { margin: 0; opacity: .85; }
        .content { padding: 34px; }
        .success { color: #167044; font-weight: bold; }
        .invalid { color: #b42318; font-weight: bold; }
        dl { display: grid; grid-template-columns: 150px 1fr; gap: 14px 20px; margin: 24px 0 0; }
        dt { color: #667085; } dd { margin: 0; font-weight: 600; }
        .qr { display: block; width: 145px; margin: 30px auto 0; }
        .code { color: var(--azul); font-family: monospace; }
    </style>
</head>
<body>
    <main class="card">
        <header class="header">
            <div style="display: flex; justify-content: space-between; align-items: center; gap: 16px; margin-bottom: 16px;">
                <img src="{{ asset('images/logo-tam.png') }}" 
                    alt="Logo TAM" 
                    style="height: 52px; width: auto; max-width: 180px; object-fit: contain;">

                <img src="{{ asset('images/logo-ciac.png') }}" 
                    alt="Logo CIAC" 
                    style="height: 52px; width: auto; max-width: 180px; object-fit: contain;">
            </div>

            <h1>Verificación de certificado</h1>
            <p>Sistema de certificados - TAM / CIAC</p>
        </header>
        <section class="content">
            @if ($certificado && $certificado->estado_cer === 'activo')
                <p class="success">✓ Certificado válido</p>
                <dl>
                    <dt>Participante</dt><dd>{{ $certificado->nombreCompleto() }}</dd>
                    <dt>Curso</dt><dd>{{ $certificado->curso_cer }}</dd>
                    <dt>Docente</dt><dd>{{ $certificado->docente_cer }}</dd>
                    <dt>Fecha de emisión</dt><dd>{{ $certificado->fecha_cer->format('d/m/Y') }}</dd>
                    <dt>Código</dt><dd class="code">{{ $certificado->codigo_cer }}</dd>
                </dl>
                <img class="qr" src="{{ route('certificados.qr', $certificado->codigo_cer) }}" alt="Código QR del certificado">
            @elseif ($certificado)
                <p class="invalid">Este certificado fue anulado y no es válido.</p>
                <p class="code">{{ $certificado->codigo_cer }}</p>
            @else
                <p class="invalid">No se encontró un certificado con este código.</p>
            @endif
        </section>
    </main>
</body>
</html>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso denegado</title>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        html, body {
            height: 100%;
            width: 100%;
        }
        
        body {
            background-color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
        }
        
        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 100%;
            max-width: 800px;
            padding: 1rem;
        }
        
        .title {
            font-size: 4rem;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 0.5rem;
            text-align: center;
        }
        
        .subtitle {
            font-size: 1.5rem;
            color: #666;
            margin-bottom: 2rem;
            text-align: center;
            font-weight: 400;
        }
        
        .image-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
        }
        
        .image-wrapper img {
            max-width: 100%;
            max-height: 65vh;
            width: auto;
            height: auto;
            object-fit: contain;
            display: block;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="title">Error 403</h1>
        <p class="subtitle">No tienes permiso para ver esta página</p>
        <div class="image-wrapper">
            <img 
                src="{{ asset('images/403.webp') }}" 
                alt="Acceso denegado"
            >
        </div>
    </div>
</body>
</html>
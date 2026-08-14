<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso denegado</title>
    
    <style>
        /* Reset completo */
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
            background-color: #030712;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            overflow: hidden; /* Elimina scrollbars */
        }
        
        .container {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100vh;
            padding: 2rem;
        }
        
        .image-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
        }
        
        .image-wrapper img {
            max-width: 90%;
            max-height: 90vh;
            width: auto;
            height: auto;
            object-fit: contain;
            display: block;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="image-wrapper">
            <img 
                src="{{ asset('images/403.webp') }}" 
                alt="Acceso denegado"
            >
        </div>
    </div>
</body>
</html>
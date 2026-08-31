# Sistema de certificados QR

Aplicación sencilla para emitir certificados y verificarlos mediante un código QR. Está construida con Laravel 13, Filament 4 y MySQL.

## Funciones

- Inicio de sesión con los usuarios de la tabla `usuarios`.
- Registro, edición, búsqueda y anulación de certificados.
- Verificación pública desde la dirección incluida en cada código QR.
- Tres cursos fijos, configurables sin crear un módulo adicional.

## Requisitos

- PHP 8.3 o superior, con las extensiones `pdo_mysql`, `mbstring`, `xml`, `curl`, `intl` y `gd`.
- Composer 2.
- MySQL 8 o MariaDB 10.

## Instalación

1. Copia `.env.example` como `.env` y configura la conexión MySQL.

2. Instala las dependencias:

   ```bash
   composer install
   composer require endroid/qr-code
   ```

3. Genera la clave de la aplicación y prepara las tablas:

   ```bash
   php artisan key:generate
   php artisan migrate --seed
   ```

4. Inicia el sistema:

   ```bash
   php artisan serve
   ```

Abre `http://127.0.0.1:8000/admin`. El usuario inicial es `admin@certificadoqr.test` y la contraseña es `password`; cámbiala antes de usar el sistema en producción.

## Cursos

Los cursos se definen en `config/courses.php`. Cada curso debe estar presente en `options` y tener su docente correspondiente en `teachers`.

Después de modificarlos, ejecuta:

```bash
php artisan optimize:clear
```

## Rutas principales

- `/admin`: administración de certificados.
- `/verificar/{codigo}`: comprobación pública de un certificado.
- `/qr/{codigo}`: imagen SVG del código QR.

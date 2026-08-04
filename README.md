# CertificadoQr

Aplicación web construida con **Laravel 13**, **Vite** y **Tailwind CSS 4** para la gestión de
certificados con verificación por código QR.

## Requisitos previos

Antes de clonar el proyecto, instala en tu equipo:

- **PHP 8.4.24**
- **Composer 2.x**
- **Node.js 18+** (con npm)
- **Git**

Consulta el archivo [`REQUIREMENTS.md`](./REQUIREMENTS.md) para la lista completa y detallada
(extensiones de PHP, base de datos, enlaces de descarga y comandos de instalación por sistema
operativo).

## Instalación

1. **Clonar el repositorio**
   ```bash
   git clone https://github.com/jhairp/CertificadoQr.git
   cd CertificadoQr
   ```

2. **Instalar las dependencias de PHP**
   ```bash
   composer install
   ```

3. **Instalar las dependencias de JavaScript**
   ```bash
   npm install
   ```

4. **Crear el archivo de entorno**
   ```bash
   cp .env.example .env
   ```

5. **Generar la clave de la aplicación**
   ```bash
   php artisan key:generate
   ```

6. **Configurar la base de datos**

   Por defecto el proyecto usa **SQLite**. Crea el archivo de la base de datos:
   ```bash
   touch database/database.sqlite
   ```
   (En Windows PowerShell: `New-Item database/database.sqlite`)

   Si prefieres MySQL o PostgreSQL, edita las variables `DB_*` en tu archivo `.env` en lugar de
   usar SQLite.

7. **Ejecutar las migraciones**
   ```bash
   php artisan migrate
   ```

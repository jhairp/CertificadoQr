# Requerimientos del sistema — CertificadoQr

Este proyecto está construido con **Laravel 13** (PHP) en el backend y **Vite + Tailwind CSS 4**
para los assets del frontend. Antes de clonar y ejecutar el proyecto, cada persona debe instalar
lo siguiente en su equipo.

## 1. Software obligatorio

| Herramienta | Versión mínima | Para qué se usa | Descarga |
|---|---|---|---|
| **PHP** | 8.4.24 o superior | Ejecutar el framework Laravel | https://www.php.net/downloads |
| **Composer** | 2.x | Instalar las dependencias PHP (`composer.json`) | https://getcomposer.org/download/ |
| **Node.js** | 18 LTS o superior (recomendado 20+) | Compilar los assets con Vite | https://nodejs.org/ |
| **npm** | Incluido con Node.js | Instalar dependencias JS (`package.json`) | Incluido con Node.js |
| **Git** | Cualquier versión reciente | Clonar el repositorio | https://git-scm.com/downloads |

### Extensiones de PHP requeridas

Laravel 13 necesita que estas extensiones estén habilitadas en tu `php.ini` (la mayoría vienen
activadas por defecto en instalaciones estándar de PHP):

- BCMath
- Ctype
- cURL
- DOM
- Fileinfo
- Filter
- JSON
- Mbstring
- OpenSSL
- PCRE
- PDO
- PDO_SQLite (si usarás SQLite, la opción por defecto del proyecto)
- Session
- Tokenizer
- XML

> Tip: puedes verificar qué extensiones tienes activas con `php -m`.

## 2. Base de datos

Por defecto el proyecto usa **SQLite** (no requiere instalar un servidor de base de datos aparte,
solo la extensión `pdo_sqlite` de PHP). Si prefieres usar otro motor, instala uno de estos y
ajusta las variables `DB_*` en tu archivo `.env`:

- MySQL 8.x / MariaDB 10.x, o
- PostgreSQL 13+

## 3. Opcional pero recomendado

- **Composer global** en el PATH del sistema (para poder correr `composer` desde cualquier carpeta).
- **Laravel Herd**, **Laravel Sail** (Docker) o **XAMPP/WAMP/MAMP** si prefieres un entorno
  preconfigurado en lugar de instalar PHP/Composer manualmente.
- Un editor con soporte para PHP/Blade/JS, como VS Code (con extensiones de Laravel).

## 4. Resumen rápido de instalación por sistema operativo

**Windows:** instala PHP con [Laravel Herd](https://herd.laravel.com/) o XAMPP, luego Composer,
luego Node.js (instalador oficial), luego Git.
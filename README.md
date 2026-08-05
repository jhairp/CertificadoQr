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

6. **Configurar la base de datos (MySQL)**

   El proyecto usa **MySQL**. Crea la base de datos vacía (el nombre debe coincidir con
   `DB_DATABASE` en tu `.env`):
   ```sql
   CREATE DATABASE certificadoqr CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

   Luego revisa/ajusta en tu `.env` las variables de conexión:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=certificadoqr
   DB_USERNAME=root
   DB_PASSWORD=
   ```

7. **instalar filament**
   ```bash
   En C:\www\php8\php.ini debes cambiar ;extension=intl a extension=intl 
   luego ejecutas:

   composer require "filament/filament:~4.0"
   ```
8. **Ejecutar las migraciones**
   ```bash
   php artisan migrate
   ```
   Esto crea las tablas `roles`, `usuarios` y `certificados` (ver estructura abajo). Si además
   quieres los datos base (roles `Administrador`/`Registrador` y un usuario admin de prueba:
   `admin@certificadoqr.test` / `password`), corre:
   ```bash
   php artisan migrate --seed
   ```

9. **Instalar la librería de generación de códigos QR (backend)**

   Las vistas de previsualización ya generan un QR en el navegador (vía CDN, solo para
   maquetar), pero para generar y guardar el QR real del certificado en el servidor se
   necesita un paquete PHP. Se recomienda `endroid/qr-code` (activamente mantenido y
   compatible con PHP 8.4 / Laravel 13):
   ```bash
   composer require endroid/qr-code
   ```
   Requiere la extensión `gd` (o `imagick`) habilitada en tu `php.ini`. Consulta
   [`REQUIREMENTS.md`](./REQUIREMENTS.md) para más detalle.

10. **Compilar los assets del frontend**
    ```bash
    npm run dev
    ```
    (o `npm run build` para producción).

## Vistas de previsualización (mockups)

Antes de conectar el proyecto a la base de datos, ya se pueden revisar en el navegador las
pantallas principales con datos de ejemplo (definidos directamente en `routes/web.php`, sin
Modelos ni Controllers todavía):

| Ruta                       | Qué muestra                                                                 |
|-----------------------------|------------------------------------------------------------------------------|
| `/dashboard`                | Cursos impartidos y cantidad de certificados emitidos por curso.            |
| `/cursos`                   | Listado de cursos registrados.                                              |
| `/cursos/registrar`         | Formulario para registrar un curso nuevo.                                   |
| `/certificados`             | Listado de certificados/estudiantes, cada uno con su QR.                    |
| `/certificados/registrar`   | Formulario para registrar un certificado; genera el QR en vivo al escribir. |
| `/certificados/verificar/{codigo}` | Página pública que se abriría al escanear el QR de un certificado.   |

Estas vistas usan un banner de "Vista previa (mockup)" para dejar claro que los datos son de
ejemplo y que los formularios todavía no guardan nada. Cuando se implementen las migraciones,
Modelos (`Curso`, `Certificado`) y Controllers, solo hay que reemplazar los arrays de
`routes/web.php` por consultas reales; las vistas Blade ya quedan listas para recibir esos
datos porque usan la misma estructura de campos.
## Estructura de la base de datos

Definida en `database/migrations/` y mapeada con los modelos `App\Models\Rol`, `App\Models\Usuario`
y `App\Models\Certificado` (tablas y columnas en español, sin los nombres por defecto de Laravel):

**`roles`**
| Columna    | Tipo               | Notas |
|------------|--------------------|-------|
| `id_rol`   | bigint, PK, auto   | |
| `nom_rol`  | string(50)         | |

**`usuarios`**
| Columna       | Tipo                       | Notas |
|---------------|----------------------------|-------|
| `id_usu`      | bigint, PK, auto           | |
| `nombre_usu`  | string(100)                | |
| `correo_usu`  | string(150), único         | |
| `password_usu`| string, hash automático    | cast `hashed` en el modelo |
| `estado_usu`  | boolean, default `true`    | activo / inactivo |
| `id_rol_1`    | FK → `roles.id_rol`        | `restrictOnDelete` |

**`certificados`**
| Columna           | Tipo                     | Notas |
|-------------------|--------------------------|-------|
| `id_cer`          | bigint, PK, auto         | |
| `codigo_cer`      | string(40), único        | se usa también para el QR |
| `nombre_per_cer`  | string(100)              | nombre del estudiante |
| `apellido_per_cer`| string(100)              | apellido del estudiante |
| `carnet_per_cer`  | string(20)               | carnet / documento |
| `docente_cer`     | string(150)              | nombre del docente (texto libre) |
| `curso_cer`       | string(150)              | nombre del curso (texto libre) |
| `fecha_cer`       | date                     | |
| `estado_cer`      | string(20), default `activo` | p. ej. `activo` / `anulado` |
| `id_usu_1`        | FK → `usuarios.id_usu`   | usuario que registró el certificado, `restrictOnDelete` |

> Nota: en este esquema `curso_cer` y `docente_cer` son campos de texto libre dentro de
> `certificados`, no tablas separadas. Si más adelante quieres reutilizar el mismo curso en
> varios certificados sin repetir texto (y así evitar errores de tipeo), se puede extraer una
> tabla `cursos` aparte — avísame si quieres que la agregue.

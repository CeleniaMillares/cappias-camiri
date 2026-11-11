# CAPPIAS Camiri - Sistema de Gestion

Sistema de gestion para la asociacion de artesanos: usuarios y roles, socios, ventas, cursos, inscripciones, ferias y reportes.

## Requisitos

- PHP 8.2 o superior
- Composer
- Node.js y npm para compilar los recursos
- Una base de datos configurada en `.env` (SQLite o PostgreSQL)

## Instalacion local

1. Instalar dependencias, crear `.env`, generar la clave y ejecutar migraciones:

   ```bash
   composer setup
   ```

2. Cargar roles, usuarios iniciales, categorias y parametros:

   ```bash
   php artisan db:seed
   ```

   Los seeders base se pueden ejecutar mas de una vez sin duplicar roles, usuarios, categorias ni parametros. Las cuentas de ejemplo (`admin@cappias.com`, `directiva@cappias.com`, etc.) son solo para desarrollo; cambia sus contrasenas antes de usar el sistema.

3. Iniciar la aplicacion:

   ```bash
   composer run dev
   ```

## Pruebas

```bash
php artisan test
```

## Respaldos

Los respaldos manuales se gestionan desde **Administrador > Respaldos** y se guardan en el disco local dentro de `storage/app/private/<APP_NAME>`. Incluyen la conexión configurada en `DB_CONNECTION` y los archivos del proyecto, incluidos los archivos públicos de `storage/app/public`. La limpieza conserva todos los respaldos de los últimos 7 días.

Para PostgreSQL, `pg_dump` debe estar disponible en el `PATH` o se debe configurar `PG_DUMP_BINARY_PATH` en `.env` con la ruta al directorio `bin` de PostgreSQL, por ejemplo:

```dotenv
PG_DUMP_BINARY_PATH='C:\Program Files\PostgreSQL\16\bin'
```

Laravel agenda el respaldo diario a las 02:00 y la limpieza a las 02:30. Para ejecutar esas tareas, configura el planificador del servidor para invocar `php artisan schedule:run` cada minuto. En Windows, crea una tarea del Programador de tareas que ejecute PHP con el argumento `artisan schedule:run` y el directorio de trabajo del proyecto.

Configura `BACKUP_ARCHIVE_PASSWORD` en `.env` para cifrar los archivos de respaldo antes de usarlos en producción.

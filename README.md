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

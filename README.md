## Reservations API

API para gestionar espacios y reservas, con autenticación por JWT, roles, validaciones de tiempo y pruebas automatizadas.

## Requisitos

PHP 8.1+
Composer
PostgreSQL

## Instalar Dependencias

`composer install`

## Configuración Inicial !!importante

Copiar el archivo de entorno, o utilizar de preferencia
`cp .env.example .env`
Este archivo es importante, ya que aquí se configura la conexión a la DB
He colocado como time zone por defecto 'America/Guayaquil' en caso de necesitar editar ir a config/app.php
En el seeder esta el usuario admin
 - correo: admin@admin.com
 - password: password123


# variables de ejemplo:

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=reservations_db
DB_USERNAME=postgres
DB_PASSWORD=tu_password

# generar la key de la app

`php artisan key:generate`

# correr migraciones

`php artisan migrate`

# correr datos de prueba

`php artisan db:seed`

# Levantar el proyecto

`php artisan serve`

## Autenticación (JWT)

-   Mensajes genéricos en errores de login para complicar posibles ataques

# Configurar JWT

`php artisan jwt:secret`

## Roles

Existen dos roles:

# ADMIN

-   ConsultarCrear, editar y eliminar Spaces
-   Acceso a sus reservas

# USER

-   Crear, editar y eliminar sus propias reservas
-   Consultar espacios disponibles

# Algunas rutas están restringidas según el rol.

## Espacios (Spaces)

Los espacios tienen:

-   Horario disponible (available_from, available_to)
-   Capacidad
-   Estado activo/inactivo
-   Las reservas solo pueden crearse dentro del horario del espacio y en bloques de 30 minutos

## Reservas (Reservations)

Reglas importantes:

-   Solo puedes ver y modificar tus propias reservas
-   No se permiten reservas que se crucen en el mismo espacio
-   El horario debe respetar:
    -   Disponibilidad del espacio
    -   Bloques de 30 minutos
-   El token dura 2h

## Tests (PHPUnit)

El proyecto incluye pruebas feature con PHPUnit.
Para ejecutarlos:
`php artisan test`
los test cubren:

-   autenticación
-   crud de espacios para administradores
-   creación de reservas
-   validar accesos

## Documentación (Swagger)

La API está documentada con Swagger (OpenAPI).
Para poder ver:

1. `php artisan serve`
2. Ir a la ruta [http://127.0.0.1:8000/api/documentation]

## Autor

Xavier Decker
https://www.github.com/xdecker

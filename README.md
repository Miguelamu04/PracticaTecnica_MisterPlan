# MisterPlan — Gestión de reservas

Aplicación web desarrollada en PHP nativo para gestionar reservas de alojamientos. El proyecto forma parte de la prueba técnica de MisterPlan.

La aplicación permite consultar reservas, aplicar filtros, crear nuevas reservas, cancelar reservas y visualizar su historial de eventos.

El objetivo ha sido desarrollar una solución sencilla y funcional, dando prioridad a la organización del código, la validación de los datos y la seguridad de las operaciones.

## Tecnologías utilizadas

- **PHP 8.3:** desarrollo del backend sin frameworks.
- **MySQL/MariaDB:** almacenamiento de reservas y eventos.
- **PDO:** acceso a la base de datos mediante consultas preparadas.
- **Twig:** renderizado de las vistas HTML.
- **JavaScript:** comunicación con la API mediante `fetch`, sin recargar la página.
- **HTML5 y CSS3:** estructura y diseño adaptable.
- **Composer:** gestión de dependencias y carga automática de clases mediante PSR-4.

## Instalación en local

### 1. Requisitos

Para ejecutar el proyecto es necesario disponer de:

- PHP 8.3 o superior, con las extensiones necesarias para MySQL, Twig y las validaciones utilizadas.
- MySQL o MariaDB.
- Composer.
- Un entorno local como WAMP, MAMP o similar.

### 2. Descargar el proyecto

Clonar el repositorio:

```bash
git clone https://github.com/Miguelamu04/PracticaTecnica_MisterPlan.git
cd PracticaTecnica_MisterPlan
```

### 3. Instalar las dependencias

Desde la raíz del proyecto:

```bash
composer install
```

Este comando instala las dependencias registradas en `composer.lock` y genera la carga automática de las clases.

### 4. Configurar la base de datos

El archivo `database/database.sql` contiene la creación de las tablas, sus relaciones, los índices y los datos de ejemplo.

Se puede importar mediante HeidiSQL, phpMyAdmin o desde la consola de MySQL:

```bash
mysql -u root -p < database/database.sql
```

El script crea automáticamente la base de datos `misterplan_reservas_v2`, siempre que el usuario utilizado tenga los permisos necesarios.

Incluye ocho reservas iniciales, con diferentes estados, y sus correspondientes eventos.

Está preparado para una instalación inicial sobre una base de datos vacía. No está diseñado como una migración repetible sobre datos existentes.

### 5. Configurar las variables de entorno

Copiar el archivo `.env.example` y renombrar la copia como `.env`.

En Windows:

```powershell
Copy-Item .env.example .env
```

Configurar los valores según la instalación local:

```env
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=misterplan_reservas_v2
DB_USER=root
DB_PASSWORD=
```

El archivo `.env` contiene las credenciales de conexión y no debe subirse al repositorio.

### 6. Iniciar la aplicación

Desde la raíz del proyecto:

```bash
php -S 127.0.0.1:8081 -t public public/router.php
```

Abrir en el navegador:

`http://127.0.0.1:8081/reservations`

El archivo `public/router.php` permite utilizar las rutas de la aplicación y servir correctamente los archivos estáticos durante el desarrollo local.

## Funcionalidades

La aplicación dispone de tres vistas principales:

**Listado de reservas**

Muestra las reservas existentes, permite filtrarlas por estado, fechas y nombre del huésped, acceder a sus detalles y cancelarlas.

Los filtros funcionan mediante JavaScript y solicitudes a la API, sin necesidad de recargar la página.

**Crear reserva**

Incluye un formulario para registrar reservas nuevas. Los campos se validan en el servidor, independientemente de las comprobaciones realizadas por el navegador.

**Detalle de reserva**

Presenta la información de la reserva y el historial de eventos asociados, incluyendo su creación y los cambios de estado.

## API REST

La aplicación expone los siguientes endpoints:

| Método | Endpoint | Descripción |
|---|---|---|
| GET | `/api/reservations` | Obtener reservas |
| GET | `/api/reservations/{id}` | Consultar una reserva |
| POST | `/api/reservations` | Crear una reserva |
| PATCH | `/api/reservations/{id}/status` | Confirmar o cancelar una reserva |

### Filtros

El listado admite los siguientes parámetros, que pueden combinarse:

- `status`: `pendiente`, `confirmada` o `cancelada`.
- `start_date`: fecha mínima de entrada.
- `end_date`: fecha máxima de salida.
- `guest`: búsqueda parcial por nombre del huésped.

Los filtros de fechas seleccionan reservas cuya entrada es igual o posterior a `start_date` y cuya salida es igual o anterior a `end_date`. Es decir, cuando se indican ambas fechas, la estancia debe encontrarse completamente dentro del intervalo.

### Códigos HTTP

La API utiliza respuestas JSON y los siguientes códigos:

- `200 OK`: consulta o cambio de estado procesado correctamente.
- `201 Created`: reserva creada.
- `400 Bad Request`: datos o identificadores inválidos.
- `404 Not Found`: reserva o recurso inexistente.
- `405 Method Not Allowed`: método HTTP no permitido para una ruta reconocida.
- `500 Internal Server Error`: error interno inesperado.

Los errores de validación se devuelven mediante un objeto JSON con la propiedad `error`.

## Organización del código

El proyecto está organizado separando las responsabilidades principales:

| Directorio | Responsabilidad |
|---|---|
| `public/` | Punto de entrada, JavaScript y CSS |
| `src/Controllers/` | Gestión de peticiones HTTP y renderizado de páginas |
| `src/Services/` | Validaciones y operaciones de la aplicación |
| `src/Models/` | Consultas y modificaciones de la base de datos |
| `src/Database/` | Configuración de la conexión PDO |
| `views/` | Plantillas Twig |
| `database/` | Estructura SQL y datos de ejemplo |
| `tests/` | Pruebas de validación del servicio |

Se utiliza la carga automática PSR-4 para evitar incluir manualmente las clases.

## Decisiones técnicas

### PHP nativo

He utilizado PHP sin frameworks, tal como se solicita en la prueba. Para mantener el código organizado, he separado las rutas, los controladores, las validaciones y el acceso a la base de datos.

### Seguridad y validación

La validación se realiza en el servidor y no depende únicamente del formulario HTML.

Se comprueban campos obligatorios, tipos de datos, longitudes, formato de correo electrónico, fechas e importes.

Las consultas SQL utilizan PDO y parámetros preparados para evitar concatenar directamente los valores recibidos del usuario.

Las vistas Twig utilizan autoescaping HTML y JavaScript muestra los datos mediante `textContent` para evitar interpretar contenido externo como HTML.

Las credenciales se almacenan en `.env`, fuera del directorio público.

### Historial y transacciones

La aplicación dispone de dos tablas principales: `reserva` y `reserva_evento`.

Cuando se crea una reserva, se registra también un evento de creación. Cuando cambia su estado, se añade un evento que describe la modificación.

Estas operaciones se realizan mediante transacciones para evitar que una reserva se actualice sin guardar su evento correspondiente.

En los cambios de estado se utiliza `SELECT ... FOR UPDATE` para bloquear la fila durante la operación y reducir el riesgo de inconsistencias ante solicitudes simultáneas.

Si una reserva ya tiene el estado solicitado, no se genera un nuevo evento.

### Interfaz y JavaScript

He utilizado Twig para generar las páginas y JavaScript nativo para consumir la API mediante `fetch`.

Las operaciones muestran mensajes de carga, éxito o error.

Durante la creación y cancelación se bloquean los envíos duplicados. En los filtros se utiliza `AbortController` para evitar que una búsqueda anterior sobrescriba los resultados de otra más reciente.

## Pruebas

El proyecto incluye cuatro pruebas automatizadas básicas de validación del servicio.

Para ejecutarlas:

```bash
php tests/ReservationServiceTest.php
```

Las pruebas comprueban:

- Rechazo de una reserva sin campos obligatorios.
- Rechazo de fechas incorrectas.
- Rechazo de importes inválidos.
- Rechazo de notas que superan el límite permitido.

Estas pruebas se centran en validaciones que se ejecutan antes de acceder a MySQL. No constituyen una cobertura completa ni sustituyen las pruebas de integración.

También se han realizado pruebas manuales de los endpoints, creación de reservas, cambios de estado, registro de eventos, filtros y respuestas HTTP.

## Limitaciones y posibles mejoras

He priorizado los requisitos funcionales y la claridad del código frente a incorporar funcionalidades adicionales.

En un desarrollo posterior mejoraría los siguientes aspectos:

- **Autenticación y autorización:** incorporar control de usuarios y permisos para restringir el acceso a los datos y a las operaciones de modificación.
- **Paginación:** limitar el número de reservas devueltas por cada consulta.
- **Pruebas automatizadas:** ampliar la cobertura con pruebas de integración sobre una base de datos independiente.
- **Gestión de estados:** definir reglas de transición más específicas si los requisitos del negocio lo necesitan.
- **Búsqueda:** optimizar las consultas por nombre del huésped para conjuntos de datos grandes.
- **Observabilidad:** incorporar registros y métricas para detectar errores y problemas de rendimiento.

Actualmente la API admite cambios entre los estados `confirmada` y `cancelada`, siempre que el estado solicitado sea válido. Una modificación que no cambia el estado no genera eventos adicionales.

## Escalabilidad: 1.000 reservas por hora

Para soportar unas 1.000 reservas por hora, mantendría la separación entre controladores, servicios y persistencia, pero introduciría mejoras progresivas según el volumen de datos y las métricas observadas.

La primera sería incorporar **paginación en el listado**, evitando recuperar todas las reservas mediante `fetchAll()` en una única petición.

También revisaría los índices de MySQL según las consultas más habituales y sus planes de ejecución. La base de datos ya incluye índices para estados, fechas e identificación de reservas y eventos, aunque la búsqueda actual con `LIKE '%texto%'` necesitaría una estrategia diferente para mejorar su rendimiento a gran escala.

Mantendría las transacciones para asegurar que los cambios de estado y sus eventos se registran conjuntamente. Además, realizaría pruebas de carga para identificar posibles cuellos de botella en PHP, MySQL y el servidor web.

En función de los resultados, valoraría optimizar las consultas, ajustar los recursos del servidor y utilizar caché únicamente en las operaciones de lectura donde tenga sentido.

Considero importante medir primero el comportamiento real del sistema antes de introducir infraestructura adicional.

## Despliegue

La aplicación está desplegada en **alwaysdata** y se puede utilizar desde la siguiente dirección:

**URL pública:** https://misterplan-demo.alwaysdata.net/reservations

Para proteger el entorno de demostración, se ha configurado autenticación HTTP Basic.

**Credenciales de acceso:**
- **Usuario:** `evaluador`
- **Contraseña:** se facilitará por separado junto con la entrega de la prueba técnica.

La aplicación utiliza PHP 8.3 y una base de datos MySQL/MariaDB con ocho reservas ficticias y su correspondiente historial de eventos.

El directorio público del servidor apunta a `public/`, mientras que el código interno, las dependencias y las credenciales de conexión permanecen fuera del acceso directo desde Internet.

También se ha configurado HTTPS y la reescritura de URL mediante `.htaccess` para permitir el funcionamiento de las rutas de la aplicación y de la API.

Se ha comprobado en el servidor el funcionamiento del listado, los filtros, la consulta de detalles, la creación de reservas, la cancelación y el registro de eventos.

El servidor integrado de PHP se utiliza únicamente para desarrollo local, no para el despliegue público.
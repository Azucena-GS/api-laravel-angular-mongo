# Laravel + Angular + MongoDB — Plantilla Docker

Plantilla de desarrollo para crear aplicaciones web utilizando **Laravel, PHP, Angular, Node.js y MongoDB**, con Docker Compose como entorno de ejecución.

El objetivo es que puedas comenzar un proyecto sin instalar PHP, Composer, Node.js, Angular CLI ni MongoDB directamente en tu sistema operativo.

Funciona en Linux y Windows mediante Docker Desktop o Docker Engine.

## 1. Tecnologías incluidas

| Tecnología | Función |
|---|---|
| Docker Engine | Ejecuta los contenedores |
| Docker Compose | Administra los servicios del proyecto |
| PHP 8.3 | Lenguaje del backend |
| Laravel | Framework del backend y API |
| Composer | Administración de dependencias PHP |
| Extensión PHP MongoDB | Permite conectar PHP con MongoDB |
| Angular CLI | Creación y desarrollo del frontend |
| Node.js 22 | Entorno de ejecución de Angular |
| MongoDB 7.0 | Base de datos documental |
| MongoDB Compass | Opcional, administración gráfica de MongoDB |
| Visual Studio Code | Editor de código recomendado |
| Git | Control de versiones |

Las versiones de PHP, Node.js y MongoDB están definidas en los Dockerfile y en `compose.yaml`. Si modificas esos archivos, revisa también los requisitos de compatibilidad de las tecnologías correspondientes.

## 2. Arquitectura

La plantilla utiliza tres servicios principales:

- **Angular:** interfaz de usuario.
- **Laravel:** lógica de negocio y API REST.
- **MongoDB:** almacenamiento de documentos.

```text
Navegador web
     |
     v
Angular :4201
     |
     | Peticiones HTTP
     v
Laravel :8000
     |
     | Red interna de Docker
     v
MongoDB :27017
```

Los archivos del proyecto se encuentran en el equipo anfitrión y se comparten con los contenedores mediante volúmenes.

Esto permite editar el código desde Visual Studio Code sin reconstruir las imágenes cada vez que modificas un archivo.

## 3. Estructura del proyecto

```text
laravel-angular-mongo/
├── compose.yaml
├── README.md
├── .gitignore
├── backend/
│   ├── Dockerfile
│   ├── .env.example
│   ├── artisan
│   ├── app/
│   ├── config/
│   ├── routes/
│   ├── composer.json
│   └── ...
└── frontend/
    ├── Dockerfile
    ├── package.json
    ├── angular.json
    ├── src/
    └── ...
```

Los archivos `vendor/`, `node_modules/`, `dist/` y `.env` local no deben publicarse en GitHub.

## 4. Requisitos del equipo

### Hardware recomendado

| Recurso | Mínimo práctico | Recomendado |
|---|---|---|
| Procesador | 2 núcleos | 4 núcleos o más |
| RAM | 8 GB | 16 GB o más |
| Espacio libre | 15 GB | 25 GB o más |
| Conexión a Internet | Para descargar imágenes y paquetes | Banda ancha |

Los requisitos dependen del tamaño del proyecto, de las imágenes descargadas y de las demás aplicaciones que estén ejecutándose.

### Software requerido

**En Linux:**

- Una distribución Linux de 64 bits compatible con Docker Engine.
- Docker Engine.
- Plugin Docker Compose v2.
- Git.
- Visual Studio Code, opcional.

**En Windows:**

- Windows compatible con la versión actual de Docker Desktop.
- Docker Desktop con backend WSL 2 recomendado.
- Git para Windows.
- Visual Studio Code, opcional.

No es necesario instalar PHP, Composer, Node.js, Angular CLI ni MongoDB directamente en Windows o Linux. Estas herramientas se utilizan dentro de los contenedores.

## 5. Puertos utilizados

| Servicio | Puerto del equipo | Puerto del contenedor |
|---|---:|---:|
| Angular | 4201 | 4200 |
| Laravel | 8000 | 8000 |
| MongoDB | 27017 | 27017 |

Direcciones locales:

- Angular: http://localhost:4201
- Laravel: http://localhost:8000
- MongoDB: `localhost:27017`, únicamente desde el equipo anfitrión.

MongoDB se publica en `127.0.0.1`, por lo que no queda expuesto directamente a otros equipos de la red mediante esa publicación.

Si un puerto está ocupado, modifica el lado izquierdo de la asignación en `compose.yaml`. Por ejemplo:

```yaml
ports:
  - "4202:4200"
```

En este caso, Angular quedaría disponible en `http://localhost:4202`.

## 6. Instalación en Linux

### 6.1. Instalar Docker

Instala Docker Engine y el plugin Docker Compose siguiendo la documentación oficial:

https://docs.docker.com/engine/install/

Comprueba que estén disponibles:

```bash
docker --version
docker compose version
docker run --rm hello-world
```

Si tu usuario no tiene permisos para utilizar Docker, consulta la documentación oficial sobre permisos y grupos. Ten presente que pertenecer al grupo `docker` otorga privilegios equivalentes a los de administrador.

### 6.2. Clonar el repositorio

```bash
git clone git@github.com:facultadautodidacta/laravel-angular-mongo.git
cd laravel-angular-mongo
```

Si no tienes configurada una clave SSH en GitHub, puedes clonar usando la URL HTTPS:

```bash
git clone https://github.com/facultadautodidacta/laravel-angular-mongo.git
```

### 6.3. Configurar Laravel

Comprueba que exista el archivo de ejemplo:

```bash
ls backend/.env.example
```

Crea el archivo local:

```bash
cp backend/.env.example backend/.env
```

En `backend/.env`, configura las variables de MongoDB:

```dotenv
MONGODB_URI="mongodb://devroot:devpass@mongo:27017/laravel_app?authSource=admin"
MONGODB_DATABASE="laravel_app"
```

Conserva las demás variables de Laravel. No publiques el archivo `.env` real.

### 6.4. Preparar las dependencias

Construye las imágenes:

```bash
docker compose build
```

Instala las dependencias de Laravel:

```bash
docker compose run --rm --no-deps laravel composer install
```

Instala las dependencias de Angular:

```bash
docker compose run --rm --no-deps angular npm install
```

Genera la clave de la aplicación Laravel:

```bash
docker compose run --rm --no-deps laravel php artisan key:generate
```

### 6.5. Iniciar los servicios

```bash
docker compose up -d
```

Comprueba el estado:

```bash
docker compose ps
```

Consulta los registros si algún servicio no inicia:

```bash
docker compose logs --tail=100
```

Cuando los servicios estén activos, abre:

- http://localhost:4201
- http://localhost:8000
- http://localhost:8000/docs/api — documentación interactiva de Scramble.
- http://localhost:8000/docs/api.json — especificación OpenAPI.
- http://localhost:8000/api/libros — API REST para consultar, crear, actualizar y eliminar libros de la colección MongoDB `books`.
- http://localhost:8000/api/health — estado de la conexión a MongoDB.

El endpoint de libros responde con `{ "data": [...] }`. Si la colección `books` todavía no tiene documentos, devuelve una lista vacía. Scramble genera la documentación a partir de las rutas bajo `/api`.

Para cargar libros de ejemplo en MongoDB (el seeder no duplica datos si la colección ya contiene libros):

```bash
docker compose exec laravel php artisan db:seed --class='Database\Seeders\BooksSeeder'
```

## 7. Instalación en Windows

### 7.1. Instalar las herramientas

Instala:

- Docker Desktop: https://www.docker.com/products/docker-desktop/
- Git para Windows: https://git-scm.com/downloads/win
- Visual Studio Code: https://code.visualstudio.com/

Abre Docker Desktop y espera hasta que indique que Docker está funcionando.

### 7.2. Clonar el repositorio

Desde PowerShell:

```powershell
git clone git@github.com:facultadautodidacta/laravel-angular-mongo.git
cd laravel-angular-mongo
```

También puedes clonar por HTTPS si no utilizas SSH.

### 7.3. Preparar Laravel y Angular

Si trabajas desde PowerShell, crea el archivo de entorno:

```powershell
Copy-Item backend/.env.example backend/.env
```

Edita `backend/.env` y agrega:

```dotenv
MONGODB_URI="mongodb://devroot:devpass@mongo:27017/laravel_app?authSource=admin"
MONGODB_DATABASE="laravel_app"
```

Después ejecuta:

```powershell
docker compose build

docker compose run --rm --no-deps laravel composer install

docker compose run --rm --no-deps angular npm install

docker compose run --rm --no-deps laravel php artisan key:generate

docker compose up -d

docker compose ps
```

Si usas Git Bash o una terminal de WSL, también puedes utilizar los comandos de la sección de Linux.

## 8. Trabajar con Visual Studio Code

Abre la carpeta raíz del proyecto.

En Linux:

```bash
code .
```

En Windows, abre Visual Studio Code y selecciona **Archivo → Abrir carpeta**.

Selecciona la carpeta `laravel-angular-mongo`.

Extensiones recomendadas:

- PHP Intelephense.
- Angular Language Service.
- Docker.
- EditorConfig for VS Code.

Los cambios que hagas en los archivos compartidos estarán disponibles en los contenedores. No necesitas abrir un editor dentro de Docker.

## 9. Comandos frecuentes de Laravel

Ejecuta los comandos desde la raíz del proyecto.

Consultar comandos disponibles:

```bash
docker compose exec laravel php artisan list
```

Consultar rutas:

```bash
docker compose exec laravel php artisan route:list
```

Crear un modelo:

```bash
docker compose exec laravel php artisan make:model Product
```

Crear un controlador:

```bash
docker compose exec laravel php artisan make:controller Api/ProductController --api
```

Limpiar la configuración cacheada:

```bash
docker compose exec laravel php artisan config:clear
```

Instalar un paquete:

```bash
docker compose exec laravel composer require nombre/paquete
```

Instalar dependencias del proyecto:

```bash
docker compose exec laravel composer install
```

Generar la clave de Laravel:

```bash
docker compose exec laravel php artisan key:generate
```

Los comandos `exec` requieren que el contenedor esté en ejecución. Para ejecutar un comando sin mantener el servicio activo, utiliza `docker compose run --rm`.

## 10. Comandos frecuentes de Angular

Consultar la versión:

```bash
docker compose exec angular npx ng version
```

Crear un componente:

```bash
docker compose exec angular npx ng generate component pages/products
```

Crear un servicio:

```bash
docker compose exec angular npx ng generate service services/products
```

Instalar una dependencia:

```bash
docker compose exec angular npm install nombre-paquete
```

Consultar los scripts de `package.json`:

```bash
docker compose exec angular npm run
```

Si agregas o modificas dependencias, actualiza `package.json` y el archivo de bloqueo correspondiente para mantener reproducibles las instalaciones.

## 11. Conexión y administración de MongoDB

### Credenciales de desarrollo

| Propiedad | Valor |
|---|---|
| Usuario | `devroot` |
| Contraseña | `devpass` |
| Base de datos de aplicación | `laravel_app` |
| Base de autenticación | `admin` |
| Host desde Laravel | `mongo` |
| Puerto interno | `27017` |

Estas credenciales son exclusivamente para desarrollo. Deben cambiarse antes de usar la aplicación en un entorno real.

### Abrir MongoDB Shell

```bash
docker compose exec mongo mongosh \
  -u devroot \
  -p devpass \
  --authenticationDatabase admin
```

Dentro de `mongosh`:

```javascript
show dbs
use laravel_app
db.products.find()
```

Salir:

```javascript
exit
```

### Conectar MongoDB Compass

Descarga MongoDB Compass:

https://www.mongodb.com/try/download/compass

Utiliza esta URI en la aplicación gráfica:

```text
mongodb://devroot:devpass@localhost:27017/?authSource=admin
```

### Comprobar la conexión desde Laravel

```bash
docker compose exec laravel php artisan tinker --execute="dump(DB::connection('mongodb')->getClient()->selectDatabase('admin')->command(['ping' => 1])->toArray());"
```

Una respuesta con `"ok" => 1.0` confirma que la conexión y la autenticación funcionan.

Para utilizar modelos MongoDB, revisa que `config/database.php` contenga una conexión con el driver `mongodb` y que el paquete `mongodb/laravel-mongodb` esté instalado.

## 12. Administración de Docker Compose

Iniciar servicios:

```bash
docker compose up -d
```

Reconstruir imágenes y arrancar servicios:

```bash
docker compose up -d --build
```

Ver contenedores:

```bash
docker compose ps
```

Ver registros:

```bash
docker compose logs -f
```

Reiniciar un servicio:

```bash
docker compose restart laravel
```

Detener los servicios del proyecto:

```bash
docker compose down
```

El comando anterior detiene y elimina los contenedores y la red de este proyecto, pero conserva los volúmenes nombrados por defecto.

**No ejecutes `docker compose down -v` salvo que realmente quieras eliminar los volúmenes y sus datos.** En esta plantilla, el volumen de MongoDB se llama `mongodb_data`.

## 13. Persistencia de MongoDB

La base de datos utiliza un volumen Docker:

```yaml
volumes:
  mongodb_data:
```

Los datos se conservan al recrear el contenedor mientras no elimines el volumen.

Para comprobar los volúmenes:

```bash
docker volume ls
```

El nombre habitual del volumen generado por Compose es:

```text
laravel-angular-mongo_mongodb_data
```

El nombre puede cambiar si se modifica el nombre del proyecto o se declara explícitamente otro nombre de volumen.

Para respaldar datos importantes, utiliza una estrategia de copias de seguridad. No consideres el volumen por sí solo como un respaldo.

## 14. Solución de problemas

### Un puerto está ocupado

Consulta qué proceso escucha en un puerto Linux:

```bash
sudo ss -ltnp 'sport = :4200'
```

Cambia el puerto externo en `compose.yaml` y vuelve a crear el servicio:

```bash
docker compose up -d angular
```

En Windows, puedes consultar puertos con:

```powershell
Get-NetTCPConnection -LocalPort 4200 -State Listen
```

### Laravel no inicia

Consulta los registros:

```bash
docker compose logs --tail=100 laravel
```

Comprueba que existan `backend/.env`, `backend/vendor/` y `backend/artisan`.

Si faltan las dependencias:

```bash
docker compose run --rm --no-deps laravel composer install
```

### Angular no inicia

Consulta:

```bash
docker compose logs --tail=100 angular
```

Si faltan dependencias:

```bash
docker compose run --rm --no-deps angular npm install
```

### MongoDB no inicia

Consulta:

```bash
docker compose logs --tail=100 mongo
```

Esta plantilla utiliza MongoDB 7.0 porque determinadas versiones de MongoDB presentan incompatibilidades con kernels Linux 6.19 y posteriores.

No cambies a otra versión sin verificar compatibilidad con el kernel y con la aplicación.

### Error de autenticación o SSL desde PHP

Comprueba el soporte SSL de la extensión:

```bash
docker compose exec laravel php --ri mongodb
```

Busca una línea que indique:

```text
libmongoc SSL => enabled
```

Si aparece deshabilitado, reconstruye la imagen de PHP con las dependencias de OpenSSL y la opción de compilación apropiada para la versión de la extensión instalada.

### MongoDB funciona, pero Laravel no se conecta

Comprueba que el host sea `mongo`, no `localhost`, cuando Laravel se ejecuta dentro de Docker.

Verifica también que la URI incluya:

```text
authSource=admin
```

Después limpia la configuración:

```bash
docker compose exec laravel php artisan config:clear
```

## 15. Seguridad

Esta plantilla está pensada para desarrollo local, no para producción.

- No publiques archivos `.env` con secretos.
- Cambia las contraseñas de ejemplo.
- No expongas MongoDB directamente a Internet.
- No uses el usuario administrador de MongoDB para operaciones ordinarias de la aplicación en producción.
- No incluyas `vendor/`, `node_modules/`, datos de la base de datos ni archivos privados en Git.
- Mantén Docker, las imágenes y las dependencias actualizados.
- Configura HTTPS, autenticación, autorización, validación y límites de recursos antes de desplegar.

## 16. Publicar cambios en GitHub

Desde la raíz del repositorio:

```bash
git status
git add .
git commit -m "Describe los cambios realizados"
git push
```

Revisa siempre `git status` antes de confirmar cambios para evitar publicar archivos sensibles.

## 17. Licencia y contribuciones

Añade una licencia al repositorio si deseas definir cómo otras personas pueden reutilizar, modificar y distribuir la plantilla.

Si deseas contribuir, crea una rama independiente, realiza tus cambios y abre un pull request.

---

**Repositorio:** https://github.com/facultadautodidacta/laravel-angular-mongo

**Objetivo:** proporcionar un entorno reproducible de desarrollo para aplicaciones Laravel + Angular + MongoDB utilizando Docker Compose, tanto en Linux como en Windows.

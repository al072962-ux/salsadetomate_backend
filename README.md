# Salsa de Tomate Backend API

Backend de una red social de recetas de cocina (MVP), basado en Laravel, con foco en:

- recetas con estructura (ingredientes + pasos + categorías)
- galería de medios por receta con foto principal obligatoria
- calificaciones y reseñas (incluyendo invitados)
- funciones sociales básicas (likes, comentarios, follows, colecciones)
- recomendación diaria de recetas
- documentación API en README y Swagger/OpenAPI


## Soporte de base de datos

El sistema está preparado para **MySQL o MariaDB**.

- Conexión por defecto del framework: `mysql`
- Driver alternativo disponible: `mariadb`
- `SQLite` no se usa en la guía de instalación ni despliegue

Archivo clave de configuración:

- [config/database.php](/home/josemanuel/Desktop/salsadetomate_backend/config/database.php)

## Documentación API (dos formatos)

1. **README (esta guía)** con explicación funcional + ejemplos `curl`.
2. **Swagger UI local** para explorar y probar endpoints.

Rutas de documentación:

- UI Swagger: `http://localhost:8000/docs/swagger`
- OpenAPI YAML: `http://localhost:8000/docs/openapi.yaml`
- Archivo OpenAPI fuente: [docs/openapi.yaml](/home/josemanuel/Desktop/salsadetomate_backend/docs/openapi.yaml)

Rutas web para docs:

- [routes/web.php](/home/josemanuel/Desktop/salsadetomate_backend/routes/web.php)
- [resources/views/docs/swagger.blade.php](/home/josemanuel/Desktop/salsadetomate_backend/resources/views/docs/swagger.blade.php)

## Bearer Auth en Swagger

El botón `Authorize` de Swagger UI te permite enviar el token Bearer y consumir rutas protegidas.

Flujo recomendado:

1. Ejecuta `POST /api/auth/register` o `POST /api/auth/login` desde Swagger.
2. Copia `access_token` de la respuesta.
3. Clic en `Authorize` (candado arriba).
4. Pega el token.
5. Ejecuta cualquier endpoint protegido (`/api/profile`, `/api/recipes`, etc.).

Nota:

- en Swagger normalmente pega el token sin prefijo.
- si no te autentica, prueba con `Bearer TU_TOKEN`.
- actualmente el backend maneja un solo token activo por usuario (si vuelves a login, invalida el token anterior).

## Arquitectura aplicada

Estructura orientada al dominio (Screaming Architecture), con controllers delgados:

- `app/Domain/Recipes` acciones de negocio de receta
- `app/Domain/Ratings` lógica de calificaciones
- `app/Domain/Feed` recomendación diaria
- `app/Http/Requests/Api/V1` validaciones
- `app/Http/Resources/Api/V1` respuesta consistente
- `app/Http/Controllers/Api/V1` capa de entrega
- `routes/api_v1.php` rutas API v1

## Reglas de negocio implementadas

1. Recetas pueden tener **mismo nombre** (no hay unicidad por título).
2. Una receta solo puede publicarse si tiene:

- al menos 1 ingrediente
- al menos 1 paso
- foto principal válida

3. El listado público permite filtros por:

- texto (`q`)
- rango de fecha (`published_from`, `published_to`)
- autor (`author_id`)
- categorías (`category_ids[]`)

4. Invitados pueden calificar recetas (`POST /api/recipes/{id}/ratings`).
5. Invitados pueden dejar comentarios (`POST /api/recipes/{id}/comments`) con campo opcional `guest_name`.
6. Se maneja rol `member`/`admin` con policies.
7. Recomendación diaria usa umbrales configurables:

- `RECIPES_DAILY_MIN_AVERAGE`
- `RECIPES_DAILY_MIN_RATINGS`

## Instalación (MySQL/MariaDB)

### 1) Clonar e instalar

```bash
composer install
cp .env.example .env
php artisan key:generate
```

### 2) Configurar `.env` para MySQL

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=salsadetomate
DB_USERNAME=salsa_user
DB_PASSWORD=tu_password
```

### 3) O configurar `.env` para MariaDB

```env
DB_CONNECTION=mariadb
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=salsadetomate
DB_USERNAME=salsa_user
DB_PASSWORD=tu_password
```

### 4) Crear base de datos y usuario (ejemplo SQL)

#### MySQL

```sql
CREATE DATABASE salsadetomate CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'salsa_user'@'%' IDENTIFIED BY 'tu_password';
GRANT ALL PRIVILEGES ON salsadetomate.* TO 'salsa_user'@'%';
FLUSH PRIVILEGES;
```

#### MariaDB

```sql
CREATE DATABASE salsadetomate CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'salsa_user'@'%' IDENTIFIED BY 'tu_password';
GRANT ALL PRIVILEGES ON salsadetomate.* TO 'salsa_user'@'%';
FLUSH PRIVILEGES;
```

### 5) Configurar CORS para tu frontend

Define orígenes permitidos en `.env`:

```env
CORS_ALLOWED_ORIGINS=http://localhost:5173,http://127.0.0.1:5173
```

Si tu frontend corre en otra URL/puerto (por ejemplo `http://localhost:3000`), agrégalo en esa variable separado por comas.

Archivo de CORS:

- [config/cors.php](/home/josemanuel/Desktop/salsadetomate_backend/config/cors.php)

### 6) Ejecutar migraciones

```bash
php artisan migrate
```

### 7) Cargar datos de prueba (demo)

```bash
php artisan db:seed
```

Si quieres recrear todo desde cero en desarrollo:

```bash
php artisan migrate:fresh --seed
```

Importante:

- `DemoDataSeeder` está pensado para entorno local.
- el seeder borra y regenera tablas del dominio (usuarios, recetas, categorías, ratings, etc.) para dejar datos consistentes.

### 8) Limpiar cache de configuración (cuando cambies `.env`)

```bash
php artisan optimize:clear
```

### 9) Exponer archivos subidos (obligatorio para ver imágenes)

```bash
php artisan storage:link
```

Este comando crea el enlace:

- `public/storage` -> `storage/app/public`

Sin ese enlace, tendrás `404` al abrir URLs como:

- `http://localhost:8000/storage/avatars/...`
- `http://localhost:8000/storage/recipes/...`

### 10) Levantar servidor

```bash
php artisan serve
```

Ahora puedes abrir Swagger en:

- `http://localhost:8000/docs/swagger`

## Datos de prueba (solo demo)

Los datos de prueba se generan en:

- [database/seeders/DemoDataSeeder.php](/home/josemanuel/Desktop/salsadetomate_backend/database/seeders/DemoDataSeeder.php)

Tablas pobladas por el seeder:

- `users`
- `categories`
- `recipes`
- `recipe_ingredients`
- `recipe_steps`
- `recipe_media`
- `recipe_ratings`
- `recipe_comments`
- `recipe_likes`
- `follows`
- `collections`
- `collection_recipes`
- `category_recipe`

Cantidad aproximada de demo data:

- 1 admin + 14 usuarios member
- 18 categorías globales (mínimo 15 cubierto)
- 12 categorías de usuario
- 30 recetas con ingredientes, pasos y galería
- ratings, comentarios, likes, follows y colecciones enlazadas

Importante sobre media demo:

- los registros de media creados por `DemoDataSeeder` en recetas usan rutas tipo `seed/recipes/...` como placeholders.
- esas rutas no siempre tienen archivo físico real.
- para probar subida real, usa los endpoints `POST /api/profile/avatar` o `POST /api/recipes/{recipe}/media`.

Categorías globales demo incluidas:

1. Desayunos
2. Comida mexicana
3. Sopas y caldos
4. Salsas
5. Ensaladas
6. Pasta
7. Arroz y granos
8. Pollo
9. Carnes
10. Pescados y mariscos
11. Postres caseros
12. Bebidas
13. Antojitos
14. Vegetarianas
15. Económicas
16. Para 1 persona
17. Para adultos mayores
18. Sin horno

Dónde modificar datos de prueba:

- categorías globales: arreglo `$globalCategoryNames` en `database/seeders/DemoDataSeeder.php`
- nombres de colecciones demo: arreglo `$collectionTemplates` en `database/seeders/DemoDataSeeder.php`
- títulos de recetas demo: arreglo `$recipeTitles` en `database/seeders/DemoDataSeeder.php`
- ingredientes base: arreglo `$ingredientPool` en `database/seeders/DemoDataSeeder.php`
- cantidades de usuarios/recetas/ratings/comentarios: métodos `run()` y `create*()` dentro de `database/seeders/DemoDataSeeder.php`
- seeder principal que se ejecuta: [database/seeders/DatabaseSeeder.php](/home/josemanuel/Desktop/salsadetomate_backend/database/seeders/DatabaseSeeder.php)

## Autenticación

Se usa Bearer token en `Authorization`:

```http
Authorization: Bearer TU_TOKEN
```

Flujo:

1. `POST /api/auth/register` o `POST /api/auth/login`
2. guardar `access_token`
3. usar el token en rutas protegidas

## Guía de conexión con Frontend (React/Vue/Angular)

Esta API está lista para SPA con token Bearer.

### 1) Configuración mínima del frontend

En tu frontend define la URL base:

```env
VITE_API_URL=http://localhost:8000
```

### 2) Login desde frontend

Request:

```http
POST /api/auth/login
Content-Type: application/json
```

Body:

```json
{
    "email": "jose@example.com",
    "password": "password123"
}
```

Respuesta esperada:

```json
{
    "token_type": "Bearer",
    "access_token": "TOKEN_AQUI",
    "user": {
        "id": 1,
        "name": "Jose Manuel",
        "role": "member"
    }
}
```

### 3) Guardar token en frontend

Ejemplo rápido:

```ts
localStorage.setItem("access_token", response.access_token);
```

### 4) Enviar token en llamadas protegidas

Header requerido:

```http
Authorization: Bearer TOKEN_AQUI
```

### 5) Ejemplo con `fetch`

```ts
const API_URL = import.meta.env.VITE_API_URL;

export async function apiFetch(path: string, options: RequestInit = {}) {
    const token = localStorage.getItem("access_token");

    const headers = new Headers(options.headers ?? {});
    headers.set("Accept", "application/json");
    if (!headers.has("Content-Type") && !(options.body instanceof FormData)) {
        headers.set("Content-Type", "application/json");
    }
    if (token) {
        headers.set("Authorization", `Bearer ${token}`);
    }

    const response = await fetch(`${API_URL}${path}`, {
        ...options,
        headers,
    });

    if (response.status === 401) {
        localStorage.removeItem("access_token");
    }

    return response;
}
```

Uso:

```ts
await apiFetch("/api/profile");
await apiFetch("/api/recipes", { method: "GET" });
await apiFetch("/api/recipes", {
    method: "POST",
    body: JSON.stringify({
        title: "Receta desde frontend",
        ingredients: [{ name: "Tomate" }],
        steps: [{ instruction: "Picar y cocinar" }],
    }),
});
```

### 6) Ejemplo con `axios`

```ts
import axios from "axios";

export const api = axios.create({
    baseURL: import.meta.env.VITE_API_URL,
    headers: { Accept: "application/json" },
});

api.interceptors.request.use((config) => {
    const token = localStorage.getItem("access_token");
    if (token) config.headers.Authorization = `Bearer ${token}`;
    return config;
});

api.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error?.response?.status === 401) {
            localStorage.removeItem("access_token");
        }
        return Promise.reject(error);
    },
);
```

### 7) Flujo recomendado para frontend

1. Registrar o login.
2. Guardar `access_token`.
3. Cargar perfil con `GET /api/auth/me` o `GET /api/profile`.
4. Permitir personalización con `PATCH /api/profile` (nombre/bio).
5. Subir avatar con `POST /api/profile/avatar` usando `FormData`.
6. Consumir endpoints públicos (`GET /api/recipes`) y protegidos (`POST /api/recipes`).
7. En `401`, limpiar token y redirigir a login.
8. En logout, llamar `POST /api/auth/logout` y limpiar token local.

### 8) Ejemplo frontend para subir avatar

```ts
const formData = new FormData();
formData.append("avatar", fileInput.files[0]);

await apiFetch("/api/profile/avatar", {
    method: "POST",
    body: formData,
});
```

### 9) Errores comunes al conectar frontend

- `401 Unauthenticated`: token vacío, vencido o sobrescrito por nuevo login.
- error CORS en navegador: origen del frontend no agregado en `CORS_ALLOWED_ORIGINS`.
- `422` al subir avatar: formato/tamaño inválido (`jpeg,jpg,png,webp`, máximo 5 MB).
- `419`/CSRF: no aplica aquí, esta API usa Bearer token, no sesión web.

## Endpoints principales

### Auth

- `POST /api/auth/register`
- `POST /api/auth/login`
- `GET /api/auth/me` (auth)
- `POST /api/auth/logout` (auth)

### Profile

- `GET /api/profile` (auth)
- `PATCH /api/profile` (auth) para nombre, bio y `avatar_url` externo
- `POST /api/profile/avatar` (auth) para subir archivo de imagen
- `DELETE /api/profile/avatar` (auth) para eliminar avatar

### Dónde se guardan los archivos media

- Avatares de usuario: `storage/app/public/avatars/{user_id}/...`
- Imágenes/videos de receta subidos por API: `storage/app/public/recipes/{recipe_id}/...`
- URL pública resultante: `http://TU_APP_URL/storage/...`

Ejemplo real:

- archivo físico: `storage/app/public/avatars/16/archivo.png`
- URL pública: `http://localhost:8000/storage/avatars/16/archivo.png`

### Recetas

- `GET /api/recipes`
- `GET /api/recipes/{recipe}`
- `POST /api/recipes` (auth)
- `PUT/PATCH /api/recipes/{recipe}` (auth)
- `DELETE /api/recipes/{recipe}` (auth)
- `POST /api/recipes/{recipe}/publish` (auth)
- `POST /api/recipes/{recipe}/unpublish` (auth)

### Medios

- `POST /api/recipes/{recipe}/media` (auth)
- `PATCH /api/recipes/{recipe}/media/{media}/primary` (auth)
- `DELETE /api/recipes/{recipe}/media/{media}` (auth)

### Ratings y comentarios

- `GET /api/recipes/{recipe}/ratings`
- `POST /api/recipes/{recipe}/ratings` (público)
- `GET /api/recipes/{recipe}/comments`
- `POST /api/recipes/{recipe}/comments` (público — invitados pueden comentar con `guest_name`)
- `DELETE /api/recipes/{recipe}/comments/{comment}` (auth)

### Categorías, feed y social

- `GET /api/categories`
- `POST /api/categories` (auth)
- `GET /api/feed/daily-recommendation`
- `POST /api/recipes/{recipe}/like` (auth)
- `DELETE /api/recipes/{recipe}/like` (auth)
- `POST /api/users/{user}/follow` (auth)
- `DELETE /api/users/{user}/follow` (auth)

### Colecciones

- `GET /api/collections` (auth)
- `POST /api/collections` (auth)
- `GET /api/collections/{collection}` (auth)
- `POST /api/collections/{collection}/recipes` (auth)
- `DELETE /api/collections/{collection}/recipes/{recipe}` (auth)
- `DELETE /api/collections/{collection}` (auth)

## Ejemplos completos de uso

### 1) Registrar usuario

```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Jose Manuel",
    "email": "jose@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

Respuesta esperada (201):

```json
{
    "token_type": "Bearer",
    "access_token": "...",
    "user": {
        "id": 1,
        "name": "Jose Manuel",
        "role": "member"
    }
}
```

### 2) Crear receta en borrador

```bash
curl -X POST http://localhost:8000/api/recipes \
  -H "Authorization: Bearer TU_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Salsa de tomate casera",
    "summary": "Receta familiar",
    "servings": 4,
    "prep_time_minutes": 10,
    "cook_time_minutes": 20,
    "category_ids": [1],
    "ingredients": [
      {"name": "Tomate", "quantity": 6, "unit": "pieza", "calories": 132},
      {"name": "Cebolla", "quantity": 1, "unit": "pieza", "calories": 44}
    ],
    "steps": [
      {"instruction": "Lava y pica los ingredientes"},
      {"instruction": "Hierve 20 minutos"}
    ]
  }'
```

### 3) Subir foto principal a la receta

```bash
curl -X POST http://localhost:8000/api/recipes/1/media \
  -H "Authorization: Bearer TU_TOKEN" \
  -F "file=@/ruta/foto.jpg" \
  -F "is_primary=true"
```

### 4) Publicar receta

```bash
curl -X POST http://localhost:8000/api/recipes/1/publish \
  -H "Authorization: Bearer TU_TOKEN"
```

Si falta foto principal/pasos/ingredientes, regresará `422`.

### 5) Calificar como invitado (sin login)

```bash
curl -X POST http://localhost:8000/api/recipes/1/ratings \
  -H "Content-Type: application/json" \
  -d '{
    "stars": 5,
    "guest_name": "Doña Rosa",
    "review": "Muy rica",
    "would_cook_again": true
  }'
```

### 6) Buscar recetas por texto + categoría + rango de fecha

```bash
curl "http://localhost:8000/api/recipes?q=tomate&category_ids[]=1&published_from=2026-02-01&published_to=2026-03-01&per_page=10"
```

### 7) Obtener recomendación diaria

```bash
curl "http://localhost:8000/api/feed/daily-recommendation?limit=1"
```

### 8) Actualizar nombre y bio de perfil

```bash
curl -X PATCH http://localhost:8000/api/profile \
  -H "Authorization: Bearer TU_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Jose Manuel Castillo",
    "bio": "Me gusta compartir recetas caseras."
  }'
```

### 9) Subir avatar de perfil (archivo)

```bash
curl -X POST http://localhost:8000/api/profile/avatar \
  -H "Authorization: Bearer TU_TOKEN" \
  -F "avatar=@/ruta/avatar.jpg"
```

### 10) Eliminar avatar de perfil

```bash
curl -X DELETE http://localhost:8000/api/profile/avatar \
  -H "Authorization: Bearer TU_TOKEN"
```

## Estructura de errores

Error de validación típico (`422`):

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "main_image": [
            "La receta debe tener una foto principal antes de publicarse."
        ]
    }
}
```

No autenticado (`401`):

```json
{
    "message": "Unauthenticated."
}
```

## Pruebas automatizadas

```bash
php artisan test
```

Cubre escenarios críticos como:

- rating público por invitado
- bloqueo de publicación sin foto principal
- filtros de listado
- recomendación diaria con umbral

# Documentación API – CRUD Piedra, Papel o Tijera

Autor: Francisco José Sobrino Palomo · DAW · CIFP Virgen de Gracia

## 1. Descripción general

API REST desarrollada con **Laravel** que combina un **CRUD de usuarios** con el juego **piedra, papel o tijera**. Toda la lógica del juego se ejecuta en el servidor: el cliente solo envía los datos mínimos para pedir la operación.

**Reglas del juego**

- Cada partida se juega **al mejor de 5**.
- Si hay **empate**, la jugada se repite (no cuenta para el marcador).
- Cuando el mejor de 5 queda decidido, la partida se marca como **finalizada** en la base de datos y se fija el resultado final.
- La tirada de la máquina la genera el servidor.

## 2. Datos generales

| Concepto | Valor |
|---|---|
| URL base (local) | `http://127.0.0.1:8000/api` |
| Formato | JSON |
| Autenticación | Laravel Sanctum (Bearer token) |
| Cabecera recomendada | `Accept: application/json` |
| Controladores | `AuthController` (login) y `Controlador` (CRUD + juego) |

> Todas las rutas llevan el prefijo `/api` (fichero `routes/api.php`).

## 3. Autenticación y roles

Se hace login en `POST /api/login`, que devuelve un token. Ese token se envía en las rutas protegidas con la cabecera:

```
Authorization: Bearer <token>
```

Los roles se guardan en el campo `rol` de la tabla `users` y los controla un middleware (no se valida el rol dentro del controlador):

| Grupo | Prefijo | Middleware | Quién accede |
|---|---|---|---|
| Públicas | — | — | Cualquiera |
| Administrador | `/admin` | `auth:sanctum`, `admin` | Usuarios con rol admin |
| Usuario | `/user` | `auth:sanctum`, `user` | Usuarios con rol user |

## 4. Resumen de endpoints

| Método | Ruta | Método del controlador | Acceso | Descripción |
|---|---|---|---|---|
| POST | `/login` | `AuthController@login` | Público | Iniciar sesión y obtener token |
| POST | `/recuperar-password` | `recuperarPassword` | Público | Recuperar contraseña por email |
| POST | `/admin` | `addUsuario` | Admin | Crear un usuario |
| GET | `/admin/{id}` | `getUsuario` | Admin | Consultar un usuario |
| DELETE | `/admin/{id}` | `deleteUsuario` | Admin | Eliminar un usuario |
| DELETE | `/admin/partidas/{id}` | `deletePartidaUsuario` | Admin | Eliminar una partida de un usuario |
| POST | `/user` | `jugar` | Usuario | Realizar una tirada |
| POST | `/user/partida` | `crearPartida` | Usuario | Crear una partida nueva |
| GET | `/user/partidas/{id}` | `getPartidas` | Usuario | Listar las partidas de un jugador |
| GET | `/user/tirada/{id_p}` | `getTiradasPartida` | Usuario | Listar las tiradas de una partida |
| GET | `/user/ranking` | `getRanking` | Usuario | Ranking de jugadores |

## 5. Endpoints públicos

### 5.1 Login

`POST /api/login`

Autentica a un usuario (admin o user) y devuelve un token de Sanctum.

**Body (JSON)**

```json
{
  "email": "usuario@example.com",
  "password": "1234"
}
```

| Campo | Tipo | Obligatorio | Descripción |
|---|---|---|---|
| email | string | Sí | Email del usuario |
| password | string | Sí | Contraseña |

**Respuestas**

- `200 OK`: login correcto, devuelve el token.
- `401 Unauthorized` / `422 Unprocessable Entity`: credenciales incorrectas o datos no válidos.

### 5.2 Recuperar contraseña

`POST /api/recuperar-password`

Inicia la recuperación de contraseña del usuario con ese email.

**Body (JSON)**

```json
{
  "email": "usuario@example.com"
}
```

| Campo | Tipo | Obligatorio | Descripción |
|---|---|---|---|
| email | string | Sí | Email de la cuenta |

**Respuestas**

- `200 OK`: solicitud procesada.
- `404 Not Found` / `422 Unprocessable Entity`: email no registrado o no válido.

## 6. Endpoints de administrador

Requieren `Authorization: Bearer <token_admin>`. Si el token no es válido se devuelve `401`; si el usuario no es admin, `403`.

### 6.1 Crear usuario

`POST /api/admin`

**Body (JSON)**

```json
{
  "name": "admin",
  "email": "admin@example.com",
  "password": "1234",
  "rol": "admin"
}
```

| Campo | Tipo | Obligatorio | Descripción |
|---|---|---|---|
| name | string | Sí | Nombre del usuario |
| email | string | Sí | Email (único) |
| password | string | Sí | Contraseña |
| rol | string | Sí | `admin` o `user` |

**Respuestas**: `201 Created` (usuario creado), `422` (datos no válidos o email duplicado).

### 6.2 Obtener usuario

`GET /api/admin/{id}`

| Parámetro | Tipo | Descripción |
|---|---|---|
| id | integer (URL) | Identificador del usuario |

**Respuestas**: `200 OK` (datos del usuario), `404` (no existe).

### 6.3 Eliminar usuario

`DELETE /api/admin/{id}`

| Parámetro | Tipo | Descripción |
|---|---|---|
| id | integer (URL) | Identificador del usuario a eliminar |

Al eliminar un usuario se borran en cascada su registro de jugador, sus partidas y sus tiradas (claves foráneas con `onDelete cascade`).

**Respuestas**: `200 OK`, `404` (no existe).

### 6.4 Eliminar partida de un usuario

`DELETE /api/admin/partidas/{id}`

| Parámetro | Tipo | Descripción |
|---|---|---|
| id | integer (URL) | Identificador del usuario dueño de la partida |

**Body (JSON)**

```json
{
  "idPartida": 1
}
```

Comprobaciones del servidor: el usuario existe (en la tabla `jugadores`), la partida existe, la partida pertenece al usuario indicado en la URL y quien hace la petición es administrador.

**Respuestas**: `200 OK`, `404` (usuario o partida inexistente), `403` (la partida no pertenece al usuario o no eres admin).

## 7. Endpoints de usuario

Requieren `Authorization: Bearer <token_user>`.

### 7.1 Crear partida

`POST /api/user/partida`

Crea una partida nueva para el jugador autenticado. No necesita body.

**Respuestas**: `201 Created` (devuelve la partida creada, con su id), `401`.

### 7.2 Jugar (realizar una tirada)

`POST /api/user`

**Body (JSON)**

```json
{
  "idPartida": 1,
  "tirada": "piedra"
}
```

| Campo | Tipo | Obligatorio | Descripción |
|---|---|---|---|
| idPartida | integer | Sí | Partida en la que se juega |
| tirada | string | Sí | `piedra`, `papel` o `tijera` |

**Proceso en el servidor**

1. Comprueba que la partida existe, es del usuario autenticado y está activa.
2. Valida que la tirada sea `piedra`, `papel` o `tijera`.
3. Genera la tirada de la máquina.
4. Determina el resultado (empate, gana jugador o gana máquina).
5. Actualiza el marcador y guarda la tirada.
6. Si el mejor de 5 queda decidido, marca la partida como finalizada y fija el resultado final.

**Respuestas**: `200 OK` (tirada de ambos, resultado y marcador), `404` (partida inexistente), `403` (la partida no es tuya), `409`/`422` (partida ya finalizada o tirada no válida).

### 7.3 Listar partidas

`GET /api/user/partidas/{id}`

| Parámetro | Tipo | Descripción |
|---|---|---|
| id | integer (URL) | Identificador del jugador del que se consultan las partidas |

**Respuestas**: `200 OK` (lista de partidas con su estado y resultado), `404`.

### 7.4 Listar tiradas de una partida

`GET /api/user/tirada/{id_p}`

| Parámetro | Tipo | Descripción |
|---|---|---|
| id_p | integer (URL) | Identificador de la partida |

**Respuestas**: `200 OK` (tiradas de la partida: tirada del jugador, tirada de la máquina y resultado), `404`.

### 7.5 Ranking

`GET /api/user/ranking`

Devuelve los mejores jugadores **sin incluir administradores**, ordenados por *winratio* (ganadas / jugadas), con su posición y sus resultados.

**Respuestas**: `200 OK`, `401`.

## 8. Modelo de datos

**users**: id, name, email, password, rol, timestamps.

**jugadores**: id, user_id (FK → users, cascade), jugadas (integer, por defecto 0), ganadas (integer, por defecto 0), timestamps.

**partidas**: id, jugador_id (FK → jugadores, cascade), resultado (integer: 1 = gana el jugador, 2 = gana la máquina), activa (boolean: true = en curso, false = finalizada), timestamps.

**tiradas**: id, partida_id (FK → partidas, cascade), tirada_jugador (string), tirada_maquina (string), resultado (integer: 0 = empate, 1 = jugador, 2 = máquina), timestamps.

## 9. Códigos de estado habituales

| Código | Significado |
|---|---|
| 200 | Petición correcta |
| 201 | Recurso creado |
| 401 | Sin token o token no válido |
| 403 | Rol no autorizado o recurso de otro usuario |
| 404 | Recurso no encontrado |
| 422 | Datos de entrada no válidos |

## 10. Colección de Postman

La colección **CRUD PiedraPapelTijera** incluye una petición por cada endpoint (`login admin`, `login usuario`, `addUsuario`, `getUsuario`, `deleteUsuario`, `crearPartida`, `jugar`, `getRanking`, `getPartidas`, `getTiradasPartida`, `deletePartidaUsuario`, `recuperarPassword`). Para probarla: ejecutar `php artisan serve`, hacer login con el usuario correspondiente y pegar el token en la pestaña *Authorization → Bearer Token* de cada petición.

Página 1 de 11

## Práctica 6 — Piedra, papel, tijera


## 2º CFGS DAW SERVIDOR

Piedra Papel Tijera

Página 2 de 11

## Objetivo

Desarrolla un servicio REST en Laravel que permita a los usuarios jugar contra la máquina al juego de Piedra, Papel o Tijera.

La aplicación contará con dos tipos de usuarios:

- user: usuarios que pueden jugar partidas.

- admin: administradores encargados de gestionar los usuarios. Los administradores no son jugadores y, por tanto, no pueden participar en partidas.

Deberás diseñar e implementar la solución utilizando Laravel, aplicando las técnicas vistas en clase para autenticación, autorización, validación, gestión de errores y creación de servicios REST.

## 1. Reglas del juego

Las partidas se jugarán al mejor de cinco tiradas.

El primer participante que consiga 3 victorias gana la partida.

Las reglas son:

- Piedra gana a Tijera.

- Tijera gana a Papel.

- Papel gana a Piedra.

- Si ambos realizan la misma tirada, se produce un empate.

La máquina deberá realizar su tirada de forma aleatoria.

El jugador nunca podrá indicar cuál ha sido la tirada de la máquina ni el resultado de la partida. Estos datos deberán ser calculados por el servidor.

Una vez que uno de los participantes consiga 3 victorias, la partida finalizará y no podrán realizarse más tiradas sobre ella.


## SERVIDOR

## 2º CFGS DAW

Piedra Papel Tijera

Página 3 de 11

## 2. Gestión de partidas

Cada usuario podrá tener como máximo una partida activa.

## Por tanto:

- Un usuario que no tenga ninguna partida activa podrá comenzar una nueva partida.

- Un usuario que ya tenga una partida activa no podrá comenzar otra.

- Cuando una partida finalice, el usuario podrá comenzar una nueva.

El servidor deberá comprobar esta condición y no se podrá confiar únicamente en las comprobaciones realizadas por el cliente.

## 3. Usuarios

Existirán dos roles:

## Usuario user

Es un jugador y podrá:

- Comenzar partidas.

- Realizar tiradas.

- Consultar sus partidas.

- Consultar las tiradas de sus partidas.

- Consultar el ranking.

## Usuario admin

Es un administrador y podrá:

- Consultar usuarios.

- Crear usuarios.

- Modificar usuarios.

- Gestionar partidas de los usuarios.

Un administrador no podrá jugar partidas.


## 2º CFGS DAW SERVIDOR

Piedra Papel Tijera

Página 4 de 11

## 4. Rutas de administración -QB

Todas las rutas que comiencen por /admin estarán disponibles únicamente para usuarios autenticados con rol admin.

## GET /admin

Devuelve el listado de usuarios registrados.

No se deberá mostrar información sensible como la contraseña.

## GET /admin/{id}

Devuelve la información del usuario cuyo identificador se recibe en la URL.

El administrador podrá consultar cualquier usuario.

## POST /admin

Permite registrar un nuevo usuario.

Los datos necesarios serán:

```
{
"nombre": "Francisco",
"email": "francisco@email.com",
"password": "123456",
"rol": "user"
}
```

El campo rol únicamente podrá contener:

- user

- admin

La contraseña deberá almacenarse de forma segura.


## 2º CFGS DAW SERVIDOR

## Piedra Papel Tijera

Página 5 de 11

## PATCH /admin/{id}

Permite modificar uno o varios datos del usuario indicado.

Al tratarse de una petición PATCH, no será necesario enviar todos los campos.

## Ejemplo:

```
{
"nombre": "Juan Manuel"
}
```

## DELETE /admin/partida/{id}

Permite al administrador eliminar una partida perteneciente al usuario cuyo identificador se indica en la URL.

En el body se enviará el identificador de la partida que se quiere eliminar.

## Ejemplo:

```
DELETE /admin/partida/3
{
"idPartida": 15
}
```

Antes de realizar la operación se deberá comprobar que:

- El usuario existe.

- La partida existe.

- La partida pertenece al usuario indicado.

- El usuario autenticado es un administrador.

## 5. Rutas de usuarios - QB

Las rutas destinadas a los jugadores estarán disponibles únicamente para usuarios autenticados con rol user.

Un usuario solo podrá consultar y gestionar sus propias partidas.

No se permitirá que un usuario utilice el identificador de otro usuario para acceder a sus partidas o tiradas.


## SERVIDOR

## 2º CFGS DAW

## Piedra Papel Tijera

Página 6 de 11

## GET /user/partidas/{id}

Devuelve las partidas del usuario indicado.

La información deberá permitir conocer:

- Las partidas que tiene el usuario.

- Qué partidas están finalizadas.

- Si tiene una partida activa.

- El resultado de las partidas finalizadas.

- El marcador de cada partida.

Ejemplo de información que podría devolver:

```
{
"id": 5,
"estado": "activa",
"victoriasUsuario": 2,
"victoriasMaquina": 1
}
```

El usuario autenticado únicamente podrá consultar sus propias partidas.

## 6. Realizar una tirada - Eloquent

## POST /user

Permite al usuario realizar una tirada en una partida activa.

En el body se enviarán:

```
{
"idPartida": 3,
"tirada": "piedra"
}
```

La tirada únicamente podrá ser:

- piedra

- papel

- tijera

El servidor deberá encargarse de:


## 2º CFGS DAW SERVIDOR

## Piedra Papel Tijera

- 2. Comprobar que la partida pertenece al usuario.

- 3. Comprobar que la partida está activa.

- 4. Validar la tirada recibida.

- 5. Generar la tirada de la máquina.

- 6. Determinar el resultado de la tirada.

- 7. Actualizar el marcador.

- 8. Guardar la información de la tirada.

- 9. Comprobar si la partida ha terminado.

- 10. Determinar el resultado final de la partida cuando corresponda.

El cliente no enviará la tirada de la máquina ni el resultado de la partida.

Página 7 de 11

- 1. Comprobar que la partida existe.

## 7. Ranking - Eloquent

## GET /user/ranking

Deberá devolver un ranking de los mejores jugadores.

Los administradores no aparecerán en el ranking.

Deberás establecer un criterio para determinar la posición de cada jugador y utilizarlo de forma coherente.

El ranking deberá mostrar información suficiente para conocer la posición y los resultados de los jugadores.

## 8. Consulta de tiradas – Eloquent/QB

POST /user/partida/tiradas/{id}

Permite consultar las tiradas realizadas por un usuario.

El {id} de la URL corresponde al identificador del usuario.

En el body se podrá indicar una partida concreta:

```
{
"idPartida": 3
}
```


## 2º CFGS DAW SERVIDOR

Piedra Papel Tijera

En este caso se deberán devolver únicamente las tiradas correspondientes a esa partida.

Si idPartida se encuentra vacío, se deberán devolver todas las tiradas de todas las partidas del usuario.

Ejemplo:

{

}

Un usuario no podrá consultar las tiradas pertenecientes a otro usuario.

Página 8 de 11

"idPartida": null

## 9. Comenzar una partida – Eloquent/QB

La aplicación deberá disponer de un servicio que permita a un usuario comenzar una nueva partida.

Antes de crearla se deberá comprobar que el usuario no tiene otra partida activa.

Si ya existe una partida activa, la operación deberá rechazarse.

Deberás decidir la ruta y método HTTP más adecuados para realizar esta operación, respetando los principios REST.

## 10. Autenticación y autorización

La aplicación deberá disponer de un sistema de autenticación.

Las operaciones deberán estar protegidas según el rol del usuario.

Se deberá garantizar que:

- Un usuario no pueda acceder a las rutas de administración.

- Un administrador no pueda utilizar las funcionalidades de juego.

- Un usuario no pueda consultar las partidas de otro usuario.

- Un usuario no pueda realizar tiradas en partidas que no le pertenecen.

- Un usuario no pueda modificar el resultado de una partida.

- Un usuario no pueda realizar tiradas en una partida finalizada.


## 2º CFGS DAW SERVIDOR

Piedra Papel Tijera

Página 9 de 11

## 11. Validación y errores

Deberás realizar una correcta validación de todos los datos recibidos.

La API deberá responder utilizando los códigos HTTP adecuados.

Como mínimo, se deberán contemplar situaciones como:

- Datos obligatorios no enviados.

- Datos con formato incorrecto.

- Usuario inexistente.

- Partida inexistente.

- Usuario sin permisos.

- Usuario intentando acceder a datos de otro usuario.

- Partida que no pertenece al usuario.

- Partida ya finalizada.

- Usuario intentando comenzar una segunda partida activa.

- Tirada no válida.

- Identificador incorrecto.

- Intento de realizar operaciones propias de un administrador por parte de un jugador.

- Intento de jugar por parte de un administrador.

Los mensajes de error deberán ser claros y permitir conocer qué ha ocurrido.

## 12. Consideraciones

Toda la lógica del juego deberá ejecutarse en el servidor.

El cliente únicamente enviará los datos necesarios para solicitar una operación.

Por ejemplo, para realizar una tirada el cliente enviará:

```
{
"idPartida": 3,
"tirada": "piedra"
}
```

El servidor será el responsable de determinar:

- La tirada de la máquina.

- Quién gana la tirada.

- El marcador.

- Si la partida continúa.


## SERVIDOR 2º CFGS DAW Piedra Papel Tijera

Página 10 de

11

- Si la partida ha finalizado.

- Quién ha ganado la partida.

No se deberá confiar en datos enviados por el cliente que puedan alterar el resultado del juego.

## 13. Rutas disponibles

Las rutas que

deberá proporcionar la aplicación son:

## Administración

| Método | Ruta | Descripción |
| --- | --- | --- |
| GET | /admin | Listar usuarios |
| GET | /admin/{id} | Consultar usuario |
| POST | /admin | Crear usuario |
| PATCH | /admin/{id} | Modificar usuario |
| DELETE /admin/partida/{id} Eliminar una partida de un usuario |   |   |

## Usuarios

| Método | Ruta | Descripción |
| --- | --- | --- |
| GET | /user/partidas/{id} | Consultar partidas de un usuario |
| POST /user |   | Realizar una tirada |
| GET | /user/ranking | Consultar ranking |
| POST |   | /user/partida/tiradas/{id} Consultar tiradas |

Además, deberás añadir la ruta necesaria para comenzar una nueva partida.

## 14. Objetivo de la práctica

El objetivo es desarrollar una API REST funcional en Laravel que permita gestionar usuarios, partidas y tiradas, aplicando correctamente:

- Autenticación.

- Autorización.

- Roles.


## SERVIDOR 2º CFGS DAW Piedra Papel Tijera

- Rutas REST.

- Validación.

- Control de errores.

- Relaciones entre los diferentes recursos.

- Lógica de negocio.

- Gestión del estado de las partidas.

- Persistencia de la información.

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\Partida;
use App\Models\User;
use App\Models\Jugador;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;


class Controlador extends Controller
{

    public function addUsuario(Request $request){

        $input = $request->all();

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'password' => 'required|min:8|max:100',
            'rol' => 'required'
        ];
        $messages = [
            'email' => 'El campo :attribute debe ser un correo electrónico válido.',
            'max' => 'El campo :attribute no debe exceder el tamaño máximo permitido.',
            'min' => 'El campo :attribute debe exceder el tamaño actual',
            'required' => 'El campo :attribute es obligatorio.'
        ];


        $validator = Validator::make($input, $rules, $messages);

        if($validator->fails()){
            return response()->json($validator->errors(),422);
        }

        $idUsuario = DB::table('users')->insertGetId([
            'name'     => $input['name'],
            'email'    => $input['email'],
            'password' => Hash::make($input['password']),
            'rol'      => $input['rol'],
        ]);

        if ($input['rol'] !== 'admin') {
            Jugador::create(['user_id' => $idUsuario]);
        }
        
        return response()->json("El usuario ha sido añadido");
    }

    public function getUsuario($id_u) {
        $usuario = DB::table('users')->where('id', $id_u)->first();
        return response()->json($usuario);
    }

    public function getPartidas($id_u) {
        $partidas = DB::table('partidas')->where('jugador_id', $id_u)->get();
        return response()->json($partidas);
    }


    // ACABAR
    public function getRanking() {
        $jugadores = Jugador::with('user')->where('jugadas', '>', 0)->get();

        foreach ($jugadores as $jugador) {
            $jugador->winrate = round($jugador->ganadas / $jugador->jugadas * 100, 2);
        }

        $ranking = $jugadores->sortByDesc('winrate')->values();

        return response()->json($ranking);
    }

    public function getTiradasPartida($id_p) {
        $tiradasUsuario = DB::table('tiradas')->where('id', $id_p)->get('tiradas_usuario');
        $tiradasMaquina = DB::table('tiradas')->where('id', $id_p)->get('tiradas_maquina');

        return response()->json($tiradasUsuario, $tiradasMaquina);
    }

    public function deleteUsuario($id_u) {
        User::where('id', $id_u)->delete();

        return response()->json("El usuario ha sido eliminado");
    }


    public function deletePartidaUsuario(Request $request, $id_u) {
        $request->validate(['idPartida' => ['required', 'integer']]);

        $jugador = Jugador::where('user_id', $id_u)->first();
        if (!$jugador) {
            return response()->json(['message' => 'El usuario no existe'], 404);
        }

        $partida = Partida::find($request->idPartida);
        if (!$partida) {
            return response()->json(['message' => 'La partida no existe'], 404);
        }

        if ((int) $partida->jugador_id !== (int) $jugador->id) {
            return response()->json(['message' => 'La partida no pertenece al usuario indicado'], 403);
        }

        $partida->delete();

        return response()->json(['message' => 'La partida ha sido eliminada']);
    }

    public function crearPartida(Request $request) {
        $jugador = Jugador::where('user_id', $request->user()->id)->first();

        if (!$jugador) {
            return response()->json(['message' => 'El usuario no es un jugador'], 403);
        }

        $partida = Partida::create([
            'jugador_id' => $jugador->id,
            'activa'     => true,
        ]);

        return response()->json($partida, 201);
    }

    public function jugar(Request $request) {

        $datos = $request->validate([
            'idPartida' => ['required', 'integer'],
            'tirada'    => ['required', 'in:piedra,papel,tijera'],
        ]);

        $partida = Partida::find($datos['idPartida']);

        if (!$partida) {
            return response()->json(['message' => 'La partida no existe'], 404);
        }

        $jugador = Jugador::where('user_id', $request->user()->id)->first();

        if (!$jugador || (int) $partida->jugador_id !== (int) $jugador->id) {
            return response()->json(['message' => 'La partida no pertenece al usuario'], 403);
        }

        if (!$partida->activa) {
            return response()->json(['message' => 'La partida ya ha terminado'], 409);
        }

        $opciones = ['piedra', 'papel', 'tijera'];
        $maquina = $opciones[array_rand($opciones)];

        $resultado = $this->decidirResultado($datos['tirada'], $maquina);

        $partida->tiradas()->create([
            'tirada_jugador' => $datos['tirada'],
            'tirada_maquina' => $maquina,
            'resultado'      => $resultado,
        ]);

        $victoriasJugador = $partida->tiradas()->where('resultado', 1)->count();
        $victoriasMaquina = $partida->tiradas()->where('resultado', 2)->count();

        if ($victoriasJugador === 3 || $victoriasMaquina === 3) {
            $partida->activa = false;
            $partida->resultado = $victoriasJugador === 3 ? 1 : 2;
            $partida->save();

            $jugador->increment('jugadas');
            if ($victoriasJugador === 3) {
                $jugador->increment('ganadas');
            }
        }

        return response()->json([
            'tirada_jugador'    => $datos['tirada'],
            'tirada_maquina'    => $maquina,
            'resultado'         => $resultado,
            'victorias_jugador' => $victoriasJugador,
            'victorias_maquina' => $victoriasMaquina,
            'partida_activa'    => $partida->activa,
            'resultado_partida' => $partida->resultado,
        ]);
    }
    public function decidirResultado($jugador, $maquina): int
    {
        if ($jugador === $maquina) {
            return 0;
        }

        $gana = ['piedra' => 'tijera', 'papel' => 'piedra', 'tijera' => 'papel'];

        return $gana[$jugador] === $maquina ? 1 : 2;
    }

    public function recuperarPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $usuario = User::where('email', $request->email)->first();

        if (!$usuario) {
            return response()->json(['message' => 'No existe ningún usuario con ese correo'], 404);
        }

        $nuevaPassword = Str::random(10);

        try {
            Mail::send('otravista', [
                'nombreUsuarioDAW2' => $usuario->name,
                'pass'              => $nuevaPassword,
            ], function ($message) use ($usuario) {
                $message->to($usuario->email)
                        ->subject('Recuperación de contraseña');
            });
        } catch (\Exception $e) {
            return response()->json([
                'enviado' => false,
                'mensaje' => 'Error al enviar el correo',
                'error'   => $e->getMessage(),
            ], 500);
        }

        // Se guarda después de enviar, para no cambiarla si el correo falla
        $usuario->password = Hash::make($nuevaPassword);
        $usuario->save();

        return response()->json(['enviado' => true, 'mensaje' => 'Te hemos enviado una nueva contraseña']);
    }


}

<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Auditoria;
use App\Models\CodigoVerificacion;
use App\Mail\CodigoVerificacionMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function usuarios()
    {
        $usuarios = User::where('estado', 'activo')->select('id', 'nombre', 'email', 'foto')->get();

        return response()->json([
            'success' => true,
            'data' => $usuarios,
        ]);
    }
    
    public function register(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        DB::beginTransaction();
        try {
            $user = User::create([
                'nombre' => $validated['nombre'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'zona_horaria' => 'UTC',
                'idioma' => 'es',
                'tema' => 'claro',
                'estado' => 'activo',
            ]);

            Auditoria::create([
                'usuario_id' => $user->id,
                'accion' => 'crear',
                'objeto_type' => User::class,
                'objeto_id' => $user->id,
                'descripcion' => 'Se registró en la plataforma',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Usuario registrado exitosamente',
                'data' => [
                    'user' => $user->only(['id', 'nombre', 'email', 'zona_horaria', 'idioma', 'tema']),
                ],
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al registrar usuario'], 500);
        }
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'type' => 'USER_NOT_FOUND',
                'message' => 'El correo no está registrado',
            ], 403);
        }

        if ($user->estado === 'inactivo') {
            return response()->json([
                'success' => false,
                'type' => 'USER_INACTIVE',
                'message' => 'Usuario deshabilitado',
            ], 403);
        }

        if (!Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'type' => 'INVALID_PASSWORD',
                'message' => 'Contraseña incorrecta',
            ], 403);
        }

        $tokenResult = $user->createToken('auth_token');
        $accessToken = $tokenResult->accessToken;
        $accessToken->expires_at = now()->addHours(16);
        $accessToken->save();
        $token = $tokenResult->plainTextToken;

        $user->ultimo_login = now();
        $user->save();

        Auditoria::create([
            'usuario_id' => $user->id,
            'accion' => 'login',
            'objeto_type' => User::class,
            'objeto_id' => $user->id,
            'descripcion' => 'Inició sesión',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Login exitoso',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => 16 * 3600,
            'data' => [
                'user' => $user->only(['id', 'nombre', 'email', 'foto', 'estado', 'zona_horaria', 'idioma', 'tema']),
            ],
        ]);
    }

    public function enviarCodigo(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $codigo = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        CodigoVerificacion::create([
            'correo' => $validated['email'],
            'codigo' => $codigo,
            'expira_en' => now()->addMinutes(15),
        ]);

        Mail::to($validated['email'])->send(new CodigoVerificacionMail($validated['email'], $codigo));

        return response()->json([
            'success' => true,
            'message' => 'Código de verificación enviado al correo',
        ]);
    }

    public function verificarCodigoCambio(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
            'codigo' => 'required|string|size:6',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $registro = CodigoVerificacion::valido($validated['email'], $validated['codigo'])->first();

        if (!$registro) {
            return response()->json([
                'success' => false,
                'message' => 'Código inválido o expirado',
            ], 401);
        }

        DB::beginTransaction();
        try {
            $user = User::where('email', $validated['email'])->first();
            $user->password = Hash::make($validated['password']);
            $user->save();

            $registro->usado = true;
            $registro->save();

            Auditoria::create([
                'usuario_id' => $user->id,
                'accion' => 'editar',
                'objeto_type' => User::class,
                'objeto_id' => $user->id,
                'descripcion' => 'Cambió su contraseña',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Contraseña actualizada correctamente',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al cambiar contraseña'], 500);
        }
    }

    public function perfil()
    {
        $user = Auth::user();
        return response()->json([
            'success' => true,
            'data' => $user->only(['id', 'nombre', 'email', 'foto', 'estado', 'ultimo_login', 'zona_horaria', 'idioma', 'tema']),
        ]);
    }

    public function actualizarPerfil(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'nombre' => 'sometimes|string|max:255',
            'foto' => 'nullable|string|max:255',
            'zona_horaria' => 'sometimes|string|max:50',
            'idioma' => 'sometimes|string|max:10',
            'tema' => 'sometimes|string|in:claro,oscuro',
        ]);

        $user->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Perfil actualizado',
            'data' => $user->only(['id', 'nombre', 'email', 'foto', 'estado', 'zona_horaria', 'idioma', 'tema']),
        ]);
    }

    public function logout(Request $request)
    {
        $user = Auth::user();

        Auditoria::create([
            'usuario_id' => $user->id,
            'accion' => 'logout',
            'objeto_type' => User::class,
            'objeto_id' => $user->id,
            'descripcion' => 'Cerró sesión',
        ]);

        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sesión cerrada exitosamente',
        ]);
    }
}

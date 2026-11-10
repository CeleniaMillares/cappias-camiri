<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Curso;
use App\Models\Usuario;
use App\Models\Rol;
use App\Models\Alumno;
use App\Models\Inscripcion;
use App\Models\HorarioCurso;
use App\Models\Producto;
use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\Pago;
use App\Models\Parametro;
use App\Events\VentaConfirmada;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Password; // Para enviar correo de restablecer contraseña
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    // ==========================================
    // CHECKOUT DE CURSO (ALUMNO)
    // ==========================================
    public function createCurso($id_curso) {
        $curso = Curso::with('horarios')->findOrFail($id_curso);
        $qrImage = Parametro::where('clave', 'qr_image_url')->value('valor');
        
        return view('publico.checkout_curso', compact('curso', 'qrImage'));
    }

    public function storeCurso(Request $request, $id_curso) {
        $request->validate([
            'correo' => 'required|email',
            'nombres' => 'required|string|max:150',
            'ci' => 'required|string|max:20',
            'telefono' => 'required|string|max:20',
            'id_horario_curso' => 'required|exists:horario_cursos,id_horario_curso',
            'metodo_pago' => 'required|in:EFECTIVO,QR',
        ]);

        $usuario = Usuario::where('correo', $request->correo)->first();

        // 🛑 Si el correo ya existe, obligamos a iniciar sesión (o pedir contraseña)
        if ($usuario) {
            // Si el usuario ya está logueado, continuamos. Si no, lo mandamos al login.
            if (!Auth::check() || Auth::user()->correo !== $request->correo) {
                return back()->withErrors(['correo' => 'Este correo ya está registrado. Por favor, inicia sesión para continuar o usa otro correo.'])->withInput();
            }
        }

        $nuevoUsuario = false;
        DB::transaction(function () use ($request, $id_curso, &$usuario, &$nuevoUsuario) {
            $horario = HorarioCurso::with('curso')
                ->lockForUpdate()
                ->findOrFail($request->id_horario_curso);
            abort_unless((string) $horario->id_curso === (string) $id_curso, 404);

            // 1. Si el usuario NO existe, lo creamos automáticamente como Alumno
            if (!$usuario) {
                $rolAlumno = Rol::where('nombre', 'Alumno')->first();
                if (!$rolAlumno) {
                    throw new \RuntimeException('No existe el rol Alumno para registrar la inscripcion.');
                }

                $passwordTemporal = Str::random(8); // Generamos contraseña temporal

                $usuario = Usuario::create([
                    'nombre' => $request->nombres,
                    'correo' => $request->correo,
                    'contrasena_hash' => Hash::make($passwordTemporal),
                    'activo' => true,
                ]);
                $usuario->roles()->attach($rolAlumno->id_rol);
                $nuevoUsuario = true;
            }

            // 2. Buscar o crear el Alumno
            $alumno = Alumno::firstOrCreate(
                ['ci' => $request->ci],
                ['nombres' => $request->nombres, 'telefono' => $request->telefono, 'edad' => 18] // Edad por defecto o pedirla
            );

            $yaInscrito = Inscripcion::where('id_alumno', $alumno->id_alumno)
                ->where('id_horario_curso', $horario->id_horario_curso)
                ->exists();
            if ($yaInscrito) {
                throw ValidationException::withMessages([
                    'id_horario_curso' => 'Este alumno ya está inscrito en este horario.',
                ]);
            }
            
            // Verificar cupo
            $inscritos = Inscripcion::where('id_horario_curso', $horario->id_horario_curso)->count();
            if ($inscritos >= $horario->cupo_maximo) {
                throw ValidationException::withMessages([
                    'id_horario_curso' => 'El cupo para este horario está lleno.',
                ]);
            }

            // Todas las inscripciones esperan la verificación de la Directiva.
            $inscripcion = Inscripcion::create([
                'id_alumno' => $alumno->id_alumno,
                'id_horario_curso' => $horario->id_horario_curso,
                'estado' => 'PENDIENTE_PAGO',
            ]);

            Pago::create([
                'id_inscripcion' => $inscripcion->id_inscripcion,
                'monto' => $horario->curso->costo,
                'metodo' => $request->metodo_pago,
                'estado' => 'PENDIENTE',
            ]);
        });

        if ($nuevoUsuario) {
            Password::sendResetLink(['correo' => $usuario->correo]);
        }

        return redirect('/')->with(
            'success',
            'Inscripción registrada. La Directiva verificará tu pago para confirmar tu cupo.'
        );
    }

    // ==========================================
    // CHECKOUT DE PRODUCTO (CLIENTE)
    // ==========================================
    public function createProducto($id_producto) {
        $producto = Producto::findOrFail($id_producto);
        $qrImage = \App\Models\Parametro::where('clave', 'qr_image_url')->value('valor');
        
        return view('publico.checkout_producto', compact('producto', 'qrImage'));
    }
    public function storeProducto(Request $request, $id_producto) {
        $request->validate([
            'correo' => 'required|email',
            'nombres' => 'required|string|max:150',
            'metodo_pago' => 'required|in:EFECTIVO,QR',
            'cantidad' => 'required|integer|min:1',
        ]);

        $usuario = Usuario::where('correo', $request->correo)->first();

        if ($usuario && (!Auth::check() || Auth::user()->correo !== $request->correo)) {
            return back()->withErrors(['correo' => 'Este correo ya está registrado. Inicia sesión o usa otro.'])->withInput();
        }

        $nuevoUsuario = false;
        DB::transaction(function () use ($request, $id_producto, &$usuario, &$nuevoUsuario) {
            $producto = Producto::whereKey($id_producto)->lockForUpdate()->firstOrFail();
            if ($producto->stock_actual < $request->cantidad) {
                throw ValidationException::withMessages([
                    'cantidad' => 'Stock insuficiente para la cantidad solicitada.',
                ]);
            }

            if (!$usuario) {
                $rolCliente = Rol::where('nombre', 'Cliente')->first();
                if (!$rolCliente) {
                    throw new \RuntimeException('No existe el rol Cliente para registrar la compra.');
                }

                $passwordTemporal = Str::random(8);

                $usuario = Usuario::create([
                    'nombre' => $request->nombres,
                    'correo' => $request->correo,
                    'contrasena_hash' => Hash::make($passwordTemporal),
                    'activo' => true,
                ]);
                $usuario->roles()->attach($rolCliente->id_rol);
                $nuevoUsuario = true;
            }

            $total = $producto->precio * $request->cantidad;
            $estadoVenta = ($request->metodo_pago === 'EFECTIVO') ? 'PAGADA' : 'PENDIENTE_PAGO';

            // Creamos la venta usando un usuario "Directiva" genérico o el mismo cliente
            // (Para simplificar, asignamos la venta al primer usuario con rol Directiva)
            $directiva = Usuario::whereHas('roles', fn($q) => $q->where('nombre', 'Directiva'))->first();
            if (!$directiva) {
                throw new \RuntimeException('No existe un usuario de Directiva para registrar la venta.');
            }

            $venta = Venta::create([
                'id_usuario_directiva' => $directiva->id_usuario,
                'total' => $total,
                'estado' => $estadoVenta,
            ]);

            DetalleVenta::create([
                'id_venta' => $venta->id_venta,
                'id_producto' => $producto->id_producto,
                'cantidad' => $request->cantidad,
                'precio_unitario' => $producto->precio,
                'subtotal' => $total,
            ]);

            if ($request->metodo_pago === 'EFECTIVO') {
                Pago::create([
                    'id_venta' => $venta->id_venta,
                    'monto' => $total,
                    'metodo' => 'EFECTIVO',
                    'estado' => 'CONFIRMADO',
                ]);
                event(new VentaConfirmada($venta));
            }
        });

        if ($nuevoUsuario) {
            Password::sendResetLink(['correo' => $usuario->correo]);
        }

        return redirect('/')->with('success', '¡Compra realizada! Revisa tu correo para crear tu contraseña y ver tu perfil.');
    }
}
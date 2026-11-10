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
use App\Events\VentaConfirmada;
use App\Events\InscripcionConfirmada;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Password; // Para enviar correo de restablecer contraseña

class CheckoutController extends Controller
{
    // ==========================================
    // CHECKOUT DE CURSO (ALUMNO)
    // ==========================================
    public function createCurso($id_curso) {
        $curso = Curso::with('horarios')->findOrFail($id_curso);
        $qrImage = \App\Models\Parametro::where('clave', 'qr_image_url')->value('valor');
        
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

        DB::transaction(function () use ($request, $id_curso, &$usuario) {
            // 1. Si el usuario NO existe, lo creamos automáticamente como Alumno
            if (!$usuario) {
                $rolAlumno = Rol::where('nombre', 'Alumno')->first();
                $passwordTemporal = Str::random(8); // Generamos contraseña temporal

                $usuario = Usuario::create([
                    'nombre' => $request->nombres,
                    'correo' => $request->correo,
                    'contrasena_hash' => Hash::make($passwordTemporal),
                    'activo' => true,
                ]);
                $usuario->roles()->attach($rolAlumno->id_rol);

                // Enviar correo de bienvenida con enlace para crear contraseña
                // (Esto usa el sistema de recuperación de contraseña nativo de Laravel)
                Password::sendResetLink(['correo' => $usuario->correo]);
            }

            // 2. Buscar o crear el Alumno
            $alumno = Alumno::firstOrCreate(
                ['ci' => $request->ci],
                ['nombres' => $request->nombres, 'telefono' => $request->telefono, 'edad' => 18] // Edad por defecto o pedirla
            );

            $horario = HorarioCurso::with('curso')->findOrFail($request->id_horario_curso);
            
            // Verificar cupo
            $inscritos = Inscripcion::where('id_horario_curso', $horario->id_horario_curso)->count();
            if ($inscritos >= $horario->cupo_maximo) {
                throw new \Exception('El cupo está lleno.');
            }

            // 3. Crear la inscripción (Pendiente o Confirmada según pago)
            $estado = ($request->metodo_pago === 'EFECTIVO') ? 'CONFIRMADA' : 'PENDIENTE_PAGO';
            $inscripcion = Inscripcion::create([
                'id_alumno' => $alumno->id_alumno,
                'id_horario_curso' => $horario->id_horario_curso,
                'estado' => $estado,
            ]);

            // 4. Registrar el pago (Si es efectivo se asume que pagó)
            if ($request->metodo_pago === 'EFECTIVO') {
                Pago::create([
                    'id_inscripcion' => $inscripcion->id_inscripcion,
                    'monto' => $horario->curso->costo,
                    'metodo' => 'EFECTIVO',
                    'estado' => 'CONFIRMADO',
                ]);
                event(new InscripcionConfirmada($inscripcion));
            }
        });

        return redirect('/')->with('success', '¡Inscripción realizada! Revisa tu correo para crear tu contraseña y ver tu perfil.');
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

        DB::transaction(function () use ($request, $id_producto, &$usuario) {
            if (!$usuario) {
                $rolCliente = Rol::where('nombre', 'Cliente')->first();
                $passwordTemporal = Str::random(8);

                $usuario = Usuario::create([
                    'nombre' => $request->nombres,
                    'correo' => $request->correo,
                    'contrasena_hash' => Hash::make($passwordTemporal),
                    'activo' => true,
                ]);
                $usuario->roles()->attach($rolCliente->id_rol);
                Password::sendResetLink(['correo' => $usuario->correo]);
            }

            $producto = Producto::findOrFail($id_producto);
            if ($producto->stock_actual < $request->cantidad) {
                throw new \Exception('Stock insuficiente.');
            }

            $total = $producto->precio * $request->cantidad;
            $estadoVenta = ($request->metodo_pago === 'EFECTIVO') ? 'PAGADA' : 'PENDIENTE_PAGO';

            // Creamos la venta usando un usuario "Directiva" genérico o el mismo cliente
            // (Para simplificar, asignamos la venta al primer usuario con rol Directiva)
            $directiva = Usuario::whereHas('roles', fn($q) => $q->where('nombre', 'Directiva'))->first();

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

        return redirect('/')->with('success', '¡Compra realizada! Revisa tu correo para crear tu contraseña y ver tu perfil.');
    }
}
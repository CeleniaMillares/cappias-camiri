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
use App\Events\InscripcionConfirmada;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Password; // Para enviar correo de restablecer contraseña
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    // ==========================================
    // CHECKOUT DE CURSO (ALUMNO)
    // ==========================================
    public function createCurso($id_curso) {
        $curso = Curso::where('activo', true)->with('horarios')->findOrFail($id_curso);
        $qrImage = Parametro::where('clave', 'qr_image_url')->value('valor');
        
        return view('publico.checkout_curso', compact('curso', 'qrImage'));
    }

    public function storeCurso(Request $request, $id_curso) {
        Curso::where('activo', true)->findOrFail($id_curso);

        $request->validate([
            'correo' => 'required|email',
            'nombres' => 'required|string|max:150',
            'ci' => 'required|string|max:20',
            'telefono' => 'required|string|max:20',
            'id_horario_curso' => 'required|exists:horario_cursos,id_horario_curso',
            'metodo_pago' => 'required|in:EFECTIVO,QR',
            'confirmar_qr' => 'sometimes|boolean',
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
        try {
            DB::transaction(function () use ($request, $id_curso, &$usuario, &$nuevoUsuario) {
                $curso = Curso::where('activo', true)
                    ->whereKey($id_curso)
                    ->lockForUpdate()
                    ->firstOrFail();
                $horario = HorarioCurso::with('curso')
                    ->lockForUpdate()
                    ->findOrFail($request->id_horario_curso);
                abort_unless((string) $horario->id_curso === (string) $curso->id_curso, 404);

                $rolAlumno = Rol::where('nombre', 'Alumno')->first();
                if (!$rolAlumno) {
                    throw new \RuntimeException('No existe el rol Alumno para registrar la inscripcion.');
                }

                if (!$usuario) {
                    $passwordTemporal = Str::random(8);

                    $usuario = Usuario::create([
                        'nombre' => $request->nombres,
                        'correo' => $request->correo,
                        'contrasena_hash' => Hash::make($passwordTemporal),
                        'activo' => true,
                    ]);
                    $nuevoUsuario = true;
                }
                $usuario->roles()->syncWithoutDetaching([$rolAlumno->id_rol]);

                $alumnoDeLaCuenta = $usuario->alumno;
                if ($alumnoDeLaCuenta && $alumnoDeLaCuenta->ci !== $request->ci) {
                    throw ValidationException::withMessages([
                        'ci' => 'El CI no coincide con el perfil de alumno asociado a esta cuenta.',
                    ]);
                }

                $alumno = Alumno::firstOrCreate(
                    ['ci' => $request->ci],
                    [
                        'id_usuario' => $usuario->id_usuario,
                        'nombres' => $request->nombres,
                        'telefono' => $request->telefono,
                        'edad' => 18,
                    ]
                );
                if ($alumno->id_usuario && $alumno->id_usuario !== $usuario->id_usuario) {
                    throw ValidationException::withMessages([
                        'ci' => 'Este CI ya está asociado a otra cuenta.',
                    ]);
                }
                if (!$alumno->id_usuario) {
                    $alumno->update(['id_usuario' => $usuario->id_usuario]);
                }

                $yaInscrito = Inscripcion::where('id_alumno', $alumno->id_alumno)
                    ->where('id_horario_curso', $horario->id_horario_curso)
                    ->exists();
                if ($yaInscrito) {
                    throw ValidationException::withMessages([
                        'id_horario_curso' => 'Este alumno ya está inscrito en este horario.',
                    ]);
                }
            
                // Las inscripciones pendientes reservan cupo hasta que la Directiva las resuelva.
                $inscritos = Inscripcion::where('id_horario_curso', $horario->id_horario_curso)->count();
                if ($inscritos >= $horario->cupo_maximo) {
                    throw ValidationException::withMessages([
                        'id_horario_curso' => 'El cupo para este horario está lleno.',
                    ]);
                }

                $qrConfirmado = $request->metodo_pago === 'QR' && $request->boolean('confirmar_qr');
                $inscripcion = Inscripcion::create([
                    'id_alumno' => $alumno->id_alumno,
                    'id_horario_curso' => $horario->id_horario_curso,
                    'estado' => $qrConfirmado ? 'CONFIRMADA' : 'PENDIENTE_PAGO',
                ]);

                Pago::create([
                    'id_inscripcion' => $inscripcion->id_inscripcion,
                    'monto' => $horario->curso->costo,
                    'metodo' => $request->metodo_pago,
                    'estado' => $qrConfirmado ? 'CONFIRMADO' : 'PENDIENTE',
                ]);

                if ($qrConfirmado) {
                    event(new InscripcionConfirmada($inscripcion));
                }
            });
        } catch (QueryException $exception) {
            if (
                $this->isDuplicateEnrollmentException($exception)
                || (
                    $this->isUniqueConstraintViolation($exception)
                    && $this->enrollmentExistsForCiAndSchedule($request->ci, $request->id_horario_curso)
                )
            ) {
                return back()->withErrors([
                    'id_horario_curso' => 'Este alumno ya se encuentra inscrito en este horario.',
                ])->withInput();
            }

            throw $exception;
        }

        if ($nuevoUsuario) {
            Password::sendResetLink(['correo' => $usuario->correo]);
        }

        $mensaje = $request->metodo_pago === 'QR' && $request->boolean('confirmar_qr')
            ? 'Inscripción confirmada y pago QR registrado.'
            : 'Inscripción registrada. La Directiva verificará tu pago para confirmar tu cupo.';

        return redirect('/')->with('success', $mensaje);
    }

    private function isDuplicateEnrollmentException(QueryException $exception): bool
    {
        $databaseMessage = $exception->errorInfo[2] ?? $exception->getMessage();

        return str_contains($databaseMessage, 'inscripciones_id_alumno_id_horario_curso_unique')
            || (
                str_contains($databaseMessage, 'inscripciones.id_alumno')
                && str_contains($databaseMessage, 'inscripciones.id_horario_curso')
            );
    }

    private function isUniqueConstraintViolation(QueryException $exception): bool
    {
        $sqlState = $exception->errorInfo[0] ?? (string) $exception->getCode();

        return in_array($sqlState, ['23505', '23000', '19'], true);
    }

    private function enrollmentExistsForCiAndSchedule(string $ci, string $idHorarioCurso): bool
    {
        $idAlumno = Alumno::where('ci', $ci)->value('id_alumno');

        return $idAlumno !== null && Inscripcion::where('id_alumno', $idAlumno)
            ->where('id_horario_curso', $idHorarioCurso)
            ->exists();
    }

    // ==========================================
    // CHECKOUT DE PRODUCTO (CLIENTE)
    // ==========================================
    public function createProducto($id_producto) {
        $producto = Producto::where('activo', true)->findOrFail($id_producto);
        $qrImage = \App\Models\Parametro::where('clave', 'qr_image_url')->value('valor');
        
        return view('publico.checkout_producto', compact('producto', 'qrImage'));
    }
    public function storeProducto(Request $request, $id_producto) {
        Producto::where('activo', true)->findOrFail($id_producto);

        $request->validate([
            'correo' => 'required|email',
            'nombres' => 'required|string|max:150',
            'metodo_pago' => 'required|in:EFECTIVO,QR',
            'cantidad' => 'required|integer|min:1',
            'confirmar_qr' => 'sometimes|boolean',
        ]);

        $usuario = Usuario::where('correo', $request->correo)->first();

        if ($usuario && (!Auth::check() || Auth::user()->correo !== $request->correo)) {
            return back()->withErrors([
                'correo' => 'Este correo ya está registrado. Inicia sesión para continuar o usa otro correo.',
            ])->withInput();
        }

        $nuevoUsuario = false;
        DB::transaction(function () use ($request, $id_producto, &$usuario, &$nuevoUsuario) {
            $producto = Producto::where('activo', true)
                ->whereKey($id_producto)
                ->lockForUpdate()
                ->firstOrFail();
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
            $pagoConfirmado = $request->metodo_pago === 'EFECTIVO'
                || ($request->metodo_pago === 'QR' && $request->boolean('confirmar_qr'));

            // Creamos la venta usando un usuario "Directiva" genérico o el mismo cliente
            // (Para simplificar, asignamos la venta al primer usuario con rol Directiva)
            $directiva = Usuario::whereHas('roles', fn($q) => $q->where('nombre', 'Directiva'))->first();
            if (!$directiva) {
                throw new \RuntimeException('No existe un usuario de Directiva para registrar la venta.');
            }

            $venta = Venta::create([
                'id_usuario_directiva' => $directiva->id_usuario,
                'total' => $total,
                'estado' => $pagoConfirmado ? 'PAGADA' : 'PENDIENTE_PAGO',
            ]);

            DetalleVenta::create([
                'id_venta' => $venta->id_venta,
                'id_producto' => $producto->id_producto,
                'cantidad' => $request->cantidad,
                'precio_unitario' => $producto->precio,
                'subtotal' => $total,
            ]);

            if ($pagoConfirmado) {
                Pago::create([
                    'id_venta' => $venta->id_venta,
                    'monto' => $total,
                    'metodo' => $request->metodo_pago,
                    'estado' => 'CONFIRMADO',
                ]);
                event(new VentaConfirmada($venta));
            }
        });

        if ($nuevoUsuario) {
            Password::sendResetLink(['correo' => $usuario->correo]);
        }

        $mensaje = $request->metodo_pago === 'QR' && !$request->boolean('confirmar_qr')
            ? 'Compra registrada. La Directiva verificará tu pago para confirmarla.'
            : '¡Compra realizada! Revisa tu correo para crear tu contraseña y ver tu perfil.';

        return redirect('/')->with('success', $mensaje);
    }
}
<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Inscripcion;
use App\Models\Alumno;
use App\Models\HorarioCurso;
use App\Models\Pago;
use App\Models\Parametro; // 🔥 IMPORTANTE
use App\Events\InscripcionConfirmada;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InscripcionController extends Controller
{
    public function index() {
        /** @var \App\Models\Usuario $user */
        $user = Auth::user();
        $query = Inscripcion::with(['alumno', 'horarioCurso.curso', 'pagos']);

        if ($user->hasRole('Alumno')) {
            $query->where('id_alumno', $user->alumno?->id_alumno ?? '');
        } elseif ($user->hasRole('Socio/Instructor')) {
            $query->whereHas('horarioCurso.curso', fn($q) => $q->where('id_usuario_instructor', $user->id_usuario));
        }

        $inscripciones = $query->orderBy('created_at', 'desc')->get();
        return view('admin.inscripciones.index', compact('inscripciones'));
    }

    public function create($id_horario_curso) {
        $horario = HorarioCurso::with('curso')->findOrFail($id_horario_curso);
        $user = Auth::user();
        if ($user->hasRole('Socio/Instructor') && $horario->curso->id_usuario_instructor !== $user->id_usuario) {
            abort(403, 'No puedes inscribir alumnos en cursos que no dictas.');
        }

        // 🔥 Cargamos el QR oficial
        $qrImage = Parametro::where('clave', 'qr_image_url')->value('valor');
        return view('admin.inscripciones.create', compact('horario', 'qrImage'));
    }

    public function store(Request $request) {
        $request->validate([
            'id_horario_curso' => 'required|exists:horario_cursos,id_horario_curso',
            'nombres' => 'required|string|max:150',
            'ci' => 'required|string|max:20',
            'telefono' => 'required|string|max:20',
            'edad' => 'required|integer|min:1',
            'metodo_pago' => 'required|in:EFECTIVO,QR,MIXTO',
            'monto_efectivo' => 'required_if:metodo_pago,MIXTO|nullable|numeric|min:0',
        ]);

        $user = Auth::user();
        $horarioParaValidar = HorarioCurso::with('curso')->findOrFail($request->id_horario_curso);
        if ($user->hasRole('Socio/Instructor') && $horarioParaValidar->curso->id_usuario_instructor !== $user->id_usuario) {
            abort(403, 'No puedes inscribir alumnos en cursos que no dictas.');
        }

        try {
            DB::transaction(function () use ($request, $user) {
                $alumno = Alumno::firstOrCreate(
                    ['ci' => $request->ci],
                    ['nombres' => $request->nombres, 'telefono' => $request->telefono, 'edad' => $request->edad]
                );

                $horario = HorarioCurso::with('curso')
                    ->lockForUpdate()
                    ->findOrFail($request->id_horario_curso);
                if ($user->hasRole('Socio/Instructor') && $horario->curso->id_usuario_instructor !== $user->id_usuario) {
                    abort(403, 'No puedes inscribir alumnos en cursos que no dictas.');
                }

                $yaInscrito = Inscripcion::where('id_alumno', $alumno->id_alumno)
                    ->where('id_horario_curso', $horario->id_horario_curso)->exists();
                if ($yaInscrito) throw new \Exception('Este alumno ya se encuentra inscrito en este curso.');

                $inscritos = Inscripcion::where('id_horario_curso', $horario->id_horario_curso)->count();
                if ($inscritos >= $horario->cupo_maximo) throw new \Exception('El cupo máximo para este horario está lleno.');

                $inscripcion = Inscripcion::create([
                    'id_alumno' => $alumno->id_alumno,
                    'id_horario_curso' => $horario->id_horario_curso,
                    'estado' => 'PENDIENTE_PAGO'
                ]);

                $costoCurso = $horario->curso->costo;
                if ($request->metodo_pago === 'MIXTO') {
                    $montoEfectivo = (float) $request->monto_efectivo;
                    if ($montoEfectivo > $costoCurso) {
                        throw new \RuntimeException('El monto en efectivo no puede superar el costo del curso.');
                    }

                    if ($montoEfectivo > 0) {
                        Pago::create([
                            'id_inscripcion' => $inscripcion->id_inscripcion,
                            'monto' => $montoEfectivo,
                            'metodo' => 'EFECTIVO',
                            'estado' => 'PENDIENTE',
                        ]);
                    }

                    $montoQr = $costoCurso - $montoEfectivo;
                    if ($montoQr > 0) {
                        Pago::create([
                            'id_inscripcion' => $inscripcion->id_inscripcion,
                            'monto' => $montoQr,
                            'metodo' => 'QR',
                            'estado' => 'PENDIENTE',
                        ]);
                    }
                } else {
                    Pago::create([
                        'id_inscripcion' => $inscripcion->id_inscripcion,
                        'monto' => $costoCurso,
                        'metodo' => $request->metodo_pago,
                        'estado' => 'PENDIENTE',
                    ]);
                }
            });

            return redirect('/admin/inscripciones')->with('success', 'Inscripción registrada correctamente.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e) {
            throw $e;
        } catch (QueryException $e) {
            if ($this->isDuplicateEnrollmentException($e)) {
                return back()->withErrors([
                    'ci' => 'Este alumno ya se encuentra inscrito en este horario.',
                ])->withInput();
            }

            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    private function isDuplicateEnrollmentException(QueryException $exception): bool
    {
        $sqlState = $exception->errorInfo[0] ?? (string) $exception->getCode();
        $databaseMessage = $exception->errorInfo[2] ?? $exception->getMessage();

        if (!in_array($sqlState, ['23505', '23000', '19'], true)) {
            return false;
        }

        return str_contains($databaseMessage, 'inscripciones_id_alumno_id_horario_curso_unique')
            || (
                str_contains($databaseMessage, 'inscripciones.id_alumno')
                && str_contains($databaseMessage, 'inscripciones.id_horario_curso')
            );
    }

    public function confirmar($id) {
        $resultado = DB::transaction(function () use ($id) {
            $inscripcion = Inscripcion::whereKey($id)->lockForUpdate()->firstOrFail();
            if ($inscripcion->estado === 'CONFIRMADA') {
                return 'ya_confirmada';
            }

            $costoCurso = $inscripcion->horarioCurso->curso->costo;
            $pagos = Pago::where('id_inscripcion', $inscripcion->id_inscripcion)
                ->lockForUpdate()
                ->get();
            $totalRegistrado = round((float) $pagos->sum('monto'), 2);

            if ($totalRegistrado !== round((float) $costoCurso, 2)) {
                return 'monto_invalido';
            }

            $pagosPendientes = $pagos->where('estado', 'PENDIENTE');
            if ($pagosPendientes->isEmpty()) {
                return 'sin_pagos_pendientes';
            }

            Pago::whereIn('id_pago', $pagosPendientes->pluck('id_pago'))
                ->update(['estado' => 'CONFIRMADO']);
            $inscripcion->update(['estado' => 'CONFIRMADA']);
            event(new InscripcionConfirmada($inscripcion));
            return 'confirmada';
        });

        if ($resultado === 'ya_confirmada') {
            return back()->with('error', 'La inscripción ya está confirmada.');
        }

        if ($resultado === 'monto_invalido' || $resultado === 'sin_pagos_pendientes') {
            return back()->with('error', 'No se puede confirmar: revisa los pagos registrados de esta inscripción.');
        }

        return redirect('/admin/inscripciones')->with('success', 'Pago confirmado. Comisión calculada.');
    }
}
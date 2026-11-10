<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Alumno;
use App\Models\Curso;
use App\Models\HorarioCurso;
use App\Models\Inscripcion;
use App\Models\Pago;
use App\Models\Parametro;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class AlumnoController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $buscar = trim((string) $request->input('buscar'));

        $alumnosQuery = Alumno::query()
            ->with(['inscripciones.horarioCurso.curso'])
            ->when($buscar !== '', function ($query) use ($buscar) {
                $query->where(function ($query) use ($buscar) {
                    $query->where('nombres', 'LIKE', "%{$buscar}%")
                        ->orWhere('ci', 'LIKE', "%{$buscar}%");
                });
            });

        $pendientesQuery = Inscripcion::with(['alumno', 'horarioCurso.curso', 'pagos'])
            ->where('estado', 'PENDIENTE_PAGO')
            ->when($buscar !== '', function ($query) use ($buscar) {
                $query->whereHas('alumno', function ($query) use ($buscar) {
                    $query->where(function ($query) use ($buscar) {
                        $query->where('nombres', 'LIKE', "%{$buscar}%")
                            ->orWhere('ci', 'LIKE', "%{$buscar}%");
                    });
                });
            });

        if ($user->hasRole('Socio/Instructor')) {
            $cursoDelInstructor = fn ($query) => $query->where(
                'id_usuario_instructor',
                $user->id_usuario
            );
            $alumnosQuery->whereHas('inscripciones.horarioCurso.curso', $cursoDelInstructor);
            $alumnosQuery->with([
                'inscripciones' => fn ($query) => $query->whereHas(
                    'horarioCurso.curso',
                    $cursoDelInstructor
                ),
            ]);
            $pendientesQuery->whereHas('horarioCurso.curso', $cursoDelInstructor);
        }

        $alumnos = $alumnosQuery->orderBy('nombres')->get();
        $inscripcionesPendientes = $pendientesQuery->orderByDesc('created_at')->get();

        return view('admin.alumnos.index', compact('alumnos', 'inscripcionesPendientes', 'buscar'));
    }

    public function create()
    {
        $user = Auth::user();
        $cursosQuery = Curso::with('horarios')->where('activo', true);

        if ($user->hasRole('Socio/Instructor')) {
            $cursosQuery->where('id_usuario_instructor', $user->id_usuario);
        }

        $cursos = $cursosQuery->orderBy('nombre')->get();
        $qrImage = Parametro::where('clave', 'qr_image_url')->value('valor');

        return view('admin.alumnos.inscribir', compact('cursos', 'qrImage'));
    }

    public function store(Request $request)
    {
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

        try {
            DB::transaction(function () use ($request, $user) {
                $horario = HorarioCurso::with('curso')
                    ->lockForUpdate()
                    ->findOrFail($request->id_horario_curso);

                if (! $horario->curso->activo) {
                    throw new \RuntimeException('Este curso ya no está disponible.');
                }

                if (
                    $user->hasRole('Socio/Instructor')
                    && $horario->curso->id_usuario_instructor !== $user->id_usuario
                ) {
                    abort(403, 'No puedes inscribir alumnos en cursos que no dictas.');
                }

                $alumno = Alumno::firstOrCreate(
                    ['ci' => $request->ci],
                    [
                        'nombres' => $request->nombres,
                        'telefono' => $request->telefono,
                        'edad' => $request->edad,
                    ]
                );

                if (Inscripcion::where('id_alumno', $alumno->id_alumno)
                    ->where('id_horario_curso', $horario->id_horario_curso)
                    ->exists()) {
                    throw new \RuntimeException('Este alumno ya se encuentra inscrito en este horario.');
                }

                $inscritos = Inscripcion::where('id_horario_curso', $horario->id_horario_curso)->count();
                if ($inscritos >= $horario->cupo_maximo) {
                    throw new \RuntimeException('El cupo máximo para este horario está lleno.');
                }

                $costoCurso = (float) $horario->curso->costo;
                $montoEfectivo = $request->metodo_pago === 'MIXTO'
                    ? (float) $request->monto_efectivo
                    : 0;

                if ($request->metodo_pago === 'MIXTO' && $montoEfectivo > $costoCurso) {
                    throw new \RuntimeException('El monto en efectivo no puede superar el costo del curso.');
                }

                $inscripcion = Inscripcion::create([
                    'id_alumno' => $alumno->id_alumno,
                    'id_horario_curso' => $horario->id_horario_curso,
                    'estado' => 'PENDIENTE_PAGO',
                ]);

                if ($request->metodo_pago === 'MIXTO') {
                    if ($montoEfectivo > 0) {
                        Pago::create([
                            'id_inscripcion' => $inscripcion->id_inscripcion,
                            'monto' => $montoEfectivo,
                            'metodo' => 'EFECTIVO',
                            'estado' => 'PENDIENTE',
                        ]);
                    }

                    $montoQr = round($costoCurso - $montoEfectivo, 2);
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

            return redirect('/admin/alumnos')->with('success', 'Inscripción registrada correctamente.');
        } catch (HttpExceptionInterface $exception) {
            throw $exception;
        } catch (QueryException $exception) {
            $sqlState = $exception->errorInfo[0] ?? (string) $exception->getCode();
            $message = $exception->errorInfo[2] ?? $exception->getMessage();

            if (
                in_array($sqlState, ['23505', '23000', '19'], true)
                && str_contains($message, 'inscripciones')
            ) {
                return back()->withErrors([
                    'ci' => 'Este alumno ya se encuentra inscrito en este horario.',
                ])->withInput();
            }

            throw $exception;
        } catch (\RuntimeException $exception) {
            return back()->withErrors(['error' => $exception->getMessage()])->withInput();
        }
    }

    public function edit($id)
    {
        $alumno = Alumno::findOrFail($id);
        $this->authorizeAlumno($alumno);

        return view('admin.alumnos.edit', compact('alumno'));
    }

    public function update(Request $request, $id)
    {
        $alumno = Alumno::findOrFail($id);
        $this->authorizeAlumno($alumno);

        $request->validate([
            'nombres' => 'required|string|max:150',
            'ci' => [
                'required',
                'string',
                'max:20',
                Rule::unique('alumnos', 'ci')->ignore($alumno->id_alumno, 'id_alumno'),
            ],
            'telefono' => 'required|string|max:20',
            'edad' => 'required|integer|min:1',
        ]);

        $alumno->update($request->only(['nombres', 'ci', 'telefono', 'edad']));

        return redirect("/admin/alumnos/{$alumno->id_alumno}/editar")
            ->with('success', 'Datos del alumno actualizados correctamente.');
    }

    public function historial($id)
    {
        $alumno = Alumno::with([
            'inscripciones.horarioCurso.curso',
            'inscripciones.pagos',
        ])->findOrFail($id);
        $this->authorizeAlumno($alumno);

        $inscripciones = $alumno->inscripciones
            ->when(Auth::user()->hasRole('Socio/Instructor'), function ($inscripciones) {
                return $inscripciones->filter(
                    fn ($inscripcion) => $inscripcion->horarioCurso->curso->id_usuario_instructor
                        === Auth::user()->id_usuario
                );
            })
            ->sortByDesc('created_at');

        return view('admin.alumnos.historial', compact('alumno', 'inscripciones'));
    }

    private function authorizeAlumno(Alumno $alumno): void
    {
        $user = Auth::user();
        if (
            $user->hasRole('Socio/Instructor')
            && ! $alumno->inscripciones()
                ->whereHas('horarioCurso.curso', fn ($query) => $query->where(
                    'id_usuario_instructor',
                    $user->id_usuario
                ))
                ->exists()
        ) {
            abort(403, 'No puedes consultar alumnos de cursos que no dictas.');
        }
    }
}

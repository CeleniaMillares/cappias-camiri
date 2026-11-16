<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Curso;
use App\Models\Usuario;
use App\Models\HorarioCurso;
use App\Models\Parametro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class CursoController extends Controller
{
    public function index() {
        /** @var \App\Models\Usuario $user */
        $user = Auth::user();

        // Si es Socio, solo ve sus propios cursos
        if ($user->hasRole('Socio/Instructor')) {
            $cursos = Curso::with(['instructor', 'horarios'])
                ->where('id_usuario_instructor', $user->id_usuario)
                ->where('activo', true)
                ->get();
        } else {
            // Si es Directiva, ve todos los cursos
            $cursos = Curso::with(['instructor', 'horarios'])
                ->where('activo', true)
                ->get();
        }

        return view('admin.cursos.index', compact('cursos'));
    }

    public function create() {
        $instructores = Usuario::whereHas('roles', fn($q) => $q->where('nombre', 'Socio/Instructor'))->get();
        $cupoMin = (int) (Parametro::where('clave', 'cupo_minimo')->value('valor') ?? 6);
        $cupoMax = (int) (Parametro::where('clave', 'cupo_maximo')->value('valor') ?? 15);
        $horariosFormulario = old('horarios', [[]]);

        return view('admin.cursos.create', compact('instructores', 'cupoMin', 'cupoMax', 'horariosFormulario'));
    }

    public function store(Request $request) {
        $cupoMin = (int) (Parametro::where('clave', 'cupo_minimo')->value('valor') ?? 6);
        $cupoMax = (int) (Parametro::where('clave', 'cupo_maximo')->value('valor') ?? 15);

        $validated = $request->validate([
            'id_usuario_instructor' => 'required|uuid|exists:usuarios,id_usuario',
            'nombre' => ['required', 'string', 'max:150', 'regex:/^[\pL\s\-]+$/u'],
            'descripcion' => 'nullable|string',
            'costo' => 'required|numeric|min:0.01',
            'imagen_url' => 'nullable|image|max:2048',
            'horarios' => 'required|array|min:1',
            'horarios.*.dia_semana' => 'required|string|max:100',
            'horarios.*.hora_inicio' => 'required|date_format:H:i',
            'horarios.*.hora_fin' => 'required|date_format:H:i',
            'horarios.*.fecha_inicio' => 'required|date_format:Y-m-d',
            'horarios.*.fecha_fin' => 'required|date_format:Y-m-d|after_or_equal:horarios.*.fecha_inicio',
            'horarios.*.cupo_maximo' => "required|integer|min:{$cupoMin}|max:{$cupoMax}",
        ]);

        $imagenPath = $request->file('imagen_url')?->store('cursos', 'public');
        if ($request->hasFile('imagen_url') && ! $imagenPath) {
            throw new \RuntimeException('No se pudo guardar la imagen del curso.');
        }

        try {
            return DB::transaction(function () use ($validated, $imagenPath) {
                $curso = Curso::create([
                    'id_usuario_instructor' => $validated['id_usuario_instructor'],
                    'nombre' => $validated['nombre'],
                    'descripcion' => $validated['descripcion'] ?? null,
                    'costo' => $validated['costo'],
                    'imagen_url' => $imagenPath,
                    'activo' => true,
                ]);

                foreach ($validated['horarios'] as $horario) {
                    $curso->horarios()->create($horario);
                }

                return redirect('/admin/cursos')->with('success', 'Curso registrado exitosamente.');
            });
        } catch (Throwable $exception) {
            if ($imagenPath) {
                Storage::disk('public')->delete($imagenPath);
            }

            throw $exception;
        }
    }

    public function edit($id) {
        $curso = Curso::with(['horarios' => fn ($query) => $query
            ->orderBy('created_at')
            ->orderBy('id_horario_curso')])->findOrFail($id);
        $instructores = Usuario::whereHas('roles', fn($q) => $q->where('nombre', 'Socio/Instructor'))->get();
        $cupoMin = (int) (Parametro::where('clave', 'cupo_minimo')->value('valor') ?? 6);
        $cupoMax = (int) (Parametro::where('clave', 'cupo_maximo')->value('valor') ?? 15);
        $horariosGuardados = $curso->horarios->map(fn ($horario) => [
            'id_horario_curso' => $horario->id_horario_curso,
            'dia_semana' => $horario->dia_semana,
            'hora_inicio' => $horario->hora_inicio,
            'hora_fin' => $horario->hora_fin,
            'fecha_inicio' => $horario->fecha_inicio,
            'fecha_fin' => $horario->fecha_fin,
            'cupo_maximo' => $horario->cupo_maximo,
        ])->all();
        $horariosFormulario = old('horarios', $horariosGuardados ?: [[]]);

        return view('admin.cursos.edit', compact('curso', 'instructores', 'cupoMin', 'cupoMax', 'horariosFormulario'));
    }

    public function update(Request $request, $id) {
        $curso = Curso::findOrFail($id);
        $cupoMin = (int) (Parametro::where('clave', 'cupo_minimo')->value('valor') ?? 6);
        $cupoMax = (int) (Parametro::where('clave', 'cupo_maximo')->value('valor') ?? 15);

        $validated = $request->validate([
            'id_usuario_instructor' => 'required|exists:usuarios,id_usuario',
            'nombre' => ['required', 'string', 'max:150', 'regex:/^[\pL\s\-]+$/u'],
            'descripcion' => 'nullable|string',
            'costo' => 'required|numeric|min:0.01',
            'imagen_url' => 'nullable|image|max:2048',
            'horarios' => 'required|array|min:1',
            'horarios.*.id_horario_curso' => [
                'nullable',
                'uuid',
                Rule::exists('horario_cursos', 'id_horario_curso')
                    ->where('id_curso', $curso->id_curso),
            ],
            'horarios.*.dia_semana' => 'required|string|max:100',
            'horarios.*.hora_inicio' => 'required|date_format:H:i',
            'horarios.*.hora_fin' => 'required|date_format:H:i',
            'horarios.*.fecha_inicio' => 'required|date_format:Y-m-d',
            'horarios.*.fecha_fin' => 'required|date_format:Y-m-d|after_or_equal:horarios.*.fecha_inicio',
            'horarios.*.cupo_maximo' => "required|integer|min:{$cupoMin}|max:{$cupoMax}",
        ]);

        $imagenAnterior = $curso->imagen_url;
        $imagenPath = $request->file('imagen_url')?->store('cursos', 'public') ?? $imagenAnterior;
        if ($request->hasFile('imagen_url') && ! $imagenPath) {
            throw new \RuntimeException('No se pudo guardar la imagen del curso.');
        }

        try {
            $result = DB::transaction(function () use ($curso, $validated, $imagenPath) {
                $curso->update([
                    'id_usuario_instructor' => $validated['id_usuario_instructor'],
                    'nombre' => $validated['nombre'],
                    'descripcion' => $validated['descripcion'] ?? null,
                    'costo' => $validated['costo'],
                    'imagen_url' => $imagenPath,
                ]);

                foreach ($validated['horarios'] as $horarioData) {
                    $idHorario = $horarioData['id_horario_curso'] ?? null;
                    unset($horarioData['id_horario_curso']);

                    if ($idHorario) {
                        $horario = $curso->horarios()
                            ->whereKey($idHorario)
                            ->lockForUpdate()
                            ->firstOrFail();
                        $cantidadInscritos = $horario->inscripciones()->count();

                        if ($cantidadInscritos > $horarioData['cupo_maximo']) {
                            throw ValidationException::withMessages([
                                'horarios' => 'El cupo de un grupo no puede ser menor que la cantidad de alumnos ya inscritos.',
                            ]);
                        }

                        $horario->update($horarioData);
                    } else {
                        $curso->horarios()->create($horarioData);
                    }
                }

                return redirect('/admin/cursos')->with('success', 'Curso actualizado correctamente.');
            });
        } catch (Throwable $exception) {
            if ($imagenPath !== $imagenAnterior) {
                Storage::disk('public')->delete($imagenPath);
            }

            throw $exception;
        }

        if ($imagenPath !== $imagenAnterior && $imagenAnterior) {
            Storage::disk('public')->delete($imagenAnterior);
        }

        return $result;
    }

    public function destroy($id) {
        $curso = Curso::findOrFail($id);
        $curso->update(['activo' => false]);
        return redirect('/admin/cursos')->with('success', 'Curso desactivado. Ya no estará disponible para inscripciones.');
    }

    // 🔥 MÉTODO QUE FALTABA: Mostrar alumnos inscritos
    // Mostrar los alumnos inscritos en un curso específico
    public function showAlumnos($id_curso) {
        $curso = Curso::with(['horarios.inscripciones.alumno', 'horarios.inscripciones.pagos'])
            ->findOrFail($id_curso);

        $user = Auth::user();
        if ($user->hasRole('Socio/Instructor') && $curso->id_usuario_instructor !== $user->id_usuario) {
            abort(403);
        }

        return view('admin.cursos.alumnos', compact('curso'));
    }
}
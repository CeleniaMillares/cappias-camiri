<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Curso;
use App\Models\Usuario;
use App\Models\HorarioCurso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
        return view('admin.cursos.create', compact('instructores'));
    }

    public function store(Request $request) {
        $request->validate([
            'id_usuario_instructor' => 'required|uuid|exists:usuarios,id_usuario',
            'nombre' => 'required|string|max:150',
            'duracion' => 'required|string|max:50',
            'costo' => 'required|numeric|min:0',
            'imagen_url' => 'nullable|image|max:2048',
            'horarios' => 'required|array|min:1',
            'horarios.*.dia_semana' => 'required|string|max:100',
            'horarios.*.hora_inicio' => 'required',
            'horarios.*.hora_fin' => 'required',
            'horarios.*.fecha_inicio' => 'required|date',
            'horarios.*.fecha_fin' => 'required|date|after_or_equal:horarios.*.fecha_inicio',
            'horarios.*.cupo_maximo' => 'required|integer|max:15',
        ]);

        return DB::transaction(function () use ($request) {
            
            $imagenPath = null;
            if ($request->hasFile('imagen_url')) {
                $imagenPath = $request->file('imagen_url')->store('cursos', 'public');
            }

            $curso = Curso::create([
                'id_usuario_instructor' => $request->id_usuario_instructor,
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'duracion' => $request->duracion,
                'costo' => $request->costo,
                'imagen_url' => $imagenPath,
                'activo' => true
            ]);

            foreach ($request->horarios as $horario) {
                HorarioCurso::create([
                    'id_curso' => $curso->id_curso,
                    'dia_semana' => $horario['dia_semana'],
                    'hora_inicio' => $horario['hora_inicio'],
                    'hora_fin' => $horario['hora_fin'],
                    'fecha_inicio' => $horario['fecha_inicio'],
                    'fecha_fin' => $horario['fecha_fin'],
                    'cupo_maximo' => $horario['cupo_maximo'],
                ]);
            }

            return redirect('/admin/cursos')->with('success', 'Curso registrado exitosamente.');
        });
    }

    public function edit($id) {
        $curso = Curso::with('horarios')->findOrFail($id);
        $instructores = Usuario::whereHas('roles', fn($q) => $q->where('nombre', 'Socio/Instructor'))->get();
        $horario = $curso->horarios->first();
        
        return view('admin.cursos.edit', compact('curso', 'instructores', 'horario'));
    }

    public function update(Request $request, $id) {
        $request->validate([
            'id_usuario_instructor' => 'required|exists:usuarios,id_usuario',
            'nombre' => 'required',
            'costo' => 'required|numeric',
            'duracion' => 'required',
            'imagen_url' => 'nullable|image|max:2048',
            'horarios.0.dia_semana' => 'required|max:100',
            'horarios.0.hora_inicio' => 'required',
            'horarios.0.hora_fin' => 'required',
            'horarios.0.fecha_inicio' => 'required|date',
            'horarios.0.fecha_fin' => 'required|date',
            'horarios.0.cupo_maximo' => 'required|integer',
        ]);

        return DB::transaction(function () use ($request, $id) {
            $curso = Curso::findOrFail($id);
            
            $imagenPath = $curso->imagen_url;
            if ($request->hasFile('imagen_url')) {
                if ($curso->imagen_url) {
                    Storage::disk('public')->delete($curso->imagen_url);
                }
                $imagenPath = $request->file('imagen_url')->store('cursos', 'public');
            }

            $curso->update([
                'id_usuario_instructor' => $request->id_usuario_instructor,
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'duracion' => $request->duracion,
                'costo' => $request->costo,
                'imagen_url' => $imagenPath,
            ]);

            if ($curso->horarios->isNotEmpty()) {
                $curso->horarios->first()->update($request->horarios[0]);
            }

            return redirect('/admin/cursos')->with('success', 'Curso actualizado correctamente.');
        });
    }

    public function destroy($id) {
        $curso = Curso::findOrFail($id);
        $curso->update(['activo' => false]);
        return redirect('/admin/cursos')->with('success', 'Curso desactivado. Ya no estará disponible para inscripciones.');
    }

    // 🔥 MÉTODO QUE FALTABA: Mostrar alumnos inscritos
    // Mostrar los alumnos inscritos en un curso específico
    public function showAlumnos($id_curso) {
        // Cargamos el curso con sus horarios e inscripciones (solo de ese curso)
        $curso = Curso::with(['horarios.inscripciones.alumno'])->findOrFail($id_curso);
        
            
        return view('admin.cursos.alumnos', compact('curso'));
    }
}
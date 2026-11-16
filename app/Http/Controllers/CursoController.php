<?php
namespace App\Http\Controllers;

use App\Models\Curso;
use App\Models\HorarioCurso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CursoController extends Controller
{
    public function index() {
        return response()->json(Curso::with(['instructor', 'horarios'])->where('activo', true)->get());
    }

    public function store(Request $request) {
        $request->validate([
            'id_usuario_instructor' => 'required|uuid|exists:usuarios,id_usuario',
            'nombre' => 'required|string|max:150',
            'costo' => 'required|numeric|min:0',
            'horarios' => 'required|array|min:1',
            'horarios.*.dia_semana' => 'required|string',
            'horarios.*.hora_inicio' => 'required',
            'horarios.*.hora_fin' => 'required',
            'horarios.*.fecha_inicio' => 'required|date',
            'horarios.*.fecha_fin' => 'required|date|after_or_equal:horarios.*.fecha_inicio',
            'horarios.*.cupo_maximo' => 'required|integer|max:15', // Regla: máximo 15
        ]);

        return DB::transaction(function () use ($request) {
            // 1. Crear el curso
            $curso = Curso::create([
                'id_usuario_instructor' => $request->id_usuario_instructor,
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'costo' => $request->costo,
                'activo' => true
            ]);

            // 2. Crear los horarios
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

            return response()->json([
                'mensaje' => 'Curso registrado exitosamente',
                'curso' => $curso->load('horarios')
            ], 201);
        });
    }
}
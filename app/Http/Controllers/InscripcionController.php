<?php
namespace App\Http\Controllers;

use App\Models\Inscripcion;
use App\Models\Alumno;
use App\Models\HorarioCurso;
use App\Events\InscripcionConfirmada;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InscripcionController extends Controller
{
    // RF-08: Inscribir alumno
    public function store(Request $request) {
        $request->validate([
            'id_horario_curso' => 'required|uuid|exists:horario_cursos,id_horario_curso',
            'nombres' => 'required|string|max:150',
            'ci' => 'required|string|max:20',
            'telefono' => 'required|string|max:20',
            'edad' => 'required|integer|min:1',
        ]);

        return DB::transaction(function () use ($request) {
            // 1. Buscar o crear al alumno (por su CI)
            $alumno = Alumno::firstOrCreate(
                ['ci' => $request->ci],
                ['nombres' => $request->nombres, 'telefono' => $request->telefono, 'edad' => $request->edad]
            );

            // 2. Verificar cupo disponible
            $horario = HorarioCurso::findOrFail($request->id_horario_curso);
            $inscritos = Inscripcion::where('id_horario_curso', $horario->id_horario_curso)->count();

            if ($inscritos >= $horario->cupo_maximo) {
                abort(400, 'El cupo máximo para este horario está lleno.');
            }

            // 3. Verificar que no esté ya inscrito
            $existe = Inscripcion::where('id_alumno', $alumno->id_alumno)
                                 ->where('id_horario_curso', $horario->id_horario_curso)
                                 ->exists();
            if ($existe) {
                abort(400, 'El alumno ya está inscrito en este horario.');
            }

            // 4. Crear la inscripción (Pendiente de Pago)
            $inscripcion = Inscripcion::create([
                'id_alumno' => $alumno->id_alumno,
                'id_horario_curso' => $horario->id_horario_curso,
                'estado' => 'PENDIENTE_PAGO'
            ]);

            return response()->json([
                'mensaje' => 'Inscripción registrada. Proceda al pago.',
                'inscripcion' => $inscripcion
            ], 201);
        });
    }

    // RF-10: Confirmar inscripción (La directiva verifica el pago)
    public function confirmar($id) {
        $inscripcion = Inscripcion::findOrFail($id);
        
        if ($inscripcion->estado === 'CONFIRMADA') {
            abort(400, 'La inscripción ya está confirmada.');
        }

        $inscripcion->update(['estado' => 'CONFIRMADA']);

        // 🔥 Disparamos el evento automático para el cálculo del 20%
        event(new InscripcionConfirmada($inscripcion));

        return response()->json([
            'mensaje' => 'Inscripción confirmada exitosamente.',
            'inscripcion' => $inscripcion
        ]);
    }
}
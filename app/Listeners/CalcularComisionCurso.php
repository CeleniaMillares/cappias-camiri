<?php
namespace App\Listeners;
use App\Events\InscripcionConfirmada;
use App\Models\Parametro;
use App\Models\Inscripcion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CalcularComisionCurso
{
    public function handle(InscripcionConfirmada $event): void
    {
        DB::transaction(function () use ($event) {
            $inscripcion = Inscripcion::whereKey($event->inscripcion->id_inscripcion)
                ->lockForUpdate()
                ->firstOrFail();

            if ($inscripcion->comision_calculada) {
                Log::info("Comisión ya calculada para Inscripción {$inscripcion->id_inscripcion}");
                return;
            }

            $inscripcion->load('horarioCurso.curso');
            $costoCurso = (float) $inscripcion->horarioCurso->curso->costo;
            $porcentaje = (float) (Parametro::where('clave', 'comision_curso')->value('valor') ?? 20);
            $comision = round(($costoCurso * $porcentaje) / 100, 2);

            $inscripcion->update([
                'comision_cappias' => $comision,
                'comision_calculada' => true,
            ]);

            Log::info("Comisión de curso calculada: Bs. {$comision} (Inscripción: {$inscripcion->id_inscripcion})");
        });
    }
}
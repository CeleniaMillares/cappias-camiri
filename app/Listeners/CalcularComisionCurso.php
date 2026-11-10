<?php
namespace App\Listeners;
use App\Events\InscripcionConfirmada;
use App\Models\Parametro;
use Illuminate\Support\Facades\Log;

class CalcularComisionCurso
{
    public function handle(InscripcionConfirmada $event): void
    {
        // Obtenemos el 20% desde la base de datos
        $porcentaje = Parametro::where('clave', 'comision_curso')->value('valor') ?? 20;
        
        $inscripcion = $event->inscripcion;
        $costoCurso = $inscripcion->horarioCurso->curso->costo;
        $comision = ($costoCurso * $porcentaje) / 100;

        // Registramos la magia en el log
        Log::info("Comisión de curso calculada: {$comision} Bs. (Curso: {$inscripcion->horarioCurso->curso->nombre})");
    }
}
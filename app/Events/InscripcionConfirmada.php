<?php
namespace App\Events;
use App\Models\Inscripcion;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InscripcionConfirmada
{
    use Dispatchable, SerializesModels;

    public $inscripcion;
    public function __construct(Inscripcion $inscripcion) {
        $this->inscripcion = $inscripcion;
    }
}
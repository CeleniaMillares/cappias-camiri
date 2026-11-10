<?php

namespace App\Observers;

use App\Models\Auditoria;
use Illuminate\Support\Facades\Auth;

class AuditoriaObserver
{
    private function registrar($accion, $modelo) {
        if (Auth::check()) {
            Auditoria::create([
                'id_usuario' => Auth::user()->id_usuario,
                'accion' => $accion,
                'entidad' => class_basename($modelo),
            ]);
        }
    }

    public function created($model) { $this->registrar('CREAR', $model); }
    public function updated($model) { $this->registrar('EDITAR', $model); }
    public function deleted($model) { $this->registrar('ELIMINAR', $model); }
}
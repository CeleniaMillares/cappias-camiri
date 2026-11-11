<?php

namespace App\Observers;

use App\Models\Auditoria;
use Illuminate\Support\Facades\Auth;

class AuditoriaObserver
{
    private function registrar($accion, $modelo) {
        Auditoria::create([
            'id_usuario' => Auth::id(),
            'nombre_usuario' => Auth::user()->nombre ?? 'Sistema',
            'accion' => $accion,
            'entidad' => class_basename($modelo),
        ]);
    }

    public function created($model) { $this->registrar('CREAR', $model); }
    public function updated($model) {
        $accion = $model->wasChanged('activo') && ! $model->getAttribute('activo')
            ? 'ELIMINAR'
            : 'EDITAR';

        $this->registrar($accion, $model);
    }
    public function deleted($model) { $this->registrar('ELIMINAR', $model); }
}
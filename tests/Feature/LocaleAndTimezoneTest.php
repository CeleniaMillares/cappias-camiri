<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class LocaleAndTimezoneTest extends TestCase
{
    public function test_app_uses_bolivia_timezone_and_spanish_messages_and_dates(): void
    {
        $this->assertSame('es', config('app.locale'));
        $this->assertSame('America/La_Paz', config('app.timezone'));

        $validator = Validator::make([], ['nombre' => ['required']]);

        $this->assertSame('El campo nombre es obligatorio.', $validator->errors()->first('nombre'));
        $this->assertSame('Los datos ingresados no son válidos.', __('The given data was invalid.'));
        $this->assertSame('No has iniciado sesión.', __('Unauthenticated.'));
        $this->assertSame('&laquo; Anterior', __('pagination.previous'));
        $this->assertSame('marzo', Carbon::parse('2026-03-01')->translatedFormat('F'));
    }
}

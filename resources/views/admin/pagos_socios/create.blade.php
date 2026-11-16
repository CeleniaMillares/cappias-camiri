@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="/admin/pagos-socios" class="mb-2 inline-flex items-center bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 px-4 py-2 rounded-lg font-semibold shadow-sm transition-colors duration-200 hover:bg-gray-300 dark:hover:bg-gray-600"><i class="fas fa-arrow-left mr-2" aria-hidden="true"></i> Volver a Pagos a Socios</a>
        <h1 class="text-2xl font-bold text-gray-800">Pagar a: {{ $socio->nombre }}</h1>
    </div>

    <div class="bg-white p-8 rounded-xl shadow-sm border border-gray-100 mb-6">
        <h2 class="text-lg font-bold border-b pb-3 mb-4 text-gray-800"><i class="fas fa-list-alt text-blue-500 mr-2"></i> Ingresos por ventas</h2>
        
        <div class="overflow-x-auto mb-6">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 text-gray-700">
                    <tr>
                        <th class="p-3 rounded-l-lg">Producto</th>
                        <th class="p-3">Cant.</th>
                        <th class="p-3 rounded-r-lg text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($ventas as $venta)
                        @foreach($venta->detalles as $detalle)
                            @if($detalle->producto->id_usuario_socio == $socio->id_usuario)
                            <tr>
                                <td class="p-3 font-medium text-gray-800">{{ $detalle->producto->nombre }}</td>
                                <td class="p-3">{{ $detalle->cantidad }}</td>
                                <td class="p-3 text-right font-semibold">Bs. {{ number_format($detalle->subtotal, 2) }}</td>
                            </tr>
                            @endif
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>

        <h2 class="text-lg font-bold border-b pb-3 mb-4 text-gray-800"><i class="fas fa-graduation-cap text-blue-500 mr-2"></i> Ingresos por cursos</h2>
        <div class="overflow-x-auto mb-6">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 text-gray-700">
                    <tr>
                        <th class="p-3">Alumno</th>
                        <th class="p-3">Curso</th>
                        <th class="p-3 text-right">Pago confirmado</th>
                        <th class="p-3 text-right">Comisión CAPPIAS</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($inscripciones as $inscripcion)
                        @php
                            $montoCurso = (float) $inscripcion->pagos->sum('monto');
                            $comisionInscripcion = $inscripcion->comision_calculada
                                ? (float) $inscripcion->comision_cappias
                                : round((float) $inscripcion->horarioCurso->curso->costo * $comisionCursoPorc / 100, 2);
                        @endphp
                        <tr>
                            <td class="p-3 font-medium text-gray-800">{{ $inscripcion->alumno->nombres }}</td>
                            <td class="p-3">{{ $inscripcion->horarioCurso->curso->nombre }}</td>
                            <td class="p-3 text-right">Bs. {{ number_format($montoCurso, 2) }}</td>
                            <td class="p-3 text-right text-red-600">Bs. {{ number_format($comisionInscripcion, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="p-4 text-center text-gray-500">Aún no hay pagos confirmados de cursos.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
            <div class="flex justify-between items-center text-lg mb-2">
                <span class="text-gray-600">Ventas — bruto:</span>
                <span class="font-bold text-gray-800">Bs. {{ number_format($bruto, 2) }}</span>
            </div>
            <div class="flex justify-between items-center text-red-600 mb-2">
                <span>Comisión CAPPIAS ({{ $comisionPorc }}%):</span>
                <span class="font-bold">- Bs. {{ number_format($comisionVentas, 2) }}</span>
            </div>
            <div class="flex justify-between items-center mb-2">
                <span class="text-gray-600">Cursos — bruto:</span>
                <span class="font-bold text-gray-800">Bs. {{ number_format($brutoCursos, 2) }}</span>
            </div>
            <div class="flex justify-between items-center text-red-600 mb-3 border-b pb-3">
                <span>Comisión CAPPIAS de cursos ({{ $comisionCursoPorc }}% configurada):</span>
                <span class="font-bold">- Bs. {{ number_format($comisionCursos, 2) }}</span>
            </div>
            <div class="flex justify-between items-center mb-2">
                <span class="text-gray-600">Total bruto:</span>
                <span class="font-bold text-gray-800">Bs. {{ number_format($brutoTotal, 2) }}</span>
            </div>
            <div class="flex justify-between items-center mb-2 text-red-600">
                <span>Comisión total CAPPIAS:</span>
                <span class="font-bold">- Bs. {{ number_format($comisionTotal, 2) }}</span>
            </div>
            <div class="flex justify-between items-center mb-2 border-b pb-3">
                <span class="text-gray-600">Neto generado:</span>
                <span class="font-bold text-gray-800">Bs. {{ number_format($netoGenerado, 2) }}</span>
            </div>
            <div class="flex justify-between items-center mb-3">
                <span class="text-gray-600">Pagado anteriormente:</span>
                <span class="font-bold text-gray-800">Bs. {{ number_format($montoPagado, 2) }}</span>
            </div>
            <div class="flex justify-between items-center text-xl font-extrabold text-blue-700">
                <span>Saldo Pendiente a Pagar:</span>
                <span>Bs. {{ number_format($saldoPendiente, 2) }}</span>
            </div>
            <p class="mt-3 text-xs text-gray-500">Los pagos parciales se distribuyen proporcionalmente entre los saldos pendientes de ventas y cursos. El comprobante registra la comisión aplicada a cada origen.</p>
        </div>
    </div>

    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <form method="POST" action="/admin/pagos-socios/crear/{{ $socio->id_usuario }}">
            @csrf
            
            <div class="mb-6">
                <label class="block mb-2 font-bold text-gray-700">Monto a Pagar (Bs):</label>
                <input type="number" step="0.01" name="monto_pagar" value="{{ old('monto_pagar', $saldoPendiente) }}" max="{{ $saldoPendiente }}" min="0.01" class="w-full border-gray-300 p-3 rounded-lg text-lg font-bold focus:ring-blue-500 focus:border-blue-500 border" required>
                <p class="text-xs text-gray-500 mt-2">Puedes modificar este monto si vas a realizar un <b>pago parcial</b> al socio.</p>
            </div>

            <div class="mb-6">
                <label for="metodo" class="block mb-2 font-bold text-gray-700">Método de pago:</label>
                <select id="metodo" name="metodo" class="w-full border-gray-300 p-3 rounded-lg border" required>
                    <option value="EFECTIVO" @selected(old('metodo', 'EFECTIVO') === 'EFECTIVO')>Efectivo</option>
                    <option value="QR" @selected(old('metodo') === 'QR')>QR</option>
                </select>
            </div>

            <button type="submit" class="bg-green-600 text-white px-6 py-4 rounded-lg hover:bg-green-700 transition w-full font-bold text-lg shadow-sm" onclick="return confirm('¿Confirmas la entrega del dinero?');">
                <i class="fas fa-hand-holding-usd mr-2"></i> Confirmar Pago
            </button>
        </form>
    </div>
</div>
@endsection
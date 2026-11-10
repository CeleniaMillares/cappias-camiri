@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="/admin/liquidaciones" class="text-blue-600 hover:underline text-sm mb-2 inline-block"><i class="fas fa-arrow-left mr-1"></i> Volver a la lista</a>
        <h1 class="text-2xl font-bold text-gray-800">Pagar a: {{ $socio->nombre }}</h1>
    </div>

    <div class="bg-white p-8 rounded-xl shadow-sm border border-gray-100 mb-6">
        <h2 class="text-lg font-bold border-b pb-3 mb-4 text-gray-800"><i class="fas fa-list-alt text-blue-500 mr-2"></i> Ventas Pendientes de Pago</h2>
        
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

        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
            <div class="flex justify-between items-center text-lg mb-2">
                <span class="text-gray-600">Total Bruto:</span>
                <span class="font-bold text-gray-800">Bs. {{ number_format($bruto, 2) }}</span>
            </div>
            <div class="flex justify-between items-center text-red-600 mb-3 border-b pb-3">
                <span>Comisión CAPPIAS ({{ $comisionPorc }}%):</span>
                <span class="font-bold">- Bs. {{ number_format($bruto * $comisionPorc / 100, 2) }}</span>
            </div>
            <div class="flex justify-between items-center text-xl font-extrabold text-blue-700">
                <span>Saldo Pendiente a Pagar:</span>
                <span>Bs. {{ number_format($saldoPendiente, 2) }}</span>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <form method="POST" action="/admin/liquidaciones/crear/{{ $socio->id_usuario }}">
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
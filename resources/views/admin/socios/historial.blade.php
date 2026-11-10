@extends('layouts.app')

@section('content')
<div class="mb-6">
    <a href="/admin/socios" class="text-blue-600 hover:underline text-sm mb-2 inline-block"><i class="fas fa-arrow-left mr-1"></i> Volver a la lista</a>
    <h1 class="text-2xl font-bold text-gray-800">Historial de Pagos: {{ $socio->usuario->nombre }}</h1>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-left text-sm text-gray-600">
        <thead class="bg-gray-50 text-gray-700 uppercase font-semibold">
            <tr>
                <th class="p-4">Fecha</th>
                <th class="p-4">Tipo</th>
                <th class="p-4">Periodo</th>
                <th class="p-4">Método</th>
                <th class="p-4">Estado</th>
                <th class="p-4 text-right">Monto</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($cuotas as $cuota)
            <tr>
                <td class="p-4">{{ \Carbon\Carbon::parse($cuota->fecha_pago)->format('d/m/Y') }}</td>
                <td class="p-4 font-bold">{{ $cuota->tipo }}</td>
                <td class="p-4">{{ $cuota->periodo ?? '-' }}</td>
                <td class="p-4">{{ $cuota->metodo }}</td>
                <td class="p-4">
                    @if($cuota->estado == 'CONFIRMADO')
                        <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs font-bold">Pagado</span>
                    @else
                        <span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded text-xs font-bold">Pendiente</span>
                    @endif
                </td>
                <td class="p-4 text-right font-bold text-green-600">Bs. {{ number_format($cuota->monto, 2) }}</td>
            </tr>
            @empty
            <tr><td colspan="6" class="p-8 text-center text-gray-500">No hay pagos registrados.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
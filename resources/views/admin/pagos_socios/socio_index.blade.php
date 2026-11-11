@extends('layouts.app')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Mis Pagos Recibidos</h1>
    <p class="text-gray-500 text-sm mt-1">Historial de ganancias abonadas por tus ventas.</p>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-left text-sm text-gray-600">
        <thead class="bg-gray-50 text-gray-700 uppercase font-semibold">
            <tr>
                <th class="p-4">Fecha del Pago</th>
                <th class="p-4">Monto Bruto</th>
                <th class="p-4">Comisión CAPPIAS</th>
                <th class="p-4">Monto Recibido</th>
                <th class="p-4">Método</th>
                <th class="p-4">Estado</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($pagos as $pago)
            <tr class="hover:bg-gray-50 transition">
                <td class="p-4">{{ $pago->created_at->format('d/m/Y H:i') }}</td>
                <td class="p-4">Bs. {{ number_format($pago->monto_bruto, 2) }}</td>
                <td class="p-4 text-red-600">- Bs. {{ number_format($pago->comision, 2) }}</td>
                <td class="p-4 font-bold text-green-600">Bs. {{ number_format($pago->monto_neto, 2) }}</td>
                <td class="p-4">{{ $pago->metodo ?? 'No especificado' }}</td>
                <td class="p-4">
                    <span class="{{ $pago->estado === 'ANULADA' ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }} px-2 py-1 rounded text-xs font-bold">
                        {{ $pago->estado === 'ANULADA' ? 'ANULADA' : 'PAGADA' }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="p-8 text-center text-gray-500">
                    <i class="fas fa-receipt text-4xl text-gray-300 mb-3 block"></i>
                    Aún no tienes pagos a socios registrados.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
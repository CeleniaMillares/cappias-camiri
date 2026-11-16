@extends('layouts.app')

@section('content')
<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Verificación de Pagos QR</h1>
        <p class="text-gray-500 text-sm mt-1">Confirma los pagos que hayan llegado a la cuenta bancaria.</p>
    </div>
    <a href="/admin/ventas" class="inline-flex items-center bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 px-4 py-2 rounded-lg font-semibold shadow-sm transition-colors duration-200 hover:bg-gray-300 dark:hover:bg-gray-600">
        <i class="fas fa-arrow-left mr-2"></i> Volver a Ventas
    </a>
</div>

@if(session('success')) 
    <div class="bg-green-100 border border-green-400 text-green-700 p-3 rounded-lg mb-6 shadow-sm"><i class="fas fa-check-circle mr-2"></i> {{ session('success') }}</div> 
@endif
@if(session('error')) 
    <div class="bg-red-100 border border-red-400 text-red-700 p-3 rounded-lg mb-6 shadow-sm"><i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}</div> 
@endif

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @forelse($ventasPendientes as $venta)
        @php
            $totalConfirmado = $venta->pagos->where('estado', 'CONFIRMADO')->sum('monto');
            $saldoPendiente = $venta->total - $totalConfirmado;
            $pagosPorVerificar = $venta->pagos->where('estado', 'PENDIENTE');
        @endphp

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden border-t-4 border-t-yellow-500">
            <div class="p-5">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <p class="font-bold text-lg text-gray-800">Venta # {{ substr($venta->id_venta, 0, 8) }}</p>
                        <p class="text-gray-500 text-sm">{{ $venta->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-gray-500 uppercase font-bold">Total</p>
                        <p class="text-lg font-bold text-blue-600">Bs. {{ number_format($venta->total, 2) }}</p>
                    </div>
                </div>

                <div class="bg-gray-50 p-3 rounded-lg mb-4 text-sm border border-gray-100">
                    <p class="font-semibold mb-1 text-gray-700">Productos:</p>
                    <ul class="list-disc list-inside text-gray-600 mb-2">
                        @foreach($venta->detalles as $det)
                            <li>{{ $det->producto->nombre }} (x{{ $det->cantidad }})</li>
                        @endforeach
                    </ul>
                    <div class="flex justify-between border-t border-gray-200 pt-2 mt-2 text-sm font-semibold">
                        <span class="text-green-600">Ya abonado: Bs. {{ number_format($totalConfirmado, 2) }}</span>
                        <span class="text-red-600">Falta: Bs. {{ number_format($saldoPendiente, 2) }}</span>
                    </div>
                </div>

                <!-- Botón de confirmar pago QR -->
                @if($pagosPorVerificar->count() > 0)
                    @foreach($pagosPorVerificar as $pago)
                        <div class="bg-yellow-50 border border-yellow-200 p-4 rounded-lg mb-2 text-center">
                            <p class="text-sm text-gray-700 mb-3 font-semibold">El cliente reporta un abono de:</p>
                            <p class="text-2xl font-bold text-gray-900 mb-4">Bs. {{ number_format($pago->monto, 2) }}</p>
                            
                            <div class="flex flex-col gap-2">
                                <form action="/admin/ventas/verificar-pago/{{ $pago->id_pago }}/aprobar" method="POST">
                                    @csrf
                                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg font-semibold shadow-sm transition-colors duration-200 hover:bg-green-700 dark:hover:bg-green-500 w-full">
                                        <i class="fas fa-check-circle mr-1"></i> Confirmar Pago QR
                                    </button>
                                </form>
                                <form action="/admin/ventas/verificar-pago/{{ $pago->id_pago }}/rechazar" method="POST">
                                    @csrf
                                    <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-lg font-semibold shadow-sm transition-colors duration-200 hover:bg-red-700 w-full text-sm">
                                        <i class="fas fa-times mr-1"></i> Rechazar Pago
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="text-center p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <i class="fas fa-hourglass-half text-gray-400 text-2xl mb-2"></i>
                        <p class="text-sm text-gray-500">Esperando que el cliente realice la transferencia...</p>
                    </div>
                @endif
            </div>
        </div>
    @empty
        <div class="col-span-3 bg-white p-12 text-center text-gray-500 rounded-xl shadow-sm border border-gray-100">
            <i class="fas fa-check-circle text-5xl text-green-300 mb-3 block"></i>
            <p class="text-lg font-semibold text-gray-700">Todo al día</p>
            <p>No hay ventas pendientes de verificación.</p>
        </div>
    @endforelse
</div>
@endsection
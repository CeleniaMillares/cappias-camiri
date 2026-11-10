@extends('layouts.app')

@section('content')
<h1 class="text-2xl font-bold mb-6">Panel del Socio</h1>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="bg-white p-6 rounded shadow">
        <h3 class="font-bold text-lg mb-4 border-b pb-2">Mis Productos</h3>
        <ul class="space-y-2">
            @forelse($productos as $prod)
            <li class="flex justify-between items-center bg-gray-50 p-2 rounded">
                <span>{{ $prod->nombre }}</span>
                <span class="text-sm font-bold bg-blue-100 text-blue-700 px-2 py-1 rounded">Stock: {{ $prod->stock_actual }}</span>
            </li>
            @empty
            <li class="text-gray-500 text-sm">No tienes productos registrados.</li>
            @endforelse
        </ul>
    </div>

    <div class="bg-white p-6 rounded shadow">
        <h3 class="font-bold text-lg mb-4 border-b pb-2">Mis Ventas Recientes</h3>
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-50">
                <tr><th class="p-2">Producto</th><th class="p-2">Cant.</th><th class="p-2">Total</th></tr>
            </thead>
            <tbody>
                @forelse($ventas as $v)
                <tr class="border-b">
                    <td class="p-2">{{ $v->producto->nombre }}</td>
                    <td class="p-2">{{ $v->cantidad }}</td>
                    <td class="p-2 font-bold text-green-600">Bs. {{ number_format($v->subtotal, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="3" class="p-2 text-gray-500 text-center">No hay ventas registradas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
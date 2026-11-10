@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Parámetros del Sistema</h1>
        <p class="text-gray-500 text-sm mt-1">Configura los valores globales y la imagen del QR de pagos.</p>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6 shadow-sm">
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <!-- QR OFICIAL -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <h2 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2"><i class="fas fa-qrcode text-blue-500 mr-2"></i> QR Oficial de Pagos</h2>
            
            @php
                $qrPath = \App\Models\Parametro::where('clave', 'qr_image_url')->value('valor');
            @endphp

            <div class="text-center mb-4">
                @if($qrPath)
                    <img src="{{ asset('storage/' . $qrPath) }}" class="mx-auto w-48 h-48 object-contain border p-2 rounded bg-gray-50 shadow-sm" alt="QR CAPPIAS">
                @else
                    <div class="w-48 h-48 mx-auto bg-gray-100 border-2 border-dashed rounded flex items-center justify-center text-gray-400">
                        <span class="text-sm">Sin imagen subida</span>
                    </div>
                @endif
            </div>

            <form method="POST" action="/admin/parametros/qr" enctype="multipart/form-data">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Subir nueva imagen de QR:</label>
                    <input type="file" name="qr_image" accept="image/*" class="w-full border p-2 rounded bg-gray-50 text-sm" required>
                </div>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 w-full font-bold text-sm">
                    <i class="fas fa-upload mr-2"></i> Actualizar Imagen Oficial
                </button>
            </form>
        </div>

        <!-- PARÁMETROS NUMÉRICOS -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <h2 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2"><i class="fas fa-cogs text-gray-500 mr-2"></i> Valores Globales</h2>
            <form method="POST" action="/admin/parametros">
                @csrf
                @foreach($parametros as $param)
                    @if($param->clave !== 'qr_image_url') <!-- Ocultamos el QR de la lista de texto -->
                    <div class="mb-4">
                        <label class="block mb-1 font-semibold text-sm text-gray-700 capitalize">{{ str_replace('_', ' ', $param->clave) }}:</label>
                        <input type="text" name="parametros[{{ $param->id_parametro }}]" value="{{ $param->valor }}" class="w-full border p-2 rounded text-sm">
                    </div>
                    @endif
                @endforeach
                <button type="submit" class="bg-slate-800 text-white px-4 py-2 rounded-lg hover:bg-slate-900 w-full font-bold text-sm mt-2">
                    <i class="fas fa-save mr-2"></i> Guardar Valores
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
@extends('layouts.app')

@section('content')
<div class="flex flex-wrap items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Respaldos del sistema</h1>
        <p class="text-sm text-gray-500 mt-1">Gestiona las copias de seguridad de CAPPIAS.</p>
    </div>
    <form method="POST" action="/admin/backups" onsubmit="return confirm('¿Generar un nuevo respaldo ahora?');">
        @csrf
        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg font-semibold shadow-sm transition-colors duration-200 hover:bg-green-700 dark:hover:bg-green-500">
            <i class="fas fa-database mr-2"></i> Generar Nuevo Backup
        </button>
    </form>
</div>

@if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
        {{ session('success') }}
    </div>
@endif

@if($errors->has('backup'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
        {{ $errors->first('backup') }}
    </div>
@endif

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-left text-sm text-gray-600">
        <thead class="bg-gray-50 text-gray-700 uppercase font-semibold">
            <tr>
                <th class="p-4">Nombre del archivo</th>
                <th class="p-4">Tamaño</th>
                <th class="p-4">Fecha de creación</th>
                <th class="p-4">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($files as $file)
                <tr>
                    <td class="p-4 font-medium text-gray-800">{{ $file['filename'] }}</td>
                    <td class="p-4">{{ number_format($file['size'] / 1048576, 2) }} MB</td>
                    <td class="p-4">{{ \Carbon\Carbon::createFromTimestamp($file['created_at'])->format('d/m/Y H:i') }}</td>
                    <td class="p-4">
                        <div class="flex flex-wrap gap-3">
                            <a href="/admin/backups/download/{{ rawurlencode($file['filename']) }}" class="inline-flex items-center gap-1 rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition-colors duration-200 hover:bg-blue-700 dark:hover:bg-blue-500">
                                <i class="fas fa-download mr-1"></i> Descargar
                            </a>
                            <form method="POST" action="/admin/backups/{{ rawurlencode($file['filename']) }}" onsubmit="return confirm('¿Eliminar este respaldo? Esta acción no se puede deshacer.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center gap-1 rounded-lg bg-red-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition-colors duration-200 hover:bg-red-700">
                                    <i class="fas fa-trash mr-1"></i> Eliminar
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="p-8 text-center text-gray-500">
                        <i class="fas fa-database text-4xl text-gray-300 mb-3 block"></i>
                        No hay respaldos disponibles.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

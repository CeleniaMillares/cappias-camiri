@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Mi Perfil de Socio</h1>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6 shadow-sm">
            {{ session('success') }}
        </div>
    @endif
    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6 shadow-sm">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Datos Personales -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <h2 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2"><i class="fas fa-user-edit text-blue-500 mr-2"></i> Datos Personales</h2>
            <form method="POST" action="/admin/mi-perfil-socio">
                @csrf @method('PUT')
                
                <div class="mb-3">
                    <label class="block text-sm font-semibold mb-1">Nombre:</label>
                    <input type="text" name="nombre" value="{{ $user->nombre }}" class="w-full border p-2 rounded-lg" required>
                </div>
                <div class="mb-3">
                    <label class="block text-sm font-semibold mb-1">Correo:</label>
                    <input type="email" name="correo" value="{{ $user->correo }}" class="w-full border p-2 rounded-lg" required>
                </div>
                <div class="mb-3">
                    <label class="block text-sm font-semibold mb-1">Teléfono:</label>
                    <input type="text" name="telefono" value="{{ $socio->telefono }}" class="w-full border p-2 rounded-lg" required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-semibold mb-1">Dirección:</label>
                    <input type="text" name="direccion" value="{{ $socio->direccion }}" class="w-full border p-2 rounded-lg">
                </div>

                <!-- Campos no editables -->
                <div class="bg-gray-50 p-3 rounded-lg border border-gray-200 mb-4">
                    <p class="text-sm text-gray-600"><strong>CI:</strong> {{ $socio->ci }}</p>
                    <p class="text-sm text-gray-600"><strong>Fecha de Inscripción:</strong> {{ \Carbon\Carbon::parse($socio->fecha_inscripcion)->format('d/m/Y') }} (No editable)</p>
                </div>

                <button type="submit" class="bg-blue-600 text-white w-full py-2 rounded-lg font-bold hover:bg-blue-700">Actualizar Datos</button>
            </form>
        </div>

        <!-- Cambiar Contraseña -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <h2 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2"><i class="fas fa-lock text-yellow-500 mr-2"></i> Cambiar Contraseña</h2>
            <form method="POST" action="/admin/mi-perfil-socio/password">
                @csrf @method('PUT')
                
                <div class="mb-3">
                    <label class="block text-sm font-semibold mb-1">Contraseña Actual:</label>
                    <input type="password" name="contrasena_actual" class="w-full border p-2 rounded-lg" required>
                </div>
                <div class="mb-3">
                    <label class="block text-sm font-semibold mb-1">Nueva Contraseña:</label>
                    <input type="password" name="contrasena_nueva" class="w-full border p-2 rounded-lg" required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-semibold mb-1">Confirmar Nueva Contraseña:</label>
                    <input type="password" name="contrasena_nueva_confirmation" class="w-full border p-2 rounded-lg" required>
                </div>
                <button type="submit" class="bg-yellow-600 text-white w-full py-2 rounded-lg font-bold hover:bg-yellow-700">Cambiar Contraseña</button>
            </form>
        </div>
    </div>

    <!-- Historial de Pagos -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 mt-6">
        <h2 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2"><i class="fas fa-history text-green-500 mr-2"></i> Historial de Pagos</h2>
        <table class="w-full text-left text-sm text-gray-600">
            <thead class="bg-gray-50 text-gray-700 uppercase font-semibold">
                <tr>
                    <th class="p-3">Fecha</th>
                    <th class="p-3">Tipo</th>
                    <th class="p-3">Periodo</th>
                    <th class="p-3 text-right">Monto</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($pagos as $pago)
                <tr>
                    <td class="p-3">{{ \Carbon\Carbon::parse($pago->fecha_pago)->format('d/m/Y') }}</td>
                    <td class="p-3 font-bold">{{ $pago->tipo }}</td>
                    <td class="p-3">{{ $pago->periodo ?? '-' }}</td>
                    <td class="p-3 text-right font-bold text-green-600">Bs. {{ number_format($pago->monto, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="p-6 text-center text-gray-500">No hay pagos registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
        <p class="text-xs text-gray-400 mt-3"><i class="fas fa-info-circle mr-1"></i> Los pagos son registrados únicamente por la Directiva.</p>
    </div>
</div>
@endsection
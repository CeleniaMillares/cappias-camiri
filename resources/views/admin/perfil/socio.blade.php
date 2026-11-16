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
                    <input type="text" name="telefono" value="{{ $socio->telefono }}" class="w-full border p-2 rounded-lg" required inputmode="numeric" minlength="7" maxlength="8" pattern="[0-9]{7,8}">
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

                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg font-semibold shadow-sm transition-colors duration-200 hover:bg-green-700 dark:hover:bg-green-500 w-full"><i class="fas fa-save mr-2" aria-hidden="true"></i> Actualizar Datos</button>
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
                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg font-semibold shadow-sm transition-colors duration-200 hover:bg-green-700 dark:hover:bg-green-500 w-full"><i class="fas fa-key mr-2" aria-hidden="true"></i> Cambiar Contraseña</button>
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
                    <th class="p-3">Estado</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($pagos as $pago)
                <tr>
                    <td class="p-3">{{ \Carbon\Carbon::parse($pago->fecha_pago)->format('d/m/Y') }}</td>
                    <td class="p-3 font-bold">{{ $pago->tipo }}</td>
                    <td class="p-3">{{ $pago->periodo ?? '-' }}</td>
                    <td class="p-3 text-right font-bold text-green-600">Bs. {{ number_format($pago->monto, 2) }}</td>
                    <td class="p-3">
                        @if($pago->estado === 'CONFIRMADO')
                            <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs font-bold">Pagado</span>
                        @elseif($pago->estado === 'PENDIENTE')
                            <span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded text-xs font-bold">Pendiente</span>
                        @else
                            <span class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs font-bold">{{ $pago->estado }}</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="p-6 text-center text-gray-500">No hay pagos registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
        <p class="text-xs text-gray-400 mt-3"><i class="fas fa-info-circle mr-1"></i> Los pagos son registrados únicamente por la Directiva.</p>
    </div>

    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 mt-6">
        <h2 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2"><i class="fas fa-store text-blue-500 mr-2"></i> Ferias</h2>
        <div class="space-y-4">
            @forelse($ferias as $feria)
                @php($participacion = $feria->socios->first())
                <div class="flex flex-col gap-3 rounded-lg border border-gray-200 p-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="font-semibold text-gray-800">{{ $feria->nombre }}</h3>
                        <p class="text-sm text-gray-600">{{ \Carbon\Carbon::parse($feria->fecha)->format('d/m/Y') }} · {{ $feria->lugar }}</p>
                    </div>
                    @if($participacion)
                        <span class="inline-flex w-fit rounded-full px-3 py-1 text-sm font-semibold {{ $participacion->pivot->estado === 'CONFIRMADO' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                            {{ $participacion->pivot->estado }}
                        </span>
                    @else
                        <form method="POST" action="/perfil/socio/feria/{{ $feria->id_feria }}">
                            @csrf
                            <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-blue-700">
                                Solicitar Participación
                            </button>
                        </form>
                    @endif
                </div>
            @empty
                <p class="text-sm text-gray-500">No hay ferias futuras disponibles.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
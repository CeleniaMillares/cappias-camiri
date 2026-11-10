<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear contraseña</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gray-100 flex items-center justify-center p-4">
    <main class="w-full max-w-md rounded-xl bg-white p-8 shadow-lg">
        <h1 class="mb-2 text-2xl font-bold text-gray-800">Crear contraseña</h1>
        <p class="mb-6 text-sm text-gray-600">Define una contraseña para ingresar a tu cuenta CAPPIAS.</p>

        @if($errors->any())
            <div class="mb-4 rounded-lg bg-red-100 p-3 text-sm text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <div>
                <label for="correo" class="mb-1 block text-sm font-semibold">Correo electrónico</label>
                <input id="correo" type="email" name="correo" value="{{ old('correo', $correo) }}" required
                    class="w-full rounded-lg border p-2">
            </div>
            <div>
                <label for="password" class="mb-1 block text-sm font-semibold">Nueva contraseña</label>
                <input id="password" type="password" name="password" required minlength="8"
                    class="w-full rounded-lg border p-2">
            </div>
            <div>
                <label for="password_confirmation" class="mb-1 block text-sm font-semibold">Confirmar contraseña</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required minlength="8"
                    class="w-full rounded-lg border p-2">
            </div>
            <button type="submit" class="w-full rounded-lg bg-blue-600 py-3 font-bold text-white hover:bg-blue-700">
                Guardar contraseña
            </button>
        </form>
    </main>
</body>
</html>

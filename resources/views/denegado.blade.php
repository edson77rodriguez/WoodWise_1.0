<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Denegado</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        .bg-gradient-denied {
            background: linear-gradient(135deg, #fce4ec 0%, #f8bbd0 50%, #f48fb1 100%);
        }
        .shadow-soft {
            box-shadow: 0 10px 30px -10px rgba(244, 143, 177, 0.3);
        }
    </style>
</head>
<body class="bg-gradient-denied min-h-screen flex items-center justify-center p-4">
    <div class="animate__animated animate__fadeInUp bg-white rounded-xl shadow-soft p-8 max-w-md w-full text-center">
        <!-- Icono (usando Heroicons) -->
        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
        </div>

        <!-- Título y mensaje -->
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Acceso Denegado</h1>
        <p class="text-gray-600 mb-6">
            Tu rol actual no tiene permisos para acceder a esta sección. 
            Por favor, contacta al administrador si necesitas acceso.
        </p>

        <!-- Botón de acción -->
        <a href="{{ url('/dashboard1') }}" 
           class="inline-block px-6 py-2 bg-pink-500 hover:bg-pink-600 text-white rounded-lg transition duration-300 ease-in-out transform hover:scale-105">
            Volver al Inicio
        </a>

        <!-- Efecto decorativo -->
        <div class="mt-6 opacity-70">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-pink-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
        </div>
    </div>
</body>
</html>
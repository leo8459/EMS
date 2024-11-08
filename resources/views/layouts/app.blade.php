<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>
        @livewireStyles
        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            @include('layouts.navigation')
            @livewireScripts
            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white dark:bg-gray-800 shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
 <!-- SweetAlert2 con eventos de Livewire -->
 <script>
    document.addEventListener('livewire:load', function () {
        // Escuchar el evento de éxito
        Livewire.on('alerta-exito', function (datos) {
            Swal.fire({
                icon: 'success',
                title: 'Éxito',
                text: datos.mensaje,
                timer: 2000,
                showConfirmButton: false
            });
        });

        // Escuchar el evento de error
        Livewire.on('alerta-error', function (datos) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: datos.mensaje,
                timer: 2000,
                showConfirmButton: false
            });
        });

        // Escuchar el evento de confirmación de eliminación
        Livewire.on('confirmar-eliminacion', function (datos) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "Esta acción no se puede deshacer.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminar'
            }).then((result) => {
                if (result.isConfirmed) {
                    Livewire.emit('confirmarEliminacion', datos.id);
                }
            });
        });
    });
</script>
    </body>
    
</html>

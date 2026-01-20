<div class="container-fluid">
    <!-- Estilos Responsivos Adicionales -->
    <style>
        /* Para pantallas de tamaño mediano (tablets) y abajo (max-width: 768px) */
        @media (max-width: 768px) {
            /* Que las columnas se comporten como filas (100% ancho) */
            .col-md-3,
            .col-md-6 {
                flex: 0 0 100%;
                max-width: 100%;
            }
            
            /* Ajustar el contenedor del canvas en dispositivos medianos */
            #canvas {
                width: 100% !important;
                height: 200px !important;
            }
        }

        /* Para pantallas muy pequeñas (teléfonos en vertical, max-width: 576px) */
        @media (max-width: 576px) {
            /* Ajustar fuente o espaciados si se desea */
            label {
                font-size: 0.9rem;
            }

            /* Reducir un poco la altura del canvas en móviles muy pequeños */
            #canvas {
                height: 180px !important;
            }

            /* Mensaje de 'poner en horizontal' visible en pantallas pequeñas 
               (solo si así lo deseas); actualmente se muestra/oculta con JS (d-none) */
        }

        /* Ajustar el canvas de firma a un ancho fluido por defecto */
        #canvas {
            max-width: 100%;
            height: auto;
        }
    </style>

    <!-- Sección de encabezado y breadcrumbs -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Entregar Admision</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Inicio</a></li>
                        <li class="breadcrumb-item active">Entregar Admision</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Contenido principal -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <!-- Tarjeta principal -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Formulario de Entrega</h3>
                        </div>
                        <div class="card-body">
                            <!-- Mensajes de sesión -->
                            @if (session()->has('message'))
                                <div class="alert alert-success">
                                    {{ session('message') }}
                                </div>
                            @endif

                            <!-- Formulario Livewire -->
                            <form wire:submit.prevent="guardarAdmision">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label for="codigo">Código</label>
                                        <input type="text" id="codigo"
                                               class="form-control"
                                               value="{{ $admision->codigo }}"
                                               disabled>
                                    </div>

                                    <div class="col-md-3">
                                        <label for="destino">Destino</label>
                                        <input type="text" id="destino"
                                               class="form-control"
                                               value="{{ $admision->destino }}"
                                               disabled>
                                    </div>

                                    @hasrole('CARTERO')
                                        <div class="col-md-3">
                                            <label for="direccion">Dirección</label>
                                            <input type="text" id="direccion"
                                                   class="form-control"
                                                   value="{{ $admision->direccion }}"
                                                   disabled>
                                        </div>
                                    @endhasrole

                                    @hasrole('VENTANILLA')
                                        <div class="col-md-3">
                                            <label for="direccion">Entrega</label>
                                            <input type="text" id="direccion"
                                                   class="form-control"
                                                   value="VENTANILLA"
                                                   disabled>
                                        </div>
                                    @endhasrole

                                    <div class="col-md-3">
                                        <label for="photo">Seleccionar Foto</label>
                                        <input type="file"
                                               id="photo"
                                               wire:model="photo"
                                               accept="image/*"
                                               capture="environment"
                                               class="form-control">
                                        @error('photo')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Segunda fila -->
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <label for="recepcionado">Recepcionado Por</label>
                                        <input type="text"
                                               id="recepcionado"
                                               wire:model="recepcionado"
                                               class="form-control">
                                        @error('recepcionado')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="observacion_entrega">Observación de Entrega</label>
                                        <textarea id="observacion_entrega"
                                                  wire:model="observacion_entrega"
                                                  class="form-control"></textarea>
                                        @error('observacion_entrega')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Tercera fila: firma -->
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <input type="hidden"
                                               id="inputbase64"
                                               wire:model="firma">
                                    </div>
                                </div>

                                <!-- Mensaje orientacion del dispositivo -->
                                <div id="message"
                                     class="alert alert-warning text-center d-none">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="fas fa-4x fa-exclamation-triangle text-warning"></i>
                                        <span class="mt-2">
                                            Por favor ponga en modo horizontal la pantalla de su
                                            teléfono si desea firmar
                                        </span>
                                    </div>
                                </div>

                                <!-- Contenedor de la firma -->
                                <div class="container-lg" id="div1">
                                    <div class="position-relative">
                                        <!-- Canvas para la firma -->
                                        <div class="text-center">
                                            <canvas id="canvas"
                                                    class="border border-secondary rounded bg-white"
                                                    width="600"
                                                    height="250">
                                            </canvas>
                                        </div>
                                    </div>

                                    <!-- Mensaje dinámico de éxito -->
                                    <div id="successMessage"
                                         class="alert alert-success text-center mt-3 d-none">
                                        <i class="fas fa-check-circle"></i> Firma guardada exitosamente.
                                    </div>

                                    <!-- Botones firma -->
                                    <div class="mb-3 text-center">
                                        <button type="button" id="guardar"
                                                class="btn btn-primary me-2">
                                            <i class="fas fa-save"></i> Guardar Firma
                                        </button>
                                        <button type="button" id="limpiar"
                                                class="btn btn-secondary">
                                            <i class="fas fa-trash"></i> Limpiar
                                        </button>
                                    </div>
                                </div>

                                <!-- Botones finales -->
                                <div class="row mt-3">
                                    <div class="col-12 text-right">
                                        <button type="submit" id="submitButton"
                                                class="btn btn-primary">
                                            Entregar
                                        </button>
                                        <button type="button"
                                                wire:click="noEntregado"
                                                class="btn btn-warning">
                                            No Entregado
                                        </button>
                                        <button type="button"
                                                wire:click="return"
                                                class="btn btn-danger">
                                            Return
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Footer de la tarjeta -->
                        <div class="card-footer">
                            <a href="{{ url()->previous() }}"
                               class="btn btn-secondary">
                                Regresar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Librería de Signature Pad -->
<script src="https://cdn.jsdelivr.net/npm/signature_pad@5.0.0/dist/signature_pad.umd.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const canvas = document.getElementById('canvas');
        const signaturePad = new SignaturePad(canvas);
        const saveButton = document.getElementById('guardar');
        const clearButton = document.getElementById('limpiar');
        const inputBase64 = document.getElementById('inputbase64');
        const successMessage = document.getElementById('successMessage');

        // Limpiar la firma
        clearButton.addEventListener('click', function() {
            signaturePad.clear();
            inputBase64.value = ""; // Limpiar el campo de firma
            inputBase64.dispatchEvent(new Event('input')); // Sincronizar con Livewire
        });

        // Guardar la firma en Base64
        saveButton.addEventListener('click', function() {
            if (signaturePad.isEmpty()) {
                alert('Por favor, haga una firma antes de guardar.');
                return;
            }

            // Convertir la firma a Base64 y asignarla al input
            const base64Signature = signaturePad.toDataURL();
            inputBase64.value = base64Signature;

            // Forzar la sincronización del valor con Livewire
            inputBase64.dispatchEvent(new Event('input')); // <- Esto asegura que Livewire lo detecte

            // Mostrar mensaje de éxito y ocultarlo después de 3 segundos
            successMessage.classList.remove('d-none');
            setTimeout(() => {
                successMessage.classList.add('d-none');
            }, 3000);
        });
    });
</script>

<div class="container-fluid">
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

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Formulario de Entrega</h3>
                        </div>
                        <div class="card-body">
                            @if (session()->has('message'))
                                <div class="alert alert-success">
                                    {{ session('message') }}
                                </div>
                            @endif
                            <form wire:submit.prevent="guardarAdmision">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label for="codigo">Código</label>
                                        <input type="text" id="codigo" class="form-control" value="{{ $admision->codigo }}" disabled>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="destino">Destino</label>
                                        <input type="text" id="destino" class="form-control" value="{{ $admision->destino }}" disabled>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="direccion">Dirección</label>
                                        <input type="text" id="direccion" class="form-control" value="{{ $admision->direccion }}" disabled>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="photo">Seleccionar Foto</label>
                                        <input type="file" id="photo" wire:model="photo" class="form-control">
                                        @error('photo') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <label for="recepcionado">Recepcionado Por</label>
                                        <input type="text" id="recepcionado" wire:model="recepcionado" class="form-control">
                                        @error('recepcionado') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="observacion_entrega">Observación de Entrega</label>
                                        <textarea id="observacion_entrega" wire:model="observacion_entrega" class="form-control"></textarea>
                                        @error('observacion_entrega') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <input type="hidden" id="inputbase64" wire:model="firma">
                                    </div>
                                </div>
                                <div id="message" class="alert alert-warning text-center d-none">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="fas fa-4x fa-exclamation-triangle text-warning"></i>
                                        <span class="mt-2">Por favor ponga en modo horizontal la pantalla de su teléfono si desea
                                            firmar</span>
                                    </div>
                                </div>

                                <div class="container-lg" id="div1">
                                    <div class="position-relative">
                                        <!-- Canvas para la firma -->
                                        <div class="text-center">
                                            <canvas id="canvas" class="border border-secondary rounded bg-white" width="600" height="250"></canvas>
                                        </div>
                                    </div>
                                
                                    <!-- Mensaje dinámico de éxito -->
                                    <div id="successMessage" class="alert alert-success text-center mt-3 d-none">
                                        <i class="fas fa-check-circle"></i> Firma guardada exitosamente.
                                    </div>
                                
                                    <!-- Botones -->
                                    <div class="mb-3 text-center">
                                        <button type="button" id="guardar" class="btn btn-primary me-2"><i class="fas fa-save"></i> Guardar Firma</button>
                                        <button type="button" id="limpiar" class="btn btn-secondary"><i class="fas fa-trash"></i> Limpiar</button>
                                    </div>
                                </div>
                                
                                <div class="row mt-3">
                                    <div class="col-12 text-right">
                                        <button type="submit" id="submitButton" class="btn btn-primary">Guardar</button>
                                        <button type="button" wire:click="noEntregado" class="btn btn-warning">No Entregado</button>
                                        <button type="button" wire:click="return" class="btn btn-danger">Return</button>
                                    </div>
                                </div>
                                
                            </form>
                        </div>
                        <div class="card-footer">
                            <a href="{{ url()->previous() }}" class="btn btn-secondary">Regresar</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/signature_pad@5.0.0/dist/signature_pad.umd.min.js"></script>
<script>
   document.addEventListener('DOMContentLoaded', function () {
    const canvas = document.getElementById('canvas');
    const signaturePad = new SignaturePad(canvas);
    const saveButton = document.getElementById('guardar');
    const clearButton = document.getElementById('limpiar');
    const inputBase64 = document.getElementById('inputbase64');
    const successMessage = document.getElementById('successMessage');

    // Limpiar la firma
    clearButton.addEventListener('click', function () {
        signaturePad.clear();
        inputBase64.value = ""; // Limpiar el campo de firma
        inputBase64.dispatchEvent(new Event('input')); // Sincronizar con Livewire
    });

    // Guardar la firma en Base64
    saveButton.addEventListener('click', function () {
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


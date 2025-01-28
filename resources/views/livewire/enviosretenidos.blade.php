<div class="container-fluid">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Expedición <i class="el el-minus-sign"></i></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Inicio</a></li>
                        <li class="breadcrumb-item active">Registros</li>
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
                            <div class="d-flex align-items-center">
                                <input type="text" wire:model="searchTerm" placeholder="Buscar..."
                                    class="form-control" style="margin-right: 10px;" wire:keydown.enter="$refresh">
                                <button type="button" class="btn btn-primary" wire:click="$refresh">Buscar</button>
                            </div>
                        </div>
                        @if (session()->has('message'))
                            <div class="alert alert-success">
                                {{ session('message') }}
                            </div>
                        @endif
                        @if (session()->has('error'))
                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                        @endif
                        <div class="card-body">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" wire:model="selectAll" /></th>
                                        <th>#</th>
                                        <th>Origen</th>
                                        <th>Servicio</th>
                                        <th>Peso</th>
                                        <th>Ciudad</th>
                                        <th>Código</th>
                                        <th>Manifiesto</th>
                                        <th>Fecha</th>
                                        <th>Observación</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($admisiones as $admisione)
                                        <tr>
                                            <td>
                                                <input type="checkbox" wire:model="selectedAdmisiones"
                                                    value="{{ $admisione->id }}" />
                                            </td>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $admisione->origen }}</td>
                                            <td>{{ $admisione->servicio }}</td>
                                            <td>{{ $admisione->peso_regional ?: ($admisione->peso_ems ?: $admisione->peso) }}</td>
                                            <td>{{ $admisione->reencaminamiento ?? $admisione->ciudad }}</td>
                                            <td>{{ $admisione->codigo }}</td>
                                            <td>{{ $admisione->manifiesto }}</td>
                                            <td>{{ $admisione->fecha }}</td>
                                            <td>{{ $admisione->observacion_entrega ?: $admisione->observacion }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer">
                            {{ $admisiones->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal -->
    <div class="modal fade @if($showModal) show d-block @endif" style="background-color: rgba(0, 0, 0, 0.5);">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Retener Envío</h5>
                    <button type="button" class="close" wire:click="$set('showModal', false)">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Código(s):</label>
                        <input type="text" class="form-control" value="{{ $codigoRetenido }}" disabled>
                    </div>
                    <div class="form-group">
                        <label>Observación:</label>
                        <input type="text" class="form-control" wire:model="observacionRetencion" placeholder="Añadir observación">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="$set('showModal', false)">Cerrar</button>
                    <button type="button" class="btn btn-primary" wire:click="retenerEnvios">Guardar</button>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    document.addEventListener('livewire:load', function () {
        window.addEventListener('reloadPage', function () {
            location.reload(); // Recarga la página completamente
        });
    });
</script>

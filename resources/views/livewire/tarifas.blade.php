<div class="container-fluid">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Admisiones Generadas</h1>
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
                                <div class="float-left d-flex align-items-center">
                                    <input type="text" wire:model="searchTerm" placeholder="Buscar..."
                                        class="form-control" style="margin-right: 10px;" wire:keydown.enter="$refresh">

                                    <button type="button" class="btn btn-primary" wire:click="$refresh">Buscar</button>
                                </div>
                                <button type="button" class="btn btn-success ml-auto" wire:click="create">
                                    Añadir Nueva Tarifa
                                </button>
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
                                        <th>Peso Min</th>
                                        <th>Peso Max</th>
                                        <th>EMS Local Cobertura 1</th>
                                        <th>EMS Local Cobertura 2</th>
                                        <th>EMS Local Cobertura 3</th>
                                        <th>EMS Local Cobertura 4</th>
                                        <th>EMS Nacional</th>
                                        <th>Destino 1</th>
                                        <th>Destino 2</th>
                                        <th>Destino 3</th>
                                        <th>Servicio</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($admisiones as $admisione)
                                        <tr>
                                            <td>{{ $admisione->peso_min }}</td>
                                            <td>{{ $admisione->peso_max }}</td>
                                            <td>{{ $admisione->ems_local_cobertura_1 }}</td>
                                            <td>{{ $admisione->ems_local_cobertura_2 }}</td>
                                            <td>{{ $admisione->ems_local_cobertura_3 }}</td>
                                            <td>{{ $admisione->ems_local_cobertura_4 }}</td>
                                            <td>{{ $admisione->ems_nacional }}</td>
                                            <td>{{ $admisione->destino_1 }}</td>
                                            <td>{{ $admisione->destino_2 }}</td>
                                            <td>{{ $admisione->destino_3 }}</td>
                                            <td>{{ $admisione->servicio }}</td>
                                            <td>
                                                <button type="button" class="btn btn-primary btn-sm"
                                                    wire:click="edit({{ $admisione->id }})">Editar</button>
                                                <button type="button" class="btn btn-danger btn-sm"
                                                    wire:click="delete({{ $admisione->id }})">Eliminar</button>
                                            </td>
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
    <div class="modal fade @if($showModal) show @endif" tabindex="-1" style="@if($showModal) display: block; @else display: none; @endif">
        <div class="modal-dialog modal-lg"> <!-- Cambiado a modal-lg para más espacio -->
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $isEdit ? 'Editar Tarifa' : 'Añadir Nueva Tarifa' }}</h5>
                    <button type="button" class="btn-close" aria-label="Close" wire:click="closeModal"></button>
                </div>
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;"> <!-- Scroll añadido aquí -->
                    <form>
                        <div class="mb-3">
                            <label for="servicio" class="form-label">Servicio</label>
                            <input type="text" class="form-control" id="servicio" wire:model="servicio">
                        </div>
                        <div class="mb-3">
                            <label for="peso_min" class="form-label">Peso Min</label>
                            <input type="number" class="form-control" id="peso_min" wire:model="peso_min" step="0.001">
                        </div>
                        <div class="mb-3">
                            <label for="peso_max" class="form-label">Peso Max</label>
                            <input type="number" class="form-control" id="peso_max" wire:model="peso_max" step="0.001">
                        </div>
                        <div class="mb-3">
                            <label for="ems_local_cobertura_1" class="form-label">EMS Local Cobertura 1</label>
                            <input type="number" class="form-control" id="ems_local_cobertura_1" wire:model="ems_local_cobertura_1">
                        </div>
                        <div class="mb-3">
                            <label for="ems_local_cobertura_2" class="form-label">EMS Local Cobertura 2</label>
                            <input type="number" class="form-control" id="ems_local_cobertura_2" wire:model="ems_local_cobertura_2">
                        </div>
                        <div class="mb-3">
                            <label for="ems_local_cobertura_3" class="form-label">EMS Local Cobertura 3</label>
                            <input type="number" class="form-control" id="ems_local_cobertura_3" wire:model="ems_local_cobertura_3">
                        </div>
                        <div class="mb-3">
                            <label for="ems_local_cobertura_4" class="form-label">EMS Local Cobertura 4</label>
                            <input type="number" class="form-control" id="ems_local_cobertura_4" wire:model="ems_local_cobertura_4">
                        </div>
                        <div class="mb-3">
                            <label for="ems_nacional" class="form-label">EMS Nacional</label>
                            <input type="number" class="form-control" id="ems_nacional" wire:model="ems_nacional">
                        </div>
                        <div class="mb-3">
                            <label for="destino_1" class="form-label">Destino 1</label>
                            <input type="number" class="form-control" id="destino_1" wire:model="destino_1">
                        </div>
                        <div class="mb-3">
                            <label for="destino_2" class="form-label">Destino 2</label>
                            <input type="number" class="form-control" id="destino_2" wire:model="destino_2">
                        </div>
                        <div class="mb-3">
                            <label for="destino_3" class="form-label">Destino 3</label>
                            <input type="number" class="form-control" id="destino_3" wire:model="destino_3">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeModal">Cancelar</button>
                    <button type="button" class="btn btn-primary" wire:click="{{ $isEdit ? 'update' : 'store' }}">Guardar</button>
                </div>
            </div>
        </div>
    </div>
    
    
</div>

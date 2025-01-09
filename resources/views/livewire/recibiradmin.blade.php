<div class="container-fluid">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Recibir Admision AGBC</h1>
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
                                    <input type="text" wire:model="searchTerm" placeholder="Buscar..." class="form-control" style="margin-right: 10px;" wire:keydown.enter="$refresh">

                                    <button type="button" class="btn btn-primary" wire:click="$refresh">Buscar</button>
                                </div>
                                <div class="card-header">
                                    <div class="d-flex align-items-center">
                                        <div class="float-left d-flex align-items-center">
                                            <input type="date" wire:model="startDate" class="form-control" style="margin-right: 10px;">
                                            <input type="date" wire:model="endDate" class="form-control" style="margin-right: 10px;">
                                
                                            <!-- Filtro por departamento -->
                                            <select wire:model="selectedDepartment" class="form-control" style="margin-right: 10px;">
                                                <option value="">TODOS LOS DEPARTAMENTOS</option>
                                                <option value="LA PAZ">LA PAZ</option>
                                                <option value="COCHABAMBA">COCHABAMBA</option>
                                                <option value="SANTA CRUZ">SANTA CRUZ</option>
                                                <option value="ORURO">ORURO</option>
                                                <option value="POTOSÍ">POTOSÍ</option>
                                                <option value="TARIJA">TARIJA</option>
                                                <option value="CHUQUISACA">CHUQUISACA</option>
                                                <option value="PANDO">PANDO</option>
                                                <option value="BENI">BENI</option>
                                            </select>
                                            
                                
                                        </div>
                                        <div class="ml-auto">
                                            <button type="button" class="btn btn-success" wire:click="downloadReport">Reporte Recibidos</button>
                                        </div>
                                        <div class="ml-auto">
                                    
                                            <button type="button" class="btn btn-warning" wire:click="recibirAdmision">Recibir Admisión</button>
    
                                    </div>
                                    <div class="ml-auto">
                                        <button type="button" class="btn btn-info" wire:click="recibirHoy">
                                            Recibir Todos Hoy
                                        </button>
                                    </div>
                                    
                                    
                                    </div>
                                </div>
                                
                                        
                                </div>
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
                                        <th>
                                            <input type="checkbox" wire:model="selectAll" wire:key="select-all" />
                                        </th>

                                        <th>#</th> <!-- Columna para el número de fila -->
                                        <th>Origen</th>
                                        <th>Servicio</th>
                                        <th>Tipo Correspondencia</th>
                                        <th class="d-none d-lg-table-cell">Cantidad</th>
                                        <th>Peso</th>
                                        <th>Precio (Bs)</th>
                                        <th>Destino</th>
                                        <th>Código</th>
                                        <th class="d-none d-lg-table-cell">Dirección</th>
                                        <th class="d-none d-lg-table-cell">Provincia</th>
                                        <th class="d-none d-lg-table-cell">Ciudad</th>
                                        <th class="d-none d-lg-table-cell">País</th>
                                        <th>Fecha</th>

                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($admisiones as $admisione)
                                        <tr>
                                            <td>
                                                <input type="checkbox" wire:model="selectedAdmisiones" value="{{ $admisione->id }}" wire:key="admision-{{ $admisione->id }}" />
                                            </td>
                                            <td>{{ $loop->iteration }}</td> <!-- Mostrar número de fila -->
                                            <td>{{ $admisione->origen }}</td>
                                            <td>{{ $admisione->servicio }}</td>
                                            <td>{{ $admisione->tipo_correspondencia }}</td>
                                            <td>{{ $admisione->cantidad }}</td>
                                            <td>{{ $admisione->peso }}</td>
                                            <td>{{ $admisione->precio }}</td>
                                            <td>{{ $admisione->destino }}</td>
                                            <td>{{ $admisione->codigo }}</td>
                                           
                                            <td>{{ $admisione->direccion }}</td>
                                            <td>{{ $admisione->provincia }}</td>
                                            <td>{{ $admisione->ciudad }}</td>
                                            <td>{{ $admisione->pais }}</td>
                                            <td>{{ $admisione->fecha }}</td>

                                            <td>
                                                {{-- <button type="button" class="btn btn-info" wire:click="edit({{ $admisione->id }})">Editar</button> --}}
                                                {{-- <button type="button" class="btn btn-warning" wire:click="devolverAdmision({{ $admisione->id }})">Devolver a admisión</button> --}}

                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <!-- Modal -->
@if($showModal)
<div class="modal fade show" style="display: block;" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-scrollable" role="document">
        <form wire:submit.prevent="saveAdmissionData">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ingresar Peso EMS y Observación</h5>
                    <button type="button" class="close" wire:click="$set('showModal', false)">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    @foreach($admissionData as $id => $admission)
                        <div class="mb-4">
                            <div class="d-flex justify-content-between">
                                <h6>Admisión Código: {{ $admission['codigo'] }}</h6>
                                <button type="button" class="btn btn-danger btn-sm" wire:click="removeAdmissionFromModal({{ $id }})">
                                    Sacar Admisión
                                </button>
                            </div>
                            <div class="form-group">
                                <label for="peso_ems_{{ $id }}">Peso EMS</label>
                                <input type="text" id="peso_ems_{{ $id }}" class="form-control" wire:model.defer="admissionData.{{ $id }}.peso_ems">
                                @error('admissionData.' . $id . '.peso_ems') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group">
                                <label for="observacion_{{ $id }}">Observación</label>
                                <textarea id="observacion_{{ $id }}" class="form-control" wire:model.defer="admissionData.{{ $id }}.observacion"></textarea>
                                @error('admissionData.' . $id . '.observacion') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <hr>
                    @endforeach
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="$set('showModal', false)">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar y Descargar Reporte</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif

                        </div>
                        <div class="card-footer">
                            {{ $admisiones->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

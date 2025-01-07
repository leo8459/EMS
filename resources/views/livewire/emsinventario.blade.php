<div class="container-fluid">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Inventario <i class="el el-minus-sign"></i></h1>
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
                            <div class="float-left d-flex align-items-center">
                                <input type="text" wire:model="searchTerm" placeholder="Buscar..."
                                    class="form-control" style="margin-right: 10px;" wire:keydown.enter="$refresh">

                                <select wire:model="selectedCity" class="form-control" style="margin-right: 10px;">
                                    <option value="">Seleccione una ciudad</option>
                                    <option value="LA PAZ">LA PAZ</option>
                                    <option value="POTOSI">POTOSI</option>
                                    <option value="ORURO">ORURO</option>
                                    <option value="SANTA CRUZ">SANTA CRUZ</option>
                                    <option value="CHUQUISACA">CHUQUISACA</option>
                                    <option value="COCHABAMBA">COCHABAMBA</option>
                                    <option value="BENI">BENI</option>
                                    <option value="PANDO">PANDO</option>
                                    <option value="TARIJA">TARIJA</option>
                                </select>

                                <button type="button" class="btn btn-primary" wire:click="$refresh">Buscar</button>
                            </div>

                            <div class="d-flex justify-content-end align-items-center mt-3">
                                <a href="{{ route('asignarcartero') }}" class="btn btn-success"
                                    style="margin-right: 10px;">
                                    Asignar Carteros
                                </a>

                                <button class="btn btn-warning" wire:click="abrirModal" style="margin-right: 10px;">
                                    Mandar a Regional / Reencaminar
                                </button>
                                <button class="btn btn-secondary" wire:click="mandarAVentanilla">
                                    Mandar a Ventanilla
                                </button>
                            </div>


                            {{-- @if (session()->has('message'))
                                <div class="alert alert-success">
                                    {{ session('message') }}
                                </div>
                            @endif
                            @if (session()->has('error'))
                                <div class="alert alert-danger">
                                    {{ session('error') }}
                                </div>
                            @endif --}}
                            <div class="card-body">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>
                                            </th>
                                            <th>#</th>
                                            <th>Origen</th>
                                            <th>Servicio</th>
                                            <th>Tipo Correspondencia</th>
                                            <th>Cantidad</th>
                                            <th>Peso</th>
                                            <th>Precio (Bs)</th>
                                            <th>Destino</th>
                                            <th>Envio</th>
                                            <th>Código</th>
                                            <th>Fecha</th>
                                            <th>Observación</th>
                                            @hasrole('SuperAdmin|Administrador')
                                                <th>Admision</th>
                                            @endhasrole
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($admisiones as $admisione)
                                            @php
                                                $now = \Carbon\Carbon::now();
                                                $fechaAdmision = \Carbon\Carbon::parse($admisione->fecha);
                                                $diffInHours = $now->diffInHours($fechaAdmision);
                                                $rowClass = '';
                                
                                                if ($diffInHours <= 24) {
                                                    $rowClass = 'table-success'; // Verde
                                                } elseif ($diffInHours <= 48) {
                                                    $rowClass = 'table-warning'; // Amarillo
                                                } else {
                                                    $rowClass = 'table-danger'; // Rojo
                                                }
                                            @endphp
                                            <tr class="{{ $rowClass }}">
                                                <td>
                                                    <!-- Asegúrate de que el checkbox esté dentro del bucle -->
                                                    <input type="checkbox" wire:model="selectedAdmisiones" value="{{ $admisione->id }}" />
                                                </td>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $admisione->origen }}</td>
                                                <td>{{ $admisione->servicio }}</td>
                                                <td>{{ $admisione->tipo_correspondencia }}</td>
                                                <td>{{ $admisione->cantidad }}</td>
                                                <td>{{ $admisione->peso_ems ?: $admisione->peso }}</td>
                                                <td>{{ $admisione->precio }}</td>
                                                <td>{{ $admisione->reencaminamiento ?? $admisione->ciudad }}</td>
                                                <td>{{ $admisione->destino }}</td>
                                                <td>{{ $admisione->codigo }}</td>
                                                <td>{{ $admisione->fecha }}</td>
                                                <td>{{ $admisione->observacion_entrega ?? '' }}</td>
                                                @hasrole('SuperAdmin|Administrador')
                                                    <td>{{ $admisione->user->name ?? 'No asignado' }}</td>
                                                @endhasrole
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                

                                <!-- Botón para abrir el modal -->
                            </div>
                            @if ($showModal)
                            <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background-color: rgba(0,0,0,0.5);">
                                <div class="modal-dialog modal-lg" role="document">
                                    <div class="modal-content" style="max-height: 80vh; overflow: hidden;">
                                        <!-- Encabezado -->
                                        <div class="modal-header">
                                            <h5 class="modal-title">Confirmar Envío</h5>
                                            <button type="button" class="close" wire:click="$set('showModal', false)">
                                                <span>&times;</span>
                                            </button>
                                        </div>
                                        <!-- Cuerpo con scroll -->
                                        <div class="modal-body" style="max-height: 60vh; overflow-y: auto;">
                                            <p>Puede enviar las admisiones seleccionadas a la regional o reencaminarlas a otro departamento.</p>
                        
                                            <!-- Mostrar el número total de envíos seleccionados -->
                                            <p><strong>Total de envíos seleccionados:</strong> {{ count($selectedAdmisionesCodes) }}</p>
                        
                                            <div class="form-group">
                                                <label for="selectedDepartment">Reencaminar al departamento (obligatorio):</label>
                                                <select wire:model="selectedDepartment" class="form-control" id="selectedDepartment">
                                                    <option value="">Seleccione un departamento</option>
                                                    <option value="LA PAZ">LA PAZ</option>
                                                    <option value="ORURO">ORURO</option>
                                                    <option value="BENI">BENI</option>
                                                    <option value="COCHABAMBA">COCHABAMBA</option>
                                                    <option value="SANTA CRUZ">SANTA CRUZ</option>
                                                    <option value="POTOSI">POTOSI</option>
                                                    <option value="CHUQUISACA">CHUQUISACA</option>
                                                    <option value="PANDO">PANDO</option>
                                                    <option value="TARIJA">TARIJA</option>
                                                </select>
                                                @if (!$selectedDepartment)
                                                    <small class="text-danger">Debe seleccionar un departamento.</small>
                                                @endif
                                            </div>
                        
                                            <!-- Mostrar los códigos de las admisiones seleccionadas -->
                                            <ul>
                                                @foreach ($selectedAdmisionesCodes as $codigo)
                                                    <li>Código: {{ $codigo }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                        <!-- Pie -->
                                        <div class="modal-footer">
                                            <button class="btn btn-primary" wire:click="mandarARegional">Guardar y Generar Excel</button>
                                            <button class="btn btn-secondary" wire:click="$set('showModal', false)">Cancelar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                        


                            @if ($showReencaminamientoModal)
                                <div class="modal fade show d-block" tabindex="-1" role="dialog"
                                    style="background-color: rgba(0,0,0,0.5);">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <!-- Encabezado -->
                                            <div class="modal-header">
                                                <h5 class="modal-title">Reencaminamiento</h5>
                                                <button type="button" class="close"
                                                    wire:click="$set('showReencaminamientoModal', false)">
                                                    <span>&times;</span>
                                                </button>
                                            </div>
                                            <!-- Cuerpo -->
                                            <div class="modal-body">
                                                <p>Seleccione el departamento al que desea reencaminar las admisiones
                                                    seleccionadas:</p>
                                                <div class="form-group">
                                                    <label for="selectedDepartment">Departamento:</label>
                                                    <select wire:model="selectedDepartment" class="form-control"
                                                        id="selectedDepartment">
                                                        <option value="">Seleccione un departamento</option>
                                                        <option value="LA PAZ">LA PAZ</option>
                                                        <option value="ORURO">ORURO</option>
                                                        <option value="BENI">BENI</option>
                                                        <option value="COCHABAMBA">COCHABAMBA</option>

                                                    </select>
                                                </div>
                                                <!-- Mostrar los códigos de las admisiones seleccionadas -->
                                                <ul>
                                                    @foreach ($selectedAdmisionesCodes as $codigo)
                                                        <li>Código: {{ $codigo }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                            <!-- Pie -->
                                            <div class="modal-footer">
                                                <button class="btn btn-primary"
                                                    wire:click="reencaminar">Confirmar</button>
                                                <button class="btn btn-secondary"
                                                    wire:click="$set('showReencaminamientoModal', false)">Cancelar</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="card-footer">
                                {{ $admisiones->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </section>

</div>

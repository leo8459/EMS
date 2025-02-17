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
                            <!-- Botón para abrir el modal -->

                            <div class="d-flex justify-content-end align-items-center mt-3">
                                <a href="{{ route('asignarcartero') }}" class="btn btn-success"
                                    style="margin-right: 10px;">
                                    Asignar Carteros
                                </a>
                                <button class="btn btn-info" wire:click="abrirModalCN33">
                                    Añadir a CN-33
                                </button>

                                <button class="btn btn-warning" wire:click="abrirModal" style="margin-right: 10px;">
                                    Mandar a Regional / Reencaminar
                                </button>
                                <button class="btn btn-secondary" wire:click="mandarAVentanilla">
                                    Mandar a Ventanilla
                                </button>
                                <button type="button" class="btn btn-success" data-toggle="modal"
                                    data-target="#createPaqueteModal">
                                    Generar Envio
                                </button>
                                <button class="btn btn-info" wire:click="abrirModalReimprimir">Reimprimir CN-33</button>

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
                                            <th><input type="checkbox" wire:model="selectAll" /></th>
                                            <th>#</th>
                                            <th>Tipo</th>
                                            <th>Origen</th>
                                            <th>Servicio</th>
                                            <th>Tipo Correspondencia</th>
                                            <th>Cantidad</th>
                                            <th>Peso (kg)</th>
                                            <th>Destino</th>
                                            <th>Código</th>
                                            <th>Fecha</th>
                                            <th>Observación</th>
                                            <th>Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Admisiones internas -->
                                        @foreach ($admisiones as $index => $admisione)
                                            @php
                                                $now = \Carbon\Carbon::now();
                                                $fechaAdmision = \Carbon\Carbon::parse($admisione->fecha);
                                                $diffInHours = $now->diffInHours($fechaAdmision);
                                                $rowClass = 'table-light';
                                                $statusText = '';
    
                                                if ($diffInHours <= 24) {
                                                    $rowClass = 'table-success'; // Verde
                                                    $statusText = 'DISPONIBLE';
                                                } elseif ($diffInHours <= 48) {
                                                    $rowClass = 'table-warning'; // Amarillo
                                                    $statusText = 'RETRASO';
                                                } else {
                                                    $rowClass = 'table-danger'; // Rojo
                                                    $statusText = 'DEVOLVER';
                                                }
                                            @endphp
                                            <tr class="{{ $rowClass }}">
                                                <td><input type="checkbox" wire:model="selectedAdmisiones" value="{{ $admisione->id }}"></td>
                                                <td>{{ $loop->iteration }}</td>
                                                <td><span class="badge bg-primary">EMS</span></td>
                                                <td>{{ $admisione->origen }}</td>
                                                <td>{{ $admisione->servicio }}</td>
                                                <td>{{ $admisione->tipo_correspondencia }}</td>
                                                <td>{{ $admisione->cantidad }}</td>
                                                <td>{{ $admisione->peso_ems ?: $admisione->peso }}</td>
                                                <td>{{ $admisione->reencaminamiento ?? $admisione->ciudad }}</td>
                                                <td>{{ $admisione->codigo }}</td>
                                                <td>{{ $admisione->fecha }}</td>
                                                <td>{{ $admisione->observacion_entrega ?? '' }}</td>
                                                <td><strong>{{ $statusText }}</strong></td>
                                            </tr>
                                        @endforeach
    
                                        <!-- Solicitudes externas -->
                                        @foreach ($solicitudesExternas as $index => $solicitud)
                                            @php
                                                $fullCode = $solicitud['guia'] ?? '';
                                                $leftCode = substr($fullCode, 4, 3);
                                                $rightCode = substr($fullCode, 7, 3);
                                                $codeToCity = [
                                                    'LPB' => 'LA PAZ',
                                                    'SRZ' => 'SANTA CRUZ',
                                                    'CIJ' => 'PANDO',
                                                    'TDD' => 'BENI',
                                                    'TJA' => 'TARIJA',
                                                    'SRE' => 'CHUQUISACA',
                                                    'ORU' => 'ORURO',
                                                    'POI' => 'POTOSI',
                                                    'CBB' => 'COCHABAMBA',
                                                ];
                                                $origen = $codeToCity[strtoupper($leftCode)] ?? 'DESCONOCIDO';
                                                $destino = $codeToCity[strtoupper($rightCode)] ?? 'DESCONOCIDO';
    
                                                // Calcular estado según `fecha_recojo_c`
                                                $fechaRecojo = \Carbon\Carbon::parse($solicitud['fecha_recojo_c'] ?? now());
                                                $diffInHours = $now->diffInHours($fechaRecojo);
                                                $rowClass = 'table-light';
                                                $statusText = '';
    
                                                if ($diffInHours <= 24) {
                                                    $rowClass = 'table-success'; // Verde
                                                    $statusText = 'DISPONIBLE';
                                                } elseif ($diffInHours <= 48) {
                                                    $rowClass = 'table-warning'; // Amarillo
                                                    $statusText = 'RETRASO';
                                                } else {
                                                    $rowClass = 'table-danger'; // Rojo
                                                    $statusText = 'DEVOLVER';
                                                }
                                            @endphp
                                            <tr class="{{ $rowClass }}">
                                                <td><input type="checkbox" wire:model="selectedSolicitudesExternas" value="{{ $solicitud['guia'] }}"></td>
                                                <td>{{ $loop->iteration + count($admisiones) }}</td>
                                                <td><span class="badge bg-warning">Contratos</span></td>
                                                <td>{{ $origen }}</td>
                                                <td>-</td>
                                                <td>-</td>
                                                <td>-</td>
                                                <td>{{ $solicitud['peso_o'] ?? '-' }}</td>
                                                <td>{{ $destino }}</td>
                                                <td>{{ $solicitud['guia'] }}</td>
                                                <td>{{ $solicitud['fecha_recojo_c'] ?? '-' }}</td>
                                                <td>-</td>
                                                <td><strong>{{ $statusText }}</strong></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @if ($showCN33Modal)
                                <div class="modal fade show d-block" tabindex="-1" role="dialog"
                                    style="background-color: rgba(0,0,0,0.5);">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Añadir a CN-33</h5>
                                                <button type="button" class="close"
                                                    wire:click="$set('showCN33Modal', false)">
                                                    <span>&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="form-group">
                                                    <label for="manualManifiesto">Manifiesto (obligatorio):</label>
                                                    <input type="text" wire:model="manualManifiesto"
                                                        class="form-control" id="manualManifiesto"
                                                        placeholder="Ej: BO0456789">
                                                    @error('manualManifiesto')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button class="btn btn-success mt-2" wire:click="añadirACN33">
                                                    Guardar en CN-33
                                                </button>
                                                <button class="btn btn-secondary"
                                                    wire:click="$set('showCN33Modal', false)">
                                                    Cancelar
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Modal para Reimprimir -->
                            @if ($showReimprimirModal)
                                <div class="modal fade show d-block" tabindex="-1" role="dialog"
                                    style="background-color: rgba(0,0,0,0.5);">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Reimprimir Manifiesto</h5>
                                                <button type="button" class="close"
                                                    wire:click="$set('showReimprimirModal', false)">
                                                    <span>&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="form-group">
                                                    <label for="manifiestoInput">Ingrese el Manifiesto:</label>
                                                    <input type="text" id="manifiestoInput" class="form-control"
                                                        wire:model="manifiestoInput">
                                                    @error('manifiestoInput')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button class="btn btn-secondary"
                                                    wire:click="$set('showReimprimirModal', false)">Cancelar</button>
                                                <button class="btn btn-primary"
                                                    wire:click="reimprimirManifiesto">Reimprimir</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if ($showModal)
                                <div class="modal fade show d-block" tabindex="-1" role="dialog"
                                    style="background-color: rgba(0,0,0,0.5);">
                                    <div class="modal-dialog modal-lg" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Confirmar Envío</h5>
                                                <button type="button" class="close"
                                                    wire:click="$set('showModal', false)">
                                                    <span>&times;</span>
                                                </button>
                                            </div>

                                            <!-- Aquí aplicamos el scroll al cuerpo del modal -->
                                            <div class="modal-body" style="max-height: 500px; overflow-y: auto;">
                                                <p>Puede enviar las admisiones seleccionadas a la regional o
                                                    reencaminarlas a otro departamento.</p>

                                                <p><strong>Total de envíos seleccionados:</strong>
                                                    {{ count($selectedAdmisionesCodes) }}</p>

                                                <!-- Selección del departamento de destino -->
                                                <div class="form-group">
                                                    <label for="selectedDepartment">Enviar al departamento
                                                        (obligatorio):</label>
                                                    <select wire:model="selectedDepartment" class="form-control"
                                                        id="selectedDepartment">
                                                        <option value="">Seleccione la Regional de Destino
                                                        </option>
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
                                                        <small class="text-danger">Debe seleccionar un
                                                            departamento.</small>
                                                    @endif
                                                </div>

                                                <!-- Campo para manifiesto -->
                                                <div class="form-group">
                                                    <label for="manualManifiesto">Manifiesto (déjelo vacío para generar
                                                        uno automático):</label>
                                                    <input type="text" wire:model="manualManifiesto"
                                                        class="form-control" id="manualManifiesto"
                                                        placeholder="Ej: BO0456789 (opcional)">
                                                </div>

                                                <!-- Selección de tipo de transporte -->
                                                <div class="form-group">
                                                    <label for="selectedTransport">Medio de Transporte:</label>
                                                    <select wire:model="selectedTransport" class="form-control"
                                                        id="selectedTransport">
                                                        <option value="AEREO">AÉREO</option>
                                                        <option value="TERRESTRE">TERRESTRE</option>
                                                    </select>
                                                </div>

                                                <!-- Número de vuelo -->
                                                <div class="form-group">
                                                    <label for="numeroVuelo">N° de vuelo/medio transporte
                                                        (opcional):</label>
                                                    <input type="text" wire:model="numeroVuelo"
                                                        class="form-control" id="numeroVuelo"
                                                        placeholder="Ingrese el número de vuelo si aplica.">
                                                </div>

                                                <hr>

                                                <!-- Mostrar solo las admisiones seleccionadas -->
                                                <h5>Admisiones Seleccionadas</h5>
                                                <table class="table table-striped">
                                                    <thead>
                                                        <tr>
                                                            <th>Código</th>
                                                            <th>Origen</th>
                                                            <th>Destino</th>
                                                            <th>Peso (kg)</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($selectedAdmisionesList as $admision)
                                                            <tr>
                                                                <td>{{ $admision->codigo }}</td>
                                                                <td>{{ $admision->origen }}</td>
                                                                <td>{{ $admision->reencaminamiento ?? $admision->ciudad }}
                                                                </td>
                                                                <td>{{ $admision->peso }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>

                                                <!-- Mostrar solo los registros externos seleccionados -->
                                                @if (!empty($solicitudesExternas))
                                                    <h5>Solicitudes Externas Seleccionadas</h5>
                                                    <table class="table table-striped">
                                                        <thead>
                                                            <tr>
                                                                <th>Guía</th>
                                                                <th>Remitente</th>
                                                                <th>Destinatario</th>
                                                                <th>Peso</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach ($solicitudesExternas as $solicitud)
                                                                <tr>
                                                                    <td>{{ $solicitud['guia'] }}</td>
                                                                    <td>{{ $solicitud['remitente'] }}</td>
                                                                    <td>{{ $solicitud['destinatario'] }}</td>
                                                                    <td>{{ $solicitud['peso_o'] ?? '-' }}</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                @endif
                                            </div>

                                            <div class="modal-footer">
                                                <button class="btn btn-primary" wire:click="mandarARegional">
                                                    Guardar y Generar PDF
                                                </button>
                                                <button class="btn btn-secondary"
                                                    wire:click="$set('showModal', false)">Cancelar</button>
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
                            @if ($showEditModal)
                                <div class="modal fade show d-block" tabindex="-1" role="dialog"
                                    style="background-color: rgba(0,0,0,0.5);">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <!-- Encabezado -->
                                            <div class="modal-header">
                                                <h5 class="modal-title">Editar Dirección</h5>
                                                <button type="button" class="close"
                                                    wire:click="$set('showEditModal', false)">
                                                    <span>&times;</span>
                                                </button>
                                            </div>
                                            <!-- Cuerpo -->
                                            <div class="modal-body">
                                                <div class="form-group">
                                                    <label for="editDireccion">Dirección:</label>
                                                    <input type="text" id="editDireccion"
                                                        wire:model="editDireccion" class="form-control">
                                                    @error('editDireccion')
                                                        <small class="text-danger">{{ $message }}</small>
                                                    @enderror
                                                </div>
                                            </div>
                                            <!-- Pie -->
                                            <div class="modal-footer">
                                                <button class="btn btn-primary" wire:click="guardarEdicion">Guardar
                                                    Cambios</button>
                                                <button class="btn btn-secondary"
                                                    wire:click="$set('showEditModal', false)">Cancelar</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            @if (session()->has('message'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    {{ session('message') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"
                                        aria-label="Close"></button>
                                </div>
                            @endif

                            @if (session()->has('error'))
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    {{ session('error') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"
                                        aria-label="Close"></button>
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
<script>
    document.addEventListener('DOMContentLoaded', function() {
        window.addEventListener('reloadPage', function() {
            // Recargar la página
            location.reload();
        });
    });
</script>
<script>
    window.addEventListener('reload-page', () => {
        location.reload();
    });
</script>

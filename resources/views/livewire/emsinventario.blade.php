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

                                <button class="btn btn-warning" wire:click="abrirModal" style="margin-right: 10px;">
                                    Mandar a Regional / Reencaminar
                                </button>
                                <button class="btn btn-secondary" wire:click="mandarAVentanilla">
                                    Mandar a Ventanilla
                                </button>
                                <button type="button" class="btn btn-success" data-toggle="modal"
                                    data-target="#createPaqueteModal">
                                    Nuevo Admision
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
                                            <th>Estado</th>
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
                                                <td>
                                                    <!-- Asegúrate de que el checkbox esté dentro del bucle -->
                                                    <input type="checkbox" wire:model="selectedAdmisiones"
                                                        value="{{ $admisione->id }}" />
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
                                                <td><strong>{{ $statusText }}</strong></td>
                                                <td>

                                                    <button class="btn btn-info btn-sm"
                                                        wire:click="abrirEditModal({{ $admisione->id }})">
                                                        Cambiar Direccion
                                                    </button>
                                                </td>
                                                @hasrole('SuperAdmin|Administrador')
                                                    <td>{{ $admisione->user->name ?? 'No asignado' }}</td>
                                                @endhasrole
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>

                                <!-- Modal para Crear Nuevo Paquete -->
                                <div wire:ignore.self class="modal fade" id="createPaqueteModal" tabindex="-1"
                                    role="dialog" aria-labelledby="createPaqueteModalLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-lg" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="createPaqueteModalLabel">Crear Nuevo
                                                    Admision</h5>
                                                <button type="button" class="close" data-dismiss="modal"
                                                    aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>

                                            <div class="modal-body">
                                                <form wire:submit.prevent="store">

                                                    <!-- Sección DATOS -->
                                                    <h5 class="mt-3" style="color: #003366;">DATOS</h5>
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="origen">Origen*</label>
                                                                <input type="text" class="form-control"
                                                                    id="origen" wire:model="origen" readonly>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="servicio">Tipo de Servicio*</label>
                                                                <select class="form-control" id="servicio"
                                                                    wire:model="servicio">
                                                                    <option value="">Seleccione el servicio
                                                                    </option>
                                                                    {{-- <option value="EMS">EMS</option> --}}
                                                                    <option value="OFICIAL">OFICIAL</option>
                                                                    <option value="TRANSITO">INTERNACIONAL</option>

                                                                    {{-- <option value="EMS">ENVIO CON DEVOLUCION</option>
                                  <option value="EMS">POSTPAGO</option> --}}

                                                                    {{-- <option value="ENCOMIENDA">ENCOMIENDA</option>
                                  <option value="TRADICIONAL">TRADICIONAL</option>
                                  <option value="CERTIFICADA">CERTIFICADA</option>
                                  <option value="ORDINARIA">ORDINARIA</option>
                                  <option value="EXPRESO">EXPRESO</option> --}}
                                                                </select>

                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label
                                                                    for="tipo_correspondencia">Correspondencia*</label>
                                                                <select class="form-control" id="tipo_correspondencia"
                                                                    wire:model="tipo_correspondencia" wire:ignore>
                                                                    <option value="">Seleccione el tipo de
                                                                        correspondencia</option>
                                                                    <option value="CARTA">CARTA</option>
                                                                    <option value="ENCOMIENDA">ENCOMIENDA</option>
                                                                    <option value="DOCUMENTO">DOCUMENTO</option>
                                                                    <option value="PAQUETE">PAQUETE</option>
                                                                    <option value="SACA M">SACA M</option>
                                                                    <option value="REVISTA">REVISTA</option>
                                                                    <option value="IMPRESO">IMPRESO</option>
                                                                    <option value="CECOGRAMA">CECOGRAMA</option>
                                                                    <option value="PEQUEÑO PAQUETE">PEQUEÑO PAQUETE
                                                                    </option>

                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="contenido">Contenido</label>
                                                        <textarea class="form-control" id="contenido" wire:model="contenido"></textarea>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="cantidad">Cantidad*</label>
                                                                <input type="number" class="form-control"
                                                                    id="cantidad" placeholder="Cantidad"
                                                                    wire:model="cantidad" value="1" disabled>
                                                            </div>

                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="destino">Destino*</label>
                                                                <select class="form-control" id="destino"
                                                                    wire:model="destino" wire:ignore>
                                                                    <option value="">Seleccione el destino
                                                                    </option>
                                                                    <option value="OFICIAL">OFICIAL
                                                                        
                                                                    </option>
                                                                    <option value="TRANSITO">TRANSITO
                                                                        
                                                                    </option>
                                                                    {{-- <option value="SUPEREXPRESS">NACIONAL SUPEREXPRESS
                                                                    </option>
                                                                    <option value="DEVOLUCION">NACIONAL CON DEVOLUCION
                                                                    </option>
                                                                    <option value="NACIONAL">NACIONAL EMS</option>
                                                                    <option value="POSTPAGO">NACIONAL POSTPAGO</option>

                                                                    <option value="CIUDADES INTERMEDIAS">CIUDADES
                                                                        INTERMEDIAS</option>
                                                                    <option value="TRINIDAD COBIJA">TRINIDAD COBIJA
                                                                    </option>
                                                                    <option value="RIVERALTA GUAYARAMERIN">RIVERALTA
                                                                        GUAYARAMERIN</option>
                                                                    <option value="EMS COBERTURA 1">EMS COBERTURA 1
                                                                    </option>
                                                                    <option value="EMS COBERTURA 2">EMS COBERTURA 2
                                                                    </option>
                                                                    <option value="EMS COBERTURA 3">EMS COBERTURA 3
                                                                    </option>
                                                                    <option value="EMS COBERTURA 4">EMS COBERTURA 4
                                                                    </option> --}}
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="codigo">Código*</label>
                                                                <input type="text" class="form-control"
                                                                    id="codigo" wire:model="codigo">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="numero_factura">Número de Factura</label>
                                                                <input type="text" class="form-control"
                                                                    id="numero_factura" wire:model="numero_factura">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="peso">Peso (Kg.)*</label>
                                                                <input type="text" wire:model="peso">





                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="precio">Precio*</label>
                                                                <input type="number" class="form-control"
                                                                    id="precio" wire:model="precio" value="0"
                                                                    readonly>
                                                            </div>
                                                        </div>
                                                    </div>


                                                    <!-- Sección REMITENTE -->
                                                    <h5 class="mt-3" style="color: #003366;">REMITENTE</h5>
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <div class="form-group" style="position: relative;">
                                                                <label for="nombre_remitente">Nombre Remitente*</label>
                                                                <input type="text" class="form-control"
                                                                    id="nombre_remitente"
                                                                    wire:model="nombre_remitente"
                                                                    oninput="showSuggestions(this.value)" wire:ignore>
                                                                <!-- Contenedor para las sugerencias -->
                                                                <div id="suggestions"
                                                                    style="position: absolute; background-color: white; border: 1px solid #ccc; width: 100%; max-height: 150px; overflow-y: auto; z-index: 1000;">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="nombre_envia">Nombre y Apellido del que
                                                                    Envia</label>
                                                                <input type="text" class="form-control"
                                                                    id="nombre_envia" wire:model="nombre_envia">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="carnet">Carnet*</label>
                                                                <input type="text" class="form-control"
                                                                    id="carnet" wire:model="carnet">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="telefono_remitente">Teléfono
                                                                    Remitente*</label>
                                                                <input type="text" class="form-control"
                                                                    id="telefono_remitente"
                                                                    wire:model="telefono_remitente">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Sección DESTINATARIO -->
                                                    <h5 class="mt-3" style="color: #003366;">DESTINATARIO</h5>
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="nombre_destinatario">Nombre
                                                                    Destinatario*</label>
                                                                <input type="text" class="form-control"
                                                                    id="nombre_destinatario"
                                                                    wire:model="nombre_destinatario">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="telefono_destinatario">Teléfono
                                                                    Destinatario</label>
                                                                <input type="text" class="form-control"
                                                                    id="telefono_destinatario"
                                                                    wire:model="telefono_destinatario">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <!-- Cambiado a col-12 para ocupar todo el ancho -->
                                                            <div class="form-group">
                                                                <label for="direccion">Dirección*</label>
                                                                <input type="text" class="form-control"
                                                                    id="direccion" wire:model="direccion">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row">

                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="ciudad">Ciudad*</label>
                                                                <select class="form-control" id="ciudad"
                                                                    wire:model="ciudad">
                                                                    <option value="">Seleccione una ciudad
                                                                    </option>
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
                                                            </div>
                                                        </div>


                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="provincia">Provincia</label>
                                                                <input type="text" class="form-control"
                                                                    id="provincia" wire:model="provincia">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="pais">País*</label>
                                                                <input type="text" class="form-control"
                                                                    id="pais" wire:model="pais">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-dismiss="modal">Cerrar</button>
                                                        <button type="button" class="btn btn-secondary"
                                                            onclick="saveFrequentSend()">Guardar
                                                            como envío frecuente</button>


                                                        <button type="submit"
                                                            class="btn btn-primary">Guardar</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Botón para abrir el modal -->
                            </div>
                            <!-- Modal para Reimprimir -->
                            @if ($showReimprimirModal)
                            <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background-color: rgba(0,0,0,0.5);">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Reimprimir Manifiesto</h5>
                                            <button type="button" class="close" wire:click="$set('showReimprimirModal', false)">
                                                <span>&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="form-group">
                                                <label for="manifiestoInput">Ingrese el Manifiesto:</label>
                                                <input type="text" id="manifiestoInput" class="form-control" wire:model="manifiestoInput">
                                                @error('manifiestoInput') <span class="text-danger">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button class="btn btn-secondary" wire:click="$set('showReimprimirModal', false)">Cancelar</button>
                                            <button class="btn btn-primary" wire:click="reimprimirManifiesto">Reimprimir</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                            
                            @if ($showModal)
                                <div class="modal fade show d-block" tabindex="-1" role="dialog"
                                    style="background-color: rgba(0,0,0,0.5);">
                                    <div class="modal-dialog modal-lg" role="document">
                                        <div class="modal-content" style="max-height: 80vh; overflow: hidden;">
                                            <!-- Encabezado -->
                                            <div class="modal-header">
                                                <h5 class="modal-title">Confirmar Envío</h5>
                                                <button type="button" class="close"
                                                    wire:click="$set('showModal', false)">
                                                    <span>&times;</span>
                                                </button>
                                            </div>
                                            <!-- Cuerpo con scroll -->
                                            <div class="modal-body" style="max-height: 60vh; overflow-y: auto;">
                                                <p>Puede enviar las admisiones seleccionadas a la regional o
                                                    reencaminarlas a otro departamento.</p>

                                                <!-- Mostrar el número total de envíos seleccionados -->
                                                <p><strong>Total de envíos seleccionados:</strong>
                                                    {{ count($selectedAdmisionesCodes) }}</p>

                                                <div class="form-group">
                                                    <label for="selectedDepartment">Reencaminar al departamento
                                                        (obligatorio):</label>
                                                    <select wire:model="selectedDepartment" class="form-control"
                                                        id="selectedDepartment">
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
                                                        <small class="text-danger">Debe seleccionar un
                                                            departamento.</small>
                                                    @endif
                                                </div>

                                                <!-- NUEVO: Campo para ingresar el Manifiesto manual -->
                                                <div class="form-group">
                                                    <label for="manualManifiesto">Manifiesto (déjelo vacío para generar
                                                        uno automáticamente):</label>
                                                    <input type="text" wire:model="manualManifiesto"
                                                        class="form-control" id="manualManifiesto"
                                                        placeholder="Ej: BO0456789 (opcional)">
                                                    <!-- No es necesario forzar validación, ya que si está vacío generará uno automático -->
                                                </div>

                                                <!-- Mostrar los códigos de las admisiones seleccionadas -->
                                                <ul>
                                                    @foreach ($selectedAdmisionesCodes as $codigo)
                                                        <li>Código: {{ $codigo }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                            <div class="modal-footer">
                                                <button class="btn btn-primary" wire:click="mandarARegional">
                                                    Guardar y Generar Excel
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
    document.addEventListener('livewire:load', function() {
        // Mostrar el modal
        window.addEventListener('mostrar-modal-expedicion-hoy', function() {
            $('#modalExpedicionHoy').modal('show');
        });

        // Ocultar el modal
        window.addEventListener('ocultar-modal-expedicion-hoy', function() {
            $('#modalExpedicionHoy').modal('hide');
        });
    });
    window.addEventListener('page-reload', () => {
        location.reload();
    });
</script>

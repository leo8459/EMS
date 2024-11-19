<div class="container-fluid">
    <!-- Encabezado y Breadcrumbs -->
    <section class="content-header">
        <!-- ... -->
    </section>

    <section class="content">
        <div class="container-fluid">
            <!-- Tabla de despachos -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <!-- Barra de búsqueda y botón Nuevo Despacho -->
                            <div class="d-flex align-items-center">
                                <input type="text" wire:model="searchTerm" placeholder="Buscar..." class="form-control" style="margin-right: 10px;" wire:keydown.enter="$refresh">

                                <button type="button" class="btn btn-primary" wire:click="$refresh">Buscar</button>
                                <button type="button" class="btn btn-success" data-toggle="modal"
                                    data-target="#createPaqueteModal">
                                    Nuevo Admision
                                </button>
                                <button type="button" class="btn btn-warning mt-2" wire:click="entregarAExpedicion">Entregar a Expedición</button>
                                <button type="button" class="btn btn-primary mt-2" wire:click="abrirModalEntregarHoy">
                                    Entregar a Expedición Generado Hoy
                                </button>
                                
                                
                            </div>
                        </div>

                        <!-- Mensajes de éxito o error -->
                        @if (session()->has('message'))
                            <div class="alert alert-success">{{ session('message') }}</div>
                        @endif
                        @if (session()->has('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif

                        <!-- Tabla de registros -->
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th><input type="checkbox" wire:model="selectAll" /></th>

                                            <th>#</th> <!-- Columna para el número de fila -->
                                            <th>Origen</th>
                                            <th>Servicio</th>
                                            <th>Tipo Correspondencia</th>
                                            <th class="d-none d-lg-table-cell">Cantidad</th>
                                            <th>Peso</th>
                                            <th>Precio (Bs)</th>
                                            <th>Destino</th>
                                            <th>Código</th>
                                            <th class="d-none d-md-table-cell">Número Factura</th>
                                            <th>Nombre Remitente</th>
                                            <th>Nombre Envía</th>
                                            <th class="d-none d-md-table-cell">Carnet</th>
                                            <th>Teléfono Remitente</th>
                                            <th>Nombre Destinatario</th>
                                            <th>Teléfono Destinatario</th>
                                            <th class="d-none d-lg-table-cell">Dirección</th>
                                            <th class="d-none d-lg-table-cell">Provincia</th>
                                            <th class="d-none d-lg-table-cell">Ciudad</th>
                                            <th class="d-none d-lg-table-cell">País</th>
                                            <th>Fecha</th>

                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($admisiones as $admisione)
                                            <tr>
                                                <td>
                                                    <input type="checkbox" wire:model="selectedAdmisiones" value="{{ $admisione->id }}" />
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
                                                <td>{{ $admisione->numero_factura }}</td>
                                                <td>{{ $admisione->nombre_remitente }}</td>
                                                <td>{{ $admisione->nombre_envia }}</td>
                                                <td>{{ $admisione->carnet }}</td>
                                                <td>{{ $admisione->telefono_remitente }}</td>
                                                <td>{{ $admisione->nombre_destinatario }}</td>
                                                <td>{{ $admisione->telefono_destinatario }}</td>
                                                <td>{{ $admisione->direccion }}</td>
                                                <td>{{ $admisione->provincia }}</td>
                                                <td>{{ $admisione->ciudad }}</td>
                                                <td>{{ $admisione->pais }}</td>
                                                <td>{{ $admisione->fecha }}</td>

                                                <td>
                                                    {{-- <button type="button" class="btn btn-info" wire:click="edit({{ $admisione->id }})">Editar</button> --}}
                                                    <button type="button" class="btn btn-danger" wire:click="delete({{ $admisione->id }})">Eliminar</button>
                                                    <button type="button" class="btn btn-secondary" wire:click="reimprimir({{ $admisione->id }})">Reimprimir</button>
                                                    <button type="button" class="btn btn-info" wire:click="edit({{ $admisione->id }})" data-toggle="modal" data-target="#updateDespachoModal">Editar</button>

                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                            </table>
                        </div>

                        <!-- Paginación -->
                        <div class="card-footer">
                            {{ $admisiones->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal para Crear Nuevo Paquete -->
    <div wire:ignore.self class="modal fade" id="createPaqueteModal" tabindex="-1" role="dialog" aria-labelledby="createPaqueteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createPaqueteModalLabel">Crear Nuevo Admision</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
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
                                    <input type="text" class="form-control" id="origen" wire:model="origen" readonly>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="servicio">Tipo de Servicio*</label>
                                    <select class="form-control" id="servicio" wire:model="servicio" wire:ignore>
                                        <option value="">Seleccione el servicio</option>
                                        <option value="EMS">EMS</option>
                                        <option value="CERTIFICADA">CERTIFICADA</option>
                                        <option value="ORDINARIA">ORDINARIA</option>
                                        <option value="ECA/PLIEGOS">ECA/PLIEGOS</option>
                                        <option value="SUPEREXPRESS">SUPEREXPRESS</option>
                                        <option value="EXPRESO">EXPRESO</option>
                                        <option value="AVISO DE RECIBO">AVISO DE RECIBO</option>

                                    </select>
                                    
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="tipo_correspondencia">Correspondencia*</label>
                                    <select class="form-control" id="tipo_correspondencia" wire:model="tipo_correspondencia" wire:ignore>
                                        <option value="">Seleccione el tipo de correspondencia</option>
                                        <option value="CARTA">CARTA</option>
                                        <option value="ENCOMIENDA">ENCOMIENDA</option>
                                        <option value="DOCUMENTO">DOCUMENTO</option>
                                        <option value="PAQUETE">PAQUETE</option>
                                        <option value="SACA M">SACA M</option>
                                        <option value="REVISTA">REVISTA</option>
                                        <option value="IMPRESO">IMPRESO</option>
                                        <option value="CECOGRAMA">CECOGRAMA</option>
                                        <option value="PEQUEÑO PAQUETE">PEQUEÑO PAQUETE</option>

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
                                    <input type="number" class="form-control" id="cantidad" placeholder="Cantidad" wire:model="cantidad" value="1" disabled>
                                </div>
                                
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="destino">Destino*</label>
                                    <select class="form-control" id="destino" wire:model="destino" wire:ignore wire:change="updatePrice">
                                        <option value="">Seleccione el destino</option>
                                        <option value="NACIONAL">NACIONAL</option>
                                        <option value="CIUDADES INTERMEDIAS">CIUDADES INTERMEDIAS</option>
                                        <option value="TRINIDAD COBIJA">TRINIDAD COBIJA</option>
                                        <option value="RIVERALTA GUAYARAMERIN">RIVERALTA GUAYARAMERIN</option>
                                        <option value="EMS COBERTURA 1">EMS COBERTURA 1</option>
                                        <option value="EMS COBERTURA 2">EMS COBERTURA 2</option>
                                        <option value="EMS COBERTURA 3">EMS COBERTURA 3</option>
                                        <option value="EMS COBERTURA 4">EMS COBERTURA 4</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="codigo">Código*</label>
                                    <input type="text" class="form-control" id="codigo" wire:model="codigo">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="numero_factura">Número de Factura</label>
                                    <input type="text" class="form-control" id="numero_factura" wire:model="numero_factura">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="peso">Peso (Kg.)*</label>
                                    <input type="text" class="form-control" id="peso" wire:model="peso"
                                    placeholder="Ej: 1.001 o 1,001" autocomplete="off" spellcheck="false">
                             
                             


                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="precio">Precio*</label>
                                    <input type="text" class="form-control" id="precio" wire:model="precio" readonly>
                                </div>
                            </div>
                        </div>
    
                        <!-- Sección REMITENTE -->
                        <h5 class="mt-3" style="color: #003366;">REMITENTE</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group" style="position: relative;">
                                    <label for="nombre_remitente">Nombre Remitente*</label>
                                    <input type="text" class="form-control" id="nombre_remitente" wire:model="nombre_remitente" oninput="showSuggestions(this.value)" wire:ignore>
                                    <!-- Contenedor para las sugerencias -->
                                    <div id="suggestions" style="position: absolute; background-color: white; border: 1px solid #ccc; width: 100%; max-height: 150px; overflow-y: auto; z-index: 1000;"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="nombre_envia">Nombre y Apellido del que Envia</label>
                                    <input type="text" class="form-control" id="nombre_envia" wire:model="nombre_envia">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="carnet">Carnet*</label>
                                    <input type="text" class="form-control" id="carnet" wire:model="carnet">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="telefono_remitente">Teléfono Remitente*</label>
                                    <input type="text" class="form-control" id="telefono_remitente" wire:model="telefono_remitente">
                                </div>
                            </div>
                        </div>
    
                        <!-- Sección DESTINATARIO -->
                        <h5 class="mt-3" style="color: #003366;">DESTINATARIO</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="nombre_destinatario">Nombre Destinatario*</label>
                                    <input type="text" class="form-control" id="nombre_destinatario" wire:model="nombre_destinatario">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="telefono_destinatario">Teléfono Destinatario</label>
                                    <input type="text" class="form-control" id="telefono_destinatario" wire:model="telefono_destinatario">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12"> <!-- Cambiado a col-12 para ocupar todo el ancho -->
                                <div class="form-group">
                                    <label for="direccion">Dirección*</label>
                                    <input type="text" class="form-control" id="direccion" wire:model="direccion">
                                </div>
                            </div>
                        </div>

                            <div class="row">

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="ciudad">Ciudad*</label>
                                        <select class="form-control" id="ciudad" wire:model="ciudad">
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
                                    </div>
                                </div>
                                
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="provincia">Provincia</label>
                                    <input type="text" class="form-control" id="provincia" wire:model="provincia">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="pais">País*</label>
                                    <input type="text" class="form-control" id="pais" wire:model="pais">
                                </div>
                            </div>
                        </div>
    
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                            <button type="button" class="btn btn-secondary" onclick="saveFrequentSend()">Guardar como envío frecuente</button>

                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    
    

    <div wire:ignore.self class="modal fade" id="modalExpedicionHoy" tabindex="-1" role="dialog" aria-labelledby="modalExpedicionHoyLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalExpedicionHoyLabel">Confirmar Entrega a Expedición</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>¿Está seguro de que desea enviar los siguientes registros a expedición?</p>
                    <ul>
                        @foreach ($codigosHoy as $codigo)
                            <li>{{ $codigo }}</li>
                        @endforeach
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" wire:click="confirmarEntregarHoy">Confirmar</button>
                </div>
            </div>
        </div>
    </div>
    


    <!-- Modal para Editar -->
    <div wire:ignore.self class="modal fade" id="updateDespachoModal" tabindex="-1" role="dialog"
        aria-labelledby="updateModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Despacho</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                        onclick="closeUpdateModal()">
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
                                    <input type="text" class="form-control" id="origen" wire:model="origen" readonly>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="servicio">Tipo de Servicio*</label>
                                    <select class="form-control" id="servicio" wire:model="servicio" wire:ignore>
                                        <option value="">Seleccione el servicio</option>
                                        <option value="EMS">EMS</option>
                                    </select>
                                    
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="tipo_correspondencia">Correspondencia*</label>
                                    <select class="form-control" id="tipo_correspondencia" wire:model="tipo_correspondencia" wire:ignore>
                                        <option value="">Seleccione el tipo de correspondencia</option>
                                        <option value="CARTA">CARTA</option>
                                        <option value="SACA M">SACA M</option>
                                        <option value="DOCUMENTO">DOCUMENTO</option>
                                        <option value="PAQUETE">PAQUETE</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="cantidad">Cantidad*</label>
                                    <input type="number" class="form-control" id="cantidad" placeholder="Cantidad" wire:model="cantidad" value="1" disabled>
                                </div>
                                
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="destino">Destino*</label>
                                    <select class="form-control" id="destino" wire:model="destino" wire:ignore wire:change="updatePrice">
                                        <option value="">Seleccione el destino</option>
                                        <option value="NACIONAL">NACIONAL</option>
                                        <option value="CIUDADES INTERMEDIAS">CIUDADES INTERMEDIAS</option>
                                        <option value="TRINIDAD COBIJA">TRINIDAD COBIJA</option>
                                        <option value="RIVERALTA GUAYARAMERIN">RIVERALTA GUAYARAMERIN</option>
                                        <option value="EMS COBERTURA 1">EMS COBERTURA 1</option>
                                        <option value="EMS COBERTURA 2">EMS COBERTURA 2</option>
                                        <option value="EMS COBERTURA 3">EMS COBERTURA 3</option>
                                        <option value="EMS COBERTURA 4">EMS COBERTURA 4</option>
                                    </select>
                                </div>
                            </div>
                            {{-- <div class="col-md-4">
                                <div class="form-group">
                                    <label for="codigo">Código*</label>
                                    <input type="text" class="form-control" id="codigo" wire:model="codigo">
                                </div>
                            </div>
                        </div> --}}
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="numero_factura">Número de Factura</label>
                                    <input type="text" class="form-control" id="numero_factura" wire:model="numero_factura">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="peso">Peso (Kg.)*</label>
                                    <input type="text" class="form-control" id="peso" wire:model="peso"
                                    placeholder="Ej: 1.001 o 1,001" autocomplete="off" spellcheck="false">
                             
                             


                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="precio">Precio*</label>
                                    <input type="text" class="form-control" id="precio" wire:model="precio" readonly>
                                </div>
                            </div>
                        </div>
    
                        <!-- Sección REMITENTE -->
                        <h5 class="mt-3" style="color: #003366;">REMITENTE</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group" style="position: relative;">
                                    <label for="nombre_remitente">Nombre Remitente*</label>
                                    <input type="text" class="form-control" id="nombre_remitente" wire:model="nombre_remitente" oninput="showSuggestions(this.value)" wire:ignore>
                                    <!-- Contenedor para las sugerencias -->
                                    <div id="suggestions" style="position: absolute; background-color: white; border: 1px solid #ccc; width: 100%; max-height: 150px; overflow-y: auto; z-index: 1000;"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="nombre_envia">Nombre Envía*</label>
                                    <input type="text" class="form-control" id="nombre_envia" wire:model="nombre_envia">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="carnet">Carnet*</label>
                                    <input type="text" class="form-control" id="carnet" wire:model="carnet">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="telefono_remitente">Teléfono Remitente*</label>
                                    <input type="text" class="form-control" id="telefono_remitente" wire:model="telefono_remitente">
                                </div>
                            </div>
                        </div>
                    </div>


                        <!-- Sección DESTINATARIO -->
                        <h5 class="mt-3" style="color: #003366;">DESTINATARIO</h5>

                        <div class="row">

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="nombre_destinatario">Nombre Destinatario*</label>
                                    <input type="text" class="form-control" id="nombre_destinatario" wire:model="nombre_destinatario">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="telefono_destinatario">Teléfono Destinatario*</label>
                                    <input type="text" class="form-control" id="telefono_destinatario" wire:model="telefono_destinatario">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12"> <!-- Cambiado a col-12 para ocupar todo el ancho -->
                                <div class="form-group">
                                    <label for="direccion">Dirección*</label>
                                    <input type="text" class="form-control" id="direccion" wire:model="direccion">
                                </div>
                            </div>
                        </div>

                            <div class="row">

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="ciudad">Ciudad*</label>
                                    <input type="text" class="form-control" id="ciudad" wire:model="ciudad">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="provincia">Provincia</label>
                                    <input type="text" class="form-control" id="provincia" wire:model="provincia">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="pais">País*</label>
                                    <input type="text" class="form-control" id="pais" wire:model="pais">
                                </div>
                            </div>
                        </div>
    
                        
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" wire:click.prevent="update" class="btn btn-primary"
                        onclick="closeUpdateModal()">Actualizar</button>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Scripts para manejar los modales -->
<script>
    function openCreateModal() {
        $('#createDespachoModal').modal('show');
    }

    function closeCreateModal() {
        $('#createDespachoModal').modal('hide');
    }

    function openUpdateModal() {
        $('#updateDespachoModal').modal('show');
    }

    function closeUpdateModal() {
        $('#updateDespachoModal').modal('hide');
    }
    function saveFrequentSend() {
        let frequentSends = JSON.parse(localStorage.getItem('frequentSends')) || [];
        let data = {
            // DATOS
            servicio: document.getElementById('servicio').value,
            tipo_correspondencia: document.getElementById('tipo_correspondencia').value,
            peso: document.getElementById('peso').value,
            destino: document.getElementById('destino').value,
            codigo: document.getElementById('codigo').value,
            numero_factura: document.getElementById('numero_factura').value,

            // REMITENTE
            nombre_remitente: document.getElementById('nombre_remitente').value,
            nombre_envia: document.getElementById('nombre_envia').value,
            carnet: document.getElementById('carnet').value,
            telefono_remitente: document.getElementById('telefono_remitente').value,

            // DESTINATARIO
            nombre_destinatario: document.getElementById('nombre_destinatario').value,
            telefono_destinatario: document.getElementById('telefono_destinatario').value,
            direccion: document.getElementById('direccion').value,
            provincia: document.getElementById('provincia').value,
            ciudad: document.getElementById('ciudad').value,
            pais: document.getElementById('pais').value,
        };

        // Evitar duplicados basados en el nombre del remitente y código
        if (!frequentSends.some(send => send.nombre_remitente === data.nombre_remitente && send.codigo === data.codigo)) {
            frequentSends.push(data);
            localStorage.setItem('frequentSends', JSON.stringify(frequentSends));
            alert('Envío guardado como frecuente.');
        } else {
            alert('Este envío ya está guardado como frecuente.');
        }
    }
    function showSuggestions(value) {
        let frequentSends = JSON.parse(localStorage.getItem('frequentSends')) || [];
        let suggestionsDiv = document.getElementById('suggestions');
        suggestionsDiv.innerHTML = '';

        if (value.length === 0) {
            return;
        }

        let suggestions = frequentSends.filter(send => send.nombre_remitente.toLowerCase().includes(value.toLowerCase()));
        suggestions.forEach(send => {
            let div = document.createElement('div');
            div.innerHTML = send.nombre_remitente;
            div.style.padding = '5px';
            div.style.cursor = 'pointer';
            div.onclick = function() {
                selectSuggestion(send);
            };
            suggestionsDiv.appendChild(div);
        });
    }

    function selectSuggestion(send) {
        // DATOS
        document.getElementById('servicio').value = send.servicio; // Combo "Tipo de Servicio"
        document.getElementById('tipo_correspondencia').value = send.tipo_correspondencia; // Combo "Correspondencia"
        document.getElementById('peso').value = send.peso;
        document.getElementById('destino').value = send.destino; // Combo "Destino"
        document.getElementById('codigo').value = send.codigo;
        document.getElementById('numero_factura').value = send.numero_factura;

        // Disparar eventos de cambio para que Livewire detecte los cambios en los combos
        document.getElementById('servicio').dispatchEvent(new Event('change', { bubbles: true }));
        document.getElementById('tipo_correspondencia').dispatchEvent(new Event('change', { bubbles: true }));
        document.getElementById('destino').dispatchEvent(new Event('change', { bubbles: true }));

        // REMITENTE
        document.getElementById('nombre_remitente').value = send.nombre_remitente;
        document.getElementById('nombre_envia').value = send.nombre_envia;
        document.getElementById('carnet').value = send.carnet;
        document.getElementById('telefono_remitente').value = send.telefono_remitente;

        // DESTINATARIO
        document.getElementById('nombre_destinatario').value = send.nombre_destinatario;
        document.getElementById('telefono_destinatario').value = send.telefono_destinatario;
        document.getElementById('direccion').value = send.direccion;
        document.getElementById('provincia').value = send.provincia;
        document.getElementById('ciudad').value = send.ciudad;
        document.getElementById('pais').value = send.pais;

        // Disparar eventos de entrada para que Livewire detecte los cambios en otros campos
        let fields = [
            'peso', 'codigo', 'numero_factura', 'nombre_remitente',
            'nombre_envia', 'carnet', 'telefono_remitente', 'nombre_destinatario',
            'telefono_destinatario', 'direccion','provincia', 'ciudad', 'pais'
        ];

        fields.forEach(function(field) {
            triggerInputEvent(field);
        });

        // Limpiar las sugerencias
        document.getElementById('suggestions').innerHTML = '';

        // Actualizar el precio si es necesario
        @this.call('updatePrice');
    }

    function triggerInputEvent(elementId) {
        var element = document.getElementById(elementId);
        var event = new Event('input', { bubbles: true });
        element.dispatchEvent(event);
    }

    function triggerInputEvent(elementId) {
        var element = document.getElementById(elementId);
        var event = new Event('input', { bubbles: true });
        element.dispatchEvent(event);
    }

    // Ocultar sugerencias cuando se hace clic fuera
    document.addEventListener('livewire:load', function () {
    window.addEventListener('pdf-downloaded', () => {
        location.reload(); // Recarga la página
    });
});

 // Escuchar eventos de Livewire para abrir o cerrar el modal
 document.addEventListener('livewire:load', function () {
        window.addEventListener('open-edit-modal', function () {
            openUpdateModal();
        });

        window.addEventListener('close-edit-modal', function () {
            closeUpdateModal();
        });

        window.addEventListener('pdf-downloaded', () => {
            location.reload(); // Recarga la página
        });
    });
    document.addEventListener('livewire:load', function () {
        Livewire.on('toggleSelectAll', (selectAll, currentPageIds) => {
            // Marca o desmarca los checkboxes según el estado de selectAll
            currentPageIds.forEach(id => {
                const checkbox = document.querySelector(`input[value="${id}"]`);
                if (checkbox) {
                    checkbox.checked = selectAll;
                }
            });
        });
    });
    document.addEventListener('DOMContentLoaded', function () {
        window.addEventListener('mostrar-modal-expedicion-hoy', function () {
            $('#modalExpedicionHoy').modal('show'); // Usa Bootstrap para mostrar el modal
        });

        window.addEventListener('ocultar-modal-expedicion-hoy', function () {
            $('#modalExpedicionHoy').modal('hide'); // Usa Bootstrap para ocultar el modal
        });
    });
</script>

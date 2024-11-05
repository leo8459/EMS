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
                                <input type="text" wire:model="searchTerm" placeholder="Buscar..."
                                    class="form-control" style="margin-right: 10px;">
                                <button type="button" class="btn btn-primary" wire:click="$refresh">Buscar</button>
                                <button type="button" class="btn btn-success" data-toggle="modal"
                                    data-target="#createPaqueteModal">
                                    Nuevo Paquete
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
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Origen</th>
                                        <th>Fecha</th>
                                        <th>Servicio</th>
                                        <th>Tipo Correspondencia</th>
                                        <th>Cantidad</th>
                                        <th>Peso</th>
                                        <th>Destino</th>
                                        <th>Código</th>
                                        <th>Precio</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($admisiones as $admisione)
                                        <tr>
                                            <td>{{ $admisione->origen }}</td>
                                            <td>{{ $admisione->fecha }}</td>
                                            <td>{{ $admisione->servicio }}</td>
                                            <td>{{ $admisione->tipo_correspondencia }}</td>
                                            <td>{{ $admisione->cantidad }}</td>
                                            <td>{{ $admisione->peso }}</td>
                                            <td>{{ $admisione->destino }}</td>
                                            <td>{{ $admisione->codigo }}</td>
                                            <td>{{ $admisione->precio }}</td>
                                            <td>
                                                <button type="button" class="btn btn-info"
                                                    wire:click="edit({{ $admisione->id }})">Editar</button>
                                                <button type="button" class="btn btn-danger"
                                                    wire:click="delete({{ $admisione->id }})">Eliminar</button>
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
                    <h5 class="modal-title" id="createPaqueteModalLabel">Crear Nuevo Paquete</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="store">
    
                        <!-- Sección DATOS -->
                        <h5 class="text-primary mt-3">DATOS</h5>
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
                                    <select class="form-control" id="servicio" wire:model="servicio">
                                        <option value="">Seleccione el servicio</option>
                                        <option value="EMS">EMS</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="tipo_correspondencia">Correspondencia*</label>
                                    <select class="form-control" id="tipo_correspondencia" wire:model="tipo_correspondencia">
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
                                    <input type="number" class="form-control" id="cantidad" placeholder="Cantidad" wire:model="1" value="1" disabled>
                                </div>
                                
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="destino">Destino*</label>
                                    <select class="form-control" id="destino" wire:model="destino" wire:change="updatePrice">
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
                        <h5 class="text-primary mt-4">REMITENTE</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="nombre_remitente">Nombre Remitente*</label>
                                    <input type="text" class="form-control" id="nombre_remitente" wire:model="nombre_remitente">
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
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="telefono_remitente">Teléfono Remitente*</label>
                                    <input type="text" class="form-control" id="telefono_remitente" wire:model="telefono_remitente">
                                </div>
                            </div>
                        </div>
    
                        <!-- Sección DESTINATARIO -->
                        <h5 class="text-primary mt-4">DESTINATARIO</h5>
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
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="direccion">Dirección*</label>
                                    <input type="text" class="form-control" id="direccion" wire:model="direccion">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="ciudad">Ciudad*</label>
                                    <input type="text" class="form-control" id="ciudad" wire:model="ciudad">
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
                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </div>
                    </form>
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
                    <form>
                        <!-- Repite los mismos campos que el modal de creación -->
                        <!-- Usa los mismos campos que en el Modal de Crear -->
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
</script>

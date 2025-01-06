<div class="container-fluid">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Envios Encamino <i class="el el-minus-sign"></i></h1>
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
                                <!-- Botón para redirigir a la ruta asignarcartero -->
                                <div class="container-fluid">
                                    <div class="d-flex justify-content-end mt-3">
                                        <a href="{{ route('asignarcartero') }}" class="btn btn-success">
                                            Asignar Carteros
                                        </a>
                                    </div>
                                </div>
                            </div>


                                                                <!--REPORTES -->

                            <div class="container-fluid">
                                <div class="row mb-3">
                                    <!-- Dropdown para seleccionar el servicio -->
                                    <div class="col-md-4">
                                        <label for="servicioSeleccionado">Seleccionar Servicio:</label>
                                        <select id="servicioSeleccionado" wire:model="servicioSeleccionado" class="form-control">
                                            <option value="">-- Seleccionar --</option>
                                            <option value="EMS">EMS</option>
                                            <option value="CERTIFICADA">CERTIFICADA</option>
                                            <option value="ORDINARIA">ORDINARIA</option>
                                            <option value="ECA/PLIEGOS">ECA/PLIEGOS</option>
                                            <option value="SUPEREXPRESS">SUPEREXPRESS</option>
                                            <option value="EXPRESO">EXPRESO</option>
                                            <option value="AVISO DE RECIBO">AVISO DE RECIBO</option>
                                        </select>
                                    </div>
                            
                                    <!-- Campo para la fecha de inicio -->
                                    <div class="col-md-4">
                                        <label for="fechaInicio">Fecha Inicio:</label>
                                        <input type="date" id="fechaInicio" wire:model="fechaInicio" class="form-control">
                                    </div>
                            
                                    <!-- Campo para la fecha de fin -->
                                    <div class="col-md-4">
                                        <label for="fechaFin">Fecha Fin:</label>
                                        <input type="date" id="fechaFin" wire:model="fechaFin" class="form-control">
                                    </div>
                                </div>
                            
                                <!-- Botón para generar el reporte -->
                                <div class="d-flex justify-content-end mt-3">
                                    <button wire:click="generarReporteDesdeTabla" class="btn btn-info">
                                        Generar Reporte de lo Filtrado
                                    </button>
                                </div>
                                
                                <!-- Mensajes de éxito o error -->
                                @if (session()->has('error'))
                                    <div class="alert alert-danger mt-3">
                                        {{ session('error') }}
                                    </div>
                                @endif
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
                                        <th>Observacion</th>
                                        <th>Cartero</th>

                                        <th>Acciones</th>
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
                                                $rowClass = 'table-success'; // Verde: menos de 24 horas
                                            } elseif ($diffInHours <= 48) {
                                                $rowClass = 'table-warning'; // Amarillo: entre 24 y 48 horas
                                            } else {
                                                $rowClass = 'table-danger'; // Rojo: más de 48 horas
                                            }
                                        @endphp
                                        <tr class="{{ $rowClass }}">
                                            <td>
                                                <input type="checkbox" wire:model="selectedAdmisiones" value="{{ $admisione->id }}" />
                                            </td>
                                            <td>{{ $loop->iteration }}</td> <!-- Mostrar número de fila -->
                                            <td>{{ $admisione->origen }}</td>
                                            <td>{{ $admisione->servicio }}</td>
                                            <td>{{ $admisione->tipo_correspondencia }}</td>
                                            <td>{{ $admisione->cantidad }}</td>
                                            <td>{{ $admisione->peso_ems }}</td>
                                            <td>{{ $admisione->precio }}</td>
                                            <td>{{ $admisione->destino }}</td>
                                            <td>{{ $admisione->codigo }}</td>
                                            <td>{{ $admisione->direccion }}</td>
                                            <td>{{ $admisione->provincia }}</td>
                                            <td>{{ $admisione->ciudad }}</td>
                                            <td>{{ $admisione->pais }}</td>
                                            <td>{{ $admisione->fecha }}</td>
                                            <td>{{ $admisione->observacion }}</td>
                                            <td>{{ $admisione->user ? $admisione->user->name : 'No asignado' }}</td>
                                            <td>
                                                <a href="{{ route('entregarenviosfirma', ['id' => $admisione->id]) }}" class="btn btn-primary">
                                                    Entregar Admision
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                
                            </table>
                            <!-- Modal para subir foto y añadir recepcionado y observacion_entrega -->
                            @if ($showModal)
                                <div class="modal fade show" style="display: block;" tabindex="-1" role="dialog">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <!-- Encabezado del modal -->
                                            <div class="modal-header">
                                                <h5 class="modal-title">Subir Foto para Admision
                                                    {{ $selectedAdmision->codigo ?? '' }}</h5>
                                                <button type="button" class="close"
                                                    wire:click="$set('showModal', false)">
                                                    <span>&times;</span>
                                                </button>
                                            </div>
                                            <!-- Cuerpo del modal -->
                                            <div class="modal-body">
                                                <form wire:submit.prevent="save">
                                                    <!-- Campo de Foto -->
                                                    <div class="form-group">
                                                        <label for="photo">Seleccionar Foto</label>
                                                        <input type="file" id="photo" wire:model="photo"
                                                            class="form-control">
                                                        @error('photo')
                                                            <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                    @if ($photo)
                                                        <img src="{{ $photo->temporaryUrl() }}" width="200"
                                                            class="mb-3">
                                                    @endif

                                                    <!-- Campo de Recepcionado -->
                                                    <div class="form-group">
                                                        <label for="recepcionado">Recepcionado Por</label>
                                                        <input type="text" id="recepcionado"
                                                            wire:model="recepcionado" class="form-control">
                                                        @error('recepcionado')
                                                            <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>

                                                    <!-- Campo de Observación de Entrega -->
                                                    <div class="form-group">
                                                        <label for="observacion_entrega">Observación de Entrega</label>
                                                        <textarea id="observacion_entrega" wire:model="observacion_entrega" class="form-control"></textarea>
                                                        @error('observacion_entrega')
                                                            <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </form>
                                            </div>
                                            <!-- Pie del modal -->
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    wire:click="$set('showModal', false)">Cancelar</button>
                                                <button type="button" class="btn btn-primary"
                                                    wire:click="save">Guardar</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Backdrop del modal -->
                                <div class="modal-backdrop fade show"></div>
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

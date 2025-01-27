<div class="container-fluid">
    <style>
        /* Ajustes personalizados para pantallas medianas */
        @media (max-width: 768px) {
            /* Reorganizar la barra de búsqueda y el botón de asignar en la cabecera */
            .card-header .d-flex.align-items-center {
                flex-wrap: wrap;
            }
            /* El input y el botón se acomodan uno debajo del otro */
            .card-header .d-flex.align-items-center .form-control {
                margin-right: 0;
                margin-bottom: 10px;
                width: 100%;
            }
            .card-header .d-flex.align-items-center .btn.btn-primary {
                width: 100%;
            }
            .d-flex.justify-content-end.mt-3 a.btn.btn-success {
                width: 100%;
                margin-top: 10px;
            }
        }
        
        /* Ajustes para pantallas muy pequeñas (ej. teléfonos) */
        @media (max-width: 576px) {
            /* Controlar un poco más el tamaño del texto en tabla si se quiere */
            table.table-striped.table-hover tbody tr td,
            table.table-striped.table-hover thead tr th {
                font-size: 0.9rem;
            }
        }
    </style>

    <!-- Encabezado / Breadcrumb -->
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

    <!-- Contenido principal -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- Col principal -->
                <div class="col-12">
                    <div class="card">
                        <!-- Cabecera de la tarjeta -->
                        <div class="card-header">
                            <div class="d-flex align-items-center">
                                <div class="float-left d-flex align-items-center">
                                    <input type="text"
                                           wire:model="searchTerm"
                                           placeholder="Buscar..."
                                           class="form-control"
                                           style="margin-right: 10px;"
                                           wire:keydown.enter="$refresh">
                                    <button type="button"
                                            class="btn btn-primary"
                                            wire:click="$refresh">
                                        Buscar
                                    </button>
                                </div>

                                <!-- Botón para redirigir a la ruta asignarsecartero -->
                                <div class="container-fluid">
                                    <div class="d-flex justify-content-end mt-3">
                                        <a href="{{ route('asignarsecartero') }}"
                                           class="btn btn-success">
                                            Asignar Carteros
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Alertas de sesión -->
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

                        <!-- Cuerpo de la tarjeta -->
                        <div class="card-body">
                            <!-- Tabla responsiva -->
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>
                                                <input type="checkbox" wire:model="selectAll" />
                                            </th>
                                            <th>#</th>
                                            <th>Origen</th>
                                            <th>Servicio</th>
                                            <th>Tipo Correspondencia</th>
                                            <!-- Ocultar en pantallas pequeñas con d-none d-lg-table-cell -->
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
                                            <th>Observación</th>
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
                                                    <input type="checkbox"
                                                           wire:model="selectedAdmisiones"
                                                           value="{{ $admisione->id }}" />
                                                </td>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $admisione->origen }}</td>
                                                <td>{{ $admisione->servicio }}</td>
                                                <td>{{ $admisione->tipo_correspondencia }}</td>
                                                <td class="d-none d-lg-table-cell">{{ $admisione->cantidad }}</td>
                                                <td>{{ $admisione->peso_ems }}</td>
                                                <td>{{ $admisione->precio }}</td>
                                                <td>{{ $admisione->destino }}</td>
                                                <td>{{ $admisione->codigo }}</td>
                                                <td class="d-none d-lg-table-cell">{{ $admisione->direccion }}</td>
                                                <td class="d-none d-lg-table-cell">{{ $admisione->provincia }}</td>
                                                <td class="d-none d-lg-table-cell">{{ $admisione->ciudad }}</td>
                                                <td class="d-none d-lg-table-cell">{{ $admisione->pais }}</td>
                                                <td>{{ $admisione->fecha }}</td>
                                                <td>{{ $admisione->observacion_entrega ? $admisione->observacion_entrega : '' }}</td>
                                                <td>{{ $admisione->user ? $admisione->user->name : 'No asignado' }}</td>
                                                <td>
                                                    <strong>{{ $statusText }}</strong>
                                                </td>
                                                <td>
                                                    <a href="{{ route('entregarenviosfirma', ['id' => $admisione->id]) }}"
                                                       class="btn btn-primary">
                                                        Entregar Admision
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Modal (si lo estás usando con Livewire) -->
                            @if ($showModal)
                                <div class="modal fade show" style="display: block;" tabindex="-1" role="dialog">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <!-- Encabezado del modal -->
                                            <div class="modal-header">
                                                <h5 class="modal-title">
                                                    Subir Foto para Admision {{ $selectedAdmision->codigo ?? '' }}
                                                </h5>
                                                <button type="button" class="close"
                                                        wire:click="$set('showModal', false)">
                                                    <span>&times;</span>
                                                </button>
                                            </div>
                                            <!-- Cuerpo del modal -->
                                            <div class="modal-body">
                                                <form wire:submit.prevent="save">
                                                    <!-- Foto -->
                                                    <div class="form-group">
                                                        <label for="photo">Seleccionar Foto</label>
                                                        <input type="file" id="photo" wire:model="photo" class="form-control">
                                                        @error('photo')
                                                            <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                    @if ($photo)
                                                        <img src="{{ $photo->temporaryUrl() }}" width="200" class="mb-3">
                                                    @endif

                                                    <!-- Recepcionado -->
                                                    <div class="form-group">
                                                        <label for="recepcionado">Recepcionado Por</label>
                                                        <input type="text" id="recepcionado" wire:model="recepcionado" class="form-control">
                                                        @error('recepcionado')
                                                            <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>

                                                    <!-- Observación de Entrega -->
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
                                                        wire:click="$set('showModal', false)">
                                                    Cancelar
                                                </button>
                                                <button type="button" class="btn btn-primary"
                                                        wire:click="save">
                                                    Guardar
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Backdrop del modal -->
                                <div class="modal-backdrop fade show"></div>
                            @endif
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
</div>

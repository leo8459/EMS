<div class="container-fluid">
    <style>
        /* Ajustes personalizados para pantallas pequeñas */
        @media (max-width: 768px) {
            /* Ajustar márgenes y espaciamientos en la cabecera de la tarjeta */
            .card-header .d-flex.align-items-center {
                flex-wrap: wrap;
            }

            /* El input de búsqueda y el botón se apilan verticalmente en pantallas pequeñas */
            .card-header .d-flex.align-items-center .form-control {
                margin-right: 0;
                margin-bottom: 10px;
                width: 100%;
            }

            /* Ajustar botón de búsqueda al 100% en pantallas muy pequeñas */
            .card-header .d-flex.align-items-center button {
                width: 100%;
            }

            /* Ajustar botón "Asignar Carteros" a ocupar todo el ancho en pantallas pequeñas */
            .d-flex.justify-content-end.mt-3 a.btn.btn-success {
                width: 100%;
                margin-top: 10px;
            }
        }

        /* Ajustes adicionales para pantallas más pequeñas (teléfonos) */
        @media (max-width: 576px) {
            /* Disminuir el tamaño de texto de la tabla si se desea */
            table.table-striped.table-hover tbody tr td,
            table.table-striped.table-hover thead tr th {
                font-size: 0.85rem;
            }

            /* Controlar la altura y el overflow para que no se descuadre */
            .table-responsive {
                overflow-x: auto;
            }
        }
    </style>

    <!-- Encabezado/ Breadcrumb -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Envios Entregados <i class="el el-minus-sign"></i></h1>
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
                <!-- Columna principal -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <!-- Barra de búsqueda -->
                            <div class="d-flex align-items-center">
                                <div class="float-left d-flex align-items-center">
                                    <input type="text" wire:model="searchTerm" placeholder="Buscar..." class="form-control" style="margin-right: 10px;" wire:keydown.enter="$refresh">
                                    <button type="button" class="btn btn-primary" wire:click="$refresh">Buscar</button>
                                </div>
                                <!-- Botón Asignar Carteros -->
                                <div class="container-fluid">
                                    <div class="d-flex justify-content-end mt-3">
                                        <a href="{{ route('asignarcartero') }}" class="btn btn-success">Asignar Envios</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Mensajes de sesión (alertas) -->
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
                            <!-- Tabla responsiva -->
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th><input type="checkbox" wire:model="selectAll" /></th>
                                            <th>#</th>
                                            <th>Origen</th>
                                            <th>Servicio</th>
                                            <th>Tipo Correspondencia</th>
                                            <!-- Columnas con clase d-none d-lg-table-cell 
                                                 se ocultan en pantallas < lg -->
                                            <th class="d-none d-lg-table-cell">Cantidad</th>
                                            <th>Peso</th>
                                            <th>Precio (Bs)</th>
                                            <th>Destino</th>
                                            <th>Código</th>
                                            <th class="d-none d-lg-table-cell">Dirección</th>
                                            <th class="d-none d-lg-table-cell">Provincia</th>
                                            <th class="d-none d-lg-table-cell">Ciudad</th>
                                            <th class="d-none d-lg-table-cell">País</th>
                                            <th>Entregado</th>
                                            <th>Observación</th>
                                            <th>Cartero</th>
                                            <th>Foto</th>
                                            <th>Firma</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($admisiones as $admisione)
                                            <tr>
                                                <td><input type="checkbox" wire:model="selectedAdmisiones" value="{{ $admisione->id }}" /></td>
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
                                                <td>{{ $admisione->updated_at }}</td>
                                                <td>{{ $admisione->observacion }}</td>
                                                <td>{{ $admisione->user ? $admisione->user->name : 'No asignado' }}</td>
                                                <td>
                                                    @if ($admisione->photo)
                                                        <div style="width: 100px; height: 100px; overflow: hidden; display: flex; justify-content: center; align-items: center; border: 1px solid #ddd; border-radius: 5px; background-color: #f9f9f9;">
                                                            <!-- Mostrando la imagen directamente con su contenido base64 -->
                                                            <img src="{{ $admisione->photo }}"
                                                                 alt="Foto de admisión"
                                                                 style="max-width: 100%; max-height: 100%; object-fit: cover; border-radius: 5px;">
                                                        </div>
                                                
                                                        <!-- Opcional: intento de descarga. Funciona en la mayoría de navegadores modernos -->
                                                        <a href="{{ $admisione->photo }}"
                                                           download="foto-admision-{{ $admisione->id }}.png"
                                                           class="btn btn-sm btn-secondary mt-1">
                                                           Descargar
                                                        </a>
                                                    @else
                                                        <span>Sin foto</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($admisione->firma_entrega)
                                                        <div style="width: 100px; height: 100px; overflow: hidden; display: flex; justify-content: center; align-items: center; border: 1px solid #ddd; border-radius: 5px; background-color: #f9f9f9;">
                                                            <img src="{{ $admisione->firma_entrega }}" alt="Firma de entrega" style="max-width: 100%; max-height: 100%; object-fit: cover; border-radius: 5px;" class="mb-2">
                                                        </div>
                                                    @else
                                                        <span>Sin firma</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
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

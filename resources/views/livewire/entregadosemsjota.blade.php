<div class="container-fluid">
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

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between">
                                <!-- Filtro de búsqueda -->
                                <div>
                                    <input type="text" wire:model="searchTerm" placeholder="Buscar..."
                                        class="form-control" style="margin-right: 10px;" wire:keydown.enter="$refresh">
                                    <button type="button" class="btn btn-primary mt-2"
                                        wire:click="$refresh">Buscar</button>
                                </div>

                                <!-- Filtros por fechas -->
                                <div class="d-flex">
                                    <div class="mr-2">
                                        <label for="startDate">Fecha Inicio:</label>
                                        <input type="date" wire:model="startDate" id="startDate"
                                            class="form-control">
                                    </div>
                                    <div>
                                        <label for="endDate">Fecha Fin:</label>
                                        <input type="date" wire:model="endDate" id="endDate" class="form-control">
                                    </div>
                                </div>

                                <!-- Filtro por Cartero -->
                                <div>
                                    <label for="selectedCartero">Filtrar por Cartero:</label>
                                    <select wire:model="selectedCartero" id="selectedCartero" class="form-control">
                                        <option value="">Todos</option>
                                        @foreach ($carteros as $cartero)
                                            <option value="{{ $cartero->id }}">{{ $cartero->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <!-- Filtro por Departamento -->
                                <div>
                                    <label for="department">Filtrar por Departamento:</label>
                                    <select wire:model="department" id="department" class="form-control">
                                        <option value="">Todos</option>
                                        <!-- Opción para mostrar todos los departamentos -->
                                        <option value="LA PAZ">LA PAZ</option>
                                        <option value="COCHABAMBA">COCHABAMBA</option>
                                        <option value="SANTA CRUZ">SANTA CRUZ</option>
                                        <option value="ORURO">ORURO</option>
                                        <option value="POTOSI">POTOSI</option>
                                        <option value="CHUQUISACA">CHUQUISACA</option>
                                        <option value="BENI">BENI</option>
                                        <option value="PANDO">PANDO</option>
                                        <option value="TARIJA">TARIJA</option>
                                    </select>
                                </div>


                                <!-- Botón para exportar PDF -->
                                <div>
                                    <button wire:click="exportToPDF" class="btn btn-danger mt-4">
                                        <i class="fas fa-file-pdf"></i>Entregados Carteros
                                    </button>
                                    <button wire:click="exportNewReportToPDF" class="btn btn-success mt-4">
                                        <i class="fas fa-file-pdf"></i> Entregados/PorEntregar
                                    </button>
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
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th><input type="checkbox" wire:model="selectAll" /></th>
                                            <th>#</th>
                                            <th>Origen</th>
                                            <th>Servicio</th>
                                            <th>Tipo Correspondencia</th>
                                            <th>Cantidad</th>
                                            <th>Peso (EMS)</th>
                                            <th>Precio (Bs)</th>
                                            <th>Destino</th>
                                            <th>Código</th>
                                            <th>Dirección</th>
                                            <th>Provincia</th>
                                            <th>Ciudad</th>
                                            <th>País</th>
                                            <th>Entregado</th>
                                            <th>Observación</th>
                                            <th>Recepcionado</th>
                                            <th>Cartero</th>
                                            <th>Foto</th>
                                            <th>Firma</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($admisiones as $admisione)
                                            <tr>
                                                <td><input type="checkbox" wire:model="selectedAdmisiones"
                                                        value="{{ $admisione->id }}" /></td>
                                                <td>{{ $loop->iteration }}</td>
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
                                                <td>{{ $admisione->updated_at->format('d/m/Y H:i') }}</td>
                                                <td>{{ $admisione->observacion }}</td>
                                                <td>{{ $admisione->recepcionado }}</td>
                                                <td>{{ $admisione->user ? $admisione->user->name : 'No asignado' }}
                                                </td>
                                                <td>
                                                    @php
                                                    $extensions = ['jpg', 'jpeg', 'png', 'gif'];
                                                    $photoPath = null;
                                                
                                                    foreach ($extensions as $ext) {
                                                        // Esta es la ruta "web" (URL) para tu archivo:
                                                        //  /storage/fotos/ELCODIGO.EXT
                                                        $path = 'storage/fotos/' . $admisione->codigo . '.' . $ext;
                                                
                                                        // Físicamente el archivo está en: storage/app/public/fotos/...
                                                        // Para validar su existencia, hacemos:
                                                        $fullPathOnDisk = storage_path('app/public/fotos/' . $admisione->codigo . '.' . $ext);
                                                
                                                        if (file_exists($fullPathOnDisk)) {
                                                            $photoPath = $path;
                                                            break;
                                                        }
                                                    }
                                                @endphp
                                                
                                                    @if ($photoPath)
                                                        <div style="width: 100px; height: 100px; overflow: hidden; display: flex; justify-content: center; align-items: center; border: 1px solid #ddd; border-radius: 5px; background-color: #f9f9f9;">
                                                            <img src="{{ asset($photoPath) }}" alt="Foto de admisión"
                                                                 style="max-width: 100%; max-height: 100%; object-fit: cover; border-radius: 5px;">
                                                        </div>
                                                        <a href="{{ asset($photoPath) }}" download class="btn btn-sm btn-secondary">Descargar</a>
                                                    @else
                                                        <span>Sin foto</span>
                                                    @endif
                                                </td>
                                                
                                                <td>
                                                    @if ($admisione->firma_entrega)
                                                        <div
                                                            style="width: 100px; height: 100px; overflow: hidden; display: flex; justify-content: center; align-items: center; border: 1px solid #ddd; border-radius: 5px; background-color: #f9f9f9;">
                                                            <img src="{{ $admisione->firma_entrega }}"
                                                                alt="Firma de entrega"
                                                                style="max-width: 100%; max-height: 100%; object-fit: cover; border-radius: 5px;">
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
                        <div class="card-footer">
                            {{ $admisiones->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

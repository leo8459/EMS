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
                            <div class="d-flex align-items-center">
                                
                                <div class="float-left d-flex align-items-center">
                                    <input type="text" wire:model="searchTerm" placeholder="Buscar..." class="form-control" style="margin-right: 10px;" wire:keydown.enter="$refresh">

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
                                        <th>Entregado</th>
                                        <th>Observacion</th>
                                        <th>Cartero</th>
                                        <th>foto</th>
                                        {{-- <th>descargar</th> --}}

                                        {{-- <th>Acciones</th> --}}
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
                                            <td>{{ $admisione->peso_ems }}</td>
                                            <td>{{ $admisione->precio }}</td>
                                            <td>{{ $admisione->destino }}</td>
                                            <td>{{ $admisione->codigo }}</td>
                                           
                                            <td>{{ $admisione->direccion }}</td>
                                            <td>{{ $admisione->provincia }}</td>
                                            <td>{{ $admisione->ciudad }}</td>
                                            <td>{{ $admisione->pais }}</td>
                                            <td>{{ $admisione->updated_at }}</td>
                                            <td>{{ $admisione->observacion }}</td>
                                            <td>{{ $admisione->user ? $admisione->user->name : 'No asignado' }}</td>
 <!-- Añadimos la columna para mostrar la foto si existe -->
  <!-- Columna para la foto y el botón de descarga -->
  <td>
    @php
        $extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $photoPath = null;
        foreach ($extensions as $ext) {
            $path = 'fotos/' . $admisione->codigo . '.' . $ext;
            if (file_exists(public_path($path))) {
                $photoPath = $path;
                break;
            }
        }
    @endphp

    @if ($photoPath)
        <img src="{{ asset($photoPath) }}" alt="Foto de admisión" width="100" class="mb-2">
        <a href="{{ asset($photoPath) }}" download class="btn btn-sm btn-secondary">Descargar</a>
    @else
        Sin foto
    @endif
</td>
                                            <td>
                                                {{-- <button type="button" class="btn btn-info" wire:click="edit({{ $admisione->id }})">Editar</button> --}}
                                                {{-- <button type="button" class="btn btn-warning" wire:click="devolverAdmision({{ $admisione->id }})">Devolver a admisión</button> --}}

                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
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

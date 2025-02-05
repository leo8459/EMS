<div class="container-fluid">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Recibir Admision AGBC</h1>
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
                                    <input type="text" wire:model="searchTerm" placeholder="Buscar código..." class="form-control" style="margin-right: 10px;" wire:keydown.enter="$refresh">

                                    <button type="button" class="btn btn-primary" wire:click="$refresh">Buscar</button>
                                </div>

                                <!-- Nuevo campo para filtrar por usuario -->
                                <div class="ml-2">
                                    <select wire:model="searchUserId" class="form-control">
                                        <option value="">Todos los usuarios</option>
                                        @foreach ($usuarios as $usuario)
                                            <option value="{{ $usuario->id }}">{{ $usuario->name }}</option>
                                        @endforeach
                                    </select>
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
                                        <th>#</th>
                                        <th>Accion</th>
                                        <th>Descripcion</th>
                                        <th>Codigo</th>
                                        <th>Fecha</th>
                                        <th>Usuario</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($admisiones as $admisione)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $admisione->accion }}</td>
                                            <td>{{ $admisione->descripcion }}</td>
                                            <td>{{ $admisione->codigo }}</td>
                                            <td>{{ $admisione->created_at->format('d/m/Y H:i') }}</td>
                                            <td>{{ $admisione->user->name ?? 'No asignado' }}</td>
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

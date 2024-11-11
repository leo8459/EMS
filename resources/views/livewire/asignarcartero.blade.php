<div class="container-fluid">
    <div class="row">
        <!-- Columna izquierda: Lista de admisiones en estado 3 -->
        <div class="col-md-6">
            <h4>Asignar Envios</h4>
            <input type="text" wire:model="searchTerm" placeholder="Buscar admisión..." class="form-control mb-3" wire:keydown.enter="searchAdmision">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Destino</th>
                        <th>Dirección</th>
                        <th>Asignar</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($admisiones as $admision)
                        <tr>
                            <td>{{ $admision->codigo }}</td>
                            <td>{{ $admision->destino }}</td>
                            <td>{{ $admision->direccion }}</td>
                            <td>
                                <button class="btn btn-primary btn-sm" wire:click="selectAdmision({{ $admision->id }})">Asignar</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $admisiones->links() }}
        </div>

        <!-- Columna derecha: Admisiones seleccionadas y asignación de cartero -->
        <div class="col-md-6">
            <h4>Admisiones Seleccionadas para Asignación</h4>
            <!-- Selección de cartero para todas las admisiones -->
            <div class="mb-3">
                <label for="selectCarteroForAll">Asignar Cartero a Todas las Admisiones</label>
                <select id="selectCarteroForAll" wire:model="selectedCarteroForAll" wire:change="assignCarteroToAll" class="form-control">
                    <option value="">Seleccione un cartero</option>
                    @foreach ($carteros as $cartero)
                        <option value="{{ $cartero->id }}">{{ $cartero->name }}</option>
                    @endforeach
                </select>
            </div>

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Destino</th>
                        <th>Dirección</th>
                        <th>Asignar Cartero</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($assignedAdmisiones as $index => $assignment)
                        <tr>
                            <td>{{ $assignment['codigo'] }}</td>
                            <td>{{ $assignment['destino'] }}</td>
                            <td>{{ $assignment['direccion'] }}</td>
                            <td>
                                <!-- Selección individual del cartero, sincronizado con el valor global -->
                                <select wire:model="assignedAdmisiones.{{ $index }}.user_id" class="form-control">
                                    <option value="">Seleccione un cartero</option>
                                    @foreach ($carteros as $cartero)
                                        <option value="{{ $cartero->id }}">{{ $cartero->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <button class="btn btn-warning btn-sm" wire:click="returnToLeftList({{ $index }})">Devolver a la lista</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Botón para guardar las asignaciones -->
            <button class="btn btn-success" wire:click="saveAssignments">Guardar Asignaciones</button>

            <!-- Mensaje de éxito -->
            @if (session()->has('message'))
                <div class="alert alert-success mt-3">
                    {{ session('message') }}
                </div>
            @endif
        </div>
    </div>
</div>

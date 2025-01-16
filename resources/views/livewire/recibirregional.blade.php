<div class="container-fluid">
    <section class="content-header">
        <h1>Inventario</h1>
    </section>

    <section class="content">
        <div class="card">
            <!-- HEADER -->
            <div class="card-header">
                <!-- Input de búsqueda -->
                <input type="text" wire:model.defer="searchTerm" wire:keydown.enter="buscar" placeholder="Buscar..."
                    class="form-control d-inline-block w-50" />
                <!-- Botón para buscar -->
                <button type="button" class="btn btn-primary" wire:click="buscar">
                    Buscar
                </button>
            </div>

            <!-- FOOTER -->
            <div class="card-footer">
                <button class="btn btn-success" wire:click="openModal">
                    Recibir Envíos
                </button>
            </div>
            <div class="card-header">
                <div class="d-flex align-items-center">
                    <div class="float-left d-flex align-items-center">
                        <input type="date" wire:model="startDate" class="form-control" style="margin-right: 10px;">
                        <input type="date" wire:model="endDate" class="form-control" style="margin-right: 10px;">

                        <!-- Filtro por departamento -->
                        <select wire:model="selectedDepartment" class="form-control" style="margin-right: 10px;">
                            <option value="">TODOS LOS DEPARTAMENTOS</option>
                            <option value="LA PAZ">LA PAZ</option>
                            <option value="COCHABAMBA">COCHABAMBA</option>
                            <option value="SANTA CRUZ">SANTA CRUZ</option>
                            <option value="ORURO">ORURO</option>
                            <option value="POTOSÍ">POTOSÍ</option>
                            <option value="TARIJA">TARIJA</option>
                            <option value="CHUQUISACA">CHUQUISACA</option>
                            <option value="PANDO">PANDO</option>
                            <option value="BENI">BENI</option>
                        </select>


                        <button type="button" class="btn btn-primary" wire:click="$refresh">Filtrar</button>
                    </div>
                    <div class="ml-auto">
                        <button type="button" class="btn btn-success" wire:click="downloadReport2">Descargar
                            Reporte</button>
                    </div>
                </div>
            </div>

            <!-- BODY: Tabla de admisiones -->
            <div class="card-body">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>
                                <!-- Checkbox de "Seleccionar Todos" -->
                                <input type="checkbox" wire:model="selectAll" />
                            </th>
                            <th>#</th>
                            <th>Origen</th>
                            <th>Peso</th>
                            <th>Envio</th>
                            <th>Destino</th>
                            <th>Código</th>
                            <th>Fecha</th>
                            <th>Observación</th>
                            {{-- <th>Reencaminamiento</th> --}}
                            @hasrole('SuperAdmin|Administrador')
                                <th>Admisión</th>
                            @endhasrole
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($admisiones as $admision)
                            <tr>
                                <td>
                                    <!-- Checkbox individual para cada admisión -->
                                    <input type="checkbox" wire:model="selectedAdmisiones"
                                        value="{{ $admision->id }}" />
                                </td>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $admision->origen }}</td>
                                <td>{{ $admision->peso_regional ?: ($admision->peso_ems ?: $admision->peso) }}</td>
                                <td>{{ $admision->destino }}</td>
                                <td>{{ $admision->reencaminamiento ?? $admision->ciudad }}</td>
                                <td>{{ $admision->codigo }}</td>
                                <td>{{ $admision->fecha }}</td>
                                <td>{{ $admision->observacion }}</td>
                                {{-- <td>{{ $admision->reencaminamiento ?? $admisione->ciudad }}</td> --}}
                                @hasrole('SuperAdmin|Administrador')
                                    <td>{{ $admision->user->name ?? 'No asignado' }}</td>
                                @endhasrole
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- Mensajes de éxito/error -->
    <div>
        @if (session()->has('message'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('message') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
    </div>

    <!-- Modal para "Recibir Envíos" -->
    @if ($showModal)
        <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background: rgba(0, 0, 0, 0.5);">

            <div class="modal-dialog" role="document" style="width: 50%; max-height: 90vh; overflow-y: auto;">

                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Recibir Envíos</h5>
                        <button type="button" class="close" wire:click="closeModal">&times;</button>
                    </div>

                    <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                        @foreach ($selectedAdmisionesData as $index => $data)
                            <div class="border rounded p-3 mb-3">
                                <h6>Envío: {{ $data['codigo'] }}</h6>

                                <!-- Peso EMS -->
                                <div class="form-group">
                                    <label for="pesoEms-{{ $index }}">Peso EMS (opcional)</label>
                                    <input type="number" step="0.01" class="form-control"
                                        id="pesoEms-{{ $index }}"
                                        wire:model="selectedAdmisionesData.{{ $index }}.peso_ems" disabled>
                                </div>

                                <!-- Peso Regional -->
                                <div class="form-group">
                                    <label for="pesoRegional-{{ $index }}">Peso Regional (opcional)</label>
                                    <input type="number" step="0.01" class="form-control"
                                        id="pesoRegional-{{ $index }}"
                                        wire:model="selectedAdmisionesData.{{ $index }}.peso_regional">
                                </div>

                                <!-- Observación -->
                                <div class="form-group">
                                    <label for="observacion-{{ $index }}">Observación (opcional)</label>
                                    <textarea class="form-control" id="observacion-{{ $index }}"
                                        wire:model="selectedAdmisionesData.{{ $index }}.observacion"></textarea>
                                </div>
                                <!-- Combo Box: Notificación -->
                                <div class="form-group">
                                    <label for="notificacion-{{ $index }}">Notificación</label>
                                    <select class="form-control" id="notificacion-{{ $index }}"
                                        wire:model="selectedAdmisionesData.{{ $index }}.notificacion">
                                        <option value="">Seleccione...</option>
                                        <option value="FALTANTE">FALTANTE</option>
                                        <option value="SOBRANTE">SOBRANTE</option>
                                        <option value="MALENCAMINADO">MALENCAMINADO</option>
                                        <option value="DAÑADO">DAÑADO</option>
                                    </select>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">
                            Cancelar
                        </button>
                        <button type="button" class="btn btn-primary" wire:click="recibirEnvios">
                            Guardar
                        </button>
                    </div>
                </div>

            </div>
        </div>
    @endif

</div>

<script>
    document.addEventListener('livewire:load', function() {
        let alert = document.querySelector('.alert');
        if (alert) {
            setTimeout(() => {
                alert.style.display = 'none';
            }, 5000); // Oculta la alerta a los 5 segundos
        }
    });
</script>

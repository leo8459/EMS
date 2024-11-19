<div class="container-fluid">
    <section class="content-header">
        <h1>Inventario</h1>
    </section>

    <section class="content">
        <div class="card">
            <div class="card-header">
                <input type="text" wire:model="searchTerm" placeholder="Buscar..." class="form-control d-inline-block w-50" />
                <button type="button" class="btn btn-primary" wire:click="$refresh">Buscar</button>
            </div>
            <div class="card-footer">
                <button class="btn btn-success" wire:click="openModal">Recibir Envíos</button>
            </div>
            <div class="card-body">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>
                                <input type="checkbox" wire:model="selectAll">
                            </th>                            
                            <th>#</th>
                            <th>Origen</th>
                            <th>Envio</th>
                            <th>Destino</th>
                            <th>Código</th>
                            <th>Peso (EMS)</th>
                            <th>Observación</th>
                            <th>Reencaminado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($admisiones as $admision)
                            <tr>
                                <td>
                                    <input type="checkbox" wire:model="selectedAdmisiones" value="{{ $admision->id }}">
                                </td>                                
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $admision->origen }}</td>
                                <td>{{ $admision->destino }}</td>
                                <td>{{ $admision->ciudad }}</td>
                                <td>{{ $admision->codigo }}</td>
                                <td>{{ $admision->peso_ems }}</td>
                                <td>{{ $admision->observacion }}</td>
                                <td>{{ $admision->reencaminamiento }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $admisiones->links() }}
            </div>
            
        </div>
    </section>
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
    
    <!-- Modal -->
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
                            <h6>Envío #{{ $data['id'] }}</h6>
                            <div class="form-group">
                                <label for="pesoEms-{{ $index }}">Peso EMS (opcional)</label>
                                <input type="number" step="0.01" class="form-control" id="pesoEms-{{ $index }}" 
                                       wire:model="selectedAdmisionesData.{{ $index }}.peso_ems"disabled>
                            </div>
                            <div class="form-group">
                                <label for="pesoRegional-{{ $index }}">Peso Regional (opcional)</label>
                                <input type="number" step="0.01" class="form-control" id="pesoRegional-{{ $index }}" 
                                       wire:model="selectedAdmisionesData.{{ $index }}.peso_regional">
                            </div>
                            <div class="form-group">
                                <label for="observacion-{{ $index }}">Observación (opcional)</label>
                                <textarea class="form-control" id="observacion-{{ $index }}" 
                                          wire:model="selectedAdmisionesData.{{ $index }}.observacion"></textarea>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeModal">Cancelar</button>
                    <button type="button" class="btn btn-primary" wire:click="recibirEnvios">Guardar</button>
                </div>
            </div>
        </div>
    </div>
@endif



</div>
<script>
    document.addEventListener('livewire:load', function () {
        let alert = document.querySelector('.alert');
        if (alert) {
            setTimeout(() => {
                alert.style.display = 'none';
            }, 5000); // 5 segundos
        }
    });
</script>
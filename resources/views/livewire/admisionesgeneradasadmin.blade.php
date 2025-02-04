<div class="container-fluid">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Admisiones Generadas</h1>
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
                                {{-- <button type="button" class="btn btn-success" wire:click="exportToExcel">Descargar Excel</button>
                                <button type="button" class="btn btn-danger" wire:click="exportToPDF">Descargar PDF</button> --}}

                            </div>
                            <div class="form-inline">
                                <label for="department">Departamento:</label>
                                <select id="department" wire:model="department" class="form-control mx-2">
                                    <option value="">Todos</option>
                                    <!-- Opción para incluir todos los departamentos -->
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

                                <label for="startDate">Desde:</label>
                                <input type="date" id="startDate" wire:model="startDate" class="form-control mx-2">

                                <label for="endDate">Hasta:</label>
                                <input type="date" id="endDate" wire:model="endDate" class="form-control mx-2">

                                <button type="button" class="btn btn-danger mx-2" wire:click="exportToPDF">Exportar
                                    PDF</button>
                                <button type="button" class="btn btn-success" wire:click="exportToExcel">Exportar
                                    Excel</button>
                                <!-- Botón para generar el backup -->
                                <button type="button" class="btn btn-info mx-2" wire:click="backupProject"
                                    wire:loading.attr="disabled" wire:target="backupProject">
                                    Generar Backup
                                </button>

                                <!-- Mensaje de "Espere por favor" -->
                             <!-- Overlay de carga: Se muestra mientras se ejecuta backupProject -->
                             <div 
                             x-data="{ progress: 0, interval: null }"
                             x-on:livewire:loading.start="
                                 progress = 0;
                                 interval = setInterval(() => {
                                     if(progress < 90) { 
                                         progress += 1; 
                                     }
                                 }, 100);
                             "
                             x-on:livewire:loading.stop="
                                 progress = 100;
                                 clearInterval(interval);
                             "
                             wire:loading wire:target="backupProject"
                             class="full-screen-overlay"
                         >
                             <div class="overlay-content">
                                 <h3>Por favor espere</h3>
                                 <p>Se está generando el backup...</p>
                                 <div class="progress">
                                     <div 
                                         class="progress-bar progress-bar-striped progress-bar-animated" 
                                         role="progressbar" 
                                         :style="'width: ' + progress + '%'" 
                                         :aria-valuenow="progress" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                         <span x-text="progress + '%'"></span>
                                     </div>
                                 </div>
                             </div>
                         </div>
<script src="//unpkg.com/alpinejs" defer></script>

<!-- Asegúrate de tener cargado Alpine.js, por ejemplo: -->
<!-- <script src="//unpkg.com/alpinejs" defer></script> -->

<style>
    /* Overlay que ocupa toda la pantalla */
    .full-screen-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5); /* Fondo semi-transparente */
        display: flex;
        justify-content: center; /* Centra horizontalmente */
        align-items: center;     /* Centra verticalmente */
        z-index: 9999;
    }
    .overlay-content {
        background: #fff;
        padding: 30px;
        border-radius: 10px;
        text-align: center;
        box-shadow: 0 0 10px rgba(0,0,0,0.3);
        width: 300px;   /* Ancho fijo */
        margin: auto;   /* Asegura que se centre */
    }
    /* Estilos para la barra de progreso */
    .progress {
        background-color: #e9ecef;
        border-radius: 5px;
        overflow: hidden;
        margin-top: 20px;
    }
    .progress-bar {
        background-color: #007bff;
        height: 20px;
        line-height: 20px;
        color: #fff;
        text-align: center;
        white-space: nowrap;
        transition: width 0.1s linear; /* Transición suave */
    }
</style>
                                
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
                                        @hasrole('ADMINISTRADOR')
                                            <th>Acciones</th>
                                        @endhasrole

                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($admisiones as $admisione)
                                        <tr>
                                            <td>
                                                <input type="checkbox" wire:model="selectedAdmisiones"
                                                    value="{{ $admisione->id }}" />
                                            </td>
                                            <td>{{ $loop->iteration }}</td> <!-- Mostrar número de fila -->
                                            <td>{{ $admisione->origen }}</td>
                                            <td>{{ $admisione->servicio }}</td>
                                            <td>{{ $admisione->tipo_correspondencia }}</td>
                                            <td>{{ $admisione->cantidad }}</td>
                                            <td>{{ $admisione->peso }}</td>
                                            <td>{{ $admisione->precio }}</td>
                                            <td>{{ $admisione->destino }}</td>
                                            <td>{{ $admisione->codigo }}</td>

                                            <td>{{ $admisione->direccion }}</td>
                                            <td>{{ $admisione->provincia }}</td>
                                            <td>{{ $admisione->ciudad }}</td>
                                            <td>{{ $admisione->pais }}</td>
                                            <td>{{ $admisione->fecha }}</td>

                                            <td>
                                                {{-- <button type="button" class="btn btn-info" wire:click="edit({{ $admisione->id }})">Editar</button> --}}
                                                @hasrole('ADMINISTRADOR')
                                                    <button type="button" class="btn btn-warning"
                                                        wire:click="devolverAdmision({{ $admisione->id }})">Devolver a
                                                        admisión</button>
                                                @endhasrole
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


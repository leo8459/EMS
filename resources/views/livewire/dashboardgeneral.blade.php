<div class="dashboard">
    <style>
        .dashboard {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            padding: 20px;
            background-color: #f4f5f7;
            justify-content: space-between;
        }

        .dashboard-title {
            width: 100%;
            font-size: 1.8rem;
            font-weight: bold;
            margin-bottom: 20px;
            color: #333;
        }

        .dashboard-department {
            width: 100%;
            margin-bottom: 20px;
        }

        .department-title {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
        }

        .department-cards {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }

        .dashboard-card {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: transform 0.2s;
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
        }

        .card-content {
            display: flex;
            flex-direction: column;
        }

        .card-content p {
            margin: 0;
            font-size: 1rem;
            color: #555;
        }

        .card-content h3 {
            margin: 5px 0 0;
            font-size: 2rem;
            color: #333;
            font-weight: bold;
        }

        .card-icon {
            border-radius: 50%;
            padding: 15px;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #fff;
            font-size: 1.5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        /* =======================
           MEDIA QUERIES
           ======================= */

        /* Ajustes para tablets o pantallas medianas */
        @media (max-width: 992px) {
            .department-cards {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        /* Ajustes para teléfonos o pantallas pequeñas */
        @media (max-width: 576px) {

            /* Las tarjetas ocupan todo el ancho en una sola columna */
            .department-cards {
                grid-template-columns: 1fr;
            }

            /* Ajustar tamaño de los títulos */
            .dashboard-title {
                font-size: 1.4rem;
            }

            .department-title {
                font-size: 1.2rem;
            }

            /* Ajustar tipografía dentro de las tarjetas */
            .card-content p {
                font-size: 0.9rem;
            }

            .card-content h3 {
                font-size: 1.5rem;
            }

            .card-icon {
                font-size: 1.2rem;
                padding: 10px;
            }
        }
    </style>

    <!-- Título General (Solo para Administradores) -->
    @if (Auth::user()->hasRole('ADMINISTRADOR'))
        <div class="dashboard-title">Resumen Nivel Nacional</div>

       
        <div class="dashboard-card">
            <div class="card-content">
                <p>Por Entregar</p>
                <h3>{{ $porEntregar }}</h3>
            </div>
            <div class="card-icon" style="background: linear-gradient(45deg, #FF9800, #FFC107);">
                🚛
            </div>
        </div>
        <div class="dashboard-card">
            <div class="card-content">
                <p>Entregados Hoy</p>
                <h3>{{ $totalEntregadosHoy }}</h3>
            </div>
            <div class="card-icon" style="background: linear-gradient(45deg, #03A9F4, #0288D1);">
                🚚
            </div>
        </div>
        <!-- Datos Generales -->
        <div class="dashboard-card">
            <div class="card-content">
                <p>Total Admisiones</p>
                <h3>{{ $totalAdmisiones }}</h3>
            </div>
            <div class="card-icon" style="background: linear-gradient(45deg, #6a11cb, #2575fc);">
                📋
            </div>
        </div>
       
        <div class="dashboard-card">
            <div class="card-content">
                <p>Total Recaudado</p>
                <h3>Bs{{ number_format($totalRecaudado, 2) }}</h3>
            </div>
            <div class="card-icon" style="background: linear-gradient(45deg, #3F51B5, #2196F3);">
                💰
            </div>
        </div>
        
        
    @endif

    <!-- Datos por Departamento -->
    @foreach ($datosPorDepartamento as $departamento => $datos)
        <div class="dashboard-department">
            <div class="department-title">{{ $departamento }}</div>
            <div class="department-cards">
               

                <!-- Admisiones en Regional -->
                <div class="dashboard-card">
                    <div class="card-content">
                        <p>Por entregar</p>
                        <h3>{{ $datos['enRegional'] }}</h3>
                    </div>
                    <div class="card-icon" style="background: linear-gradient(45deg, #FF9800, #FFC107);">
                        🚛
                    </div>
                </div>
                <!-- Admisiones Generadas Hoy -->
                <div class="dashboard-card">
                    <div class="card-content">
                        <p>Entregados Hoy</p>
                        <h3>{{ $datos['admisionesHoy'] }}</h3>
                    </div>
                    <div class="card-icon" style="background: linear-gradient(45deg, #03A9F4, #0288D1);">
                        🚚
                    </div>
                </div>


                {{-- <!-- Total Entregados -->
                <div class="dashboard-card">
                    <div class="card-content">
                        <p>Total Entregados</p>
                        <h3>{{ $datos['totalEntregados'] }}</h3>
                    </div>
                    <div class="card-icon" style="background: linear-gradient(45deg, #4CAF50, #8BC34A);">
                        ✅
                    </div>
                </div> --}}
                
                <!-- Total Admisiones -->
                <div class="dashboard-card">
                    <div class="card-content">
                        <p>Total Admisiones</p>
                        <h3>{{ $datos['totalAdmisiones'] }}</h3>
                    </div>
                    <div class="card-icon" style="background: linear-gradient(45deg, #6a11cb, #2575fc);">
                        📋
                    </div>
                </div>
                <!-- Total Recaudado -->
                <div class="dashboard-card">
                    <div class="card-content">
                        <p>Total Recaudado</p>
                        <h3>Bs{{ number_format($datos['totalRecaudado'], 2) }}</h3>
                    </div>
                    <div class="card-icon" style="background: linear-gradient(45deg, #3F51B5, #2196F3);">
                        💰
                    </div>


                </div>

            </div>

        </div>
    @endforeach

    <div class="dashboard">
        <canvas id="estado7Chart" width="400" height="400"></canvas>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('estado7Chart').getContext('2d');
            const estado7Data = @json($estado7Data); // Datos enviados desde el backend

            const labels = Object.keys(estado7Data);
            const data = Object.values(estado7Data);

            new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Envíos en Regional',
                        data: data,
                        backgroundColor: [
                            '#FF6384',
                            '#36A2EB',
                            '#FFCE56',
                            '#4BC0C0',
                            '#9966FF',
                            '#FF9F40',
                            '#66BB6A',
                            '#D32F2F',
                            '#FBC02D'
                        ],
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'Envíos Recibidos Departamento'
                        }
                    }
                }
            });
        });
    </script>

    <div class="dashboard">
        <div class="chart-container">
            <canvas id="estado5Chart" width="400" height="400"></canvas>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Gráfico de Estado 5
            const canvasEstado5 = document.getElementById('estado5Chart');
            if (canvasEstado5) {
                const ctx5 = canvasEstado5.getContext('2d');
                const estado5Data = @json($estado5Data); // Cambiar a estado5Data

                const labels5 = Object.keys(estado5Data);
                const data5 = Object.values(estado5Data);

                if (data5.length > 0) {
                    new Chart(ctx5, {
                        type: 'pie',
                        data: {
                            labels: labels5,
                            datasets: [{
                                label: 'Envíos Entregados',
                                data: data5,
                                backgroundColor: [
                                    '#4BC0C0',
                                    '#9966FF',
                                    '#FF6384',
                                    '#36A2EB',
                                    '#FFCE56',
                                    '#FF9F40',
                                    '#66BB6A',
                                    '#D32F2F',
                                    '#FBC02D'
                                ],
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    position: 'top',
                                },
                                title: {
                                    display: true,
                                    text: 'Envíos Entregados por Departamento'
                                }
                            }
                        }
                    });
                } else {
                    console.log('No hay datos para el gráfico de estado 5.');
                }
            } else {
                console.log('Canvas para el gráfico de estado 5 no encontrado.');
            }
        });
    </script>

    <div class="dashboard">
        <div class="chart-container">
            <canvas id="estadoComparativoChart" width="400" height="400"></canvas>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Gráfico Comparativo Estados
            const canvasEstadoComparativo = document.getElementById('estadoComparativoChart');
            if (canvasEstadoComparativo) {
                const ctxComparativo = canvasEstadoComparativo.getContext('2d');
                const estadoComparativoData = @json($estadoComparativo);

                const labelsComparativo = Object.keys(estadoComparativoData);
                const dataComparativo = Object.values(estadoComparativoData);

                if (dataComparativo.length > 0) {
                    new Chart(ctxComparativo, {
                        type: 'pie',
                        data: {
                            labels: labelsComparativo,
                            datasets: [{
                                label: 'Entregados/No Entregados',
                                data: dataComparativo,
                                backgroundColor: [
                                    '#4CAF50', // Entregados
                                    '#F44336', // No Entregados
                                ],
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    position: 'top',
                                },
                                title: {
                                    display: true,
                                    text: 'Comparativa: Entregados vs Por entregar (%)'
                                }
                            }
                        }
                    });
                } else {
                    console.log('No hay datos para el gráfico comparativo.');
                }
            } else {
                console.log('Canvas para el gráfico comparativo no encontrado.');
            }
        });
    </script>
</div>

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
    </style>

    <!-- Título General (Solo para Administradores) -->
    @if (Auth::user()->hasRole('ADMINISTRADOR'))
        <div class="dashboard-title">Resumen Nivel Nacional</div>

        <!-- Datos Generales -->
        <div class="dashboard-card">
            <div class="card-content">
                <p>Total Admisiones</p>
                <h3>{{ $totalAdmisiones }}</h3>
            </div>
            <div class="card-icon" style="background: linear-gradient(45deg, #FF5722, #FFC107);">
                📋
            </div>
        </div>
        <div class="dashboard-card">
            <div class="card-content">
                <p>Total Entregados</p>
                <h3>{{ $totalEntregados }}</h3>
            </div>
            <div class="card-icon" style="background: linear-gradient(45deg, #4CAF50, #8BC34A);">
                ✅
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
        <div class="dashboard-card">
            <div class="card-content">
                <p>Admisiones Generadas Hoy</p>
                <h3>{{ $admisionesHoy }}</h3>
            </div>
            <div class="card-icon" style="background: linear-gradient(45deg, #9C27B0, #E91E63);">
                📅
            </div>
        </div>
    @endif

    <!-- Datos por Departamento -->
    @foreach ($datosPorDepartamento as $departamento => $datos)
        <div class="dashboard-department">
            <div class="department-title">{{ $departamento }}</div>
            <div class="department-cards">
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

             

          
                <!-- Admisiones Generadas Hoy -->
                <div class="dashboard-card">
                    <div class="card-content">
                        <p>Admisiones Generadas Hoy</p>
                        <h3>{{ $datos['admisionesHoy'] }}</h3>
                    </div>
                    <div class="card-icon" style="background: linear-gradient(45deg, #9C27B0, #E91E63);">
                        📅
                    </div>
                </div>
                   <!-- Total Entregados -->
                   <div class="dashboard-card">
                    <div class="card-content">
                        <p>Total Entregados</p>
                        <h3>{{ $datos['totalEntregados'] }}</h3>
                    </div>
                    <div class="card-icon" style="background: linear-gradient(45deg, #4CAF50, #8BC34A);">
                        ✅
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
</div>

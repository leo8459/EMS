<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Envíos Entregados y Faltantes</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
            background-color: #f9f9f9;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header img {
            width: 100%;
            max-width: 750px;
            height: auto;
            margin-bottom: 20px;
        }
        .title {
            text-align: center;
            margin: 0;
            font-size: 24px;
            text-transform: uppercase;
            color: #222;
            letter-spacing: 1px;
        }
        .generated {
            text-align: center;
            margin: 5px 0 20px 0;
            font-size: 14px;
            color: #555;
        }
        .summary {
            margin-bottom: 20px;
        }
        .summary p {
            font-size: 14px;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #fff;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        table, th, td {
            border: 1px solid #ccc;
        }
        th {
            background-color: #004080;
            color: #fff;
            font-weight: bold;
            text-align: center;
            padding: 10px;
            font-size: 14px;
        }
        td {
            padding: 10px;
            text-align: center;
            font-size: 12px;
            color: #333;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        tr:hover {
            background-color: #e6f7ff;
        }
        .footer {
            text-align: center;
            margin-top: 50px;
            font-size: 12px;
            color: #777;
        }
        .footer p {
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/CABECERA.jpg'))) }}" alt="Cabecera Agencia Boliviana de Correos">
    </div>
    <h2 class="title">Reporte de Envíos Entregados y Por Entregar</h2>
    <p class="generated">Generado el: {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</p>
    <div class="summary">
        <p><strong>Total Entregados:</strong> {{ $totalEntregados }}</p>
        <p><strong>Total Entregados por Ventanilla:</strong> {{ $totalVentanilla }}</p>
        <p><strong>Total Entregados por Cartero:</strong> {{ $totalCartero }}</p>
        <p><strong>Total Por Entregar (Fecha establecida):</strong> {{ $totalFaltantes }}</p>
        <p><strong>Rango de Fechas:</strong> 
            @if($startDate && $endDate)
                {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
            @else
                No especificado
            @endif
        </p>
        @if($department)
            <p><strong>Departamento:</strong> {{ $department }}</p>
        @endif
    </div>
    
    <h3>Envíos Entregados por Ventanilla</h3>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Origen</th>
                <th>Destino</th>
                <th>Código</th>
                <th>Peso (kg)</th>
                <th>Actualizado</th>
                <th>Observación</th>
                <th>Firma</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($admisionesVentanilla as $index => $admision)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $admision->origen }}</td>
                    <td>{{ $admision->reencaminamiento ?: $admision->ciudad }}</td>
                    <td>{{ $admision->codigo }}</td>
                    <td>{{ number_format($admision->peso_regional ?: ($admision->peso_ems ?: $admision->peso), 2) }}</td>
                    <td>{{ $admision->updated_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $admision->observacion ?: '' }}</td>
                    <td>
                        @if ($admision->firma_entrega)
                            <div
                                style="width: 100px; height: 100px; overflow: hidden; display: flex; justify-content: center; align-items: center; border: 1px solid #ddd; border-radius: 5px; background-color: #f9f9f9;">
                                <img src="{{ $admision->firma_entrega }}"
                                    alt="Firma de entrega"
                                    style="max-width:100%; max-height: 100%; object-fit: cover; border-radius: 5px;">
                            </div>
                        @else
                            <span>Sin firma</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    
    <h3>Envíos Entregados por Cartero</h3>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Origen</th>
                <th>Destino</th>
                <th>Código</th>
                <th>Peso (kg)</th>
                <th>Actualizado</th>
                <th>Observación</th>
                <th>Firma</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($admisionesCartero as $index => $admision)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $admision->origen }}</td>
                    <td>{{ $admision->reencaminamiento ?: $admision->ciudad }}</td>
                    <td>{{ $admision->codigo }}</td>
                    <td>{{ number_format($admision->peso_regional ?: ($admision->peso_ems ?: $admision->peso), 2) }}</td>
                    <td>{{ $admision->updated_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $admision->observacion ?: '' }}</td>
                    <td>
                        @if ($admision->firma_entrega)
                            <div
                                style="width: 100px; height: 100px; overflow: hidden; display: flex; justify-content: center; align-items: center; border: 1px solid #ddd; border-radius: 5px; background-color: #f9f9f9;">
                                <img src="{{ $admision->firma_entrega }}"
                                    alt="Firma de entrega"
                                    style="max-width:100%; max-height: 100%; object-fit: cover; border-radius: 5px;">
                            </div>
                        @else
                            <span>Sin firma</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Envíos Filtrados</title>
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
            margin-bottom: 40px;
        }
        .header img {
            width: 100%;
            max-width: 750px;
            height: auto;
            margin-bottom: 20px;
        }
        .titulo {
            text-align: center;
            margin-bottom: 30px;
            font-size: 24px;
            text-transform: uppercase;
            color: #222;
            letter-spacing: 1px;
        }
        h2 {
            margin-top: 40px;
            font-size: 18px;
            color: #004080;
            border-bottom: 2px solid #004080;
            padding-bottom: 5px;
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
        p {
            font-size: 14px;
            color: #666;
            margin: 10px 0;
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
    <h1 class="titulo">Reporte de Envíos Filtrados</h1>
    @foreach ($datos as $servicio => $admisiones)
        {{-- <h2>{{ $servicio }}</h2> --}}
        @if ($admisiones->isNotEmpty())
            <table>
                <thead>
                    <tr>
                        <th>Origen</th>
                        <th>Reencaminamiento/Ciudad</th>
                        <th>Código</th>
                        <th>Peso</th>
                        <th>Fecha</th>
                        <th>Observación</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($admisiones as $admision)
                        <tr>
                            <td>{{ $admision->origen }}</td>
                            <td>{{ $admision->reencaminamiento ?: $admision->ciudad }}</td>
                            <td>{{ $admision->codigo }}</td>
                            <td>{{ number_format($admision->peso_regional ?: ($admision->peso_ems ?: $admision->peso), 2) }}</td>
                            <td>{{ $admision->fecha }}</td>
                            <td>{{ $admision->observacion ?: 'N/A' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>No hay datos disponibles para este servicio.</p>
        @endif
    @endforeach
    <div class="footer">
        <p>Reporte generado automáticamente por el sistema de EMS.</p>
        <p>© {{ date('Y') }} Agencia Boliviana de Correos. Todos los derechos reservados.</p>
    </div>
</body>
</html>

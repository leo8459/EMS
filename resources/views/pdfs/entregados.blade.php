<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Envíos Entregados</title>
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
    <h2 class="title">Reporte de Envíos Entregados</h2>
    <p class="generated">Generado el: {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</p>
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
            </tr>
        </thead>
        <tbody>
            @foreach ($admisiones as $index => $admision)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $admision->origen }}</td>
                    <td>{{ $admision->reencaminamiento ?: $admision->ciudad }}</td>
                    <td>{{ $admision->codigo }}</td>
                    <td>{{ number_format($admision->peso_regional ?: ($admision->peso_ems ?: $admision->peso), 2) }}</td>
                    <td>{{ $admision->updated_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $admision->observacion ?: '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="footer">
        <p>Reporte generado automáticamente por el sistema de EMS.</p>
        <p>© {{ date('Y') }} Agencia Boliviana de Correos. Todos los derechos reservados.</p>
    </div>
</body>
</html>

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
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
        }
        .header p {
            margin: 0;
            font-size: 12px;
            color: #555;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 5px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Envíos Entregados</h1>
        <p>Generado el: {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</p>
    </div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Origen</th>
                <th>Destino</th>
                <th>Código</th>
                <th>Peso</th>
                <th>Actualizado</th>
                <th>Observación</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($admisiones as $index => $admision)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $admision->origen }}</td>
                    <td>
                        {{ $admision->reencaminamiento ?: $admision->ciudad }}
                    </td>
                    <td>{{ $admision->codigo }}</td>
                    <td>
                        {{ $admision->peso_regional ?: ($admision->peso_ems ?: $admision->peso) }}
                    </td>
                    <td>{{ $admision->updated_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $admision->observacion }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>

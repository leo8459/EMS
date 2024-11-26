<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Admisiones</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h1>Reporte de Admisiones</h1>
    <table>
        <thead>
            <tr>
                <th>Origen</th>
                <th>Destino</th>
                <th>Código</th>
                <th>Peso</th>
                <th>Entregado</th>
                <th>Observación</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($admisiones as $admision)
                <tr>
                    <td>{{ $admision->origen }}</td>
                    <td>{{ $admision->reencaminamiento ?: $admision->ciudad }}</td>
                    <td>{{ $admision->codigo }}</td>
                    <td>{{ $admision->peso_regional ?: ($admision->peso_ems ?: $admision->peso) }}</td>
                    <td>{{ $admision->updated_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $admision->observacion }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>

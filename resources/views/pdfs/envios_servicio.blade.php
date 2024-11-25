<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Filtrado</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .titulo { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1 class="titulo">Reporte de Envíos Filtrados</h1>
    @foreach ($datos as $servicio => $admisiones)
        <h2>{{ $servicio }}</h2>
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
                            <td>{{ $admision->peso_regional ?: ($admision->peso_ems ?: $admision->peso) }}</td>
                            <td>{{ $admision->fecha }}</td>
                            <td>{{ $admision->observacion }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>No hay datos disponibles para este servicio.</p>
        @endif
    @endforeach
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Admisiones</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px; /* Reducir tamaño de fuente */
            margin: 20px; /* Reducir márgenes */
        }
        .header {
            text-align: center;
            margin-bottom: 10px; /* Reducir espacio inferior */
        }
        .header img {
            height: 50px; /* Reducir tamaño de imagen */
        }
        .title {
            font-size: 14px; /* Reducir tamaño de título */
            font-weight: bold;
            margin-top: 5px;
        }
        .subtitle {
            font-size: 12px;
            margin-top: 2px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px; /* Reducir espacio superior */
        }
        th, td {
            border: 1px solid #000;
            padding: 4px; /* Reducir relleno */
            text-align: center;
        }
        th {
            background-color: #4F81BD;
            color: #fff;
            font-size: 10px; /* Reducir tamaño de fuente en encabezados */
        }
        .footer {
            margin-top: 10px; /* Reducir espacio superior */
            text-align: right;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ public_path('images/CABECERA.jpg') }}" alt="Cabecera">
        <div class="title">Arqueo</div>
        <div class="subtitle">AGENCIA BOLIVIANA DE CORREOS</div>
        <div class="subtitle">EXPRESADO EN BS.</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Fecha</th>
                <th>Cantidad</th>
                <th>Origen</th>
                <th>Tipo de Correspondencia</th>
                <th>Código de Envío</th>
                <th>Peso</th>
                <th>País/Ciudad de Destino</th>
                <th>N° Factura</th>
                <th>Importe (Bs)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($admisiones as $index => $admision)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $admision->fecha }}</td>
                    <td>{{ $admision->cantidad }}</td>
                    <td>{{ $admision->origen }}</td>
                    <td>{{ $admision->tipo_correspondencia }}</td>
                    <td>{{ $admision->codigo }}</td>
                    <td>{{ $admision->peso }}</td>
                    <td>{{ $admision->ciudad ?? 'SIN DESTINO' }}</td>
                    <td>{{ $admision->numero_factura }}</td>
                    <td>{{ $admision->precio }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Total Importe: {{ $admisiones->sum('precio') }} Bs</p>
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CN 22</title>
    <style>
        body {
            margin: 10px;
        }

        table {
    width: 100%;
    table-layout: fixed;
    border-collapse: collapse;
    font-size: 8px; /* Reducimos el tamaño de fuente */
    margin-bottom: 5px; /* Reducimos el margen entre tablas */
    page-break-inside: avoid;
}

        th,
        td {
    border: 1px solid #000;
    padding: 1px; /* Reducimos el padding */
    vertical-align: top;
}

        thead {
            background-color: #ffffff;
        }

        .rotated-table-wrapper {
    display: flex;
    justify-content: center;
    align-items: center;
    overflow: auto;
    margin-top: 10px; /* Ajusta el margen para que haya espacio suficiente */
}

.rotated-table {
    transform: rotate(270deg);
    transform-origin: right top;
    margin-top: 20px;
    width: 250px; 
    max-width: 100%;
}

    </style>
</head>

<body>
    <!-- Primera tabla -->
    <div>
        <table style="width: 700px;">
            <colgroup>
                <col style="width: 87px">
                <col style="width: 90px">
                <col style="width: 80px">
                <col style="width: 86px">
                <col style="width: 108px">
                <col style="width: 138px">
                <col style="width: 20px">
                <col style="width: 125px">
            </colgroup>
            <thead>
                <tr>
                    <td colspan="3"><img src="{{ public_path('images/images.png') }}" alt="" width="150" height="50"></td>
                    <td colspan="3" rowspan="2">
                        <div style="text-align: center; font-size: 14px;">
                            {!! DNS1D::getBarcodeHTML($codigo, 'C128', 1.0, 40) !!}<br>{{ $codigo }}
                        </div>
                    </td>
                    <td rowspan="8" style="text-align: center; font-size: 7px;">
                        {{-- Datos de ubicación --}}
                    </td>
                </tr>
                <tr>
                    <td>OF. ORIGEN: <br>
                        <div style="text-align: right;">{{ $origen }}</div>
                    </td>
                    <td>OF. DESTINO: <br>
                        <div style="text-align: right;">{{ $destino }}</div>
                    </td>
                    <td></td>
                </tr>
                <tr>
                    <td colspan="3" rowspan="2">
                        NOMBRE REMITENTE: <br>
                        <div style="text-align: right; font-size: 14px;">{{ $nombre_remitente }}</div>
                    </td>
                    <td colspan="3" rowspan="2">NOMBRE DESTINATARIO: <br>
                        <div style="text-align: right; font-size: 14px;">{{ $nombre_destinatario }}</div>
                    </td>
                </tr>
                <tr></tr>
                <tr>
                    <td colspan="3" rowspan="2">DIRECCIÓN Y TELÉFONO:
                        <div style="text-align: right; font-size: 14px;"><br>{{ $telefono_remitente }}</div>
                    </td>
                    <td colspan="3" rowspan="2">DIRECCIÓN Y TELÉFONO:<br>
                        <div style="text-align: right; font-size: 14px;">
                            {{ $direccion }}<br>{{ $telefono_destinatario }}</div>
                    </td>
                </tr>
                <tr></tr>
                <tr>
                    <td colspan="3">DESCRIPCIÓN:
                        <div style="text-align: right; font-size: 14px;">{{ $tipo_correspondencia }}</div>
                    </td>
                    <td rowspan="2" style="vertical-align: top;">FIRMA AGBC:<br></td>
                    <td colspan="2" rowspan="2" style="vertical-align: top;">FIRMA:<br></td>
                </tr>
                <tr>
                    <td>IMPORTE: <br>
                        <div style="text-align: right;">{{ $precio }}</div>
                    </td>
                    <td>PESO:<br>
                        <div style="text-align: right;">{{ $peso }} kg</div>
                    </td>
                    <td>FECHA:<br>
                        <div style="text-align: right;">{{ date('Y-m-d') }}</div>
                    </td>
                </tr>
            </thead>
        </table>
    </div>

    <!-- Añadimos un margen grande entre las tablas repetidas -->
    <div style="height: 40px;"></div>

    <div class="page-break">
        <table style="width: 700px;">
            <colgroup>
                <col style="width: 87px">
                <col style="width: 90px">
                <col style="width: 80px">
                <col style="width: 86px">
                <col style="width: 108px">
                <col style="width: 138px">
                <col style="width: 20px">
                <col style="width: 125px">
            </colgroup>
            <thead>
                <tr>
                    <td colspan="3"><img src="{{ public_path('images/images.png') }}" alt="" width="150" height="50"></td>
                    <td colspan="3" rowspan="2">
                        <div style="text-align: center; font-size: 14px;">
                            {!! DNS1D::getBarcodeHTML($codigo, 'C128', 1.0, 40) !!}<br>{{ $codigo }}
                        </div>
                    </td>
                    <td rowspan="8" style="text-align: center; font-size: 7px;">
                        {{-- Datos de ubicación --}}
                    </td>
                </tr>
                <tr>
                    <td>OF. ORIGEN: <br>
                        <div style="text-align: right;">{{ $origen }}</div>
                    </td>
                    <td>OF. DESTINO: <br>
                        <div style="text-align: right;">{{ $destino }}</div>
                    </td>
                    <td></td>
                </tr>
                <tr>
                    <td colspan="3" rowspan="2">
                        NOMBRE REMITENTE: <br>
                        <div style="text-align: right; font-size: 14px;">{{ $nombre_remitente }}</div>
                    </td>
                    <td colspan="3" rowspan="2">NOMBRE DESTINATARIO: <br>
                        <div style="text-align: right; font-size: 14px;">{{ $nombre_destinatario }}</div>
                    </td>
                </tr>
                <tr></tr>
                <tr>
                    <td colspan="3" rowspan="2">DIRECCIÓN Y TELÉFONO:
                        <div style="text-align: right; font-size: 14px;"><br>{{ $telefono_remitente }}</div>
                    </td>
                    <td colspan="3" rowspan="2">DIRECCIÓN Y TELÉFONO:<br>
                        <div style="text-align: right; font-size: 14px;">
                            {{ $direccion }}<br>{{ $telefono_destinatario }}</div>
                    </td>
                </tr>
                </tr>
                <tr></tr>
                <tr>
                    <td colspan="3">DESCRIPCIÓN:
                        <div style="text-align: right; font-size: 14px;">{{ $tipo_correspondencia }}</div>
                    </td>
                    <td rowspan="2" style="vertical-align: top;">FIRMA AGBC:<br></td>
                    <td colspan="2" rowspan="2" style="vertical-align: top;">FIRMA :<br></td>
                </tr>
                <tr>
                    <td>IMPORTE: <br>
                        <div style="text-align: right;">{{ $precio }}</div>
                    </td>
                    <td>PESO:<br>
                        <div style="text-align: right;">{{ $peso }} kg</div>
                    </td>
                    <td>FECHA:<br>
                        <div style="text-align: right;">{{ date('Y-m-d') }}</div>
                    </td>
                </tr>
            </thead>
        </table>
    </div>
  <div class="page-break">
        <table style="width: 700px;">
            <colgroup>
                <col style="width: 87px">
                <col style="width: 90px">
                <col style="width: 80px">
                <col style="width: 86px">
                <col style="width: 108px">
                <col style="width: 138px">
                <col style="width: 20px">
                <col style="width: 125px">
            </colgroup>
            <thead>
                <tr>
                    <td colspan="3"><img src="{{ public_path('images/images.png') }}" alt="" width="150" height="50"></td>
                    <td colspan="3" rowspan="2">
                        <div style="text-align: center; font-size: 14px;">
                            {!! DNS1D::getBarcodeHTML($codigo, 'C128', 1.0, 40) !!}<br>{{ $codigo }}
                        </div>
                    </td>
                    <td rowspan="8" style="text-align: center; font-size: 7px;">
                        {{-- Datos de ubicación --}}
                    </td>
                </tr>
                <tr>
                    <td>OF. ORIGEN: <br>
                        <div style="text-align: right;">{{ $origen }}</div>
                    </td>
                    <td>OF. DESTINO: <br>
                        <div style="text-align: right;">{{ $destino }}</div>
                    </td>
                    <td></td>
                </tr>
                <tr>
                    <td colspan="3" rowspan="2">
                        NOMBRE REMITENTE: <br>
                        <div style="text-align: right; font-size: 14px;">{{ $nombre_remitente }}</div>
                    </td>
                    <td colspan="3" rowspan="2">NOMBRE DESTINATARIO: <br>
                        <div style="text-align: right; font-size: 14px;">{{ $nombre_destinatario }}</div>
                    </td>
                </tr>
                <tr></tr>
                <tr>
                    <td colspan="3" rowspan="2">DIRECCIÓN Y TELÉFONO:
                        <div style="text-align: right; font-size: 14px;"><br>{{ $telefono_remitente }}</div>
                    </td>
                    <td colspan="3" rowspan="2">DIRECCIÓN Y TELÉFONO:<br>
                        <div style="text-align: right; font-size: 14px;">
                            {{ $direccion }}<br>{{ $telefono_destinatario }}</div>
                    </td>
                </tr>
                </tr>
                <tr></tr>
                <tr>
                    <td colspan="3">DESCRIPCIÓN:
                        <div style="text-align: right; font-size: 14px;">{{ $tipo_correspondencia }}</div>
                    </td>
                    <td rowspan="2" style="vertical-align: top;">FIRMA AGBC:<br></td>
                    <td colspan="2" rowspan="2" style="vertical-align: top;">FIRMA:<br></td>
                </tr>
                <tr>
                    <td>IMPORTE: <br>
                        <div style="text-align: right;">{{ $precio }}</div>
                    </td>
                    <td>PESO:<br>
                        <div style="text-align: right;">{{ $peso }} kg</div>
                    </td>
                    <td>FECHA:<br>
                        <div style="text-align: right;">{{ date('Y-m-d') }}</div>
                    </td>
                </tr>
            </thead>
        </table>
    </div>
    <div class="page-break">
        <table style="width: 700px;">
            <colgroup>
                <col style="width: 87px">
                <col style="width: 90px">
                <col style="width: 80px">
                <col style="width: 86px">
                <col style="width: 108px">
                <col style="width: 138px">
                <col style="width: 20px">
                <col style="width: 125px">
            </colgroup>
            <thead>
                <tr>
                    <td colspan="3"><img src="{{ public_path('images/images.png') }}" alt="" width="150" height="50"></td>
                    <td colspan="3" rowspan="2">
                        <div style="text-align: center; font-size: 14px;">
                            {!! DNS1D::getBarcodeHTML($codigo, 'C128', 1.0, 40) !!}<br>{{ $codigo }}
                        </div>
                    </td>
                    <td rowspan="8" style="text-align: center; font-size: 8px;">
                        {{-- Datos de ubicación --}}
                    </td>
                </tr>
                <tr>
                    <td>OF. ORIGEN: <br>
                        <div style="text-align: right;">{{ $origen }}</div>
                    </td>
                    <td>OF. DESTINO: <br>
                        <div style="text-align: right;">{{ $destino }}</div>
                    </td>
                    <td></td>
                </tr>
                <tr>
                    <td colspan="3" rowspan="2">
                        NOMBRE REMITENTE: <br>
                        <div style="text-align: right; font-size: 14px;">{{ $nombre_remitente }}</div>
                    </td>
                    <td colspan="3" rowspan="2">NOMBRE DESTINATARIO: <br>
                        <div style="text-align: right; font-size: 14px;">{{ $nombre_destinatario }}</div>
                    </td>
                </tr>
                <tr></tr>
                <tr>
                    <td colspan="3" rowspan="2">DIRECCIÓN Y TELÉFONO:
                        <div style="text-align: right; font-size: 14px;"><br>{{ $telefono_remitente }}</div>
                    </td>
                    <td colspan="3" rowspan="2">DIRECCIÓN Y TELÉFONO:<br>
                        <div style="text-align: right; font-size: 14px;">
                            {{ $direccion }}<br>{{ $telefono_destinatario }}</div>
                    </td>
                </tr>
                </tr>
                <tr></tr>
                <tr>
                    <td colspan="3">DESCRIPCIÓN:
                        <div style="text-align: right; font-size: 14px;">{{ $tipo_correspondencia }}</div>
                    </td>
                    <td rowspan="2" style="vertical-align: top;">FIRMA AGBC:<br></td>
                    <td colspan="2" rowspan="2" style="vertical-align: top;">FIRMA:<br></td>
                </tr>
                <tr>
                    <td>IMPORTE: <br>
                        <div style="text-align: right;">{{ $precio }}</div>
                    </td>
                    <td>PESO:<br>
                        <div style="text-align: right;">{{ $peso }} kg</div>
                    </td>
                    <td>FECHA:<br>
                        <div style="text-align: right;">{{ date('Y-m-d') }}</div>
                    </td>
                </tr>
            </thead>
        </table>
    </div>

    {{-- <div class="page-break">

    <div class="rotated-table-wrapper">
        <div class="rotated-table">
            <table style="width: 350px;">
                <colgroup>
                    <col style="width: 100px">
                    <col style="width: 100px">
                    <col style="width: 100px">
                    <col style="width: 100px">
                </colgroup>
                <thead>
                    <tr>
                        <th colspan="3" rowspan="4">{!! DNS1D::getBarcodeHTML($codigo, 'C128', 1.0, 30) !!}<br>{{ $codigo }}</th>
                        <th>RETORNAR A:</th>
                    </tr>
                    <tr>
                        <th rowspan="3"></th>
                    </tr>
                    <tr></tr>
                    <tr></tr>
                </thead>
                <tbody>
                    <tr>
                        <td rowspan="3"><img src="{{ public_path('images/images.png') }}" alt="" width="80"
                                height="30"></td>
                        <td colspan="2">DECLARACIÓN ADUANERA</td>
                        <td rowspan="3">A1-NACIONAL</td>
                    </tr>
                    <tr>
                        <td colspan="2" rowspan="2"></td>
                    </tr>
                    <tr></tr>
                    <tr>
                        <td>DESDE:</td>
                        <td colspan="3">DESTINATARIO</td>
                    </tr>
                    <tr>
                        <td>AGENCIA BOLIVIANA DE CORREOS - {{ $origen }}</td>
                        <td colspan="3">{{ $nombre_destinatario }}</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td colspan="3">DIRECCIÓN:</td>
                    </tr>
                    <tr>
                        <td>PESO: {{ $peso }} kg</td>
                        <td colspan="3">{{ $direccion }}</td>
                    </tr>
                    <tr>
                        <td>DESTINO: {{ $destino }}</td>
                        <td colspan="3">TELÉFONO:</td>
                    </tr>
                    <tr>
                        <td>SERVICIO: {{ $servicio }}</td>
                        <td colspan="3">{{ $telefono_destinatario }}</td>
                    </tr>
                    <tr>
                        <td>CONTENIDO</td>
                        <td>CANTIDAD</td>
                        <td>VALOR</td>
                        <td>FECHA ENVÍO</td>
                    </tr>
                    <tr>
                        <td rowspan="2">{{ $tipo_correspondencia }}</td>
                        <td rowspan="2">1</td>
                        <td rowspan="2">{{ $numero_factura }}</td>
                        <td rowspan="2">{{ date('Y-m-d') }}</td>
                    </tr>
                    <tr></tr>
                    <tr>
                        <td rowspan="3">BO |
                            <?php echo substr($codigo, 0, 2); ?>
                        </td>
                        <td colspan="3" rowspan="5">El destinatario firmante, cuyo nombre y dirección figuran en
                            el envío,
                            certifica que los datos indicados en la declaración son correctos y que este envío no
                            contiene ningún artículo peligroso prohibido por la legislación o las normas aduaneras.</td>
                    </tr>
                    <tr></tr>
                    <tr></tr>
                    <tr>
                        <td rowspan="2">AGBC</td>
                    </tr>
                    <tr></tr>
                </tbody>
            </table>
        </div>
    </div> --}}
</body>

</html>

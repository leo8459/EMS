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
            font-size: 8px;
            /* Reducimos el tamaño de fuente */
            margin-bottom: 3px;
            /* Reducimos el margen entre tablas */
            page-break-inside: avoid;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 1px;
            /* Reducimos el padding */
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
            margin-top: 3px;
            /* Ajusta el margen para que haya espacio suficiente */
        }

        .rotated-table {
            transform: rotate(270deg);
            transform-origin: right top;
            margin-top: 20px;
            width: 250px;
            max-width: 100%;
        }

        .watermark {
            position: absolute;
            top: 35%;
            left: 35%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 36px;
            color: rgba(100, 100, 100, 0.12);
            white-space: nowrap;
            pointer-events: none;
            user-select: none;
            z-index: 0;
        }

        .boleta {
            position: relative;
            margin-bottom: 10px;
        }

        .boleta table {
            position: relative;
            z-index: 1;
        }

        .watermark-local {
            position: absolute;
            top: 10%;
            left: 35%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 42px;
            color: rgba(120, 120, 120, .18);
            white-space: nowrap;
            pointer-events: none;
            user-select: none;
            z-index: 5;
            /* <<< siempre por encima de la tabla */
        }
    </style>

</head>
@php
    $marcaAgua = match ($destino) {
        'SUPEREXPRESS' => 'NACIONAL SUPEREXPRESS',
        'DEVOLUCION' => 'NACIONAL CON DEVOLUCION',
        'NACIONAL' => 'NACIONAL EMS',
        'POSTPAGO' => 'NACIONAL POSTPAGO',
        'CIUDADES INTERMEDIAS' => 'CIUDADES INTERMEDIAS',
        'TRINIDAD COBIJA' => 'TRINIDAD COBIJA',
        'RIVERALTA GUAYARAMERIN' => 'RIVERALTA GUAYARAMERIN',
        'EMS COBERTURA 1' => 'EMS COBERTURA 1',
        'EMS COBERTURA 2' => 'EMS COBERTURA 2',
        'EMS COBERTURA 3' => 'EMS COBERTURA 3',
        'EMS COBERTURA 4' => 'EMS COBERTURA 4',
        default => '',
    };
@endphp

<body>
    <!-- Primera tabla -->
    <div class="boleta page-break">


        <table style="width: 800px;">
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
                    <td colspan="3">
                        <img src="{{ public_path('images/ems.png') }}" alt="" width="150" height="50"><br>
                        <span style="font-size: 8px; font-weight: bold;">AGENCIA BOLIVIANA DE CORREOS</span>
                    </td>
                    <td colspan="3" rowspan="2" style="text-align: center; vertical-align: middle;">
                        <div style="display: inline-block; text-align: center;">
                            <div style="margin-bottom: 5px;">
                                {!! DNS1D::getBarcodeHTML($codigo, 'C128', 1.0, 40) !!}
                            </div>
                            <span style="font-size: 16px; font-weight: bold;">{{ $codigo }}</span>
                        </div>
                    </td>



                    <td rowspan="8" style="text-align:center;font-size:7px;vertical-align:middle;">
                        <!-- QR de rastreo existente -->
{!! QrCode::format('svg')->size(60)->margin(0)->generate($qrLink) !!}
                        Rastreo QR.<br>
                        correos.gob.bo:8000
                        <hr style="border:0;border-top:1px dotted #000;margin:4px 0;">

                        <!-- 🔽 NUEVO QR DE VISITA -->
                        {!! QrCode::format('svg')->size(60)->margin(0)->generate($qrWeb ?? 'https://correos.gob.bo/') !!}

                        <span style="font-size:8px;">Visítanos aquí</span><br>
                        correos.gob.bo
                    </td>


                </tr>
                <tr>
                    <td>OF. ORIGEN: <br>
                        <div style="text-align: right;">{{ $origen }}</div>
                    </td>
                    <td>OF. DESTINO: <br>
                        <div style="text-align: right;">{{ $ciudad }}</div>
                    </td>
                    <td></td>
                </tr>
                <tr>
                    <td colspan="3" rowspan="2">
                        NOMBRE REMITENTE: <br>
                        <div style="text-align: right; font-size: 8px;">{{ $nombre_remitente }}</div>
                    </td>
                    <td colspan="3" rowspan="2">NOMBRE DESTINATARIO: <br>
                        <div style="text-align: right; font-size: 8px;">{{ $nombre_destinatario }}</div>
                    </td>
                </tr>
                <tr></tr>
                <tr>
                    <td colspan="3" rowspan="2">DIRECCIÓN Y TELÉFONO:
                        <div style="text-align: right; font-size: 8px;"><br>{{ $telefono_remitente }}</div>
                    </td>
                    <td colspan="3" rowspan="2">DIRECCIÓN Y TELÉFONO:<br>
                        <div style="text-align: right; font-size: 8px;">
                            {{ $direccion }}<br>{{ $telefono_destinatario }}</div>
                    </td>
                </tr>
                </tr>
                <tr></tr>
                <tr>
                    <td colspan="3">DESCRIPCIÓN:
                        <div style="text-align: justify; font-size: 8px; word-wrap: break-word; white-space: pre-line;">
                            @if (!empty($contenido))
                                {{ $contenido }}<br>
                            @endif
                            DESTINO: {{ $destino }}
                        </div>
                    </td>
                    <td rowspan="2" style="vertical-align: top;">
                        {{ Auth::user()->name }}:<br>
                    </td>
                    <td colspan="2" rowspan="2" style="vertical-align: top;">FIRMA :<br></td>
                </tr>

                <tr>
                    <td>FECHA Y HORA:<br>
                        <div style="text-align: right;">{{ \Carbon\Carbon::parse($fecha)->format('Y-m-d H:i:s') }}
                        </div>
                    </td>


                    <td>PESO:<br>
                        <div style="text-align: right;">{{ $peso }} kg</div>
                    </td>
                    <td>IMPORTE: <br>
                        <div style="text-align: right;">{{ $precio }}</div>
                    </td>
                </tr>
            </thead>
        </table>
        @if ($marcaAgua)
            <div class="watermark-local">{{ $marcaAgua }}</div>
        @endif
    </div>

    <!-- Añadimos un margen grande entre las tablas repetidas -->
    <div style="height: 10px; border-top: 1px dashed #000; margin: 10px 0;"></div>

    <div class="boleta page-break">

        <table style="width: 800px;">
            <colgroup>
                <col style="width: 87px">
                <col style="width: 90px">
                <col style="width: 80px">
                <col style="width: 86px">
                <col style="width: 150px"> <!-- Agrandar la columna de DESCRIPCIÓN -->
                <col style="width: 90px"> <!-- Reducir DIRECCIÓN Y TELÉFONO -->
                <col style="width: 20px">
                <col style="width: 125px">
            </colgroup>
            <thead>
                <tr>
                    <td colspan="3">
                        <img src="{{ public_path('images/ems.png') }}" alt="" width="150"
                            height="50"><br>
                        <span style="font-size: 8px; font-weight: bold;">AGENCIA BOLIVIANA DE CORREOS</span>
                    </td>
                    <td colspan="3" rowspan="2" style="text-align: center; vertical-align: middle;">
                        <div style="display: inline-block; text-align: center;">
                            <div style="margin-bottom: 5px;">
                                {!! DNS1D::getBarcodeHTML($codigo, 'C128', 1.0, 40) !!}
                            </div>
                            <span style="font-size: 16px; font-weight: bold;">{{ $codigo }}</span>
                        </div>
                    </td>



                    <td rowspan="8" style="text-align:center;font-size:7px;vertical-align:middle;">
                        <!-- QR de rastreo existente -->
                        <img src="data:image/svg+xml;base64, {!! base64_encode(QrCode::format('svg')->size(60)->margin(0)->generate($qrLink)) !!}" alt="QR Rastreo"><br>
                        Rastreo QR.<br>
                        correos.gob.bo:8000
                        <hr style="border:0;border-top:1px dotted #000;margin:4px 0;">

                        <!-- 🔽 NUEVO QR DE VISITA -->
                        <img src="data:image/svg+xml;base64, {!! base64_encode(
                            QrCode::format('svg')->size(60)->margin(0)->generate($qrWeb ?? 'https://correos.gob.bo/'),
                        ) !!}" alt="QR Web"><br>
                        <span style="font-size:8px;">Visítanos aquí</span><br>
                        correos.gob.bo
                    </td>

                </tr>
                <tr>
                    <td>OF. ORIGEN: <br>
                        <div style="text-align: right;">{{ $origen }}</div>
                    </td>
                    <td>OF. DESTINO: <br>
                        <div style="text-align: right;">{{ $ciudad }}</div>
                    </td>
                    <td></td>
                </tr>
                <tr>
                    <td colspan="3" rowspan="2">
                        NOMBRE REMITENTE: <br>
                        <div style="text-align: right; font-size: 8px;">{{ $nombre_remitente }}</div>
                    </td>
                    <td colspan="3" rowspan="2">NOMBRE DESTINATARIO: <br>
                        <div style="text-align: right; font-size: 8px;">{{ $nombre_destinatario }}</div>
                    </td>
                </tr>
                <tr></tr>
                <tr>
                    <td colspan="3" rowspan="2">DIRECCIÓN Y TELÉFONO:
                        <div style="text-align: right; font-size: 8px;"><br>{{ $telefono_remitente }}</div>
                    </td>
                    <td colspan="3" rowspan="2">DIRECCIÓN Y TELÉFONO:<br>
                        <div style="text-align: right; font-size: 8px;">
                            {{ $direccion }}<br>{{ $telefono_destinatario }}</div>
                    </td>
                </tr>
                </tr>
                <tr></tr>
                <tr>
                    <td colspan="3">DESCRIPCIÓN:
                        <div
                            style="text-align: justify; font-size: 8px; word-wrap: break-word; white-space: pre-line;">
                            @if (!empty($contenido))
                                {{ $contenido }}<br>
                            @endif
                            DESTINO: {{ $destino }}
                        </div>
                    </td>

                    <td rowspan="2" style="vertical-align: top;">
                        {{ Auth::user()->name }}:<br>
                    </td>
                    <td colspan="2" rowspan="2" style="vertical-align: top;">FIRMA :<br></td>
                </tr>

                <tr>
                    <td>FECHA Y HORA:<br>
                        <div style="text-align: right;">{{ \Carbon\Carbon::parse($fecha)->format('Y-m-d H:i:s') }}
                        </div>
                    </td>


                    <td>PESO:<br>
                        <div style="text-align: right;">{{ $peso }} kg</div>
                    </td>
                    <td>IMPORTE: <br>
                        <div style="text-align: right;">{{ $precio }}</div>
                    </td>
                </tr>
            </thead>
        </table>
        @if ($marcaAgua)
            <div class="watermark-local">{{ $marcaAgua }}</div>
        @endif
    </div>





    {{-- <div style="height: 10px; border-top: 1px dashed #000; margin: 10px 0;"></div>

  <div class="page-break">
        <table style="width: 800px;">
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
                    <td colspan="3">
                        <img src="{{ public_path('images/ems.png') }}" alt="" width="150" height="50"><br>
                        <span style="font-size: 8px; font-weight: bold;">AGENCIA BOLIVIANA DE CORREOS</span>
                    </td>
                    <td colspan="3" rowspan="2" style="text-align: center; vertical-align: middle;">
                        <div style="display: inline-block; text-align: center;">
                            <div style="margin-bottom: 5px;">
                                {!! DNS1D::getBarcodeHTML($codigo, 'C128', 1.0, 40) !!}
                            </div>
                            <span style="font-size: 16px; font-weight: bold;">{{ $codigo }}</span>
                        </div>
                    </td>
                    
                    
                    
                    <td rowspan="8" style="text-align: center; font-size: 7px; vertical-align: middle;">
                        <div style="text-align: center; margin: 0 auto;">
                            <img src="data:image/svg+xml;base64, {!! base64_encode(QrCode::format('svg')->size(60)->margin(0)->generate($qrLink)) !!}" alt="QR Code" style="display: block; margin: 0 auto;">
                            <p style="font-size: 10px; margin-top: 5px;">Rastreo QR.</p>
                            <p style="font-size: 10px; margin-top: 5px;">correos.gob.bo:8000</p>

                        </div>
                    </td>
                    
                </tr>
                <tr>
                    <td>OF. ORIGEN: <br>
                        <div style="text-align: right;">{{ $origen }}</div>
                    </td>
                    <td>OF. DESTINO: <br>
                        <div style="text-align: right;">{{ $ciudad }}</div>
                    </td>
                    <td></td>
                </tr>
                <tr>
                    <td colspan="3" rowspan="2">
                        NOMBRE REMITENTE: <br>
                        <div style="text-align: right; font-size: 8px;">{{ $nombre_remitente }}</div>
                    </td>
                    <td colspan="3" rowspan="2">NOMBRE DESTINATARIO: <br>
                        <div style="text-align: right; font-size: 8px;">{{ $nombre_destinatario }}</div>
                    </td>
                </tr>
                <tr></tr>
                <tr>
                    <td colspan="3" rowspan="2">DIRECCIÓN Y TELÉFONO:
                        <div style="text-align: right; font-size: 8px;"><br>{{ $telefono_remitente }}</div>
                    </td>
                    <td colspan="3" rowspan="2">DIRECCIÓN Y TELÉFONO:<br>
                        <div style="text-align: right; font-size: 8px;">
                            {{ $direccion }}<br>{{ $telefono_destinatario }}</div>
                    </td>
                </tr>
                </tr>
                <tr></tr>
                <tr>
                    <td colspan="3">DESCRIPCIÓN:
                        <div style="text-align: justify; font-size: 8px; word-wrap: break-word; white-space: pre-line;">
                            @if (!empty($contenido))
                                {{ $contenido }}
                            @endif
                        </div>
                    </td>
                    <td rowspan="2" style="vertical-align: top;">
                        {{ Auth::user()->name }}:<br>
                    </td>
                    <td colspan="2" rowspan="2" style="vertical-align: top;">FIRMA :<br></td>
                </tr>
                
                <tr>
                    <td>FECHA:<br>
                        <div style="text-align: right;">{{ date('Y-m-d') }}</div>
                    </td>
                  
                    <td>PESO:<br>
                        <div style="text-align: right;">{{ $peso }} kg</div>
                    </td>
                    <td>IMPORTE: <br>
                        <div style="text-align: right;">{{ $precio }}</div>
                    </td>
                </tr>
            </thead>
        </table>
    </div>
    <div style="height: 10px; border-top: 1px dashed #000; margin: 10px 0;"></div>

    <div class="page-break">
        <table style="width: 800px;">
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
                    <td colspan="3">
                        <img src="{{ public_path('images/ems.png') }}" alt="" width="150" height="50"><br>
                        <span style="font-size: 8px; font-weight: bold;">AGENCIA BOLIVIANA DE CORREOS</span>
                    </td>
                    <td colspan="3" rowspan="2" style="text-align: center; vertical-align: middle;">
                        <div style="display: inline-block; text-align: center;">
                            <div style="margin-bottom: 5px;">
                                {!! DNS1D::getBarcodeHTML($codigo, 'C128', 1.0, 40) !!}
                            </div>
                            <span style="font-size: 16px; font-weight: bold;">{{ $codigo }}</span>
                        </div>
                    </td>
                    
                    
                    
                    <td rowspan="8" style="text-align: center; font-size: 7px; vertical-align: middle;">
                        <div style="text-align: center; margin: 0 auto;">
                            <img src="data:image/svg+xml;base64, {!! base64_encode(QrCode::format('svg')->size(60)->margin(0)->generate($qrLink)) !!}" alt="QR Code" style="display: block; margin: 0 auto;">
                            <p style="font-size: 10px; margin-top: 5px;">Rastreo QR.</p>
                            <p style="font-size: 10px; margin-top: 5px;">correos.gob.bo:8000</p>

                        </div>
                    </td>
                    


                </tr>
                <tr>
                    <td>OF. ORIGEN: <br>
                        <div style="text-align: right;">{{ $origen }}</div>
                    </td>
                    <td>OF. DESTINO: <br>
                        <div style="text-align: right;">{{ $ciudad }}</div>
                    </td>
                    <td></td>
                </tr>
                <tr>
                    <td colspan="3" rowspan="2">
                        NOMBRE REMITENTE: <br>
                        <div style="text-align: right; font-size: 8px;">{{ $nombre_remitente }}</div>
                    </td>
                    <td colspan="3" rowspan="2">NOMBRE DESTINATARIO: <br>
                        <div style="text-align: right; font-size: 8px;">{{ $nombre_destinatario }}</div>
                    </td>
                </tr>
                <tr></tr>
                <tr>
                    <td colspan="3" rowspan="2">DIRECCIÓN Y TELÉFONO:
                        <div style="text-align: right; font-size: 8px;"><br>{{ $telefono_remitente }}</div>
                    </td>
                    <td colspan="3" rowspan="2">DIRECCIÓN Y TELÉFONO:<br>
                        <div style="text-align: right; font-size: 8px;">
                            {{ $direccion }}<br>{{ $telefono_destinatario }}</div>
                    </td>
                </tr>
                </tr>
                <tr></tr>
                <tr>
                    <td colspan="3">DESCRIPCIÓN:
                        <div style="text-align: justify; font-size: 8px; word-wrap: break-word; white-space: pre-line;">
                            @if (!empty($contenido))
                                {{ $contenido }}
                            @endif
                        </div>
                    </td>
                    <td rowspan="2" style="vertical-align: top;">
                        {{ Auth::user()->name }}:<br>
                    </td>
                    <td colspan="2" rowspan="2" style="vertical-align: top;">FIRMA :<br></td>
                </tr>
                
                <tr>
                    <td>FECHA:<br>
                        <div style="text-align: right;">{{ date('Y-m-d') }}</div>
                    </td>
                  
                    <td>PESO:<br>
                        <div style="text-align: right;">{{ $peso }} kg</div>
                    </td>
                    <td>IMPORTE: <br>
                        <div style="text-align: right;">{{ $precio }}</div>
                    </td>
                </tr>
            </thead>
        </table>
    </div> --}}

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

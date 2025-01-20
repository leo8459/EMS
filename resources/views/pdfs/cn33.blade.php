<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Manifiesto CN-33</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
        }
        .header {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            position: relative;
        }
        .header td {
            border: 1px solid #333;
            padding: 5px;
        }
        .header .title {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
        }
        .header .sub-header {
            text-align: center;
        }
        .header .field {
            font-weight: bold;
        }
        .header .logo {
            position: absolute;
            top: -10px;
            left: 10px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .table th, .table td {
            border: 1px solid #333;
            padding: 5px;
            text-align: center;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        {{-- Imagen AGBCAzul en la esquina superior izquierda --}}
        <img src="{{ public_path('images/ems.png') }}" alt="" width="150" height="50"><br>
        <table style="width: 100%; border-collapse: collapse; table-layout: fixed;">
            <tr>
                <td colspan="4" class="title" style="text-align: center;">POSTAL DESIGNATED OPERATOR - EMS</td>
                <td rowspan="3" style="text-align: center; vertical-align: middle; width: 25%;">
                    @if (!empty($currentManifiesto))
                        {{-- Genera el código de barras con tamaño personalizado y centrado --}}
                        <div style="display: flex; flex-direction: column; align-items: center; justify-content: center;">
                            {!! DNS1D::getBarcodeHTML($currentManifiesto, 'C128', 1.5, 40) !!}
                            <p>{{ $currentManifiesto }}</p>
                        </div>
                    @else
                        <p>No Manifiesto</p>
                    @endif
                </td>
            </tr>
            <tr>
                <td colspan="2" class="sub-header" style="text-align: center;">BO-BOLIVIA</td>
                <td colspan="2" class="sub-header" style="text-align: center;">CN-33</td>
            </tr>
            <tr>
                <td class="field" style="width: 15%;">Office of Origin:</td>
                <td style="width: 25%;">{{ $loggedInUserCity }}</td>
                <td class="field" style="width: 15%;">Office of Destination:</td>
                <td style="width: 25%;">
                    @if (!empty($reencaminamiento))
                        {{ $reencaminamiento }}
                    @else
                        {{ $destinationCity }}
                    @endif
                </td>
            </tr>
            <tr>
                <td class="field">DESPACHO:</td>
                <td>{{ $currentManifiesto }}</td>
                <td class="field">Día de Despacho:</td>
                <td>{{ now()->format('d/m/Y') }}</td>
                <td class="field">Hora:</td>
                <td style="text-align: right;">{{ now()->format('H:i') }}</td>
            </tr>
            <tr>
                <td class="field">PRIORITARIO:</td>
                <td>X</td>
                <td class="field">POR AÉREO:</td>
                <td>X</td>
                <td colspan="2">TRÁNSITO: {{ $destinationCity }}</td>
            </tr>
        </table>
    </div>
    
    

    <table class="table">
        <thead>
            <tr>
                <th>ENVIO</th>
                <th>ORIG</th>
                <th>DEST</th>
                <th>CANT</th>
                <th>COR</th>
                <th>PESO</th>
                <th>REMITENTE</th>
                <th>ENDAS</th>
                <th>EMS</th>
                <th>OBSERVACIÓN</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $totalCantidad = 0;
                $totalPeso = 0;
            @endphp

            @foreach ($admisiones as $admision)
                @php
                    $peso = $admision->peso_ems ?: $admision->peso;
                    $totalCantidad += 1;
                    $totalPeso += $peso;
                @endphp
                <tr>
                    <td>{{ $admision->codigo }}</td>
                    <td>{{ $admision->origen }}</td>
                    <td>
                        @if (!empty($admision->reencaminamiento))
                            {{ $admision->reencaminamiento }}
                        @else
                            {{ $admision->ciudad }}
                        @endif
                    </td>
                    <td>1</td>
                    <td></td>
                    <td>
                        @if (!empty($admision->peso_regional))
                            {{ $admision->peso_regional }}
                        @elseif (!empty($admision->peso_ems))
                            {{ $admision->peso_ems }}
                        @else
                            {{ $admision->peso }}
                        @endif
                    </td>
                    <td>{{ $admision->nombre_remitente }}</td>
                    <td></td>
                    <td></td>
                    <td>{{ $admision->observacion }}</td>
                </tr>
                
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td><strong>TOTAL</strong></td>
                <td></td>
                <td></td>
                <td><strong>{{ $totalCantidad }}</strong></td>
                <td></td>
                <td><strong>{{ $totalPeso }}</strong></td>
                <td colspan="4"></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <table style="width: 100%; margin-top: 50px; text-align: center; border-collapse: collapse;">
            <tr>
                <td style="border: 1px solid #333; padding: 10px; text-align: left; width: 30%;">
                    <strong>Dispatching office of exchange</strong><br>
                    {{ $loggedInUserCity }}<br>
                    <strong>Signature</strong><br>
                    {{ Auth::user()->name }}<br>
                    Salidas Internacionales
                </td>
                <td style="border: 1px solid #333; padding: 10px; width: 30%;">
                    <strong>The official of the carrier of airport</strong><br>
                    Date and signature
                </td>
                <td style="border: 1px solid #333; padding: 10px; width: 30%;">
                    <strong>Office of exchange of destination</strong><br>
                    Date and signature
                </td>
            </tr>
        </table>
    </div>
</body>
</html>


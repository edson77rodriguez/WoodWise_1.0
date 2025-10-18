<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Parcela - {{ $parcela->nom_parcela }}</title>
    <style>
        /* Estilos base con colores directos (sin variables CSS) */
        body {
            font-family: Arial, sans-serif;
            color: #333333;
            line-height: 1.4;
            padding: 25px;
            margin: 0;
        }

        /* Encabezado profesional */
        .header {
            text-align: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #4a7c30; /* Verde WoodWise */
        }

        .title {
            color: #1a3a16; /* Verde oscuro WoodWise */
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .subtitle {
            color: #4a3c27; /* Marrón WoodWise */
            font-size: 13px;
        }

        /* Secciones organizadas */
        .section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }

        .section-title {
            background-color: #1a3a16; /* Verde oscuro WoodWise */
            color: white;
            padding: 8px 12px;
            font-weight: bold;
            border-radius: 4px 4px 0 0;
            margin-bottom: 0;
            font-size: 13px;
        }

        /* Tablas profesionales */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 12px;
            border: 1px solid #dddddd;
        }

        .info-table th {
            background-color: #1a3a16; /* Verde oscuro WoodWise */
            color: white;
            text-align: left;
            padding: 8px 10px;
            font-weight: bold;
            border-right: 1px solid #2a4b21;
        }

        .info-table td {
            padding: 7px 10px;
            border-bottom: 1px solid #eeeeee;
            border-right: 1px solid #eeeeee;
        }

        .info-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .info-table tr:last-child td {
            border-bottom: none;
        }

        /* Pie de página profesional */
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666666;
            padding-top: 10px;
            border-top: 1px solid #dddddd;
        }

        /* Utilidades */
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-bold { font-weight: bold; }
        .mb-3 { margin-bottom: 15px; }
        .total-row {
            background-color: #f0f7ec !important; /* Verde claro */
            font-weight: bold;
        }

        /* Estilos para impresión */
        @page {
            size: A4;
            margin: 15mm;
        }

        @media print {
            body {
                padding: 0;
                font-size: 10pt;
            }

            .header {
                margin-bottom: 15px;
            }

            .section {
                margin-bottom: 15px;
            }

            .info-table {
                font-size: 9pt;
            }

            .footer {
                font-size: 8pt;
            }
        }
    </style>
</head>
<body>
<div class="header">
    <h1 class="title">Informe Técnico de Parcela Forestal</h1>
    <p class="subtitle">{{ $parcela->nom_parcela }} | Generado el {{ now()->format('d/m/Y') }}</p>
</div>

<div class="section">
    <div class="section-title">Datos Generales de la Parcela</div>
    <table class="info-table">
        <tr>
            <th width="20%">Identificación:</th>
            <td width="30%">{{ $parcela->id_parcela }}</td>
            <th width="20%">Nombre:</th>
            <td width="30%">{{ $parcela->nom_parcela }}</td>
        </tr>
        <tr>
            <th>Ubicación Geográfica:</th>
            <td colspan="3">{{ $parcela->ubicacion }}</td>
        </tr>
        <tr>
            <th>Área Total:</th>
            <td>{{ number_format($parcela->extension, 2) }} hectáreas</td>
            <th>Productor Responsable:</th>
            <td>{{ $parcela->productor ? $parcela->productor->persona->nom : 'No asignado' }}</td>
        </tr>
        <tr>
            <th>Total de Trozas:</th>
            <td>{{ $parcela->trozas_count }}</td>
            <th>Volumen Maderable Total:</th>
            <td>{{ number_format($parcela->volumen_maderable, 2) }} m³</td>
        </tr>
        <tr>
            <th>Densidad de Plantación:</th>
            <td>{{ number_format($parcela->trozas_count / $parcela->extension, 2) }} árboles/ha</td>
            <th>Técnico Responsable:</th>
            <td>{{ Auth::user()->persona->nom }} {{ Auth::user()->persona->ap }}</td>
        </tr>
    </table>
</div>

@if($parcela->trozas_count > 0)
    <div class="section">
        <div class="section-title">Inventario Detallado de Trozas</div>
        <table class="info-table">
            <thead>
            <tr>
                <th width="10%">ID Troza</th>
                <th width="20%">Especie</th>
                <th width="12%">Longitud (m)</th>
                <th width="12%">Diámetro (cm)</th>
                <th width="12%">Densidad</th>
                <th width="17%" class="text-right">Volumen (m³)</th>
                <th width="17%">Fecha Registro</th>
            </tr>
            </thead>
            <tbody>
            @foreach($parcela->trozas as $troza)
                <tr>
                    <td>TRZ-{{ $troza->id_troza }}</td>
                    <td>{{ $troza->especie->nom_cientifico ?? 'N/A' }}</td>
                    <td>{{ number_format($troza->longitud, 2) }}</td>
                    <td>{{ number_format($troza->diametro, 2) }}</td>
                    <td>{{ number_format($troza->densidad, 2) }}</td>
                    <td class="text-right">
                        {{ $troza->estimacion ? number_format($troza->estimacion->calculo, 4) : 'N/A' }}
                    </td>
                    <td>{{ $troza->created_at->format('d/m/Y') }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="5" class="text-right text-bold">Total Volumen Maderable:</td>
                <td class="text-right text-bold">{{ number_format($parcela->volumen_maderable, 4) }} m³</td>
                <td></td>
            </tr>
            </tbody>
        </table>
    </div>
@endif

<div class="section">
    <div class="section-title">Resumen de Volúmenes</div>
    <table class="info-table">
        <thead>
        <tr>
            <th>Concepto</th>
            <th class="text-right">Valor</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>Número Total de Trozas</td>
            <td class="text-right">{{ $parcela->trozas_count }}</td>
        </tr>
        <tr>
            <td>Volumen Maderable Total</td>
            <td class="text-right">{{ number_format($parcela->volumen_maderable, 4) }} m³</td>
        </tr>
        <tr>
            <td>Volumen Promedio por Troza</td>
            <td class="text-right">
                {{ $parcela->trozas_count > 0 ? number_format($parcela->volumen_maderable / $parcela->trozas_count, 4) : 0 }} m³
            </td>
        </tr>
        </tbody>
    </table>
</div>

<div class="section">
    <div class="section-title">Biomasa y Carbono</div>
    <table class="info-table">
        <thead>
        <tr>
            <th>Concepto</th>
            <th class="text-right">Valor por Troza (kg)</th>
            <th class="text-right">Total Parcela (kg)</th>
        </tr>
        </thead>
        <tbody>
        @foreach($parcela->trozas as $troza)
            <tr>
                <td>TRZ-{{ $troza->id_troza }} - {{ $troza->especie->nom_cientifico ?? 'N/A' }}</td>
                <td class="text-right">
                    @if($troza->estimacion)
                        {{ number_format($troza->estimacion->calculo * $troza->densidad * 1000, 2) }}
                    @else
                        N/A
                    @endif
                </td>
                <td class="text-right">
                    @if($troza->estimacion)
                        {{ number_format($troza->estimacion->calculo * $troza->densidad * 1000, 2) }}
                    @else
                        N/A
                    @endif
                </td>
            </tr>
        @endforeach
        <tr class="total-row">
            <td class="text-bold">Total Biomasa</td>
            <td class="text-right text-bold">
                @php
                    $totalBiomasa = 0;
                    foreach($parcela->trozas as $troza) {
                        if($troza->estimacion) {
                            $totalBiomasa += $troza->estimacion->calculo * $troza->densidad * 1000;
                        }
                    }
                @endphp
                {{ number_format($totalBiomasa, 2) }} kg
            </td>
            <td class="text-right text-bold">{{ number_format($totalBiomasa, 2) }} kg</td>
        </tr>
        <tr class="total-row">
            <td class="text-bold">Carbono Almacenado (50% biomasa)</td>
            <td class="text-right text-bold">{{ number_format($totalBiomasa * 0.5, 2) }} kg</td>
            <td class="text-right text-bold">{{ number_format($totalBiomasa * 0.5, 2) }} kg</td>
        </tr>
        </tbody>
    </table>
</div>

<div class="section">
    <div class="section-title">Densidad y Totales por Parcela</div>
    <table class="info-table">
        <thead>
        <tr>
            <th>Concepto</th>
            <th class="text-right">Valor</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>Área Total de la Parcela</td>
            <td class="text-right">{{ number_format($parcela->extension, 2) }} ha</td>
        </tr>
        <tr>
            <td>Total de Árboles en la Parcela</td>
            <td class="text-right">{{ $parcela->trozas_count }}</td>
        </tr>
        <tr>
            <td>Densidad de Plantación</td>
            <td class="text-right">{{ number_format($parcela->trozas_count / $parcela->extension, 2) }} árboles/ha</td>
        </tr>
        <tr class="total-row">
            <td class="text-bold">Volumen Maderable Total</td>
            <td class="text-right text-bold">{{ number_format($parcela->volumen_maderable, 4) }} m³</td>
        </tr>
        <tr class="total-row">
            <td class="text-bold">Biomasa Total</td>
            <td class="text-right text-bold">
                @php
                    $totalBiomasa = 0;
                    foreach($parcela->trozas as $troza) {
                        if($troza->estimacion) {
                            $totalBiomasa += $troza->estimacion->calculo * $troza->densidad * 1000;
                        }
                    }
                @endphp
                {{ number_format($totalBiomasa, 2) }} kg
            </td>
        </tr>
        <tr class="total-row">
            <td class="text-bold">Carbono Total Almacenado</td>
            <td class="text-right text-bold">{{ number_format($totalBiomasa * 0.5, 2) }} kg</td>
        </tr>
        </tbody>
    </table>
</div>

<div class="footer">
    <p class="text-bold">WoodWise - Sistema de Gestión Forestal Sostenible</p>
    <p>© {{ date('Y') }} WoodWise Technologies. Todos los derechos reservados.</p>
    <p>Documento generado automáticamente el {{ now()->format('d/m/Y H:i') }}</p>
</div>
</body>
</html>

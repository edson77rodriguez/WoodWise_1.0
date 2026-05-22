<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Informe SIGMAD</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #1f2937;
            margin: 24px;
        }
        h1, h2 {
            margin: 0 0 8px 0;
        }
        h1 { font-size: 20px; }
        h2 { font-size: 14px; margin-top: 18px; }
        .muted { color: #6b7280; }
        .section { margin-top: 16px; }
        .pill {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            background: #e5f3ea;
            color: #0f5132;
            font-size: 11px;
            margin-left: 8px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        th, td {
            border: 1px solid #e5e7eb;
            padding: 6px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background: #f3f4f6;
        }
        ul { margin: 6px 0 0 16px; }
        .note { font-size: 11px; color: #6b7280; margin-top: 8px; }
    </style>
</head>
<body>
    <h1>Informe SIGMAD</h1>
    <div class="muted">Generado: {{ $fecha }}</div>

    <div class="section">
        <h2>Perfil</h2>
        <div><strong>Usuario:</strong> {{ trim(($persona->nom ?? '') . ' ' . ($persona->ap ?? '') . ' ' . ($persona->am ?? '')) }}</div>
        <div><strong>Telefono:</strong> {{ $persona->telefono }}</div>
        <div><strong>Rol:</strong> {{ $rol }} <span class="pill">Acceso activo</span></div>
    </div>

    <div class="section">
        <h2>Parcelas asignadas</h2>
        @if($parcelas->isEmpty())
            <div class="muted">No hay parcelas asignadas.</div>
        @else
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Parcela</th>
                        <th>Ubicacion</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($parcelas as $parcela)
                        <tr>
                            <td>{{ $parcela->id_parcela }}</td>
                            <td>{{ $parcela->nom_parcela }}</td>
                            <td>{{ $parcela->ubicacion ?? 'Sin dato' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="section">
        <h2>Especies disponibles</h2>
        @if($especies->isEmpty())
            <div class="muted">No hay especies registradas.</div>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Nombre comun</th>
                        <th>Nombre cientifico</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($especies as $especie)
                        <tr>
                            <td>{{ $especie->nom_comun }}</td>
                            <td>{{ $especie->nom_cientifico ?? 'Sin dato' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="section">
        <h2>Medidas recomendadas</h2>
        <h3>Arboles</h3>
        <ul>
            <li>DAP (diametro a la altura del pecho): medir a 1.30 m del suelo.</li>
            <li>Altura total: altura completa del arbol desde la base hasta la punta.</li>
        </ul>

        <h3>Trozas</h3>
        <ul>
            <li>Diametro superior: extremo delgado.</li>
            <li>Longitud: largo total de la troza.</li>
            <li>Densidad: valor usado para estimaciones.</li>
            <li>Opcional: diametro inferior y diametro medio.</li>
        </ul>
        <div class="note">Usa la plantilla oficial para evitar errores de formato.</div>
    </div>
</body>
</html>

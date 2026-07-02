<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte Impacto Ambiental</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h1 { font-size: 18px; }
        .section { margin-bottom: 12px; }
        .kv { display:flex; gap:8px }
        .small { font-size: 11px; color:#555 }
        .rec { margin-bottom: 10px; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        .rec h3 { margin: 0 0 4px 0; font-size: 13px; }
        .rec-meta { font-size: 11px; color: #666; margin-bottom: 4px; }
        .rec ul { margin: 4px 0 0 16px; padding: 0; }
    </style>
</head>
<body>
    <h1>Reporte: Impacto Ambiental</h1>
    <div class="small">Generado: {{ $fecha }}</div>

    <div class="section">
        <strong>Usuario:</strong> {{ $impacto['usuario'] ?? ($persona->nom ?? '') }}<br>
        <strong>Alcance:</strong> {{ $impacto['filtro']['modo'] ?? 'todas' }}
    </div>

    <div class="section">
        <h2>Métricas principales</h2>
        <div class="kv"><div><strong>Árboles:</strong></div><div>{{ $impacto['totales']['arboles'] ?? 0 }}</div></div>
        <div class="kv"><div><strong>Trozas:</strong></div><div>{{ $impacto['totales']['trozas'] ?? 0 }}</div></div>
        <div class="kv"><div><strong>Biomasa total (kg):</strong></div><div>{{ number_format($impacto['totales']['biomasa_total'] ?? 0, 2) }}</div></div>
        <div class="kv"><div><strong>Carbono total (kg):</strong></div><div>{{ number_format($impacto['totales']['carbono_total'] ?? 0, 2) }}</div></div>
    </div>

    <div class="section">
        <h2>Alertas</h2>
        @if(!empty($impacto['alertas']))
            <ul>
            @foreach($impacto['alertas'] as $a)
                <li>{{ $a }}</li>
            @endforeach
            </ul>
        @else
            <div>No se detectaron alertas.</div>
        @endif
    </div>

    <div class="section">
        <h2>Recomendaciones</h2>
        @if(!empty($impacto['recomendaciones_detalladas']))
            @foreach($impacto['recomendaciones_detalladas'] as $r)
                <div class="rec">
                    <h3>{{ $r['title'] ?? 'Recomendación' }}</h3>
                    <div class="rec-meta">
                        Prioridad: {{ $r['priority'] ?? 'media' }} |
                        Métrica: {{ $r['metric_impacted'] ?? 'no especificada' }}
                    </div>
                    <div>{{ $r['recommendation'] ?? '' }}</div>
                    @if(!empty($r['actions']))
                        <strong>Acciones sugeridas:</strong>
                        <ul>
                            @foreach($r['actions'] as $action)
                                <li>{{ $action }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endforeach
        @elseif(!empty($impacto['recomendaciones']))
            <ul>
                @foreach($impacto['recomendaciones'] as $r)
                    <li>{{ $r }}</li>
                @endforeach
            </ul>
        @else
            <div>No hay recomendaciones específicas.</div>
        @endif
    </div>

    @if(!empty($impacto['analisis_ia']['resumen']))
    <div class="section">
        <h2>Análisis inteligente</h2>
        <div>{{ $impacto['analisis_ia']['resumen'] }}</div>
    </div>
    @endif

    <div class="small">SIGMAD - Sistema Inteligente de Gestión Maderable</div>
</body>
</html>
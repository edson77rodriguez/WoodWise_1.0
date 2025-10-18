<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Parcela;
use App\Models\Troza;
use App\Models\Estimacion;
use Illuminate\Http\Request;
use PDF;
use Carbon\Carbon;

class ProductorDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (Auth::user()->persona->rol->nom_rol !== 'Productor') {
                abort(403, 'Acceso exclusivo para Productores.');
            }
            return $next($request);
        });
    }

    public function index()
    {
        $productor = Auth::user()->productor;

        // CORRECCIÓN: Precargamos las relaciones completas que la vista necesita
        $parcelas = $productor->parcelas()
            ->with(['arboles', 'estimaciones.tipoEstimacion'])
            ->withCount('arboles')
            ->orderBy('nom_parcela')
            ->get();

        // Este cálculo ahora es más simple
        $stats = [
            'total_parcelas' => $parcelas->count(),
            'total_trozas' => $parcelas->sum(function($p) { return $p->trozas->count(); }), // Asumiendo que `trozas` también está en el modelo Parcela
            'total_estimaciones' => $parcelas->sum(function($p) { return $p->estimaciones->count(); }),
        ];

        return view('P.index', compact('parcelas', 'stats'));
    }

   // En tu ProductorDashboardController.php

public function exportarGeneral()
{
    $productor = Auth::user()->productor;

    if (!$productor || $productor->parcelas->isEmpty()) {
        return redirect()->back()->with('error', 'No hay datos para generar el reporte.');
    }

    // --- CORRECCIÓN EN LA CONSULTA ---
    // Cargamos las relaciones exactamente como la vista las necesita: Parcela -> Trozas -> Estimacion
    $parcelas = $productor->parcelas()
        ->with(['trozas.estimacion']) // Carga las trozas y, para cada troza, su estimación asociada
        ->withCount('trozas')
        ->get();
    
    $stats = [
        'total_parcelas' => $parcelas->count(),
        'total_trozas' => $parcelas->sum('trozas_count'),
        // El conteo de estimaciones se hace recorriendo las relaciones ya cargadas
        'total_estimaciones' => $parcelas->sum(function($parcela) {
            return $parcela->trozas->whereNotNull('estimacion')->count();
        }),
    ];
    
    // --- Lógica del Logo (ya está correcta) ---
    $path = public_path('img/woodwise.png');
    if (!file_exists($path)) {
         return redirect()->back()->with('error', 'No se encontró el archivo del logo');
    }
    $type = pathinfo($path, PATHINFO_EXTENSION);
    $data = file_get_contents($path);
    $logo_base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);

    $data = [
        'parcelas' => $parcelas,
        'fecha' => Carbon::now()->format('d/m/Y'),
        'titulo' => 'Reporte General del Productor',
        'stats' => $stats,
        'logo' => $logo_base64
    ];
    
    try {
        $pdf = PDF::loadView('P.pdf_export_general', $data)->setPaper('a4', 'landscape');
        return $pdf->download('reporte_general_' . now()->format('Y-m-d') . '.pdf');
    } catch (\Exception $e) {
        return redirect()->back()->with('error', 'Error al generar PDF: ' . $e->getMessage());
    }
}
    /**
     * Genera un PDF para una troza específica, asegurando que pertenezca al productor.
     */
    public function generarPdfTroza($id)
    {
        // Lógica de acceso mucho más simple y segura a través de relaciones
        $troza = Auth::user()->productor->trozas()->with('parcela.productor.persona')->findOrFail($id);

        $data = [
            'troza' => $troza,
            'fecha' => Carbon::now()->format('d/m/Y'),
        ];

        $pdf = Pdf::loadView('P.troza', $data);
        return $pdf->download('reporte_troza_' . $troza->id_troza . '.pdf');
    }

    /**
     * Genera un PDF para una estimación específica, asegurando que pertenezca al productor.
     */
    public function generarPdfEstimacion($id)
    {
        // Lógica de acceso mucho más simple y segura a través de relaciones
        $estimacion = Auth::user()->productor->estimaciones()->with('troza.parcela')->findOrFail($id);

        $data = [
            'estimacion' => $estimacion,
            'fecha' => Carbon::now()->format('d/m/Y'),
        ];

        $pdf = Pdf::loadView('P.estimacion', $data);
        return $pdf->download('reporte_estimacion_' . $estimacion->id_estimacion . '.pdf');
    }

    /**
     * Método privado para calcular estadísticas y evitar repetir código.
     */
    private function _getProductorStats($parcelas)
    {
        return [
            'total_parcelas' => $parcelas->count(),
            'total_trozas' => $parcelas->sum('trozas_count'),
            'total_estimaciones' => $parcelas->sum('estimaciones_count'),
        ];
    }
}
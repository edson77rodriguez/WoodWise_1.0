<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Parcela;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MobileParcelaReportController extends Controller
{
    public function download(
        Request $request,
        int $id
    ) {
        // ============================================
        // 1. USUARIO AUTENTICADO
        // ============================================

        $user = $request->user()->load([
            'persona.rol',
            'persona.tecnico',
            'persona.productor',
        ]);

        $persona = $user->persona;

        if (!$persona || !$persona->rol) {
            return response()->json([
                'message' =>
                    'El usuario no tiene un perfil configurado correctamente.',
            ], 403);
        }

        $rol =
            $persona->rol->nom_rol;


        // ============================================
        // 2. PRODUCTOR
        // ============================================

        if ($rol === 'Productor') {

            $productor =
                $persona->productor;

            if (!$productor) {
                return response()->json([
                    'message' =>
                        'El usuario no tiene un perfil de productor configurado.',
                ], 403);
            }

            $parcela =
                Parcela::query()
                    ->where(
                        'id_parcela',
                        $id
                    )
                    ->where(
                        'id_productor',
                        $productor->id_productor
                    )
                    ->with([
                        'trozas.estimaciones.tipoEstimacion',
                        'trozas.especie',
                        'arboles.estimaciones.tipoEstimacion',
                        'arboles.especie',
                        'turnosCorta',
                        'productor.persona',
                    ])
                    ->first();

            if (!$parcela) {
                return response()->json([
                    'message' =>
                        'La parcela no existe o no pertenece al productor autenticado.',
                ], 404);
            }


            // Logo utilizado por el reporte web.
            $logoBase64 = '';

            if (extension_loaded('gd')) {

                $logoPath =
                    public_path(
                        'assets/images/SIGMAD.svg'
                    );

                if (file_exists($logoPath)) {

                    $logoBase64 =
                        'data:image/svg+xml;base64,'
                        . base64_encode(
                            file_get_contents(
                                $logoPath
                            )
                        );
                }
            }


            $data = [
                'parcela' =>
                    $parcela,

                'logo' =>
                    $logoBase64,

                'fecha' =>
                    Carbon::now()
                        ->format('d/m/Y H:i'),

                'productor' =>
                    $productor,
            ];


            $pdf =
                Pdf::loadView(
                    'P.pdf.parcela-v2',
                    $data
                )
                ->setPaper(
                    'letter',
                    'portrait'
                );


            $fileName =
                'Parcela_'
                . $parcela->nom_parcela
                . '_'
                . now()->format('Y-m-d')
                . '.pdf';


            return $pdf->download(
                $fileName
            );
        }


        // ============================================
        // 3. TÉCNICO
        // ============================================

        if ($rol === 'Tecnico') {

            $tecnico =
                $persona->tecnico;

            if (!$tecnico) {
                return response()->json([
                    'message' =>
                        'El usuario no tiene un perfil de técnico configurado.',
                ], 403);
            }


            /*
             * El Técnico solamente puede descargar
             * reportes de parcelas que tenga asignadas.
             */
            $autorizada =
                $tecnico
                    ->parcelas()
                    ->where(
                        'parcelas.id_parcela',
                        $id
                    )
                    ->exists();


            if (!$autorizada) {
                return response()->json([
                    'message' =>
                        'La parcela no existe o no está asignada a este técnico.',
                ], 404);
            }


            $parcela =
                Parcela::with([
                    'productor.persona',
                    'trozas.especie',
                    'trozas.estimaciones.tipoEstimacion',
                    'trozas.estimaciones.formula',
                    'arboles.especie',
                    'arboles.estimaciones1.tipoEstimacion',
                    'arboles.estimaciones1.formula',
                ])
                ->findOrFail(
                    $id
                );


            // ========================================
            // TOTALES TROZAS
            // ========================================

            $totalVolumenTrozas =
                $parcela
                    ->trozas
                    ->flatMap
                    ->estimaciones
                    ->sum('calculo');

            $totalBiomasaTrozas =
                $parcela
                    ->trozas
                    ->flatMap
                    ->estimaciones
                    ->sum('biomasa');

            $totalCarbonoTrozas =
                $parcela
                    ->trozas
                    ->flatMap
                    ->estimaciones
                    ->sum('carbono');


            // ========================================
            // TOTALES ÁRBOLES
            // ========================================

            $totalVolumenArboles =
                $parcela
                    ->arboles
                    ->flatMap
                    ->estimaciones1
                    ->sum('calculo');

            $totalBiomasaArboles =
                $parcela
                    ->arboles
                    ->flatMap
                    ->estimaciones1
                    ->sum('biomasa');

            $totalCarbonoArboles =
                $parcela
                    ->arboles
                    ->flatMap
                    ->estimaciones1
                    ->sum('carbono');


            // ========================================
            // TOTALES GENERALES
            // ========================================

            $totales = [
                'volumen' =>
                    $totalVolumenTrozas
                    +
                    $totalVolumenArboles,

                'biomasa' =>
                    $totalBiomasaTrozas
                    +
                    $totalBiomasaArboles,

                'carbono' =>
                    $totalCarbonoTrozas
                    +
                    $totalCarbonoArboles,

                'trozas' =>
                    $parcela->trozas->count(),

                'arboles' =>
                    $parcela->arboles->count(),

                'estimaciones' =>
                    $parcela
                        ->trozas
                        ->flatMap
                        ->estimaciones
                        ->count()
                    +
                    $parcela
                        ->arboles
                        ->flatMap
                        ->estimaciones1
                        ->count(),
            ];


            // ========================================
            // ESTADÍSTICAS
            // ========================================

            $estadisticas = [
                'volumen_trozas' =>
                    $totalVolumenTrozas,

                'volumen_arboles' =>
                    $totalVolumenArboles,

                'biomasa_trozas' =>
                    $totalBiomasaTrozas,

                'biomasa_arboles' =>
                    $totalBiomasaArboles,

                'carbono_trozas' =>
                    $totalCarbonoTrozas,

                'carbono_arboles' =>
                    $totalCarbonoArboles,

                'altura_promedio' =>
                    $parcela
                        ->arboles
                        ->avg('altura_total')
                    ?? 0,

                'dap_promedio' =>
                    $parcela
                        ->arboles
                        ->avg('diametro_pecho')
                    ?? 0,
            ];


            $pdf =
                Pdf::loadView(
                    'pdf.parcela-tecnico',
                    [
                        'parcela' =>
                            $parcela,

                        'totales' =>
                            $totales,

                        'estadisticas' =>
                            $estadisticas,

                        'tecnico' =>
                            $tecnico,

                        'fecha_generacion' =>
                            now(),
                    ]
                )
                ->setPaper(
                    'A4',
                    'portrait'
                );


            $fileName =
                'Reporte_Parcela_'
                . $parcela->nom_parcela
                . '_'
                . now()->format('Y-m-d')
                . '.pdf';


            return $pdf->download(
                $fileName
            );
        }


        // ============================================
        // 4. ROL NO SOPORTADO
        // ============================================

        return response()->json([
            'message' =>
                'El rol del usuario no puede generar reportes de parcela.',
        ], 403);
    }
}
<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Parcela;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MobileProductorReportController extends Controller
{
    public function download(Request $request)
    {
        // ============================================
        // 1. USUARIO AUTENTICADO
        // ============================================

        $user = $request->user()->load([
            'persona.rol',
            'persona.productor',
        ]);

        $persona = $user->persona;

        if (!$persona || !$persona->rol) {
            return response()->json([
                'message' =>
                    'El usuario no tiene un perfil configurado correctamente.',
            ], 403);
        }


        // ============================================
        // 2. SOLO PRODUCTOR
        // ============================================

        if ($persona->rol->nom_rol !== 'Productor') {
            return response()->json([
                'message' =>
                    'Solo los productores pueden generar el reporte general.',
            ], 403);
        }

        $productor = $persona->productor;

        if (!$productor) {
            return response()->json([
                'message' =>
                    'El usuario no tiene un perfil de productor configurado.',
            ], 403);
        }


        // ============================================
        // 3. OBTENER TODAS SUS PARCELAS
        // ============================================

        $parcelas =
            Parcela::where(
                'id_productor',
                $productor->id_productor
            )
            ->with([
                'trozas.estimaciones.tipoEstimacion',
                'arboles.estimaciones.tipoEstimacion',
                'turnosCorta',
            ])
            ->withCount([
                'trozas',
                'arboles',
            ])
            ->get();


        if ($parcelas->isEmpty()) {
            return response()->json([
                'message' =>
                    'El productor no tiene parcelas disponibles para generar el reporte.',
            ], 404);
        }


        // ============================================
        // 4. TOTALES GENERALES
        // ============================================

        $totalVolumen = 0;
        $totalBiomasa = 0;
        $totalCarbono = 0;


        foreach ($parcelas as $parcela) {

            // ----------------------------------------
            // TROZAS
            // ----------------------------------------

            foreach ($parcela->trozas as $troza) {

                $vol =
                    $troza
                        ->estimaciones
                        ->where(
                            'tipoEstimacion.desc_estimacion',
                            'Volumen'
                        )
                        ->first();

                if ($vol) {
                    $totalVolumen +=
                        $vol->calculo;
                }
            }


            // ----------------------------------------
            // ÁRBOLES
            // ----------------------------------------

            foreach ($parcela->arboles as $arbol) {

                $vol =
                    $arbol
                        ->estimaciones
                        ->where(
                            'tipoEstimacion.desc_estimacion',
                            'Volumen'
                        )
                        ->first();

                $bio =
                    $arbol
                        ->estimaciones
                        ->where(
                            'tipoEstimacion.desc_estimacion',
                            'Biomasa'
                        )
                        ->first();

                $car =
                    $arbol
                        ->estimaciones
                        ->where(
                            'tipoEstimacion.desc_estimacion',
                            'Carbono'
                        )
                        ->first();


                if ($vol) {
                    $totalVolumen +=
                        $vol->calculo;
                }

                if ($bio) {
                    $totalBiomasa +=
                        $bio->calculo;
                }

                if ($car) {
                    $totalCarbono +=
                        $car->calculo;
                }
            }
        }


        // ============================================
        // 5. LOGO
        // ============================================

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


        // ============================================
        // 6. ESTADÍSTICAS
        // ============================================

        $stats = [
            'total_parcelas' =>
                $parcelas->count(),

            'total_trozas' =>
                $parcelas->sum(
                    'trozas_count'
                ),

            'total_arboles' =>
                $parcelas->sum(
                    'arboles_count'
                ),

            'total_extension' =>
                $parcelas->sum(
                    'extension'
                ),

            'total_volumen' =>
                $totalVolumen,

            'total_biomasa' =>
                $totalBiomasa,

            'total_carbono' =>
                $totalCarbono,
        ];


        // ============================================
        // 7. DATOS PARA LA VISTA EXISTENTE
        // ============================================

        $data = [
            'parcelas' =>
                $parcelas,

            'productor' =>
                $productor,

            'stats' =>
                $stats,

            'logo' =>
                $logoBase64,

            'fecha' =>
                Carbon::now()
                    ->format('d/m/Y H:i'),
        ];


        // ============================================
        // 8. GENERAR PDF
        // ============================================

        $pdf =
            Pdf::loadView(
                'P.pdf.general-v2',
                $data
            )
            ->setPaper(
                'letter',
                'landscape'
            );


        $fileName =
            'Reporte_General_'
            . now()->format('Y-m-d')
            . '.pdf';


        return $pdf->download(
            $fileName
        );
    }
}
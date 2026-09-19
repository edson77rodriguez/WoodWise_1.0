<?php

namespace App\Http\Controllers;

use App\Models\Mosaic;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MosaicController extends Controller
{
    /**
     * Mostrar ortomosaicos.
     */
    public function index()
    {
        $projects = Project::where('status', 'active')
            ->orderBy('name')
            ->get();

        $mosaics = Mosaic::with('project')
            ->latest()
            ->get();

        return view('mosaics.index', compact(
            'projects',
            'mosaics'
        ));
    }


    /**
     * Crear registro y generar URL temporal R2.
     */
    public function presign(Request $request)
    {
        $validated = $request->validate([
            'project_id' => [
                'required',
                'exists:projects,id'
            ],

            'filename' => [
                'required',
                'string',
                'max:255'
            ],

            'size_bytes' => [
                'required',
                'integer',
                'min:1'
            ],

            'mime_type' => [
                'nullable',
                'string',
                'max:100'
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | Validar extensión
        |--------------------------------------------------------------------------
        */

        $extension = strtolower(
            pathinfo(
                $validated['filename'],
                PATHINFO_EXTENSION
            )
        );

        if (!in_array($extension, ['tif', 'tiff'])) {
            return response()->json([
                'message' =>
                    'Solo se permiten archivos GeoTIFF .tif o .tiff.'
            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | Proyecto
        |--------------------------------------------------------------------------
        */

        $project = Project::findOrFail(
            $validated['project_id']
        );


        /*
        |--------------------------------------------------------------------------
        | UUID del mosaico
        |--------------------------------------------------------------------------
        */

        $mosaicUuid = (string) Str::uuid();


        /*
        |--------------------------------------------------------------------------
        | Clave del objeto en R2
        |--------------------------------------------------------------------------
        */

        $objectKey =
            'projects/'
            . $project->uuid
            . '/mosaics/'
            . $mosaicUuid
            . '/original/orthomosaic.'
            . $extension;


        /*
        |--------------------------------------------------------------------------
        | Registro inicial
        |--------------------------------------------------------------------------
        */

        $mosaic = Mosaic::create([

            'uuid' =>
                $mosaicUuid,

            'project_id' =>
                $project->id,

            'original_name' =>
                $validated['filename'],

            'object_key' =>
                $objectKey,

            'size_bytes' =>
                $validated['size_bytes'],

            'mime_type' =>
                $validated['mime_type']
                    ?? 'image/tiff',

            'extension' =>
                $extension,

            'status' =>
                'uploading',
        ]);


        /*
        |--------------------------------------------------------------------------
        | URL temporal de subida
        |--------------------------------------------------------------------------
        |
        | El archivo va directamente:
        |
        | Navegador -> R2
        |
        | NO:
        |
        | Navegador -> Laravel -> R2
        |
        */

        try {

            $upload = Storage::disk('r2')
                ->temporaryUploadUrl(
                    $objectKey,
                    now()->addMinutes(30)
                );

        } catch (\Throwable $e) {

            $mosaic->delete();

            return response()->json([
                'message' =>
                    'No se pudo generar la URL de subida.',

                'error' =>
                    $e->getMessage(),
            ], 500);
        }


        return response()->json([

            'status' =>
                'ok',

            'mosaic_uuid' =>
                $mosaic->uuid,

            'object_key' =>
                $objectKey,

            'upload_url' =>
                $upload['url'],

            'headers' =>
                $upload['headers'] ?? [],
        ]);
    }


    /**
     * Confirmar que la subida terminó.
     */
    public function complete(Request $request)
    {
        $validated = $request->validate([
            'mosaic_uuid' => [
                'required',
                'uuid'
            ],
        ]);


        $mosaic = Mosaic::where(
            'uuid',
            $validated['mosaic_uuid']
        )->firstOrFail();


        /*
        |--------------------------------------------------------------------------
        | Verificar físicamente en R2
        |--------------------------------------------------------------------------
        */

        if (
            !Storage::disk('r2')
                ->exists($mosaic->object_key)
        ) {

            return response()->json([
                'message' =>
                    'El archivo todavía no existe en R2.'
            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | Tamaño real
        |--------------------------------------------------------------------------
        */

        $size = Storage::disk('r2')
            ->size($mosaic->object_key);


        /*
        |--------------------------------------------------------------------------
        | Actualizar estado
        |--------------------------------------------------------------------------
        */

        $mosaic->update([

            'size_bytes' =>
                $size,

            'status' =>
                'uploaded',

            'uploaded_at' =>
                now(),
        ]);


        return response()->json([

            'status' =>
                'ok',

            'mosaic_uuid' =>
                $mosaic->uuid,

            'size_bytes' =>
                $size,

            'message' =>
                'Ortomosaico almacenado correctamente.'
        ]);
    }
}
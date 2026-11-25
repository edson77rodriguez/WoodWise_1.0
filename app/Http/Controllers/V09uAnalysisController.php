<?php

namespace App\Http\Controllers;

use App\Models\AnalysisJob;
use App\Models\Mosaic;
use App\Services\V09uAnalysisService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

/** Una vista independiente: no cambia el botón histórico V0.7E. */
class V09uAnalysisController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $mosaics = Mosaic::query()->with('project')
            ->where('status', 'ready')
            ->whereHas('project', fn ($q) => $q->where('user_id', $user->id))
            ->orderByDesc('id')->get();
        $jobs = AnalysisJob::query()->with('mosaic.project')
            ->where('analysis_type', 'wall_to_wall_tree_crown')
            ->where('parameters->pipeline_version', 'V0.9U')
            ->whereHas('mosaic.project', fn ($q) => $q->where('user_id', $user->id))
            ->orderByDesc('id')->limit(20)->get();
        return view('analysis.v09u', compact('mosaics', 'jobs'));
    }

    public function start(Request $request, Mosaic $mosaic, V09uAnalysisService $service)
    {
        $mosaic->loadMissing('project');
        abort_unless($mosaic->project && (int) $mosaic->project->user_id === (int) $request->user()->id, 403);
        try {
            $job = $service->start($mosaic);
            return response()->json([
                'status' => 'processing', 'analysis_uuid' => $job->uuid,
                'status_url' => route('analysis.v09u.status', ['analysisUuid' => $job->uuid]),
            ], 202);
        } catch (Throwable $e) {
            Log::warning('SIGMAD V0.9U: inicio fallido/no confirmado', [
                'mosaic_uuid' => $mosaic->uuid, 'message' => $e->getMessage(),
            ]);
            return response()->json(['message' => $e->getMessage()], 409);
        }
    }

    public function status(Request $request, string $analysisUuid, V09uAnalysisService $service)
    {
        $job = AnalysisJob::with('mosaic.project', 'artifacts')
            ->where('uuid', $analysisUuid)->firstOrFail();
        abort_unless($job->mosaic && $job->mosaic->project
            && (int) $job->mosaic->project->user_id === (int) $request->user()->id, 403);
        abort_unless(data_get($job->parameters, 'pipeline_version') === 'V0.9U', 404);
        try {
            $job = $service->refresh($job);
        } catch (Throwable $e) {
            Log::warning('SIGMAD V0.9U: consulta temporalmente fallida', [
                'uuid' => $job->uuid, 'message' => $e->getMessage(),
            ]);
            return response()->json([
                'status' => 'unreachable', 'analysis_uuid' => $job->uuid,
                'message' => 'No se pudo actualizar el estado; consulte el servidor y reintente más tarde.',
            ], 503);
        }
        $interrupted = is_string($job->error_message)
            && str_starts_with($job->error_message, 'INTERRUPTED:');
        return response()->json([
            'status' => $interrupted ? 'interrupted' : $job->status,
            'analysis_uuid' => $job->uuid,
            'progress' => (int) $job->progress,
            'total_tiles' => (int) $job->total_tiles,
            'processed_tiles' => (int) $job->processed_tiles,
            'summary' => $job->summary,
            'error_message' => $job->error_message,
            'map_url' => $job->status === 'completed'
                ? route('analysis.map', ['analysisUuid' => $job->uuid]) : null,
            'artifacts' => $job->status === 'completed' ? $job->artifacts->map(fn ($a) => [
                'type' => $a->type, 'filename' => $a->filename,
                'download_url' => route('analysis.artifacts.download', [
                    'analysisUuid' => $job->uuid, 'artifactUuid' => $a->uuid,
                ]),
            ])->all() : [],
        ]);
    }
}

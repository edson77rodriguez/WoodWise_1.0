<?php

namespace App\Http\Controllers;

use App\Models\AnalysisJob;
use App\Models\Mosaic;
use App\Services\V08sAnalysisService;
use Illuminate\Http\Request;

class V08sAnalysisController extends Controller
{
    public function index(Request $request)
    {
        $mosaics=Mosaic::query()->with('project')
            ->where('status','ready')
            ->whereHas('project',fn($q)=>$q->where('user_id',$request->user()->id))
            ->orderByDesc('id')->get();
        return view('analysis.v08s',compact('mosaics'));
    }

    public function start(Request $request,Mosaic $mosaic,V08sAnalysisService $service)
    {
        $mosaic->loadMissing('project');
        abort_unless($mosaic->project && (int)$mosaic->project->user_id===(int)$request->user()->id,403);
        $job=$service->start($mosaic);
        return response()->json(['status'=>'processing','analysis_uuid'=>$job->uuid,
            'status_url'=>route('analysis.v08s.status',['analysisUuid'=>$job->uuid])],202);
    }

    public function status(Request $request,string $analysisUuid,V08sAnalysisService $service)
    {
        $job=AnalysisJob::with('mosaic.project')->where('uuid',$analysisUuid)->firstOrFail();
        abort_unless($job->mosaic && $job->mosaic->project &&
            (int)$job->mosaic->project->user_id===(int)$request->user()->id,403);
        abort_unless(data_get($job->parameters,'pipeline_version')==='V0.8S',404);
        $job=$service->refresh($job);
        return response()->json([
            'status'=>$job->status,'analysis_uuid'=>$job->uuid,
            'summary'=>$job->summary,
            'map_url'=>$job->status==='completed'
                ?route('analysis.map',['analysisUuid'=>$job->uuid]):null,
            'error_message'=>$job->status==='failed'?$job->error_message:null,
        ]);
    }
}

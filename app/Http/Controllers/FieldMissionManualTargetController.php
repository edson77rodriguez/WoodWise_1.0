<?php

namespace App\Http\Controllers;

use App\Models\FieldMission;
use App\Models\FieldMissionManualTarget;
use App\Models\FieldObservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * SIGMAD Misiones V0.5: objetivos manuales auditados, separados de las máscaras IA.
 * Sus geometrías usan metros UTM del CRS de la misión, no GeoJSON EPSG:4326.
 */
class FieldMissionManualTargetController extends Controller
{
    private function checkAdmin(Request $request, FieldMission $mission): void
    {
        abort_unless($request->user()?->loadMissing('persona.rol')->persona?->rol?->nom_rol === 'Administrador', 403);
        abort_unless($mission->status === 'active', 404);
        abort_unless((bool)preg_match('/^EPSG:(326|327)\d{2}$/', (string)$mission->crs), 422);
    }

    private function rules(bool $geometryRequired): array
    {
        return [
            'geometry' => [$geometryRequired ? 'required' : 'sometimes', 'array'],
            'geometry.type' => [$geometryRequired ? 'required' : 'required_with:geometry', Rule::in(['Point','Polygon'])],
            'geometry.coordinates' => [$geometryRequired ? 'required' : 'required_with:geometry', 'array'],
            'probable_species' => ['required', Rule::in(['PL', 'QR', 'O', 'ND'])],
            'reason' => ['required', Rule::in(['visible_omitido','fusionada','oculta','dudosa','otro'])],
            'priority' => ['required', Rule::in(['baja','normal','alta'])],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    private function checkGeometry(array $g, FieldMission $mission): void
    {
        $bounds = $mission->bounds;
        if (!is_array($bounds) || count($bounds) !== 4) {
            throw ValidationException::withMessages(['geometry' => 'La misión no tiene límites UTM válidos.']);
        }
        $b = array_values(array_map('floatval', $bounds));
        if ($b[0] >= $b[2] || $b[1] >= $b[3]) {
            throw ValidationException::withMessages(['geometry' => 'Bounds UTM inválidos.']);
        }
        $type = $g['type'] ?? '';
        $coordinates = $g['coordinates'] ?? null;
        if ($type === 'Point') {
            $points = [$coordinates];
        } elseif ($type === 'Polygon' && is_array($coordinates) && count($coordinates) === 1) {
            $points = $coordinates[0];
            if (!is_array($points) || count($points) < 4 || count($points) > 65) {
                throw ValidationException::withMessages(['geometry' => 'Polígono: de 3 a 64 vértices más cierre.']);
            }
        } else {
            throw ValidationException::withMessages(['geometry' => 'Solo punto o polígono exterior simple.']);
        }
        foreach ($points as $p) {
            if (!is_array($p) || count($p) !== 2 || !is_numeric($p[0]) || !is_numeric($p[1])) {
                throw ValidationException::withMessages(['geometry' => 'Coordenadas UTM inválidas.']);
            }
            $x=(float)$p[0]; $y=(float)$p[1];
            if (!is_finite($x) || !is_finite($y) || $x < $b[0] - .5 || $x > $b[2] + .5 || $y < $b[1] - .5 || $y > $b[3] + .5) {
                throw ValidationException::withMessages(['geometry' => 'La marca queda fuera del ortomosaico.']);
            }
        }
        if ($type === 'Polygon') {
            $start = $points[0]; $end = $points[count($points) - 1];
            if (hypot((float)$start[0]-(float)$end[0], (float)$start[1]-(float)$end[1]) > .001) {
                throw ValidationException::withMessages(['geometry' => 'Cierra correctamente el polígono.']);
            }
            $twiceArea = 0.;
            for ($i=0; $i<count($points)-1; $i++) {
                $twiceArea += (float)$points[$i][0] * (float)$points[$i+1][1]
                           - (float)$points[$i+1][0] * (float)$points[$i][1];
            }
            $area=abs($twiceArea)/2;
            if ($area < .1 || $area > 2500) {
                throw ValidationException::withMessages(['geometry' => 'Área de copa aproximada fuera de rango: 0.1–2500 m².']);
            }
            // Impide cruces simples de segmentos; polígonos degenerados no deben guardarse.
            $n=count($points)-1;
            for ($i=0; $i<$n; $i++) for ($j=$i+2; $j<$n; $j++) {
                if ($i===0 && $j===$n-1) continue;
                if ($this->segmentsCross($points[$i],$points[$i+1],$points[$j],$points[$j+1])) {
                    throw ValidationException::withMessages(['geometry' => 'La copa presenta segmentos cruzados.']);
                }
            }
        }
    }

    private function segmentsCross(array $a, array $b, array $c, array $d): bool
    {
        $orientation = static fn($p,$q,$r): float =>
            ((float)$q[0]-(float)$p[0])*((float)$r[1]-(float)$p[1])
            -((float)$q[1]-(float)$p[1])*((float)$r[0]-(float)$p[0]);
        $o1=$orientation($a,$b,$c); $o2=$orientation($a,$b,$d);
        $o3=$orientation($c,$d,$a); $o4=$orientation($c,$d,$b);
        return (($o1>0 && $o2<0)||($o1<0 && $o2>0)) &&
               (($o3>0 && $o4<0)||($o3<0 && $o4>0));
    }

    private function dataForMission(FieldMission $mission): array
    {
        $targets=FieldMissionManualTarget::query()->where('field_mission_id',$mission->id)
            ->where('status','active')->orderBy('id')->limit(201)->get();
        abort_if($targets->count()>200,422,'La misión supera el límite de 200 objetivos manuales.');
        return [
            'type'=>'FeatureCollection',
            'crs'=>$mission->crs,
            'features'=>$targets->map(fn($t)=>$t->toMissionFeature())->all(),
        ];
    }

    public function index(Request $request, FieldMission $mission)
    {
        $this->checkAdmin($request,$mission);
        return response()->json(['manual_targets'=>$this->dataForMission($mission)])
            ->header('Cache-Control','private, no-store');
    }

    public function store(Request $request, FieldMission $mission)
    {
        $this->checkAdmin($request,$mission);
        abort_if(strlen((string)$request->getContent())>16000,413,'Marca demasiado grande.');
        $v=$request->validate($this->rules(true));
        $this->checkGeometry($v['geometry'],$mission);
        $target=DB::transaction(function () use($request,$mission,$v) {
            DB::table('field_missions')->where('id',$mission->id)->lockForUpdate()->first();
            if (FieldMissionManualTarget::query()->where('field_mission_id',$mission->id)->where('status','active')->count()>=200) {
                throw ValidationException::withMessages(['geometry'=>'Máximo 200 objetivos manuales por misión.']);
            }
            $target=FieldMissionManualTarget::create([
                'uuid'=>(string)Str::uuid(), 'field_mission_id'=>$mission->id,
                'created_by'=>$request->user()->id,'updated_by'=>$request->user()->id,
                'geometry_type'=>$v['geometry']['type'],'geometry'=>$v['geometry'], 'crs'=>$mission->crs,
                'probable_species'=>$v['probable_species'],'reason'=>$v['reason'],
                'priority'=>$v['priority'],'status'=>'active','note'=>$v['note']??null,
            ]);
            DB::table('field_mission_manual_target_events')->insert([
                'manual_target_id'=>$target->id, 'actor_id'=>$request->user()->id,
                'action'=>'created','before_state'=>null,
                'after_state'=>json_encode($target->toMissionFeature(),JSON_THROW_ON_ERROR),
                'created_at'=>now(),
            ]);
            return $target;
        },3);
        return response()->json(['feature'=>$target->toMissionFeature()],201)->header('Cache-Control','private, no-store');
    }

    public function update(Request $request, FieldMission $mission, FieldMissionManualTarget $target)
    {
        $this->checkAdmin($request,$mission);
        abort_unless($target->field_mission_id===$mission->id && $target->status==='active',404);
        abort_if(strlen((string)$request->getContent())>16000,413);
        $v=$request->validate($this->rules(false));
        if (isset($v['geometry'])) $this->checkGeometry($v['geometry'],$mission);
        $changed=DB::transaction(function () use($request,$mission,$target,$v) {
            DB::table('field_missions')->where('id',$mission->id)->lockForUpdate()->first();
            $current=FieldMissionManualTarget::whereKey($target->id)->lockForUpdate()->firstOrFail();
            abort_unless($current->status==='active',409);
            $alreadyMeasured=FieldObservation::where('grupo','H')
                ->where('payload->mission_uuid',$mission->uuid)
                ->where('payload->manual_target_uuid',$current->uuid)->exists();
            if ($alreadyMeasured && isset($v['geometry']) && $v['geometry']!==$current->geometry) {
                throw ValidationException::withMessages(['geometry'=>'Una marca ya medida no puede cambiar de posición.']);
            }
            $before=$current->toMissionFeature();
            foreach (['probable_species','reason','priority','note'] as $key) if (array_key_exists($key,$v)) $current->{$key}=$v[$key];
            if (isset($v['geometry'])) {$current->geometry=$v['geometry'];$current->geometry_type=$v['geometry']['type'];}
            $current->updated_by=$request->user()->id;
            if (!$current->isDirty(['probable_species','reason','priority','note','geometry','geometry_type']))return $current;
            $current->save();
            DB::table('field_mission_manual_target_events')->insert([
                'manual_target_id'=>$current->id,'actor_id'=>$request->user()->id,
                'action'=>'updated','before_state'=>json_encode($before,JSON_THROW_ON_ERROR),
                'after_state'=>json_encode($current->toMissionFeature(),JSON_THROW_ON_ERROR),
                'created_at'=>now(),
            ]);
            return $current;
        },3);
        return response()->json(['feature'=>$changed->toMissionFeature()])->header('Cache-Control','private, no-store');
    }

    public function destroy(Request $request, FieldMission $mission, FieldMissionManualTarget $target)
    {
        $this->checkAdmin($request,$mission);
        abort_unless($target->field_mission_id===$mission->id && $target->status==='active',404);
        $v=$request->validate(['justification'=>['required','string','min:8','max:500']]);
        DB::transaction(function () use($request,$mission,$target,$v) {
            DB::table('field_missions')->where('id',$mission->id)->lockForUpdate()->first();
            $current=FieldMissionManualTarget::whereKey($target->id)->lockForUpdate()->firstOrFail();
            abort_unless($current->status==='active',409);
            $hasObservations=FieldObservation::where('grupo','H')->where('payload->mission_uuid',$mission->uuid)
                ->where('payload->manual_target_uuid',$current->uuid)->exists();
            if ($hasObservations) throw ValidationException::withMessages(['target'=>'La marca tiene mediciones; no se permite retirarla.']);
            $before=$current->toMissionFeature();
            $current->status='void';$current->updated_by=$request->user()->id;$current->save();
            DB::table('field_mission_manual_target_events')->insert([
                'manual_target_id'=>$current->id,'actor_id'=>$request->user()->id,
                'action'=>'voided','before_state'=>json_encode($before,JSON_THROW_ON_ERROR),
                'after_state'=>null,'justification'=>$v['justification'],'created_at'=>now(),
            ]);
        },3);
        return response()->json(['ok'=>true])->header('Cache-Control','private, no-store');
    }
}

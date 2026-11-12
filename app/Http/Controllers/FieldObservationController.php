<?php

namespace App\Http\Controllers;

use App\Models\FieldObservation;
use App\Models\FieldMission;
use App\Models\Parcela;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class FieldObservationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user()->loadMissing('persona.rol', 'persona.tecnico');
        $rol = $user->persona?->rol?->nom_rol;

        if ($rol === 'Administrador') {
            $parcelas = Parcela::orderBy('nom_parcela')->get(['id_parcela', 'nom_parcela']);
        } else {
            $tecnico = $user->persona?->tecnico;
            $parcelas = $tecnico
                ? $tecnico->parcelas()->orderBy('parcelas.nom_parcela')->get()
                : collect();
        }

        // Misiones visibles únicamente si la parcela pertenece al técnico; el
        // administrador puede consultar todas. No se envían GeoJSON al HTML.
        $missions = FieldMission::query()->where('status', 'active');
        if ($rol !== 'Administrador') {
            $missions->whereIn('id_parcela', $parcelas->pluck('id_parcela'));
        }
        $missionList = $missions->latest('id')->limit(80)->get()
            ->map(fn ($m) => [
                'uuid' => $m->uuid,
                'title' => $m->title,
                'id_parcela' => $m->id_parcela,
                'selected_count' => $m->selected_count,
            ])->values();

        return view('campo.index', [
            'campoBootstrap' => [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'parcelas' => $parcelas->map(fn ($p) => [
                    'id' => $p->id_parcela,
                    'name' => $p->nom_parcela,
                ])->values(),
                'sync_url' => route('campo.sync'),
                'missions' => $missionList,
                'mission_url_template' => route('campo.mission.data', ['mission' => '__UUID__']),
                'is_admin' => $rol === 'Administrador',
                'mission_admin_url' => $rol === 'Administrador' ? route('campo.missions.create') : null,
            ],
        ]);
    }

    public function sync(Request $request): JsonResponse
    {
        // No confundir esta API web de observaciones con /api/v1/mobile/sync/completada,
        // que reporta eventos del aplicativo operativo previo.
        $validated = $request->validate([
            'uuid' => ['required', 'uuid'],
            'id_parcela' => ['required', 'integer', 'exists:parcelas,id_parcela'],
            'grupo' => ['required', Rule::in(['A', 'B', 'C', 'H'])],
            'client_created_at' => ['required', 'date'],
            'payload' => ['required', 'array:tree_id,candidate_id,localizado,especie,estado,dap_cm,altura_m,conf_altura,r_n_m,r_s_m,r_e_m,r_o_m,dominancia,solapamiento,visibilidad,latitud,longitud,gps_precision_m,gps_fecha,gps_metodo,observaciones,motivo_sin_medicion,mission_uuid,detection_ref'],
            'payload.mission_uuid' => ['nullable', 'uuid'],
            'payload.detection_ref' => ['nullable', 'string', 'max:220'],
            'payload.tree_id' => ['nullable', 'string', 'max:80'],
            'payload.candidate_id' => ['nullable', 'string', 'max:80'],
            'payload.localizado' => ['nullable', Rule::in(['si', 'no', 'dudoso'])],
            'payload.especie' => ['nullable', Rule::in(['PL', 'QR', 'O', 'ND'])],
            'payload.estado' => ['nullable', Rule::in(['vivo', 'muerto', 'ND'])],
            'payload.dap_cm' => ['nullable', 'numeric', 'gt:0', 'lte:1000'],
            'payload.altura_m' => ['nullable', 'numeric', 'gt:0', 'lte:150'],
            'payload.conf_altura' => ['nullable', Rule::in(['alta', 'media', 'baja'])],
            'payload.r_n_m' => ['nullable', 'numeric', 'gte:0', 'lte:100'],
            'payload.r_s_m' => ['nullable', 'numeric', 'gte:0', 'lte:100'],
            'payload.r_e_m' => ['nullable', 'numeric', 'gte:0', 'lte:100'],
            'payload.r_o_m' => ['nullable', 'numeric', 'gte:0', 'lte:100'],
            'payload.dominancia' => ['nullable', Rule::in(['D', 'CD', 'I', 'S'])],
            'payload.solapamiento' => ['nullable', 'integer', 'between:0,3'],
            'payload.visibilidad' => ['nullable', Rule::in(['F', 'M', 'D', 'MD'])],
            'payload.latitud' => ['nullable', 'numeric', 'between:-90,90'],
            'payload.longitud' => ['nullable', 'numeric', 'between:-180,180'],
            'payload.gps_precision_m' => ['nullable', 'numeric', 'gte:0', 'lte:100000'],
            'payload.gps_fecha' => ['nullable', 'date'],
            'payload.gps_metodo' => ['nullable', Rule::in(['telefono', 'sin_gps'])],
            'payload.observaciones' => ['nullable', 'string', 'max:3000'],
            'payload.motivo_sin_medicion' => ['nullable', 'string', 'max:300'],
        ]);

        // Evitar carga grande/anidamiento innecesario hasta normalizar las variables.
        if (strlen((string) $request->getContent()) > 32_768) {
            return response()->json(['message' => 'Registro demasiado grande.'], 413);
        }

        $user = $request->user()->loadMissing('persona.rol', 'persona.tecnico');
        $rol = $user->persona?->rol?->nom_rol;
        if ($rol !== 'Administrador') {
            $tecnico = $user->persona?->tecnico;
            if (!$tecnico || !$tecnico->parcelas()
                ->where('parcelas.id_parcela', $validated['id_parcela'])->exists()) {
                return response()->json(['message' => 'Parcela no asignada al usuario.'], 403);
            }
        }

        $payload = $validated['payload'];
        $treeId = $payload['tree_id'] ?? null;
        $candidateId = $payload['candidate_id'] ?? null;
        if ($validated['grupo'] === 'A' && !$candidateId) {
            return response()->json(['message' => 'Grupo A requiere ID del candidato.'], 422);
        }
        if ($validated['grupo'] === 'A' && empty($payload['localizado'])) {
            return response()->json(['message' => 'Grupo A requiere indicar localizado.'], 422);
        }
        if (in_array($validated['grupo'], ['B','C','H'], true) && !$treeId) {
            return response()->json(['message' => 'Grupo B/C/H requiere Tree ID.'], 422);
        }

        // Si hay misión: validar la asignación en el servidor, no confiar en
        // los identificadores recibidos del teléfono. Grupo B/C no exige match.
        if (!empty($payload['mission_uuid'])) {
            $mission = FieldMission::query()->where('uuid', $payload['mission_uuid'])
                ->where('status', 'active')->with('analysisJob')->first();
            if (!$mission || (int)$mission->id_parcela !== (int)$validated['id_parcela']) {
                return response()->json(['message' => 'Misión inexistente o de otra parcela.'], 422);
            }
            if ($rol !== 'Administrador') {
                $tecnico = $user->persona?->tecnico;
                if (!$tecnico || !$tecnico->parcelas()
                    ->where('parcelas.id_parcela', $mission->id_parcela)->exists()) {
                    return response()->json(['message' => 'Misión no asignada.'], 403);
                }
            }
            if ($validated['grupo'] === 'A') {
                $found = false;
                foreach ($mission->geojson['features'] ?? [] as $feature) {
                    if (($feature['properties']['operational_id'] ?? null) === $candidateId) {
                        $found = true;
                        break;
                    }
                }
                $expectedRef = $mission->analysisJob->uuid . ':' . $candidateId;
                if (!$found || ($payload['detection_ref'] ?? null) !== $expectedRef) {
                    return response()->json(['message' => 'El candidato no pertenece a la misión seleccionada.'], 422);
                }
            } elseif (!empty($payload['detection_ref'])) {
                return response()->json(['message' => 'Grupo B/C/H no debe asociarse a IA durante captura.'], 422);
            }
        } elseif (!empty($payload['detection_ref'])) {
            return response()->json(['message' => 'La referencia IA requiere una misión.'], 422);
        }

        // El cuerpo se preserva como evidencia; el hash detecta reutilización del UUID
        // con datos distintos. No se mezclan automáticamente mediciones diferentes.
        $encoded = json_encode([
            'id_parcela' => (int) $validated['id_parcela'],
            'grupo' => $validated['grupo'],
            'client_created_at' => $validated['client_created_at'],
            'payload' => $payload,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        $hash = hash('sha256', $encoded);

        $save = function () use ($validated, $user, $payload, $hash, $treeId, $candidateId): array {
            $existing = FieldObservation::where('uuid', $validated['uuid'])->lockForUpdate()->first();
            if ($existing) {
                // Si lo envió otro usuario, no permitir confirmar ni exponerlo.
                if ((int) $existing->user_id !== (int) $user->id) {
                    return ['status' => 409, 'body' => ['message' => 'UUID ya utilizado.']];
                }
                if (!hash_equals($existing->payload_sha256, $hash)) {
                    return ['status' => 409, 'body' => ['message' => 'Conflicto: UUID con contenido diferente.']];
                }
                return ['status' => 200, 'body' => ['ok' => true, 'uuid' => $existing->uuid, 'id' => $existing->id, 'duplicate' => true]];
            }

            $created = FieldObservation::create([
                'uuid' => $validated['uuid'],
                'user_id' => $user->id,
                'id_parcela' => $validated['id_parcela'],
                'grupo' => $validated['grupo'],
                'tree_id' => $treeId,
                'candidate_id' => $candidateId,
                'payload' => $payload,
                'payload_sha256' => $hash,
                'client_created_at' => $validated['client_created_at'],
                'synced_at' => now(),
            ]);
            return ['status' => 201, 'body' => ['ok' => true, 'uuid' => $created->uuid, 'id' => $created->id, 'duplicate' => false]];
        };

        try {
            $result = DB::transaction($save);
        } catch (QueryException $e) {
            // Posible reintento simultáneo: reconsultar la clave única.
            $existing = FieldObservation::where('uuid', $validated['uuid'])->first();
            if (!$existing || (int) $existing->user_id !== (int) $user->id ||
                !hash_equals($existing->payload_sha256, $hash)) {
                throw $e;
            }
            $result = ['status' => 200, 'body' => ['ok' => true, 'uuid' => $existing->uuid, 'id' => $existing->id, 'duplicate' => true]];
        }

        return response()->json($result['body'], $result['status']);
    }
}

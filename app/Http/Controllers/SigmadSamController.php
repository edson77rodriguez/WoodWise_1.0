<?php
/** SIGMAD V0.9.3: authenticated SAM2 GPU proxy. No model weights or images stored by Laravel. */
namespace App\Http\Controllers;

use App\Models\FieldMission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Validation\ValidationException;

class SigmadSamController extends Controller
{
    private function authorizeAdmin(Request $request, FieldMission $mission): void
    {
        abort_unless($request->user()?->loadMissing('persona.rol')->persona?->rol?->nom_rol === 'Administrador', 403);
        abort_unless($mission->status === 'active', 404);
    }

    private function settings(): array
    {
        return [trim((string)config('sigmad_sam.url', '')), trim((string)config('sigmad_sam.token', ''))];
    }

    public function status(Request $request, FieldMission $mission)
    {
        $this->authorizeAdmin($request, $mission);
        [$url, $token] = $this->settings();
        $enabled = strlen($token) >= 24 && str_starts_with($url, 'https://');
        return response()->json(['configured' => $enabled,
            'message' => $enabled ? 'Servicio GPU configurado; la conectividad se verificará al generar.'
                : 'GPU aún no configurada. El editor manual continúa disponible.'])
            ->header('Cache-Control', 'no-store');
    }

    private function master(FieldMission $mission): array
    {
        $analysisUuid = $mission->analysis_uuid ?: $mission->analysisJob?->uuid;
        abort_unless(is_string($analysisUuid) && preg_match('/^[a-f0-9-]{36}$/i', $analysisUuid), 422);
        $file = storage_path('app/sigmad_master_hd/'.$analysisUuid.'/manifest.json');
        abort_unless(is_file($file), 422, 'Falta el mapa maestro HD de este análisis.');
        $m = json_decode((string)file_get_contents($file), true);
        abort_unless(is_array($m) && ($m['schema'] ?? null) === 'sigmad-master-utm-v1'
            && ($m['analysis_uuid'] ?? null) === $analysisUuid
            && is_array($m['resolutions'] ?? null) && count($m['resolutions']) >= 1
            && is_array($m['extent'] ?? null) && count($m['extent']) === 4, 422,
            'Manifiesto HD no válido.');
        return $m;
    }

    public function propose(Request $request, FieldMission $mission)
    {
        $this->authorizeAdmin($request, $mission);
        [$url, $token] = $this->settings();
        if (!str_starts_with($url, 'https://') || strlen($token) < 24) {
            return response()->json(['message' => 'SAM no está conectado. Configura la URL HTTPS y el token GPU en SIGMAD.'], 503);
        }
        $v = $request->validate([
            'image_base64' => ['required','string','max:4000000'],
            'width' => ['required','integer','in:768'],
            'height' => ['required','integer','in:768'],
            'xmin' => ['required','numeric'],
            'ymax' => ['required','numeric'],
            'resolution' => ['required','numeric','gt:0'],
            'analysis_uuid' => ['required','string','max:50'],
            'points' => ['required','array','min:1','max:25'],
            'points.*.x' => ['required','numeric','between:0,767'],
            'points.*.y' => ['required','numeric','between:0,767'],
            'points.*.label' => ['required','integer','in:0,1'],
        ]);
        if (!collect($v['points'])->contains(fn($p) => (int)$p['label'] === 1)) {
            throw ValidationException::withMessages(['points' => 'Marca al menos un punto verde dentro de la copa.']);
        }
        $m = $this->master($mission);
        if ($v['analysis_uuid'] !== $m['analysis_uuid']) abort(422, 'Análisis y recorte no coinciden.');
        $res = (float)end($m['resolutions']);
        if (!is_finite($res) || abs((float)$v['resolution']-$res) > max(1e-9,$res*1e-6)) abort(422, 'Resolución de recorte incorrecta.');
        $ext = array_map('floatval', $m['extent']);
        $x0 = (float)$v['xmin']; $y1 = (float)$v['ymax'];
        $margin = 768*$res;
        if ($x0 < $ext[0]-$margin || $x0 > $ext[2] || $y1 < $ext[1] || $y1 > $ext[3]+$margin) {
            abort(422, 'Recorte fuera del área de análisis.');
        }
        $binary = base64_decode($v['image_base64'], true);
        if (!is_string($binary) || strlen($binary) > 3000000) abort(422, 'Imagen demasiado grande.');
        $size = @getimagesizefromstring($binary);
        if (!is_array($size) || $size[0] !== 768 || $size[1] !== 768 || $size['mime'] !== 'image/jpeg') {
            abort(422, 'Se requiere un recorte JPEG de 768×768 píxeles.');
        }
        try {
            $response = Http::withToken($token)->acceptJson()->timeout(75)->connectTimeout(7)
                ->withOptions(['allow_redirects' => false])
                ->post(rtrim($url, '/').'/segment', [
                    'image_base64' => $v['image_base64'],
                    'points' => $v['points'],
                ]);
        } catch (ConnectionException $e) {
            return response()->json(['message' => 'No se pudo conectar a la GPU. La anotación original permanece intacta.'], 503);
        }
        if (!$response->successful()) {
            return response()->json(['message' => 'El servicio SAM no pudo procesar esta imagen (HTTP '.$response->status().').'], 502);
        }
        $data = $response->json();
        if (!is_array($data) || !is_array($data['masks'] ?? null) || count($data['masks']) > 3) {
            return response()->json(['message' => 'Respuesta SAM inválida.'], 502);
        }
        $safe = [];
        foreach ($data['masks'] as $entry) {
            if (!is_array($entry) || !is_string($entry['png_base64'] ?? null)) abort(502);
            $png = base64_decode($entry['png_base64'], true);
            if (!is_string($png) || strlen($png) > 1500000 || substr($png, 0, 8) !== "\x89PNG\r\n\x1a\n") abort(502);
            $shape = @getimagesizefromstring($png);
            if (!$shape || $shape[0] !== 768 || $shape[1] !== 768) abort(502);
            $score = (float)($entry['score'] ?? -1);
            if (!is_finite($score) || $score < 0 || $score > 1) abort(502);
            $safe[] = ['png_base64' => $entry['png_base64'], 'score' => $score,
                'pixels' => (int)($entry['pixels'] ?? 0)];
        }
        return response()->json([
            'ok' => true,
            'masks' => $safe,
            'model_id' => 'sam2.1-hiera-small',
            'image_sha256' => hash('sha256', $binary),
            'crop' => [
                'xmin' => $x0, 'ymax' => $y1, 'resolution' => $res,
                'width' => 768, 'height' => 768,
                'analysis_uuid' => $m['analysis_uuid'], 'crs' => $m['crs'],
            ],
        ])->header('Cache-Control', 'private, no-store');
    }
}

<?php
namespace App\Http\Controllers;

use App\Models\FieldMission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Client\ConnectionException;
use Throwable;

/** Proxy con autenticación de sesión, autorización por misión y token solo del servidor.
 * No escribe máscaras: el usuario decide Aplicar y guarda desde el flujo versionado de SIGMAD.
 */
class SigmadSamInteractiveController extends Controller
{
    public function predict(Request $request, FieldMission $mission)
    {
        abort_unless($request->user()?->loadMissing('persona.rol')->persona?->rol?->nom_rol === 'Administrador', 403);
        abort_unless($mission->status === 'active', 404);
        abort_unless((bool) config('sigmad_sam.enabled'), 503, 'SAM interactivo no está habilitado.');
        $endpoint = (string) config('sigmad_sam.url');
        $secret = (string) config('sigmad_sam.token');
        abort_unless(str_starts_with($endpoint, 'https://') && strlen($secret)>=32,
            503, 'SAM no tiene puente HTTPS o token configurado.');
        $data = $request->validate([
            'image' => ['required','file','mimes:jpg,jpeg,png,webp','max:5000'],
            'points' => ['required','string','max:4096'],
            'analysis_uuid' => ['required','uuid'],
            'crs' => ['required','regex:/^EPSG:(326|327)(0[1-9]|[1-5][0-9]|60)$/'],
            'crop_bounds' => ['required','string','max:300'],
        ]);
        $analysis = (string) ($mission->analysis_uuid ?: $mission->analysisJob?->uuid);
        if (!$analysis || $analysis !== $data['analysis_uuid'] || $mission->crs !== $data['crs']) {
            throw ValidationException::withMessages(['analysis_uuid'=>'Misión, análisis y CRS no coinciden.']);
        }
        $pts = json_decode($data['points'],true);
        if (!is_array($pts) || count($pts)<1 || count($pts)>20) {
            throw ValidationException::withMessages(['points'=>'Se requieren entre 1 y 20 puntos.']);
        }
        $hasPositive = false;
        foreach ($pts as $point) {
            if (!is_array($point) || count($point)!==3 ||
                !is_numeric($point['x'] ?? null) || !is_numeric($point['y'] ?? null) ||
                !in_array($point['label'] ?? null,[0,1],true) ||
                (float)$point['x']<0 || (float)$point['x']>=1024 ||
                (float)$point['y']<0 || (float)$point['y']>=1024) {
                throw ValidationException::withMessages(['points'=>'Puntos fuera del recorte SAM.']);
            }
            if ($point['label']===1) $hasPositive=true;
        }
        if (!$hasPositive) throw ValidationException::withMessages(['points'=>'Agrega al menos un punto verde.']);
        $bounds=json_decode($data['crop_bounds'],true);
        if (!is_array($bounds) || count($bounds)!==4) {
            throw ValidationException::withMessages(['crop_bounds'=>'Extensión UTM incorrecta.']);
        }
        foreach ($bounds as $coord) if (!is_numeric($coord) || !is_finite((float)$coord)) {
            throw ValidationException::withMessages(['crop_bounds'=>'Extensión UTM incorrecta.']);
        }
        [$x1,$y1,$x2,$y2]=array_map('floatval',$bounds);
        if ($x1 >= $x2 || $y1 >= $y2 || ($x2-$x1)>150 || ($y2-$y1)>150 ||
            abs(($x2-$x1)-($y2-$y1))>0.1) {
            throw ValidationException::withMessages(['crop_bounds'=>'Extensión SAM demasiado grande o no cuadrada.']);
        }
        // Una petición del navegador no puede pedir mosaicos de un análisis distinto.
        // El raster se compone de teselas HD que el usuario ya está autorizado a consultar.
        $missionBounds = $mission->bounds;
        if (is_array($missionBounds) && count($missionBounds)===4) {
            $b=array_values(array_map('floatval',$missionBounds));
            if ($x2<$b[0]-100 || $x1>$b[2]+100 || $y2<$b[1]-100 || $y1>$b[3]+100) {
                throw ValidationException::withMessages(['crop_bounds'=>'Recorte no relacionado con el área de la misión.']);
            }
        }
        $file=$data['image'];
        $size=@getimagesize($file->getRealPath());
        if (!is_array($size) || $size[0]!==1024 || $size[1]!==1024) {
            throw ValidationException::withMessages(['image'=>'El recorte debe ser RGB de 1024×1024 píxeles.']);
        }
        $fh=fopen($file->getRealPath(),'rb');
        if (!$fh) abort(422,'No fue posible leer el recorte.');
        try {
            $answer=Http::timeout(150)->connectTimeout(12)
                ->withHeaders(['X-SIGMAD-AI-KEY'=>$secret,'ngrok-skip-browser-warning'=>'true'])
                ->acceptJson()->attach('image',$fh,'sigmad_crop.'.$file->extension(),['Content-Type'=>$file->getMimeType()])
                ->post($endpoint.'/sam/interactive/v010/predict', ['points'=>$data['points']]);
            if (!$answer->successful()) {
                $error=$answer->status()===409 ? 'GPU ocupada; termina el análisis actual y vuelve a intentar.'
                    : ($answer->status()===401 ? 'El token del puente SAM es incorrecto.'
                    : 'SAM devolvió HTTP '.$answer->status().': '.mb_substr((string)($answer->json('detail') ?? ''),0,120));
                return response()->json(['message'=>$error],$answer->status()===409?409:502);
            }
            $result=$answer->json();
            if (!is_array($result) || ($result['status'] ?? null)!=='ok' || !is_array($result['candidates']??null)) {
                return response()->json(['message'=>'SAM devolvió una respuesta inesperada.'],502);
            }
            // Los detalles de inferencia se devuelven exclusivamente al administrador autenticado.
            return response()->json($result);
        } catch (ConnectionException $exception) {
            return response()->json(['message'=>'El Colab o túnel ngrok no responde. Verifica el mismo puente V0.9U.'],503);
        } finally {
            if (is_resource($fh)) fclose($fh);
        }
    }
}

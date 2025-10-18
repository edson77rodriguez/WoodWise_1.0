<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Persona;
use App\Models\Tecnico;
use App\Models\Parcela;
use App\Models\Especie;
use App\Models\Turno_Corta;
use App\Models\Tipo_Estimacion;
use App\Models\Formula;
use App\Models\Troza;

class DashboardController extends Controller
{
    /**
     * [REFACTORIZACIÓN] El constructor ahora asegura que CUALQUIER método
     * en este controlador requiera autenticación. Esto elimina la necesidad
     * de verificar Auth::check() en cada método.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Muestra el dashboard apropiado según el rol del usuario autenticado.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index(Request $request)
    {
        $user = $request->user()->load('persona.rol'); // Eager loading para eficiencia

        if (!$user->persona || !$user->persona->rol) {
            // Maneja el caso raro de un usuario sin persona o rol.
            // Esto es más seguro que permitir que la cadena de llamadas falle.
            Auth::logout();
            return redirect()->route('login')->with('error', 'Su cuenta no está configurada correctamente. Contacte al administrador.');
        }

        $rol = $user->persona->rol->nom_rol;

        switch ($rol) {
            case 'Administrador':
                return $this->handleAdministrador($user);
            case 'Tecnico':
                return $this->handleTecnico($user->persona);
            default:
                abort(403, 'Rol no autorizado para acceder al dashboard.');
        }
    }

    /**
     * Carga los datos y retorna la vista para el rol de Administrador.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\View\View
     */
    protected function handleAdministrador(User $user)
    {
        // [REFACTORIZACIÓN] Todas las consultas de la vista se mueven aquí.
        // Esto sigue el patrón MVC y previene consultas en cada carga de página del layout.
        $userCount = User::count();
        $parcelaCount = Parcela::count();
        $especieCount = Especie::count();
        $turnosActivosCount = Turno_Corta::whereNull('fecha_fin')->count();

        return view('dashboard', [
            'user' => $user,
            'userCount' => $userCount,
            'parcelaCount' => $parcelaCount,
            'especieCount' => $especieCount,
            'turnosActivosCount' => $turnosActivosCount,
        ]);
    }

    /**
     * Carga los datos y retorna la vista para el rol de Técnico.
     *
     * @param  \App\Models\Persona  $persona
     * @return \Illuminate\View\View
     */
    protected function handleTecnico(Persona $persona)
    {
        // [REFACTORIZACIÓN] Usamos firstOrFail para simplificar el código.
        // Si no encuentra al técnico, automáticamente lanzará un error 404.
        $tecnico = Tecnico::where('id_persona', $persona->id_persona)->firstOrFail();

        // La consulta de parcelas paginadas es correcta y eficiente.
        $parcelas = $tecnico->parcelas()
            ->withCount('trozas') // withCount es muy eficiente.
            ->orderBy('nom_parcela')
            ->paginate(10);

        // [OPTIMIZACIÓN DE CONSULTA] El cálculo anterior era incorrecto (solo sumaba la página actual).
        // Esta consulta obtiene el conteo TOTAL de trozas para TODAS las parcelas del técnico,
        // directamente desde la base de datos, lo cual es mucho más rápido y preciso.
        $totalTrozas = Troza::whereIn(
            'id_parcela',
            $tecnico->parcelas()->pluck('id_parcela') // Obtenemos solo los IDs de las parcelas del técnico
        )->count();

        // Estos datos se mantienen igual, son necesarios para los modales.
        $tiposEstimacion = Tipo_Estimacion::all();
        $formulas = Formula::all();

        return view('tecnicos.dashboard', [
            'user' => $persona->user,
            'tecnico' => $tecnico,
            'parcelas' => $parcelas,
            'totalTrozas' => $totalTrozas,
            'tiposEstimacion' => $tiposEstimacion,
            'formulas' => $formulas
        ]);
    }
}
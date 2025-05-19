<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use App\Helpers\AdminHelper;
use App\Models\User;

class EstadisticasController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $data = [
            'page' => 'Estadisticas',
            'subpage' => 'Global',
            'rol' => $user->getRoleNames()->first(),
            'user' => $user
        ];
        $data['alert'] = AdminHelper::get_count_alert($data['rol'], $user->id_sede); //gestorsede
        //Verificamos si las alertas tiene mas de 0 para cambiar el estado de la alerta
        if ($data['alert'] > 0) {
            AdminHelper::change_status_alert($data['rol'], $user->id_sede);
        }

        if ($user->can('global.Pertenece a empresa aliada.v')) {
            $sedeUsuario = DB::table('tb_sede')->where('id_sede', $user->id_sede)->first();
            if ($sedeUsuario && isset($sedeUsuario->id_empresa)) {
                $data['sedes'] = DB::table('tb_sede')
                    ->where('id_empresa', $sedeUsuario->id_empresa)
                    ->get();
            } else {
                $data['sedes'] = collect([]);
            }
        } elseif ($user->can('global.Solo ver sede asignada.v')) {
            $data['sedes'] = DB::table('tb_sede')
                ->where('id_sede', $user->id_sede)
                ->get();
        } else {
            $data['sedes'] = DB::table('tb_sede')->get();
        }

        $data['estados'] = DB::table('tb_estado')->get();

        echo view('layouts.header', $data);
        echo view('layouts.nav', $data);
        echo view('dashboard.estadisticas.index', $data);
        echo view('layouts.footer', $data);
    }

    public function estadisticasAgentes()
    {
        $user = Auth::user();
        $data = [
            'page' => 'Estadisticas',
            'subpage' => 'Agentes',
            'rol' => $user->getRoleNames()->first(),
            'user' => $user
        ];
        $data['alert'] = AdminHelper::get_count_alert($data['rol'], $user->id_sede); //gestorsede
        //Verificamos si las alertas tiene mas de 0 para cambiar el estado de la alerta
        if ($data['alert'] > 0) {
            AdminHelper::change_status_alert($data['rol'], $user->id_sede);
        }

        if ($user->can('global.Pertenece a empresa aliada.v')) {
            $sedeUsuario = DB::table('tb_sede')->where('id_sede', $user->id_sede)->first();
            if ($sedeUsuario && isset($sedeUsuario->id_empresa)) {
                $data['sedes'] = DB::table('tb_sede')
                    ->where('id_empresa', $sedeUsuario->id_empresa)
                    ->get();
            } else {
                $data['sedes'] = collect([]);
            }
        } elseif ($user->can('global.Solo ver sede asignada.v')) {
            $data['sedes'] = DB::table('tb_sede')
                ->where('id_sede', $user->id_sede)
                ->get();
        } else {
            $data['sedes'] = DB::table('tb_sede')->get();
        }

        $agentes = User::permission('global.Asignar citas call.v')
            ->orderBy('id', 'asc')
            ->get();
        $data['listado_agentes'] = $agentes->toArray();


        $data['estados'] = DB::table('tb_estado')->get();

        $data['soloPropias'] = Auth::user()->can('estadisticas.Solo ver estadísticas propias');

        $data['miUsuarioId'] = Auth::id();


        echo view('layouts.header', $data);
        echo view('layouts.nav', $data);
        echo view('dashboard.estadisticas.agentes', $data);
        echo view('layouts.footer', $data);
    }

    public function estadisticasSedes()
    {
        $user = Auth::user();
        $data = [
            'page' => 'Estadisticas',
            'subpage' => 'sedes',
            'rol' => $user->getRoleNames()->first(),
            'user' => $user
        ];
        $data['alert'] = AdminHelper::get_count_alert($data['rol'], $user->id_sede); //gestorsede
        //Verificamos si las alertas tiene mas de 0 para cambiar el estado de la alerta
        if ($data['alert'] > 0) {
            AdminHelper::change_status_alert($data['rol'], $user->id_sede);
        }
        //Sedes
        if ($user->can('global.Pertenece a empresa aliada.v')) {
            $sedeUsuario = DB::table('tb_sede')->where('id_sede', $user->id_sede)->first();
            if ($sedeUsuario && isset($sedeUsuario->id_empresa)) {
                $data['sedes'] = DB::table('tb_sede')->where('id_empresa', $sedeUsuario->id_empresa)->get();
            } else {
                $data['sedes'] = collect([]);
            }
        } elseif ($user->can('global.Solo ver sede asignada.v')) {
            $data['sedes'] = DB::table('tb_sede')->where('id_sede', $user->id_sede)->get();
        } else {
            $data['sedes'] = DB::table('tb_sede')->get();
        }
        //Estados
        $data['estados'] = DB::table('tb_estado')->get();

        echo view('layouts.header', $data);
        echo view('layouts.nav', $data);
        echo view('dashboard.estadisticas.sedes', $data);
        echo view('layouts.footer', $data);
    }


    public function getCitasData(Request $request)
    {
        $startDate = $request->input('start_date');
        $range = $request->input('range', 1);

        $data = $this->generateStats('t1.reserva_cita', $startDate, $range);
        return response()->json($data);
    }

    public function getCreacionesData(Request $request)
    {
        $startDate = $request->input('start_date');
        $range = $request->input('range', 1);

        $data = $this->generateStats('t1.created_at', $startDate, $range);
        return response()->json($data);
    }

    private function generateStats($field, $startDate = null, $range = null)
    {
        $user = Auth::user();
        // Definir la fecha de inicio
        $startDate = $startDate ? Carbon::parse($startDate) : Carbon::today();

        // Variables de fecha (definidas según el rango)
        switch ($range) {
            case 1: // Semana actual y la pasada
                $currentDateStart = $startDate->copy()->startOfWeek();
                $currentDateEnd = $startDate->copy()->endOfWeek();
                $historicoStart = $currentDateStart->copy()->subWeek();
                $historicoEnd = $currentDateEnd->copy()->subWeek();
                $periodoSeleccionado = "Semana";
                break;
            case 2: // Quincena
                $currentDateStart = $startDate->copy()->subDays(7)->startOfWeek();
                $currentDateEnd = $startDate->copy()->endOfWeek();
                $historicoStart = $currentDateStart->copy()->subWeeks(2);
                $historicoEnd = $currentDateEnd->copy()->subWeeks(2);
                $periodoSeleccionado = "Quincena";
                break;
            case 3: // Mes actual y mes anterior
                $currentDateStart = $startDate->copy()->startOfMonth();
                $currentDateEnd = $startDate->copy()->endOfMonth();
                $historicoStart = $currentDateStart->copy()->subMonth()->startOfMonth();
                $historicoEnd = $currentDateStart->copy()->subMonth()->endOfMonth();
                $periodoSeleccionado = "Mes";
                break;
            case 4: // Trimestre (últimos 3 meses) y su histórico
                $currentDateStart = $startDate->copy()->subMonths(3)->startOfMonth();
                $currentDateEnd = $startDate->copy()->endOfMonth();
                $historicoStart = $currentDateStart->copy()->subMonths(3)->startOfMonth();
                $historicoEnd = $currentDateStart->copy()->subMonth()->endOfMonth();
                $periodoSeleccionado = "Trimestre";
                break;
            default:
                $currentDateStart = $startDate->copy()->startOfWeek();
                $currentDateEnd = $startDate->copy()->endOfWeek();
                $historicoStart = $currentDateStart->copy()->subWeek();
                $historicoEnd = $currentDateEnd->copy()->subWeek();
                $periodoSeleccionado = "Semana";
                break;
        }


        $queryActual = DB::table('tb_cita as t1')
            ->join('tb_sede as s', 't1.id_sede', '=', 's.id_sede')
            ->select(DB::raw("DATE($field) as date"), DB::raw("COUNT(*) as count"))
            ->whereBetween($field, [$currentDateStart, $currentDateEnd]);

        // Si el usuario tiene el permiso de empresa aliada, filtrar por la empresa asociada
        if ($user->can('global.Pertenece a empresa aliada.v')) {
            $sedeUsuario = DB::table('tb_sede')->where('id_sede', $user->id_sede)->first();
            if ($sedeUsuario && isset($sedeUsuario->id_empresa)) {
                $queryActual->where('s.id_empresa', $sedeUsuario->id_empresa);
            }
        }

        $dataActual = $queryActual->groupBy(DB::raw("DATE($field)"))
            ->orderBy(DB::raw("DATE($field)"))
            ->get()
            ->keyBy('date');

        // Consulta histórica similar
        $queryHistorico = DB::table('tb_cita as t1')
            ->join('tb_sede as s', 't1.id_sede', '=', 's.id_sede')
            ->select(DB::raw("DATE($field) as date"), DB::raw("COUNT(*) as count"))
            ->whereBetween($field, [$historicoStart, $historicoEnd]);

        if ($user->can('global.Pertenece a empresa aliada.v')) {
            if ($sedeUsuario && isset($sedeUsuario->id_empresa)) {
                $queryHistorico->where('s.id_empresa', $sedeUsuario->id_empresa);
            }
        }

        $dataHistorico = $queryHistorico->groupBy(DB::raw("DATE($field)"))
            ->orderBy(DB::raw("DATE($field)"))
            ->get()
            ->keyBy('date');

        // Calcular el total de días del rango actual
        $rangeTotal = $currentDateStart->diffInDays($currentDateEnd) + 1;
        $TotalDatesActual = [];
        $TotalDatesHistorico = [];

        foreach (range(0, $rangeTotal - 1) as $dayOffset) {
            $currentDate = $currentDateStart->copy()->addDays($dayOffset)->toDateString();
            $historicoDate = $historicoStart->copy()->addDays($dayOffset)->toDateString();

            $TotalDatesActual[] = [
                'date'  => $currentDate,
                'count' => $dataActual[$currentDate]->count ?? 0,
            ];
            $TotalDatesHistorico[] = [
                'date'  => $historicoDate,
                'count' => $dataHistorico[$historicoDate]->count ?? 0,
            ];
        }

        return [
            'TotalDatesActual'      => $TotalDatesActual,
            'TotalDatesHistorico'   => $TotalDatesHistorico,
            'currrentDateStart'     => $currentDateStart->format('d-m-Y'),
            'currrentDateEnd'       => $currentDateEnd->format('d-m-Y'),
            'TotalDatesHistoricoStart' => $historicoStart->format('d-m-Y'),
            'TotalDatesHistoricoEnd'   => $historicoEnd->format('d-m-Y'),
            'peiodoSelecionado'     => $periodoSeleccionado,
        ];
    }

    public function getComparativaEstado(Request $r)
    {
        return $this->generateComparativaByField(
            $r,
            'id_estado',
            $r->date_field ?? 'c.reserva_cita'
        );
    }

    public function getComparativaEstadoVerificado(Request $r)
    {
        return $this->generateComparativaByField(
            $r,
            'id_estado_verificado',
            $r->date_field ?? 'c.reserva_cita'
        );
    }

    /**
     * Helper genérico para armar comparativas “Actual vs Histórico” agrupadas por
     * un campo de estado (por ejemplo c.id_estado o c.id_estado_verificado).
     *
     * @param  Request $r
     * @param  string  $estadoField       // p.e. "c.id_estado"
     * @param  string  $dateField         // p.e. "c.reserva_cita" o "c.created_at"
     */
    private function generateComparativaByField(Request $r, string $estadoField, string $dateField)
    {
        $user = Auth::user();

        // 1) Parseo de fecha y rango
        $startDate = $r->input('start_date')
            ? Carbon::parse($r->input('start_date'))
            : Carbon::today();

        $range = (int)($r->input('range', 1));

        // 2) Calcular intervalos actual / histórico
        switch ($range) {
            case 1: // Semana actual vs pasada
                $currentStart  = $startDate->copy()->startOfWeek();
                $currentEnd    = $startDate->copy()->endOfWeek();
                $historicoStart = $currentStart->copy()->subWeek();
                $historicoEnd  = $currentEnd->copy()->subWeek();
                $periodo       = 'Semana';
                break;

            case 2: // Quincena: 2 semanas actual vs 2 semanas anteriores
                $currentStart   = $startDate->copy()->subDays(13)->startOfWeek();
                $currentEnd     = $startDate->copy()->endOfWeek();
                $historicoStart = $currentStart->copy()->subWeeks(2);
                $historicoEnd   = $currentEnd->copy()->subWeeks(2);
                $periodo        = 'Quincena';
                break;

            case 3: // Mes actual vs anterior
                $currentStart   = $startDate->copy()->startOfMonth();
                $currentEnd     = $startDate->copy()->endOfMonth();
                $historicoStart = $currentStart->copy()->subMonth()->startOfMonth();
                $historicoEnd   = $currentStart->copy()->subMonth()->endOfMonth();
                $periodo        = 'Mes';
                break;

            case 4: // Trimestre actual vs anterior
                $currentStart   = $startDate->copy()->subMonths(2)->startOfMonth();
                $currentEnd     = $startDate->copy()->endOfMonth();
                $historicoStart = $currentStart->copy()->subMonths(3)->startOfMonth();
                $historicoEnd   = $currentStart->copy()->subMonth()->endOfMonth();
                $periodo        = 'Trimestre';
                break;

            default:
                // fallback a semanal
                $currentStart  = $startDate->copy()->startOfWeek();
                $currentEnd    = $startDate->copy()->endOfWeek();
                $historicoStart = $currentStart->copy()->subWeek();
                $historicoEnd  = $currentEnd->copy()->subWeek();
                $periodo       = 'Semana';
                break;
        }

        // 3) Obtener lista maestro de nombres de estado
        $estadosList = DB::table('tb_estado')
            ->orderBy('nombre_estado')
            ->pluck('nombre_estado')
            ->all();

        // 4) Consulta actual
        $queryA = DB::table('tb_cita as c')
            ->join('tb_estado as e', "c.{$estadoField}", '=', 'e.id_estado')
            ->selectRaw("e.nombre_estado as estado, COUNT(*) as total")
            ->whereBetween($dateField, [$currentStart, $currentEnd]);

        // Filtra empresa aliada si aplica
        if ($user->can('global.Pertenece a empresa aliada.v')) {
            $empresa = DB::table('tb_sede')
                ->where('id_sede', $user->id_sede)
                ->value('id_empresa');
            if ($empresa) {
                $queryA->whereExists(function ($q) use ($empresa) {
                    $q->select(DB::raw(1))
                        ->from('tb_sede as s')
                        ->whereColumn('c.id_sede', 's.id_sede')
                        ->where('s.id_empresa', $empresa);
                });
            }
        }

        $actualData = $queryA
            ->groupBy('e.nombre_estado')
            ->pluck('total', 'estado')
            ->all();

        // 5) Consulta histórica
        $queryH = DB::table('tb_cita as c')
            ->join('tb_estado as e', "c.{$estadoField}", '=', 'e.id_estado')
            ->selectRaw("e.nombre_estado as estado, COUNT(*) as total")
            ->whereBetween($dateField, [$historicoStart, $historicoEnd]);

        if ($user->can('global.Pertenece a empresa aliada.v') && isset($empresa)) {
            $queryH->whereExists(function ($q) use ($empresa) {
                $q->select(DB::raw(1))
                    ->from('tb_sede as s')
                    ->whereColumn('c.id_sede', 's.id_sede')
                    ->where('s.id_empresa', $empresa);
            });
        }

        $historicoData = $queryH
            ->groupBy('e.nombre_estado')
            ->pluck('total', 'estado')
            ->all();

        // 6) Armar series respetando el orden maestro
        $serieActual = [];
        $serieHist   = [];
        foreach ($estadosList as $est) {
            $serieActual[] = (int)($actualData[$est]   ?? 0);
            $serieHist[]   = (int)($historicoData[$est] ?? 0);
        }

        // 7) Devolver JSON
        return response()->json([
            'labels'       => $estadosList,
            'serieActual'  => $serieActual,
            'serieHist'    => $serieHist,
            'periodo'      => $periodo,
            'currentStart' => $currentStart->toDateString(),
            'currentEnd'   => $currentEnd->toDateString(),
            'histStart'    => $historicoStart->toDateString(),
            'histEnd'      => $historicoEnd->toDateString(),
        ]);
    }

    public function getStatsPorEstadoAgentes(Request $r)
    {
        $start      = Carbon::parse($r->start_date)->startOfDay();
        $rangeDays  = $r->rangeDays ?? 6;
        $end        = $start->copy()->addDays($rangeDays)->endOfDay();
        $agentId    = $r->agent_id ?? null;
        $dateField  = $r->date_field === 'creacion' ? 'c.created_at' : 'c.reserva_cita';

        $estPrincipales = $r->input('estados', []);
        // si no llega ninguno, se toma todos:
        if (empty($estPrincipales)) {
            $estPrincipales = DB::table('tb_estado')->pluck('nombre_estado')->toArray();
        }

        $query = DB::table('tb_cita as c')
            ->join('tb_estado as e', 'c.id_estado', '=', 'e.id_estado')
            ->selectRaw("
          DATE($dateField) as fecha,
          CASE WHEN e.nombre_estado IN (" . implode(',', array_map(fn($s) => "'$s'", $estPrincipales)) . ")
            THEN e.nombre_estado ELSE 'Otros' END as estado,
          e.color_estado as color,
          COUNT(*) as total
        ")
            ->whereBetween($dateField, [$start, $end]);

        if ($agentId) {
            $query->where('c.id_agente_callcenter', $agentId);
        }

        $raw = $query->groupBy('fecha', 'estado', 'color')
            ->orderBy('fecha')
            ->get();

        // siempre construyo full fechas
        $fechas = [];
        for ($i = 0; $i <= $rangeDays; $i++) {
            $fechas[] = $start->copy()->addDays($i)->toDateString();
        }

        // armo series
        $series = [];
        foreach (array_merge($estPrincipales, ['Otros']) as $est) {
            $data = [];
            $color = null;
            foreach ($fechas as $f) {
                $row = $raw->first(fn($r) => $r->fecha == $f && $r->estado == $est);
                $data[] = $row?->total ?? 0;
                if ($row && !$color) $color = $row->color;
            }
            $series[] = ['name' => $est, 'data' => $data, 'color' => $color ?: '#adb5bd'];
        }

        // total line
        $totalPerDay = [];
        for ($i = 0; $i < count($fechas); $i++) {
            $sum = 0;
            foreach ($series as $s) $sum += $s['data'][$i];
            $totalPerDay[] = $sum;
        }
        $series[] = ['name' => 'Total', 'data' => $totalPerDay, 'color' => '#00bbe3'];

        return response()->json([
            'categories' => $fechas,
            'series'    => $series
        ]);
    }

    public function getStatsPorSede(Request $r)
    {
        // convertimos fechas
        $start = Carbon::parse($r->start_date)->startOfDay();
        $range = $r->rangeDays ?? 6;
        $end   = $start->copy()->addDays($range)->endOfDay();

        // sedes seleccionadas (array de id_sede)
        $sedeIds = $r->input('sedes', []);

        // consulta: total citas por sede
        $qb = DB::table('tb_cita as c')
            ->join('tb_sede as s', 'c.id_sede', '=', 's.id_sede')
            ->select('s.id_sede', 's.nombre_sede', DB::raw('COUNT(*) as total'))
            ->whereBetween('c.reserva_cita', [$start, $end])
            ->groupBy('s.id_sede', 's.nombre_sede');

        if (count($sedeIds))
            $qb->whereIn('s.id_sede', $sedeIds);

        $rows = $qb->get();

        // preparar datos para la gráfica de barras
        $categories = $rows->pluck('nombre_sede')->all();
        $dataBar    = $rows->pluck('total')->all();

        $sumTotal = array_sum($dataBar);

        $dataPie = $rows->map(function ($r) {
            return [
                'name' => $r->nombre_sede,
                'y'    => (int) $r->total,
            ];
        })->all();

        return response()->json([
            'categories' => $categories,
            'dataBar'    => $dataBar,
            'dataPie'    => $dataPie,
        ]);
    }
}

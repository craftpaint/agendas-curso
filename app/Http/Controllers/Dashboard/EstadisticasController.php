<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

    public function viewSedes()
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
}

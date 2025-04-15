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
        //Sedes
        if ($user->can('global.Solo ver sede asignada.v')) {
            $data['sedes'] = DB::table('tb_sede')
                ->where('id_sede', $user->id_sede)
                ->get();
        } else {
            $data['sedes'] = DB::table('tb_sede')->get();
        }
        //Estados
        $sql = "SELECT * FROM tb_estado";
        $data['estados'] = DB::select($sql);
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
        if ($user->can('global.Solo ver sede asignada.v')) {
            $data['sedes'] = DB::table('tb_sede')
                ->where('id_sede', $user->id_sede)
                ->get();
        } else {
            $data['sedes'] = DB::table('tb_sede')->get();
        }
        //Estados
        $sql = "SELECT * FROM tb_estado";
        $data['estados'] = DB::select($sql);
        echo view('layouts.header', $data);
        echo view('layouts.nav', $data);
        echo view('dashboard.estadisticas.sedes', $data);
        echo view('layouts.footer', $data);
    }


    public function getCitasData(Request $request)
    {
        $startDate = $request->input('start_date');
        $range = $request->input('range');
        if ($range == null) {
            $range = 1;
        }

        $data = $this->generateStats('tb_cita.reserva_cita', $startDate, $range); // Cambia el campo según tu BD
        return response()->json($data);
    }

    public function getCreacionesData(Request $request)
    {
        $startDate = $request->input('start_date');
        $range = $request->input('range');
        if ($range == null) {
            $range = 1;
        }


        $data = $this->generateStats('tb_cita.created_at', $startDate, $range); // Cambia el campo según tu BD
        return response()->json($data);
    }

    private function generateStats($field, $startDate = null, $range = null)
    {
        // Definir la fecha de inicio
        $startDate = $startDate ? Carbon::parse($startDate) : Carbon::today();

        $currentDateStart = null;
        $currentDateEnd = null;
        $TotalDatesHistoricoStart = null;
        $TotalDatesHistoricoEnd = null;
        $peiodoSelecionado = "Semanal";

        switch ($range) {
            case 1: // Última semana
                $currentDateStart = $startDate->copy()->startOfWeek(); // Lunes de esta semana
                $currentDateEnd = $startDate->copy()->endOfWeek(); // Domingo de esta semana

                // Semana histórica (anterior)
                $TotalDatesHistoricoStart = $currentDateStart->copy()->subWeek(); // Lunes de la semana pasada
                $TotalDatesHistoricoEnd = $currentDateEnd->copy()->subWeek(); // Domingo de la semana pasada
                $peiodoSelecionado = "Semana";
                break;

            case 2: // Últimas dos semanas
                $currentDateStart = $startDate->copy()->subDays(7)->startOfWeek(); // Lunes de hace dos semanas
                $currentDateEnd = $startDate->copy()->endOfWeek(); // Domingo de esta semana

                // Histórico de dos semanas anteriores
                $TotalDatesHistoricoStart = $currentDateStart->copy()->subWeeks(2); // Lunes de hace cuatro semanas
                $TotalDatesHistoricoEnd = $currentDateEnd->copy()->subWeeks(2); // Domingo de hace dos semanas
                $peiodoSelecionado = "Quincena";
                break;

            case 3: // Últimos tres meses
                $currentDateStart = $startDate->copy()->startOfMonth(); // Primer día del mes actual
                $currentDateEnd = $startDate->copy()->endOfMonth(); // Último día del mes actual

                // Histórico de tres meses anteriores
                $TotalDatesHistoricoStart = $currentDateStart->copy()->subMonths()->startOfMonth(); // Primer día del mes anterior al rango actual
                $TotalDatesHistoricoEnd = $currentDateStart->copy()->subMonth()->endOfMonth(); // Último día del mes anterior al rango actual
                $peiodoSelecionado = "Mes";

                break;

            case 4: // Últimos tres meses
                $currentDateStart = $startDate->copy()->subMonths(3)->startOfMonth(); // Día 1 de hace tres meses
                $currentDateEnd = $startDate->copy()->endOfMonth(); // Último día del mes actual

                // Histórico de tres meses anteriores
                $TotalDatesHistoricoStart = $currentDateStart->copy()->subMonths(3)->startOfMonth(); // Día 1 de seis meses atrás
                $TotalDatesHistoricoEnd = $currentDateStart->copy()->subMonth()->endOfMonth(); // Último día del mes anterior al rango actual
                $peiodoSelecionado = "Trimestre";

                break;

            default: // Última semana (por defecto)
                $currentDateStart = $startDate->copy()->startOfWeek(); // Lunes de esta semana
                $currentDateEnd = $startDate->copy()->endOfWeek(); // Domingo de esta semana

                // Histórico (anterior)
                $TotalDatesHistoricoStart = $currentDateStart->copy()->subWeek(); // Lunes de la semana pasada
                $TotalDatesHistoricoEnd = $currentDateEnd->copy()->subWeek(); // Domingo de la semana pasada
                $peiodoSelecionado = "Semana";

                break;
        }


        // Obtener datos de la semana actual
        $TotalDatesActualData = DB::table('tb_cita')
            ->select(DB::raw("DATE($field) as date"), DB::raw("COUNT(*) as count"))
            ->whereBetween($field, [$currentDateStart, $currentDateEnd])
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date'); // Organizar datos por fecha

        // Obtener datos de la semana anterior
        $TotalDatesHistoricoData = DB::table('tb_cita')
            ->select(DB::raw("DATE($field) as date"), DB::raw("COUNT(*) as count"))
            ->whereBetween($field, [$TotalDatesHistoricoStart, $TotalDatesHistoricoEnd])
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date'); // Organizar datos por fecha

        // Calcular la cantidad de días en el rango actual
        $rangeTotal = $currentDateStart->diffInDays($currentDateEnd) + 1; // Incluye el día final


        // Crear un array para cada día de la semana (lunes a domingo)
        $TotalDatesActual = [];
        $TotalDatesHistorico = [];
        foreach (range(0, $rangeTotal - 1) as $dayOffset) {
            $currentDate = $currentDateStart->copy()->addDays($dayOffset)->toDateString();
            $previousDate = $TotalDatesHistoricoStart->copy()->addDays($dayOffset)->toDateString();

            $TotalDatesActual[] = [
                'date' => $currentDate,
                'count' => $TotalDatesActualData[$currentDate]->count ?? 0,
            ];

            $TotalDatesHistorico[] = [
                'date' => $previousDate,
                'count' => $TotalDatesHistoricoData[$previousDate]->count ?? 0,
            ];
        }

        $currentDateStart = $currentDateStart->copy()->format('d-m-Y');
        $currentDateEnd = $currentDateEnd->copy()->format('d-m-Y');
        $TotalDatesHistoricoStart = $TotalDatesHistoricoStart->copy()->format('d-m-Y');
        $TotalDatesHistoricoEnd = $TotalDatesHistoricoEnd->copy()->format('d-m-Y');

        return [
            'TotalDatesActual' => $TotalDatesActual,
            'TotalDatesHistorico' => $TotalDatesHistorico,
            'currrentDateStart' => $currentDateStart,
            'currrentDateEnd' => $currentDateEnd,
            'TotalDatesHistoricoStart' => $TotalDatesHistoricoStart,
            'TotalDatesHistoricoEnd' => $TotalDatesHistoricoEnd,
            'peiodoSelecionado' => $peiodoSelecionado,
        ];
    }
}

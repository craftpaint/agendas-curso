<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Helpers\AdminHelper;


class EmpresasController extends Controller
{
    /**
     * Muestra el listado de empresas.
     */
    public function index()
    {
        // Usamos Query Builder para obtener las empresas
        // Asumiendo que la tabla se llama "empresas"
        $empresas = DB::table('tb_empresa')->orderBy('id_empresa', 'asc')->get();

        // Por cada empresa, obtener sedes asociadas y calcular la suma de usuarios de dichas sedes.
        foreach ($empresas as $empresa) {
            // Asume que en la tabla 'sedes' existe la columna 'empresa_id' que relaciona la sede con la empresa.
            $sedes = DB::table('tb_sede')->where('id_empresa', $empresa->id_empresa)->get();
            $empresa->sedes_count = $sedes->count();

            $usuarios_count = 0;
            // Se asume que en la tabla 'users' existe la columna 'id_sede' para relacionar un usuario con una sede.
            foreach ($sedes as $sede) {
                $usuarios_count += DB::table('users')->where('id_sede', $sede->id_sede)->count();
            }
            $empresa->usuarios_count = $usuarios_count;
        }

        $user = Auth::user();

        $data = [
            'page'      => 'Configuracion',
            'subpage'   => 'Empresa',
            'rol'       => $user->getRoleNames()->first(),
            'user'      => $user,
            'empresas'  => $empresas
        ];

        $alert = AdminHelper::get_count_alert($data['rol'], $user->id_sede); //gestorsede
        $data['alert'] = $alert;

        echo view('layouts.header', $data);
        echo view('layouts.nav', $data);
        echo view('layouts.navigation', $data);
        echo view('dashboard.empresas.index', $data);
        echo view('layouts.footer', $data);
    }

    // Guarda una nueva empresa en la base de datos.
    public function guardarEmpresa(Request $request) {
        $response = [
            'Status' => 500,
            'Message' => "Ocurrió un error al guardar la empresa.",
            'Success' => false,
            'Data' => null
        ];

        try {
            $nombre_empresa = $request->input('nombre-empresa');
            $descripcion_empresa = $request->input('descripcion-empresa');
            $tipo_documento = $request->input('tipo-documento');
            $numero_documento = $request->input('numero-documento');
            $plan_empresa = $request->input('plan-empresa');
            $plantilla_empresa = $request->input('plantilla-empresa');
            $logo_url = $request->input('logo-url');

            $data = DB::table('tb_empresa')->insert([
                'Nombre' => $nombre_empresa,
                'Descripcion' => $descripcion_empresa,
                'tipo_documento_empresa' => $tipo_documento,
                'documento_empresa' => $numero_documento,
                'plan_empresa' => $plan_empresa,
                'logo' => $logo_url,
                'id_plantilla' => $plantilla_empresa
            ]);

            $response = [
                'Status' => 200,
                'Message' => "La empresa se ha guardado exitosamente.",
                'Success' => true,
                'Data' => $data
            ];
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            $response['Message'] = "Error al guardar los datos de la nueva empresa.";
        }
        return response()->json($response);
    }

    /**
     * Actualiza la empresa en la base de datos.
     */
    public function actualizar(Request $request, $id) {
        $response = [
            'Status' => 500,
            'Message' => "Ocurrió un error al actualizar la empresa.",
            'Success' => false,
            'Data' => null
        ];

        try {
            $nombre_empresa = $request->input('nombre-empresa');
            $descripcion_empresa = $request->input('descripcion-empresa');
            $tipo_documento = $request->input('tipo-documento');
            $numero_documento = $request->input('numero-documento');
            $plan_empresa = $request->input('plan-empresa');
            $plantilla_empresa = $request->input('plantilla-empresa');
            $logo_url = $request->input('logo-url');

            if ($logo_url === '' || $logo_url === null) {
                $logo_url = null;
            }

            $data = DB::table('tb_empresa')->where('id_empresa', $id)->update([
                'Nombre' => $nombre_empresa,
                'Descripcion' => $descripcion_empresa,
                'tipo_documento_empresa' => $tipo_documento,
                'documento_empresa' => $numero_documento,
                'plan_empresa' => $plan_empresa,
                'logo' => $logo_url,
                'id_plantilla' => $plantilla_empresa
            ]);

            $response = [
                'Status' => 200,
                'Message' => "La empresa se ha actualizado exitosamente.",
                'Success' => true,
                'Data' => $data
            ];
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            $response['Message'] = "Error al actualizar los datos de la empresa.";
        }
        return response()->json($response);
    }

    // Cambia el estado de la empresa (activa/inactiva)
    public function cambiarEstado(Request $request) {
        $response = [
            'Status' => 500,
            'Message' => "Ocurrió un error al cambiar el estado de la empresa.",
            'Success' => false,
            'Data' => null
        ];

        try {
            $id_empresa = $request->input('id_empresa');
            $empresa = DB::Table('tb_empresa')
                ->select('id_empresa', 'Nombre', 'Descripcion', 'tipo_documento_empresa', 'documento_empresa', 'plan_empresa', 'logo', 'id_plantilla', 'estado')
                ->where('id_empresa', $id_empresa)
                ->first();
            
            $newEstado = ($empresa->estado == 1) ? 0 : 1;
            $data = DB::table('tb_empresa')->where('id_empresa', $id_empresa)->update(['estado' => $newEstado]);

            $response = [
                'Status' => 200,
                'Message' => "Se realizó el cambio de estado de la empresa exitosamente.",
                'Success' => true,
                'Data' => $data
            ];
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            $response['Message'] = "Error al cambiar el estado de la empresa.";
        }
        return response()->json($response);
    }

    // Guarda el logo de la empresa
    public function guardarLogo(Request $request) {
        $response = [
            'Status' => 500,
            'Message' => "Ocurrió un error al guardar la imagen del logo.",
            'Success' => false,
            'Data' => null
        ];
        
        try {
            $path = $request->file('file')->store('empresas/logos', 'public_html');

            $response = [
                'Status' => 200,
                'Message' => "El logo se ha guardado exitosamente.",
                'Success' => true,
                'Data' => $path
            ];
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            $response['Message'] = "Error al guardar los datos del nuevo logo.";
        }
        return response()->json($response);
    }

    // Obtiene los datos para el dashboard de la empresa
    public function obtenerDashboardEmpresa(Request $request) {
        $user = Auth::user();

        // Verifica si se ha pasado un ID de empresa en la solicitud
        if ($request && $request->has('id_empresa')) {
            $empresaUser = DB::table('tb_empresa')->where('id_empresa', $request->input('id_empresa'))->first();
        } else {
            $sedeUser = DB::table('tb_sede')->where('id_sede', $user->id_sede)->first();
            $empresaUser = DB::table('tb_empresa')->where('id_empresa', $sedeUser->id_empresa)->first();
        }

        // Se buscan los demás datos relevantes de la empresa
        $empresas = DB::table('tb_empresa')->orderBy('id_empresa', 'asc')->get();
        $sedesEmpresa = DB::table('tb_sede')->where('id_empresa', $empresaUser->id_empresa)->get();
        $empresaPaqueteActivo = DB::table('tb_empresa_paquete')
            ->where('id_empresa', $empresaUser->id_empresa)
            ->where('estado', 'ACTIVO')
            ->join('tb_paquete', 'tb_empresa_paquete.id_paquete', '=', 'tb_paquete.id_paquete')
            ->leftJoin('tb_pago_empresa', 'tb_empresa_paquete.id_empresa_paquete', '=', 'tb_pago_empresa.id_empresa_paquete')
            ->select(
                'tb_empresa_paquete.*',
                'tb_paquete.*',
                'tb_pago_empresa.*'
            )
            ->first();
        
        if ($empresaPaqueteActivo) {
            $empresaPaqueteActivo->citas_faltantes = $empresaPaqueteActivo->numero_citas - $empresaPaqueteActivo->citas_consumidas;

            // Se realiza el calculo de las citas según el paquete activo
            // Se obtienen las citas agendadas para el paquete activo
            $empresaPaqueteActivo->citasAgendadas = DB::table('tb_cita')
                ->join('tb_estado', 'tb_cita.id_estado_verificado', '=', 'tb_estado.id_estado')
                ->where('id_empresa_paquete', $empresaPaqueteActivo->id_empresa_paquete)
                ->where('tb_estado.nombre_estado', 'Agendado')
                ->count();
            
            // Se obtienen las citas canceladas para el paquete activo
            $empresaPaqueteActivo->citasCanceladas = DB::table('tb_cita')
                ->join('tb_estado', 'tb_cita.id_estado_verificado', '=', 'tb_estado.id_estado')
                ->where('id_empresa_paquete', $empresaPaqueteActivo->id_empresa_paquete)
                ->where('tb_estado.nombre_estado', 'Cancelo')
                ->count();
            
            // Se obtienen las citas asistidas para el paquete activo
            $empresaPaqueteActivo->citasAsistidas = DB::table('tb_cita')
                ->join('tb_estado', 'tb_cita.id_estado_verificado', '=', 'tb_estado.id_estado')
                ->where('id_empresa_paquete', $empresaPaqueteActivo->id_empresa_paquete)
                ->where('tb_estado.nombre_estado', 'Asistió')
                ->count();

            // Se obtienen las citas confirmadas para el paquete activo
            $empresaPaqueteActivo->citasConfirmadas = DB::table('tb_cita')
                ->join('tb_estado', 'tb_cita.id_estado_verificado', '=', 'tb_estado.id_estado')
                ->where('id_empresa_paquete', $empresaPaqueteActivo->id_empresa_paquete)
                ->where('tb_estado.nombre_estado', 'Confirma cita')
                ->count();
            
            // Se obtienen las citas no asistidas para el paquete activo
            $empresaPaqueteActivo->citasNoAsistidas = DB::table('tb_cita')
                ->join('tb_estado', 'tb_cita.id_estado_verificado', '=', 'tb_estado.id_estado')
                ->where('id_empresa_paquete', $empresaPaqueteActivo->id_empresa_paquete)
                ->where('tb_estado.nombre_estado', 'No Asistió')
                ->count();
            
            // Se obtienen las citas no contestadas para el paquete activo
            $empresaPaqueteActivo->citasNoContestadas = DB::table('tb_cita')
                ->join('tb_estado', 'tb_cita.id_estado_verificado', '=', 'tb_estado.id_estado')
                ->where('id_empresa_paquete', $empresaPaqueteActivo->id_empresa_paquete)
                ->where('tb_estado.nombre_estado', 'No contesta')
                ->count();
            
            // Se obtienen las citas duplicadas para el paquete activo
            $empresaPaqueteActivo->citasDuplicadas = DB::table('tb_cita')
                ->join('tb_estado', 'tb_cita.id_estado_verificado', '=', 'tb_estado.id_estado')
                ->where('id_empresa_paquete', $empresaPaqueteActivo->id_empresa_paquete)
                ->where('tb_estado.nombre_estado', 'Duplicado')
                ->count();
            
            // Se obtienen las citas asistido sede para el paquete activo
            $empresaPaqueteActivo->citasAsistidoSede = DB::table('tb_cita')
                ->join('tb_estado', 'tb_cita.id_estado_verificado', '=', 'tb_estado.id_estado')
                ->where('id_empresa_paquete', $empresaPaqueteActivo->id_empresa_paquete)
                ->where('tb_estado.nombre_estado', 'Asistido Sede')
                ->count();
            
            // Se obtienen las citas en seguimiento para el paquete activo
            $empresaPaqueteActivo->citasSeguimiento = DB::table('tb_cita')
                ->join('tb_estado', 'tb_cita.id_estado_verificado', '=', 'tb_estado.id_estado')
                ->where('id_empresa_paquete', $empresaPaqueteActivo->id_empresa_paquete)
                ->where('tb_estado.nombre_estado', 'Seguimiento')
                ->count();
            
            // Se obtienen las citas reprogramadas para el paquete activo
            $empresaPaqueteActivo->citasReprogramadas = DB::table('tb_cita')
                ->join('tb_estado', 'tb_cita.id_estado_verificado', '=', 'tb_estado.id_estado')
                ->where('id_empresa_paquete', $empresaPaqueteActivo->id_empresa_paquete)
                ->where('tb_estado.nombre_estado', 'Reprograma')
                ->count();
            
            // Se obtienen las citas no simit para el paquete activo
            $empresaPaqueteActivo->citasNoSimit = DB::table('tb_cita')
                ->join('tb_estado', 'tb_cita.id_estado_verificado', '=', 'tb_estado.id_estado')
                ->where('id_empresa_paquete', $empresaPaqueteActivo->id_empresa_paquete)
                ->where('tb_estado.nombre_estado', 'No Simit')
                ->count();
            
            // Se obtienen las citas existentes por mes actual para el paquete activo.
            $citasPorDiaAgendadas = DB::table('tb_cita')
                ->select(DB::raw('DAY(reserva_cita) as dia, COUNT(*) as total_citas'))
                ->where('id_empresa_paquete', $empresaPaqueteActivo->id_empresa_paquete)
                ->whereBetween('reserva_cita', [
                    now()->startOfMonth()->format('Y-m-d H:i:s'),
                    now()->endOfMonth()->format('Y-m-d H:i:s')
                ])
                ->groupBy(DB::raw('DAY(reserva_cita)'))
                ->orderBy('dia')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->dia => $item->total_citas];
                });

            // Completa los días faltantes
            $diasMes = range(1, now()->daysInMonth);
            $citasCompletasAgendadas = array_fill_keys($diasMes, 0);
            
            foreach ($citasPorDiaAgendadas as $dia => $total) {
                $citasCompletasAgendadas[$dia] = $total;
            }

            // Se guarda el valor resultante
            $empresaPaqueteActivo->citasAgendadasPorMesActual = array_values($citasCompletasAgendadas);

            // Se obtienen las citas asistidas por mes actual para el paquete activo.
            $citasPorDiaAsistidas = DB::table('tb_cita')
                ->select(DB::raw('DAY(reserva_cita) as dia, COUNT(*) as total_citas'))
                ->join('tb_estado', 'tb_cita.id_estado_verificado', '=', 'tb_estado.id_estado')
                ->where('id_empresa_paquete', $empresaPaqueteActivo->id_empresa_paquete)
                ->where('tb_estado.nombre_estado', 'Asistió')
                ->whereBetween('reserva_cita', [
                    now()->startOfMonth()->format('Y-m-d H:i:s'),
                    now()->endOfMonth()->format('Y-m-d H:i:s')
                ])
                ->groupBy(DB::raw('DAY(reserva_cita)'))
                ->orderBy('dia')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->dia => $item->total_citas];
                });
            
            // Completa los días faltantes
            $diasMes = range(1, now()->daysInMonth);
            $citasCompletasAsistidas = array_fill_keys($diasMes, 0);
            
            foreach ($citasPorDiaAsistidas as $dia => $total) {
                $citasCompletasAsistidas[$dia] = $total;
            }

            // Se guarda el valor resultante
            $empresaPaqueteActivo->citasAsistidasPorMesActual = array_values($citasCompletasAsistidas);

        }

        // Se obtienen los paquetes que pertenecen a esa empresa.
        $empresaPaquetes = DB::table('tb_empresa_paquete')
            ->where('id_empresa', $empresaUser->id_empresa)
            ->join('tb_paquete', 'tb_empresa_paquete.id_paquete', '=', 'tb_paquete.id_paquete')
            ->leftJoin('tb_pago_empresa', 'tb_empresa_paquete.id_empresa_paquete', '=', 'tb_pago_empresa.id_empresa_paquete')
            ->select(
                'tb_empresa_paquete.*',
                'tb_paquete.*',
                'tb_pago_empresa.*'
            )
            ->get();

        $empresa = [
            'empresa_user' => $empresaUser,
            'sedes_empresa' => $sedesEmpresa,
            'empresa_paquete_activo' => $empresaPaqueteActivo,
            'empresa_paquetes' => $empresaPaquetes,
        ];

        $data = [
            'page' => 'Empresa',
            'subpage' => 'Dashboard',
            'rol' => $user->getRoleNames()->first(),
            'user' => $user,
            'empresa' => $empresa,
            'empresas' => $empresas,
        ];

        $alert = AdminHelper::get_count_alert($data['rol'], $user->id_sede);
        $data['alert'] = $alert;

        echo view('layouts.header', $data);
        echo view('layouts.nav', $data);
        echo view('layouts.navigation', $data);
        echo view('dashboard.empresas.dashboard', $data);
        echo view('layouts.footer', $data);
    }
}

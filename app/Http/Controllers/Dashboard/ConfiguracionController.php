<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Helpers\AdminHelper;
use App\Models\User;
use Carbon\Carbon;

class ConfiguracionController extends Controller {
    public function index() {
        $user = auth()->user();

        $data = [
            'page'      => 'Configuracion',
            'subpage'   => 'General',
            'rol'       => $user->getRoleNames()->first(),
            'user'      => $user
        ];

        $alert = AdminHelper::get_count_alert($data['rol'], $user->id_sede); //gestorsede
        $data['alert'] = $alert;

        echo view('layouts.header', $data);
        echo view('layouts.nav', $data);
        echo view('layouts.navigation', $data);
        echo view('dashboard.configuracion.index', $data);
        echo view('layouts.footer', $data);
    }

    public function obtenerConfiguracionGeneral(Request $request) {
        if ($request->ajax()) {
            $response = [
                'Status' => 500,
                'Message' => "Ocurrió un error al consultar las configuraciones generales.",
                'Success' => false,
                'Data' => []
            ];

            try {
                $user = Auth::user();

                // Obtener los parámetros del request
                $length = $request->input('length');
                $start = $request->input('start');
                $draw = $request->input('draw');
                $filtro_clave_configuracion_general = $request->input('filtro_clave_configuracion_general');

                // Consulta base de configuraciones generales
                $query = DB::Table('tb_config');

                // Obtener el total de registros filtrados
                $recordsTotal = $query->count();

                //Aplicar la paginación y obtener la data
                $data = $query->skip($start)->take($length)->get();

                $response = [
                    'Status' => 200,
                    'Message' => "La consulta de las configuraciones generales ha respondido exitosamente.",
                    'Success' => true,
                    'Data' => [
                        'draw' => $draw,
                        'recordsTotal' => $recordsTotal,
                        'recordsFiltered' => $recordsTotal,
                        'configuraciones_generales' => $data
                    ]
                ];
            } catch (\Throwable $e) {
                Log::error($e->getMessage());
                $response['Message'] = "Error al obtener los datos de configuración general.";
            }
            return response()->json($response);
        }
    }

    public function guardarConfiguracionGeneral(Request $request) {
        if ($request->ajax()) {
            $response = [
                'Status' => 500,
                'Message' => "Ocurrió un error al guardar la configuración general.",
                'Success' => false,
                'Data' => []
            ];

            try {
                $clave = $request->input('clave-configuracion-general');
                $valor = $request->input('valor-configuracion-general');

                // Insertar nueva configuración general
                DB::Table('tb_config')->insert([
                    'config_key' => $clave,
                    'config_value' => $valor,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);

                $response = [
                    'Status' => 200,
                    'Message' => "La configuración general ha sido guardada exitosamente.",
                    'Success' => true,
                    'Data' => []
                ];
            } catch (\Throwable $e) {
                Log::error($e->getMessage());
                $response['Message'] = "Error al guardar la configuración general.";
            }
            return response()->json($response);
        }
    }

    public function actualizarConfiguracionGeneral(Request $request, $clave_original) {
        if ($request->ajax()) {
            $response = [
                'Status' => 500,
                'Message' => "Ocurrió un error al actualizar la configuración general.",
                'Success' => false,
                'Data' => []
            ];

            try {
                $clave = $request->input('clave-configuracion-general');
                $valor = $request->input('valor-configuracion-general');

                // Actualizar configuración general
                DB::Table('tb_config')
                    ->where('config_key', $clave_original)
                    ->update([
                        'config_key' => $clave,
                        'config_value' => $valor,
                        'updated_at' => Carbon::now()
                    ]);

                $response = [
                    'Status' => 200,
                    'Message' => "La configuración general ha sido actualizada exitosamente.",
                    'Success' => true,
                    'Data' => []
                ];
            } catch (\Throwable $e) {
                Log::error($e->getMessage());
                $response['Message'] = "Error al actualizar la configuración general.";
            }
            return response()->json($response);
        }
    }

    public function eliminarConfiguracionGeneral(Request $request) {
        if ($request->ajax()) {
            $response = [
                'Status' => 500,
                'Message' => "Ocurrió un error al eliminar la configuración general.",
                'Success' => false,
                'Data' => null
            ];

            try {
                $clave = $request->input('clave');
                $data = DB::table('tb_config')->where('config_key', $clave)->delete();

                $response = [
                    'Status' => 200,
                    'Message' => "La configuración general se ha eliminado exitosamente.",
                    'Success' => true,
                    'Data' => $data
                ];

            } catch (\Throwable $e) {
                Log::error($e->getMessage());
                $response['Message'] = "Error al eliminar la configuración general.";
            }
            return response()->json($response);
        }
    }
}

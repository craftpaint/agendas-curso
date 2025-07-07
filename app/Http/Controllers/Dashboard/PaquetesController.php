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

class PaquetesController extends Controller {
    public function index() {
        $paquetes = DB::table('tb_paquete')->orderBy('id_paquete', 'desc')->get();
        $user = Auth::user();

        $data = [
            'page'      => 'Paquete',
            'subpage'   => 'Listado',
            'rol'       => $user->getRoleNames()->first(),
            'user'      => $user,
            'paquetes'  => $paquetes
        ];

        $alert = AdminHelper::get_count_alert($data['rol'], $user->id_sede); //gestorsede
        $data['alert'] = $alert;

        echo view('layouts.header', $data);
        echo view('layouts.nav', $data);
        echo view('layouts.navigation', $data);
        echo view('dashboard.paquetes.index', $data);
        echo view('layouts.footer', $data);
    }

    public function obtenerPaquetes(Request $request) {
        if ($request->ajax()) {
            $response = [
                'Status' => 500,
                'Message' => "Ocurrió un error al consultar los paquetes registrados.",
                'Success' => false,
                'Data' => []
            ];

            try {
                $user = Auth::user();

                // Obtener los parámetros del request
                $length = $request->input('length');
                $start = $request->input('start');
                $draw = $request->input('draw');
                $filtro_nombre = $request->input('filtro_nombre');
                $filtro_tipo = $request->input('filtro_tipo');
                $filtro_valor = $request->input('filtro_valor');

                //Consulta base de paquetes;
                $query = DB::Table('tb_paquete')
                    ->select('id_paquete', 'nombre_paquete', 'descripcion_paquete', 'numero_citas', 'valor', 'tipo_paquete', 'deleted_at');

                if ($filtro_nombre) {
                    $palabras = preg_split('/\s+/', trim($filtro_nombre));
                    foreach ($palabras as $palabra) {
                        if (!empty($palabra)) {
                            $query->where(function ($q) use ($palabra) {
                                $q->where('nombre_paquete', 'like', '%' . $palabra . '%');
                            });
                        }
                    }
                }

                if ($filtro_tipo) {
                    $query->where('tipo_paquete', $filtro_tipo);
                }

                if ($filtro_valor) {
                    $palabras = preg_split('/\s+/', trim($filtro_valor));
                    foreach ($palabras as $palabra) {
                        if (!empty($palabra)) {
                            $query->where(function ($q) use ($palabra) {
                                $q->where('valor', 'like', '%' . $palabra . '%');
                            });
                        }
                    }
                }

                // Obtener el total de registros filtrados
                $recordsTotal = $query->count();

                //Aplicar la paginación y obtener la data
                $data = $query->skip($start)->take($length)->get();

                $response = [
                    'Status' => 200,
                    'Message' => "La consulta de los paquetes ha respondido exitosamente.",
                    'Success' => true,
                    'Data' => [
                        'draw' => $draw,
                        'recordsTotal' => $recordsTotal,
                        'recordsFiltered' => $recordsTotal,
                        'paquetes' => $data
                    ]
                ];
            } catch (\Throwable $e) {
                Log::error($e->getMessage());
                $response['Message'] = "Error al obtener los datos.";
            }
            return response()->json($response);
        }
    }

    public function cambioEstadoPaquete(Request $request) {
        $id_paquete = $request->input('id_paquete');
        $response = [
            'Status' => 500,
            'Message' => "Ocurrió un error al cambiar el estado del paquete.",
            'Success' => false,
            'Data' => null
        ];

        try {
            $paquete = DB::Table('tb_paquete')
                ->select('id_paquete', 'nombre_paquete', 'descripcion_paquete', 'numero_citas', 'valor', 'tipo_paquete', 'deleted_at')
                ->where('id_paquete', $id_paquete)
                ->first();
            
            $newDeletedAt = ($paquete->deleted_at == null) ? Carbon::now() : null;
            $data = DB::table('tb_paquete')->where('id_paquete', $id_paquete)->update(['deleted_at' => $newDeletedAt]);

            $response = [
                'Status' => 200,
                'Message' => "Se realizo el cambio de estado del paquete exitosamente.",
                'Success' => true,
                'Data' => $data
            ];        
        } catch (\Throwable $e) {
           Log::error($e->getMessage());
            $response['Message'] = "Error al actualizar el paquete indicado.";
        }
        return response()->json($response);
    }
}

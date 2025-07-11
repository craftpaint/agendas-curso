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
}

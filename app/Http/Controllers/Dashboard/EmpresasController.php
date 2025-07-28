<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Helpers\AdminHelper;
use App\Helpers\EmpresaHelper;
use App\Helpers\EstadisticasHelper;


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
        $data = [
            'page' => 'Empresa',
            'subpage' => 'Dashboard',
            'rol' => $user->getRoleNames()->first(),
            'user' => $user,
        ];

        $alert = AdminHelper::get_count_alert($data['rol'], $user->id_sede);
        $data['alert'] = $alert;

        echo view('layouts.header', $data);
        echo view('layouts.nav', $data);
        echo view('layouts.navigation', $data);
        echo view('dashboard.empresas.dashboard', $data);
        echo view('layouts.footer', $data);
    }

    public function obtenerDatosProgressBarDasboardEmpresa(Request $request) {
        $response = [
            'Status' => 500,
            'Message' => 'Ocurrió un error al consultar los datos para la barra de progreso del paquete.',
            'Success' => false,
            'Data' => null
        ];

        try {
            $id_empresa = $request->input('id_empresa');
            $data = EstadisticasHelper::obtenerDatosProgressBarDashboardEmpresa($id_empresa);

            if ($data) {
                $response['Status'] = 200;
                $response['Message'] = "Los datos han sido cargados exitosamente.";
                $response['Success'] = true;
                $response['Data'] = $data;
            } else {
                $response['Status'] = 200;
                $response['Success'] = true;
                $response['Message'] = "La empresa no posee un paquete activo para consultar los datos.";
            }
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            $response['Message'] = "Error al consultar los datos de la barra de progreso de la empresa.";
        }
        return response()->json($response);
    }

    public function obtenerDatosLineBarMixedDashboardEmpresa(Request $request) {
        $response = [
            'Status' => 500,
            'Message' => 'Ocurrió un error al consultar los datos para la barra del paquete.',
            'Success' => false,
            'Data' => null
        ];

        try {
            $id_empresa = $request->input('id_empresa');
            $data = EstadisticasHelper::obtenerDatosLineBarMixedDashboardEmpresa($id_empresa);

            if ($data) {
                $response['Status'] = 200;
                $response['Message'] = "Los datos han sido cargados exitosamente.";
                $response['Success'] = true;
                $response['Data'] = $data;
            } else {
                $response['Status'] = 200;
                $response['Success'] = true;
                $response['Message'] = "La empresa no posee un paquete activo para consultar los datos.";
            }
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            $response['Message'] = "Error al consultar los datos para la barra de lineas.";
        }
        return response()->json($response);
    }

    public function obtenerDatosHistorialPaquetes(Request $request) {
        $response = [
            'Status' => 500,
            'Message' => 'Ocurrió un error al consultar los datos del historial de paquete.',
            'Success' => false,
            'Data' => null
        ];

        try {
            $id_empresa = $request->input('id_empresa');
            $data = EstadisticasHelper::obtenerDatosHistorialPaquetes($id_empresa);

            if ($data) {
                $response['Status'] = 200;
                $response['Message'] = "Los datos han sido cargados exitosamente.";
                $response['Success'] = true;
                $response['Data'] = $data;
            } else {
                $response['Status'] = 200;
                $response['Success'] = true;
                $response['Message'] = "La empresa no posee paquetes comprados hasta la fecha.";
            }
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            $response['Message'] = "Error al consultar el historial de paquetes.";
        }
        return response()->json($response);
    }

    public function validarExistenciaEmpresa(Request $request) {
        $response = [
            'Status' => 500,
            'Message' => "Ocurrió un error al verificar la empresa",
            'Success' => false,
            'Data' => null
        ];

        try {
            $tipo_documento = $request->input('tipo_documento_empresa');
            $documento_empresa = $request->input('documento_empresa');

            $data = EmpresaHelper::validarExistenciaEmpresa($tipo_documento, $documento_empresa);
            $response = [
                'Status' => 200,
                'Success' => true,
                'Data' => $data
            ];

            if ($data) {
                $response['Message'] = "La empresa ha sido consultada exitosamente.";
            } else {
                $response['Message'] = "No existen datos de la empresa consultada.";
            }
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            $response['Message'] = "Error al consultar la empresa.";
        }
        return response()->json($response);
    }

    public function registrarEmpresaPaquete(Request $request) {
        $response = [
            'Status' => 500,
            'Message' => "Ocurrió un error al verificar la empresa",
            'Success' => false,
            'Data' => null
        ];

        try {
            $data_empresa = $request->input('data_empresa');
            $paquete_seleccionado = $request->input('paquete_seleccionado');

            $data = EmpresaHelper::registrarEmpresaPaquete($data_empresa, $paquete_seleccionado);
            $response = [
                'Status' => 200,
                'Success' => true,
                'Data' => $data
            ];

            if ($data) {
                $response['Message'] = "Se han registrado la empresa y el paquete exitosamente.";
            }
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            $response['Message'] = "Error al registrar el paquete y la empresa seleccionado.";
        }
        return response()->json($response);
    }

    public function handleWebhook(Request $request) {
        $response = [
            'Status' => 500,
            'Message' => "Ocurrió un error al recibir los datos de Wompi.",
            'Success' => false,
            'Data' => null
        ];

        try {
            $header = $request->header('x-event-checksum');
            $body = $request->getContent();

            $data = EmpresaHelper::handleWebhook($header, $body);

            if ($data) {
                $response['Status'] = 200;
                $response['Message'] = "Los datos han sido procesados exitosamente.";
                $response['Success'] = true;
                $response['Data'] = $data;
            } else {
                $response['Message'] = "No se pudo procesar correctamente los datos de Wompi.";
            }
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            $response['Message'] = "Error al procesar los datos recibidos de Wompi.";
        }
        return response()->json($response);
    }

    public function consultarEmpresaPorSede($id) {
        $response = [
            'Status' => 500,
            'Message' => "Ocurrió un error al consultar la empresa por sede.",
            'Success' => false,
            'Data' => null
        ];

        try {
            $data = EmpresaHelper::consultarEmpresaPorSede($id);

            if ($data) {
                $response['Status'] = 200;
                $response['Message'] = "Los datos han sido cargados exitosamente.";
                $response['Success'] = true;
                $response['Data'] = $data;
            } else {
                $response['Message'] = "No se encontró la empresa.";
            }
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            $response['Message'] = "Error al consultar la empresa por la sede.";
        }
        return response()->json($response);
    }

    public function consultarTodasEmpresas() {
        $response = [
            'Status' => 500,
            'Message' => "Ocurrió un error al consultar la empresa.",
            'Success' => false,
            'Data' => null
        ];

        try {
            $data = EmpresaHelper::consultarTodasEmpresas();

            if ($data) {
                $response['Status'] = 200;
                $response['Message'] = "Los datos han sido cargados exitosamente.";
                $response['Success'] = true;
                $response['Data'] = $data;
            } else {
                $response['Message'] = "No se encontró las empresas.";
            }
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            $response['Message'] = "Error al consultar la empresa.";
        }
        return response()->json($response);
    }

    public function consultarEmpresa($id) {
        $response = [
            'Status' => 500,
            'Message' => "Ocurrió un error al consultar la empresa.",
            'Success' => false,
            'Data' => null
        ];

        try {
            $data = EmpresaHelper::consultarEmpresa($id);

            if ($data) {
                $response['Status'] = 200;
                $response['Message'] = "Los datos han sido cargados exitosamente.";
                $response['Success'] = true;
                $response['Data'] = $data;
            } else {
                $response['Message'] = "No se encontró la empresa.";
            }
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            $response['Message'] = "Error al consultar la empresa.";
        }
        return response()->json($response);
    }
}

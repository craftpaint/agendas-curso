<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Helpers\AdminHelper;

class ClientesController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $data = [
            'page' => 'Clientes',
            'subpage' => 'Listado',
            'rol' => $user->getRoleNames()->first(),
            'user' => $user
        ];
        $alert = AdminHelper::get_count_alert($data['rol'], $user->id_sede); //gestorsede
        $data['alert'] = $alert;
        echo view('layouts.header', $data);
        echo view('layouts.nav', $data);
        echo view('layouts.navigation', $data);
        echo view('dashboard.clientes.index', $data);
        echo view('layouts.footer', $data);
    }
    //Obtener las actividades de la base de datos
    public function get_clientes(Request $request)
    {
        if ($request->ajax()) {
            $objLoad = ['validate' => false];
            //Ejecución de la funcion
            try {
                $length = $request->request->get('length');
                $start = $request->request->get('start');
                $draw = $request->request->get('draw');
                //Verificamos si existe un filtro de busqueda
                $search = $_POST['search']['value'];
                $sql = "SELECT * FROM tb_cliente LIMIT $start, $length";
                if ($search) {
                    $sql = "SELECT * FROM tb_cliente WHERE nombre_cliente LIKE '%$search%' OR doc_cliente LIKE '%$search%' OR telefono_cliente LIKE '%$search%' OR email_cliente LIKE '%$search%' LIMIT $start, $length";
                }
                //Ejecutamos la query
                $data = DB::select($sql);
                $total_response = 999999;
                //Retornamos la respuesta
                $objLoad = array(
                    "draw" => $draw,
                    "recordsTotal" => $total_response,
                    "recordsFiltered" => $total_response,
                    "data" => $data,
                    "validate" => true
                );
            } catch (\Throwable $e) {
                Log::error($e->getMessage());
                $objLoad['text'] = 'Error al obtener los datos';
            }
            //retornar respuesta
            return response()->json($objLoad);
        }
    }
    //Guardar cliente
    public function add()
    {
        $user = Auth::user();
        $data = [
            'page' => 'Clientes',
            'subpage' => 'Listado',
            'rol' => $user->getRoleNames()->first(),
            'user' => $user
        ];
        $alert = AdminHelper::get_count_alert($data['rol'], $user->id_sede); //gestorsede
        $data['alert'] = $alert;
        echo view('layouts.header', $data);
        echo view('layouts.nav', $data);
        echo view('layouts.navigation', $data);
        echo view('dashboard.clientes.add', $data);
        echo view('layouts.footer', $data);
    }
    //Obtener las actividades de la base de datos
    public function save(Request $request)
    {
        if ($request->ajax()) {
            $objLoad = [
                'validate' => false,
                'text' => 'Error al guardar la sede'
            ];
            //Ejecución de la funcion
            try {
                $id = DB::table('tb_cliente')->insertGetId([
                    'nombre_cliente'   => $request->nombre_cliente,
                    'apellido_cliente' => $request->apellido_cliente,
                    'email_cliente'    => $request->email_cliente,
                    'tipo_doc_cliente' => $request->tipo_doc_cliente,
                    'doc_cliente'      => $request->doc_cliente,
                    'telefono_cliente' => $request->telefono_cliente,
                    'desc_cliente'     => $request->desc_cliente,
                ]);

                if ($id) {
                    $objLoad = array(
                        "validate" => true,
                        "text" => 'Cliente guardado correctamente',
                        "id" => $id,
                        'nombre'   => $request->nombre_cliente . ' ' . $request->apellido_cliente
                    );
                }
            } catch (\Throwable $e) {
                Log::error($e->getMessage());
            }
            //retornar respuesta
            return response()->json($objLoad);
        }
    }
    //Ver cliente
    public function view($id)
    {
        $user = Auth::user();
        $data = [
            'page' => 'Clientes',
            'subpage' => 'Listado',
            'rol' => $user->getRoleNames()->first(),
            'user' => $user
        ];
        $alert = AdminHelper::get_count_alert($data['rol'], $user->id_sede); //gestorsede
        $data['alert'] = $alert;
        $data['cliente'] = AdminHelper::get_cliente_by_id($id);
        $data['vehiculos'] = AdminHelper::get_vehiculo_by_id_cliente($id);
        echo view('layouts.header', $data);
        echo view('layouts.nav', $data);
        echo view('layouts.navigation', $data);
        echo view('dashboard.clientes.view', $data);
        echo view('layouts.footer', $data);
    }
    //editar cliente
    public function edit($id)
    {
        $user = Auth::user();
        $data = [
            'page' => 'Clientes',
            'subpage' => 'Listado',
            'rol' => $user->getRoleNames()->first(),
            'user' => $user
        ];
        $alert = AdminHelper::get_count_alert($data['rol'], $user->id_sede); //gestorsede
        $data['alert'] = $alert;
        $data['cliente'] = AdminHelper::get_cliente_by_id($id);
        $data['vehiculos'] = AdminHelper::get_vehiculo_by_id_cliente($id);
        echo view('layouts.header', $data);
        echo view('layouts.nav', $data);
        echo view('layouts.navigation', $data);
        echo view('dashboard.clientes.edit', $data);
        echo view('layouts.footer', $data);
    }
    //Obtener las actividades de la base de datos
    public function update(Request $request)
    {
        if ($request->ajax()) {
            $objLoad = [
                'validate' => false,
                'text' => 'Error al guardar el cliente'
            ];
            //Ejecución de la funcion
            try {
                $id_cliente = $request->request->get('id_cliente');
                $nombre_cliente = $request->request->get('nombre_cliente');
                $apellido_cliente = $request->request->get('apellido_cliente');
                $email_cliente = $request->request->get('email_cliente');
                $tipo_doc_cliente = $request->request->get('tipo_doc_cliente');
                $doc_cliente = $request->request->get('doc_cliente');
                $telefono_cliente = $request->request->get('telefono_cliente');
                $desc_cliente = $request->request->get('desc_cliente');
                //Actualizamos al cliente
                $sql = "UPDATE tb_cliente SET nombre_cliente = '$nombre_cliente', apellido_cliente = '$apellido_cliente', email_cliente = '$email_cliente', tipo_doc_cliente = '$tipo_doc_cliente', doc_cliente = '$doc_cliente', telefono_cliente = '$telefono_cliente', desc_cliente = '$desc_cliente' WHERE id_cliente = $id_cliente";
                $update = DB::update($sql);
                if ($update) {
                    $objLoad = array(
                        "validate" => true,
                        "text" => 'Cliente actualizado correctamente',
                        "id" => $id_cliente
                    );
                }
            } catch (\Throwable $e) {
                Log::error($e->getMessage());
            }
            //retornar respuesta
            return response()->json($objLoad);
        }
    }
    //Eliminar cliente
    public function delete_cliente(Request $request)
    {
        if ($request->ajax()) {
            $objLoad = [
                'validate' => false,
                'text' => 'Error al borrar el cliente'
            ];
            try {
                $id = $request->request->get('id');

                //Verificamos si el cliente tiene vehiculos
                $sql = "SELECT * FROM tb_vehiculo WHERE id_cliente = $id";
                $data = DB::select($sql);
                if (is_array($data) && !empty($data)) {
                    $objLoad['text'] = 'El cliente tiene vehiculos asociados';
                    return response()->json($objLoad);
                }
                $sql = "DELETE FROM tb_cliente WHERE id_cliente = $id";
                $delete = DB::delete($sql);
                if ($delete) {
                    $objLoad = array(
                        "validate" => true,
                        "text" => 'Cliente eliminado correctamente'
                    );
                }
            } catch (\Throwable $e) {
                Log::error($e->getMessage());
            }
            return response()->json($objLoad);
        }
    }
    //Vechiculos
    public function vehiculos()
    {
        $user = Auth::user();
        $data = [
            'page' => 'Clientes',
            'subpage' => 'Vehiculos',
            'rol' => $user->getRoleNames()->first(),
            'user' => $user
        ];
        $alert = AdminHelper::get_count_alert($data['rol'], $user->id_sede); //gestorsede
        $data['alert'] = $alert;
        echo view('layouts.header', $data);
        echo view('layouts.nav', $data);
        echo view('layouts.navigation', $data);
        echo view('dashboard.clientes.vehiculos', $data);
        echo view('layouts.footer', $data);
    }
    //Obtener las actividades de la base de datos
    public function get_vehiculos(Request $request)
    {
        if ($request->ajax()) {
            $objLoad = ['validate' => false];
            //Ejecución de la funcion
            try {
                $length = $request->request->get('length');
                $start = $request->request->get('start');
                $draw = $request->request->get('draw');
                //Verificamos si existe un filtro de busqueda
                $search = $_POST['search']['value'];
                $sql = "SELECT * FROM tb_vehiculo as t1 INNER JOIN tb_cliente as t2 ON t1.id_cliente = t2.id_cliente  LIMIT $start, $length";
                if ($search) {
                    $sql = "SELECT * FROM tb_vehiculo as t1 INNER JOIN tb_cliente as t2 ON t1.id_cliente = t2.id_cliente  WHERE t1.placa_vehiculo LIKE '%$search%' LIMIT $start, $length";
                }
                //Ejecutamos la query
                $data = DB::select($sql);
                $total_response = 999999;
                //Retornamos la respuesta
                $objLoad = array(
                    "draw" => $draw,
                    "recordsTotal" => $total_response,
                    "recordsFiltered" => $total_response,
                    "data" => $data,
                    "validate" => true
                );
            } catch (\Throwable $e) {
                Log::error($e->getMessage());
                $objLoad['text'] = 'Error al obtener los datos';
            }
            //retornar respuesta
            return response()->json($objLoad);
        }
    }

    //Obtener las actividades de la base de datos
    public function get_vehiculos_by_id_cliente(Request $request)
    {
        if ($request->ajax()) {
            $objLoad = [
                'validate' => false,
                'text' => 'Error al obtener los datos',
            ];
            try {
                $id = $request->request->get('id_cliente');
                $sql = "SELECT * FROM tb_vehiculo as t1 INNER JOIN tb_cliente as t2 ON t1.id_cliente = t2.id_cliente  WHERE t1.id_cliente = $id";
                $vehiculos = DB::select($sql);
                if (is_array($vehiculos) && !empty($vehiculos)) {
                    $objLoad = [
                        'validate' => true,
                        'vehiculos' => $vehiculos,
                    ];
                }
            } catch (\Throwable $e) {
                Log::error($e->getMessage());
            }
            return response()->json($objLoad);
        }
    }
    //Agregar vehiculos
    public function add_vehiculos()
    {
        $user = Auth::user();
        $data = [
            'page' => 'Clientes',
            'subpage' => 'Vehiculos',
            'rol' => $user->getRoleNames()->first(),
            'user' => $user
        ];
        $alert = AdminHelper::get_count_alert($data['rol'], $user->id_sede); //gestorsede
        $data['alert'] = $alert;
        echo view('layouts.header', $data);
        echo view('layouts.nav', $data);
        echo view('layouts.navigation', $data);
        echo view('dashboard.clientes.add_vehiculos', $data);
        echo view('layouts.footer', $data);
    }
    //Obtener las actividades de la base de datos
    public function save_vehiculo(Request $request)
    {
        if ($request->ajax()) {
            $objLoad = [
                'validate' => false,
                'text' => 'Error al guardar el vehiculo'
            ];
            try {
                $id_cliente = $request->request->get('id_cliente');
                $tipo_vehiculo = $request->request->get('tipo_vehiculo');
                $placa_vehiculo = strtoupper($request->request->get('placa_vehiculo'));
                $modelo_vehiculo = $request->request->get('modelo_vehiculo');
                $sql = "INSERT INTO tb_vehiculo (id_cliente, tipo_vehiculo, placa_vehiculo, modelo_vehiculo) VALUES ($id_cliente, '$tipo_vehiculo', '$placa_vehiculo', '$modelo_vehiculo')";
                $save = DB::insert($sql);
                if ($save) {
                    $objLoad = array(
                        "validate" => true,
                        "text" => 'Vehiculo guardada correctamente',
                        "id" => DB::getPdo()->lastInsertId()
                    );
                }
            } catch (\Throwable $e) {
                Log::error($e->getMessage());
            }
            return response()->json($objLoad);
        }
    }
    //Obtener clientes y vehiculos
    public function get_clientes_in_vehicle()
    {
        $response = (object)['results' => []];
        if (isset($_GET['term'])) {
            $search = $_GET['term'];
            $sql = "SELECT * FROM tb_cliente WHERE nombre_cliente LIKE '%$search%' OR doc_cliente LIKE '%$search%' OR telefono_cliente LIKE '%$search%' OR email_cliente LIKE '%$search%' LIMIT 50";
            $data = DB::select($sql);
            if (is_array($data) && !empty($data)) {
                foreach ($data as $cliente) {
                    $response->results[] = (object)[
                        'id' => $cliente->id_cliente,
                        'text' => $cliente->tipo_doc_cliente . $cliente->doc_cliente . ': ' . $cliente->nombre_cliente . ' ' . $cliente->apellido_cliente
                    ];
                }
            }
        }
        echo json_encode($response);
    }
    //Ver sede
    public function edit_vehiculos($id)
    {
        $user = Auth::user();
        $data = [
            'page' => 'Clientes',
            'subpage' => 'Vehiculos',
            'rol' => $user->getRoleNames()->first(),
            'user' => $user
        ];
        $alert = AdminHelper::get_count_alert($data['rol'], $user->id_sede); //gestorsede
        $data['alert'] = $alert;
        $data['vehiculo'] = AdminHelper::get_vehiculo_by_id($id);
        $data['cliente'] = false;
        if (isset($data['vehiculo']['id_cliente']) && $data['vehiculo']['id_cliente'] != null) {
            $data['cliente'] = AdminHelper::get_cliente_by_id($data['vehiculo']['id_cliente']);
        }
        echo view('layouts.header', $data);
        echo view('layouts.nav', $data);
        echo view('layouts.navigation', $data);
        echo view('dashboard.clientes.edit_vehiculos', $data);
        echo view('layouts.footer', $data);
    }
    //Ver sede
    public function view_vehiculos($id)
    {
        $user = Auth::user();
        $data = [
            'page' => 'Clientes',
            'subpage' => 'Vehiculos',
            'rol' => $user->getRoleNames()->first(),
            'user' => $user
        ];
        $alert = AdminHelper::get_count_alert($data['rol'], $user->id_sede); //gestorsede
        $data['alert'] = $alert;
        $data['vehiculo'] = AdminHelper::get_vehiculo_by_id($id);
        $data['cliente'] = false;
        if (isset($data['vehiculo']['id_cliente']) && $data['vehiculo']['id_cliente'] != null) {
            $data['cliente'] = AdminHelper::get_cliente_by_id($data['vehiculo']['id_cliente']);
        }
        echo view('layouts.header', $data);
        echo view('layouts.nav', $data);
        echo view('layouts.navigation', $data);
        echo view('dashboard.clientes.view_vehiculos', $data);
        echo view('layouts.footer', $data);
    }
    //Obtener las actividades de la base de datos
    public function update_vehiculo(Request $request)
    {
        if ($request->ajax()) {
            $objLoad = [
                'validate' => false,
                'text' => 'Error al guardar el vehiculo'
            ];
            try {
                $id_vehiculo = $request->request->get('id_vehiculo');
                $id_cliente = $request->request->get('id_cliente');
                $tipo_vehiculo = $request->request->get('tipo_vehiculo');
                $placa_vehiculo = $request->request->get('placa_vehiculo');
                $modelo_vehiculo = $request->request->get('modelo_vehiculo');
                $sql = "UPDATE tb_vehiculo SET id_cliente = $id_cliente, tipo_vehiculo = '$tipo_vehiculo', placa_vehiculo = '$placa_vehiculo', modelo_vehiculo = '$modelo_vehiculo' WHERE id_vehiculo = $id_vehiculo";
                $update = DB::update($sql);
                if ($update) {
                    $objLoad = array(
                        "validate" => true,
                        "text" => 'Vehiculo actualizado correctamente',
                        "id" => $id_vehiculo
                    );
                }
            } catch (\Throwable $e) {
                Log::error($e->getMessage());
            }
            return response()->json($objLoad);
        }
    }
    //Eliminar cliente
    public function delete_vehiculo(Request $request)
    {
        if ($request->ajax()) {
            $objLoad = [
                'validate' => false,
                'text' => 'Error al borrar el cliente'
            ];
            try {
                $id = $request->request->get('id');
                $sql = "DELETE FROM tb_vehiculo WHERE id_vehiculo = $id";
                $delete = DB::delete($sql);
                if ($delete) {
                    $objLoad = array(
                        "validate" => true,
                        "text" => 'Vehiculo eliminado correctamente'
                    );
                }
            } catch (\Throwable $e) {
                Log::error($e->getMessage());
            }
            return response()->json($objLoad);
        }
    }
    //Descargar clientes y vehiculos
    public function dowload(Request $request)
    {
        if ($request->ajax()) {
            $objLoad = [
                'validate' => false,
                'text' => 'Error al borrar el estado'
            ];
            try {
                $start = $request->request->get('start');
                $type = $request->request->get('type');
                $length = 100;
                if ($type == "cliente") {
                    $sql = "SELECT * FROM tb_cliente LIMIT $start, $length";
                } else {
                    $sql = "SELECT * FROM tb_vehiculo as t1 INNER JOIN tb_cliente as t2 ON t1.id_cliente = t2.id_cliente  LIMIT $start, $length";
                }
                $records = DB::select($sql);
                if (empty($records)) {
                    return response()->json([
                        'status' => 'completed',
                        'url' => asset('data/data_' . $type . '.csv')
                    ]);
                }
                $filePath = 'data/data_' . $type . '.csv';
                $fullPath = public_path($filePath);
                if (!file_exists(public_path('data'))) {
                    mkdir(public_path('data'), 0777, true);
                }
                $file = fopen($fullPath, $start == 0 ? 'w' : 'a');
                if ($start == 0) {
                    if ($type == "cliente") {
                        fputcsv($file, [
                            'Nombre',
                            'Documento',
                            'Telefono',
                            'Email',
                            'Descripción'
                        ]);
                    } else {
                        fputcsv($file, [
                            'Vehiculo',
                            'Placa',
                            'Modelo',
                            'Nombre',
                            'Documento'
                        ]);
                    }
                }
                foreach ($records as $record) {
                    if ($type == "cliente") {
                        fputcsv($file, [
                            $record->nombre_cliente . ' ' . $record->apellido_cliente,
                            $record->tipo_doc_cliente . ' ' . $record->doc_cliente,
                            $record->telefono_cliente,
                            $record->email_cliente,
                            $record->desc_cliente
                        ]);
                    } else {
                        fputcsv($file, [
                            $record->tipo_vehiculo,
                            $record->placa_vehiculo,
                            $record->modelo_vehiculo,
                            $record->nombre_cliente . ' ' . $record->apellido_cliente,
                            $record->tipo_doc_cliente . ' ' . $record->doc_cliente,
                        ]);
                    }
                }
                fclose($file);
                return response()->json(['status' => 'in_progress', 'nextStart' => $start + $length]);
            } catch (\Throwable $e) {
                Log::error($e->getMessage());
            }
            return response()->json($objLoad);
        }
    }
}

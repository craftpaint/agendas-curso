<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Helpers\AdminHelper;

class SedesController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $data = [
            'page' => 'Sedes',
            'subpage' => 'Listado',
            'rol' => $user->getRoleNames()->first(),
            'user' => $user
        ];
        $alert = AdminHelper::get_count_alert($data['rol'], $user->id_sede); //gestorsede
        $data['alert'] = $alert;
        $alert = AdminHelper::get_count_alert($data['rol'], $user->id_sede); //gestorsede
        $data['alert'] = $alert;
        echo view('layouts.header', $data);
        echo view('layouts.nav', $data);
        echo view('dashboard.sedes.index', $data);
        echo view('layouts.footer', $data);
    }
    //Obtener las actividades de la base de datos
    public function get_sedes(Request $request)
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
                $sql = "SELECT * FROM tb_sede LIMIT " . $start . ", " . $length . "";
                if ($search) {
                    $sql = "SELECT * FROM tb_sede WHERE tel_sede LIKE '%" . $search . "%' OR idrun_sede LIKE '%" . $search . "%' OR nombre_sede LIKE '%" . $search . "%' LIMIT " . $start . ", " . $length . "";
                }
                //Ejecutamos la query
                $data = DB::select($sql);;
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
    //Ver sede
    public function add()
    {
        $user = Auth::user();
        $data = [
            'page' => 'Sedes',
            'subpage' => 'Listado',
            'rol' => $user->getRoleNames()->first(),
            'user' => $user
        ];
        $alert = AdminHelper::get_count_alert($data['rol'], $user->id_sede); //gestorsede
        $data['alert'] = $alert;
        $data['servicios'] = AdminHelper::get_servicios();
        $data['horarios'] = AdminHelper::get_horarios();
        $data['festivos'] = AdminHelper::get_festivos();
        echo view('layouts.header', $data);
        echo view('layouts.nav', $data);
        echo view('dashboard.sedes.add', $data);
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
                $idrun_sede = $request->request->get('idrun_sede');
                $nombre_sede = $request->request->get('nombre_sede');
                $tel_sede = $request->request->get('tel_sede');
                $estado_sede = $request->request->get('estado_sede');
                $id_servicio = $request->request->get('id_servicio');
                $direccion_sede = $request->request->get('direccion_sede');
                $festivos_sede = $request->input('festivos_sede');
                //Guardamo la sede
                $sql = "INSERT INTO tb_sede (idrun_sede, nombre_sede, direccion_sede, tel_sede, estado_sede, id_servicio, festivos_sede) VALUES ('" . $idrun_sede . "', '" . $nombre_sede . "', '" . $direccion_sede . "', '" . $tel_sede . "', '" . $estado_sede . "', '" . $id_servicio . "', '" . serialize($festivos_sede) . "')";
                $save = DB::insert($sql);
                if ($save) {
                    $id_sede = DB::getPdo()->lastInsertId();
                    //Guardamos los horarios
                    $a_dias = array('1', '2', '3', '4', '5', '6', '7');
                    foreach ($a_dias as $dia) {
                        if (isset($_POST['horario_' . $dia])) {
                            $horarios = $_POST['horario_' . $dia];
                            foreach ($horarios as $horario) {
                                if ($horario['id'] != '' && $horario['cupos'] != '') {
                                    $sql = "INSERT INTO tb_sede_horario (id_sede, id_horario, cupo_sede_horario, dia_sede_horario) VALUES ('" . $id_sede . "', '" . $horario['id'] . "', '" . $horario['cupos'] . "', '" . $dia . "')";
                                    DB::insert($sql);
                                    //Verificamos si viene full
                                    $semanaFull = $request->request->get('semanaFull');
                                    if ($semanaFull == 1) {
                                        $sql = "INSERT INTO tb_sede_horario (id_sede, id_horario, cupo_sede_horario, dia_sede_horario) VALUES ('" . $id_sede . "', '" . $horario['id'] . "', '" . $horario['cupos'] . "', '2')";
                                        DB::insert($sql);
                                        $sql = "INSERT INTO tb_sede_horario (id_sede, id_horario, cupo_sede_horario, dia_sede_horario) VALUES ('" . $id_sede . "', '" . $horario['id'] . "', '" . $horario['cupos'] . "', '3')";
                                        DB::insert($sql);
                                        $sql = "INSERT INTO tb_sede_horario (id_sede, id_horario, cupo_sede_horario, dia_sede_horario) VALUES ('" . $id_sede . "', '" . $horario['id'] . "', '" . $horario['cupos'] . "', '4')";
                                        DB::insert($sql);
                                        $sql = "INSERT INTO tb_sede_horario (id_sede, id_horario, cupo_sede_horario, dia_sede_horario) VALUES ('" . $id_sede . "', '" . $horario['id'] . "', '" . $horario['cupos'] . "', '5')";
                                        DB::insert($sql);
                                    }
                                }
                            }
                        }
                    }
                    $objLoad = array(
                        "validate" => true,
                        "text" => 'Sede guardada correctamente',
                        "id" => $id_sede
                    );
                }
            } catch (\Throwable $e) {
                Log::error($e->getMessage());
            }
            //retornar respuesta
            return response()->json($objLoad);
        }
    }
    //Ver sede
    public function edit($id)
    {
        $user = Auth::user();
        $data = [
            'page' => 'Sedes',
            'subpage' => 'Listado',
            'rol' => $user->getRoleNames()->first(),
            'user' => $user
        ];
        $alert = AdminHelper::get_count_alert($data['rol'], $user->id_sede); //gestorsede
        $data['alert'] = $alert;
        $data['sede'] = AdminHelper::get_sede_by_id($id);
        $data['sede_horarios'] = AdminHelper::get_sede_horarios($id);
        $data['servicios'] = AdminHelper::get_servicios();
        $data['horarios'] = AdminHelper::get_horarios();
        $data['festivos'] = AdminHelper::get_festivos();
        echo view('layouts.header', $data);
        echo view('layouts.nav', $data);
        echo view('dashboard.sedes.edit', $data);
        echo view('layouts.footer', $data);
    }
    //Obtener las actividades de la base de datos
    public function update(Request $request)
    {
        if ($request->ajax()) {
            $objLoad = [
                'validate' => false,
                'text' => 'Error al guardar la sede'
            ];
            try {
                $id_sede = $request->request->get('id_sede');
                $idrun_sede = $request->request->get('idrun_sede');
                $nombre_sede = $request->request->get('nombre_sede');
                $tel_sede = $request->request->get('tel_sede');
                $estado_sede = $request->request->get('estado_sede');
                $id_servicio = $request->request->get('id_servicio');
                $direccion_sede = $request->request->get('direccion_sede');
                $festivos_sede = $request->input('festivos_sede');
                //Verificamos si viene full
                $semanaFull = $request->request->get('semanaFull');
                $sql = "UPDATE tb_sede SET idrun_sede = '" . $idrun_sede . "', nombre_sede = '" . $nombre_sede . "', direccion_sede = '" . $direccion_sede . "', tel_sede = '" . $tel_sede . "', estado_sede = '" . $estado_sede . "', id_servicio = '" . $id_servicio . "', festivos_sede = '" . serialize($festivos_sede) . "' WHERE id_sede = " . $id_sede . "";
                DB::update($sql);
                $sql = "DELETE FROM tb_sede_horario WHERE id_sede = " . $id_sede . "";
                DB::delete($sql);
                $a_dias = array('1', '2', '3', '4', '5', '6', '7');
                foreach ($a_dias as $dia) {
                    if (isset($_POST['horario_' . $dia])) {
                        $horarios = $_POST['horario_' . $dia];
                        foreach ($horarios as $horario) {
                            if ($horario['id'] != '' && $horario['cupos'] != '') {
                                $sql = "INSERT INTO tb_sede_horario (id_sede, id_horario, cupo_sede_horario, dia_sede_horario) VALUES ('" . $id_sede . "', '" . $horario['id'] . "', '" . $horario['cupos'] . "', '" . $dia . "')";
                                DB::insert($sql);
                                if ($semanaFull == 1) {
                                    $sql = "INSERT INTO tb_sede_horario (id_sede, id_horario, cupo_sede_horario, dia_sede_horario) VALUES ('" . $id_sede . "', '" . $horario['id'] . "', '" . $horario['cupos'] . "', '2')";
                                    DB::insert($sql);
                                    $sql = "INSERT INTO tb_sede_horario (id_sede, id_horario, cupo_sede_horario, dia_sede_horario) VALUES ('" . $id_sede . "', '" . $horario['id'] . "', '" . $horario['cupos'] . "', '3')";
                                    DB::insert($sql);
                                    $sql = "INSERT INTO tb_sede_horario (id_sede, id_horario, cupo_sede_horario, dia_sede_horario) VALUES ('" . $id_sede . "', '" . $horario['id'] . "', '" . $horario['cupos'] . "', '4')";
                                    DB::insert($sql);
                                    $sql = "INSERT INTO tb_sede_horario (id_sede, id_horario, cupo_sede_horario, dia_sede_horario) VALUES ('" . $id_sede . "', '" . $horario['id'] . "', '" . $horario['cupos'] . "', '5')";
                                    DB::insert($sql);
                                }
                            }
                        }
                    }
                    if ($semanaFull == 1) {
                        break;
                    }
                }
                $objLoad = array(
                    "validate" => true,
                    "text" => 'Sede actualizada correctamente',
                    "id" => $id_sede
                );
            } catch (\Throwable $e) {
                Log::error($e->getMessage());
            }
            return response()->json($objLoad);
        }
    }
    //Vamos a borrar la sede
    public function delete_sede(Request $request)
    {
        if ($request->ajax()) {
            $objLoad = [
                'validate' => false,
                'text' => 'Error al borrar la sede'
            ];
            //Ejecución de la funcion
            try {
                $id = $request->request->get('id');
                $sql = "DELETE FROM tb_sede_horario WHERE id_sede = " . $id . "";
                DB::delete($sql);
                $sql = "DELETE FROM tb_sede WHERE id_sede = " . $id . "";
                DB::delete($sql);
                $objLoad = array(
                    "validate" => true,
                    "text" => 'Sede borrada correctamente'
                );
            } catch (\Throwable $e) {
                Log::error($e->getMessage());
            }
            //retornar respuesta
            return response()->json($objLoad);
        }
    }
    //Horarios
    public function configuracion()
    {
        $user = Auth::user();
        $data = [
            'page' => 'Sedes',
            'subpage' => 'Configuración',
            'rol' => $user->getRoleNames()->first(),
            'user' => $user
        ];
        $alert = AdminHelper::get_count_alert($data['rol'], $user->id_sede); //gestorsede
        $data['alert'] = $alert;
        echo view('layouts.header', $data);
        echo view('layouts.nav', $data);
        echo view('dashboard.sedes.configuracion', $data);
        echo view('layouts.footer', $data);
    }
    //Obtener las actividades de la base de datos
    public function get_horarios(Request $request)
    {
        if ($request->ajax()) {
            $objLoad = ['validate' => false];
            //Ejecución de la funcion
            try {
                $length = $request->request->get('length');
                $start = $request->request->get('start');
                $draw = $request->request->get('draw');
                $sql = "SELECT * FROM tb_horario ORDER BY inicio_horario ASC LIMIT " . $start . ", " . $length . "";
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
    public function add_horarios(Request $request)
    {
        if ($request->ajax()) {
            $objLoad = [
                'validate' => false,
                'text' => 'Error al guardar el horario'
            ];
            //Ejecución de la funcion
            try {
                $inicio_horario = $request->request->get('inicio_horario');
                $fin_horario = $request->request->get('fin_horario');
                $rango = date("h:i a", strtotime($inicio_horario)) . ' - ' . date("h:i a", strtotime($fin_horario));
                $sql = "INSERT INTO tb_horario (inicio_horario, fin_horario, rango_horario) VALUES ('" . $inicio_horario . "', '" . $fin_horario . "', '" . $rango . "')";
                $save = DB::insert($sql);
                if ($save) {
                    $objLoad = array(
                        "validate" => true,
                        "text" => 'Horario guardado correctamente'
                    );
                }
            } catch (\Throwable $e) {
                Log::error($e->getMessage());
            }
            return response()->json($objLoad);
        }
    }
    //Vamos a borrar la sede
    public function delete_horario(Request $request)
    {
        if ($request->ajax()) {
            $objLoad = [
                'validate' => false,
                'text' => 'Error al borrar el horario'
            ];
            //Ejecución de la funcion
            try {
                $id = $request->request->get('id');
                $sql = "SELECT * FROM tb_sede_horario WHERE id_horario = " . $id . "";
                $data = DB::select($sql);
                if (count($data) > 0) {
                    $objLoad['text'] = 'No se puede borrar el horario porque esta asignado a una sede';
                    return response()->json($objLoad);
                }
                $sql = "DELETE FROM tb_horario WHERE id_horario  = " . $id . "";
                DB::delete($sql);
                $objLoad = array(
                    "validate" => true,
                    "text" => 'Horario borrado correctamente'
                );
            } catch (\Throwable $e) {
                Log::error($e->getMessage());
            }
            //retornar respuesta
            return response()->json($objLoad);
        }
    }
    //Obtener las actividades de la base de datos
    public function get_festivos(Request $request)
    {
        if ($request->ajax()) {
            $objLoad = [
                'validate' => false,
                'text' => 'Error al obtener los datos'
            ];
            //Ejecución de la funcion
            try {
                $length = $request->request->get('length');
                $start = $request->request->get('start');
                $draw = $request->request->get('draw');
                $sql = "SELECT * FROM tb_festivos LIMIT " . $start . ", " . $length . "";
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
            }
            //retornar respuesta
            return response()->json($objLoad);
        }
    }
    //Obtener las actividades de la base de datos
    public function add_festivos(Request $request)
    {
        if ($request->ajax()) {
            $objLoad = [
                'validate' => false,
                'text' => 'Error al guardar el festivo'
            ];
            try {
                $festivo = $request->request->get('festivo');
                //Guardamos solo fecha
                $festivo = date("Y-m-d", strtotime($festivo));
                $sql = "INSERT INTO tb_festivos (fecha) VALUES ('" . $festivo . "')";
                $save = DB::insert($sql);
                if ($save) {
                    $objLoad = array(
                        "validate" => true,
                        "text" => 'Festivo guardado correctamente'
                    );
                }
            } catch (\Throwable $e) {
                Log::error($e->getMessage());
            }
            return response()->json($objLoad);
        }
    }
    //Vamos a borrar la sede
    public function delete_festivos(Request $request)
    {
        if ($request->ajax()) {
            $objLoad = [
                'validate' => false,
                'text' => 'Error al borrar el festivo'
            ];
            //Ejecución de la funcion
            try {
                $fecha = $request->request->get('fecha');
                $id = $request->request->get('id');
                $sql = "SELECT * FROM tb_sede WHERE festivos_sede LIKE '%" . $fecha . "%'";
                $data = DB::select($sql);
                if (count($data) > 0) {
                    $objLoad['text'] = 'No se puede borrar el festivo porque esta asignado a una sede';
                    return response()->json($objLoad);
                }
                $sql = "DELETE FROM tb_festivos WHERE id   = " . $id . "";
                DB::delete($sql);
                $objLoad = array(
                    "validate" => true,
                    "text" => 'Festivo borrado correctamente'
                );
            } catch (\Throwable $e) {
                Log::error($e->getMessage());
            }
            //retornar respuesta
            return response()->json($objLoad);
        }
    }
    //Obtener las servicios de la base de datos
    public function get_servicio(Request $request)
    {
        if ($request->ajax()) {
            $objLoad = ['validate' => false];
            //Ejecución de la funcion
            try {
                $length = $request->request->get('length');
                $start = $request->request->get('start');
                $draw = $request->request->get('draw');
                $sql = "SELECT * FROM tb_servicio LIMIT " . $start . ", " . $length . "";
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

    //Obtener las servicios de la base de datos
    public function get_servicio_by_id_sede(Request $request)
    {
        if ($request->ajax()) {
            $objLoad = [
                'validate' => false,
                'text' => 'Error al obtener los datos',
            ];
            //Ejecución de la funcion
            try {
                $id_sede = $request->request->get('id_sede');
                $sql = "SELECT * FROM tb_servicio as t1 INNER JOIN tb_sede as t2 ON t1.id_servicio = t2.id_servicio WHERE t2.id_sede = $id_sede";
                $data = DB::select($sql);
                //Retornamos la respuesta
                if (is_array($data) && !empty($data)) {
                    $objLoad = array(
                        "validate" => true,
                        "tipo_servicio" => $data,
                    );
                }
            } catch (\Throwable $e) {
                Log::error($e->getMessage());
                $objLoad['text'] = 'Error al obtener los datos';
            }
            //retornar respuesta
            return response()->json($objLoad);
        }
    }
    //agregar servicio de la base de datos
    public function add_servicio(Request $request)
    {
        if ($request->ajax()) {
            $objLoad = [
                'validate' => false,
                'text' => 'Error al guardar el servicio'
            ];
            try {
                $tipo_servicio = $request->request->get('tipo_servicio');
                $desc_servicio = $request->request->get('desc_servicio');
                $sql = "INSERT INTO tb_servicio (tipo_servicio, desc_servicio) VALUES ('" . $tipo_servicio . "', '" . $desc_servicio . "')";
                $save = DB::insert($sql);
                if ($save) {
                    $objLoad = array(
                        "validate" => true,
                        "text" => 'Servicio guardado correctamente'
                    );
                }
            } catch (\Throwable $e) {
                Log::error($e->getMessage());
            }
            return response()->json($objLoad);
        }
    }
    //Vamos a borrar la sede
    public function delete_servicio(Request $request)
    {
        if ($request->ajax()) {
            $objLoad = [
                'validate' => false,
                'text' => 'Error al borrar el servicio'
            ];
            //Ejecución de la funcion
            try {
                $id = $request->request->get('id');
                $sql = "SELECT * FROM tb_sede WHERE id_servicio  LIKE '%" . $id . "%'";
                $data = DB::select($sql);
                if (count($data) > 0) {
                    $objLoad['text'] = 'No se puede borrar el servicio porque esta asignado a una sede';
                    return response()->json($objLoad);
                }
                $sql = "DELETE FROM tb_servicio WHERE id_servicio    = " . $id . "";
                DB::delete($sql);
                $objLoad = array(
                    "validate" => true,
                    "text" => 'Servicio borrado correctamente'
                );
            } catch (\Throwable $e) {
                Log::error($e->getMessage());
            }
            //retornar respuesta
            return response()->json($objLoad);
        }
    }
}

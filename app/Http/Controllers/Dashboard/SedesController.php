<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Helpers\AdminHelper;

class SedesController extends Controller
{
    public function index() {
        $user = Auth::user();
        $data = [
            'page' => 'Sedes',
            'subpage' => 'Listado',
            'rol' => $user->getRoleNames()->first(),
            'user' => $user
        ];
        $empresas = DB::table('tb_empresa')->orderBy('id_empresa', 'asc')->get();
        $data['empresas'] = $empresas;
        $alert = AdminHelper::get_count_alert($data['rol'], $user->id_sede); //gestorsede
        $data['alert'] = $alert;
        $alert = AdminHelper::get_count_alert($data['rol'], $user->id_sede); //gestorsede
        $data['alert'] = $alert;
        echo view('layouts.header', $data);
        echo view('layouts.nav', $data);
        echo view('layouts.navigation', $data);
        echo view('dashboard.sedes.index', $data);
        echo view('layouts.footer', $data);
    }

    //Obtener las actividades de la base de datos
    public function get_sedes(Request $request) {
        if ($request->ajax()) {
            $objLoad = ['validate' => false];
            //Ejecución de la funcion
            try {

                $user = Auth::user();

                $length = $request->input('length', 10);
                $start = $request->input('start', 0);
                $draw = $request->input('draw');
                $search = $request->input('search.value');

                // Base query
                $query = DB::table('tb_sede as sede')
                    ->select(
                        'sede.*',
                        'empresa.Nombre as Nombre_empresa',
                        'ciudad.nombre as Nombre_ciudad',
                    )
                    ->leftJoin('tb_ciudad as ciudad', 'sede.id_ciudad', '=', 'ciudad.id_ciudad')
                    ->leftJoin('tb_empresa as empresa', 'sede.id_empresa', '=', 'empresa.id_empresa');

                // Si hay búsqueda
                if (!empty($search)) {
                    $query->where(function ($q) use ($search) {
                        $q->where('tel_sede', 'like', "%{$search}%")
                            ->orWhere('idrun_sede', 'like', "%{$search}%")
                            ->orWhere('nombre_sede', 'like', "%{$search}%");
                    });
                }
                if ($user->can('global.Pertenece a empresa aliada.v')) {
                    // Se asume que la tabla de sedes tiene la columna 'empresa_id'
                    $sede_usuario = DB::table('tb_sede')->where('id_sede', $user->id_sede)->first();
                    if ($sede_usuario && isset($sede_usuario->id_empresa)) {
                        $query->where('id_empresa', $sede_usuario->id_empresa);
                        $totalRecords = DB::table('tb_sede')->where('id_empresa', $sede_usuario->id_empresa)->count();
                    }
                } else {
                    // Si el usuario no tiene el permiso, se pueden mostrar todas las sedes
                    $totalRecords = DB::table('tb_sede')->count();
                }

                $totalFiltered = $query->count();

                // Obtener los datos paginados
                $data = $query
                    ->offset($start)
                    ->limit($length)
                    ->get();


                $objLoad = [
                    'draw' => intval($draw),
                    'recordsTotal' => $totalRecords,
                    'recordsFiltered' => $totalFiltered,
                    'data' => $data,
                    'validate' => true
                ];
            } catch (\Throwable $e) {
                Log::error($e->getMessage());
                $objLoad['text'] = 'Error al obtener los datos';
            }
            //retornar respuesta
            return response()->json($objLoad);
        }
    }

    //Ver sede
    public function add() {
        $user = Auth::user();
        $data = [
            'page' => 'Sedes',
            'subpage' => 'Listado',
            'rol' => $user->getRoleNames()->first(),
            'user' => $user
        ];
        $empresas = DB::table('tb_empresa')->orderBy('id_empresa', 'asc')->get();
        $data['empresas'] = $empresas;
        $ciudades = AdminHelper::get_ciudades_activas();
        $data['ciudades'] = $ciudades;
        $alert = AdminHelper::get_count_alert($data['rol'], $user->id_sede); //gestorsede
        $data['alert'] = $alert;
        $data['servicios'] = AdminHelper::get_servicios();
        $data['horarios'] = AdminHelper::get_horarios();
        $data['festivos'] = AdminHelper::get_festivos();
        echo view('layouts.header', $data);
        echo view('layouts.nav', $data);
        echo view('layouts.navigation', $data);
        echo view('dashboard.sedes.add', $data);
        echo view('layouts.footer', $data);
    }

    //Obtener las actividades de la base de datos
    public function save(Request $request) {
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
                $id_empresa = $request->request->get('id_empresa');
                $id_ciudad = $request->request->get('id_ciudad');
                $latitud_sede = $request->request->get('latitud_sede');
                $longitud_sede = $request->request->get('longitud_sede');
                $horario_sede = $request->request->get('horario_sede');
                //Guardamo la sede
                $save = DB::table('tb_sede')
                    ->insert([
                        'idrun_sede' => $idrun_sede,
                        'nombre_sede' => $nombre_sede,
                        'direccion_sede' => $direccion_sede,
                        'tel_sede' => $tel_sede,
                        'estado_sede' => $estado_sede,
                        'id_servicio' => $id_servicio,
                        'festivos_sede' => serialize($festivos_sede),
                        'id_empresa' => $id_empresa,
                        'id_ciudad' => $id_ciudad,
                        'latitud' => $latitud_sede,
                        'longitud' => $longitud_sede,
                        'horario' => $horario_sede
                    ]);
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
    public function edit($id) {
        $user = Auth::user();
        $data = [
            'page' => 'Sedes',
            'subpage' => 'Listado',
            'rol' => $user->getRoleNames()->first(),
            'user' => $user
        ];
        $empresas = DB::table('tb_empresa')->orderBy('id_empresa', 'asc')->get();
        $data['empresas'] = $empresas;
        $ciudades = AdminHelper::get_ciudades_activas();
        $data['ciudades'] = $ciudades;
        $alert = AdminHelper::get_count_alert($data['rol'], $user->id_sede); //gestorsede
        $data['alert'] = $alert;
        $data['sede'] = AdminHelper::get_sede_by_id($id);
        $data['sede_horarios'] = AdminHelper::get_sede_horarios($id);
        $data['servicios'] = AdminHelper::get_servicios();
        $data['horarios'] = AdminHelper::get_horarios();
        $data['festivos'] = AdminHelper::get_festivos();
        echo view('layouts.header', $data);
        echo view('layouts.nav', $data);
        echo view('layouts.navigation', $data);
        echo view('dashboard.sedes.edit', $data);
        echo view('layouts.footer', $data);
    }

    //Obtener las actividades de la base de datos
    public function update(Request $request) {
        if ($request->ajax()) {

            // 1. Validación de campos
            $data = $request->validate([
                'id_sede'         => 'required|integer|exists:tb_sede,id_sede',
                'idrun_sede'      => 'required|string|max:50',
                'nombre_sede'     => 'required|string|max:255',
                'tel_sede'        => 'required|string|max:50',
                'estado_sede'     => 'required|in:Activo,Inactivo',
                'id_servicio'     => 'required|integer|exists:tb_servicio,id_servicio',
                'direccion_sede'  => 'required|string',
                'festivos_sede'   => 'nullable|array',
                'festivos_sede.*' => 'date',
                'id_empresa'      => 'nullable|integer|exists:tb_empresa,id_empresa',
                'semanaFull'      => 'nullable|in:1',
                'horario_sede'    => 'required|string|max:255',
                'latitud_sede'    => 'required|string|max:50',
                'longitud_sede'   => 'required|string|max:50',
                'id_ciudad'       => 'required|integer|exists:tb_ciudad,id_ciudad',
            ]);

            $id_sede = $data['id_sede'];

            $objLoad = [
                'validate' => false,
                'text' => 'Error al guardar la sede'
            ];
            try {

                try {
                    DB::table('tb_sede')
                        ->where('id_sede', $id_sede)
                        ->update([
                            'idrun_sede'        => $data['idrun_sede'],
                            'nombre_sede'       => $data['nombre_sede'],
                            'direccion_sede'    => $data['direccion_sede'],
                            'tel_sede'          => $data['tel_sede'],
                            'estado_sede'       => $data['estado_sede'],
                            'id_servicio'       => $data['id_servicio'],
                            'id_empresa'        => $data['id_empresa'] ?? null,
                            'latitud'           => $data['latitud_sede'],
                            'longitud'          => $data['longitud_sede'],
                            'horario'           => $data['horario_sede'],
                            'id_ciudad'         => $data['id_ciudad'],
                            'festivos_sede'     => serialize($data['festivos_sede'] ?? []),
                        ]);
                } catch (\Throwable $e) {
                    Log::error('Error al actualizar la sede: ' . $e->getMessage());
                    return response()->json(['validate' => false, 'text' => 'Error al actualizar la sede']);
                }

                // 3. Eliminar horarios anteriores
                DB::table('tb_sede_horario')
                    ->where('id_sede', $id_sede)
                    ->delete();

                // 4. Preparar inserciones de horarios
                $inserts = [];
                $dias = range(1, 7); // 1=Lun ... 7=Dom

                foreach ($dias as $dia) {
                    $key = "horario_{$dia}";
                    $horariosDia = $request->input($key, []);

                    // Si estamos en martes–viernes y se marco semanaFull, saltamos:
                    if (! empty($data['semanaFull']) && $dia >= 2 && $dia <= 5) {
                        continue;
                    }

                    foreach ($horariosDia as $item) {
                        if (empty($item['id']) || empty($item['cupos'])) {
                            continue;
                        }

                        // inserción para el día original
                        $inserts[] = [
                            'id_sede'           => $id_sede,
                            'id_horario'        => $item['id'],
                            'cupo_sede_horario' => $item['cupos'],
                            'dia_sede_horario'  => $dia,
                        ];

                        // si es lunes y marcaste semanaFull, replicamos a Mart–Vie
                        if (! empty($data['semanaFull']) && $dia == 1) {
                            for ($d = 2; $d <= 5; $d++) {
                                $inserts[] = [
                                    'id_sede'           => $id_sede,
                                    'id_horario'        => $item['id'],
                                    'cupo_sede_horario' => $item['cupos'],
                                    'dia_sede_horario'  => $d,
                                ];
                            }
                        }
                    }
                }

                // 5. Insertar todos los horarios de una vez
                if (! empty($inserts)) {
                    DB::table('tb_sede_horario')->insert($inserts);
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
    public function delete_sede(Request $request) {
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
    public function configuracion() {
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
        echo view('layouts.navigation', $data);
        echo view('dashboard.sedes.configuracion', $data);
        echo view('layouts.footer', $data);
    }

    //Obtener las actividades de la base de datos
    public function get_horarios(Request $request) {
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
    public function add_horarios(Request $request) {
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
    public function delete_horario(Request $request) {
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
    public function get_festivos(Request $request) {
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
    public function add_festivos(Request $request) {
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
    public function delete_festivos(Request $request) {
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
    public function get_servicio(Request $request) {
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

    public static function get_ciudades(Request $request) {
        if ($request->ajax()) {
            $objLoad = ['validate' => false];
            //Ejecución de la funcion
            try {

                $user = Auth::user();

                $length = $request->request->get('length');
                $start = $request->request->get('start');
                $draw = $request->request->get('draw');
                $search = $request->input('search.value', '');

                $query = DB::table('tb_ciudad')
                    ->select('tb_ciudad.*');

                // Agregar filtro de búsqueda si existe
                if (!empty($search)) {
                    $query->where(function ($q) use ($search) {
                        $q->where('tb_ciudad.nombre', 'like', '%' . $search . '%');
                    });
                }


                // Obtener el total filtrado antes del paginado
                $totalFiltered = $query->count();

                // Aplicar paginación
                $ciudades = $query
                    ->offset($start)
                    ->limit($length)
                    ->get();



                //Retornamos la respuesta
                $objLoad = [
                    'status' => 200,
                    'validate' => true,
                    'draw' => $draw,
                    'data' => $ciudades,
                    'recordsTotal' => $totalFiltered, // O puedes usar User::count() si quieres el total sin filtro
                    'recordsFiltered' => $totalFiltered,
                    'message' => 'Ciudades obtenidas correctamente'
                ];
            } catch (\Throwable $e) {
                Log::error($e->getMessage());
                $objLoad = [
                    'status' => 500,
                    'message' => 'Error al obtener las ciudades',
                ];
            }
            //retornar respuesta
            return response()->json($objLoad);
        }
        try {
            $query = DB::table('tb_ciudades')->get();
            return $query->toArray();
        } catch (\Exception $e) {
            Log::error('get_servicios error: ' . $e->getMessage());
            return false;
        }
    }

    public function get_ciudad(Request $request) {
        if ($request->ajax()) {
            $objLoad = [
                'validate' => false,
                'status' => 500,
                'message' => 'Error al obtener las ciudades',
            ];
            //Ejecución de la funcion
            try {
                $id_ciudad = $request->request->get('id_ciudad');
                $data = DB::table('tb_ciudad')->where('id_ciudad', $id_ciudad)->first();
                $objLoad = array(
                    'status' => 200,
                    'validate' => true,
                    'data' => $data,
                    'message' => 'Ciudades obtenidas correctamente'
                );
            } catch (\Throwable $e) {
                Log::error($e->getMessage());
            }
            //retornar respuesta
            return response()->json($objLoad);
        }
    }

    //CREAR CIUDAD
    public function add_ciudad(Request $request) {
        if ($request->ajax()) {
            $objLoad = [
                'status' => 500,
                'validate' => false,
                'message' => 'Error al guardar la ciudad'
            ];
            try {
                $nombre = $request->request->get('nombre_ciudad');
                $longitud = $request->request->get('longitud_ciudad');
                $latitud = $request->request->get('latitud_ciudad');
                $nivel_zoom = $request->request->get('nivel_zoom_ciudad');
                $ahora = Carbon::now();
                $save = DB::table('tb_ciudad')->insert([
                    'nombre' => $nombre,
                    'longitud' => $longitud,
                    'latitud' => $latitud,
                    'nivel_zoom' => $nivel_zoom,
                    'estado' => 'ACTIVO',
                    'created_at' => $ahora,
                    'updated_at' => $ahora
                ]);
                if ($save) {
                    $objLoad = array(
                        "status" => 200,
                        "validate" => true,
                        "message" => 'La ciudad ha sido guardada correctamente'
                    );
                }
            } catch (\Throwable $e) {
                Log::error($e->getMessage());
            }
            return response()->json($objLoad);
        }
    }

    //ACTUALIZAR CIUDAD
    public function save_ciudad(Request $request) {
        if ($request->ajax()) {
            $objLoad = [
                'status' => 500,
                'validate' => false,
                'message' => 'Error al guardar la ciudad'
            ];
            //Ejecución de la funcion
            try {
                $id_ciudad = $request->request->get('id_ciudad');
                $nombre = $request->request->get('nombre_ciudad');
                $longitud = $request->request->get('longitud_ciudad');
                $latitud = $request->request->get('latitud_ciudad');
                $nivel_zoom = $request->request->get('nivel_zoom_ciudad');
                $upddate_at = carbon::now();

                $save = DB::table('tb_ciudad')
                    ->where('id_ciudad', $id_ciudad)
                    ->update([
                        'nombre' => $nombre,
                        'longitud' => $longitud,
                        'latitud' => $latitud,
                        'nivel_zoom' => $nivel_zoom,
                        'updated_at' => $upddate_at
                    ]);
                if ($save) {
                    $objLoad = array(
                        "status" => 200,
                        "validate" => true,
                        "message" => 'La ciudad ha sido guardada correctamente'
                    );
                }
            } catch (\Throwable $e) {
                Log::error($e->getMessage());
            }
            //retornar respuesta
            return response()->json($objLoad);
        }
    }

    //CAMBIAR ESTADO CIUDAD
    public function change_estado_ciudad(Request $request) {
        if ($request->ajax()) {
            $objLoad = [
                'status' => 500,
                'validate' => false,
                'message' => 'Error al cambiar el estado de la ciudad'
            ];
            try {
                $id_ciudad = $request->request->get('id_ciudad');
                $estado = $request->request->get('estado');
                $ahora = Carbon::now();
                if ($estado == 'ACTIVO') {
                    $delete_at = $ahora;
                    $nuevo_estado = 'DESACTIVADO';
                } else {
                    $delete_at = null;
                    $nuevo_estado = 'ACTIVO';
                }
                $save = DB::table('tb_ciudad')
                    ->where('id_ciudad', $id_ciudad)
                    ->update([
                        'estado' => $nuevo_estado,
                        'updated_at' => $ahora,
                        'deleted_at' => $delete_at
                    ]);
                if ($save) {
                    $objLoad = array(
                        "status" => 200,
                        "validate" => true,
                        "message" => 'La ciudad ha sido guardada correctamente'
                    );
                }
            } catch (\Throwable $e) {
                Log::error($e->getMessage());
            }
            return response()->json($objLoad);
        }
    }

    //Obtener las servicios de la base de datos
    public function get_servicio_by_id_sede(Request $request) {
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
    public function add_servicio(Request $request) {
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
    public function delete_servicio(Request $request) {
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

    public function obtenerUbicaciones() {
        // Obtener todas las ciudades con sus sedes relacionadas
        $ciudades = DB::table('tb_ciudad')
            ->select('id_ciudad', 'nombre', 'latitud', 'longitud', 'nivel_zoom')
            ->where('estado', 'ACTIVO')
            ->get();

        $resultado = [];

        foreach ($ciudades as $ciudad) {
            // Obtener sedes de la ciudad
            $sedes = DB::table('tb_sede')
                ->where('id_ciudad', $ciudad->id_ciudad)
                ->where('estado_sede', 'Activo')
                ->get();

            $tab_ubicacion = [];
            foreach ($sedes as $sede) {
                $tab_ubicacion[] = [
                    'titulo'            => $sede->nombre_sede,
                    'latitud_tienda'    => $sede->latitud,
                    'longitud_tienda'   => $sede->longitud,
                    'direccion'         => $sede->direccion_sede,
                    'telefono'          => $sede->tel_sede,
                    'horario'           => $sede->horario,
                    'complemento_iframe' => $sede->id_sede, // Puedes cambiar esto según tu lógica
                ];
            }

            $resultado[] = [
                'nombre_ubicacion'   => $ciudad->nombre,
                'latitud_ubicacion'  => $ciudad->latitud,
                'longitud_ubicacion' => $ciudad->longitud,
                'zoom'               => $ciudad->nivel_zoom,
                'tab_ubicacion'      => $tab_ubicacion,
            ];
        }

        return response()->json($resultado);
    }

    public function validarSedeParticipante($contact_id) {
        $respuesta = [
            'Status' => 500,
            'Message' => 'Error al validar si la sede es participante',
            'Success' => false,
            'Data' => null
        ];

        try {
            if($contact_id) {
                $ciudad = DB::table('tb_cita')
                    ->join('tb_sede', 'tb_cita.id_sede', '=', 'tb_sede.id_sede')
                    ->join('tb_ciudad', 'tb_sede.id_ciudad', '=', 'tb_ciudad.id_ciudad')
                    ->where('tb_cita.id_whatsapp_sendpulse', $contact_id)
                    ->orderBy('tb_cita.id_cita', 'desc')
                    ->select('tb_ciudad.*')
                    ->first();

                if ($ciudad && !empty($ciudad)) {
                    if (mb_stripos($ciudad->nombre, 'Bogotá') !== false) {
                        $respuesta['Status'] = 200;
                        $respuesta['Message'] = 'La sede es participante del concurso.';
                        $respuesta['Success'] = true;
                        $respuesta['Data'] = true;
                    } else {
                        $respuesta['Status'] = 200;
                        $respuesta['Message'] = 'La sede no es participante del concurso.';
                        $respuesta['Success'] = true;
                        $respuesta['Data'] = false;
                    }
                } else {
                    $respuesta['Message'] = 'No se encontró una sede participante para el contact_id proporcionado';
                }
            } else {
                $respuesta['Message'] = 'No se proporcionó el contact_id';
            }
        } catch (\Throwable $e) {
            Log::error('Error al validar si la sede es participante: ' . $e->getMessage());
            $respuesta['Message'] = 'Error al validar si la sede es participante';
        }
        return response()->json($respuesta);
    }
}

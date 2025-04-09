<?php

namespace App\Http\Controllers\Dashboard;

use App\Services\SendPulseService;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Helpers\AdminHelper;
use App\Models\User;
use Carbon\Carbon;


class CitasController extends Controller
{
    protected $sendPulse;

    public function __construct(SendPulseService $sendPulse)
    {
        $this->sendPulse = $sendPulse;
    }
    public function index()
    {
        $user = Auth::user();
        $data = [
            'page' => 'Citas',
            'subpage' => 'Listado',
            'rol' => $user->getRoleNames()->first(),
            'user' => $user
        ];
        $data['alert'] = AdminHelper::get_count_alert($data['rol'], $user->id_sede); //gestorsede
        //Verificamos si las alertas tiene mas de 0 para cambiar el estado de la alerta
        if ($data['alert'] > 0) {
            AdminHelper::change_status_alert($data['rol'], $user->id_sede);
        }
        //Sedes
        if ($data['rol'] == 'gestorsede') {
            $sql = "SELECT * FROM tb_sede WHERE id_sede = " . $user->id_sede;
        } else {
            $sql = "SELECT * FROM tb_sede";
        }
        $data['sedes'] = DB::select($sql);;
        //Estados
        $sql = "SELECT * FROM tb_estado";
        $data['estados'] = DB::select($sql);

        $agentes = User::role('callcenter')
            ->where('callcenter_habilitado', 1)
            ->orderBy('id', 'asc')
            ->get();
        // log::info($agentes);
        $lideragentes = User::role('lidercallcenter')
            ->where('callcenter_habilitado', 1)
            ->orderBy('id', 'asc')
            ->get();

        $agentes = $agentes->merge($lideragentes);
        $data['agentes'] = $agentes->toArray();

        // Si quieres loguearlo en formato colección:
        log::info(collect($data['agentes']));

        $agentes2 = User::role('callcenter')
            ->orderBy('id', 'asc')
            ->get();
        // log::info($agentes);
        $lideragentes2 = User::role('lidercallcenter')
            ->orderBy('id', 'asc')
            ->get();

        $agentes2 = $agentes2->merge($lideragentes2);
        $data['listado_agentes'] = $agentes2->toArray();

        // Si quieres loguearlo en formato colección:
        //log::info(collect($data['agentes']));
        //Servicios Liquidador
        $sql = "SELECT * FROM tb_servicio_liquidador";
        $data['servicios_liquidador'] = DB::select($sql);
        //Origenes
        $sql = "SELECT DISTINCT origen FROM tb_cita WHERE origen IS NOT NULL ORDER BY origen ASC;";
        $data['origenes'] = DB::select($sql);
        $data['tipoSede'] = "";
        echo view('layouts.header', $data);
        echo view('layouts.nav', $data);
        echo view('dashboard.citas.index', $data);
        echo view('layouts.footer', $data);
    }
    public function indexCDA()
    {
        $user = Auth::user();
        $data = [
            'page' => 'Citas',
            'subpage' => 'ListadoCDA',
            'rol' => $user->getRoleNames()->first(),
            'user' => $user
        ];
        $data['alert'] = AdminHelper::get_count_alert($data['rol'], $user->id_sede); //gestorsede
        //Verificamos si las alertas tiene mas de 0 para cambiar el estado de la alerta
        if ($data['alert'] > 0) {
            AdminHelper::change_status_alert($data['rol'], $user->id_sede);
        }
        //Sedes
        if ($data['rol'] == 'gestorsede') {
            $sql = "SELECT * FROM tb_sede WHERE id_sede = " . $user->id_sede;
        } else {
            $sql = "SELECT * FROM tb_sede";
        }
        $data['sedes'] = DB::select($sql);;
        //Estados
        $sql = "SELECT * FROM tb_estado";
        $data['estados'] = DB::select($sql);
        //Servicios Liquidador
        $sql = "SELECT * FROM tb_servicio_liquidador";
        $data['servicios_liquidador'] = DB::select($sql);
        //Origenes
        $sql = "SELECT DISTINCT origen FROM tb_cita WHERE origen IS NOT NULL ORDER BY origen ASC;";
        $data['origenes'] = DB::select($sql);
        $data['tipoSede'] = "CDA";
        echo view('layouts.header', $data);
        echo view('layouts.nav', $data);
        echo view('dashboard.citas.index', $data);
        echo view('layouts.footer', $data);
    }
    public function indexCIA()
    {
        $user = Auth::user();
        $data = [
            'page' => 'Citas',
            'subpage' => 'ListadoCIA',
            'rol' => $user->getRoleNames()->first(),
            'user' => $user
        ];
        $data['alert'] = AdminHelper::get_count_alert($data['rol'], $user->id_sede); //gestorsede
        //Verificamos si las alertas tiene mas de 0 para cambiar el estado de la alerta
        if ($data['alert'] > 0) {
            AdminHelper::change_status_alert($data['rol'], $user->id_sede);
        }
        //Sedes
        if ($data['rol'] == 'gestorsede') {
            $sql = "SELECT * FROM tb_sede WHERE id_sede = " . $user->id_sede;
        } else {
            $sql = "SELECT * FROM tb_sede";
        }
        $data['sedes'] = DB::select($sql);;
        //Estados
        $sql = "SELECT * FROM tb_estado";
        $data['estados'] = DB::select($sql);
        //Servicios Liquidador
        $sql = "SELECT * FROM tb_servicio_liquidador";
        $data['servicios_liquidador'] = DB::select($sql);
        //Origenes
        $sql = "SELECT DISTINCT origen FROM tb_cita WHERE origen IS NOT NULL ORDER BY origen ASC;";
        $data['origenes'] = DB::select($sql);
        $data['tipoSede'] = "CIA";
        echo view('layouts.header', $data);
        echo view('layouts.nav', $data);
        echo view('dashboard.citas.index', $data);
        echo view('layouts.footer', $data);
    }
    public function indexCRC()
    {
        $user = Auth::user();
        $data = [
            'page' => 'Citas',
            'subpage' => 'ListadoCRC',
            'rol' => $user->getRoleNames()->first(),
            'user' => $user
        ];
        $data['alert'] = AdminHelper::get_count_alert($data['rol'], $user->id_sede); //gestorsede
        //Verificamos si las alertas tiene mas de 0 para cambiar el estado de la alerta
        if ($data['alert'] > 0) {
            AdminHelper::change_status_alert($data['rol'], $user->id_sede);
        }
        //Sedes
        if ($data['rol'] == 'gestorsede') {
            $sql = "SELECT * FROM tb_sede WHERE id_sede = " . $user->id_sede;
        } else {
            $sql = "SELECT * FROM tb_sede";
        }
        $data['sedes'] = DB::select($sql);;
        //Estados
        $sql = "SELECT * FROM tb_estado";
        $data['estados'] = DB::select($sql);
        //Servicios Liquidador
        $sql = "SELECT * FROM tb_servicio_liquidador";
        $data['servicios_liquidador'] = DB::select($sql);
        //Origenes
        $sql = "SELECT DISTINCT origen FROM tb_cita WHERE origen IS NOT NULL ORDER BY origen ASC;";
        $data['origenes'] = DB::select($sql);
        $data['tipoSede'] = "CRC";
        echo view('layouts.header', $data);
        echo view('layouts.nav', $data);
        echo view('dashboard.citas.index', $data);
        echo view('layouts.footer', $data);
    }
    public function indexCEA()
    {
        $user = Auth::user();
        $data = [
            'page' => 'Citas',
            'subpage' => 'ListadoCEA',
            'rol' => $user->getRoleNames()->first(),
            'user' => $user
        ];
        $data['alert'] = AdminHelper::get_count_alert($data['rol'], $user->id_sede); //gestorsede
        //Verificamos si las alertas tiene mas de 0 para cambiar el estado de la alerta
        if ($data['alert'] > 0) {
            AdminHelper::change_status_alert($data['rol'], $user->id_sede);
        }
        //Sedes
        if ($data['rol'] == 'gestorsede') {
            $sql = "SELECT * FROM tb_sede WHERE id_sede = " . $user->id_sede;
        } else {
            $sql = "SELECT * FROM tb_sede";
        }
        $data['sedes'] = DB::select($sql);;
        //Estados
        $sql = "SELECT * FROM tb_estado";
        $data['estados'] = DB::select($sql);
        //Servicios Liquidador
        $sql = "SELECT * FROM tb_servicio_liquidador";
        $data['servicios_liquidador'] = DB::select($sql);
        //Origenes
        $sql = "SELECT DISTINCT origen FROM tb_cita WHERE origen IS NOT NULL ORDER BY origen ASC;";
        $data['origenes'] = DB::select($sql);
        $data['tipoSede'] = "CEA";
        echo view('layouts.header', $data);
        echo view('layouts.nav', $data);
        echo view('dashboard.citas.index', $data);
        echo view('layouts.footer', $data);
    }
    //Obtener las citas de la base de datos
    public function get_citas(Request $request)
    {
        if ($request->ajax()) {
            $objLoad = ['validate' => false];

            try {
                $user = Auth::user();
                $rol = $user->getRoleNames()->first();

                // Obtener los parámetros del request
                $length = $request->input('length');
                $start = $request->input('start');
                $draw = $request->input('draw');
                $tipo_cita = $request->input('tipo_cita');
                $filtro_dia = $request->input('filtro_dia');
                $filtro_dia_end = $request->input('filtro_dia_end');
                $filtro_sede = $request->input('filtro_sede');
                $filtro_estado = $request->input('filtro_estado');
                $filtro_estado_verificado = $request->input('filtro_estado_verificado');
                $filtro_responsable = $request->input('filtro_responsable');
                $filtro_origen = $request->input('filtro_origen');
                $filtro_agente = $request->input('filtro_agente');
                $filtro_search = $request->input('filtro_search');
                $fecha_actual = date('Y-m-d');

                // Construir el query base utilizando el Query Builder
                $query = DB::table('tb_cita as t1')
                    ->select(
                        't1.*',
                        't1.created_at as fecha_create',
                        't2.nombre_cliente',
                        't2.apellido_cliente',
                        't2.doc_cliente',
                        't2.tipo_doc_cliente',
                        't2.telefono_cliente',
                        't2.email_cliente',
                        't3.id_estado as estado_actual_id',
                        't3.nombre_estado as estado_actual_nombre',
                        't3.color_estado as estado_actual_color',
                        't4.id_estado as estado_verificado_id',
                        't4.nombre_estado as estado_verificado_nombre',
                        't4.color_estado as estado_verificado_color',
                        't5.nombre_sede',
                        't5.id_servicio',
                        't6.tipo_servicio',
                        'a.name as agente_callcenter',
                        'l.id_liquidador',
                        'l.estado_liquidador',
                        'l.comentario_liquidador',
                        'l.pago_liquidador',
                        's.id_servicio_liquidador',
                        's.nombre_servicio_liquidador',
                        's.valor_servicio_liquidador',
                        's.color_servicio_liquidador',
                        'v.id_vehiculo',
                        'v.placa_vehiculo',
                        'v.tipo_vehiculo',
                        'v.modelo_vehiculo'
                    )
                    ->addSelect(DB::raw("(SELECT COUNT(*) FROM tb_seguimiento AS ts WHERE ts.id_cita = t1.id_cita) AS total_anotaciones"))
                    ->join('tb_cliente as t2', 't1.id_cliente', '=', 't2.id_cliente')
                    ->join('tb_estado as t3', 't1.id_estado', '=', 't3.id_estado')
                    ->join('tb_estado as t4', 't1.id_estado_verificado', '=', 't4.id_estado')
                    ->join('tb_sede as t5', 't1.id_sede', '=', 't5.id_sede')
                    ->join('tb_servicio as t6', 't5.id_servicio', '=', 't6.id_servicio')
                    ->join('users as a', 't1.id_agente_callcenter', '=', 'a.id')
                    ->leftJoin('tb_liquidador as l', 't1.id_cita', '=', 'l.id_cita')
                    ->leftJoin('tb_servicio_liquidador as s', 't1.id_servicio_liquidador', '=', 's.id_servicio_liquidador')
                    ->leftJoin('tb_vehiculo as v', 't1.id_vehiculo', '=', 'v.id_vehiculo')
                    ->where('t1.id_cita', '>', 0);

                // Aplicar filtros por rol
                if ($rol == 'gestorsede') {
                    $query->where('t5.id_sede', $user->id_sede);
                }
                if ($rol == 'callcenter') {
                    $query->where('t1.id_agente_callcenter', $user->id);
                }

                // Filtros de fechas
                if ($filtro_dia && $filtro_dia_end) {
                    $query->whereBetween('t1.reserva_cita', [$filtro_dia, $filtro_dia_end]);
                } elseif ($filtro_dia) {
                    $query->where('t1.reserva_cita', $filtro_dia);
                } else {
                    $query->where('t1.reserva_cita', '>=', $fecha_actual);
                }

                // Otros filtros
                if ($filtro_sede) {
                    if (is_array($filtro_sede)) {
                        $query->whereIn('t5.id_sede', $filtro_sede);
                    } else {
                        $query->where('t5.id_sede', $filtro_sede);
                    }
                }

                if ($filtro_estado) {
                    if (is_array($filtro_estado)) {
                        $query->whereIn('t3.id_estado', $filtro_estado);
                    } else {
                        $query->where('t3.id_estado', $filtro_estado);
                    }
                }

                if ($filtro_estado_verificado) {
                    if (is_array($filtro_estado_verificado)) {
                        $query->whereIn('t4.id_estado', $filtro_estado_verificado);
                    } else {
                        $query->where('t4.id_estado', $filtro_estado_verificado);
                    }
                }

                if ($filtro_responsable) {
                    if (is_array($filtro_responsable)) {
                        $query->whereIn('t1.responsable_origen', $filtro_responsable);
                    } else {
                        $query->where('t1.responsable_origen', $filtro_responsable);
                    }
                }

                if ($filtro_origen) {
                    if (is_array($filtro_origen)) {
                        $query->whereIn('t1.origen', $filtro_origen);
                    } else {
                        $query->where('t1.origen', $filtro_origen);
                    }
                }

                if ($filtro_agente) {
                    if (is_array($filtro_agente)) {
                        $query->whereIn('t1.id_agente_callcenter', $filtro_agente);
                    } else {
                        $query->where('t1.id_agente_callcenter', $filtro_agente);
                    }
                }

                // Filtro de búsqueda dividido en palabras
                if ($filtro_search) {
                    $palabras = preg_split('/\s+/', trim($filtro_search));
                    foreach ($palabras as $palabra) {
                        if (!empty($palabra)) {
                            $query->where(function ($q) use ($palabra) {
                                $q->where('t2.nombre_cliente', 'like', '%' . $palabra . '%')
                                    ->orWhere('t2.apellido_cliente', 'like', '%' . $palabra . '%')
                                    ->orWhere('t2.doc_cliente', 'like', '%' . $palabra . '%')
                                    ->orWhere('t2.telefono_cliente', 'like', '%' . $palabra . '%');
                            });
                        }
                    }
                }

                if ($tipo_cita) {
                    $query->where('t6.tipo_servicio', $tipo_cita);
                }

                // Manejo de ordenamiento
                $columnsConsulta = [
                    0 => 't2.nombre_cliente',
                    1 => '',
                    2 => 't5.nombre_sede',
                    3 => 't1.reserva_cita',
                    4 => 't1.created_at',
                    5 => 't3.nombre_estado',
                    6 => 't4.nombre_estado',
                    7 => 't1.id_agente_callcenter',
                    8 => 't1.responsable_origen',
                    9 => 't1.origen',
                ];

                $order_column_index = $request->input('order.0.column');
                $order_direction = $request->input('order.0.dir', 'asc');
                $order_direction = ($order_direction === 'asc') ? 'asc' : 'desc';

                // Se define la columna a ordenar. Si el índice no corresponde o está vacío, se ordena por 't1.reserva_cita'
                $order_column = isset($columnsConsulta[$order_column_index]) && $columnsConsulta[$order_column_index]
                    ? $columnsConsulta[$order_column_index]
                    : 't1.reserva_cita';

                if ($order_column == 't1.reserva_cita') {
                    // Ordenamiento compuesto: primero por reserva_cita y luego por rango_horario (convertido a formato de hora)
                    $query->orderBy('t1.reserva_cita', $order_direction);
                    // Se utiliza DB::raw para usar la función MySQL STR_TO_DATE() junto con SUBSTRING_INDEX sobre rango_horario
                    $query->orderBy(DB::raw("STR_TO_DATE(SUBSTRING_INDEX(t1.rango_horario, ' -', 1), '%h:%i %p')"), 'asc');
                } else {
                    $query->orderBy($order_column, $order_direction);
                }

                // Obtener el total de registros filtrados
                $recordsTotal = $query->count();

                // Aplicar paginación y obtener la data
                $data = $query->skip($start)->take($length)->get();

                // Preparar la respuesta
                $objLoad = [
                    "draw" => $draw,
                    "recordsTotal" => $recordsTotal,
                    "recordsFiltered" => $recordsTotal,
                    "data" => $data,
                    "validate" => true,
                ];
            } catch (\Throwable $e) {
                Log::error($e->getMessage());
                $objLoad['text'] = 'Error al obtener los datos';
            }

            return response()->json($objLoad);
        }
    }

    //Crear citas
    public function add()
    {
        $user = Auth::user();
        $data = [
            'page' => 'Citas',
            'subpage' => 'Crear citas',
            'rol' => $user->getRoleNames()->first(),
            'user' => $user
        ];
        $alert = AdminHelper::get_count_alert($data['rol'], $user->id_sede); //gestorsede
        $data['alert'] = $alert;
        //Sedes
        if ($data['rol'] == 'gestorsede') {
            $sql = "SELECT * FROM tb_sede WHERE id_sede = " . $user->id_sede;
        } else {
            $sql = "SELECT * FROM tb_sede";
        }
        $data['sedes'] = DB::select($sql);;
        //Estados
        $sql = "SELECT * FROM tb_estado";
        $data['estados'] = DB::select($sql);
        //Servicios Liquidador
        $sql = "SELECT * FROM tb_servicio_liquidador";
        $data['servicios_liquidador'] = DB::select($sql);
        echo view('layouts.header', $data);
        echo view('layouts.nav', $data);
        echo view('dashboard.citas.add', $data);
        echo view('layouts.footer', $data);
    }
    //Obtener las actividades de la base de datos
    public function get_horarios(Request $request)
    {
        if ($request->ajax()) {
            $objLoad = [
                'validate' => false,
                'text' => 'Error al obtener los datos',
            ];
            try {
                $id = $request->request->get('id');
                $sql = "SELECT * FROM tb_sede_horario as t1 INNER JOIN tb_horario as t2 ON t1.id_horario = t2.id_horario  WHERE t1.id_sede = $id";
                $horarios = DB::select($sql);
                if (is_array($horarios) && !empty($horarios)) {
                    $objLoad = [
                        'validate' => true,
                        'horarios' => $horarios,
                    ];
                }
            } catch (\Throwable $e) {
                Log::error($e->getMessage());
            }
            return response()->json($objLoad);
        }
    }
    //Obtener las actividades de la base de datos
    public function save(Request $request)
    {
        if ($request->ajax()) {
            $objLoad = [
                'validate' => false,
                'text' => 'Error al guardar la sede'
            ];
            try {
                $user = Auth::user();
                $rol =  $user->getRoleNames()->first();
                $id_sede = $request->request->get('id_sede');
                $id_cliente = $request->request->get('id_cliente');
                $id_vehiculo = $request->request->get('id_vehiculo');
                $id_estado = $request->request->get('id_estado');
                $id_estado_verificado = $request->request->get('id_estado_verificado');
                $id_sede_horario  = $request->request->get('id_sede_horario');
                $reserva_cita = $request->request->get('reserva_cita');
                $id_servicio_liquidador = $request->request->get('id_servicio_liquidador');
                $desc_cita = $request->request->get('desc_cita');

                // Obtener nombre de la sede
                $sede = DB::table('tb_sede')->where('id_sede', $id_sede)->first();
                $nombre_sede = $sede ? $sede->nombre_sede : 'Sede no encontrada';
                $direccion_sede = $sede ? $sede->direccion_sede : 'Dirección no encontrada';
                $id_servicio = $sede ? $sede->id_servicio : 'Servicio no encontrado';

                $servicio = DB::table('tb_servicio')->where('id_servicio', $id_servicio)->first();
                $nombre_servicio = $servicio ? $servicio->tipo_servicio : 'Servicio no encontrado';
                // Obtener nombre del cliente
                $cliente = DB::table('tb_cliente')->where('id_cliente', $id_cliente)->first();
                $nombre_cliente = $cliente ? $cliente->nombre_cliente : 'Cliente no encontrado';
                $apellido_cliente = $cliente ? $cliente->apellido_cliente : 'Apellido no encontrado';
                $email_cliente = $cliente ? $cliente->email_cliente : 'Email no disponible';
                $telefono_cliente = $cliente ? $cliente->telefono_cliente : 'Teléfono no disponible';

                // Asignar valores adicionales
                $creado_por = $rol . "-" . $user->name;
                $origen = 'Curso Comparendo';
                $responsable_origen = 'Curso Comparendo';
                $tipo_dispositivo = $request->header('User-Agent'); // Detectar el dispositivo desde el User-Agent
                // Procesar el User-Agent para determinar el tipo de dispositivo
                if (preg_match('/mobile/i', $tipo_dispositivo)) {
                    $tipo_dispositivo = 'Mobile';
                } elseif (preg_match('/tablet/i', $tipo_dispositivo)) {
                    $tipo_dispositivo = 'Tablet';
                } else {
                    $tipo_dispositivo = 'Desktop';
                }

                $date = \DateTime::createFromFormat('d/m/Y', $reserva_cita);
                if ($date) {
                    $reserva_cita = $date->format('Y-m-d');
                } else {
                    throw new \Exception("El formato de la fecha es incorrecto");
                }
                $horario_sedes = AdminHelper::get_horario_by_id($id_sede_horario);
                if (is_array($horario_sedes) && !empty($horario_sedes)) {
                    $cupo_sede_horario = $horario_sedes['cupo_sede_horario'];
                    $id_horario = $horario_sedes['id_horario'];
                    //Obtenemos el rango horario by id
                    $horario = AdminHelper::get_horarios_by_id($id_horario);
                    $rango_horario = $horario['rango_horario'];
                    $sql = "SELECT * FROM tb_cita WHERE id_sede = $id_sede AND reserva_cita = '$reserva_cita' AND rango_horario = '$rango_horario'";
                    $citas = DB::select($sql);
                    if (count($citas) >= $cupo_sede_horario) {
                        $objLoad['text'] = 'No hay cupo disponible para la cita';
                        return response()->json($objLoad);
                    }
                }

                if ($rol == "callcenter" || $rol == "lidercallcenter") {
                    $idAgenteCallcenter = $user->id;
                } else {
                    $agentes = User::role('callcenter')
                        ->where('callcenter_habilitado', 1)
                        ->orderBy('id', 'asc')
                        ->get();
                    // log::info($agentes);
                    $lideragentes = User::role('lidercallcenter')
                        ->where('callcenter_habilitado', 1)
                        ->orderBy('id', 'asc')
                        ->get();

                    $agentes = $agentes->merge($lideragentes);
                    // log::info($agentes);
                    //  LEER EL PUNTERO ACTUAL DESDE tb_config
                    $config = DB::table('tb_config')
                        ->where('config_key', 'round_robin_callcenter')
                        ->first();
                    // Si no existe, lo iniciamos en 0
                    $puntero = $config ? (int)$config->config_value : 0;

                    // SELECCIONAR AL AGENTE SIGUIENTE
                    $countAgentes = $agentes->count();
                    $idAgenteCallcenter = null;
                    if ($countAgentes > 0) {
                        // Si el puntero sobrepasa el total de agentes, reiniciamos a 0
                        if ($puntero >= $countAgentes) {
                            $puntero = 0;
                        }
                        // Asignamos el agente según la posición del puntero
                        $idAgenteCallcenter = $agentes[$puntero]->id;

                        // Incrementamos el puntero y lo guardamos en tb_config
                        $puntero++;
                        DB::table('tb_config')->updateOrInsert(
                            ['config_key' => 'round_robin_callcenter'],
                            ['config_value' => $puntero]
                        );
                    }
                }
                $agenteValue   = is_null($idAgenteCallcenter) ? "NULL" : $idAgenteCallcenter;
                if ($id_vehiculo) {
                    $sql = "INSERT INTO tb_cita (id_cliente, id_sede, id_estado, id_vehiculo,id_estado_verificado, id_servicio_liquidador, id_agente_callcenter,reserva_cita, rango_horario, desc_cita,responsable_origen, creado_por, origen, tipo_dispositivo, created_at, updated_at) VALUES ($id_cliente, $id_sede, $id_estado, $id_vehiculo, $id_estado_verificado, 5, $agenteValue, '$reserva_cita', '$rango_horario', '$desc_cita', '$responsable_origen', '$creado_por', '$origen', '$tipo_dispositivo', DATE_SUB(NOW(), INTERVAL 5 HOUR), DATE_SUB(NOW(), INTERVAL 5 HOUR))";
                } else {
                    $sql = "INSERT INTO tb_cita (id_cliente, id_sede, id_estado,id_estado_verificado, id_servicio_liquidador, id_agente_callcenter,reserva_cita, rango_horario, desc_cita,responsable_origen, creado_por, origen, tipo_dispositivo, created_at, updated_at) VALUES ($id_cliente, $id_sede, $id_estado, $id_estado_verificado, 5, $agenteValue, '$reserva_cita', '$rango_horario', '$desc_cita', '$responsable_origen', '$creado_por', '$origen', '$tipo_dispositivo', DATE_SUB(NOW(), INTERVAL 5 HOUR), DATE_SUB(NOW(), INTERVAL 5 HOUR))";
                }
                // $sql = "INSERT INTO tb_cita (id_cliente, id_sede, id_estado, reserva_cita, rango_horario, desc_cita) VALUES ($id_cliente, $id_sede, $id_estado, '$reserva_cita', '$rango_horario', '$desc_cita')";
                $save = DB::insert($sql);
                if ($save) {
                    try {

                        // Preparar los datos para la plantilla de SendPulse
                        $templateVariables = [
                            'nombre_cliente'   => $nombre_cliente,
                            'apellido_cliente' => $apellido_cliente,
                            'nombre_sede'      => $nombre_sede,
                            'direccion_sede'   => $direccion_sede,
                            'email_cliente'    => $email_cliente,
                            'telefono_cliente' => $telefono_cliente,
                            'reserva_cita'     => $reserva_cita,
                            'rango_horario'    => $rango_horario,
                            'origen'           => $origen,
                            'tipo_dispositivo' => $tipo_dispositivo,
                            'nombre_servicio'  => $nombre_servicio,
                        ];
                        // Enviar el correo al cliente
                        $enviadoCliente = $this->sendPulse->sendEmailConfirmacion(
                            $email_cliente,
                            $nombre_cliente,
                            $nombre_cliente . " Confirmamos tu cita",
                            $templateVariables
                        );

                        if (!$enviadoCliente) {
                            Log::error("Error al enviar el correo al cliente");
                        }
                    } catch (\Exception $e) {
                        Log::error($e->getMessage());
                    }
                    // Obtener el ID de la cita recién creada
                    $id_cita = DB::getPdo()->lastInsertId();
                    try {
                        $saveliquidador = DB::table('tb_liquidador')->insert([
                            'id_cita' => $id_cita,
                            'estado_liquidador' => "Pendiente",
                            'comentario_liquidador' => "",
                            'pago_liquidador' => "Pendiente",
                            'created_at' => DB::raw('DATE_SUB(NOW(), INTERVAL 5 HOUR)'),
                            'updated_at' => DB::raw('DATE_SUB(NOW(), INTERVAL 5 HOUR)')
                        ]);
                    } catch (\Throwable $e) {
                        Log::error($e->getMessage());
                    }
                    $objLoad = [
                        'validate' => true,
                        'text' => 'Cita guardada correctamente',
                        'id' => $id_cita
                    ];
                }
            } catch (\Throwable $e) {
                Log::error($e->getMessage());
            }
            return response()->json($objLoad);
        }
    }
    //Ver sede
    public function view($id)
    {
        $user = Auth::user();
        $data = [
            'page' => 'Citas',
            'subpage' => 'Listado',
            'rol' => $user->getRoleNames()->first(),
            'user' => $user
        ];
        $alert = AdminHelper::get_count_alert($data['rol'], $user->id_sede); //gestorsede
        $data['alert'] = $alert;
        $data['cita'] = AdminHelper::get_cita_by_id($id);
        if (!is_array($data['cita']) || empty($data['cita'])) {
            return redirect()->route('citas');
        }
        $data['cita'] = $data['cita'][0];
        $sql = "SELECT * FROM tb_sede";
        $data['sedes'] = DB::select($sql);;
        $sql = "SELECT * FROM tb_estado";
        $data['estados'] = DB::select($sql);
        //Servicios Liquidador
        $sql = "SELECT * FROM tb_servicio_liquidador";
        $data['servicios_liquidador'] = DB::select($sql);
        $sql = "SELECT * FROM tb_seguimiento WHERE id_cita = $id";
        $data['anotaciones'] = DB::select($sql);
        //Recorremos las anotaciones para ingresar el nombre del usuario
        foreach ($data['anotaciones'] as $key => $value) {
            $user = User::find($value->id_user);
            $data['anotaciones'][$key]->nombre_user = $user->name;
        }
        echo view('layouts.header', $data);
        echo view('layouts.nav', $data);
        echo view('dashboard.citas.view', $data);
        echo view('layouts.footer', $data);
    }
    //Ver sede
    public function edit($id)
    {
        $user = Auth::user();
        $data = [
            'page' => 'Citas',
            'subpage' => 'Listado',
            'rol' => $user->getRoleNames()->first(),
            'user' => $user
        ];
        $alert = AdminHelper::get_count_alert($data['rol'], $user->id_sede); //gestorsede
        $data['alert'] = $alert;
        $data['cita'] = AdminHelper::get_cita_by_id($id);
        if (!is_array($data['cita']) || empty($data['cita'])) {
            return redirect()->route('citas');
        }
        $data['cita'] = $data['cita'][0];
        $sql = "SELECT * FROM tb_sede";
        $data['sedes'] = DB::select($sql);;
        $sql = "SELECT * FROM tb_estado";
        $data['estados'] = DB::select($sql);
        //Servicios Liquidador
        $sql = "SELECT * FROM tb_servicio_liquidador";
        $data['servicios_liquidador'] = DB::select($sql);
        $sql = "SELECT * FROM tb_seguimiento WHERE id_cita = $id";
        $data['anotaciones'] = DB::select($sql);
        $sql = "SELECT * FROM tb_vehiculo WHERE id_cliente = $id";
        $data['vehiculos'] = DB::select($sql);
        $agentes = User::role('callcenter')
            ->orderBy('id', 'asc')
            ->get();
        $lideragentes = User::role('lidercallcenter')
            ->orderBy('id', 'asc')
            ->get();
        $agentes = $agentes->merge($lideragentes);
        $data['agentes_callcenter'] = $agentes;
        //Recorremos las anotaciones para ingresar el nombre del usuario
        foreach ($data['anotaciones'] as $key => $value) {
            $user = User::find($value->id_user);
            $data['anotaciones'][$key]->nombre_user = $user->name;
        }
        echo view('layouts.header', $data);
        echo view('layouts.nav', $data);
        echo view('dashboard.citas.edit', $data);
        echo view('layouts.footer', $data);
    }
    //Actualizar cita
    public function update(Request $request)
    {
        if ($request->ajax()) {
            $objLoad = [
                'validate' => false,
                'text' => 'Error al actualizar la cita'
            ];
            try {
                $id_sede = $request->request->get('id_sede');
                $id_cita = $request->request->get('id_cita');
                $id_cliente = $request->request->get('id_cliente');
                $id_vehiculo = $request->request->get('id_vehiculo');
                $id_estado = $request->request->get('id_estado');
                $id_estado_verificado = $request->request->get('id_estado_verificado');
                $id_sede_horario  = $request->request->get('id_sede_horario');
                $id_servicio_liquidador = $request->request->get('id_servicio_liquidador');
                $reserva_cita = $request->request->get('reserva_cita');
                $desc_cita = $request->request->get('desc_cita');
                $id_agente_callcenter = $request->request->get('id_agente_callcenter');
                $date = \DateTime::createFromFormat('d/m/Y', $reserva_cita);
                if ($date) {
                    $reserva_cita = $date->format('Y-m-d');
                } else {
                    throw new \Exception("El formato de la fecha es incorrecto");
                }
                $horario_sedes = AdminHelper::get_horario_by_id($id_sede_horario);
                if (is_array($horario_sedes) && !empty($horario_sedes)) {
                    $cupo_sede_horario = $horario_sedes['cupo_sede_horario'];
                    $id_horario = $horario_sedes['id_horario'];
                    //Obtenemos el rango horario by id
                    $horario = AdminHelper::get_horarios_by_id($id_horario);
                    $rango_horario = $horario['rango_horario'];
                    $sql = "SELECT * FROM tb_cita WHERE id_sede = $id_sede AND reserva_cita = '$reserva_cita' AND rango_horario = '$rango_horario'  AND id_cita <> $id_cita";
                    $citas = DB::select($sql);
                    if (count($citas) >= $cupo_sede_horario) {
                        $objLoad['text'] = 'No hay cupo disponible para la cita';
                        return response()->json($objLoad);
                    }
                }
                if ($id_vehiculo) {
                    $sql = "UPDATE tb_cita SET id_cliente = $id_cliente, id_sede = $id_sede, id_estado = $id_estado, id_vehiculo = $id_vehiculo, id_estado_verificado = $id_estado_verificado, id_servicio_liquidador = $id_servicio_liquidador, id_agente_callcenter = $id_agente_callcenter, reserva_cita = '$reserva_cita', rango_horario = '$rango_horario', desc_cita = '$desc_cita' WHERE id_cita = $id_cita";
                } else {
                    $sql = "UPDATE tb_cita SET id_cliente = $id_cliente, id_sede = $id_sede, id_estado = $id_estado, id_estado_verificado = $id_estado_verificado, id_servicio_liquidador = $id_servicio_liquidador, id_agente_callcenter = $id_agente_callcenter, reserva_cita = '$reserva_cita', rango_horario = '$rango_horario', desc_cita = '$desc_cita' WHERE id_cita = $id_cita";
                }
                DB::update($sql);
                //Verificamos si viene anotaciones
                $nota_seguimiento = $request->request->get('nota_seguimiento');
                $titulo_seguimiento = $request->request->get('titulo_seguimiento');
                if ($nota_seguimiento != '' && $titulo_seguimiento != '') {
                    $sql = "INSERT INTO tb_seguimiento (titulo_seguimiento, nota_seguimiento, id_cita, id_user) VALUES ('$titulo_seguimiento', '$nota_seguimiento', $id_cita, " . Auth::user()->id . ")";
                    DB::insert($sql);
                }
                $objLoad = [
                    'validate' => true,
                    'text' => 'Cita actualizada correctamente',
                    'id' => $id_cita
                ];
            } catch (\Throwable $e) {
                Log::error($e->getMessage());
            }
            return response()->json($objLoad);
        }
    }
    //Vamos a borrar la sede
    public function delete(Request $request)
    {
        if ($request->ajax()) {
            $objLoad = [
                'validate' => false,
                'text' => 'Error al borrar la sede'
            ];
            //Ejecución de la funcion
            try {
                $id = $request->request->get('id');
                $sql = "DELETE FROM tb_cita WHERE id_cita = $id";
                DB::delete($sql);
                $objLoad = [
                    'validate' => true,
                    'text' => 'Cita borrada correctamente'
                ];
            } catch (\Throwable $e) {
                Log::error($e->getMessage());
            }
            //retornar respuesta
            return response()->json($objLoad);
        }
    }
    //Cambiamos estado
    public function change_estado(Request $request)
    {
        if ($request->ajax()) {
            $objLoad = ['validate' => false];
            //Ejecución de la funcion
            try {

                $id_estado = $request->request->get('id_estado');
                $id_cita = $request->request->get('id_cita');
                $sql = "UPDATE tb_cita SET id_estado = $id_estado WHERE id_cita = $id_cita";
                DB::update($sql);
                $objLoad = [
                    'validate' => true,
                    'text' => 'Cita borrada correctamente'
                ];
            } catch (\Throwable $e) {
                Log::error($e->getMessage());
            }
            //retornar respuesta
            return response()->json($objLoad);
        }
    }
    //Cambiamos estado
    public function change_estado_verificado(Request $request)
    {
        if ($request->ajax()) {
            $objLoad = ['validate' => false];
            //Ejecución de la funcion
            try {

                $id_estado_verificado = $request->request->get('id_estado');
                $id_cita = $request->request->get('id_cita');
                $sql = "UPDATE tb_cita SET id_estado_verificado = '$id_estado_verificado' WHERE id_cita = '$id_cita'";
                DB::update($sql);
                $objLoad = [
                    'validate' => true,
                    'text' => 'Cita borrada correctamente'
                ];
            } catch (\Throwable $e) {
                Log::error($e->getMessage());
            }
            //retornar respuesta
            return response()->json($objLoad);
        }
    }
    public function change_agente_call(Request $request)
    {
        if ($request->ajax()) {
            $objLoad = ['validate' => false];
            //Ejecución de la funcion
            try {

                $id_agente = $request->request->get('id_agente');
                $id_cita = $request->request->get('id_cita');
                $sql = "UPDATE tb_cita SET id_agente_callcenter = '$id_agente' WHERE id_cita = '$id_cita'";
                $sqlupdate = DB::update($sql);
                if ($sqlupdate) {
                    $objLoad = [
                        'validate' => true,
                        'text' => 'Agente Callcenter actualizado correctamente',
                        'id' => $id_cita
                    ];
                } else {
                    $objLoad['text'] = 'No se realizaron cambios en el agente Callcenter.';
                    log::error('No se realizaron cambios en el agente Callcenter.');
                    log::error($sqlupdate);
                    log::info($id_agente);
                    log::info($id_cita);
                }
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
            'page' => 'Citas',
            'subpage' => 'Configuración',
            'rol' => $user->getRoleNames()->first(),
            'user' => $user
        ];
        $alert = AdminHelper::get_count_alert($data['rol'], $user->id_sede); //gestorsede
        $data['alert'] = $alert;
        echo view('layouts.header', $data);
        echo view('layouts.nav', $data);
        echo view('dashboard.citas.configuracion', $data);
        echo view('layouts.footer', $data);
    }
    //Obtener las actividades de la base de datos
    public function get_estados(Request $request)
    {
        if ($request->ajax()) {
            $objLoad = ['validate' => false];
            //Ejecución de la funcion
            try {
                $length = $request->request->get('length');
                $start = $request->request->get('start');
                $draw = $request->request->get('draw');
                $sql = "SELECT * FROM tb_estado LIMIT " . $start . ", " . $length . "";
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
    public function add_estados(Request $request)
    {
        if ($request->ajax()) {
            $objLoad = [
                'validate' => false,
                'text' => 'Error al guardar el servicio'
            ];
            try {
                $nombre_estado = $request->request->get('nombre_estado');
                $desc_estado = $request->request->get('desc_estado');
                $color_estado = $request->request->get('color_estado');
                $sql = "INSERT INTO tb_estado (nombre_estado, desc_estado,color_estado) VALUES ('$nombre_estado', '$desc_estado', '$color_estado')";
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
    //Editar Estado
    public function edit_estados($id)
    {
        $user = Auth::user();
        $data = [
            'page' => 'Citas',
            'subpage' => 'Configuración',
            'rol' => $user->getRoleNames()->first(),
            'user' => $user
        ];
        $alert = AdminHelper::get_count_alert($data['rol'], $user->id_sede); //gestorsede
        $data['alert'] = $alert;

        // Obtener los datos del estado por ID
        $sql = "SELECT * FROM tb_estado WHERE id_estado = ?";
        $estado = DB::select($sql, [$id]);

        if (empty($estado)) {
            // Redirigir si el estado no existe
            return redirect()->route('estados')->withErrors('El estado no existe o ha sido eliminado.');
        }
        // Pasar los datos del estado a la vista
        $data['estado'] = $estado[0];

        echo view('layouts.header', $data);
        echo view('layouts.nav', $data);
        echo view('dashboard.citas.edit_estados', $data);
        echo view('layouts.footer', $data);
    }
    public function update_estados(Request $request)
    {
        if ($request->ajax()) {
            $objLoad = [
                'validate' => false,
                'text' => 'Error al actualizar el estado'
            ];
            try {
                // Validar los datos recibidos
                $request->validate([
                    'id_estado' => 'required|integer|exists:tb_estado,id_estado',
                    'nombre_estado' => 'required|string|max:255',
                    'desc_estado' => 'required|string|max:255',
                    'color_estado' => 'required|string|size:7' // Aseguramos que sea un color HEX
                ]);

                // Actualizar el estado en la base de datos
                $update = DB::table('tb_estado')
                    ->where('id_estado', $request->id_estado)
                    ->update([
                        'nombre_estado' => $request->nombre_estado,
                        'desc_estado' => $request->desc_estado,
                        'color_estado' => $request->color_estado,
                        'updated_at' => now() // Actualizar la fecha de modificación
                    ]);

                if ($update) {
                    // Respuesta exitosa
                    $objLoad = [
                        'validate' => true,
                        'text' => 'Estado actualizado correctamente',
                        'id' => $request->id_estado
                    ];
                } else {
                    $objLoad['text'] = 'No se realizaron cambios en el estado.';
                }
            } catch (\Throwable $e) {
                Log::error($e->getMessage());
            }
            return response()->json($objLoad);
        }
    }
    //Vamos a borrar la sede
    public function delete_estados(Request $request)
    {
        if ($request->ajax()) {
            $objLoad = [
                'validate' => false,
                'text' => 'Error al borrar el estado'
            ];
            try {
                $id = $request->request->get('id');
                $sql = "SELECT * FROM tb_sede WHERE id_estado  LIKE '%" . $id . "%'";
                $data = DB::select($sql);
                if (count($data) > 0) {
                    $objLoad['text'] = 'No se puede borrar el servicio porque esta asignado a una sede';
                    return response()->json($objLoad);
                }
                $sql = "DELETE FROM tb_estado WHERE id_servicio    = " . $id . "";
                DB::delete($sql);
                $objLoad = array(
                    "validate" => true,
                    "text" => 'Estado borrado correctamente'
                );
            } catch (\Throwable $e) {
                Log::error($e->getMessage());
            }
            return response()->json($objLoad);
        }
    }
    //Vamos a borrar la sede
    public function dowload(Request $request)
    {
        if ($request->ajax()) {
            $objLoad = [
                'validate' => false,
                'text' => 'Error al borrar el estado'
            ];
            try {
                $user = Auth::user();
                $rol =  $user->getRoleNames()->first();
                $start = $request->request->get('start');
                $length = 100;
                // Filtros
                $filtro_dia = $request->input('filtro_dia');
                $filtro_dia_end = $request->input('filtro_dia_end');
                $filtro_sede = $request->input('filtro_sede');
                $filtro_estado = $request->input('filtro_estado');
                $fecha_actual = date('Y-m-d');
                // Construcción de la consulta


                if ($rol == 'gestorsede') {
                    $sql = "SELECT *, t1.created_at as fecha_create FROM tb_cita as t1
                    INNER JOIN tb_cliente as t2 ON t1.id_cliente = t2.id_cliente
                    INNER JOIN tb_estado as t3 ON t1.id_estado = t3.id_estado
                    INNER JOIN tb_sede as t5 ON t1.id_sede = t5.id_sede WHERE t1.id_cita > 0 AND t5.id_sede = " . $user->id_sede;
                } else {
                    $sql = "SELECT *, t1.created_at as fecha_create FROM tb_cita as t1
                    INNER JOIN tb_cliente as t2 ON t1.id_cliente = t2.id_cliente
                    INNER JOIN tb_estado as t3 ON t1.id_estado = t3.id_estado
                    INNER JOIN tb_sede as t5 ON t1.id_sede = t5.id_sede WHERE t1.id_cita > 0";
                }

                // Aplicar filtros
                if ($filtro_dia && $filtro_dia_end) {
                    $sql .= " AND t1.reserva_cita BETWEEN '$filtro_dia' AND '$filtro_dia_end'";
                } elseif ($filtro_dia) {
                    $sql .= " AND t1.reserva_cita = '$filtro_dia'";
                } else {
                    $sql .= " AND t1.reserva_cita >= '$fecha_actual'";
                }
                if ($filtro_sede) {
                    $sql .= " AND t5.id_sede = $filtro_sede";
                }
                if ($filtro_estado) {
                    $sql .= " AND t3.id_estado = $filtro_estado";
                }
                // Limitar registros por página
                $sql .= " LIMIT $start, $length";
                $records = DB::select($sql);
                if (empty($records)) {
                    return response()->json([
                        'status' => 'completed',
                        'url' => url('data/data_cita.csv')
                    ]);
                }
                // Ruta y apertura del archivo CSV en la carpeta public/data
                $filePath = 'public_html/data/data_cita.csv';
                $fullPath = base_path($filePath);
                // Crear la carpeta 'data' si no existe
                if (!file_exists(base_path('public_html/data'))) {
                    mkdir(base_path('public_html/data'), 0777, true);
                }
                $file = fopen($fullPath, $start == 0 ? 'w' : 'a');
                if ($start == 0) {
                    // Encabezados en la primera escritura
                    fputcsv($file, [
                        'ID Cita',
                        'Cliente',
                        'Estado',
                        'Sede',
                        'Horario',
                        'Fecha de Reserva',
                        'Descripción',
                        'Fecha Creación',
                        'Nombre Cliente',
                        'Apellido Cliente',
                        'Tipo Documento',
                        'Documento Cliente',
                        'Teléfono Cliente',
                        'Email Cliente',
                        'Descripción Cliente',
                        'Nombre Estado',
                        'Descripción Estado',
                        'Nombre Sede',
                        'Dirección Sede',
                        'Teléfono Sede',
                        'Estado Sede',
                        'Horario de Rango'
                    ]);
                }
                foreach ($records as $record) {
                    fputcsv($file, [
                        $record->id_cita,
                        $record->nombre_cliente . ' ' . $record->apellido_cliente,
                        $record->nombre_estado,
                        $record->nombre_sede,
                        $record->rango_horario,
                        $record->reserva_cita,
                        $record->desc_cita,
                        $record->created_at,
                        $record->nombre_cliente,
                        $record->apellido_cliente,
                        $record->tipo_doc_cliente,
                        $record->doc_cliente,
                        $record->telefono_cliente,
                        $record->email_cliente,
                        $record->desc_cliente,
                        $record->nombre_estado,
                        $record->desc_estado,
                        $record->nombre_sede,
                        $record->direccion_sede,
                        $record->tel_sede,
                        $record->estado_sede,
                        $record->rango_horario
                    ]);
                }
                fclose($file);
                return response()->json(['status' => 'in_progress', 'nextStart' => $start + $length]);
            } catch (\Throwable $e) {
                Log::error($e->getMessage());
            }
            return response()->json($objLoad);
        }
    }

    public function get_new_records(Request $request)
    {
        if ($request->ajax()) {
            try {
                $user = Auth::user();
                $rol = $user->getRoleNames()->first();

                $last_execution = cache()->get('last_cron_execution', now());
                if (!$last_execution instanceof \Carbon\Carbon) {
                    $last_execution = \Carbon\Carbon::createFromTimestamp(strtotime($last_execution));
                }
                $next_execution = $last_execution->copy()->addMinutes(5);
                $time_remaining = max(0, $next_execution->diffInSeconds(now()));

                $nuevos_registros = 0;
                $sede = "todas las sedes";

                if ($rol === 'gestorsede') {
                    $id_sede = $user->id_sede;
                    $nuevos_registros = DB::table('tb_cita')
                        ->where('id_sede', $id_sede)
                        ->where('created_at', '>=', now()->subMinutes(5))
                        ->count();
                    $sede = "la sede $id_sede";
                } else {
                    $nuevos_registros = cache()->get('new_records_global', 0);
                }

                return response()->json([
                    'validate' => true,
                    'nuevos_registros' => $nuevos_registros,
                    'sede' => $sede,
                    'time_remaining' => $time_remaining,
                ]);
            } catch (\Throwable $e) {
                return response()->json([
                    'validate' => false,
                    'message' => 'Error al obtener los registros nuevos.'
                ]);
            }
        }
    }

    /*
    |-------------------------------------------------------------------------------
    | LIQUIDADOR
    |-------------------------------------------------------------------------------
    */
    public function indexLiquidador()
    {
        $user = Auth::user();
        $data = [
            'page' => 'Liquidador',
            'subpage' => 'Listado',
            'rol' => $user->getRoleNames()->first(),
            'user' => $user
        ];
        $data['alert'] = AdminHelper::get_count_alert($data['rol'], $user->id_sede); //gestorsede
        //Verificamos si las alertas tiene mas de 0 para cambiar el estado de la alerta
        if ($data['alert'] > 0) {
            AdminHelper::change_status_alert($data['rol'], $user->id_sede);
        }
        //Sedes
        if ($data['rol'] == 'gestorsede') {
            $sql = "SELECT * FROM tb_sede WHERE id_sede = " . $user->id_sede;
        } else {
            $sql = "SELECT * FROM tb_sede";
        }
        $data['sedes'] = DB::select($sql);;
        //Estados
        $sql = "SELECT * FROM tb_estado";
        $data['estados'] = DB::select($sql);
        //Servicios Liquidador
        $sql = "SELECT * FROM tb_servicio_liquidador";
        $data['servicios_liquidador'] = DB::select($sql);
        //Origenes
        $sql = "SELECT DISTINCT origen FROM tb_cita WHERE origen IS NOT NULL ORDER BY origen ASC;";
        $data['origenes'] = DB::select($sql);
        $data['tipoSede'] = "";

        echo view('layouts.header', $data);
        echo view('layouts.nav', $data);
        echo view('dashboard.liquidador.index', $data);
        echo view('layouts.footer', $data);
    }
    public function indexLiquidadorCDA()
    {
        $user = Auth::user();
        $data = [
            'page' => 'Liquidador',
            'subpage' => 'ListadoCDA',
            'rol' => $user->getRoleNames()->first(),
            'user' => $user
        ];
        $data['alert'] = AdminHelper::get_count_alert($data['rol'], $user->id_sede); //gestorsede
        //Verificamos si las alertas tiene mas de 0 para cambiar el estado de la alerta
        if ($data['alert'] > 0) {
            AdminHelper::change_status_alert($data['rol'], $user->id_sede);
        }
        //Sedes
        if ($data['rol'] == 'gestorsede') {
            $sql = "SELECT * FROM tb_sede WHERE id_sede = " . $user->id_sede;
        } else {
            $sql = "SELECT * FROM tb_sede";
        }
        $data['sedes'] = DB::select($sql);;
        //Estados
        $sql = "SELECT * FROM tb_estado";
        $data['estados'] = DB::select($sql);
        //Servicios Liquidador
        $sql = "SELECT * FROM tb_servicio_liquidador";
        $data['servicios_liquidador'] = DB::select($sql);
        //Origenes
        $sql = "SELECT DISTINCT origen FROM tb_cita WHERE origen IS NOT NULL ORDER BY origen ASC;";
        $data['origenes'] = DB::select($sql);
        $data['tipoSede'] = "CDA";
        echo view('layouts.header', $data);
        echo view('layouts.nav', $data);
        echo view('dashboard.liquidador.index', $data);
        echo view('layouts.footer', $data);
    }
    public function indexLiquidadorCIA()
    {
        $user = Auth::user();
        $data = [
            'page' => 'Liquidador',
            'subpage' => 'ListadoCIA',
            'rol' => $user->getRoleNames()->first(),
            'user' => $user
        ];
        $data['alert'] = AdminHelper::get_count_alert($data['rol'], $user->id_sede); //gestorsede
        //Verificamos si las alertas tiene mas de 0 para cambiar el estado de la alerta
        if ($data['alert'] > 0) {
            AdminHelper::change_status_alert($data['rol'], $user->id_sede);
        }
        //Sedes
        if ($data['rol'] == 'gestorsede') {
            $sql = "SELECT * FROM tb_sede WHERE id_sede = " . $user->id_sede;
        } else {
            $sql = "SELECT * FROM tb_sede";
        }
        $data['sedes'] = DB::select($sql);;
        //Estados
        $sql = "SELECT * FROM tb_estado";
        $data['estados'] = DB::select($sql);
        //Servicios Liquidador
        $sql = "SELECT * FROM tb_servicio_liquidador";
        $data['servicios_liquidador'] = DB::select($sql);
        //Origenes
        $sql = "SELECT DISTINCT origen FROM tb_cita WHERE origen IS NOT NULL ORDER BY origen ASC;";
        $data['origenes'] = DB::select($sql);
        $data['tipoSede'] = "CIA";
        echo view('layouts.header', $data);
        echo view('layouts.nav', $data);
        echo view('dashboard.liquidador.index', $data);
        echo view('layouts.footer', $data);
    }
    public function indexLiquidadorCRC()
    {
        $user = Auth::user();
        $data = [
            'page' => 'Liquidador',
            'subpage' => 'ListadoCRC',
            'rol' => $user->getRoleNames()->first(),
            'user' => $user
        ];
        $data['alert'] = AdminHelper::get_count_alert($data['rol'], $user->id_sede); //gestorsede
        //Verificamos si las alertas tiene mas de 0 para cambiar el estado de la alerta
        if ($data['alert'] > 0) {
            AdminHelper::change_status_alert($data['rol'], $user->id_sede);
        }
        //Sedes
        if ($data['rol'] == 'gestorsede') {
            $sql = "SELECT * FROM tb_sede WHERE id_sede = " . $user->id_sede;
        } else {
            $sql = "SELECT * FROM tb_sede";
        }
        $data['sedes'] = DB::select($sql);;
        //Estados
        $sql = "SELECT * FROM tb_estado";
        $data['estados'] = DB::select($sql);
        //Servicios Liquidador
        $sql = "SELECT * FROM tb_servicio_liquidador";
        $data['servicios_liquidador'] = DB::select($sql);
        //Origenes
        $sql = "SELECT DISTINCT origen FROM tb_cita WHERE origen IS NOT NULL ORDER BY origen ASC;";
        $data['origenes'] = DB::select($sql);
        $data['tipoSede'] = "CRC";
        echo view('layouts.header', $data);
        echo view('layouts.nav', $data);
        echo view('dashboard.liquidador.index', $data);
        echo view('layouts.footer', $data);
    }
    public function indexLiquidadorCEA()
    {
        $user = Auth::user();
        $data = [
            'page' => 'Liquidador',
            'subpage' => 'ListadoCEA',
            'rol' => $user->getRoleNames()->first(),
            'user' => $user
        ];
        $data['alert'] = AdminHelper::get_count_alert($data['rol'], $user->id_sede); //gestorsede
        //Verificamos si las alertas tiene mas de 0 para cambiar el estado de la alerta
        if ($data['alert'] > 0) {
            AdminHelper::change_status_alert($data['rol'], $user->id_sede);
        }
        //Sedes
        if ($data['rol'] == 'gestorsede') {
            $sql = "SELECT * FROM tb_sede WHERE id_sede = " . $user->id_sede;
        } else {
            $sql = "SELECT * FROM tb_sede";
        }
        $data['sedes'] = DB::select($sql);;
        //Estados
        $sql = "SELECT * FROM tb_estado";
        $data['estados'] = DB::select($sql);
        //Servicios Liquidador
        $sql = "SELECT * FROM tb_servicio_liquidador";
        $data['servicios_liquidador'] = DB::select($sql);
        //Origenes
        $sql = "SELECT DISTINCT origen FROM tb_cita WHERE origen IS NOT NULL ORDER BY origen ASC;";
        $data['origenes'] = DB::select($sql);
        $data['tipoSede'] = "CEA";
        echo view('layouts.header', $data);
        echo view('layouts.nav', $data);
        echo view('dashboard.liquidador.index', $data);
        echo view('layouts.footer', $data);
    }

    //Obtener las citas para la cita liquidador de la base de datos
    public function get_citas_liquidador(Request $request)
    {
        if ($request->ajax()) {
            $objLoad = ['validate' => false];
            //Ejecución de la funcion
            try {
                $user = Auth::user();
                $rol  = $user->getRoleNames()->first();

                // Parámetros DataTables
                $length = $request->input('length');
                $start  = $request->input('start');
                $draw   = $request->input('draw');
                $tipo_cita = $request->request->get('tipo_cita');

                // Filtros personalizados
                $filtro_dia      = $request->input('filtro_dia');
                $filtro_dia_end  = $request->input('filtro_dia_end');
                $filtro_sede     = $request->input('filtro_sede');
                $filtro_search   = $request->input('filtro_search');
                $filtro_servicio_liquidador = $request->input('filtro_servicio_liquidador');
                $filtro_estado_validacion_liquidador = $request->input('filtro_estado_validacion_liquidador');
                $filtro_estado_pago_liquidador = $request->input('filtro_estado_pago_liquidador');
                // Omitimos $filtro_estado_verificado porque forzaremos "Asistió"

                // Ordenamiento
                // Definimos columnas para ordenamiento
                $order_column_index = $request->input('order.0.column', 0);
                $order_direction    = $request->input('order.0.dir', 'asc');

                // Se asume que el checkbox es la primera columna (índice 0)
                $columnsConsulta = [
                    0 => '', // columna para el checkbox (no ordenable)
                    1 => 't2.nombre_cliente',
                    2 => 't5.nombre_sede',
                    3 => 't1.reserva_cita',      // Aquí se encuentra la fecha de cita
                    4 => 't1.created_at',
                    5 => 'l.estado_liquidador',
                    6 => '',
                    7 => 's.valor_servicio_liquidador',
                    8 => 'l.pago_liquidador'
                ];


                // Si el índice recibido no existe o corresponde a la columna vacía, forzamos a usar "t1.reserva_cita"
                if (!isset($columnsConsulta[$order_column_index]) || $columnsConsulta[$order_column_index] == '') {
                    $order_column = 't1.reserva_cita';
                } else {
                    $order_column = $columnsConsulta[$order_column_index];
                }

                // Asegurarse de que la dirección sea válida
                $order_direction = ($order_direction === 'asc') ? 'ASC' : 'DESC';

                // Rango por defecto: primer y último día del mes actual
                $startOfMonth = date('Y-m-01');
                $endOfMonth   = date('Y-m-t');

                // --------------------------------------
                // 1) Consulta base para contar registros
                // --------------------------------------
                $sqlBase = "
                                FROM tb_cita AS t1
                                INNER JOIN tb_cliente AS t2
                                    ON t1.id_cliente = t2.id_cliente
                                INNER JOIN tb_estado AS t3
                                    ON t1.id_estado = t3.id_estado
                                INNER JOIN tb_estado AS t4
                                    ON t1.id_estado_verificado = t4.id_estado
                                INNER JOIN tb_sede   AS t5
                                    ON t1.id_sede = t5.id_sede
                                INNER JOIN tb_servicio AS t6
                                    ON t5.id_servicio = t6.id_servicio
                                LEFT JOIN tb_liquidador AS l
                                    ON t1.id_cita = l.id_cita
                                LEFT JOIN tb_vehiculo AS v
                                    ON t1.id_vehiculo = v.id_vehiculo
                                LEFT JOIN tb_servicio_liquidador AS s
                                    ON t1.id_servicio_liquidador = s.id_servicio_liquidador
                                WHERE t1.id_cita > 0
                                AND t4.nombre_estado = 'Asistió'
                            ";

                // Filtro si el rol es 'gestorsede'
                if ($rol == 'gestorsede') {
                    $sqlBase .= " AND t5.id_sede = " . $user->id_sede;
                }

                // Filtro de fechas
                if (!empty($filtro_dia) && !empty($filtro_dia_end)) {
                    $sqlBase .= " AND t1.reserva_cita BETWEEN '$filtro_dia' AND '$filtro_dia_end'";
                    $fecha_inicio = $filtro_dia;
                    $fecha_fin = $filtro_dia_end;
                } else {
                    // Por defecto, primer y último día del mes
                    $fecha_inicio = $startOfMonth;
                    $fecha_fin = $endOfMonth;
                    $sqlBase .= " AND t1.reserva_cita BETWEEN '$startOfMonth' AND '$endOfMonth'";
                }

                // Filtro de sede
                if (!empty($filtro_sede)) {
                    $sqlBase .= " AND t5.id_sede = $filtro_sede";
                }

                // Filtro de servicio liquidador
                if (!empty($filtro_servicio_liquidador)) {
                    $sqlBase .= " AND t1.id_servicio_liquidador = $filtro_servicio_liquidador";
                }

                // Filtro de estado validacion liquidador
                if (!empty($filtro_estado_validacion_liquidador)) {
                    $sqlBase .= " AND l.estado_liquidador = '$filtro_estado_validacion_liquidador'";
                }

                // Filtro de estado pago liquidador
                if (!empty($filtro_estado_pago_liquidador)) {
                    $sqlBase .= " AND l.pago_liquidador = '$filtro_estado_pago_liquidador'";
                }

                // Filtro de búsqueda
                if (!empty($filtro_search)) {
                    // Eliminar espacios extra y dividir la búsqueda por espacios
                    $palabras = preg_split('/\s+/', trim($filtro_search));

                    foreach ($palabras as $palabra) {
                        // Verificamos que la palabra no este vacía
                        if (!empty($palabra)) {
                            $sqlBase .= " AND (
                                t2.nombre_cliente LIKE '%" . addslashes($palabra) . "%'
                                OR t2.apellido_cliente LIKE '%" . addslashes($palabra) . "%'
                                OR t2.doc_cliente LIKE '%" . addslashes($palabra) . "%'
                                OR t2.telefono_cliente LIKE '%" . addslashes($palabra) . "%'
                            )";
                        }
                    }
                }

                if ($tipo_cita) {
                    $sqlBase .= " AND t6.tipo_servicio = '$tipo_cita'";
                }

                // --------------------------------------
                // 2) Consulta para contar total registros
                // --------------------------------------
                $sqlCount = "SELECT COUNT(*) as total " . $sqlBase;
                $recordsTotal = DB::selectOne($sqlCount)->total;

                // --------------------------------------
                // 3) Consulta para paginación
                // --------------------------------------
                $sqlData = "SELECT t1.*,
                                t1.created_at AS fecha_create,

                                t2.nombre_cliente,
                                t2.apellido_cliente,
                                t2.doc_cliente,
                                t2.tipo_doc_cliente,
                                t2.telefono_cliente,

                                t3.id_estado        AS estado_actual_id,
                                t3.nombre_estado    AS estado_actual_nombre,
                                t3.color_estado     AS estado_actual_color,

                                t4.id_estado        AS estado_verificado_id,
                                t4.nombre_estado    AS estado_verificado_nombre,
                                t4.color_estado     AS estado_verificado_color,

                                t5.nombre_sede,
                                t5.id_servicio,

                                t6.tipo_servicio,

                                l.id_liquidador,
                                l.estado_liquidador,
                                l.comentario_liquidador,
                                l.pago_liquidador,

                                v.id_vehiculo,
                                v.placa_vehiculo,
                                v.tipo_vehiculo,
                                v.modelo_vehiculo,

                                s.id_servicio_liquidador,
                                s.nombre_servicio_liquidador,
                                s.valor_servicio_liquidador,
                                s.color_servicio_liquidador,

                                (SELECT COUNT(*)
                                    FROM tb_seguimiento AS ts
                                    WHERE ts.id_cita = t1.id_cita) AS total_anotaciones
                            " . $sqlBase . "
                            ORDER BY $order_column, t1.rango_horario, t1.id_sede $order_direction
                            LIMIT " . (int)$start . ", " . (int)$length;

                log::info($sqlData);
                $data = DB::select($sqlData);

                // --------------------------------------
                // 4) Consulta de totales/estadísticas
                //    (sin limit/offset)
                // --------------------------------------
                $sqlTotals = "
                                SELECT
                                    COALESCE(SUM(s.valor_servicio_liquidador), 0) AS total_valor_a_liquidar,
                                    COALESCE(SUM(
                                        CASE WHEN l.estado_liquidador = 'confirmado'
                                            THEN s.valor_servicio_liquidador
                                            ELSE 0 END
                                    ), 0) AS total_valor_liquidado,

                                    -- conteo de estado_liquidador
                                    SUM(CASE WHEN l.estado_liquidador = 'confirmado' THEN 1 ELSE 0 END) AS total_confirmados,
                                    SUM(CASE WHEN l.estado_liquidador = 'errado'     THEN 1 ELSE 0 END) AS total_errados,
                                    SUM(CASE WHEN l.estado_liquidador = 'pendiente'  THEN 1 ELSE 0 END) AS total_pendientes,
                                    SUM(CASE WHEN l.estado_liquidador = 'en validación'  THEN 1 ELSE 0 END) AS total_validacion,

                                    -- conteo de pago_liquidador
                                    SUM(CASE WHEN l.pago_liquidador = 'pendiente' THEN 1 ELSE 0 END) AS total_pago_pendiente,
                                    SUM(CASE WHEN l.pago_liquidador = 'pagado'    THEN 1 ELSE 0 END) AS total_pago_pagado
                            " . $sqlBase;

                $totals = DB::selectOne($sqlTotals);

                // --------------------------------------
                // 5) Estructura de respuesta final
                // --------------------------------------
                $objLoad = [
                    "draw"            => $draw,
                    "recordsTotal"    => $recordsTotal,
                    "recordsFiltered" => $recordsTotal,
                    "data"            => $data,
                    "validate"        => true,
                    "extra"           => [
                        "fecha_inicio"           => $fecha_inicio,
                        "fecha_fin"              => $fecha_fin,
                        "total_valor_a_liquidar" => $totals->total_valor_a_liquidar,
                        "total_valor_liquidado"  => $totals->total_valor_liquidado,
                        "total_confirmados"      => $totals->total_confirmados,
                        "total_errados"          => $totals->total_errados,
                        "total_pendientes"       => $totals->total_pendientes,
                        "total_validacion"       => $totals->total_validacion,
                        "total_pago_pendiente"   => $totals->total_pago_pendiente,
                        "total_pago_pagado"      => $totals->total_pago_pagado,
                    ]
                ];
            } catch (\Throwable $e) {
                Log::error($e->getMessage());
                $objLoad['text'] = 'Error al obtener los datos';
            }
            //retornar respuesta
            return response()->json($objLoad);
        }
    }

    //Cambiamos estado
    public function change_servicio_liquidador(Request $request)
    {
        if ($request->ajax()) {
            $objLoad = ['validate' => false];
            //Ejecución de la funcion
            try {

                $id_servicio_liquidador = $request->request->get('id_servicio_liquidador');
                $id_cita = $request->request->get('id_cita');
                $sql = "UPDATE tb_cita SET id_servicio_liquidador = '$id_servicio_liquidador' WHERE id_cita = '$id_cita'";
                DB::update($sql);
                $objLoad = [
                    'validate' => true,
                    'text' => 'Servicio actualizado correctamente'
                ];
            } catch (\Throwable $e) {
                Log::error($e->getMessage());
            }
            //retornar respuesta
            return response()->json($objLoad);
        }
    }

    public function get_seguimiento_cita(Request $request)
    {
        if ($request->ajax()) {
            $objLoad = [
                'validate' => false,
                'text'     => 'Error al obtener el seguimiento'
            ];
            try {
                $id_cita = $request->input('id_cita');
                if (empty($id_cita)) {
                    throw new \Exception("No se proporcionó el ID de la cita");
                }

                // Obtener las anotaciones (seguimiento) de la cita, ordenadas por fecha (más recientes primero)
                $anotaciones = DB::table('tb_seguimiento')
                    ->where('id_cita', $id_cita)
                    ->orderBy('created_at', 'ASC')
                    ->get();

                // Construir el HTML de la línea de tiempo
                $html = '<ul class="timeline mb-0 py-2">';
                if (!$anotaciones->isEmpty()) {
                    foreach ($anotaciones as $anotacion) {
                        // Buscar el nombre del usuario autor de la anotación
                        $user = User::find($anotacion->id_user);
                        $nombre_user = $user ? $user->name : 'Desconocido';

                        $html .= '<li class="timeline-item timeline-item-transparent">';
                        $html .= '  <span class="timeline-point timeline-point-success"></span>';
                        $html .= '  <div class="timeline-event text-left">';
                        $html .= '      <div class="timeline-header mb-2">';
                        $html .= '          <h6 class="mb-0">' . htmlspecialchars($anotacion->titulo_seguimiento) . '</h6>';
                        $html .= '          <small class="text-muted">' . $anotacion->created_at . '</small>';
                        $html .= '      </div>';
                        $html .= '      <p class="m-0" style="text-align: left;">' . htmlspecialchars($anotacion->nota_seguimiento) . '</p>';
                        $html .= '      <p class="m-0" style="text-align: right;">Autor: <strong>' . htmlspecialchars($nombre_user) . '</strong></p>';
                        $html .= '  </div>';
                        $html .= '</li>';
                    }
                } else {
                    $html .= '<li class="timeline-item timeline-item-transparent">';
                    $html .= '  <div class="timeline-event">';
                    $html .= '      <p class="m-0">No se encontró seguimiento para esta cita.</p>';
                    $html .= '  </div>';
                    $html .= '</li>';
                }
                $html .= '</ul>';

                $objLoad = [
                    'validate' => true,
                    'html'     => $html
                ];
            } catch (\Throwable $e) {
                Log::error($e->getMessage());
                $objLoad['text'] = $e->getMessage();
            }
            return response()->json($objLoad);
        }
    }
    public function get_seguimiento_cita_con_actualizacion(Request $request)
    {
        if ($request->ajax()) {
            $objLoad = [
                'validate' => false,
                'text'     => 'Error al obtener el seguimiento'
            ];
            try {
                $id_cita = $request->input('id_cita');
                if (empty($id_cita)) {
                    throw new \Exception("No se proporcionó el ID de la cita");
                }

                // Obtener las anotaciones (seguimiento) de la cita, ordenadas por fecha (más recientes primero)
                $anotaciones = DB::table('tb_seguimiento')
                    ->where('id_cita', $id_cita)
                    ->orderBy('created_at', 'ASC')
                    ->get();

                // Construir el HTML de la línea de tiempo
                $html = '<div class="container mx-auto row justify-content-around">';
                $html .= '<div class="col-12 col-md-6 mb-4">';
                $html .= '<ul class="timeline mb-0 py-2" style="heigth:auto !important;">';
                if (!$anotaciones->isEmpty()) {
                    foreach ($anotaciones as $anotacion) {
                        // Buscar el nombre del usuario autor de la anotación
                        $user = User::find($anotacion->id_user);
                        $nombre_user = $user ? $user->name : 'Desconocido';
                        $rol = $user->getRoleNames()->first();

                        $html .= '<li class="timeline-item timeline-item-transparent"> ';
                        if ($rol == 'callcenter' || $rol == 'lidercallcenter') {
                            $html .= '  <span class="timeline-point timeline-point-success"></span>';
                        } elseif ($rol == 'gestorsede') {
                            $html .= '  <span class="timeline-point timeline-point-warning"></span>';
                        } else {
                            $html .= '  <span class="timeline-point timeline-point-info"></span>';
                        }
                        $html .= '  <div class="timeline-event text-left">';
                        $html .= '      <div class="timeline-header mb-2">';
                        $html .= '          <h6 class="mb-0">' . htmlspecialchars($anotacion->titulo_seguimiento) . '</h6>';
                        $html .= '          <small class="text-muted">' . $anotacion->created_at . '</small>';
                        $html .= '      </div>';
                        $html .= '      <p class="m-0" style="text-align: left;">' . htmlspecialchars($anotacion->nota_seguimiento) . '</p>';
                        $html .= '      <p class="m-0" style="text-align: left;"><small>Autor: <strong>' . htmlspecialchars($nombre_user) . '</strong></small><br><small>' . $rol . '</small></p>';
                        $html .= '  </div>';
                        $html .= '</li>';
                    }
                } else {
                    $html .= '<li class="timeline-item timeline-item-transparent">';
                    $html .= '  <div class="timeline-event">';
                    $html .= '      <p class="m-0">No se encontró seguimiento para esta cita.</p>';
                    $html .= '  </div>';
                    $html .= '</li>';
                }
                $html .= '</ul>';
                $html .= '</div>';

                // Agregar el formulario para agregar un nuevo seguimiento
                $html .= '
                <div class="col-12 col-md-5 mb-4">
                <h5>Agregar Seguimiento</h5>
                <form id="form-seguimiento-modal">
                    <input type="hidden" name="id_cita" value="' . htmlspecialchars($id_cita) . '">
                    <div class="mb-3 text-left">
                        <label class="form-label">Título del seguimiento</label>
                        <input type="text" class="form-control" name="titulo_seguimiento" id="titulo_seguimiento" required>
                    </div>
                    <div class="mb-3 text-left">
                        <label class="form-label">Nota del seguimiento</label>
                        <textarea class="form-control" rows="3" name="nota_seguimiento" id="nota_seguimiento" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-success">Guardar Seguimiento</button>
                </form>
                </div>
                </div>
            ';

                $objLoad = [
                    'validate' => true,
                    'html'     => $html
                ];
            } catch (\Throwable $e) {
                Log::error($e->getMessage());
                $objLoad['text'] = $e->getMessage();
            }
            return response()->json($objLoad);
        }
    }

    public function save_seguimiento(Request $request)
    {
        if ($request->ajax()) {
            $objLoad = [
                'validate' => false,
                'text'     => 'Error al guardar seguimiento'
            ];
            try {
                $id_cita = $request->input('id_cita');
                $titulo  = $request->input('titulo_seguimiento');
                $nota    = $request->input('nota_seguimiento');

                if (empty($id_cita) || empty($titulo) || empty($nota)) {
                    throw new \Exception('Todos los campos son requeridos');
                }

                // Obtener la hora actual y restar 5 horas para ajustar a la hora UTC de Bogotá
                $now = Carbon::now()->subHours(5);

                // Insertar la nueva anotación en la base de datos
                DB::table('tb_seguimiento')->insert([
                    'titulo_seguimiento' => $titulo,
                    'nota_seguimiento'   => $nota,
                    'id_cita'            => $id_cita,
                    'id_user'            => Auth::user()->id,
                    'created_at'         => $now,
                    'updated_at'         => $now
                ]);

                $objLoad = [
                    'validate' => true,
                    'text'     => 'Seguimiento guardado correctamente'
                ];
            } catch (\Throwable $e) {
                Log::error($e->getMessage());
                $objLoad['text'] = $e->getMessage();
            }
            return response()->json($objLoad);
        }
    }


    //Obtener las actividades de la base de datos
    public function get_servicio_liquidador(Request $request)
    {
        if ($request->ajax()) {
            $objLoad = ['validate' => false];
            //Ejecución de la funcion
            try {
                $length = $request->request->get('length');
                $start = $request->request->get('start');
                $draw = $request->request->get('draw');
                // Obtenemos los registros paginados
                $data = DB::table('tb_servicio_liquidador')
                    ->offset($start)
                    ->limit($length)
                    ->get();

                $total_response = DB::table('tb_servicio_liquidador')
                    ->offset($start)
                    ->limit($length)
                    ->get()
                    ->count();
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
    public function add_servicio_liquidador(Request $request)
    {
        if ($request->ajax()) {
            $objLoad = [
                'validate' => false,
                'text' => 'Error al guardar el servicio'
            ];
            try {
                $nombre_servicio_liquidador = $request->request->get('nombre_servicio_liquidador');
                $valor_servicio_liquidador = $request->request->get('valor_servicio_liquidador');
                $color_servicio_liquidador = $request->request->get('color_servicio_liquidador');
                $save = DB::table('tb_servicio_liquidador')->insert([
                    'nombre_servicio_liquidador' => $nombre_servicio_liquidador,
                    'valor_servicio_liquidador'  => $valor_servicio_liquidador,
                    'color_servicio_liquidador'  => $color_servicio_liquidador,
                ]);
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
    //Editar Servicio Liquidador
    public function edit_servicio_liquidador($id)
    {
        $user = Auth::user();
        $data = [
            'page' => 'Citas',
            'subpage' => 'Configuración',
            'rol' => $user->getRoleNames()->first(),
            'user' => $user
        ];
        $alert = AdminHelper::get_count_alert($data['rol'], $user->id_sede); //gestorsede
        $data['alert'] = $alert;

        // Obtener los datos del servicio por ID
        $sql = "SELECT * FROM tb_servicio_liquidador WHERE id_servicio_liquidador = ?";
        $servicio = DB::select($sql, [$id]);

        if (empty($servicio)) {
            // Redirigir si el servicio no existe
            return redirect()->route('/dashboard/citas/configuracion')->withErrors('El servicio no existe o ha sido eliminado.');
        }
        // Pasar los datos del servicio a la vista
        $data['servicio'] = $servicio[0];

        echo view('layouts.header', $data);
        echo view('layouts.nav', $data);
        echo view('dashboard.liquidador.edit_servicios', $data);
        echo view('layouts.footer', $data);
    }
    public function update_servicio_liquidador(Request $request)
    {
        if ($request->ajax()) {
            $objLoad = [
                'validate' => false,
                'text' => 'Error al actualizar el servicio liquidador'
            ];
            try {
                // Validar los datos recibidos
                $request->validate([
                    'id_servicio_liquidador' => 'required|integer|exists:tb_servicio_liquidador,id_servicio_liquidador',
                    'nombre_servicio_liquidador' => 'required|string|max:50',
                    'valor_servicio_liquidador' => 'required|integer|max:9999999999',
                    'color_servicio_liquidador' => 'required|string|max:9' // Aseguramos que sea un color HEX
                ]);

                // Actualizar el servicio en la base de datos
                $update = DB::table('tb_servicio_liquidador')
                    ->where('id_servicio_liquidador', $request->id_servicio_liquidador)
                    ->update([
                        'nombre_servicio_liquidador' => $request->nombre_servicio_liquidador,
                        'valor_servicio_liquidador' => $request->valor_servicio_liquidador,
                        'color_servicio_liquidador' => $request->color_servicio_liquidador,
                        'updated_at' => now() // Actualizar la fecha de modificación
                    ]);

                if ($update) {
                    // Respuesta exitosa
                    $objLoad = [
                        'validate' => true,
                        'text' => 'Servicio actualizado correctamente',
                        'id' => $request->id_servicio_liquidador,
                        'reload' => true
                    ];
                } else {
                    $objLoad['text'] = 'No se realizaron cambios en el servicio.';
                }
            } catch (\Throwable $e) {
                Log::error($e->getMessage());
            }
            return response()->json($objLoad);
        }
    }
    //Vamos a borrar la sede
    public function delete_servicio_liquidador(Request $request)
    {
        if ($request->ajax()) {
            $objLoad = [
                'validate' => false,
                'text' => 'Error al borrar el estado'
            ];
            try {
                $id = $request->request->get('id');
                $sql = "SELECT * FROM tb_sede WHERE id_estado  LIKE '%" . $id . "%'";
                $data = DB::select($sql);
                if (count($data) > 0) {
                    $objLoad['text'] = 'No se puede borrar el servicio porque esta asignado a una sede';
                    return response()->json($objLoad);
                }
                $sql = "DELETE FROM tb_estado WHERE id_servicio    = " . $id . "";
                DB::delete($sql);
                $objLoad = array(
                    "validate" => true,
                    "text" => 'Estado borrado correctamente'
                );
            } catch (\Throwable $e) {
                Log::error($e->getMessage());
            }
            return response()->json($objLoad);
        }
    }

    //Cambiamos estado
    public function change_estado_servicio_liquidador(Request $request)
    {
        if ($request->ajax()) {
            $objLoad = ['validate' => false];
            //Ejecución de la funcion
            try {

                $id_liquidador = $request->request->get('id_liquidador');
                $estado_liquidador = $request->request->get('estado_liquidador');
                $sql = "UPDATE tb_liquidador SET estado_liquidador = '$estado_liquidador' WHERE id_liquidador = '$id_liquidador'";
                $sqlupdate = DB::update($sql);
                if ($sqlupdate) {
                    $objLoad = [
                        'validate' => true,
                        'text' => 'Estado servicio liquidador actualizado correctamente',
                        'id' => $id_liquidador
                    ];
                } else {
                    $objLoad['text'] = 'No se realizaron cambios en el estado.';
                    log::error('No se realizaron cambios en el estado.');
                    log::error($sqlupdate);
                    log::info($id_liquidador);
                    log::info($estado_liquidador);
                }
            } catch (\Throwable $e) {
                Log::error($e->getMessage());
            }
            //retornar respuesta
            return response()->json($objLoad);
        }
    }

    //actualizar comentario
    public function updateComentario(Request $request)
    {
        if ($request->ajax()) {
            $objLoad = [
                'validate' => false,
                'text' => 'Error al guardar el comentario'
            ];
            try {
                $id_cita = $request->request->get('id_cita');
                $id_liquidador = $request->request->get('id_liquidador');
                $comentario = $request->request->get('comentario_liquidador');

                $sql = "UPDATE tb_liquidador SET comentario_liquidador = '$comentario' WHERE id_cita = $id_cita AND id_liquidador = $id_liquidador";
                DB::update($sql);
                $objLoad = [
                    'validate' => true,
                    'text' => 'Comentario actualizado correctamente'
                ];
            } catch (\Throwable $e) {
                Log::error($e->getMessage());
            }
            //retornar respuesta
            return response()->json($objLoad);
        }
    }

    //actualizar comentario
    public function updatePagoMasivo(Request $request)
    {
        if ($request->ajax()) {
            $objLoad = [
                'validate' => false,
                'text'     => 'Error al actualizar el estado de pago'
            ];
            try {
                // Array de objetos con { id_cita, id_liquidador }
                $citas            = $request->input('citas', []);
                $pago_liquidador  = $request->input('pago_liquidador'); // "pagado" o "pendiente"

                // Recorremos el array para actualizar cada registro
                foreach ($citas as $cita) {
                    // Convertir a int para evitar inyecciones (o usa bindParam si gustas)
                    $id_cita       = (int) $cita['id_cita'];
                    $id_liquidador = (int) $cita['id_liquidador'];

                    // Actualizar con Query Builder
                    DB::table('tb_liquidador')
                        ->where('id_cita', $id_cita)
                        ->where('id_liquidador', $id_liquidador)
                        ->update([
                            'pago_liquidador' => $pago_liquidador
                        ]);
                }

                $objLoad = [
                    'validate' => true,
                    'text'     => 'Estado de pago actualizado correctamente.'
                ];
            } catch (\Throwable $e) {
                Log::error($e->getMessage());
                $objLoad['text'] = 'Error: ' . $e->getMessage();
            }
            //retornar respuesta
            return response()->json($objLoad);
        }
    }
    // Generamos el CSV de liquidador
    public function dowloadLiquidador(Request $request)
    {
        if ($request->ajax()) {
            $objLoad = [
                'validate' => false,
                'text' => 'Error al borrar el estado'
            ];
            try {
                $user = Auth::user();
                $rol =  $user->getRoleNames()->first();
                $start = $request->request->get('start');
                $length = 100;

                // Filtros personalizados
                $filtro_dia      = $request->input('filtro_dia');
                $filtro_dia_end  = $request->input('filtro_dia_end');
                $filtro_sede     = $request->input('filtro_sede');
                $filtro_search   = $request->input('filtro_search');
                $filtro_servicio_liquidador = $request->input('filtro_servicios_liquidador');
                $filtro_estado_validacion_liquidador = $request->input('filtro_estado_validacion_liquidador');
                $filtro_estado_pago_liquidador = $request->input('filtro_estado_pago_liquidador');
                $tipo_cita = $request->input('tipo_cita');
                $fecha_actual = date('Y-m-d');
                // Rango por defecto: primer y último día del mes actual
                $startOfMonth = date('Y-m-01');
                $endOfMonth   = date('Y-m-t');
                // Construcción de la consulta

                // Log::info($request);
                // --------------------------------------
                // 1) Consulta base para contar registros
                // --------------------------------------
                $sqlBase = "
                                FROM tb_cita AS t1
                                INNER JOIN tb_cliente AS t2
                                    ON t1.id_cliente = t2.id_cliente
                                INNER JOIN tb_estado AS t3
                                    ON t1.id_estado = t3.id_estado
                                INNER JOIN tb_estado AS t4
                                    ON t1.id_estado_verificado = t4.id_estado
                                INNER JOIN tb_sede   AS t5
                                    ON t1.id_sede = t5.id_sede
                                INNER JOIN tb_servicio AS t6
                                    ON t5.id_servicio = t6.id_servicio
                                LEFT JOIN tb_liquidador AS l
                                    ON t1.id_cita = l.id_cita
                                LEFT JOIN tb_vehiculo AS v
                                    ON t1.id_vehiculo = v.id_vehiculo
                                LEFT JOIN tb_servicio_liquidador AS s
                                    ON t1.id_servicio_liquidador = s.id_servicio_liquidador
                                WHERE t1.id_cita > 0
                                AND t4.nombre_estado = 'Asistió'
                            ";

                // Filtro si el rol es 'gestorsede'
                if ($rol == 'gestorsede') {
                    $sqlBase .= " AND t5.id_sede = " . $user->id_sede;
                }

                // Filtro de fechas
                if (!empty($filtro_dia) && !empty($filtro_dia_end)) {
                    $sqlBase .= " AND t1.reserva_cita BETWEEN '$filtro_dia' AND '$filtro_dia_end'";
                    $dateNomArchivo = $filtro_dia . "-" . $filtro_dia_end;
                } else {
                    // Por defecto, primer y último día del mes
                    $sqlBase .= " AND t1.reserva_cita BETWEEN '$startOfMonth' AND '$endOfMonth'";
                    $dateNomArchivo = $startOfMonth . "-" . $endOfMonth;
                }

                // Filtro de sede
                if (!empty($filtro_sede)) {
                    $sqlBase .= " AND t5.id_sede = $filtro_sede";
                }

                // Filtro de servicio liquidador
                if (!empty($filtro_servicio_liquidador)) {
                    $sqlBase .= " AND t1.id_servicio_liquidador = $filtro_servicio_liquidador";
                    // log::info($filtro_servicio_liquidador . " - Si pasa");
                }

                // Filtro de estado validacion liquidador
                if (!empty($filtro_estado_validacion_liquidador)) {
                    $sqlBase .= " AND l.estado_liquidador = '$filtro_estado_validacion_liquidador'";
                    // log::info($filtro_estado_validacion_liquidador . " - Si pasa");
                }

                // Filtro de estado pago liquidador
                if (!empty($filtro_estado_pago_liquidador)) {
                    $sqlBase .= " AND l.pago_liquidador = '$filtro_estado_pago_liquidador'";
                    // log::info($filtro_estado_pago_liquidador . " - Si pasa");
                }

                // Filtro de búsqueda
                if (!empty($filtro_search)) {
                    $sqlBase .= "
                AND (
                    t2.nombre_cliente   LIKE '%$filtro_search%' OR
                    t2.apellido_cliente LIKE '%$filtro_search%' OR
                    t2.doc_cliente      LIKE '%$filtro_search%' OR
                    t2.telefono_cliente LIKE '%$filtro_search%'
                )";
                }

                if ($tipo_cita) {
                    $sqlBase .= " AND t6.tipo_servicio = '$tipo_cita'";
                }

                // --------------------------------------
                // 3) Consulta para paginación
                // --------------------------------------
                $sqlData = "SELECT t1.*,
                                t1.created_at AS fecha_create,

                                t2.nombre_cliente,
                                t2.apellido_cliente,
                                t2.doc_cliente,
                                t2.tipo_doc_cliente,
                                t2.telefono_cliente,
                                t2.email_cliente,
                                t2.desc_cliente,

                                t3.id_estado        AS estado_actual_id,
                                t3.nombre_estado    AS estado_actual_nombre,
                                t3.color_estado     AS estado_actual_color,

                                t4.id_estado        AS estado_verificado_id,
                                t4.nombre_estado    AS estado_verificado_nombre,
                                t4.color_estado     AS estado_verificado_color,
                                t4.desc_estado      AS estado_verificado_desc,

                                t5.nombre_sede,
                                t5.direccion_sede,
                                t5.tel_sede,
                                t5.estado_sede,

                                t6.tipo_servicio,

                                l.id_liquidador,
                                l.estado_liquidador,
                                l.comentario_liquidador,
                                l.pago_liquidador,

                                v.id_vehiculo,
                                v.placa_vehiculo,
                                v.tipo_vehiculo,
                                v.modelo_vehiculo,

                                s.id_servicio_liquidador,
                                s.nombre_servicio_liquidador,
                                s.valor_servicio_liquidador,
                                s.color_servicio_liquidador
                            " . $sqlBase . "
                            ORDER BY t1.reserva_cita, t1.rango_horario, t1.id_sede ASC
                            LIMIT " . (int)$start . ", " . (int)$length;

                // Log::info($sqlData);
                // Limitar registros por página
                $records =  DB::select($sqlData);
                if (empty($records)) {
                    return response()->json([
                        'status' => 'completed',
                        'url' => url('data/export-liquidador-' . $dateNomArchivo . '.csv')
                    ]);
                }
                // Ruta y apertura del archivo CSV en la carpeta public/data
                $filePath = 'public_html/data/export-liquidador-' . $dateNomArchivo . '.csv';
                $fullPath = base_path($filePath);
                // Crear la carpeta 'data' si no existe
                if (!file_exists(base_path('public_html/data'))) {
                    mkdir(base_path('public_html/data'), 0777, true);
                }
                $file = fopen($fullPath, $start == 0 ? 'w' : 'a');
                if ($start == 0) {
                    // 1) Escribir BOM para que Excel reconozca UTF-8
                    fwrite($file, chr(239) . chr(187) . chr(191));
                    // 2) Forzar a Excel a usar ';' como separador de columnas
                    fwrite($file, "sep=;\n");
                    // Encabezados en la primera escritura
                    fputcsv($file, [
                        'ID Cita',
                        'Cliente',
                        'Estado',
                        'Sede',
                        'Horario',
                        'Fecha de Reserva',
                        'Descripción',
                        'Fecha Creación',
                        'Tipo Documento',
                        'Documento Cliente',
                        'Teléfono Cliente',
                        'Email Cliente',
                        'Estado Liquidador',
                        'Tipo Servicio',
                        'Costo',
                        'Comentario Liquidador',
                        'Placa Vehiculo',
                        'tipo Vehiculo',
                        'Modelo Vehiculo',
                        'Creado Por',
                        'Origen',
                        'Url Variables',
                        'Tipo dispositivo'
                    ], ';');
                }
                foreach ($records as $record) {
                    fputcsv($file, [
                        $record->id_cita,
                        $record->nombre_cliente . ' ' . $record->apellido_cliente,
                        $record->estado_verificado_nombre,
                        $record->nombre_sede,
                        $record->rango_horario,
                        $record->reserva_cita,
                        $record->desc_cita,
                        $record->created_at,
                        $record->tipo_doc_cliente,
                        $record->doc_cliente,
                        "\t" . $record->telefono_cliente,
                        $record->email_cliente,
                        $record->estado_liquidador,
                        $record->nombre_servicio_liquidador,
                        $record->valor_servicio_liquidador,
                        $record->comentario_liquidador,
                        $record->placa_vehiculo,
                        $record->tipo_vehiculo,
                        $record->modelo_vehiculo,
                        $record->placa_vehiculo,
                        $record->origen,
                        $record->url_variables,
                        $record->tipo_dispositivo,
                    ], ';');
                }
                fclose($file);
                if (empty($records)) {
                    // Si ya se terminó de procesar, se puede retornar una respuesta de descarga
                    return response()->download($fullPath, 'export-liquidador-' . $dateNomArchivo . '.csv', [
                        'Content-Type' => 'text/csv; charset=UTF-8',
                        'Content-Disposition' => 'attachment; filename="export-liquidador-' . $dateNomArchivo . '.csv"'
                    ]);
                }
                return response()->json(['status' => 'in_progress', 'nextStart' => $start + $length]);
            } catch (\Throwable $e) {
                Log::error($e->getMessage());
            }
            return response()->json($objLoad);
        }
    }
}

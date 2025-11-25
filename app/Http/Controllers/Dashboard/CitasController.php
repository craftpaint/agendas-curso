<?php

namespace App\Http\Controllers\Dashboard;

use App\Services\SendPulseService;
use App\Services\CrmService;
use App\Services\ScrapingService;
use App\Helpers\UtilsHelper;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Helpers\AdminHelper;
use App\Helpers\PaqueteHelper;
use App\Models\User;
use Carbon\Carbon;
use App\Jobs\UpdateStepDealCrm;
use App\Jobs\UpdateOperatorDealCrm;

use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class CitasController extends Controller
{
    protected $sendPulse;
    protected $crmService;
    protected $scrapingService;
    protected $utilsHelper;

    public function __construct(SendPulseService $sendPulse, CrmService $crmService, ScrapingService $scrapingService, UtilsHelper $utilsHelper)
    {
        $this->sendPulse = $sendPulse;
        $this->crmService = $crmService;
        $this->scrapingService = $scrapingService;
        $this->utilsHelper = $utilsHelper;
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
        if ($user->can('global.Solo ver sede asignada.v')) {
            $data['sedes'] = DB::table('tb_sede')
                ->where('id_sede', $user->id_sede)
                ->get();
        } else {
            $data['sedes'] = DB::table('tb_sede')->get();
        }
        //Estados
        $data['estados'] = DB::table('tb_estado')->get();

        $agentes = User::permission('global.Asignar citas call.v')
            ->where('callcenter_habilitado', 1)
            ->orderBy('id', 'asc')
            ->get();
        $data['agentes'] = $agentes->toArray();

        $agentes2 = User::permission('global.Asignar citas call.v')
            ->orderBy('id', 'asc')
            ->get();

        $data['listado_agentes'] = $agentes2->toArray();

        $data['servicios_liquidador'] = DB::table('tb_servicio_liquidador')->get();
        //Origenes
        $data['origenes'] = DB::table('tb_cita')
            ->select('origen')
            ->whereNotNull('origen')
            ->distinct()
            ->orderBy('origen', 'asc')
            ->get();

        $data['tipoSede'] = "";

        echo view('layouts.header', $data);
        echo view('layouts.nav', $data);
        echo view('layouts.navigation', $data);
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
                $filtro_tipo_paquete_cita = $request->input('filtro_tipo_paquete_cita');
                $filtro_search = $request->input('filtro_search');
                $fecha_actual = date('Y-m-d');

                // Subconsulta: obtener el último liquidador por cita
                $liquidadorSub = DB::table('tb_liquidador')
                    ->select('id_cita', DB::raw('MAX(id_liquidador) as id_liquidador'))
                    ->groupBy('id_cita');

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
                        't5.direccion_sede',
                        't5.id_servicio',
                        't6.tipo_servicio',
                        'a.name as agente_callcenter',
                        'a.id_user_sendpulse',
                        'a.id_chatbot_sendpulse',
                        'a.id_plantilla_sendpulse',
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
                        'v.modelo_vehiculo',
                        'tb_paquete.tipo_paquete'
                    )
                    ->addSelect(DB::raw("(SELECT COUNT(*) FROM tb_seguimiento AS ts WHERE ts.id_cita = t1.id_cita) AS total_anotaciones"))
                    ->join('tb_cliente as t2', 't1.id_cliente', '=', 't2.id_cliente')
                    ->join('tb_estado as t3', 't1.id_estado', '=', 't3.id_estado')
                    ->join('tb_estado as t4', 't1.id_estado_verificado', '=', 't4.id_estado')
                    ->join('tb_sede as t5', 't1.id_sede', '=', 't5.id_sede')
                    ->join('tb_servicio as t6', 't5.id_servicio', '=', 't6.id_servicio')
                    ->join('users as a', 't1.id_agente_callcenter', '=', 'a.id')
                    ->leftJoin('tb_empresa_paquete', 't1.id_empresa_paquete', '=', 'tb_empresa_paquete.id_empresa_paquete')
                    ->leftJoin('tb_paquete', 'tb_empresa_paquete.id_paquete', '=', 'tb_paquete.id_paquete')

                    // JOIN con subconsulta para evitar duplicados
                    ->leftJoinSub(
                        $liquidadorSub,
                        'lm',
                        function ($join) {
                            $join->on('t1.id_cita', '=', 'lm.id_cita');
                        }
                    )
                    ->leftJoin('tb_liquidador as l', function ($join) {
                        $join->on('lm.id_liquidador', '=', 'l.id_liquidador');
                    })
                    ->leftJoin('tb_servicio_liquidador as s', 't1.id_servicio_liquidador', '=', 's.id_servicio_liquidador')
                    ->leftJoin('tb_vehiculo as v', 't1.id_vehiculo', '=', 'v.id_vehiculo')
                    ->where('t1.id_cita', '>', 0);

                // Si el usuario tiene el permiso 'global.Pertenece a empresa aliada.v',
                // entonces filtrar por la empresa asociada a la sede del usuario.
                if ($user->can('global.Pertenece a empresa aliada.v')) {
                    // Se asume que la tabla de sedes tiene la columna 'empresa_id'
                    $sede_usuario = DB::table('tb_sede')->where('id_sede', $user->id_sede)->first();
                    if ($sede_usuario && isset($sede_usuario->id_empresa)) {
                        $query->where('t5.id_empresa', $sede_usuario->id_empresa);
                    }
                }

                // Aplicar filtros por rol
                if ($user->can('global.Solo ver sede asignada.v')) {
                    $query->where('t5.id_sede', $user->id_sede);
                }
                if ($user->can('global.Solo ver citas asignadas.v')) {
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

                if ($filtro_tipo_paquete_cita) {
                    if (is_array($filtro_tipo_paquete_cita)) {
                        $query->whereIn('tb_paquete.tipo_paquete', $filtro_tipo_paquete_cita);
                    } else {
                        $query->where('tb_paquete.tipo_paquete', $filtro_tipo_paquete_cita);
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
                                    ->orWhere('t2.telefono_cliente', 'like', '%' . $palabra . '%')
                                    ->orWhere('t2.email_cliente', 'like', '%' . $palabra . '%')
                                    ->orWhere('v.placa_vehiculo', 'like', '%' . $palabra . '%');
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
        if ($user->can('global.Solo ver sede asignada.v')) {
            $data['sedes'] = DB::table('tb_sede')
                ->where('id_sede', $user->id_sede)
                ->get();
        } else {
            $data['sedes'] = DB::table('tb_sede')->get();
        }
        // Estados: se obtienen todos los estados.
        $data['estados'] = DB::table('tb_estado')->get();

        // Servicios Liquidador: se obtienen todos los servicios.
        $data['servicios_liquidador'] = DB::table('tb_servicio_liquidador')->get();

        echo view('layouts.header', $data);
        echo view('layouts.nav', $data);
        echo view('layouts.navigation', $data);
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
                $id = $request->input('id');
                $horarios = DB::table('tb_sede_horario as t1')
                    ->join('tb_horario as t2', 't1.id_horario', '=', 't2.id_horario')
                    ->where('t1.id_sede', $id)
                    ->get();

                if ($horarios->isNotEmpty()) {
                    $objLoad = [
                        'validate' => true,
                        'horarios' => $horarios
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
                'text' => 'Error al guardar la cita'
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
                $codigo_comparendo = $request->request->get('codigo_comparendo');
                $desc_cita = $request->request->get('desc_cita');
                $empresa_paquete = PaqueteHelper::obtenerPaqueteActivoEmpresa($id_sede);

                // Obtener nombre de la sede
                $sede = DB::table('tb_sede')->where('id_sede', $id_sede)->first();
                $nombre_sede = $sede ? $sede->nombre_sede : 'Sede no encontrada';
                $direccion_sede = $sede ? $sede->direccion_sede : 'Dirección no encontrada';
                $id_servicio = $sede ? $sede->id_servicio : 'Servicio no encontrado';
                $latitud = $sede ? str_replace(',', '.', $sede->latitud) : '';
                $longitud = $sede ? str_replace(',', '.', $sede->longitud) : '';

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

                if ($user->can('global.Asignar citas call.v')) {
                    $idAgenteCallcenter = $user->id;
                } else {
                    $agentes = User::permission('global.Asignar citas call.v')
                        ->where('callcenter_habilitado', 1)
                        ->orderBy('id', 'asc')
                        ->get();
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
                // Insertar cita
                $now = Carbon::now();
                $citaData = [
                    'id_cliente' => $id_cliente,
                    'id_sede' => $id_sede,
                    'id_estado' => $id_estado,
                    'id_estado_verificado' => $id_estado_verificado,
                    'id_servicio_liquidador' => 5,
                    'id_agente_callcenter' => $idAgenteCallcenter ?? null,
                    'id_servicio_liquidador' => $id_servicio_liquidador,
                    'codigos_comparendo'      => $codigo_comparendo,
                    'reserva_cita' => $reserva_cita,
                    'rango_horario' => $rango_horario,
                    'desc_cita' => $desc_cita,
                    'responsable_origen' => 'Curso Comparendo',
                    'creado_por' => $rol . "-" . $user->name,
                    'origen' => 'Curso Comparendo',
                    'tipo_dispositivo' => $tipo_dispositivo,
                    'created_at' => $now,
                    'updated_at' => $now,
                    'id_empresa_paquete' => $empresa_paquete
                ];

                if ($id_vehiculo) {
                    $citaData['id_vehiculo'] = $id_vehiculo;
                }

                $id_cita = DB::table('tb_cita')->insertGetId($citaData);


                if ($id_cita) {
                    try {
                        // Consulta la información del cliente
                        $cliente = DB::table('tb_cliente')->where('id_cliente', $id_cliente)->first();

                        // Consulta la información de la sede
                        $sede = DB::table('tb_sede')->where('id_sede', $id_sede)->first();

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
                            'latitud'          => $latitud,
                            'longitud'         => $longitud,
                        ];
                        // Enviar el correo al cliente
                        $enviadoCliente = $this->sendPulse->sendEmailConfirmacion(
                            $email_cliente,
                            $nombre_cliente,
                            $nombre_cliente . " Confirmamos tu cita",
                            $cliente->doc_cliente,
                            $sede,
                            $templateVariables
                        );

                        if (!$enviadoCliente) {
                            Log::error("Error al enviar el correo al cliente");
                        }
                    } catch (\Exception $e) {
                        Log::error($e->getMessage());
                    }
                    try {
                        // Crear liquidador
                        DB::table('tb_liquidador')->insert([
                            'id_cita' => $id_cita,
                            'estado_liquidador' => "Pendiente",
                            'comentario_liquidador' => "",
                            'pago_liquidador' => "Pendiente",
                            'created_at' => $now,
                            'updated_at' => $now
                        ]);
                        // Verificar si se envían anotaciones para seguimiento
                        $nota_seguimiento  = $request->input('nota_seguimiento');
                        $titulo_seguimiento = $request->input('titulo_seguimiento');
                        if (!empty($nota_seguimiento) && !empty($titulo_seguimiento)) {
                            DB::table('tb_seguimiento')->insert([
                                'titulo_seguimiento' => $titulo_seguimiento,
                                'nota_seguimiento'   => $nota_seguimiento,
                                'id_cita'            => $id_cita,
                                'id_user'            => Auth::user()->id,
                                'created_at'         => Carbon::now(),
                                'updated_at'         => Carbon::now()
                            ]);
                        }
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

        // Obtener alertas
        $data['alert'] = AdminHelper::get_count_alert($data['rol'], $user->id_sede);


        // Obtener la cita (se asume que AdminHelper::get_cita_by_id retorna un arreglo)
        $data['cita'] = AdminHelper::get_cita_by_id($id);
        if (!is_array($data['cita']) || empty($data['cita'])) {
            return redirect()->route('citas');
        }
        $data['cita'] = $data['cita'][0];

        // Obtener sedes, estados y servicios liquidador con Query Builder
        $data['sedes'] = DB::table('tb_sede')->get();
        $data['estados'] = DB::table('tb_estado')->get();
        $data['servicios_liquidador'] = DB::table('tb_servicio_liquidador')->get();

        // Anotaciones (seguimientos) de la cita
        $data['anotaciones'] = DB::table('tb_seguimiento')
            ->where('id_cita', $id)
            ->get();

        // Recorrer las anotaciones para añadir el nombre del usuario que las ingresó
        foreach ($data['anotaciones'] as $anotacion) {
            $usuario = User::find($anotacion->id_user);
            $anotacion->nombre_user = $usuario ? $usuario->name : null;
        }
        echo view('layouts.header', $data);
        echo view('layouts.nav', $data);
        echo view('layouts.navigation', $data);
        echo view('dashboard.citas.view', $data);
        echo view('layouts.footer', $data);
    }
    //Ver sede
    public function edit($id)
    {
        $user = Auth::user();
        $data = [
            'page'    => 'Citas',
            'subpage' => 'Listado',
            'rol'     => $user->getRoleNames()->first(),
            'user'    => $user,
        ];

        $data['alert'] = AdminHelper::get_count_alert($data['rol'], $user->id_sede);
        $data['cita'] = AdminHelper::get_cita_by_id($id);
        if (!is_array($data['cita']) || empty($data['cita'])) {
            return redirect()->route('citas');
        }
        $data['cita'] = $data['cita'][0];

        $data['sedes'] = DB::table('tb_sede')->get();
        $data['estados'] = DB::table('tb_estado')->get();
        $data['servicios_liquidador'] = DB::table('tb_servicio_liquidador')->get();
        $data['anotaciones'] = DB::table('tb_seguimiento')->where('id_cita', $id)->get();

        // Obtener vehículos asociados al cliente
        $data['vehiculos'] = DB::table('tb_vehiculo')
            ->where('id_cliente', $id)
            ->get();

        // Obtener agentes callcenter que tengan el permiso y estén habilitados
        $data['agentes_callcenter'] = User::permission('global.Asignar citas call.v')
            ->where('callcenter_habilitado', 1)
            ->orderBy('id', 'asc')
            ->get();

        // Recorrer anotaciones para asignar el nombre del usuario
        foreach ($data['anotaciones'] as $anotacion) {
            $usuario = User::find($anotacion->id_user);
            $anotacion->nombre_user = $usuario ? $usuario->name : null;
        }

        echo view('layouts.header', $data);
        echo view('layouts.nav', $data);
        echo view('layouts.navigation', $data);
        echo view('dashboard.citas.edit', $data);
        echo view('layouts.footer', $data);
    }
    //Actualizar cita

    public function update(Request $request)
    {
        if ($request->ajax()) {
            $objLoad = [
                'validate' => false,
                'text'     => 'Error al actualizar la cita'
            ];

            try {
                // Obtener datos del request
                $id_sede                = $request->input('id_sede');
                $id_cita                = $request->input('id_cita');
                $id_cliente             = $request->input('id_cliente');
                $id_vehiculo            = $request->input('id_vehiculo');
                $id_estado              = $request->input('id_estado');
                $id_estado_verificado   = $request->input('id_estado_verificado');
                $id_sede_horario        = $request->input('id_sede_horario');
                $id_servicio_liquidador = $request->input('id_servicio_liquidador');
                $reserva_cita           = $request->input('reserva_cita');
                $desc_cita              = $request->input('desc_cita');
                $id_agente_callcenter   = $request->input('id_agente_callcenter');
                $codigo_comparendo      = $request->input('codigo_comparendo');
                $id_whatsapp_sendpulse  = $request->input('id_whatsapp_sendpulse');
                $id_trato_sendpulse     = $request->input('id_trato_sendpulse');

                // Convertir la fecha de formato d/m/Y a Y-m-d
                $date = \DateTime::createFromFormat('d/m/Y', $reserva_cita);
                if ($date) {
                    $reserva_cita = $date->format('Y-m-d');
                } else {
                    throw new \Exception("El formato de la fecha es incorrecto");
                }

                // Obtener el horario de la sede
                $horario_sedes = AdminHelper::get_horario_by_id($id_sede_horario);
                if (is_array($horario_sedes) && !empty($horario_sedes)) {
                    $cupo_sede_horario = $horario_sedes['cupo_sede_horario'];
                    $id_horario = $horario_sedes['id_horario'];
                    // Obtener el rango horario
                    $horario = AdminHelper::get_horarios_by_id($id_horario);
                    $rango_horario = $horario['rango_horario'];

                    // Verificar cupo usando Query Builder
                    $citasCount = DB::table('tb_cita')
                        ->where('id_sede', $id_sede)
                        ->where('reserva_cita', $reserva_cita)
                        ->where('rango_horario', $rango_horario)
                        ->where('id_cita', '<>', $id_cita)
                        ->count();

                    if ($citasCount >= $cupo_sede_horario) {
                        $objLoad['text'] = 'No hay cupo disponible para la cita';
                        return response()->json($objLoad);
                    }
                }

                // Preparar datos para la actualización.
                // Si $id_vehiculo viene, se actualiza ese campo; si no, se omite o se deja nulo.
                $updateData = [
                    'id_cliente'             => $id_cliente,
                    'id_sede'                => $id_sede,
                    'id_estado'              => $id_estado,
                    'id_estado_verificado'   => $id_estado_verificado,
                    'id_servicio_liquidador' => $id_servicio_liquidador,
                    'codigos_comparendo'      => $codigo_comparendo,
                    'id_agente_callcenter'   => $id_agente_callcenter,
                    'reserva_cita'           => $reserva_cita,
                    'rango_horario'          => $rango_horario,
                    'desc_cita'              => $desc_cita,
                    'id_whatsapp_sendpulse'  => $id_whatsapp_sendpulse,
                    'id_trato_sendpulse'     => $id_trato_sendpulse,
                    'updated_at'             => Carbon::now()
                ];

                // Agregar id_vehiculo si se proporciona
                if ($id_vehiculo) {
                    $updateData['id_vehiculo'] = $id_vehiculo;
                }

                PaqueteHelper::validarCambioEstado($id_cita, $id_estado_verificado);
                // Realizar la actualización
                DB::table('tb_cita')
                    ->where('id_cita', $id_cita)
                    ->update($updateData);

                // Insertar la nueva anotación en la base de datos
                DB::table('tb_seguimiento')->insert([
                    'titulo_seguimiento' => 'Actualización de cita',
                    'nota_seguimiento'   => 'La cita ha sido actualizada completamente.',
                    'id_cita'            => $id_cita,
                    'id_user'            => Auth::user()->id,
                    'created_at'         => Carbon::now(),
                    'updated_at'         => Carbon::now()
                ]);

                // Verificar si se envían anotaciones para seguimiento
                $nota_seguimiento  = $request->input('nota_seguimiento');
                $titulo_seguimiento = $request->input('titulo_seguimiento');
                if (!empty($nota_seguimiento) && !empty($titulo_seguimiento)) {
                    DB::table('tb_seguimiento')->insert([
                        'titulo_seguimiento' => $titulo_seguimiento,
                        'nota_seguimiento'   => $nota_seguimiento,
                        'id_cita'            => $id_cita,
                        'id_user'            => Auth::user()->id,
                        'created_at'         => Carbon::now(),
                        'updated_at'         => Carbon::now()
                    ]);
                }

                // Se consulta el ID del deal en CRM relacionado a la cita
                $idDealCrm = DB::table('tb_cita')
                    ->where('id_cita', $id_cita)
                    ->value('id_trato_sendpulse');

                // Se consulta el Step relacionado al estado
                $step_sendpulse = DB::table('tb_estado')
                    ->where('id_estado', $id_estado_verificado)
                    ->value('id_step_sendpulse');

                // Se ejecuta el cambio en el CRM.
                if ($step_sendpulse && $idDealCrm) {
                    updateStepDealCrm::dispatch($idDealCrm, $step_sendpulse)->onQueue('crm');
                }

                UpdateOperatorDealCrm::dispatch($id_cita, $id_agente_callcenter)->onQueue('crm');
                $objLoad = [
                    'validate' => true,
                    'text'     => 'Cita actualizada correctamente',
                    'id'       => $id_cita
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
                'text'     => 'Error al borrar la sede'
            ];
            try {
                $id = $request->input('id');
                DB::table('tb_cita')
                    ->where('id_cita', $id)
                    ->delete();

                $objLoad = [
                    'validate' => true,
                    'text'     => 'Cita borrada correctamente'
                ];
            } catch (\Throwable $e) {
                Log::error($e->getMessage());
            }
            return response()->json($objLoad);
        }
    }
    //Cambiamos estado
    public function change_estado(Request $request)
    {
        if ($request->ajax()) {
            $objLoad = ['validate' => false];
            try {
                $id_estado = $request->input('id_estado');
                $id_cita   = $request->input('id_cita');

                DB::table('tb_cita')
                    ->where('id_cita', $id_cita)
                    ->update([
                        'id_estado' => $id_estado,
                        'updated_at' => Carbon::now()
                    ]);

                $nombre_estado = DB::table('tb_estado')
                    ->where('id_estado', $id_estado)
                    ->value('nombre_estado');

                // Insertar la nueva anotación en la base de datos
                DB::table('tb_seguimiento')->insert([
                    'titulo_seguimiento' => 'El estado de la cita ha cambiado',
                    'nota_seguimiento'   => 'El estado de la cita ha cambiado a ' . $nombre_estado,
                    'id_cita'            => $id_cita,
                    'id_user'            => Auth::user()->id,
                    'created_at'         => Carbon::now(),
                    'updated_at'         => Carbon::now()
                ]);

                $objLoad = [
                    'validate' => true,
                    'text'     => 'Cita actualizada correctamente'
                ];
            } catch (\Throwable $e) {
                Log::error($e->getMessage());
            }
            return response()->json($objLoad);
        }
    }
    //Cambiamos estado
    public function change_estado_verificado(Request $request)
    {
        if ($request->ajax()) {
            $objLoad = ['validate' => false];

            try {
                $id_estado_verificado = $request->input('id_estado');
                $id_cita = $request->input('id_cita');

                PaqueteHelper::validarCambioEstado($id_cita, $id_estado_verificado);

                DB::table('tb_cita')
                    ->where('id_cita', $id_cita)
                    ->update([
                        'id_estado_verificado' => $id_estado_verificado,
                        'updated_at' => Carbon::now()
                    ]);


                $nombre_estado = DB::table('tb_estado')
                    ->where('id_estado', $id_estado_verificado)
                    ->value('nombre_estado');

                // Insertar la nueva anotación en la base de datos
                DB::table('tb_seguimiento')->insert([
                    'titulo_seguimiento' => 'El estado verificado de la cita ha cambiado',
                    'nota_seguimiento'   => 'El estado verificado de la cita ha cambiado a ' . $nombre_estado,
                    'id_cita'            => $id_cita,
                    'id_user'            => Auth::user()->id,
                    'created_at'         => Carbon::now(),
                    'updated_at'         => Carbon::now()
                ]);

                // Se consulta el ID del deal en CRM relacionado a la cita
                $idDealCrm = DB::table('tb_cita')
                    ->where('id_cita', $id_cita)
                    ->value('id_trato_sendpulse');

                // Se consulta el Step relacionado al estado
                $step_sendpulse = DB::table('tb_estado')
                    ->where('id_estado', $id_estado_verificado)
                    ->value('id_step_sendpulse');

                if ($step_sendpulse && $idDealCrm) {
                    updateStepDealCrm::dispatch($idDealCrm, $step_sendpulse)->onQueue('crm');
                }

                $objLoad = [
                    'validate' => true,
                    'text' => 'Estado verificado actualizado correctamente'
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
                // Usamos el Query Builder
                $updated = DB::table('tb_cita')
                    ->where('id_cita', $id_cita)
                    ->update([
                        'id_agente_callcenter' => $id_agente,
                        'updated_at' => Carbon::now()
                    ]);

                if ($updated) {
                    // Se ejecuta el cambio en el CRM.
                    UpdateOperatorDealCrm::dispatch($id_cita, $id_agente)->onQueue('crm');

                    $nombre_agente = DB::table('users')
                        ->where('id', $id_agente)
                        ->value('name');

                    // Insertar la nueva anotación en la base de datos
                    DB::table('tb_seguimiento')->insert([
                        'titulo_seguimiento' => 'El agente de la cita ha cambiado',
                        'nota_seguimiento'   => 'El agente de la cita ha sido reasignado a ' . $nombre_agente,
                        'id_cita'            => $id_cita,
                        'id_user'            => Auth::user()->id,
                        'created_at'         => Carbon::now(),
                        'updated_at'         => Carbon::now()
                    ]);

                    $objLoad = [
                        'validate' => true,
                        'text' => 'Agente Callcenter actualizado correctamente',
                        'id' => $id_cita
                    ];
                } else {
                    $objLoad['text'] = 'No se realizaron cambios en el agente Callcenter.';
                    log::error('No se realizaron cambios en el agente Callcenter.');
                    log::error($updated);
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
        echo view('layouts.navigation', $data);
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
                $id_step_sendpulse = $request->request->get('id_step_sendpulse');
                $color_estado = $request->request->get('color_estado');
                $sql = "INSERT INTO tb_estado (nombre_estado, desc_estado, id_step_sendpulse, color_estado) VALUES ('$nombre_estado', '$desc_estado ', ' $id_step_sendpulse', '$color_estado')";
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
        echo view('layouts.navigation', $data);
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
                    'id_step_sendpulse' => 'required|nullable|integer',
                    'color_estado' => 'required|string|size:7' // Aseguramos que sea un color HEX
                ]);

                // Actualizar el estado en la base de datos
                $update = DB::table('tb_estado')
                    ->where('id_estado', $request->id_estado)
                    ->update([
                        'nombre_estado' => $request->nombre_estado,
                        'desc_estado' => $request->desc_estado,
                        'id_step_sendpulse' => $request->id_step_sendpulse,
                        'color_estado' => $request->color_estado,
                        'updated_at' =>  Carbon::now() // Actualizar la fecha de modificación
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

    //Descargar archivo
    public function dowload(Request $request)
    {
        if ($request->ajax()) {
            $objLoad = [
                'validate' => false,
                'text' => 'Error al descargar el listado de las citas'
            ];

            try {
                $user = Auth::user();
                $filtros = $request->input();

                $fecha_actual = date('Y-m-d');
                // Rango por defecto: primer y último día del mes actual
                $startOfMonth = date('Y-m-01');
                $endOfMonth   = date('Y-m-t');

                $query = $this->citasconsultaDB();

                // --------------------------------------
                // 1) Construcción de la consulta base (SQL directo)
                // --------------------------------------

                // Filtro para el rol 'gestorsede'
                if ($user->can('global.Solo ver sede asignada.v')) {
                    $query->where('t5.id_sede', $user->id_sede);
                }

                // Filtro de fechas: si se envían ambos, se filtra entre esas fechas; si no se envían, se utiliza el mes actual
                if (!empty($filtros['filtro_dia']) && !empty($filtros['filtro_dia_end'])) {
                    $query->whereBetween('t1.reserva_cita', [$filtros['filtro_dia'], $filtros['filtro_dia_end']]);
                    $dateNomArchivo = $filtros['filtro_dia'] . "-" . $filtros['filtro_dia_end'];
                } else {
                    $query->whereBetween('t1.reserva_cita', [$startOfMonth, $endOfMonth]);
                    $dateNomArchivo = $startOfMonth . "-" . $endOfMonth;
                }

                // Filtro de sede
                if (!empty($filtros['filtro_sede'])) {
                    $query->where('t5.id_sede', $filtros['filtro_sede']);
                }

                // Filtro de servicio liquidador
                if (!empty($filtros['filtro_servicio_liquidador'])) {
                    $query->where('t1.id_servicio_liquidador', $filtros['filtro_servicio_liquidador']);
                }

                // Filtro de estado validación liquidador
                if (!empty($filtros['filtro_estado_validacion_liquidador'])) {
                    $query->where('l.estado_liquidador', $filtros['filtro_estado_validacion_liquidador']);
                }

                // Filtro de estado pago liquidador
                if (!empty($filtros['filtro_estado_pago_liquidador'])) {
                    $query->where('l.pago_liquidador', $filtros['filtro_estado_pago_liquidador']);
                }

                // Filtro de búsqueda (nombre, apellido, documento o teléfono del cliente)
                if (!empty($filtros['filtro_search'])) {
                    $filtro_search = $filtros['filtro_search'];
                    $query->where(function ($q) use ($filtro_search) {
                        $searchTerm = '%' . $filtro_search . '%';
                        $q->where('t2.nombre_cliente', 'LIKE', $searchTerm)
                            ->orWhere('t2.apellido_cliente', 'LIKE', $searchTerm)
                            ->orWhere('t2.doc_cliente', 'LIKE', $searchTerm)
                            ->orWhere('t2.telefono_cliente', 'LIKE', $searchTerm)
                            ->orWhere('t2.email_cliente', 'LIKE', $searchTerm)
                            ->orWhere('v.placa_vehiculo', 'like', '%' . $searchTerm . '%');
                    });
                }

                // Filtro por tipo de cita
                if (!empty($filtros['tipo_cita'])) {
                    $query->where('t6.tipo_servicio', $filtros['tipo_cita']);
                }

                // Aquí podrías agregar otros filtros (como responsable, origen, agente) si fuese necesario
                if (!empty($filtros['filtro_responsable'])) {
                    $query->where('t1.responsable_origen', $filtros['filtro_responsable']);
                }
                if (!empty($filtros['filtro_origen'])) {
                    $query->where('t1.origen', $filtros['filtro_origen']);
                }
                if (!empty($filtros['filtro_agente'])) {
                    $query->where('t1.id_agente_callcenter', $filtros['filtro_agente']);
                }

                if (!empty($filtros['filtro_tipo_paquete_cita'])) {
                    $query->whereIn('tb_paquete.tipo_paquete', $filtros['filtro_tipo_paquete_cita']);
                }

                // Ejecutar la consulta
                $records = $query->get();

                // Si ya no hay registros, se envía la respuesta 'completed' con la URL del archivo
                if (empty($records)) {
                    return response()->json([
                        'status' => 'completed',
                        'url' => url('data/export-citas-' . $dateNomArchivo . '.xlsx')
                    ]);
                }

                // --------------------------------------
                // 3) Escritura del archivo CSV
                // --------------------------------------
                $filePath = 'public_html/data/export-citas-' . $dateNomArchivo . '.xlsx';
                $fullPath = base_path($filePath);

                // Crear la carpeta 'public_html/data' si no existe
                if (!file_exists(base_path('public_html/data'))) {
                    mkdir(base_path('public_html/data'), 0777, true);
                }

                // TITULOS DEL EXCEL
                $headers = [
                    'ID Cita',
                    'Cliente',
                    'Estado',
                    'Empresa',
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
                    'Seguimiento cita',
                    'Placa Vehiculo',
                    'Tipo Vehiculo',
                    'Modelo Vehiculo',
                    'Origen',
                    'Url Variables',
                    'Tipo dispositivo'
                ];

                $rows_records = [];

                foreach ($records as $record) {
                    $data = [
                        $record->id_cita,
                        $record->nombre_cliente . ' ' . $record->apellido_cliente,
                        $record->estado_verificado_nombre,
                        $record->Nombre,
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
                        $record->comentarios,
                        $record->placa_vehiculo,
                        $record->tipo_vehiculo,
                        $record->modelo_vehiculo,
                        $record->origen,
                        $record->url_variables,
                        $record->tipo_dispositivo
                    ];
                    $rows_records[] = $data;
                }

                // CONVERSIÓN A COLECCIÓN
                $convertedRecords = [];
                foreach ($rows_records as $record) {
                    $data = (array) $record;
                    foreach ($data as $key => $value) {
                        $data[$key] = is_object($value) || is_array($value) ? json_encode($value) : $value;
                    }
                    $convertedRecords[] = $data;
                }

                $collection = collect($convertedRecords);

                if (!empty($collection)) {
                    $excelName = 'export-citas-' . $dateNomArchivo . '.xlsx';
                    $this->exportarExcel($excelName, $collection, $headers);

                    return response()->json([
                        'status' => 'completed',
                        'url' => url('data/export-citas-' . $dateNomArchivo . '.xlsx')
                    ]);
                }
            } catch (\Throwable $e) {
                Log::error($e->getMessage());
            }
            return response()->json($objLoad);
        }
    }

    public function citasconsultaDB()
    {
        return DB::table('tb_cita as t1')
            ->select([
                't1.*',
                't1.created_at as fecha_create',
                't2.nombre_cliente',
                't2.apellido_cliente',
                't2.doc_cliente',
                't2.tipo_doc_cliente',
                't2.telefono_cliente',
                't2.email_cliente',
                't2.desc_cliente',
                't3.id_estado as estado_actual_id',
                't3.nombre_estado as estado_actual_nombre',
                't3.color_estado as estado_actual_color',
                't4.id_estado as estado_verificado_id',
                't4.nombre_estado as estado_verificado_nombre',
                't4.color_estado as estado_verificado_color',
                't4.desc_estado as estado_verificado_desc',
                't5.nombre_sede',
                't5.direccion_sede',
                't5.tel_sede',
                't5.estado_sede',
                't6.tipo_servicio',
                'l.id_liquidador',
                'l.estado_liquidador',
                'l.comentario_liquidador',
                'l.pago_liquidador',
                'v.id_vehiculo',
                'v.placa_vehiculo',
                'v.tipo_vehiculo',
                'v.modelo_vehiculo',
                's.id_servicio_liquidador',
                's.nombre_servicio_liquidador',
                's.valor_servicio_liquidador',
                's.color_servicio_liquidador',
                'tb_empresa.Nombre',
                DB::raw("(SELECT GROUP_CONCAT(
                    CONCAT_WS(' - ', titulo_seguimiento, nota_seguimiento)
                    SEPARATOR '|'
                )
                FROM tb_seguimiento
                WHERE id_cita = t1.id_cita) AS comentarios")
            ])
            ->join('tb_cliente as t2', 't1.id_cliente', '=', 't2.id_cliente')
            ->join('tb_estado as t3', 't1.id_estado', '=', 't3.id_estado')
            ->join('tb_estado as t4', 't1.id_estado_verificado', '=', 't4.id_estado')
            ->join('tb_sede as t5', 't1.id_sede', '=', 't5.id_sede')
            ->join('tb_servicio as t6', 't5.id_servicio', '=', 't6.id_servicio')
            ->leftJoin('tb_liquidador as l', 't1.id_cita', '=', 'l.id_cita')
            ->leftJoin('tb_vehiculo as v', 't1.id_vehiculo', '=', 'v.id_vehiculo')
            ->leftJoin('tb_servicio_liquidador as s', 't1.id_servicio_liquidador', '=', 's.id_servicio_liquidador')
            ->leftJoin('tb_empresa_paquete', 't1.id_empresa_paquete', '=', 'tb_empresa_paquete.id_empresa_paquete')
            ->leftJoin('tb_empresa', 'tb_empresa_paquete.id_empresa', '=', 'tb_empresa.id_empresa')
            ->leftJoin('tb_paquete', 'tb_empresa_paquete.id_paquete', '=', 'tb_paquete.id_paquete')
            ->where('t1.id_cita', '>', 0)
            ->orderBy('t1.reserva_cita')
            ->orderBy('t1.rango_horario')
            ->orderBy('t1.id_sede');
    }

    public function exportarExcel($name, $data, $headers)
    {
        $export = new class($data, $headers) implements WithHeadings, FromCollection, ShouldAutoSize {
            private $data;
            private $headers;

            public function __construct(Collection $data, $headers)
            {
                $this->data = $data;
                $this->headers = $headers;
            }

            public function headings(): array
            {
                return $this->headers;
            }

            public function Collection(): Collection
            {
                return $this->data;
            }
        };
        $resultado = Excel::store($export, $name, 'public_html/data');
        return $resultado;
    }

    public function get_new_records(Request $request)
    {
        if ($request->ajax()) {
            try {
                $user = Auth::user();

                $last_execution = cache()->get('last_cron_execution', now());
                if (!$last_execution instanceof \Carbon\Carbon) {
                    $last_execution = \Carbon\Carbon::createFromTimestamp(strtotime($last_execution));
                }
                $next_execution = $last_execution->copy()->addMinutes(5);
                $time_remaining = max(0, $next_execution->diffInSeconds(now()));

                $nuevos_registros = 0;
                $sede = "todas las sedes";

                if ($user->can('global.Solo ver sede asignada.v')) {
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
        //Servicios Liquidador
        $sql = "SELECT * FROM tb_servicio_liquidador";
        $data['servicios_liquidador'] = DB::select($sql);
        //Origenes
        $sql = "SELECT DISTINCT origen FROM tb_cita WHERE origen IS NOT NULL ORDER BY origen ASC;";
        $data['origenes'] = DB::select($sql);
        $data['tipoSede'] = "";

        echo view('layouts.header', $data);
        echo view('layouts.nav', $data);
        echo view('layouts.navigation', $data);
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
        //Servicios Liquidador
        $sql = "SELECT * FROM tb_servicio_liquidador";
        $data['servicios_liquidador'] = DB::select($sql);
        //Origenes
        $sql = "SELECT DISTINCT origen FROM tb_cita WHERE origen IS NOT NULL ORDER BY origen ASC;";
        $data['origenes'] = DB::select($sql);
        $data['tipoSede'] = "CDA";
        echo view('layouts.header', $data);
        echo view('layouts.nav', $data);
        echo view('layouts.navigation', $data);
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
        if ($user->can('global.Solo ver sede asignada.v')) {
            $data['sedes'] = DB::table('tb_sede')
                ->where('id_sede', $user->id_sede)
                ->get();
        } else {
            $data['sedes'] = DB::table('tb_sede')->get();
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
        echo view('layouts.navigation', $data);
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
        if ($user->can('global.Solo ver sede asignada.v')) {
            $data['sedes'] = DB::table('tb_sede')
                ->where('id_sede', $user->id_sede)
                ->get();
        } else {
            $data['sedes'] = DB::table('tb_sede')->get();
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
        echo view('layouts.navigation', $data);
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
        if ($user->can('global.Solo ver sede asignada.v')) {
            $data['sedes'] = DB::table('tb_sede')
                ->where('id_sede', $user->id_sede)
                ->get();
        } else {
            $data['sedes'] = DB::table('tb_sede')->get();
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
        echo view('layouts.navigation', $data);
        echo view('dashboard.liquidador.index', $data);
        echo view('layouts.footer', $data);
    }

    //Obtener las citas para la cita liquidador de la base de datos
    public function get_citas_liquidador(Request $request)
    {
        if ($request->ajax()) {
            $objLoad = ['validate' => false];

            try {
                $user = Auth::user();

                // Parámetros de DataTables y filtros
                $length      = $request->input('length', 10);
                $start       = $request->input('start', 0);
                $draw        = $request->input('draw');
                $tipo_cita   = $request->input('tipo_cita');

                $filtro_dia                  = $request->input('filtro_dia');
                $filtro_dia_end              = $request->input('filtro_dia_end');
                $filtro_sede                 = $request->input('filtro_sede');
                $filtro_search               = $request->input('filtro_search');
                $filtro_servicio_liquidador  = $request->input('filtro_servicio_liquidador');
                $filtro_estado_validacion_liquidador = $request->input('filtro_estado_validacion_liquidador');
                $filtro_estado_pago_liquidador      = $request->input('filtro_estado_pago_liquidador');
                $filtro_tipo_paquete_cita_liquidador = $request->input('filtro_tipo_paquete_cita_liquidador');

                // Ordenamiento: definición de columnas de referencia
                $order_column_index = $request->input('order.0.column', 0);
                $order_direction = strtoupper($request->input('order.0.dir', 'asc'));
                $columnsConsulta = [
                    0 => null, // columna para checkbox, no ordenable
                    1 => 't2.nombre_cliente',
                    2 => 't5.nombre_sede',
                    3 => 't1.reserva_cita',
                    4 => 't1.created_at',
                    5 => 'l.estado_liquidador',
                    6 => null,
                    7 => 's.valor_servicio_liquidador',
                    8 => 'l.pago_liquidador'
                ];
                $order_column = (!empty($columnsConsulta[$order_column_index]))
                    ? $columnsConsulta[$order_column_index]
                    : 't1.reserva_cita';

                // Fechas: si no se definen, se usan primer y último día del mes actual
                $startOfMonth = date('Y-m-01');
                $endOfMonth   = date('Y-m-t');
                $fecha_inicio = (!empty($filtro_dia) && !empty($filtro_dia_end))
                    ? $filtro_dia
                    : $startOfMonth;
                $fecha_fin = (!empty($filtro_dia) && !empty($filtro_dia_end))
                    ? $filtro_dia_end
                    : $endOfMonth;

                // --- Construir la consulta base ---
                $baseQuery = DB::table('tb_cita as t1')
                    ->join('tb_cliente as t2', 't1.id_cliente', '=', 't2.id_cliente')
                    ->join('tb_estado as t3', 't1.id_estado', '=', 't3.id_estado')
                    ->join('tb_estado as t4', 't1.id_estado_verificado', '=', 't4.id_estado')
                    ->join('tb_sede as t5', 't1.id_sede', '=', 't5.id_sede')
                    ->join('tb_servicio as t6', 't5.id_servicio', '=', 't6.id_servicio')
                    ->leftJoin('tb_liquidador as l', 't1.id_cita', '=', 'l.id_cita')
                    ->leftJoin('tb_vehiculo as v', 't1.id_vehiculo', '=', 'v.id_vehiculo')
                    ->leftJoin('tb_servicio_liquidador as s', 't1.id_servicio_liquidador', '=', 's.id_servicio_liquidador')
                    ->leftJoin('tb_empresa_paquete', 't1.id_empresa_paquete', '=', 'tb_empresa_paquete.id_empresa_paquete')
                    ->leftJoin('tb_paquete', 'tb_empresa_paquete.id_paquete', '=', 'tb_paquete.id_paquete')
                    ->select([
                        't1.*',
                        DB::raw('t1.created_at as fecha_create'),
                        't2.nombre_cliente',
                        't2.apellido_cliente',
                        't2.doc_cliente',
                        't2.tipo_doc_cliente',
                        't2.telefono_cliente',
                        't3.id_estado as estado_actual_id',
                        't3.nombre_estado as estado_actual_nombre',
                        't3.color_estado as estado_actual_color',
                        't4.id_estado as estado_verificado_id',
                        't4.nombre_estado as estado_verificado_nombre',
                        't4.color_estado as estado_verificado_color',
                        't5.nombre_sede',
                        't5.id_servicio',
                        't6.tipo_servicio',
                        'l.id_liquidador',
                        'l.estado_liquidador',
                        'l.comentario_liquidador',
                        'l.pago_liquidador',
                        'v.id_vehiculo',
                        'v.placa_vehiculo',
                        'v.tipo_vehiculo',
                        'v.modelo_vehiculo',
                        's.id_servicio_liquidador',
                        's.nombre_servicio_liquidador',
                        's.valor_servicio_liquidador',
                        's.color_servicio_liquidador',
                        'tb_paquete.tipo_paquete'
                    ])
                    ->where('t1.id_cita', '>', 0)
                    ->where('t4.nombre_estado', 'Asistió')
                    ->whereBetween('t1.reserva_cita', [$fecha_inicio, $fecha_fin]);

                // --- Filtros por sede y empresa ---
                if ($user->can('global.Solo ver sede asignada.v')) {
                    $baseQuery->where('t5.id_sede', $user->id_sede);
                }
                if ($user->can('global.Pertenece a empresa aliada.v')) {
                    $sede_usuario = DB::table('tb_sede')
                        ->where('id_sede', $user->id_sede)
                        ->first();
                    if ($sede_usuario && isset($sede_usuario->id_empresa)) {
                        $baseQuery->where('t5.id_empresa', $sede_usuario->id_empresa);
                    }
                }

                // --- Filtros personalizados adicionales ---
                if (!empty($filtro_sede)) {
                    $baseQuery->where('t5.id_sede', $filtro_sede);
                }
                if (!empty($filtro_servicio_liquidador)) {
                    $baseQuery->where('t1.id_servicio_liquidador', $filtro_servicio_liquidador);
                }
                if (!empty($filtro_estado_validacion_liquidador)) {
                    $baseQuery->where('l.estado_liquidador', $filtro_estado_validacion_liquidador);
                }
                if (!empty($filtro_estado_pago_liquidador)) {
                    $baseQuery->where('l.pago_liquidador', $filtro_estado_pago_liquidador);
                }
                if (!empty($filtro_tipo_paquete_cita_liquidador)) {
                    if (is_array($filtro_tipo_paquete_cita_liquidador)) {
                        $baseQuery->whereIn('tb_paquete.tipo_paquete', $filtro_tipo_paquete_cita_liquidador);
                    } else {
                        $baseQuery->where('tb_paquete.tipo_paquete', $filtro_tipo_paquete_cita_liquidador);
                    }
                }
                if (!empty($filtro_search)) {
                    $palabras = preg_split('/\\s+/', trim($filtro_search));
                    $baseQuery->where(function ($q) use ($palabras) {
                        foreach ($palabras as $palabra) {
                            if (!empty($palabra)) {
                                $q->where(function ($sub) use ($palabra) {
                                    $sub->where('t2.nombre_cliente', 'like', "%{$palabra}%")
                                        ->orWhere('t2.apellido_cliente', 'like', "%{$palabra}%")
                                        ->orWhere('t2.doc_cliente', 'like', "%{$palabra}%")
                                        ->orWhere('t2.telefono_cliente', 'like', "%{$palabra}%")
                                        ->orWhere('t2.email_cliente', 'like', "%{$palabra}%")
                                        ->orWhere('v.placa_vehiculo', 'like', "%{$palabra}%");
                                });
                            }
                        }
                    });
                }
                if ($tipo_cita) {
                    $baseQuery->where('t6.tipo_servicio', $tipo_cita);
                }

                // --- Obtener total filtrado ---
                $recordsTotal = $baseQuery->count();

                // Clonar la consulta base para obtener la data paginada
                $pagedQuery = clone $baseQuery;
                $dataResults = $pagedQuery
                    ->orderByRaw("$order_column, t1.rango_horario, t1.id_sede $order_direction")
                    ->offset($start)
                    ->limit($length)
                    ->get();


                // --- Consulta de totales/estadísticas ---
                // Recontruimos la query para totales sin los SELECTs previos que causan conflicto con los agregados.
                $totalsQuery = DB::table('tb_cita as t1')
                    ->join('tb_sede as t5', 't1.id_sede', '=', 't5.id_sede')
                    ->join('tb_estado as t4', 't1.id_estado_verificado', '=', 't4.id_estado')
                    ->leftJoin('tb_liquidador as l', 't1.id_cita', '=', 'l.id_cita')
                    ->leftJoin('tb_servicio_liquidador as s', 't1.id_servicio_liquidador', '=', 's.id_servicio_liquidador')
                    ->where('t1.id_cita', '>', 0)
                    ->where('t4.nombre_estado', 'Asistió')
                    ->whereBetween('t1.reserva_cita', [$fecha_inicio, $fecha_fin]);

                // Aplicar los mismos filtros de sede y empresa
                if ($user->can('global.Solo ver sede asignada.v')) {
                    $totalsQuery->where('t1.id_sede', $user->id_sede);
                }
                if ($user->can('global.Pertenece a empresa aliada.v')) {
                    if (isset($sede_usuario) && isset($sede_usuario->id_empresa)) {
                        $totalsQuery->join('tb_sede as ts', 't1.id_sede', '=', 'ts.id_sede')
                            ->where('ts.id_empresa', $sede_usuario->id_empresa);
                    }
                }
                if (!empty($filtro_servicio_liquidador)) {
                    $totalsQuery->where('t1.id_servicio_liquidador', $filtro_servicio_liquidador);
                }
                if (!empty($filtro_estado_validacion_liquidador)) {
                    $totalsQuery->where('l.estado_liquidador', $filtro_estado_validacion_liquidador);
                }
                if (!empty($filtro_estado_pago_liquidador)) {
                    $totalsQuery->where('l.pago_liquidador', $filtro_estado_pago_liquidador);
                }
                if ($tipo_cita) {
                    $totalsQuery->join('tb_servicio as t6', 't5.id_servicio', '=', 't6.id_servicio')
                        ->where('t6.tipo_servicio', $tipo_cita);
                }

                $totals = $totalsQuery->selectRaw("
                COALESCE(SUM(s.valor_servicio_liquidador), 0) as total_valor_a_liquidar,
                COALESCE(SUM(CASE WHEN l.estado_liquidador = 'confirmado' THEN s.valor_servicio_liquidador ELSE 0 END), 0) as total_valor_liquidado,
                SUM(CASE WHEN l.estado_liquidador = 'confirmado' THEN 1 ELSE 0 END) as total_confirmados,
                SUM(CASE WHEN l.estado_liquidador = 'errado' THEN 1 ELSE 0 END) as total_errados,
                SUM(CASE WHEN l.estado_liquidador = 'pendiente' THEN 1 ELSE 0 END) as total_pendientes,
                SUM(CASE WHEN l.estado_liquidador = 'en validación' THEN 1 ELSE 0 END) as total_validacion,
                SUM(CASE WHEN l.pago_liquidador = 'pendiente' THEN 1 ELSE 0 END) as total_pago_pendiente,
                SUM(CASE WHEN l.pago_liquidador = 'pagado' THEN 1 ELSE 0 END) as total_pago_pagado
            ")->first();

                $objLoad = [
                    "draw" => intval($draw),
                    "recordsTotal" => $recordsTotal,
                    "recordsFiltered" => $recordsTotal,
                    "data" => $dataResults,
                    "validate" => true,
                    "extra" => [
                        "fecha_inicio" => $fecha_inicio,
                        "fecha_fin" => $fecha_fin,
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
                $id_servicio_liquidador = $request->input('id_servicio_liquidador');
                $id_cita = $request->input('id_cita');

                $updated = DB::table('tb_cita')
                    ->where('id_cita', $id_cita)
                    ->update([
                        'id_servicio_liquidador' => $id_servicio_liquidador,
                        'updated_at' => Carbon::now()
                    ]);

                if ($updated) {

                    $nombre_servicio = DB::table('tb_servicio_liquidador')
                        ->where('id_servicio_liquidador', $id_servicio_liquidador)
                        ->value('nombre_servicio_liquidador');

                    // Insertar la nueva anotación en la base de datos
                    DB::table('tb_seguimiento')->insert([
                        'titulo_seguimiento' => 'El servicio de la cita ha cambiado',
                        'nota_seguimiento'   => 'El servicio de la cita ha cambiado a ' . $nombre_servicio,
                        'id_cita'            => $id_cita,
                        'id_user'            => Auth::user()->id,
                        'created_at'         => Carbon::now(),
                        'updated_at'         => Carbon::now()
                    ]);

                    $objLoad = [
                        'validate' => true,
                        'text' => 'Servicio actualizado correctamente'
                    ];
                } else {
                    $objLoad['text'] = 'No se realizaron cambios en el servicio.';
                    Log::warning("No se actualizó la cita ID $id_cita con el servicio liquidador ID $id_servicio_liquidador");
                }
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
                    <button type="submit" class="btn btn-success" id="btn-save-seguimiento">Guardar Seguimiento</button>
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

                $now = Carbon::now();

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

    public function get_informacion_simit(Request $request) {
        $response = [
            'Status' => 500,
            'Message' => "Ocurrió un error al obtener la información del Simit.",
            'Success' => false,
            'Data' => null
        ];
        $id_cita = $request->input('id_cita');

        if (empty($id_cita)) {
            return response()->json($response);
        }

        try {
            $cita = DB::table('tb_cita')
                ->where('id_cita', $id_cita)
                ->first();
            $metadata = json_decode($cita->metadata_simit, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $response = [
                    'Status' => 500,
                    'Message' => "Ocurrió un error al convertir la metadata de la información del Simit.",
                    'Success' => false,
                    'Data' => null
                ];
                return response()->json($response);
            }

            $verificacionSimit = $this->scrapingService->VerificarInformacion($id_cita, $metadata);

            $html = '<div class="m-auto">';
            $html .= '<div class="col-md-12">';
            $html .= '<div class="alert alert-warning" role="alert"><i class="ti ti-info-circle"></i>
            ¡Atención! Tenga en cuenta que estos datos son solo una aproximación de resultados hechos por el sistema.
            Deberá de verificar que la información sea correcta en la imagen que se encuentra en la sección inferior. <i class="ti ti-arrow-big-down"></i>
            </div>';
            $html .= '<h5 class="text-center">Fecha de Consulta: ' . $metadata['fechaCaptura'] . '</h5>';
            foreach ($verificacionSimit as $registro) {
                $columna_tipo = explode(" ", $registro[0]);
                $columna_infraccion = explode(" ", $registro[4]);
                $numero_comparendo = $columna_tipo[0] ?? 'No se encontró el dato';
                $tipo_infraccion = $columna_tipo[1] ?? 'No se encontró el dato';
                $fecha_imposicion = $columna_tipo[4] ?? 'No se encontró la fecha';
                $fecha_notificacion = $registro[1] ?? 'No se encontró la fecha';
                $placa_vehiculo = $registro[2] ?? 'No se encontró la placa';
                $secretaria = $registro[3] ?? 'No se encontró la secretaría';
                $infraccion = $columna_infraccion[0] ?? 'No se encontró la infracción';

                $html .= '<div class="col-12">';
                $html .= '<div class="row">';
                $html .= '<div class="card bg-info mt-3 mb-3 col-12 col-md-4">';
                $html .= '<div class="row">';
                $html .= '<div class="col-md-5">';
                $html .= '<i class="card-img-top ti ti-checkup-list display-1" style="color:white;"></i>';
                $html .= '<h5 style="color:white;"><span style="color:#d3d93b;">Número: </span>' . $numero_comparendo . '</h5>';
                $html .= '</div>';
                $html .= '<div class="col-md-7">';
                $html .= '<div class="card-body text-start">';
                $html .= '<h5 class="card-title" style="color:white;"><span style="color:#d3d93b;">Tipo: </span>' . $tipo_infraccion . '</h5>';
                $html .= '<p class="card-text" style="color:white;"><span style="color:#d3d93b;">Fecha de imposición: </span>' . $fecha_imposicion . '</p>';
                $html .= '<p class="card-text" style="color:white;"><span style="color:#d3d93b;">Fecha de notificación: </span>' . $fecha_notificacion . '</p>';
                $html .= '<p class="card-text" style="color:white;"><span style="color:#d3d93b;">Placa del vehículo: </span>' . $placa_vehiculo . '</p>';
                $html .= '<p class="card-text" style="color:white;"><span style="color:#d3d93b;">Secretaría: </span>' . $secretaria . '</p>';
                $html .= '<p class="card-text" style="color:white;"><span style="color:#d3d93b;">Infracción: </span>' . $infraccion . '</p>';
                $html .= '</div>';
                $html .= '</div>';
                $html .= '</div>';
                $html .= '</div>';
                $html .= '</div>';
                $html .= '</div>';
            }
            $html .= '</div>';
            $html .= '<div class="col-md-12 ">';
            $html .= '<h3 class="text-center">Datos del SIMIT:</h3>';

            if ($cita->origen_scraping == 'Node') {
                $html .= '<iframe  style="width: 100%; height: 50vh; border: none;" src="' . env('SCRAPING_RUTA_BASE') . $metadata['urlHtml'] . '"></iframe>';
            } else if ($cita->origen_scraping == 'N8N') {
                $html .= '<iframe  style="width: 100%; height: 50vh; border: none;" src="' . env('N8N_RUTA_BASE') . $metadata['urlHtml'] . '"></iframe>';
            }
            
            $html .= '</div>';
            $html .= '</div>';
            
            $response = [
                'Status' => 200,
                'Message' => "Se obtuvo la información del SIMIT correctamente.",
                'Success' => true,
                'Data' => $html
            ];
        } catch (\Throwable $e) {
            Log::error("Ocurrió un error al intentar obtener la información del Simit:" . $e);
        }
        return response()->json($response);
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
        echo view('layouts.navigation', $data);
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
                        'updated_at' => carbon::now() // Actualizar la fecha de modificación
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

                $updated = DB::table('tb_liquidador')
                    ->where('id_liquidador', $id_liquidador)
                    ->update([
                        'estado_liquidador' => $estado_liquidador,
                        'updated_at' => Carbon::now()
                    ]);

                if ($updated) {
                    $objLoad = [
                        'validate' => true,
                        'text' => 'Estado servicio liquidador actualizado correctamente',
                        'id' => $id_liquidador
                    ];
                } else {
                    $objLoad['text'] = 'No se realizaron cambios en el estado.';
                    log::error('No se realizaron cambios en el estado.');
                    log::error($updated);
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

                $updated = DB::table('tb_liquidador')
                    ->where('id_cita', $id_cita)
                    ->where('id_liquidador', $id_liquidador)
                    ->update([
                        'comentario_liquidador' => $comentario,
                        'updated_at' => Carbon::now()
                    ]);

                if ($updated) {
                    DB::table('tb_cita')
                        ->where('id_cita', $id_cita)
                        ->update(['updated_at' => Carbon::now()]);

                    $objLoad = [
                        'validate' => true,
                        'text' => 'Comentario actualizado correctamente'
                    ];
                } else {
                    Log::warning("No se actualizó el comentario. ID cita: $id_cita, ID liquidador: $id_liquidador");
                }
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
                            'pago_liquidador' => $pago_liquidador,
                            'updated_at' => Carbon::now()
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
                'text' => 'Error al descargar el archivo liquidador'
            ];

            try {
                $user = Auth::user();
                $filtros = $request->input();

                $fecha_actual = date('Y-m-d');
                // Rango por defecto: primer y último día del mes actual
                $startOfMonth = date('Y-m-01');
                $endOfMonth   = date('Y-m-t');
                // Construcción de la consulta

                //Construcción de la consulta
                $query = $this->liquidadorconsultaBD();

                // Filtro si el rol es 'gestorsede'
                if ($user->can('global.Solo ver sede asignada.v')) {
                    $query->where('t5.id_sede', $user->id_sede);
                }

                // Filtro de fechas
                if (!empty($filtros['filtro_dia']) && !empty($filtros['filtro_dia_end'])) {
                    $query->whereBetween('t1.reserva_cita', [$filtros['filtro_dia'], $filtros['filtro_dia_end']]);
                    $dateNomArchivo = $filtros['filtro_dia'] . "-" . $filtros['filtro_dia_end'];
                } else {
                    // Por defecto, primer y último día del mes
                    $query->whereBetween('t1.reserva_cita', [$startOfMonth, $endOfMonth]);
                    $dateNomArchivo = $startOfMonth . "-" . $endOfMonth;
                }

                // Filtro de sede
                if (!empty($filtros['filtro_sede'])) {
                    $query->where('t5.id_sede', $filtros['filtro_sede']);
                }

                // Filtro de servicio liquidador
                if (!empty($filtros['filtro_servicio_liquidador'])) {
                    $query->where('t1.id_servicio_liquidador', $filtros['filtro_servicio_liquidador']);
                }

                // Filtro de estado validacion liquidador
                if (!empty($filtros['filtro_estado_validacion_liquidador'])) {
                    $query->where('l.estado_liquidador', $filtros['filtro_estado_validacion_liquidador']);
                }

                // Filtro de estado pago liquidador
                if (!empty($filtros['filtro_estado_pago_liquidador'])) {
                    $query->where('l.pago_liquidador', $filtros['filtro_estado_pago_liquidador']);
                }

                // Filtro de búsqueda
                if (!empty($filtros['filtro_search'])) {
                    $filtro_search = $filtros['filtro_search'];
                    $query->where(function ($q) use ($filtro_search) {
                        $searchTerm = '%' . $filtro_search . '%';
                        $q->where('t2.nombre_cliente', 'LIKE', $searchTerm)
                            ->orWhere('t2.apellido_cliente', 'LIKE', $searchTerm)
                            ->orWhere('t2.doc_cliente', 'LIKE', $searchTerm)
                            ->orWhere('t2.telefono_cliente', 'LIKE', $searchTerm)
                            ->orWhere('t2.email_cliente', 'LIKE', $searchTerm)
                            ->orWhere('v.placa_vehiculo', 'LIKE', $searchTerm);
                    });
                }

                if ($filtros['tipo_cita']) {
                    $query->where('t6.tipo_servicio', $filtros['tipo_cita']);
                }

                //Ejecuta la consulta
                $records =  $query->get();

                if (empty($records)) {
                    return response()->json([
                        'status' => 'completed',
                        'url' => url('data/export-liquidador-' . $dateNomArchivo . '.xlsx')
                    ]);
                }

                // Ruta y apertura del archivo CSV en la carpeta public/data
                $filePath = 'public_html/data/export-liquidador-' . $dateNomArchivo . '.xlsx';
                $fullPath = base_path($filePath);

                // Crear la carpeta 'data' si no existe
                if (!file_exists(base_path('public_html/data'))) {
                    mkdir(base_path('public_html/data'), 0777, true);
                }

                $headers = [
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
                    'Origen',
                    'Url Variables',
                    'Tipo dispositivo'
                ];

                foreach ($records as $record) {
                    $data =  [
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
                        $record->origen,
                        $record->url_variables,
                        $record->tipo_dispositivo,
                    ];
                    $rows_records[] = $data;
                }

                //CONVERSIÓN A COLECCIÓN
                $convertedRecords = [];
                foreach ($rows_records as $record) {
                    $data = (array) $record;
                    foreach ($data as $key => $value) {
                        $data[$key] = is_object($value) || is_array($value) ? json_encode($value) : $value;
                    }
                    $convertedRecords[] = $data;
                }

                $collection = collect($convertedRecords);

                if (!empty($collection)) {
                    $excelName = 'export-liquidador-' . $dateNomArchivo . '.xlsx';
                    $this->exportarExcel($excelName, $collection, $headers);

                    return response()->json([
                        'status' => 'completed',
                        'url' => url('data/export-liquidador-' . $dateNomArchivo . '.xlsx')
                    ]);
                }
            } catch (\Throwable $e) {
                Log::error($e->getMessage());
            }
            return response()->json($objLoad);
        }
    }

    public function liquidadorconsultaBD()
    {
        return  DB::table('tb_cita as t1')
            ->select([
                't1.*',
                't1.created_at as fecha_create',
                't2.nombre_cliente',
                't2.apellido_cliente',
                't2.doc_cliente',
                't2.tipo_doc_cliente',
                't2.telefono_cliente',
                't2.email_cliente',
                't2.desc_cliente',
                't3.id_estado as estado_actual_id',
                't3.nombre_estado as estado_actual_nombre',
                't3.color_estado as estado_actual_color',
                't4.id_estado as estado_verificado_id',
                't4.nombre_estado as estado_verificado_nombre',
                't4.color_estado as estado_verificado_color',
                't4.desc_estado as estado_verificado_desc',
                't5.nombre_sede',
                't5.direccion_sede',
                't5.tel_sede',
                't5.estado_sede',
                't6.tipo_servicio',
                'l.id_liquidador',
                'l.estado_liquidador',
                'l.comentario_liquidador',
                'l.pago_liquidador',
                'v.id_vehiculo',
                'v.placa_vehiculo',
                'v.tipo_vehiculo',
                'v.modelo_vehiculo',
                's.id_servicio_liquidador',
                's.nombre_servicio_liquidador',
                's.valor_servicio_liquidador',
                's.color_servicio_liquidador'
            ])
            ->join('tb_cliente as t2', 't1.id_cliente', '=', 't2.id_cliente')
            ->join('tb_estado as t3', 't1.id_estado', '=', 't3.id_estado')
            ->join('tb_estado as t4', 't1.id_estado_verificado', '=', 't4.id_estado')
            ->join('tb_sede as t5', 't1.id_sede', '=', 't5.id_sede')
            ->join('tb_servicio as t6', 't5.id_servicio', '=', 't6.id_servicio')
            ->leftJoin('tb_liquidador as l', 't1.id_cita', '=', 'l.id_cita')
            ->leftJoin('tb_vehiculo as v', 't1.id_vehiculo', '=', 'v.id_vehiculo')
            ->leftJoin('tb_servicio_liquidador as s', 't1.id_servicio_liquidador', '=', 's.id_servicio_liquidador')
            ->where('t1.id_cita', '>', 0)
            ->where('t4.nombre_estado', 'Asistió')
            ->orderBy('t1.reserva_cita')
            ->orderBy('t1.rango_horario')
            ->orderBy('t1.id_sede');
    }

    public function get_estado_metodo_scraping()
    {
        $response = [
            'Status' => 500,
            'Message' => "Ocurrió un error al consultar el estado del método de Scraping.",
            'Success' => false,
            'Data' => null
        ];

        try {
            $metodo_actual = DB::table('tb_config')
                ->where('config_key', 'method_scraping')
                ->value('config_value');

            $response = [
                'Status' => 200,
                'Message' => "Se cambió el método de Scraping con exito.",
                'Success' => true,
                'Data' => $metodo_actual
            ];
        } catch (\Throwable $e) {
            Log::error("Ocurrió un error al consultar el estado del método de Scraping.");
        }
        return response()->json($response);
    }

    public function metodo_scraping(Request $request)
    {
        $response = [
            'Status' => 500,
            'Message' => "Ocurrió un error al cambiar el método de Scraping.",
            'Success' => false,
            'Data' => null
        ];

        $metodoSeleccionado = $request->input('metodoSeleccionado');

        try {
            DB::table('tb_config')
                ->where('config_key', 'method_scraping')
                ->update([
                    'config_value' => $metodoSeleccionado,
                    'updated_at' => Carbon::now()
                ]);

            $response = [
                'Status' => 200,
                'Message' => "Se cambió el método de Scraping con exito.",
                'Success' => true,
                'Data' => null
            ];
        } catch (\Throwable $e) {
            Log::error("Ocurrió un error al intentar cambiar el método de Scraping.");
        }
        return response()->json($response);
    }
}

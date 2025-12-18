<?php

namespace App\Http\Controllers;

use App\Services\SendPulseService;
use App\Services\CrmService;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Helpers\AdminHelper;
use App\Helpers\PaqueteHelper;
use App\Helpers\UtilsHelper;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;
use App\Jobs\WhatsappJob;
use App\Jobs\ScrapingSimitJob;

class LoadController extends Controller
{
    protected $sendPulse;
    protected $crmService;
    protected $utilsHelper;

    public function __construct(SendPulseService $sendPulse, CrmService $crmService, UtilsHelper $utilsHelper)
    {
        $this->sendPulse = $sendPulse;
        $this->crmService = $crmService;
        $this->utilsHelper = $utilsHelper;
    }

    public function index(Request $request)
    {

        // Capturar TODOS los parámetros de la URL
        $urlParams = $request->query->all();

        // Filtrar y guardar solo utm_source en sesión si existe
        if (isset($urlParams['utm_source'])) {
            $request->session()->put('utm_source', $urlParams['utm_source']);
        }

        
        $data = [];
        echo view('load/index', $data);
    }
    public function createcita($id_sede, Request $request)
    {
        // Capturar TODOS los parámetros de la URL
        $urlParams = $request->query->all();

        // Filtrar y guardar solo utm_source en sesión si existe
        if (isset($urlParams['utm_source'])) {
            $request->session()->put('utm_source', $urlParams['utm_source']);
        }

        $sql = "SELECT * FROM tb_servicio_liquidador";
        $servicios_liquidador = DB::select($sql);

        // ===== MODULO INDEPENDIENTE CIUDAD =====

            // Obtener sede
            $sede_db = DB::table('tb_sede')
                ->where('id_sede', $id_sede)
                ->first();

            // Obtener id_ciudad
            $id_ciudad = $sede_db->id_ciudad ?? null;

            // Obtener nombre de la ciudad
            $nombre_ciudad = null;
            if ($id_ciudad) {
                $nombre_ciudad = DB::table('tb_ciudad')
                    ->where('id_ciudad', $id_ciudad)
                    ->value('nombre');
            }

        $data = [
            'id_sede' => $id_sede,
            'sede' => AdminHelper::get_sede_by_id($id_sede),
            'urlParams' => $urlParams,
            'servicios_liquidador' => $servicios_liquidador,

            // 👉 NUEVO (ciudad)
            'id_ciudad' => $id_ciudad,
            'nombre_ciudad' => $nombre_ciudad
        ];

        echo view('load/createcita', $data);
    }

    public function savecita(Request $request)
    {
        if ($request->ajax()) {
            $objLoad = [
                'validate' => false,
                'text' => 'Error al agendar la cita',
            ];
            try {
                $id_sede = $request->request->get('id_sede');
                $nombre_cliente = $request->request->get('nombre_cliente');
                $apellido_cliente = $request->request->get('apellido_cliente');
                $email_cliente = $request->request->get('email_cliente');
                $telefono_cliente = $request->request->get('telefono_cliente');
                $tipo_doc_cliente = $request->request->get('tipo_doc_cliente');
                $doc_cliente = $request->request->get('doc_cliente');
                $id_sede_horario = $request->request->get('id_sede_horario');
                $tipo_vehiculo = $request->request->get('tipo_vehiculo');
                $placa_vehiculo = strtoupper($request->request->get('placa_vehiculo'));
                $modelo_vehiculo = $request->request->get('modelo_vehiculo');
                $reserva_cita = $request->request->get('reserva_cita');
                $servicio_liquidador = $request->request->get('servicio_liquidador');
                $codigo_comparendo = $request->request->get('codigo_comparendo');
                $empresa_paquete = PaqueteHelper::obtenerPaqueteActivoEmpresa($id_sede);
                // log::info($codigo_comparendo);

                // Obtener nombre de la sede
                $sede = DB::table('tb_sede')->where('id_sede', $id_sede)->first();
                $id_ciudad = $sede->id_ciudad ?? null;

                $nombre_sede = $sede ? $sede->nombre_sede : 'Sede no encontrada';
                $direccion_sede = $sede ? $sede->direccion_sede : 'Dirección no encontrada';

                // Asignar valores adicionales
                $creado_por = 'Cliente'; // Identifica que la cita fue creada por el cliente
                // Obtener la variable "utm_source" desde la URL
                $origen = $request->input('utm_source', 'Desconocido');
                if ($origen == 'Desconocido' || $origen == '' || $origen == null) {
                    $responsable_origen = 'Desconocido';
                } elseif ($origen == 'QR' || $origen == 'qr' || $origen == 'Qr' || $origen == 'QRCode' || $origen == 'qrcode') {
                    $responsable_origen = 'Sede';
                } else {
                    $responsable_origen = 'Curso Comparendo';
                }
                $tipo_dispositivo = $request->header('User-Agent'); // Detectar el dispositivo desde el User-Agent

                // Procesar el User-Agent para determinar el tipo de dispositivo
                if (preg_match('/mobile/i', $tipo_dispositivo)) {
                    $tipo_dispositivo = 'Mobile';
                } elseif (preg_match('/tablet/i', $tipo_dispositivo)) {
                    $tipo_dispositivo = 'Tablet';
                } else {
                    $tipo_dispositivo = 'Desktop';
                }

                // Obtener el JSON de variables de URL
                $urlVariables = $request->request->get('url_variables');

                // Convertir a array y validar
                $urlVariablesArray = json_decode($urlVariables, true) ?: [];

                //Creamos el formato de la fecha de reserva
                $date = \DateTime::createFromFormat('d/m/Y', $reserva_cita);
                if ($date) {
                    $reserva_cita = $date->format('Y-m-d');
                } else {
                    throw new \Exception("El formato de la fecha es incorrecto");
                }
                //Verificamos disponibilidad de la cita
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

                //Verificamos si el cliente existe por doc_cliente
                $cliente = AdminHelper::get_cliente_by_doc($doc_cliente);
                if (!$cliente) {
                    //Si no existe el cliente lo creamos
                    $sql = "INSERT INTO tb_cliente (nombre_cliente, apellido_cliente, email_cliente, tipo_doc_cliente, doc_cliente, telefono_cliente, desc_cliente) VALUES ('$nombre_cliente', '$apellido_cliente', '$email_cliente', '$tipo_doc_cliente', '$doc_cliente', '$telefono_cliente', 'Creado por el cliente')";
                    DB::insert($sql);
                    $id_cliente = DB::getPdo()->lastInsertId();
                } else {
                    $id_cliente = $cliente['id_cliente'];
                }

                //Verificamos si el vehiculo existe por placa
                if ($placa_vehiculo == '' || $placa_vehiculo == null) {
                    $id_vehiculo = "NULL";
                } else {
                    $vehiculo = AdminHelper::get_vehiculo_by_placa($placa_vehiculo);
                    if (!$vehiculo) {
                        //Si no existe el vehiculo lo creamos
                        $sql = "INSERT INTO tb_vehiculo (id_cliente, placa_vehiculo, tipo_vehiculo, modelo_vehiculo) VALUES ( $id_cliente, '$placa_vehiculo', '$tipo_vehiculo', '$modelo_vehiculo')";
                        DB::insert($sql);
                        $id_vehiculo = DB::getPdo()->lastInsertId();
                    } else {
                        // log::info($vehiculo[0]['id_vehiculo']);
                        $id_vehiculo = $vehiculo[0]['id_vehiculo'];
                    }
                }

                // VERIFICA SI TIENE CITAS AGENDADAS
                $citas_agendadas = $this->getCitasAgendadas(new \Illuminate\Http\Request(['cc' => $doc_cliente]));
                $save = null;
                $insertData = [
                    'id_cliente' => $id_cliente,
                    'id_sede' => $id_sede,
                    'id_servicio_liquidador' => $servicio_liquidador,
                    'codigos_comparendo' => $codigo_comparendo, // Este puede ser un string JSON
                    'id_vehiculo' => $id_vehiculo,
                    'reserva_cita' => $reserva_cita,
                    'rango_horario' => $rango_horario,
                    'desc_cita' => 'Creada por el cliente',
                    'responsable_origen' => $responsable_origen,
                    'creado_por' => $creado_por,
                    'origen' => $origen,
                    'url_variables' => json_encode($urlVariablesArray),
                    'tipo_dispositivo' => $tipo_dispositivo,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                    'id_empresa_paquete' => $empresa_paquete
                ];

                if ($citas_agendadas) {
                    $query_estado_duplicado = DB::table('tb_estado')->select(['tb_estado.*'])->where('tb_estado.nombre_estado', 'Duplicado');
                    $estado_duplicado = $query_estado_duplicado->first();
                    $response_citas_agendadas_historico = $this->getCitasAgendadasHistorico(new \Illuminate\Http\Request(['cc' => $doc_cliente]));
                    $data_citas_agendadas_historico = $response_citas_agendadas_historico->getData(true);

                    // SE AGREGAN LOS ESTADOS
                    $insertData['id_estado'] = $estado_duplicado->id_estado;
                    $insertData['id_estado_verificado'] = $estado_duplicado->id_estado;
                    $callcenter_habilitado = false;
                    $agenteValue = null;

                    // SE VERIFICA SI EL AGENTE CALL-CENTER ESTÁ HABILITADO
                    try {
                        $callcenter_habilitado = $this->verificarEstadoCallCenter($data_citas_agendadas_historico['Data'][0]['id_agente_callcenter']);
                    } catch (\Exception $e) {
                        $callcenter_habilitado = false;
                    }

                    if ($callcenter_habilitado) {
                        $agenteValue = $data_citas_agendadas_historico['Data'][0]['id_agente_callcenter'];
                        $insertData['id_agente_callcenter'] = $agenteValue;
                    } else {
                        $idAgenteCallcenter = $this->RoundRobinCallCenter();
                        $agenteValue = is_null($idAgenteCallcenter) ? "NULL" : $idAgenteCallcenter;
                        $insertData['id_agente_callcenter'] = $agenteValue;
                    }

                    // SE REALIZA LA INSERCIÓN DE LA CITA
                    $save = DB::table('tb_cita')->insert($insertData);

                    // SE REALIZA LA INSERCIÓN DEL SEGUIMIENTO
                    if ($save) {
                        $this->postSeguimientoDuplicados(new \Illuminate\Http\Request(['cc' => $doc_cliente]));
                    }
                } else {
                    $query_estado_agendado = DB::table('tb_estado')->select(['tb_estado.*'])->where('tb_estado.nombre_estado', 'Agendado');
                    $estado_agendado = $query_estado_agendado->first();
                    $response_citas_agendadas_historico = $this->getCitasAgendadasHistorico(new \Illuminate\Http\Request(['cc' => $doc_cliente]));
                    $data_citas_agendadas_historico = $response_citas_agendadas_historico->getData(true);
                    $callcenter_habilitado = false;
                    $agenteValue = null;

                    // SE VERIFICA SI EL AGENTE CALL-CENTER ESTÁ HABILITADO
                    try {
                        $callcenter_habilitado = $this->verificarEstadoCallCenter($data_citas_agendadas_historico['Data'][0]['id_agente_callcenter']);
                    } catch (\Exception $e) {
                        $callcenter_habilitado = false;
                    }

                    if ($callcenter_habilitado) {
                        $agenteValue = $data_citas_agendadas_historico['Data'][0]['id_agente_callcenter'];
                        $insertData['id_agente_callcenter'] = $agenteValue;
                    } else {
                        $idAgenteCallcenter = $this->RoundRobinCallCenter();
                        $agenteValue = is_null($idAgenteCallcenter) ? "NULL" : $idAgenteCallcenter;
                        $insertData['id_agente_callcenter'] = $agenteValue;
                    }

                    // SE AGREGAN LOS ESTADOS Y EL CALLCENTER
                    $insertData['id_estado'] = $estado_agendado->id_estado;
                    $insertData['id_estado_verificado'] = $estado_agendado->id_estado;
                    $insertData['id_agente_callcenter'] = $agenteValue;

                    // SE REALIZA LA INSERCIÓN DE LA CITA
                    $save = DB::table('tb_cita')->insert($insertData);
                }

                if ($save) {

                    try {
                        // Consulta la información de la sede
                        $sede = DB::table('tb_sede')->where('id_sede', $id_sede)->first();
                        $id_ciudad = $sede->id_ciudad ?? null;

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
                            'tipo_dispositivo' => $tipo_dispositivo
                        ];
                        // Enviar el correo al cliente
                        $enviadoCliente = $this->sendPulse->sendEmailConfirmacion(
                            $email_cliente,
                            $nombre_cliente,
                            $nombre_cliente . " Confirmamos tu cita",
                            $doc_cliente,
                            $sede,

                            $templateVariables
                        );

                        if (!$enviadoCliente) {
                            Log::error("Error al enviar uno o ambos correos con SendPulse.");
                        }
                    } catch (\Exception $e) {
                        Log::error($e->getMessage());
                    }

                    // Obtener el ID de la cita recién creada
                    $ultimaCita = DB::table('tb_cita')->orderBy('id_cita', 'desc')->first();
                    $id_cita = $ultimaCita->id_cita;

                    // Envía el mensaje de WhatsApp al cliente
                    WhatsappJob::dispatch($id_cita, $citas_agendadas)->onQueue('Whatsapp');

                    // Se envía la cita para validar en el SIMIT
                    $metodo_actual = DB::table('tb_config')
                        ->where('config_key', 'method_scraping')
                        ->value('config_value');
                    
                    // SE un switch case para validar que método debe de usar
                    switch ($metodo_actual) {
                        case 1:
                            ScrapingSimitJob::dispatch($id_cita, $doc_cliente)->onQueue('Scraping');
                            break;
                        case 2:
                            Log::info("Se enviaría al Agente ChatGPT para realizar el Scraping.");
                            break;
                    }

                    try {
                        $saveliquidador = DB::table('tb_liquidador')->insert([
                            'id_cita' => $id_cita,
                            'estado_liquidador' => "Pendiente",
                            'comentario_liquidador' => "",
                            'pago_liquidador' => "Pendiente",
                            'created_at' => DB::raw('DATE_SUB(NOW(), INTERVAL 5 HOUR)'),
                            'updated_at' => DB::raw('DATE_SUB(NOW(), INTERVAL 5 HOUR)')
                        ]);

                        //AQUI SE ENVÍA LA INFORMACIÓN DE LOS DETALLES DE LA CITA
                        //$url_detalles = config('app.url').'/agendas-cursos/public_html/api/detalles-cita/'.Crypt::encryptString($id_cita);
                        //$detalles = Http::get($url_detalles);

                    } catch (\Throwable $e) {
                        Log::error($e->getMessage());
                    }

                    $objLoad = [
                        'validate' => true,
                        'text' => 'Cita guardada correctamente',
                        'id' => Crypt::encryptString($id_cita)
                    ];
                }
            } catch (\Throwable $e) {
                Log::error($e->getMessage());
            }
            return response()->json($objLoad);
        }
    }

    public function RoundRobinCallCenter()
    {
        $agentes = User::permission('global.Asignar citas call.v')
            ->where('callcenter_habilitado', 1)
            ->orderBy('id', 'asc')
            ->get();

        Log::info($agentes);

        $config = DB::table('tb_config')
            ->where('config_key', 'round_robin_callcenter')
            ->first();

        // SI NO EXISTE, SE INICIALIZA EN 0 DE NUEVO
        $puntero = $config ? (int)$config->config_value : 0;

        $countAgentes = $agentes->count();
        $idAgenteCallcenter = null;

        if ($countAgentes > 0) {
            // SI EL PUNTERO SOBREPASA EL TOTAL DE AGENTES, REINICIAMOS A 0
            if ($puntero >= $countAgentes) {
                $puntero = 0;
            }

            // ASIGNAMOS EL AGENTE SEGÚN LA POSICIÓN DEL PUNTERO Y SE INCREMENTA
            $idAgenteCallcenter = $agentes[$puntero]->id;
            $puntero++;

            DB::table('tb_config')->updateOrInsert(
                ['config_key' => 'round_robin_callcenter'],
                ['config_value' => $puntero]
            );

            Log::info($idAgenteCallcenter);

            return $idAgenteCallcenter;
        }
    }

    public function gethorarios(Request $request)
    {
        if ($request->ajax()) {
            $objLoad = [
                'validate' => false,
                'text' => 'Error al obtener los datos',
            ];
            try {
                $id = $request->request->get('id');
                $sql = "SELECT * FROM tb_sede_horario as t1 INNER JOIN tb_horario as t2 ON t1.id_horario = t2.id_horario  WHERE t1.id_sede = $id order by t2.inicio_horario asc";
                $horarios = DB::select($sql);
                if (is_array($horarios) && !empty($horarios)) {
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

    public function getCitasAgendadas(Request $request)
    {
        $cedula = (string) $request->input('cc');
        $fecha_comparacion = now()->subDay(4)->startOfDay();

        $query = DB::table('tb_cita')
            ->join('tb_cliente', 'tb_cita.id_cliente', '=', 'tb_cliente.id_cliente')
            ->select([
                'tb_cita.*',
                'tb_cliente.nombre_cliente',
                'tb_cliente.apellido_cliente',
            ])
            ->where('tb_cliente.doc_cliente', $cedula)
            ->where('tb_cita.reserva_cita', '>', $fecha_comparacion)
            ->orderBy('reserva_cita', 'desc');

        $result = $query->get();

        if ($result->count() != 0) {
            return true;
        } else {
            return false;
        }
    }

    public function getDetallesCita($id)
    {
        $idCita = Crypt::decryptString($id);

        $query = DB::table('tb_cita')
            ->join('tb_sede', 'tb_cita.id_sede', '=', 'tb_sede.id_sede')
            ->join('tb_cliente', 'tb_cita.id_cliente', '=', 'tb_cliente.id_cliente')
            ->select([
                'tb_cliente.nombre_cliente',
                'tb_cliente.apellido_cliente',
                'tb_cliente.tipo_doc_cliente',
                'tb_cliente.doc_cliente',
                'tb_cliente.telefono_cliente',
                'tb_cliente.email_cliente',
                'tb_cita.reserva_cita',
                'tb_cita.rango_horario',
                'tb_sede.nombre_sede',
                'tb_sede.direccion_sede'
            ])
            ->where('tb_cita.id_cita', $idCita);

        $data_cita = $query->first();
        return $data_cita;
    }

    public function verificarEstadoCallCenter($idCallcenter)
    {
        $agente = User::permission('global.Asignar citas call.v')
            ->where('id', $idCallcenter)
            ->where('callcenter_habilitado', 1)
            ->first();

        if ($agente != null) {
            return true;
        } else {
            return false;
        }
    }

    public function getCitasAgendadasHistorico(Request $request)
    {
        $cedula = (string) $request->input('cc');

        $query = DB::table('tb_cita')
            ->join('tb_cliente', 'tb_cita.id_cliente', '=', 'tb_cliente.id_cliente')
            ->select([
                'tb_cita.*',
                'tb_cliente.nombre_cliente',
                'tb_cliente.apellido_cliente',
            ])
            ->where('tb_cliente.doc_cliente', $cedula)
            ->orderBy('reserva_cita', 'desc');

        $result = $query->get();

        if ($result->count() != 0) {
            return response()->json([
                'resultado' => true,
                'Data' => $result
            ]);
        } else {
            return response()->json([
                'resultado' => false,
                'Data' => []
            ]);
        }
    }

    public function postSeguimientoDuplicados(Request $request)
    {
        $cedula = (string) $request->input('cc');
        $query_sistema = DB::table('users')->select(['users.*'])->where('email', 'jrubio@zocodigital.com');
        $query = DB::table('tb_cita')->join('tb_cliente', 'tb_cita.id_cliente', '=', 'tb_cliente.id_cliente')->select(['tb_cita.*'])->where('tb_cliente.doc_cliente', $cedula)->orderBy('created_at', 'desc');
        $ultimaCita = $query->first();
        $sistema = $query_sistema->first();

        if ($ultimaCita != null && $sistema != null) {
            DB::table('tb_seguimiento')->insert([
                'titulo_seguimiento' => 'Cambio de estado cita',
                'nota_seguimiento'  => 'El sistema ha realizado el cambio del estado de la cita a duplicado.',
                'id_cita'           => $ultimaCita->id_cita,
                'id_user'           => $sistema->id,
            ]);
        }
    }

    public function postVerificarCuposHorario(Request $request)
    {

        if (!$request->has('horarios_disponibles')) {
            return true;
        }

        $horarios_disponibles = $request->input('horarios_disponibles');
        $fechaFormateada = \Carbon\Carbon::createFromFormat('d/m/Y', $request->input('fecha_seleccionada'))->format('Y-m-d');

        foreach ($horarios_disponibles as $index => &$horario) {
            $countCitas = DB::table('tb_cita')
                ->where('id_sede', $request->input('sede'))
                ->where('reserva_cita', $fechaFormateada)
                ->where('rango_horario', $horario['rango_horario'])
                ->count();

            if ($countCitas < (int) $horario['cupo_sede_horario']) {
                $horario['disponible'] = true;
            } else {
                $horario['disponible'] = false;
            }
        }

        return $horarios_disponibles;
    }
}

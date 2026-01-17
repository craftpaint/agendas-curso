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
            'ids' => [] // Para retornar IDs de citas creadas
        ];

        try {
            // ────────────────────────────────────────────────────────────────
            // Datos comunes
            // ────────────────────────────────────────────────────────────────
            $id_sede = $request->request->get('id_sede');
            $nombre_cliente = $request->request->get('nombre_cliente');
            $apellido_cliente = $request->request->get('apellido_cliente');
            $email_cliente = $request->request->get('email_cliente');
            $telefono_cliente = $request->request->get('telefono_cliente');
            $tipo_doc_cliente = $request->request->get('tipo_doc_cliente');
            $doc_cliente = $request->request->get('doc_cliente');
            $origen = $request->input('utm_source', 'Desconocido');
            $urlVariables = $request->request->get('url_variables');
            $urlVariablesArray = json_decode($urlVariables, true) ?: [];
            $tipo_dispositivo = $this->detectDevice($request->header('User-Agent'));

            if ($origen == 'Desconocido' || $origen == '' || $origen == null) {
                $responsable_origen = 'Desconocido';
            } elseif (in_array(strtolower($origen), ['qr', 'qrcode'])) {
                $responsable_origen = 'Sede';
            } else {
                $responsable_origen = 'Curso Comparendo';
            }

            $creado_por = 'Cliente';
            $empresa_paquete = PaqueteHelper::obtenerPaqueteActivoEmpresa($id_sede);

            // Sede info
            $sede = DB::table('tb_sede')->where('id_sede', $id_sede)->first();
            $id_ciudad = $sede->id_ciudad ?? null;
            $nombre_sede = $sede ? $sede->nombre_sede : 'Sede no encontrada';
            $direccion_sede = $sede ? $sede->direccion_sede : 'Dirección no encontrada';
            $latitud = $sede ? str_replace(',', '.', $sede->latitud) : '';
            $longitud = $sede ? str_replace(',', '.', $sede->longitud) : '';

            // ────────────────────────────────────────────────────────────────
            // Array de comparendos
            // ────────────────────────────────────────────────────────────────
            $comparendos = $request->input('comparendos', []);
            $totalComparendos = count($comparendos);

            if ($totalComparendos == 0 || $totalComparendos > 3) {
                $objLoad['text'] = 'Número inválido de comparendos.';
                return response()->json($objLoad);
            }

            // Determinar servicio liquidador según cantidad
            $servicio_liquidador = $this->getServicioLiquidadorId($totalComparendos);

            // ────────────────────────────────────────────────────────────────
            // NUEVO: Generar identificador único de grupo (UUID v4)
            // ────────────────────────────────────────────────────────────────
            $grupo_uuid = \Illuminate\Support\Str::uuid()->toString(); // Ej: "550e8400-e29b-41d4-a716-446655440000"

            // ────────────────────────────────────────────────────────────────
            // Cliente
            // ────────────────────────────────────────────────────────────────
            $cliente = AdminHelper::get_cliente_by_doc($doc_cliente);
            if (!$cliente) {
                DB::insert(
                    "INSERT INTO tb_cliente (nombre_cliente, apellido_cliente, email_cliente, tipo_doc_cliente, doc_cliente, telefono_cliente, desc_cliente) VALUES (?, ?, ?, ?, ?, ?, 'Creada por el cliente')",
                    [$nombre_cliente, $apellido_cliente, $email_cliente, $tipo_doc_cliente, $doc_cliente, $telefono_cliente]
                );
                $id_cliente = DB::getPdo()->lastInsertId();
            } else {
                $id_cliente = $cliente['id_cliente'];
            }

            // ────────────────────────────────────────────────────────────────
            // Duplicados y estados
            // ────────────────────────────────────────────────────────────────
            $citas_agendadas = $this->getCitasAgendadas(new \Illuminate\Http\Request(['cc' => $doc_cliente]));
            $estado_duplicado = DB::table('tb_estado')->where('nombre_estado', 'Duplicado')->first();
            $estado_agendado = DB::table('tb_estado')->where('nombre_estado', 'Agendado')->first();
            $id_estado_default = $citas_agendadas ? $estado_duplicado->id_estado : $estado_agendado->id_estado;
            $id_estado_verificado_default = $id_estado_default;

            // Agente callcenter
            $idAgenteCallcenter = $this->getAgenteCallcenter($doc_cliente, $citas_agendadas);

            // ────────────────────────────────────────────────────────────────
            // Procesar cada comparendo
            // ────────────────────────────────────────────────────────────────
            $citasCreadas = [];
            DB::beginTransaction();

            foreach ($comparendos as $index => $comp) {
                $reserva_cita = $comp['reserva_cita'] ?? null;
                $id_sede_horario = $comp['id_sede_horario'] ?? null;
                $tipo_vehiculo = $comp['tipo_vehiculo'] ?? null;
                $placa_vehiculo = strtoupper($comp['placa_vehiculo'] ?? '');
                $codigo_comparendo = $comp['codigo_comparendo'] ?? '';
                $fecha_notificacion = $comp['fecha_notificacion'] ?? null;

                // Formatear fechas
                $date_reserva = \DateTime::createFromFormat('d/m/Y', $reserva_cita);
                if (!$date_reserva) {
                    throw new \Exception("Formato de fecha reserva inválido en comparendo #" . ($index + 1));
                }
                $reserva_cita = $date_reserva->format('Y-m-d');

                $date_notif = \DateTime::createFromFormat('d/m/Y', $fecha_notificacion);
                if (!$date_notif) {
                    throw new \Exception("Formato de fecha notificación inválido en comparendo #" . ($index + 1));
                }
                $fecha_notificacion = $date_notif->format('Y-m-d');

                // Validar horario y cupo
                $horario_sedes = AdminHelper::get_horario_by_id($id_sede_horario);
                if (empty($horario_sedes)) {
                    throw new \Exception("Horario inválido en comparendo #" . ($index + 1));
                }

                $cupo_sede_horario = $horario_sedes['cupo_sede_horario'];
                $id_horario = $horario_sedes['id_horario'];
                $horario = AdminHelper::get_horarios_by_id($id_horario);
                $rango_horario = $horario['rango_horario'];

                $sql = "SELECT COUNT(*) as count FROM tb_cita WHERE id_sede = ? AND reserva_cita = ? AND rango_horario = ?";
                $citas_count = DB::selectOne($sql, [$id_sede, $reserva_cita, $rango_horario])->count;

                if ($citas_count >= $cupo_sede_horario) {
                    throw new \Exception("No hay cupo disponible para la cita en comparendo #" . ($index + 1));
                }

                // Vehículo
                $id_vehiculo = null;
                if (!empty($placa_vehiculo)) {
                    $vehiculo = AdminHelper::get_vehiculo_by_placa($placa_vehiculo);
                    if (!$vehiculo) {
                        DB::insert(
                            "INSERT INTO tb_vehiculo (id_cliente, placa_vehiculo, tipo_vehiculo, modelo_vehiculo) VALUES (?, ?, ?, '0000')",
                            [$id_cliente, $placa_vehiculo, $tipo_vehiculo]
                        );
                        $id_vehiculo = DB::getPdo()->lastInsertId();
                    } else {
                        $id_vehiculo = $vehiculo[0]['id_vehiculo'];
                    }
                }

                // ────────────────────────────────────────────────────────────────
                // Datos para insertar (con el nuevo campo grupo_comparendos)
                // ────────────────────────────────────────────────────────────────
                $insertData = [
                    'id_cliente'              => $id_cliente,
                    'id_sede'                 => $id_sede,
                    'id_estado'               => $id_estado_default,
                    'id_estado_verificado'    => $id_estado_verificado_default,
                    'id_servicio_liquidador'  => $servicio_liquidador,
                    'id_agente_callcenter'    => $idAgenteCallcenter,
                    'codigos_comparendo'      => $codigo_comparendo,
                    'reserva_cita'            => $reserva_cita,
                    'rango_horario'           => $rango_horario,
                    'desc_cita'               => 'Creada por el cliente - Comparendo #' . ($index + 1),
                    'responsable_origen'      => $responsable_origen,
                    'creado_por'              => $creado_por,
                    'origen'                  => $origen,
                    'url_variables'           => json_encode($urlVariablesArray),
                    'tipo_dispositivo'        => $tipo_dispositivo,
                    'created_at'              => Carbon::now(),
                    'updated_at'              => Carbon::now(),
                    'id_empresa_paquete'      => $empresa_paquete,
                    'fecha_notificacion'      => $fecha_notificacion,

                    // ¡NUEVO CAMPO!
                    'grupo_comparendos'       => $grupo_uuid,
                ];

                if ($id_vehiculo) {
                    $insertData['id_vehiculo'] = $id_vehiculo;
                }

                // Insertar
                $id_cita = DB::table('tb_cita')->insertGetId($insertData);
                $citasCreadas[] = $id_cita;

                // Email de confirmación
                $this->sendConfirmationEmail($id_cliente, $id_sede, $id_cita, $reserva_cita, $rango_horario, $origen, $tipo_dispositivo);

                // Seguimiento duplicados (solo una vez por grupo, pero como está dentro del loop, se ejecuta por cada uno - si quieres solo una vez, muévelo fuera)
                if ($citas_agendadas) {
                    $this->postSeguimientoDuplicados(new \Illuminate\Http\Request(['cc' => $doc_cliente]));
                }
            }

            DB::commit();

            $objLoad['validate'] = true;
            $objLoad['text'] = 'Citas agendadas exitosamente.';
            $objLoad['ids'] = $citasCreadas;
            $objLoad['grupo'] = $grupo_uuid; // Opcional: para debug o futuras referencias

            return response()->json($objLoad);

        } catch (\Exception $e) {
            DB::rollback();
            $objLoad['text'] = $e->getMessage();
            Log::error("Error al guardar citas múltiples: " . $e->getMessage());
            return response()->json($objLoad);
        }
    }
}

private function getServicioLiquidadorId($total) {
    $nombre = match($total) {
        1 => '1 comparendo',
        2 => '2 comparendos',
        3 => '3 comparendos',
        default => '+3 comparendos'
    };
    $servicio = DB::table('tb_servicio_liquidador')->where('nombre_servicio_liquidador', $nombre)->first();
    return $servicio ? $servicio->id_servicio_liquidador : 5; // Default si no encuentra
}

private function detectDevice($userAgent) {
    if (preg_match('/mobile/i', $userAgent)) return 'Mobile';
    if (preg_match('/tablet/i', $userAgent)) return 'Tablet';
    return 'Desktop';
}

private function getAgenteCallcenter($doc_cliente, $citas_agendadas) {
    if (!$citas_agendadas) return null; // No duplicado, no agente
    $historico = $this->getCitasAgendadasHistorico(new \Illuminate\Http\Request(['cc' => $doc_cliente]))->getData(true)['Data'][0] ?? null;
    $callcenter_habilitado = $this->verificarEstadoCallCenter($historico['id_agente_callcenter'] ?? null);
    if ($callcenter_habilitado) return $historico['id_agente_callcenter'];
    return $this->RoundRobinCallCenter();
}

private function sendConfirmationEmail($id_cliente, $id_sede, $id_cita, $reserva_cita, $rango_horario, $origen, $tipo_dispositivo) {
    // Lógica existente adaptada para una cita
    $cliente = DB::table('tb_cliente')->where('id_cliente', $id_cliente)->first();
    $sede = DB::table('tb_sede')->where('id_sede', $id_sede)->first();
    $servicio = DB::table('tb_servicio')->where('id_servicio', $sede->id_servicio)->first();
    $templateVariables = [
        'nombre_cliente' => $cliente->nombre_cliente,
        'apellido_cliente' => $cliente->apellido_cliente,
        'nombre_sede' => $sede->nombre_sede,
        'direccion_sede' => $sede->direccion_sede,
        'email_cliente' => $cliente->email_cliente,
        'telefono_cliente' => $cliente->telefono_cliente,
        'reserva_cita' => $reserva_cita,
        'rango_horario' => $rango_horario,
        'origen' => $origen,
        'tipo_dispositivo' => $tipo_dispositivo,
        'nombre_servicio' => $servicio->tipo_servicio ?? 'Servicio no encontrado',
        'latitud' => str_replace(',', '.', $sede->latitud),
        'longitud' => str_replace(',', '.', $sede->longitud),
    ];
    $this->sendPulse->sendEmailConfirmacion(
        $cliente->email_cliente,
        $cliente->nombre_cliente,
        $cliente->nombre_cliente . " Confirmamos tu cita #$id_cita",
        $cliente->doc_cliente,
        $sede,
        $templateVariables
    );
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

        // Fecha seleccionada
        $fechaFormateada = Carbon::createFromFormat(
            'd/m/Y',
            $request->input('fecha_seleccionada')
        )->format('Y-m-d');

        $ahora = Carbon::now();

        foreach ($horarios_disponibles as $index => &$horario) {

            /*
            |--------------------------------------------------
            | Validación margen de 15 minutos (solo HOY)
            |--------------------------------------------------
            */
            if ($fechaFormateada === $ahora->format('Y-m-d')) {

                // Hora inicio del horario
                $horaInicio = Carbon::createFromFormat(
                    'Y-m-d H:i:s',
                    $fechaFormateada . ' ' . $horario['inicio_horario']
                );

                // Hora mínima permitida
                $horaMinima = $ahora->copy()->addHour();

                if ($horaInicio->lt($horaMinima)) {
                    //  No cumple margen → se muestra DESHABILITADO
                    $horario['disponible'] = false;
                    continue;
                }
            }

            /*
            |--------------------------------------------------
            | Validación de cupo
            |--------------------------------------------------
            */
            $countCitas = DB::table('tb_cita')
                ->where('id_sede', $request->input('sede'))
                ->where('reserva_cita', $fechaFormateada)
                ->where('rango_horario', $horario['rango_horario'])
                ->count();

            if ($countCitas < (int)$horario['cupo_sede_horario']) {
                $horario['disponible'] = true;
            } else {
                //  Cupo lleno → se muestra DESHABILITADO
                $horario['disponible'] = false;
            }
        }

        // IMPORTANTE: devolvemos TODOS los horarios
        return $horarios_disponibles;
    }

    public function limitar_agente($idAgente, $id_cita, $citas_agendadas) {
        $agente_limitado = DB::table('tb_config')
            ->where('config_key', 'limited_agent')
            ->value('config_value');

        $porcentaje_envio_sendpulse = DB::table('tb_config')
            ->where('config_key', 'sendpulse_delivery_rate')
            ->value('config_value');

        
        if($agente_limitado == $idAgente) {
            $enviar = $this->probabilidad_envio_sendpulse($porcentaje_envio_sendpulse);

            if($enviar) {
                // Envía el mensaje de WhatsApp al cliente
                WhatsappJob::dispatch($id_cita, $citas_agendadas)->onQueue('Whatsapp');
            }
        } else {
            // Envía el mensaje de WhatsApp al cliente
            WhatsappJob::dispatch($id_cita, $citas_agendadas)->onQueue('Whatsapp');
        }
    }

    function probabilidad_envio_sendpulse(int $probabilidad_true): bool {
        $probabilidad = max(0, min(100, $probabilidad_true));
        $aleatorio = mt_rand(1, 100);
        return $aleatorio <= $probabilidad;
    }
}

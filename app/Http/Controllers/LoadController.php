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
                // ================================
                // DETECTAR TIPO DE FORMULARIO
                // ================================
                $isMultiComparendo = $request->has('comparendos');
                
                if ($isMultiComparendo) {
                    // ================================
                    // NUEVO FLUJO: MÚLTIPLES COMPARENDOS
                    // ================================
                    return $this->saveMultiComparendos($request);
                } else {
                    // ================================
                    // FLUJO ANTIGUO: COMPATIBILIDAD
                    // ================================
                    return $this->saveSingleCita($request);
                }
                
            } catch (\Throwable $e) {
                Log::error('Error en savecita: ' . $e->getMessage());
                Log::error($e->getTraceAsString());
                $objLoad['text'] = 'Error interno del sistema. Por favor intente nuevamente.';
                return response()->json($objLoad);
            }
        }
    }

    /**
     * Guardar múltiples comparendos (NUEVO FLUJO)
     */
    private function saveMultiComparendos(Request $request)
    {
        $objLoad = [
            'validate' => false,
            'text' => 'Error al agendar las citas',
        ];

        try {
            // Datos del solicitante
            $id_sede = $request->input('id_sede');
            $nombre_cliente = $request->input('nombre_cliente');
            $apellido_cliente = $request->input('apellido_cliente');
            $email_cliente = $request->input('email_cliente');
            $telefono_cliente = $request->input('telefono_cliente');
            $tipo_doc_cliente = $request->input('tipo_doc_cliente');
            $doc_cliente = $request->input('doc_cliente');
            $empresa_paquete = PaqueteHelper::obtenerPaqueteActivoEmpresa($id_sede);

            // Obtener array de comparendos
            $comparendos = $request->input('comparendos', []);
            
            // Validar que haya al menos un comparendo
            if (empty($comparendos)) {
                $objLoad['text'] = 'Debe agregar al menos un comparendo';
                return response()->json($objLoad);
            }

            // Validar límite de comparendos (máximo 3)
            if (count($comparendos) > 3) {
                $objLoad['text'] = 'No puede agendar más de 3 comparendos';
                return response()->json($objLoad);
            }

            // Obtener información de la sede
            $sede = DB::table('tb_sede')->where('id_sede', $id_sede)->first();
            if (!$sede) {
                $objLoad['text'] = 'Sede no encontrada';
                return response()->json($objLoad);
            }

            $nombre_sede = $sede->nombre_sede;
            $direccion_sede = $sede->direccion_sede;
            $latitud = str_replace(',', '.', $sede->latitud);
            $longitud = str_replace(',', '.', $sede->longitud);
            $id_ciudad = $sede->id_ciudad ?? null;

            // Configurar origen y dispositivo
            $origen = $request->input('utm_source', 'Desconocido');
            $responsable_origen = $this->determinarResponsableOrigen($origen);
            $tipo_dispositivo = $this->determinarTipoDispositivo($request);
            $urlVariablesArray = $this->obtenerVariablesUrl($request);

            // ================================
            // CREAR O BUSCAR CLIENTE
            // ================================
            $cliente = AdminHelper::get_cliente_by_doc($doc_cliente);
            if (!$cliente) {
                $id_cliente = DB::table('tb_cliente')->insertGetId([
                    'nombre_cliente' => $nombre_cliente,
                    'apellido_cliente' => $apellido_cliente,
                    'email_cliente' => $email_cliente,
                    'tipo_doc_cliente' => $tipo_doc_cliente,
                    'doc_cliente' => $doc_cliente,
                    'telefono_cliente' => $telefono_cliente,
                    'desc_cliente' => 'Creado por el cliente - Múltiples comparendos',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);
            } else {
                $id_cliente = $cliente['id_cliente'];
            }

            // ================================
            // VERIFICAR CITAS DUPLICADAS
            // ================================
            $citas_agendadas = $this->getCitasAgendadas(new Request(['cc' => $doc_cliente]));
            
            // Determinar estados según si hay citas duplicadas
            if ($citas_agendadas) {
                $estado = DB::table('tb_estado')->where('nombre_estado', 'Duplicado')->first();
            } else {
                $estado = DB::table('tb_estado')->where('nombre_estado', 'Agendado')->first();
            }

            // ================================
            // ASIGNAR AGENTE CALL CENTER
            // ================================
            $agenteValue = $this->asignarAgenteCallCenter($citas_agendadas, $doc_cliente);

            // ================================
            // PROCESAR CADA COMPARENDO
            // ================================
            $citasCreadas = [];
            $errores = [];

            foreach ($comparendos as $index => $comparendo) {
                try {
                    // Validar campos requeridos del comparendo
                    if (empty($comparendo['reserva_cita']) || empty($comparendo['id_sede_horario'])) {
                        $errores[] = "Comparendo " . ($index + 1) . ": Fecha y horario son requeridos";
                        continue;
                    }

                    // Formatear fecha de reserva
                    $date = \DateTime::createFromFormat('d/m/Y', $comparendo['reserva_cita']);
                    if (!$date) {
                        $errores[] = "Comparendo " . ($index + 1) . ": Formato de fecha incorrecto";
                        continue;
                    }
                    $reserva_cita = $date->format('Y-m-d');

                    // Formatear fecha de notificación si existe
                    $fecha_notificacion = null;
                    if (!empty($comparendo['fecha_notificacion_comparendo'])) {
                        $dateNotif = \DateTime::createFromFormat('d/m/Y', $comparendo['fecha_notificacion_comparendo']);
                        if ($dateNotif) {
                            $fecha_notificacion = $dateNotif->format('Y-m-d');
                        }
                    }

                    // Verificar disponibilidad del horario
                    $horario_sedes = AdminHelper::get_horario_by_id($comparendo['id_sede_horario']);
                    if (empty($horario_sedes) || !is_array($horario_sedes)) {
                        $errores[] = "Comparendo " . ($index + 1) . ": Horario no disponible";
                        continue;
                    }

                    $cupo_sede_horario = $horario_sedes['cupo_sede_horario'];
                    $id_horario = $horario_sedes['id_horario'];
                    $horario = AdminHelper::get_horarios_by_id($id_horario);
                    $rango_horario = $horario['rango_horario'];

                    // Verificar cupos disponibles
                    $citasCount = DB::table('tb_cita')
                        ->where('id_sede', $id_sede)
                        ->where('reserva_cita', $reserva_cita)
                        ->where('rango_horario', $rango_horario)
                        ->count();

                    if ($citasCount >= $cupo_sede_horario) {
                        $errores[] = "Comparendo " . ($index + 1) . ": No hay cupo disponible";
                        continue;
                    }

                    // ================================
                    // CREAR VEHÍCULO (si se especifica)
                    // ================================
                    $id_vehiculo = null;
                    if (!empty($comparendo['placa_vehiculo']) && !empty($comparendo['tipo_vehiculo'])) {
                        $placa_vehiculo = strtoupper($comparendo['placa_vehiculo']);
                        $vehiculo = AdminHelper::get_vehiculo_by_placa($placa_vehiculo);

                        if (!$vehiculo) {
                            $id_vehiculo = DB::table('tb_vehiculo')->insertGetId([
                                'id_cliente' => $id_cliente,
                                'placa_vehiculo' => $placa_vehiculo,
                                'tipo_vehiculo' => $comparendo['tipo_vehiculo'],
                                'modelo_vehiculo' => '0000',
                                'created_at' => Carbon::now(),
                                'updated_at' => Carbon::now()
                            ]);
                        } else {
                            $id_vehiculo = $vehiculo[0]['id_vehiculo'];
                        }
                    }

                    // ================================
                    // CREAR LA CITA
                    // ================================
                    $insertData = [
                        'id_cliente' => $id_cliente,
                        'id_sede' => $id_sede,
                        'id_estado' => $estado->id_estado,
                        'id_estado_verificado' => $estado->id_estado,
                        'id_servicio_liquidador' => 5, // 1 comparendo = 1 curso
                        'id_agente_callcenter' => $agenteValue,
                        'codigos_comparendo' => $comparendo['codigo_comparendo'] ?? null,
                        'fecha_notificacion_comparendo' => $fecha_notificacion, // NUEVO CAMPO
                        'reserva_cita' => $reserva_cita,
                        'rango_horario' => $rango_horario,
                        'id_vehiculo' => $id_vehiculo,
                        'desc_cita' => 'Comparendo ' . ($index + 1) . ' - Creada por el cliente (Multi-comparendo)',
                        'responsable_origen' => $responsable_origen,
                        'creado_por' => 'Cliente',
                        'origen' => $origen,
                        'url_variables' => json_encode($urlVariablesArray),
                        'tipo_dispositivo' => $tipo_dispositivo,
                        'tipo_agendamiento' => 'multiple', // NUEVO: identificar agendamiento múltiple
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                        'id_empresa_paquete' => $empresa_paquete
                    ];

                    // Insertar la cita
                    $id_cita = DB::table('tb_cita')->insertGetId($insertData);
                    $citasCreadas[] = $id_cita;

                    // ================================
                    // PROCESOS ADICIONALES POR CITA
                    // ================================
                    
                    // 1. Enviar email de confirmación
                    $this->enviarEmailConfirmacion(
                        $email_cliente,
                        $nombre_cliente,
                        $doc_cliente,
                        $sede,
                        [
                            'nombre_cliente' => $nombre_cliente,
                            'apellido_cliente' => $apellido_cliente,
                            'nombre_sede' => $nombre_sede,
                            'direccion_sede' => $direccion_sede,
                            'email_cliente' => $email_cliente,
                            'telefono_cliente' => $telefono_cliente,
                            'reserva_cita' => $reserva_cita,
                            'rango_horario' => $rango_horario,
                            'origen' => $origen,
                            'tipo_dispositivo' => $tipo_dispositivo,
                            'latitud' => $latitud,
                            'longitud' => $longitud,
                        ]
                    );

                    // 2. Crear registro en tb_liquidador
                    DB::table('tb_liquidador')->insert([
                        'id_cita' => $id_cita,
                        'estado_liquidador' => "Pendiente",
                        'comentario_liquidador' => "",
                        'pago_liquidador' => "Pendiente",
                        'created_at' => DB::raw('DATE_SUB(NOW(), INTERVAL 5 HOUR)'),
                        'updated_at' => DB::raw('DATE_SUB(NOW(), INTERVAL 5 HOUR)')
                    ]);

                    // 3. Limitar agente si es necesario
                    $this->limitar_agente($agenteValue, $id_cita, $citas_agendadas);

                    // 4. Scraping SIMIT (solo si no hay citas duplicadas)
                    if (!$citas_agendadas) {
                        $metodo_actual = DB::table('tb_config')
                            ->where('config_key', 'method_scraping')
                            ->value('config_value');
                        
                        if ($metodo_actual > 0) {
                            ScrapingSimitJob::dispatch($id_cita, $doc_cliente, $metodo_actual)->onQueue('Scraping');
                        }
                    }

                } catch (\Exception $e) {
                    $errores[] = "Comparendo " . ($index + 1) . ": " . $e->getMessage();
                    Log::error('Error procesando comparendo ' . ($index + 1) . ': ' . $e->getMessage());
                }
            }

            // ================================
            // VERIFICAR RESULTADOS
            // ================================
            if (empty($citasCreadas)) {
                $objLoad['text'] = 'No se pudo crear ninguna cita. Errores: ' . implode(', ', $errores);
                return response()->json($objLoad);
            }

            // Si hay errores pero también citas creadas
            if (!empty($errores)) {
                $objLoad['validate'] = true;
                $objLoad['text'] = count($citasCreadas) . ' citas creadas con algunos errores: ' . implode(', ', $errores);
                $objLoad['id'] = Crypt::encryptString(implode(',', $citasCreadas));
                return response()->json($objLoad);
            }

            // Todas las citas creadas exitosamente
            $objLoad['validate'] = true;
            $objLoad['text'] = count($citasCreadas) . ' citas agendadas correctamente';
            $objLoad['id'] = Crypt::encryptString(implode(',', $citasCreadas));
            
            // Insertar seguimiento si hay duplicados
            if ($citas_agendadas && !empty($citasCreadas)) {
                $this->postSeguimientoDuplicados(new Request(['cc' => $doc_cliente]));
            }

            return response()->json($objLoad);

        } catch (\Exception $e) {
            Log::error('Error en saveMultiComparendos: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            $objLoad['text'] = 'Error interno al procesar múltiples comparendos';
            return response()->json($objLoad);
        }
    }

    /**
     * Guardar cita única (FLUJO ANTIGUO - COMPATIBILIDAD)
     */
    private function saveSingleCita(Request $request)
    {
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

            // Obtener nombre de la sede
            $sede = DB::table('tb_sede')->where('id_sede', $id_sede)->first();
            $id_ciudad = $sede->id_ciudad ?? null;

            $nombre_sede = $sede ? $sede->nombre_sede : 'Sede no encontrada';
            $direccion_sede = $sede ? $sede->direccion_sede : 'Dirección no encontrada';
            $latitud = $sede ? str_replace(',', '.', $sede->latitud) : '';
            $longitud = $sede ? str_replace(',', '.', $sede->longitud) : '';

            // Asignar valores adicionales
            $creado_por = 'Cliente';
            $origen = $request->input('utm_source', 'Desconocido');
            $responsable_origen = $this->determinarResponsableOrigen($origen);
            $tipo_dispositivo = $this->determinarTipoDispositivo($request);
            $urlVariablesArray = $this->obtenerVariablesUrl($request);

            // Formatear fecha de reserva
            $date = \DateTime::createFromFormat('d/m/Y', $reserva_cita);
            if ($date) {
                $reserva_cita = $date->format('Y-m-d');
            } else {
                throw new \Exception("El formato de la fecha es incorrecto");
            }
            
            // Verificar disponibilidad de la cita
            $horario_sedes = AdminHelper::get_horario_by_id($id_sede_horario);
            if (is_array($horario_sedes) && !empty($horario_sedes)) {
                $cupo_sede_horario = $horario_sedes['cupo_sede_horario'];
                $id_horario = $horario_sedes['id_horario'];
                $horario = AdminHelper::get_horarios_by_id($id_horario);
                $rango_horario = $horario['rango_horario'];
                $sql = "SELECT * FROM tb_cita WHERE id_sede = $id_sede AND reserva_cita = '$reserva_cita' AND rango_horario = '$rango_horario'";
                $citas = DB::select($sql);
                if (count($citas) >= $cupo_sede_horario) {
                    $objLoad['text'] = 'No hay cupo disponible para la cita';
                    return response()->json($objLoad);
                }
            }

            // Verificar si el cliente existe
            $cliente = AdminHelper::get_cliente_by_doc($doc_cliente);
            if (!$cliente) {
                $sql = "INSERT INTO tb_cliente (nombre_cliente, apellido_cliente, email_cliente, tipo_doc_cliente, doc_cliente, telefono_cliente, desc_cliente) VALUES ('$nombre_cliente', '$apellido_cliente', '$email_cliente', '$tipo_doc_cliente', '$doc_cliente', '$telefono_cliente', 'Creado por el cliente')";
                DB::insert($sql);
                $id_cliente = DB::getPdo()->lastInsertId();
            } else {
                $id_cliente = $cliente['id_cliente'];
            }

            // Verificar si el vehículo existe
            if ($placa_vehiculo == '' || $placa_vehiculo == null) {
                $id_vehiculo = null;
            } else {
                $vehiculo = AdminHelper::get_vehiculo_by_placa($placa_vehiculo);
                if (!$vehiculo) {
                    $sql = "INSERT INTO tb_vehiculo (id_cliente, placa_vehiculo, tipo_vehiculo, modelo_vehiculo) VALUES ( $id_cliente, '$placa_vehiculo', '$tipo_vehiculo', '$modelo_vehiculo')";
                    DB::insert($sql);
                    $id_vehiculo = DB::getPdo()->lastInsertId();
                } else {
                    $id_vehiculo = $vehiculo[0]['id_vehiculo'];
                }
            }

            // VERIFICAR SI TIENE CITAS AGENDADAS
            $citas_agendadas = $this->getCitasAgendadas(new Request(['cc' => $doc_cliente]));
            $save = null;
            
            $insertData = [
                'id_cliente' => $id_cliente,
                'id_sede' => $id_sede,
                'id_servicio_liquidador' => $servicio_liquidador,
                'codigos_comparendo' => $codigo_comparendo,
                'id_vehiculo' => $id_vehiculo,
                'reserva_cita' => $reserva_cita,
                'rango_horario' => $rango_horario,
                'desc_cita' => 'Creada por el cliente',
                'responsable_origen' => $responsable_origen,
                'creado_por' => $creado_por,
                'origen' => $origen,
                'url_variables' => json_encode($urlVariablesArray),
                'tipo_dispositivo' => $tipo_dispositivo,
                'tipo_agendamiento' => 'individual', // NUEVO: identificar agendamiento individual
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'id_empresa_paquete' => $empresa_paquete
            ];

            if ($citas_agendadas) {
                $query_estado_duplicado = DB::table('tb_estado')->select(['tb_estado.*'])->where('tb_estado.nombre_estado', 'Duplicado');
                $estado_duplicado = $query_estado_duplicado->first();
                $response_citas_agendadas_historico = $this->getCitasAgendadasHistorico(new Request(['cc' => $doc_cliente]));
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
                    $agenteValue = is_null($idAgenteCallcenter) ? null : $idAgenteCallcenter;
                    $insertData['id_agente_callcenter'] = $agenteValue;
                }

                // SE REALIZA LA INSERCIÓN DE LA CITA
                $save = DB::table('tb_cita')->insert($insertData);

                // SE REALIZA LA INSERCIÓN DEL SEGUIMIENTO
                if ($save) {
                    $this->postSeguimientoDuplicados(new Request(['cc' => $doc_cliente]));
                }
            } else {
                $query_estado_agendado = DB::table('tb_estado')->select(['tb_estado.*'])->where('tb_estado.nombre_estado', 'Agendado');
                $estado_agendado = $query_estado_agendado->first();
                $response_citas_agendadas_historico = $this->getCitasAgendadasHistorico(new Request(['cc' => $doc_cliente]));
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
                    $agenteValue = is_null($idAgenteCallcenter) ? null : $idAgenteCallcenter;
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
                // Obtener el ID de la cita recién creada
                $ultimaCita = DB::table('tb_cita')->orderBy('id_cita', 'desc')->first();
                $id_cita = $ultimaCita->id_cita;

                try {
                    // Enviar email de confirmación
                    $this->enviarEmailConfirmacion(
                        $email_cliente,
                        $nombre_cliente,
                        $doc_cliente,
                        $sede,
                        [
                            'nombre_cliente' => $nombre_cliente,
                            'apellido_cliente' => $apellido_cliente,
                            'nombre_sede' => $nombre_sede,
                            'direccion_sede' => $direccion_sede,
                            'email_cliente' => $email_cliente,
                            'telefono_cliente' => $telefono_cliente,
                            'reserva_cita' => $reserva_cita,
                            'rango_horario' => $rango_horario,
                            'origen' => $origen,
                            'tipo_dispositivo' => $tipo_dispositivo,
                            'latitud' => $latitud,
                            'longitud' => $longitud,
                        ]
                    );
                } catch (\Exception $e) {
                    Log::error($e->getMessage());
                }

                //Limitar agente si es el caso
                $this->limitar_agente($agenteValue, $id_cita, $citas_agendadas);

                // Se envía la cita para validar en el SIMIT
                $metodo_actual = DB::table('tb_config')
                    ->where('config_key', 'method_scraping')
                    ->value('config_value');

                // Se valida si debe realizar el scraping o no
                if ($metodo_actual > 0 && !$citas_agendadas) {
                    ScrapingSimitJob::dispatch($id_cita, $doc_cliente, $metodo_actual)->onQueue('Scraping');
                }

                try {
                    DB::table('tb_liquidador')->insert([
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
                    'id' => Crypt::encryptString($id_cita)
                ];
            }
            
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            $objLoad['text'] = 'Error al procesar la solicitud: ' . $e->getMessage();
        }
        
        return response()->json($objLoad);
    }

    /**
     * Métodos auxiliares reutilizables
     */
    
    private function determinarResponsableOrigen($origen)
    {
        if ($origen == 'Desconocido' || $origen == '' || $origen == null) {
            return 'Desconocido';
        } elseif (in_array(strtolower($origen), ['qr', 'qrcode'])) {
            return 'Sede';
        } else {
            return 'Curso Comparendo';
        }
    }

    private function determinarTipoDispositivo(Request $request)
    {
        $tipo_dispositivo = $request->header('User-Agent');
        
        if (preg_match('/mobile/i', $tipo_dispositivo)) {
            return 'Mobile';
        } elseif (preg_match('/tablet/i', $tipo_dispositivo)) {
            return 'Tablet';
        } else {
            return 'Desktop';
        }
    }

    private function obtenerVariablesUrl(Request $request)
    {
        $urlVariables = $request->request->get('url_variables');
        return json_decode($urlVariables, true) ?: [];
    }

    private function asignarAgenteCallCenter($citas_agendadas, $doc_cliente)
    {
        if ($citas_agendadas) {
            $response_citas_agendadas_historico = $this->getCitasAgendadasHistorico(new Request(['cc' => $doc_cliente]));
            $data_citas_agendadas_historico = $response_citas_agendadas_historico->getData(true);
            
            if (isset($data_citas_agendadas_historico['Data'][0]['id_agente_callcenter'])) {
                $callcenter_habilitado = $this->verificarEstadoCallCenter($data_citas_agendadas_historico['Data'][0]['id_agente_callcenter']);
                if ($callcenter_habilitado) {
                    return $data_citas_agendadas_historico['Data'][0]['id_agente_callcenter'];
                }
            }
        }
        
        return $this->RoundRobinCallCenter();
    }

    private function enviarEmailConfirmacion($email_cliente, $nombre_cliente, $doc_cliente, $sede, $templateVariables)
    {
        try {
            $enviadoCliente = $this->sendPulse->sendEmailConfirmacion(
                $email_cliente,
                $nombre_cliente,
                $nombre_cliente . " Confirmamos tu cita",
                $doc_cliente,
                $sede,
                $templateVariables
            );

            if (!$enviadoCliente) {
                Log::error("Error al enviar el correo al cliente");
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }

    /**
     * Métodos públicos existentes (sin cambios)
     */
    
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
        
        return null;
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

    /**
     * Verificar cupos de horario (alias para compatibilidad)
     */
    public function verificarCuposHorario(Request $request)
    {
        // Llama a la función existente
        $horarios_verificados = $this->postVerificarCuposHorario($request);
        
        // Si devuelve true (cuando no hay horarios_disponibles), devolver array vacío
        if ($horarios_verificados === true) {
            return response()->json(['horarios_verificados' => []]);
        }
        
        // Devolver en el formato esperado
        return response()->json(['horarios_verificados' => $horarios_verificados]);
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
    
    /**
     * Mostrar confirmación de múltiples citas
     */
    public function confirmacion($ids)
    {
        try {
            // Decrypt the IDs first
            $decryptedIds = Crypt::decryptString($ids);
            $idsArray = explode(',', $decryptedIds);
            
            $citas = DB::table('tb_cita')
                ->join('tb_sede', 'tb_cita.id_sede', '=', 'tb_sede.id_sede')
                ->join('tb_cliente', 'tb_cita.id_cliente', '=', 'tb_cliente.id_cliente')
                ->whereIn('tb_cita.id_cita', $idsArray)
                ->select(
                    'tb_cita.*', 
                    'tb_sede.nombre_sede', 
                    'tb_sede.direccion_sede',
                    'tb_cliente.nombre_cliente',
                    'tb_cliente.apellido_cliente',
                    'tb_cliente.doc_cliente'
                )
                ->orderBy('tb_cita.reserva_cita')
                ->orderBy('tb_cita.rango_horario')
                ->get();
            
            return view('load.confirmacion', [
                'citas' => $citas,
                'total' => count($citas),
                'encryptedIds' => $ids // Pasar también para posibles usos
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error en confirmación: ' . $e->getMessage());
            abort(404, 'Enlace de confirmación inválido o expirado');
        }
    }
    
    /**
     * Nueva función para obtener ciudad
     */
    public function obtenerCiudad(Request $request)
    {
        if ($request->ajax()) {
            try {
                $id_ciudad = $request->input('id_ciudad');
                
                $ciudad = DB::table('tb_ciudad')
                    ->where('id_ciudad', $id_ciudad)
                    ->select('id_ciudad', 'nombre')
                    ->first();
                
                if ($ciudad) {
                    return response()->json([
                        'success' => true,
                        'ciudad' => $ciudad
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Ciudad no encontrada'
                    ]);
                }
                
            } catch (\Exception $e) {
                Log::error('Error obteniendo ciudad: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Error interno'
                ]);
            }
        }
    }
}
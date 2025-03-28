<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Mail;
use App\Mail\FormularioCompletado;
use App\Mail\FormularioCompletadoNotifiInterno;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Helpers\AdminHelper;
use App\Models\User;

class LoadController extends Controller
{
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

        $data = [
            'id_sede' => $id_sede,
            'sede' => AdminHelper::get_sede_by_id($id_sede),
            'urlParams' => $urlParams
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

                // Obtener nombre de la sede
                $sede = DB::table('tb_sede')->where('id_sede', $id_sede)->first();
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
                        log::info($vehiculo[0]['id_vehiculo']);
                        $id_vehiculo = $vehiculo[0]['id_vehiculo'];
                    }
                }
                //  OBTENER EL LISTADO DE AGENTES CALLCENTER HABILITADOS
                $agentes = User::role('callcenter')
                    ->where('callcenter_habilitado', 1)
                    ->orderBy('id', 'asc')
                    ->get();
                log::info($agentes);
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
                log::info($idAgenteCallcenter);
                $agenteValue   = is_null($idAgenteCallcenter) ? "NULL" : $idAgenteCallcenter;


                //Creamos la cita
                $sql = "INSERT INTO tb_cita (id_cliente, id_sede, id_estado, id_estado_verificado,id_agente_callcenter, id_vehiculo, reserva_cita, rango_horario, desc_cita,responsable_origen, creado_por, origen, url_variables, tipo_dispositivo, created_at, updated_at)
                    VALUES ($id_cliente, $id_sede, 1, 1, $agenteValue, $id_vehiculo, '$reserva_cita', '$rango_horario', 'Creada por el cliente','$responsable_origen', '$creado_por', '$origen','" . json_encode($urlVariablesArray) . "', '$tipo_dispositivo', DATE_SUB(NOW(), INTERVAL 5 HOUR), DATE_SUB(NOW(), INTERVAL 5 HOUR))";
                $save = DB::insert($sql);
                if ($save) {

                    try {
                        // Preparar los datos del formulario para el correo
                        $formData = [
                            'nombre_cliente' => $nombre_cliente,
                            'nombre_sede' => $nombre_sede,
                            'direccion_sede' => $direccion_sede,
                            'apellido_cliente' => $apellido_cliente,
                            'email_cliente' => $email_cliente,
                            'telefono_cliente' => $telefono_cliente,
                            'reserva_cita' => $reserva_cita,
                            'rango_horario' => $rango_horario,
                            'origen' => $origen,
                            'tipo_dispositivo' => $tipo_dispositivo,
                        ];

                        // Enviar el correo al cliente
                        // Mail::to($email_cliente)->send(new FormularioCompletado($formData));
                        // Enviar el correo a don miguel
                        // Mail::to('jrubio@zocodigital.com')->send(new FormularioCompletadoNotifiInterno($formData));
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
    public function gethorarios(Request $request)
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
}

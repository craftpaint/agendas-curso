<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Services\WhatsappService;
use App\Services\CrmService;
use Illuminate\Support\Str;

class UtilsHelper {

    protected $sendPulseWhatsapp;
    protected $sendPulseCrm;

    public function __construct(WhatsappService $sendPulseWhatsapp, CrmService $sendPulseCrm) {
        $this->sendPulseWhatsapp = $sendPulseWhatsapp;
        $this->sendPulseCrm = $sendPulseCrm;
    }

    public function enviarConfirmacionWhatsappCliente($id_cita) {
        $cita = DB::table('tb_cita')->where('id_cita', $id_cita)->first();
        $cliente = DB::table('tb_cliente')->where('id_cliente', $cita->id_cliente)->first();
        $agente = DB::table('users')->where('id', $cita->id_agente_callcenter)->first();
        $sede = DB::table('tb_sede')->where('id_sede', $cita->id_sede)->first();
        $metodo = 'Ninguno';

        if ($agente->id_chatbot_sendpulse && $agente->id_user_sendpulse && $agente->id_plantilla_sendpulse) {
            $metodo = 'SendPulse WhatsApp';
            $datosUsuario = $this->sendPulseWhatsapp->searchContactByPhone($cliente->telefono_cliente, $agente->id_chatbot_sendpulse);
            //Corrección de estructura de datos
            $fechaFormateada = date('Y/m/d', strtotime($cita->reserva_cita));
            $partes = explode('-', $cita->rango_horario);
            $horaFormateada = trim($partes[0] ?? '');

            $data = [
                'bot_id' => $agente->id_chatbot_sendpulse,
                'phone' => $cliente->telefono_cliente,
                'template' => [
                    'name' => $agente->id_plantilla_sendpulse,
                    'components' => [
                        [
                            'type' => 'body',
                            'parameters' => [
                                [
                                    'type' => 'text',
                                    'text' => $cliente->nombre_cliente
                                ],
                                [
                                    'type' => 'text',
                                    'text' => $cliente->apellido_cliente
                                ],
                                [
                                    'type' => 'text',
                                    'text' => $agente->name
                                ],
                                [
                                    'type' => 'text',
                                    'text' => $fechaFormateada
                                ],
                                [
                                    'type' => 'text',
                                    'text' => $horaFormateada
                                ],
                                [
                                    'type' => 'text',
                                    'text' => $sede->nombre_sede
                                ],
                                [
                                    'type' => 'text',
                                    'text' => $sede->direccion_sede
                                ]
                            ]
                        ]
                    ],
                    'language' => [
                        'policy' => 'deterministic',
                        'code' => 'es'
                    ]
                ]
            ];

            //Enviar mensaje de confirmación por Whatsapp al cliente
            if($datosUsuario){
                $TemplateEnviado = $this->sendPulseWhatsapp->sendWhatsappTemplateByPhone($data);
                if (!$TemplateEnviado) {
                    Log::error("No se pudo enviar la plantilla de confirmación de Whatsapp al cliente");
                    return ['exito' => false, 'metodo' => $metodo];
                }

                //Se asigna el id de whatsapp a la cita
                DB::table('tb_cita')
                    ->where('tb_cita.id_cita', $id_cita)
                    ->update([
                        'tb_cita.id_whatsapp_sendpulse' => $datosUsuario['data']['id'],
                        'tb_cita.updated_at' => Carbon::now()
                    ]);

                $operadorAsignado = $this->sendPulseWhatsapp->assignOperatorToContact($datosUsuario['data']['id'], $agente->id_user_sendpulse);
                if (!$operadorAsignado) {
                    Log::error("No se pudo asignar el operador al contacto de Whatsapp.");
                    return ['exito' => false, 'metodo' => $metodo];
                }
            } else {
                $contactoCreado = $this->sendPulseWhatsapp->createContactWhatsapp($cliente->telefono_cliente, $cliente->nombre_cliente, [], [], $agente->id_chatbot_sendpulse);
                $TemplateEnviado = $this->sendPulseWhatsapp->sendWhatsappTemplateByPhone($data);
                if (!$contactoCreado || !$TemplateEnviado) {
                    Log::error("No se pudo crear el contacto de Whatsapp y tampoco enviar la plantilla de confirmación de cita al cliente");
                    return ['exito' => false, 'metodo' => $metodo];
                }
                
                $datosUsuario = $this->sendPulseWhatsapp->searchContactByPhone($cliente->telefono_cliente, $agente->id_chatbot_sendpulse);
                if (!$datosUsuario) {
                    Log::error("No se encontró el contacto de Whatsapp creado.");
                    return ['exito' => false, 'metodo' => $metodo];
                }

                //Se asigna el id de whatsapp a la cita
                DB::table('tb_cita')
                    ->where('tb_cita.id_cita', $id_cita)
                    ->update([
                        'tb_cita.id_whatsapp_sendpulse' => $datosUsuario['data']['id'],
                        'tb_cita.updated_at' => Carbon::now()
                    ]);

                $operadorAsignado = $this->sendPulseWhatsapp->assignOperatorToContact($datosUsuario['data']['id'], $agente->id_user_sendpulse);
                if (!$operadorAsignado) {
                    Log::error("No se pudo asignar el operador al contacto de Whatsapp.");
                    return ['exito' => false, 'metodo' => $metodo];
                }
            }

            // Crear usuario de CRM y crear el trato
            $datosUsuarioCRM = $this->sendPulseCrm->searchContactByExternalContactId($datosUsuario['data']['id']);
            if ($datosUsuarioCRM) {
                $tratoCreado = $this->sendPulseCrm->createDealCrm($agente->id_user_sendpulse, $cliente->nombre_cliente, $cliente->apellido_cliente, $sede->nombre_sede, $datosUsuarioCRM['data']['id']);
                if (!$tratoCreado) {
                    Log::error("No se pudo crear el trato en el CRM.");
                    return ['exito' => false, 'metodo' => $metodo];
                }

                // Se asigna el ID del trato en la cita
                DB::table('tb_cita')
                    ->where('tb_cita.id_cita', $id_cita)
                    ->update([
                        'tb_cita.id_trato_sendpulse' => $tratoCreado['data']['id'],
                        'tb_cita.updated_at' => Carbon::now()
                    ]);

                $tratoAsignado = $this->sendPulseCrm->assignDealToContact($tratoCreado['data']['id'], $datosUsuarioCRM['data']['id']);
                if (!$tratoAsignado) {
                    Log::error("No se pudo asignar el trato al contacto en el CRM.");
                    return ['exito' => false, 'metodo' => $metodo];
                }
            } else {
                $contactoCrmCreado = $this->sendPulseCrm->createContactCrm($cliente->nombre_cliente, $cliente->apellido_cliente, $agente->id_user_sendpulse, $datosUsuario['data']['id']);
                $contactoCrmEncontrado = $this->sendPulseCrm->searchContactByExternalContactId($datosUsuario['data']['id']);
                if (!$contactoCrmCreado || !$contactoCrmEncontrado) {
                    Log::error("No se pudo crear el contacto en el CRM o no se encontró después de crearlo.");
                    return ['exito' => false, 'metodo' => $metodo];
                }

                $messengerAsignado = $this->sendPulseCrm->assignMessengerContactCrm($cliente->telefono_cliente, $agente->id_chatbot_sendpulse, $datosUsuario['data']['id'], $contactoCrmEncontrado['data']['id']);
                if (!$messengerAsignado) {
                    Log::error("No se pudo asignar el chat del bot al contacto de CRM.");
                    return ['exito' => false, 'metodo' => $metodo];
                }

                $tratoCreado = $this->sendPulseCrm->createDealCrm($agente->id_user_sendpulse, $cliente->nombre_cliente, $cliente->apellido_cliente, $sede->nombre_sede, $contactoCrmEncontrado['data']['id']);
                if (!$tratoCreado) {
                    Log::error("No se pudo crear el trato en el CRM.");
                    return ['exito' => false, 'metodo' => $metodo];
                }

                // Se asigna el ID del trato en la cita
                DB::table('tb_cita')
                    ->where('tb_cita.id_cita', $id_cita)
                    ->update([
                        'tb_cita.id_trato_sendpulse' => $tratoCreado['data']['id'],
                        'tb_cita.updated_at' => Carbon::now()
                    ]);

                $tratoAsignado = $this->sendPulseCrm->assignDealToContact($tratoCreado['data']['id'], $contactoCrmEncontrado['data']['id']);
                if (!$tratoAsignado) {
                    Log::error("No se pudo asignar el trato al contacto en el CRM.");
                    return ['exito' => false, 'metodo' => $metodo];
                }
            }
        }

        if ($agente->id_agente_chatwoot && $agente->id_equipo_agentes_chatwoot && $agente->id_inbox_chatwoot && $agente->nombre_plantilla_chatwoot && $agente->idioma_plantilla_chatwoot && $agente->id_whatsapp_business_phone_number) {
            $metodo = 'ChatWoot WhatsApp';

            $body = [
                "numero_cliente" => $cliente->telefono_cliente,
                "correo_cliente" => $cliente->email_cliente,
                "plantilla" => [
                    "nombre" => $agente->nombre_plantilla_chatwoot,
                    "parametros" => [
                        "1" => $cliente->nombre_cliente . ' ' . $cliente->apellido_cliente,
                        "2" => $agente->name,
                        "3" => date('Y/m/d', strtotime($cita->reserva_cita)),
                        "4" => trim(explode('-', $cita->rango_horario)[0] ?? ''),
                        "5" => $sede->nombre_sede,
                        "6" => $sede->direccion_sede
                    ],
                    "idioma" => $agente->idioma_plantilla_chatwoot
                ],
                "id_agente_chatwoot" => $agente->id_agente_chatwoot,
                "id_equipo_agentes_chatwoot" => $agente->id_equipo_agentes_chatwoot,
                "id_inbox_chatwoot" => $agente->id_inbox_chatwoot,
                "whatsapp_business_phone_number_id" => $agente->id_whatsapp_business_phone_number,
                "url_cita" => env('APP_URL') . '/dashboard/citas/edit/' . $cita->id_cita
            ];

            if ($sede->id_ciudad) {
                $body['ciudad_sede'] = DB::table('tb_ciudad')->where('id_ciudad', $sede->id_ciudad)->value('nombre');
                $body['enviar_imagen_sorteo'] = Str::contains($body['ciudad_sede'], 'Bogotá') ? true : false;
            }

            $codigos = json_decode($cita->codigos_comparendo, true);

            $body['codigos_comparendo'] = (empty($codigos) || !is_array($codigos)) ? '': implode(' - ', array_column($codigos, 'value'));

            $response = Http::withHeaders([
                'Content-Type'  => 'application/json'
            ])->post(env('N8N_RUTA_BASE') . 'webhook-test/send_utilidad_confirmacion_cita', $body);
            
            Log::info("Response ChatWoot: " . $response);
            if (!$response['Success']) {
                Log::error("No se pudo enviar la plantilla de confirmación de Whatsapp al cliente vía ChatWoot. ERROR: " . $response->body());
                return ['exito' => false, 'metodo' => $metodo];
            } else {
                DB::table('tb_cita')->where('tb_cita.id_cita', $id_cita)->update([
                    'tb_cita.id_conversacion_chatwoot' => $response['Data'],
                    'tb_cita.updated_at' => Carbon::now()
                ]);
            }
        }
        return ['exito' => true, 'metodo' => $metodo];
    }

    public static function enviarNotificacionSedes() {
        $secretKey = DB::table('tb_config')
            ->where('config_key', 'encryption_key')
            ->first();
        $rutasEnvio = DB::table('tb_config')
            ->where('config_key', 'url_send_sedes_json')
            ->first();

        $numeroPrincipal = DB::table('tb_config')
            ->where('config_key', 'primary_contact_number')
            ->first();

        if (($rutasEnvio && !empty($rutasEnvio->config_value)) && ($secretKey && !empty($secretKey->config_value))) {
            $sedes = DB::table('tb_sede')
                ->leftJoin('tb_localidad', 'tb_sede.id_localidad', '=', 'tb_localidad.id_localidad')
                ->leftJoin('tb_ciudad', 'tb_sede.id_ciudad', '=', 'tb_ciudad.id_ciudad')
                ->select(
                    'tb_sede.id_sede as id_sede',
                    'tb_sede.nombre_sede as nombre_sede',
                    'tb_sede.direccion_sede as direccion_sede',
                    'tb_sede.tel_sede as telefono_sede',
                    'tb_sede.estado_sede as estado_sede',
                    'tb_sede.festivos_sede as festivos_sede',
                    'tb_sede.latitud as latitud_sede',
                    'tb_sede.longitud as longitud_sede',
                    'tb_sede.horario as horario_sede',
                    'tb_sede.barrio as barrio_sede',
                    'tb_sede.url_video as url_video_sede',
                    'tb_sede.url_imagen as url_imagen_sede',
                    'tb_sede.created_at as created_at_sede',
                    'tb_sede.updated_at as updated_at_sede',
                    'tb_sede.id_localidad as id_localidad',
                    'tb_localidad.nombre_localidad as nombre_localidad',
                    'tb_localidad.latitud as latitud_localidad',
                    'tb_localidad.longitud as longitud_localidad',
                    'tb_localidad.nivel_zoom as nivel_zoom_localidad',
                    'tb_localidad.created_at as created_at_localidad',
                    'tb_localidad.updated_at as updated_at_localidad',
                    'tb_localidad.deleted_at as deleted_at_localidad',
                    'tb_sede.id_ciudad as id_ciudad',
                    'tb_ciudad.nombre as nombre_ciudad',
                    'tb_ciudad.latitud as latitud_ciudad',
                    'tb_ciudad.longitud as longitud_ciudad',
                    'tb_ciudad.nivel_zoom as nivel_zoom_ciudad',
                    'tb_ciudad.estado as estado_ciudad',
                    'tb_ciudad.created_at as created_at_ciudad',
                    'tb_ciudad.updated_at as updated_at_ciudad',
                    'tb_ciudad.deleted_at as deleted_at_ciudad'
                )
                ->get()
                ->map(function ($item) {
                    $data = [
                        'sede' => [
                            'id' => $item->id_sede,
                            'nombre' => $item->nombre_sede,
                            'direccion' => $item->direccion_sede,
                            'telefono' => $item->telefono_sede,
                            'estado' => $item->estado_sede,
                            'festivos' => json_decode($item->festivos_sede),
                            'latitud' => $item->latitud_sede,
                            'longitud' => $item->longitud_sede,
                            'horario' => $item->horario_sede,
                            'barrio' => $item->barrio_sede,
                            'url_video' => $item->url_video_sede,
                            'url_imagen' => $item->url_imagen_sede,
                            'fecha_creacion' => $item->created_at_sede,
                            'fecha_actualizacion' => $item->updated_at_sede
                        ]
                    ];

                    if ($item->id_localidad != null && $item->id_localidad != 0) {
                        $data['localidad']  = [
                            'nombre' => $item->nombre_localidad,
                            'latitud' => $item->latitud_localidad,
                            'longitud' => $item->longitud_localidad,
                            'nivel_zoom' => $item->nivel_zoom_localidad,
                            'fecha_creacion' => $item->created_at_localidad,
                            'fecha_actualizacion' => $item->updated_at_localidad,
                            'fecha_eliminacion' => $item->deleted_at_localidad
                        ];
                    }

                    if ($item->id_ciudad != null && $item->id_ciudad != 0) {
                        $data['ciudad']  = [
                            'nombre' => $item->nombre_ciudad,
                            'latitud' => $item->latitud_ciudad,
                            'longitud' => $item->longitud_ciudad,
                            'nivel_zoom' => $item->nivel_zoom_ciudad,
                            'estado' => $item->estado_ciudad,
                            'fecha_creacion' => $item->created_at_ciudad,
                            'fecha_actualizacion' => $item->updated_at_ciudad,
                            'fecha_eliminacion' => $item->deleted_at_ciudad
                        ];
                    }
                    return $data;
                });
            
            // Enviar datos por POST a la URL configurada
            $rutas = json_decode($rutasEnvio->config_value);

            foreach ($rutas as $ruta) {
                try {
                    $body = [
                        'fecha_envio' => Carbon::now(),
                        'sedes' => $sedes,
                        'numero_principal' => $numeroPrincipal->config_value
                    ];
                    
                    $textoPlano = json_encode($body, JSON_UNESCAPED_UNICODE);
                    $key = hash('sha256', $secretKey->config_value, true);
                    $iv = random_bytes(16);
                    $textoCifrado = openssl_encrypt($textoPlano, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);

                    if ($textoCifrado === false) {
                        Log::error("Error al cifrar los datos de las sedes para enviar a: " . $ruta->nombre_sitio);
                    } else {
                        $hmac = hash_hmac('sha256', $iv . $textoCifrado, $secretKey->config_value, true);

                        $toSend = [
                            'iv' => base64_encode($iv),
                            'data' => base64_encode($textoCifrado),
                            'hmac' => base64_encode($hmac),
                        ];

                        $response = Http::withHeaders([
                            'Content-Type'  => 'application/json'
                        ])->post($ruta->url, $toSend);
                        
                        if ($response->successful()) {
                            Log::info("Información de sedes enviada correctamente a: " . $ruta->nombre_sitio);
                        } else {
                            Log::error("Error al enviar información de sedes a: " . $ruta->nombre_sitio . " - ". $ruta->url . " ERROR: " . $response->body());
                        }
                    }
                } catch (\Throwable $e) {
                    Log::error("Excepción al enviar la información actualizada de las sedes a todos los sitios: " . $e->getMessage());
                }
            }
        }
    }
}
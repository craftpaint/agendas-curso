<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Services\WhatsappService;
use App\Services\CrmService;

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
        $datosUsuario = $this->sendPulseWhatsapp->searchContactByPhone($cliente->telefono_cliente, $agente->id_chatbot_sendpulse);

        if ($agente->email == "mariafernandagallo7@gmail.com"){
            //Corrección de estructura de datos
            $fechaFormateada = date('Y/m/d', strtotime($cita->reserva_cita));
            $partes = explode('-', $cita->rango_horario);
            $horaFormateada = trim($partes[0] ?? '');

            $data = [
                'bot_id' => $agente->id_chatbot_sendpulse,
                'phone' => $cliente->telefono_cliente,
                'template' => [
                    'name' => 'utilidad_confirmacion_cita',
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
                    return false;
                }

                $operadorAsignado = $this->sendPulseWhatsapp->assignOperatorToContact($datosUsuario->data->id, $agente->id_user_sendpulse);
                if (!$operadorAsignado) {
                    Log::error("No se pudo asignar el operador al contacto de Whatsapp");
                    return false;
                }
            } else {
                $contactoCreado = $this->sendPulseWhatsapp->createContactWhatsapp($cliente->telefono_cliente, $cliente->nombre_cliente, [], [], $agente->id_chatbot_sendpulse);
                $TemplateEnviado = $this->sendPulseWhatsapp->sendWhatsappTemplateByPhone($data);
                if (!$contactoCreado || !$TemplateEnviado) {
                    Log::error("No se pudo crear el contacto de Whatsapp y tampoco enviar la plantilla de confirmación de cita al cliente");
                    return false;
                }

                $datosUsuario = $this->sendPulseWhatsapp->searchContactByPhone($cliente->telefono_cliente, $agente->id_chatbot_sendpulse);
                if (!$datosUsuario) {
                    Log::error("No se encontró el contacto de Whatsapp creado.");
                    return false;
                }

                $operadorAsignado = $this->sendPulseWhatsapp->assignOperatorToContact($datosUsuario->data->id, $agente->id_user_sendpulse);
                if (!$operadorAsignado) {
                    Log::error("No se pudo asignar el operador al contacto de Whatsapp");
                    return false;
                }
            }

            // Crear usuario de CRM y crear el trato
            $datosUsuarioCRM = $this->sendPulseCrm->searchContactByExternalContactId($datosUsuario->data->id);
            if (!$datosUsuarioCRM) {
                
            }

        }
    }
}
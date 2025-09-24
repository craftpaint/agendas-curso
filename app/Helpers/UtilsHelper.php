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

        if ($agente->id_chatbot_sendpulse && $agente->id_user_sendpulse && $agente->id_plantilla_sendpulse) {
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
                    return false;
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
                    return false;
                }
            }

            // Crear usuario de CRM y crear el trato
            $datosUsuarioCRM = $this->sendPulseCrm->searchContactByExternalContactId($datosUsuario['data']['id']);
            if ($datosUsuarioCRM) {
                $tratoCreado = $this->sendPulseCrm->createDealCrm($agente->id_user_sendpulse, $cliente->nombre_cliente, $cliente->apellido_cliente, $sede->nombre_sede, $datosUsuarioCRM['data']['id']);
                if (!$tratoCreado) {
                    Log::error("No se pudo crear el trato en el CRM.");
                    return false;
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
                    return false;
                }
            } else {
                $contactoCrmCreado = $this->sendPulseCrm->createContactCrm($cliente->nombre_cliente, $cliente->apellido_cliente, $agente->id_user_sendpulse, $datosUsuario['data']['id']);
                $contactoCrmEncontrado = $this->sendPulseCrm->searchContactByExternalContactId($datosUsuario['data']['id']);
                if (!$contactoCrmCreado || !$contactoCrmEncontrado) {
                    Log::error("No se pudo crear el contacto en el CRM o no se encontró después de crearlo.");
                    return false;
                }

                $messengerAsignado = $this->sendPulseCrm->assignMessengerContactCrm($cliente->telefono_cliente, $agente->id_chatbot_sendpulse, $datosUsuario['data']['id'], $contactoCrmEncontrado['data']['id']);
                if (!$messengerAsignado) {
                    Log::error("No se pudo asignar el chat del bot al contacto de CRM.");
                    return false;
                }

                $tratoCreado = $this->sendPulseCrm->createDealCrm($agente->id_user_sendpulse, $cliente->nombre_cliente, $cliente->apellido_cliente, $sede->nombre_sede, $contactoCrmEncontrado['data']['id']);
                if (!$tratoCreado) {
                    Log::error("No se pudo crear el trato en el CRM.");
                    return false;
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
                    return false;
                }
            }
        }
        return true;
    }
}
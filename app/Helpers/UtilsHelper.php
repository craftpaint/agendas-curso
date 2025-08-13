<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Services\WhatsappService;

class UtilsHelper {

    protected $sendPulseWhatsapp;

    public function __construct(WhatsappService $sendPulseWhatsapp) {
        $this->sendPulseWhatsapp = $sendPulseWhatsapp;
    }

    public function enviarConfirmacionWhatsappCliente($id_cita) {
        $cita = DB::table('tb_cita')->where('id_cita', $id_cita)->first();
        $cliente = DB::table('tb_cliente')->where('id_cliente', $cita->id_cliente)->first();
        $agente = DB::table('users')->where('id', $cita->id_agente_callcenter)->first();
        $sede = DB::table('tb_sede')->where('id_sede', $cita->id_sede)->first();
        $datosUsuario = $this->sendPulseWhatsapp->searchContactByPhone($cliente->telefono_cliente);

        if ($agente->email == "mariafernandagallo7@gmail.com"){
            //Corrección de estructura de datos
            $fechaFormateada = date('Y/m/d', strtotime($cita->reserva_cita));
            $partes = explode('-', $cita->rango_horario);
            $horaFormateada = trim($partes[0] ?? '');

            $data = [
                'bot_id' => env('SENDPULSE_WHATSAPP_BOT_ID'),
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
            if($datosUsuario){
                $this->sendPulseWhatsapp->sendWhatsappTemplateByPhone($data);
            } else {
                $this->sendPulseWhatsapp->createContactWhatsapp($cliente->telefono_cliente, $cliente->nombre_cliente, [], []);
                $this->sendPulseWhatsapp->sendWhatsappTemplateByPhone($data);
            }
        }
    }
}
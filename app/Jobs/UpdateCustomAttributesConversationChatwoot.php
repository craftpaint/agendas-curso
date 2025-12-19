<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Services\ChatwootService;

class UpdateCustomAttributesConversationChatwoot implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $idCita;
    protected $idAgente;

    public function __construct(int $idCita, int $idAgente) {
        $this->idCita = $idCita;
        $this->idAgente = $idAgente;
    }

    public function handle(ChatwootService $chatwootService): void {
        $agentInfo = DB::table('users')
            ->where('users.id', $this->idAgente)
            ->first();

        $cita = DB::table('tb_cita')
            ->where('tb_cita.id_cita', $this->idCita);

        $idConversationChatwoot = $cita->value('id_conversacion_chatwoot');
        $sedeCita = DB::table('tb_sede')
            ->where('tb_sede.id_sede', $cita->value('id_sede'))
            ->value('nombre_sede');
        
        $ciudadSedeCita = DB::table('tb_sede')
            ->where('tb_sede.id_sede', $cita->value('id_sede'))
            ->join('tb_ciudad', 'tb_ciudad.id_ciudad', '=', 'tb_sede.id_ciudad')
            ->value('tb_ciudad.nombre');

        $fechaCita = explode(' ', $cita->value('reserva_cita'), 2)[0];
        $horaCita = explode('-', $cita->value('rango_horario'), 2)[0];
        $arrayCodigosCita = json_decode($cita->value('codigos_comparendo'), true) ?? [];
        $codigosCita = collect($arrayCodigosCita)->pluck('value')->implode(' - ');
        $sedeparticipante = false;

        if (str_contains($ciudadSedeCita, 'Bogotá')) {
            $sedeparticipante = true;
        }

        $bodyAttributes = [
            'custom_attributes' => [
                'link_de_la_cita_en_el_motor' => env('APP_URL') . '/dashboard/citas/edit/' . $this->idCita,
                'sede' => $sedeCita,
                'ciudad_sede' => $ciudadSedeCita,
                'fecha' => $fechaCita,
                'hora' => $horaCita,
                'codigos' => $codigosCita,
                'whatsapp_business_phone_number_id' => $agentInfo->id_whatsapp_business_phone_number,
                'enviar_imagen_sorteo' => $sedeparticipante
            ]
        ];

        if (!empty($idConversationChatwoot)) {
            $chatwootCustomAttributesActualizado = $chatwootService->updateCustomAtributesConversation($idConversationChatwoot, $bodyAttributes);
            if (!$chatwootCustomAttributesActualizado) {
                Log::error("No se pudo actualizar los atributos personalizados de la conversación en Chatwoot con ID " . $idConversationChatwoot);
            }
        }
    }
}

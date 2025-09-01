<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Services\CrmService;
use App\Services\WhatsappService;

class UpdateOperatorDealCrm implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $idCita;
    protected $idAgente;

    public function __construct(int $idCita, int $idAgente) {
        $this->idCita = $idCita;
        $this->idAgente = $idAgente;
    }

    public function handle(CrmService $crmService, WhatsappService $whatsappService): void {
        $idDealCrm = DB::table('tb_cita')
            ->where('tb_cita.id_cita', $this->idCita)
            ->value('tb_cita.id_trato_sendpulse');

        $phoneClient = DB::table('tb_cita')
            ->join('tb_cliente', 'tb_cita.id_cliente', '=', 'tb_cliente.id_cliente')
            ->where('tb_cita.id_cita', $this->idCita)
            ->value('tb_cliente.telefono_cliente');
        
        $agentInfo = DB::table('users')
            ->where('users.id', $this->idAgente)
            ->first();

        if (!empty($idDealCrm) && !empty($agentInfo->id_user_sendpulse) && !empty($agentInfo->id_chatbot_sendpulse)) {
            $datosUsuario = $whatsappService->searchContactByPhone($phoneClient, $agentInfo->id_chatbot_sendpulse);
            if (!$datosUsuario) {
                Log::error("No se encontró el usuario en WhatsApp.");
            }

            $whatsappActualizado = $whatsappService->assignOperatorToContact($datosUsuario['data']['id'], $agentInfo->id_user_sendpulse);
            if (!$whatsappActualizado) {
                Log::error("No se pudo actualizar el operador del contacto en WhatsApp.");
            }

            $dealActualizado = $crmService->updateResponsibleDealCrm($idDealCrm, $agentInfo->id_user_sendpulse);
            if (!$dealActualizado) {
                Log::error("No se pudo actualizar el responsable del trato en CRM.");
            }
        }
    }
}

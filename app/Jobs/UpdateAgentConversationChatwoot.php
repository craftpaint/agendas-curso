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

class UpdateAgentConversationChatwoot implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $idCita;
    protected $idAgente;

    public function __construct(int $idCita, int $idAgente) {
        $this->idCita = $idCita;
        $this->idAgente = $idAgente;
    }

    public function handle(ChatwootService $chatwootService): void {
        $idConversationChatwoot = DB::table('tb_cita')
            ->where('tb_cita.id_cita', $this->idCita)
            ->value('tb_cita.id_conversacion_chatwoot');
        
        $agentInfo = DB::table('users')
            ->where('users.id', $this->idAgente)
            ->first();
        
        if (!empty($idConversationChatwoot) && !empty($agentInfo->id_agente_chatwoot) && !empty($agentInfo->id_equipo_agentes_chatwoot)) {
            $chatwootAgentActualizado = $chatwootService->updateConversationAgent($idConversationChatwoot, $agentInfo->id_agente_chatwoot, $agentInfo->id_equipo_agentes_chatwoot);
            if (!$chatwootAgentActualizado) {
                Log::error("No se pudo actualizar el agente de la conversación en Chatwoot con ID " . $idConversationChatwoot);
            }
        }
    }
}

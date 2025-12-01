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

class UpdateLabelConversationChatwoot implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $idCita;
    protected $idEstadoVerificado;

    public function __construct(int $idCita, int $idEstadoVerificado) {
        $this->idCita = $idCita;
        $this->idEstadoVerificado = $idEstadoVerificado;
    }

    public function handle(ChatwootService $chatwootService): void {
        $idConversationChatwoot = DB::table('tb_cita')
            ->where('tb_cita.id_cita', $this->idCita)
            ->value('tb_cita.id_conversacion_chatwoot');

        $estadoVerificado = DB::table('tb_estado')
            ->where('tb_estado.id_estado', $this->idEstadoVerificado)
            ->value('tb_estado.nombre_estado');
        
        if (!empty($idConversationChatwoot) && !empty($estadoVerificado)) {
            $chatwootLabelActualizado = $chatwootService->updateLabelConversation($idConversationChatwoot, $estadoVerificado);
            if (!$chatwootLabelActualizado) {
                Log::error("No se pudo actualizar la etiqueta de la conversación en Chatwoot con ID " . $idConversationChatwoot);
            }
        }
    }
}

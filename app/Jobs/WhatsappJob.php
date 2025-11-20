<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Helpers\UtilsHelper;
use App\Services\CrmService;

class WhatsappJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $id_cita;
    protected $citas_agendadas;

    public function __construct(int $id_cita, bool $citas_agendadas) {
        $this->id_cita = $id_cita;
        $this->citas_agendadas = $citas_agendadas;
    }

    public function handle(UtilsHelper $utilsHelper, CrmService $crmService): void {
        $whatsappEnviado = $utilsHelper->enviarConfirmacionWhatsappCliente($this->id_cita);

        if ($whatsappEnviado['exito'] == true) {
            if ($whatsappEnviado['metodo'] == 'SendPulse WhatsApp' && $this->citas_agendadas) {
                // Se consulta el ID del deal en CRM relacionado a la cita
                $idDealCrm = DB::table('tb_cita')
                    ->where('id_cita', $this->id_cita)
                    ->value('id_trato_sendpulse');

                // Se consulta el Step para duplicado
                $step_sendpulse = DB::table('tb_estado')
                    ->where('nombre_estado', 'Duplicado')
                    ->value('id_step_sendpulse');

                if ($step_sendpulse && $idDealCrm) {
                    $dealActualizado = $crmService->updateStepDealCrm($idDealCrm, $step_sendpulse);

                    if (!$dealActualizado) {
                        Log::error("No se pudo actualizar el paso del trato en CRM para el estado Duplicado.");
                    }
                }
            }
        } else {
            Log::error("No se pudo enviar el mensaje de confirmación de Whatsapp al cliente o no se ha creado el trato de CRM.");
        }
    }
}

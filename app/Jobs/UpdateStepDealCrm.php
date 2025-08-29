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

class UpdateStepDealCrm implements ShouldQueue{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $idDealCrm;
    protected $step_sendpulse;

    public function __construct(int $idDealCrm, int $step_sendpulse) {
        $this->idDealCrm = $idDealCrm;
        $this->step_sendpulse = $step_sendpulse;
    }

    public function handle(CrmService $crmService): void {
        $dealActualizado = $crmService->updateStepDealCrm($this->idDealCrm, $this->step_sendpulse);

        if (!$dealActualizado) {
            Log::error("No se pudo actualizar el paso del trato en CRM.");
        }
    }
}

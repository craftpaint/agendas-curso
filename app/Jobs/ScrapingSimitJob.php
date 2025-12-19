<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Services\ScrapingService;


class ScrapingSimitJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    protected $id_cita;
    protected $doc_cliente;
    protected $metodo_actual;

    public function __construct(int $id_cita, string $doc_cliente, int $metodo_actual) {
        $this->id_cita = $id_cita;
        $this->doc_cliente = $doc_cliente;
        $this->metodo_actual = $metodo_actual;
    }

    public function handle(ScrapingService $scrapingService): void {
        $scraping = null;
        $origen_scraping = null;

        switch ($this->metodo_actual) {
            case 1:
                $origen_scraping = 'Node';
                $scraping = $scrapingService->ScrapingNode($this->id_cita, $this->doc_cliente);
                break;
            case 2:
                $origen_scraping = 'N8N';
                $scraping = $scrapingService->ScrapingN8N($this->id_cita, $this->doc_cliente);
                break;
        }

        if ($scraping) {
            DB::table('tb_cita')
                ->where('id_cita', $this->id_cita)
                ->update([
                    'origen_scraping' => $origen_scraping,
                    'metadata_simit' => $scraping['Data']
                ]);
        } else {
            $attempt = $this->attempts();
            $delays = [
                1 => 7200,    // 2 horas
                2 => 21600,   // 6 horas
            ];

            $delay = $delays[$attempt] ?? 0;

            if ($delay > 0) {
                Log::warning("Scraping falló para cita {$this->id_cita}, intento #{$attempt}. Reintentando en {$delay} segundos.");
                $this->release($delay);
            } else {
                Log::error("Scraping falló definitivamente para cita {$this->id_cita} después de {$attempt} intentos.");
            }
        }
    }
}

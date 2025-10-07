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

        switch ($this->metodo_actual) {
            case 1:
                $scraping = $scrapingService->ScrapingNode($this->id_cita, $this->doc_cliente);
                break;
            case 2:
                Log::info("Se enviaría al Agente ChatGPT para realizar el Scraping.");
                break;
        }

        if ($scraping) {
            DB::table('tb_cita')
                ->where('id_cita', $this->id_cita)
                ->update([
                    'metadata_simit' => $scraping['Data']
                ]);
        } else {
            Log::error("El scraping para la cita " . $this->id_cita . " falló, por lo tanto se vuelve a agregar a la cola.");
            ScrapingSimitJob::dispatch($this->id_cita, $this->doc_cliente, $this->metodo_actual)->onQueue('Scraping');
        }
    }
}

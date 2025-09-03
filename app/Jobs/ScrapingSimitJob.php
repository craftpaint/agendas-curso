<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Services\ScrapingService;

class ScrapingSimitJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $id_cita;
    protected $doc_cliente;

    public function __construct(int $id_cita, string $doc_cliente) {
        $this->id_cita = $id_cita;
        $this->doc_cliente = $doc_cliente;
    }

    public function handle(ScrapingService $scrapingService): void {
        $scraping = $scrapingService->ScrapingNode($this->id_cita, $this->doc_cliente);

        if ($scraping) {
            Log::info("El scraping ha funcionado con exito: ", $scraping);
            $scrapingService->VerificarInformacion($this->id_cita, $scraping);
        } else {
            Log::error("El scraping para el documento " . $this->doc_cliente . " falló, por lo tanto se vuelve a agregar a la cola.");
            ScrapingSimitJob::dispatch($this->id_cita, $this->doc_cliente)->onQueue('Scraping');
        }
    }
}

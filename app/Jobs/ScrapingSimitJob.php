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

    public function __construct(int $id_cita, string $doc_cliente) {
        $this->id_cita = $id_cita;
        $this->doc_cliente = $doc_cliente;
    }

    public function handle(ScrapingService $scrapingService): void {
        $scraping = $scrapingService->ScrapingNode($this->id_cita, $this->doc_cliente);

        if ($scraping) {
            DB::table('tb_cita')
                ->where('id_cita', $this->id_cita)
                ->update([
                    'url_simit_imagen' => $scraping['Data']['urlImagen'],
                    'metadata_simit' => $scraping['Data']
                ]);
        } else {
            Log::error("El scraping para la cita " . $this->id_cita . " falló, por lo tanto se vuelve a agregar a la cola.");
            ScrapingSimitJob::dispatch($this->id_cita, $this->doc_cliente)->onQueue('Scraping');
        }
    }
}

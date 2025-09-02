<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ScrapingService {
    public function ScrapingNode($recipientDocument) {
        try {
            $response = Http::withHeaders([
                'Content-Type'  => 'application/json'
            ])->get(env('SCRAPING_RUTA_BASE') . 'scrape/' . $recipientDocument);

            if ($response->successful()) {
                return $response->json();
            } else {
                Log::error("Error al realizar el Scraping en Node: " . $response->body());
                return false;
            }
        } catch (\Throwable $e) {
            Log::error("Excepción al realizar el Scraping: " . $e->getMessage());
            return false;
        }
    }
}
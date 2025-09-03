<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ScrapingService {
    public function ScrapingNode($recipientIdCita, $recipientDocument) {
        try {
            $response = Http::withHeaders([
                'Content-Type'  => 'application/json'
            ])->get(env('SCRAPING_RUTA_BASE') . 'scrape/' . $recipientDocument . '/' . $recipientIdCita);

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

    public function VerificarInformacion($id_cita, $scraping) {
        $data = $scraping['Data'];
        $cita = DB::table('tb_cita')
            ->where('id_cita', $id_cita)
            ->first();

        try {
            $datosFiltradosInfraccion = [];
            $datosFiltradosNotificacion = [];

            // Se filtra primero por el tipo de infracción
            foreach ($data['datosTablaFormateados'] as $index => &$registro) {
                $partes_infraccion = explode(" ", $registro[4]);

                if ($cita->codigos_comparendo->value == $partes_infraccion[0]) {
                    $datosFiltradosInfraccion[] = $registro;
                }
            }

            if (count($datosFiltradosInfraccion) > 1) {
                foreach ($datosFiltradosInfraccion as $index => $registro) {
                    // Se agrega la validación de las fechas para elegir el más reciente
                }
            }
        } catch (\Throwable $e) {
            Log::error("Excepción al realizar el Scraping: " . $e->getMessage());
            return false;
        }
    }
}
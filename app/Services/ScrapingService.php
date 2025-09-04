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
            $datosFiltradosInfraccion = $this->filtrarRegistrosInfraccion($cita, $data);

            if (!$datosFiltradosInfraccion) {
                Log::info("Ocurrió un error al intentar filtrar por el tipo de infracción.");
                return false;
            }

            Log::info("Registros encontrados con el mismo código de comparendo", ['Data' => $datosFiltradosInfraccion]);
            $cantidadResultadosInfraccion = count($datosFiltradosInfraccion);

            switch ($cantidadResultadosInfraccion) {
                case $cantidadResultadosInfraccion > 1:
                    $datosFiltradosNotificacion = $this->filtrarRegistrosNotificacion($cita, $datosFiltradosInfraccion);

                    if (!$datosFiltradosNotificacion) {
                        Log::info("Ocurrió un error al intentar filtrar por fecha de notificación más reciente.");
                        return false;
                    }

                    break;
                case $cantidadResultadosInfraccion == 1:
                    Log::info("Se verificó en el SIMIT el comparendo.");
                    break;
                default:
                    Log::info("No se encontró coincidencias con el código de comparendo registrado.");
                    break;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error("Excepción al realizar el Scraping: " . $e->getMessage());
            return false;
        }
    }

    private function filtrarRegistrosInfraccion($cita, $data) {
        $resultado = [];

        try {
            if (!empty($cita->codigos_comparendo)) {
                // Se extraen los códigos de comparendos relacionados en la cita
                $codigos_comparendo = json_decode($cita->codigos_comparendo);
                $valores_codigos = array_map(function($obj) { 
                    return $obj->value; 
                }, $codigos_comparendo);

                foreach ($data['datosTablaFormateados'] as $index => &$registro) {
                    $partes_infraccion = explode(" ", $registro[4]);

                    if (json_last_error() === JSON_ERROR_NONE && in_array($partes_infraccion[0], $valores_codigos)) {
                        $resultado[] = $registro;
                    }
                }
            }

            return $resultado;
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function filtrarRegistrosNotificacion($cita, $registros) {
        $resultado = [];
        $registrosNoAplica = [];
        $fechaMasReciente = null;
        $registroMasReciente = null;

        // Se extraen los códigos de comparendos relacionados en la cita
        $codigos_comparendo = json_decode($cita->codigos_comparendo);
        $valores_codigos = array_map(function($obj) { 
            return $obj->value;
        }, $codigos_comparendo);

        try {
            foreach ($valores_codigos as $indexCodigos => $codigo) {
                foreach ($registros as $indexRegistros => $registro) {
                    $partes_infraccion = explode(" ", $registro[4]);

                    if ($registro[1] != "No aplica") {
                        $fechaRegistro = strtotime($registro[1]);

                        if (($fechaMasReciente == null && $partes_infraccion[0] == $codigo) || ($fechaRegistro > $fechaMasReciente && $partes_infraccion[0] == $codigo)) {
                            $fechaMasReciente = $fechaRegistro;
                            $registroMasReciente = $registro;
                        }
                    } else {
                        $registrosNoAplica[] = $registro;
                    }
                }
                $resultado[] = $registroMasReciente;
                $fechaMasReciente = null;
                $registroMasReciente = null;
            }

            $resultado = $resultado + $registrosNoAplica;
            return $resultado;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
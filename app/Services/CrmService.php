<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CrmService {
    public function createContactCrm($recipientFirstName, $recipientLastName, $recipientResponsibleId, $recipientWhatsappContactId) {
        $accessToken = $this->getAccessToken();

        if (!$accessToken) {
            Log::error("Error al obtener token de acceso de SendPulse");
            return false;
        }

        $data = [
            "responsibleId" => $recipientResponsibleId,
            "firstName" => $recipientFirstName,
            "lastName" => $recipientLastName,
            "externalContactId" => $recipientWhatsappContactId
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type'  => 'application/json'
            ])->post(env('SENDPULSE_CRM_RUTA_BASE') . '/contacts/create', $data);

            if ($response->successful()) {
                return true;
            } else {
                Log::error("Error al crear el nuevo contacto en CRM de Whatsapp: " . $response->body());
                return false;
            }
        } catch (\Throwable $e) {
            Log::error("Excepción al crear el contacto de CRM con SendPulse: " . $e->getMessage());
            return false;
        }
    }

    public function createDealCrm($recipientResponsibleId, $recipientFirstName, $recipientLastName, $recipientSedeName, $recipientContactCrmId) {
        $accessToken = $this->getAccessToken();

        if (!$accessToken) {
            Log::error("Error al obtener token de acceso de SendPulse");
            return false;
        }

        try {
            $dataPipeline = $this->searchPipeline();
            if (!$dataPipeline) {
                Log::error("Error al obtener el pipeline de CRM");
                return false;
            }

            $stepId = null;
            $dataPipeline->steps.forEach(function($step) {
                if ($step->name === 'Agendado') {
                    $stepId = $step->id;
                }
            });

            $data = [
                "pipelineId" => env('SENDPULSE_CRM_PIPELINE_ID'),
                "stepId" => $stepId,
                "responsibleId" => $recipientResponsibleId,
                "name" => $recipientFirstName . ' ' . $recipientLastName . ' - ' .$recipientSedeName,
                "price" => 1,
                "currency" => "COP",
                "contactId" => [
                    $recipientContactCrmId
                ]
            ];

            
        } catch (\Throwable $th) {
            Log::error("Excepción al crear el trato de CRM con SendPulse: " . $e->getMessage());
            return false;
        }
    }

    public function searchPipeline() {
        $accessToken = $this->getAccessToken();

        if (!$accessToken) {
            Log::error("Error al obtener token de acceso de SendPulse");
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type'  => 'application/json'
            ])->get(env('SENDPULSE_CRM_RUTA_BASE') . '/pipelines/' . env('SENDPULSE_CRM_PIPELINE_ID'));

            if ($response->successful()) {
                return $response->json();
            } else {
                Log::error("Error al consultar el pipeline de CRM con SendPulse: " . $response->body());
                return false;
            }
        } catch (\Throwable $e) {
            Log::error("Excepción al buscar el pipeline de CRM con SendPulse: " . $e->getMessage());
            return false;
        }
    }

    public function searchContactByExternalContactId($externalContactId) {
        $accessToken = $this->getAccessToken();

        if (!$accessToken) {
            Log::error("Error al obtener token de acceso de SendPulse");
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type'  => 'application/json'
            ])->get(env('SENDPULSE_CRM_RUTA_BASE') . '/contacts/external/' . $externalContactId);

            if ($response->successful()) {
                return $response->json();
            } else {
                Log::error("Error al consultar al contacto por número de contacto externo: " . $response->body());
                return false;
            }
        } catch (\Throwable $e) {
            Log::error("Excepción al consultar el usuario de CRM con SendPulse: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene el token de acceso de SendPulse usando client_id y client_secret.
     *
     * @return string|null
     */
    private function getAccessToken() {
        $clientId     = env('SENDPULSE_CLIENT_ID');
        $clientSecret = env('SENDPULSE_CLIENT_SECRET');

        $response = Http::post('https://api.sendpulse.com/oauth/access_token', [
            'grant_type'    => 'client_credentials',
            'client_id'     => $clientId,
            'client_secret' => $clientSecret
        ]);

        if ($response->successful() && isset($response->json()['access_token'])) {
            return $response->json()['access_token'];
        }
        Log::error("Error obteniendo token de SendPulse: " . $response->body());
        return null;
    }
}
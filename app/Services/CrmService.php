<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

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
                Log::error("Error al crear el nuevo contacto en CRM: " . $response->body());
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
            foreach ($dataPipeline['data']['steps'] as $step) {
                if ($step['name'] === 'Agendado') {
                    $stepId = $step['id'];
                    break;
                }
            }

            if (!$stepId) {
                Log::error("No se encontró el step 'Agendado' en el pipeline");
                return false;
            }

            $fecha = Carbon::now();
            $mes = $fecha->format('m');
            $año = $fecha->format('Y');

            $data = [
                "pipelineId" => env('SENDPULSE_CRM_PIPELINE_ID'),
                "stepId" => $stepId,
                "responsibleId" => $recipientResponsibleId,
                "name" => $recipientFirstName . ' ' . $recipientLastName . ' - ' .$recipientSedeName . ' - ' . $mes . '/' . $año,
                "price" => 1,
                "currency" => "COP",
                "contactId" => [
                    $recipientContactCrmId
                ]
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type'  => 'application/json'
            ])->post(env('SENDPULSE_CRM_RUTA_BASE') . '/deals', $data);

            if ($response->successful()) {
                return $response->json();
            } else {
                Log::error("Error al crear el nuevo trato con el contacto: " . $response->body());
                return false;
            }
        } catch (\Throwable $e) {
            Log::error("Excepción al crear el trato de CRM con SendPulse: " . $e->getMessage());
            return false;
        }
    }

    public function updateStepDealCrm($recipientDealId, $recipientStepId) {
        $accessToken = $this->getAccessToken();

        if (!$accessToken) {
            Log::error("Error al obtener token de acceso de SendPulse");
            return false;
        }

        try {
            $responseDeal = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type'  => 'application/json'
            ])->get(env('SENDPULSE_CRM_RUTA_BASE') . '/deals/' . $recipientDealId);

            if ($responseDeal->successful()) {
                $dataDeal = $responseDeal->json();

                $dataDealUpdate = [
                    "pipelineId" => $dataDeal['data']['pipelineId'],
                    "status" => $dataDeal['data']['status'],
                    "stepId" => $recipientStepId,
                    "responsibleId" => $dataDeal['data']['responsibleId'],
                    "name" => $dataDeal['data']['name'],
                    "price" => $dataDeal['data']['price'],
                    "currency" => $dataDeal['data']['currency'],
                    "sourceId" => $dataDeal['data']['sourceId'],
                    "order" => $dataDeal['data']['order']
                ];

                $responseUpdateDeal = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type'  => 'application/json'
                ])->put(env('SENDPULSE_CRM_RUTA_BASE') . '/deals/' . $recipientDealId, $dataDealUpdate);

                if ($responseUpdateDeal->successful()) {
                    return true;
                } else {
                    Log::error("Error al actualizar el trato en CRM: " . $responseUpdateDeal->body());
                    return false;
                }
            } else {
                Log::error("Error al consultar el trato del CRM para actualizar: " . $responseDeal->body());
                return false;
            }
        } catch (\Throwable $e) {
            Log::error("Excepción al actualizar el paso del trato en CRM con SendPulse: " . $e->getMessage());
            return false;
        }
    }

    public function updateResponsibleDealCrm($recipientDealId, $recipientResponsibleId) {
        $accessToken = $this->getAccessToken();

        if (!$accessToken) {
            Log::error("Error al obtener token de acceso de SendPulse");
            return false;
        }

        try {
            $responseDeal = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type'  => 'application/json'
            ])->get(env('SENDPULSE_CRM_RUTA_BASE') . '/deals/' . $recipientDealId);

            if ($responseDeal->successful()) {
                $dataDeal = $responseDeal->json();

                $dataDealUpdate = [
                    "pipelineId" => $dataDeal['data']['pipelineId'],
                    "status" => $dataDeal['data']['status'],
                    "stepId" => $dataDeal['data']['stepId'],
                    "responsibleId" => $recipientResponsibleId,
                    "name" => $dataDeal['data']['name'],
                    "price" => $dataDeal['data']['price'],
                    "currency" => $dataDeal['data']['currency'],
                    "sourceId" => $dataDeal['data']['sourceId'],
                    "order" => $dataDeal['data']['order']
                ];

                $responseUpdateDeal = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type'  => 'application/json'
                ])->put(env('SENDPULSE_CRM_RUTA_BASE') . '/deals/' . $recipientDealId, $dataDealUpdate);

                if ($responseUpdateDeal->successful()) {
                    return true;
                } else {
                    Log::error("Error al actualizar el trato en CRM: " . $responseUpdateDeal->body());
                    return false;
                }
            } else {
                Log::error("Error al consultar el trato del CRM para actualizar: " . $responseDeal->body());
                return false;
            }

        } catch (\Throwable $e) {
            Log::error("Excepción al actualizar el responsable del trato en CRM con SendPulse: " . $e->getMessage());
            return false;
        }
    }

    public function assignMessengerContactCrm($recipientPhoneNumber, $recipientBotId, $recipientContactWhatsappId, $recipientContactCrmId) {
        $accessToken = $this->getAccessToken();

        if (!$accessToken) {
            Log::error("Error al obtener token de acceso de SendPulse");
            return false;
        }

        $data = [
            "typeId" => 5,
            "login" => $recipientPhoneNumber,
            "botId" => $recipientBotId,
            "contactId" => $recipientContactWhatsappId,
            "chatbotUrl" => env('SENDPULSE_CHATBOT_URL_BASE') . $recipientBotId . '/contacts/all/' . $recipientContactWhatsappId,
            "isMainChatbot" => true
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type'  => 'application/json'
            ])->post(env('SENDPULSE_CRM_RUTA_BASE') . '/contacts/' . $recipientContactCrmId . '/messengers', $data);

            if ($response->successful()) {
                return true;
            } else {
                Log::error("Error al asignar el chat del bot al contacto de CRM: " . $response->body());
                return false;
            }
        } catch (\Throwable $e) {
            Log::error("Excepción al asignar el chat del bot al contacto con el CRM: " . $e->getMessage());
            return false;
        }
    }

    public function assignDealToContact($recipientDealId, $recipientContactCrmId) {
        $accessToken = $this->getAccessToken();

        if (!$accessToken) {
            Log::error("Error al obtener token de acceso de SendPulse");
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type'  => 'application/json'
            ])->post(env('SENDPULSE_CRM_RUTA_BASE') . '/deals/' . $recipientDealId . '/contacts/' . $recipientContactCrmId);

            if ($response->successful()) {
                return true;
            } else {
                Log::error("Error al asignar el trato al contacto de CRM: " . $response->body());
                return false;
            }
        } catch (\Throwable $e) {
            Log::error("Excepción al asignar trato a contacto en CRM con SendPulse: " . $e->getMessage());
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
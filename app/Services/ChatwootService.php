<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ChatwootService {
    public function __construct() {
        $this->baseUrl = env('CHATWOOT_RUTA_BASE');
        $this->apiKey = env('CHATWOOT_API_KEY');
        $this->accountId = env('CHATWOOT_ACCOUNT_ID');
    }

    public function updateConversationAgent($recipientIdConversation, $recipientIdAgent, $recipientTeamIdAgent) {
        try {

            $body = [
                'assignee_id' => $recipientIdAgent,
                'team_id' => $recipientTeamIdAgent
            ];

            $response = Http::withHeaders([
                'api_access_token' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . "api/v1/accounts/" . $this->accountId . "/conversations/" . $recipientIdConversation . "/assignments", $body);

            if ($response->successful()) {
                return true;
            } else {
                Log::error("Error al actualizar la conversación en Chatwoot con ID " . $recipientIdConversation . ". Respuesta: " . $response->body());
                return false;
            }
        } catch (\Throwable $e) {
            Log::error("Error al actualizar la conversación en Chatwoot con ID " . $recipientIdConversation . " y error: " . $e->getMessage());
            return false;
        }
    }

    public function updateLabelConversation($recipientIdConversation, $recipientNameStatus) {
        try {
            $labelName = str_replace(' ', '_', strtolower($recipientNameStatus));

            $body = [
                'labels' => [$labelName]
            ];

            $response = Http::withHeaders([
                'api_access_token' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . "api/v1/accounts/" . $this->accountId . "/conversations/" . $recipientIdConversation . "/labels", $body);

            if ($response->successful()) {
                return true;
            } else {
                Log::error("Error al actualizar la etiqueta de la conversación en Chatwoot con ID " . $recipientIdConversation . ". Respuesta: " . $response->body());
                return false;
            }
        } catch (\Throwable $e) {
            Log::error("Error al actualizar la etiqueta de la conversación en Chatwoot con ID " . $recipientIdConversation . " y error: " . $e->getMessage());
            return false;
        }
    }
}
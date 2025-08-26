<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class WhatsappService {
    public function createContactWhatsapp($recipientPhone, $recipientName, $recipientTags = [], $recipientVariables = [], $chatbot) {
        $accessToken = $this->getAccessToken();

        if (!$accessToken) {
            Log::error("Error al obtener token de acceso de SendPulse");
            return false;
        }

        $data = [
            "phone" => $recipientPhone,
            "name" => $recipientName,
            "bot_id" => $chatbot,
            "tags" => $recipientTags,
            "variables" => $recipientVariables
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type'  => 'application/json'
            ])->post(env('SENDPULSE_WHATSAPP_RUTA_BASE') . '/contacts', $data);

            if ($response->successful()) {
                return true;
            } else {
                Log::error("Error al crear el nuevo contacto de Whatsapp: " . $response->body());
                return false;
            }
        } catch (\Exception $e) {
            Log::error("Excepción al crear el contacto con SendPulse: " . $e->getMessage());
            return false;
        }
    }

    public function sendWhatsappTemplateByPhone($data) {
        $accessToken = $this->getAccessToken();

        if (!$accessToken) {
            Log::error("Error al obtener token de acceso de SendPulse");
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type'  => 'application/json'
            ])->post(env('SENDPULSE_WHATSAPP_RUTA_BASE') . '/contacts/sendTemplateByPhone', $data);

            if ($response->successful()) {
                return true;
            } else {
                Log::error("Error al enviar el mensaje por Whatsapp: " . $response->body());
                return false;
            }
        } catch (\Exception $e) {
            Log::error("Excepción al enviar el mensaje de Whatsapp con SendPulse: " . $e->getMessage());
            return false;
        }
    }

    public function searchContactByPhone($phone, $chatbot) {
        $accessToken = $this->getAccessToken();

        if (!$accessToken) {
            Log::error("Error al obtener token de acceso de SendPulse");
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type'  => 'application/json'
            ])->get(env('SENDPULSE_WHATSAPP_RUTA_BASE') . '/contacts/getByPhone?phone=' . $phone . '&bot_id=' . $chatbot);
            
            if ($response->successful()) {
                return $response->json();
            } else {
                Log::error("Error al consultar al contacto por número de telefono: " . $response->body());
                return false;
            }
        } catch (\Throwable $e) {
            Log::error("Excepción al consultar el usuario de Whatsapp con SendPulse: " . $e->getMessage());
            return false;
        }
    }

    public function assignOperatorToContact($contactId, $operatorId) {
        $accessToken = $this->getAccessToken();

        if (!$accessToken) {
            Log::error("Error al obtener token de acceso de SendPulse");
            return false;
        }

        $data = [
            "contact_id" => $contactId,
            "operator_id" => $operatorId
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type'  => 'application/json'
            ])->post(env('SENDPULSE_WHATSAPP_RUTA_BASE') . '/contacts/operators/assign', $data);

            if ($response->successful()) {
                return true;
            } else {
                Log::error("Error al asignar el operador al contacto: " . $response->body());
                return false;
            }
        } catch (\Throwable $e) {
            Log::error("Excepción al asignar el operador al contacto: " . $e->getMessage());
            return false;
        }
    }

    public function webhookWhatsapp(Request $request) {
        $data = $request->all();

        try {
            $ultimaCita = DB::table('tb_cita')
                ->where('id_whatsapp_sendpulse', $data[0]['contact']['id'])
                ->orderBy('created_at', 'desc')
                ->first();
            
            if ($ultimaCita) {
                DB::table('tb_cita')
                    ->where('id_cita', $ultimaCita->id_cita)
                    ->update(['notificado_chatbot' => true]);
            }
        } catch (\Throwable $e) {
            Log::error("Excepción al procesar el webhook de Whatsapp: " . $e->getMessage());
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
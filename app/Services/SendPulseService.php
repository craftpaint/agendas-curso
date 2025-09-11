<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendPulseService
{
    /**
     * Envía un correo utilizando una plantilla de SendPulse.
     *
     * @param string $recipientEmail Correo del destinatario.
     * @param string $recipientName  Nombre del destinatario.
     * @param string $subject        Asunto del correo.
     * @param array  $templateVariables Variables para la plantilla.
     * @return bool
     */
    public function sendEmailConfirmacion($recipientEmail, $recipientName, $subject, array $templateVariables)
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            Log::error("Error al obtener token de acceso de SendPulse");
            return false;
        }

        // Armar el payload usando la plantilla
        $data = [
            "email" => [
                "subject"  => $subject,
                "template" => [
                    "id"        => 16046,
                    "variables" => $templateVariables,
                ],
                "from"     => [
                    "name"  => config('mail.from.name'),
                    "email" => config('mail.from.address'),
                ],
                "to"       => [
                    [
                        "name"  => $recipientName,
                        "email" => $recipientEmail,
                    ]
                ],
            ]
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type'  => 'application/json'
            ])->post('https://api.sendpulse.com/smtp/emails', $data);

            if ($response->successful()) {
                return true;
            } else {
                Log::error("Error enviando correo con plantilla: " . $response->body());
                return false;
            }
        } catch (\Exception $e) {
            Log::error("Excepción al enviar correo con SendPulse: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene el token de acceso de SendPulse usando client_id y client_secret.
     *
     * @return string|null
     */
    private function getAccessToken()
    {
        $clientId     = env('SENDPULSE_CLIENT_ID');
        $clientSecret = env('SENDPULSE_CLIENT_SECRET');

        $response = Http::post('https://api.sendpulse.com/oauth/access_token', [
            'grant_type'    => 'client_credentials',
            'client_id'     => $clientId,
            'client_secret' => $clientSecret,
        ]);

        if ($response->successful() && isset($response->json()['access_token'])) {
            return $response->json()['access_token'];
        }

        Log::error("Error obteniendo token de SendPulse: " . $response->body());
        return null;
    }
}

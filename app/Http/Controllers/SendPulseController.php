<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendPulseController extends Controller
{
    /**
     * Envía un correo usando la API SMTP de SendPulse.
     */
    public function sendEmailConfirmacion(Request $request)
    {
        // Obtén el token de acceso para la API de SendPulse.
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return response()->json(['message' => 'Error al obtener token de acceso'], 500);
        }

        // Construir el cuerpo del correo según la documentación de SendPulse.
        $data = [
            "email" => [
                // La versión HTML se debe enviar codificada en Base64.
                "html"    => base64_encode("<p>Este es un correo de prueba usando SendPulse</p>"),
                "text"    => "Este es un correo de prueba usando SendPulse",
                "subject" => "Correo de prueba SendPulse",
                "from"    => [
                    "name"  => "Club del Conductor",
                    "email" => "hola@mg.clubdelconductor.com"
                ],
                // Recibe el correo del destinatario desde el request o especifica uno fijo.
                "to"      => [
                    [
                        "name"  => "Destinatario",
                        "email" => $request->get('email')
                    ]
                ]
                // Puedes agregar otros parámetros como "cc", "bcc", "attachments" si lo requieres.
            ]
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type'  => 'application/json'
            ])->post('https://api.sendpulse.com/smtp/emails', $data);

            if ($response->successful()) {
                return response()->json(['message' => 'Correo enviado correctamente']);
            } else {
                Log::error("Error enviando correo: " . $response->body());
                return response()->json([
                    'message' => 'Error al enviar el correo',
                    'error'   => $response->body()
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error("Excepción al enviar correo: " . $e->getMessage());
            return response()->json(['message' => 'Excepción al enviar el correo'], 500);
        }
    }

    /**
     * Obtiene el token de acceso de SendPulse usando client_id y client_secret.
     */
    private function getAccessToken()
    {
        // Asegúrate de definir SENDPULSE_CLIENT_ID y SENDPULSE_CLIENT_SECRET en tu archivo .env
        $clientId     = env('SENDPULSE_CLIENT_ID');
        $clientSecret = env('SENDPULSE_CLIENT_SECRET');

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post('https://api.sendpulse.com/oauth/access_token', [
            'grant_type'    => 'client_credentials',
            'client_id'     => $clientId,
            'client_secret' => $clientSecret,
        ]);

        Log::info('Client ID: ' . $clientId);
        Log::info('Client Secret: ' . $clientSecret);

        if ($response->successful() && isset($response->json()['access_token'])) {
            return $response->json()['access_token'];
        }

        Log::error("Error obteniendo token de acceso SendPulse: " . $response->body());
        return null;
    }
}

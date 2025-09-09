<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Eluceo\iCal\Domain\Entity\Event;
use Eluceo\iCal\Domain\Entity\Calendar;
use Eluceo\iCal\Domain\ValueObject\SingleDay;
use Eluceo\iCal\Domain\ValueObject\Date;
use Eluceo\iCal\Domain\ValueObject\Timestamp;
use Eluceo\iCal\Domain\ValueObject\DateTime;
use Eluceo\iCal\Domain\ValueObject\Location;
use Eluceo\iCal\Domain\ValueObject\Organizer;
use Eluceo\iCal\Domain\ValueObject\Uri;
use Eluceo\iCal\Presentation\Factory\CalendarFactory;
use DateTimeImmutable;

class SendPulseService {
    /**
     * Envía un correo utilizando una plantilla de SendPulse.
     *
     * @param string $recipientEmail Correo del destinatario.
     * @param string $recipientName  Nombre del destinatario.
     * @param string $subject        Asunto del correo.
     * @param array  $templateVariables Variables para la plantilla.
     * @return bool
     */
    public function sendEmailConfirmacion($recipientEmail, $recipientName, $subject, array $templateVariables) {
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
                    "id"        => env('MAIL_PLANTILLA_ID'),
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

    private function generateIcsContent($eventData) {
        $date = $eventData['reserva_cita'];
        $start_time = explode("-", $eventData['rango_horario'])[0]; 
        $end_time = explode("-", $eventData['rango_horario'])[1];

        $event = new Event();
        $event->setSummary("Curso comparendo - Cita")
            ->setDescription("Cita para el curso de comparendos.");
        
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

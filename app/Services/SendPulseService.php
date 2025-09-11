<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Eluceo\iCal\Domain\Entity\Event;
use Eluceo\iCal\Domain\Entity\Calendar;
use Eluceo\iCal\Domain\ValueObject\SingleDay;
use Eluceo\iCal\Domain\ValueObject\Date;
use Eluceo\iCal\Domain\ValueObject\Timestamp;
use Eluceo\iCal\Domain\ValueObject\TimeSpan;
use Eluceo\iCal\Domain\ValueObject\DateTime;
use Eluceo\iCal\Domain\ValueObject\Location;
use Eluceo\iCal\Domain\ValueObject\Organizer;
use Eluceo\iCal\Domain\ValueObject\EmailAddress;
use Eluceo\iCal\Presentation\Factory\CalendarFactory;
use Eluceo\iCal\Domain\ValueObject\Alarm;
use Eluceo\iCal\Domain\ValueObject\Alarm\DisplayAction;
use Eluceo\iCal\Domain\ValueObject\Alarm\RelativeTrigger;
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
    public function sendEmailConfirmacion($recipientEmail, $recipientName, $subject, $doc_cliente, array $templateVariables) {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            Log::error("Error al obtener token de acceso de SendPulse");
            return false;
        }

        $icsContent = $this->generateIcsContent($templateVariables);
        
        if (!$icsContent) {
            Log::error("Error al generar contenido ICS");
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
                "attachments" => [
                        "evento.ics"    => $icsContent,
                ]
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
        try {
            $event = new Event();
            $event->setSummary("Curso comparendo - Cita")
                ->setDescription("Recuerda estar 30 minutos antes de tu cita.");

            // Extraer datos
            $date = $eventData['reserva_cita'];
            $times = explode(" - ", $eventData['rango_horario']);
            $start_time = trim($times[0]); 
            $end_time = trim($times[1]);
            $location = $eventData['nombre_sede'] . " - " . $eventData['direccion_sede'];

            // Convertir horas a formato 24h
            $start_time_24h = date('H:i', strtotime($start_time));
            $end_time_24h = date('H:i', strtotime($end_time));
            
            // Crear objetos Carbon para las fechas completas
            $startDateTime = Carbon::createFromFormat('Y-m-d', $date)->setTimeFromTimeString($start_time_24h);
            $endDateTime = Carbon::createFromFormat('Y-m-d', $date)->setTimeFromTimeString($end_time_24h);

            // Verificar si la hora de fin es anterior a la de inicio (cruce de medianoche)
            if ($endDateTime <= $startDateTime) {
                $endDateTime->addDay();
            }

            // Convertir a DateTimeImmutable
            $startDateTimeImmutable = \DateTimeImmutable::createFromMutable($startDateTime);
            $endDateTimeImmutable = \DateTimeImmutable::createFromMutable($endDateTime);

            // Crear objetos DateTime para eluceo/ical
            $eventStartDateTime = new DateTime($startDateTimeImmutable, true);
            $eventEndDateTime = new DateTime($endDateTimeImmutable, true);

            $event->setOccurrence(
                new TimeSpan($eventStartDateTime, $eventEndDateTime)
            );
            
            $event->setLocation(new Location($location));

            $organizer = new Organizer(
                new EmailAddress(config('mail.from.address')),
                config('mail.from.name')
            );
            $event->setOrganizer($organizer);

            // Agregar alarma para 30 minutos antes del evento
            $alarma30Min = new Alarm(
                new DisplayAction('Recordatorio: Curso comparendo - Cita en 30 minutos'),
                new RelativeTrigger(new \DateInterval('PT30M'))
            );
            $event->addAlarm($alarma30Min);

            // Crear calendario
            $calendar = new Calendar([$event]);
            $componentFactory = new CalendarFactory();
            $calendarComponent = $componentFactory->createCalendar($calendar);

            return (string) $calendarComponent;
        } catch (\Throwable $e) {
            Log::error("Error generando contenido ICS: " . $e->getMessage());
            Log::error("Datos usados: ", [
                'reserva_cita' => $eventData['reserva_cita'],
                'rango_horario' => $eventData['rango_horario']
            ]);
            return null;
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

<?php

namespace App\Mail;


use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

use Mailgun\Mailgun;

class FormularioCompletadoNotifiInterno extends Mailable
{
    use Queueable, SerializesModels;

    public $formData;
    /**
     * Create a new message instance.
     */
    public function __construct($formData)
    {
        $this->formData = $formData;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        // Personaliza el asunto del correo con los datos del cliente
        $subject = 'CLUBY: otra cita agendada para ' . $this->formData['nombre_cliente'] . ' ' . $this->formData['apellido_cliente'];

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'email.formulario-completado-notifi-interno',
            with: ['formData' => $this->formData],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}

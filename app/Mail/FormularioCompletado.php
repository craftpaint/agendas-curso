<?php

namespace App\Mail;


use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

use Mailgun\Mailgun;

class FormularioCompletado extends Mailable
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
        $subject = $this->formData['nombre_cliente'] . ' tu cita ha sido agendada en ' . $this->formData['nombre_sede'];

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        if ($this->formData['nombre_servicio'] == 'CDA') {
            return new Content(
                view: 'email.formulario-completado-cda',
                with: ['formData' => $this->formData],
            );
        } else {
            return new Content(
                view: 'email.formulario-completado',
                with: ['formData' => $this->formData],
            );
        }
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

<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Resumen de alertas de Recursos humanos para los administradores. */
class AlertasRrhhMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public array $resultado,
    ) {}

    public function envelope(): Envelope
    {
        $t = $this->resultado['totales'];

        return new Envelope(
            subject: "Recursos humanos: {$t['critico']} urgentes y {$t['aviso']} avisos",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.rrhh-alertas',
            with: [
                'alertas' => $this->resultado['alertas'],
                'totales' => $this->resultado['totales'],
                'categorias' => $this->resultado['categorias'],
                'url' => url('/rrhh'),
            ],
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

final class ResumenGastosDiario extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Collection $vencidos,
        public readonly Collection $proximos,
        public readonly int $mes,
        public readonly int $anio,
        public readonly float $totalPendiente,
    ) {}

    public function envelope(): Envelope
    {
        $asunto = 'Resumen de Gastos';

        if ($this->vencidos->count() > 0) {
            $asunto = "⚠️ Tienes {$this->vencidos->count()} pago(s) vencido(s)";
        } elseif ($this->proximos->count() > 0) {
            $asunto = "📌 Tienes {$this->proximos->count()} pago(s) próximo(s)";
        }

        return new Envelope(subject: $asunto);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.resumen-gastos');
    }
}

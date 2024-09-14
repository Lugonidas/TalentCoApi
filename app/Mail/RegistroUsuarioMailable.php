<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RegistroUsuarioMailable extends Mailable
{
    use Queueable, SerializesModels;

    public $usuario;
    public $verificationUrl; // Agrega esta propiedad si necesitas una URL de verificación

    /**
     * Create a new message instance.
     */
    public function __construct($usuario, $verificationUrl = null)
    {
        $this->usuario = $usuario;
        $this->verificationUrl = $verificationUrl;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->from(env('MAIL_FROM_ADDRESS'))
                    ->subject('Bienvenido a la Plataforma TalentCo')
                    ->view('emails.registro')
                    ->with([
                        'usuario' => $this->usuario,
                        'verificationUrl' => $this->verificationUrl,
                    ]);
    }
}

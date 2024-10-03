<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MensajeEnviado implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $mensaje;
    public $usuario;

    public function __construct($mensaje, $usuario)
    {
        $this->mensaje = $mensaje;
        $this->usuario = $usuario;
    }

    public function broadcastOn()
    {
        return new Channel('chat.' . $this->mensaje->id_conversacion);
    }

    public function broadcastAs()
    {
        return 'MensajeEnviado';
    }
}

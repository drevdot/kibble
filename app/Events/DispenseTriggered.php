<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow; // <-- Importación corregida
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DispenseTriggered implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $mac_address;
    public $action; //'food' or 'water'

    public function __construct($mac_address, $action)
    {
        $this->mac_address = $mac_address;
        $this->action = $action;
    }

    public function broadcastOn(): array
    {
        // Crea un canal de transmisión basado en la dirección MAC de la máquina
        return [
            new Channel('machine.' . $this->mac_address),
        ];
    }
    
    public function broadcastWith(): array
    {
        return [
            'action' => $this->action,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
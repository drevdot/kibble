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
        // Reverb/Pusher solo permite [A-Za-z0-9_\-=@,.;] -> ':' es inválido.
        // 'AA:BB:CC' -> 'aa-bb-cc'. El frontend y el ESP32 deben usar la misma regla.
        $safeMac = str_replace(':', '-', strtolower($this->mac_address));

        // Crea un canal de transmisión basado en la dirección MAC de la máquina
        return [
            new Channel('machine.' . $safeMac),
        ];
    }
    
    public function broadcastWith(): array
    {
        return [
            'action' => $this->action,
            'mac_address' => $this->mac_address,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
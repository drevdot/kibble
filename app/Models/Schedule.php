<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $fillable = ['machine_id', 'type', 'scheduled_time', 'is_active'];

    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }
}
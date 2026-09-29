<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $fillable = ['machine_id', 'trigger_time', 'dispense_type', 'portion_grams', 'is_active'];

    public function machine() {
        return $this->belongsTo(Machine::class);
    }
}
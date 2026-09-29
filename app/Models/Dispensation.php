<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dispensation extends Model
{
    protected $fillable = ['machine_id', 'dispense_type', 'trigger_source'];

    public function machine() {
        return $this->belongsTo(Machine::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Machine extends Model
{
    protected $fillable = ['user_id', 'mac_address', 'alias', 'food_level_pct', 'water_level_pct'];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function dispensations() {
        return $this->hasMany(Dispensation::class);
    }

    public function schedules() {
        return $this->hasMany(Schedule::class);
    }
}

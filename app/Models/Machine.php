<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Machine extends Model
{
    protected $fillable = ['name', 'water_level', 'food_level', 'is_online'];

    public function dispensations()
    {
        return $this->hasMany(Dispensation::class);
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }
}
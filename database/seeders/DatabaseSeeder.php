<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = \App\Models\User::factory()->create([
        'name' => 'Administrador',
        'email' => 'admin@kibble.com',
        'password' => bcrypt('password'),
    ]);

    $machine = \App\Models\Machine::create([
        'user_id' => $user->id,
        'mac_address' => 'AA:BB:CC:DD:EE:FF',
        'alias' => 'Dispensador Principal',
        'food_level_pct' => 75,
        'water_level_pct' => 40,
    ]);

    \App\Models\Dispensation::create([
        'machine_id' => $machine->id,
        'dispense_type' => 'food',
        'trigger_source' => 'manual',
    ]);
    }
}

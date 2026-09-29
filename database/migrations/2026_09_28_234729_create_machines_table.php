<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('machines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('mac_address')->unique();//Para identificar el dispositivo
            $table->string('alias')->nullable(); //Nombre de la mascota
            $table->integer('food_level_pct')->default(0); //Porcentaje del ultrasonico para comida
            $table->integer('water_level_pct')->default(0); //Porcentaje del ultrasonico para agua
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('machines');
    }
};

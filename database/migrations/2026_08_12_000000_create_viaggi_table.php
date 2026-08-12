<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('viaggi', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->enum('gestione', ['tour_operator', 'interno']);
            $table->string('destinazione');
            $table->date('data_partenza');
            $table->date('data_rientro');
            $table->string('locandina')->nullable();
            $table->json('trasporti')->nullable();
            $table->json('sistemazioni')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('viaggi');
    }
};

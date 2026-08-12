<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cliente_documenti', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clienti')->cascadeOnDelete();
            $table->string('tipo')->nullable();
            $table->string('numero')->nullable();
            $table->date('scadenza')->nullable();
            $table->string('nome_originale');
            $table->string('percorso');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('dimensione')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cliente_documenti');
    }
};

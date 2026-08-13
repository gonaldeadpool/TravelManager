<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('viaggio_tappe_raccolta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('viaggio_id')->constrained('viaggi')->cascadeOnDelete();
            $table->string('nome');
            $table->time('orario');
            $table->timestamps();
            $table->unique(['viaggio_id', 'nome']);
        });

        Schema::create('viaggio_tappa_cliente', function (Blueprint $table) {
            $table->foreignId('viaggio_id')->constrained('viaggi')->cascadeOnDelete();
            $table->foreignId('tappa_id')->constrained('viaggio_tappe_raccolta')->cascadeOnDelete();
            $table->foreignId('cliente_id')->constrained('clienti')->cascadeOnDelete();
            $table->primary(['viaggio_id', 'cliente_id']);
            $table->unique(['tappa_id', 'cliente_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('viaggio_tappa_cliente');
        Schema::dropIfExists('viaggio_tappe_raccolta');
    }
};

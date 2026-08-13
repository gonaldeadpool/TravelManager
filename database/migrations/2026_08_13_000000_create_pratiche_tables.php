<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pratiche', function (Blueprint $table) {
            $table->id();
            $table->foreignId('viaggio_id')->constrained('viaggi')->restrictOnDelete();
            $table->decimal('totale', 10, 2)->default(0);
            $table->decimal('acconto', 10, 2)->default(0);
            $table->date('data_acconto')->nullable();
            $table->decimal('saldo', 10, 2)->default(0);
            $table->date('data_saldo')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('cliente_pratica', function (Blueprint $table) {
            $table->foreignId('pratica_id')->constrained('pratiche')->cascadeOnDelete();
            $table->foreignId('cliente_id')->constrained('clienti')->restrictOnDelete();
            $table->primary(['pratica_id', 'cliente_id']);
        });

        Schema::create('pratica_documenti', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pratica_id')->constrained('pratiche')->cascadeOnDelete();
            $table->string('nome_originale');
            $table->string('percorso');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('dimensione')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pratica_documenti');
        Schema::dropIfExists('cliente_pratica');
        Schema::dropIfExists('pratiche');
    }
};
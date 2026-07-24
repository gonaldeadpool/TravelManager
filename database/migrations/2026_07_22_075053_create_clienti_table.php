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
        Schema::create('clienti', function (Blueprint $table) {

            $table->id();

            $table->string('nome');
            $table->string('cognome');

            $table->string('telefono')->nullable();
            $table->string('email')->nullable();

            $table->string('codice_fiscale')->nullable();

            $table->date('data_nascita')->nullable();

            $table->timestamps();
        });
    }

};

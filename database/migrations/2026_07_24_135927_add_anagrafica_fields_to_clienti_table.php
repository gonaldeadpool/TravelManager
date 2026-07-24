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
    Schema::table('clienti', function (Blueprint $table) {

        $table->string('luogo_nascita')->nullable();

        $table->string('cellulare')->nullable();

        $table->string('indirizzo')->nullable();

        $table->string('cap', 10)->nullable();

        $table->string('citta')->nullable();

        $table->string('provincia', 2)->nullable();

        $table->string('nazione')->nullable();

        $table->text('note')->nullable();

    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clienti', function (Blueprint $table) {

            $table->dropColumn([
                'luogo_nascita',
                'cellulare',
                'indirizzo',
                'cap',
                'citta',
                'provincia',
                'nazione',
                'note'
            ]);

        });
    }
};

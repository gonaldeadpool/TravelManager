<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('viaggi', function (Blueprint $table) {
            $table->string('tipologia')->default('viaggio')->after('nome');
            $table->decimal('prezzo', 10, 2)->nullable()->after('data_rientro');
            $table->unsignedInteger('minimo_partecipanti')->nullable()->after('prezzo');
        });

        Schema::table('viaggi', function (Blueprint $table) {
            $table->dropColumn('gestione');
        });
    }

    public function down(): void
    {
        Schema::table('viaggi', function (Blueprint $table) {
            $table->enum('gestione', ['tour_operator', 'interno'])->nullable()->after('nome');
        });

        Schema::table('viaggi', function (Blueprint $table) {
            $table->dropColumn(['tipologia', 'prezzo', 'minimo_partecipanti']);
        });
    }
};
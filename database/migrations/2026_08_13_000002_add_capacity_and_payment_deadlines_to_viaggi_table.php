<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('viaggi', function (Blueprint $table) {
            $table->unsignedInteger('massimo_partecipanti')->nullable()->after('minimo_partecipanti');
            $table->date('data_acconto')->nullable()->after('massimo_partecipanti');
            $table->decimal('importo_minimo_acconto', 10, 2)->nullable()->after('data_acconto');
            $table->date('data_saldo')->nullable()->after('importo_minimo_acconto');
        });
    }

    public function down(): void
    {
        Schema::table('viaggi', function (Blueprint $table) {
            $table->dropColumn(['massimo_partecipanti', 'data_acconto', 'importo_minimo_acconto', 'data_saldo']);
        });
    }
};
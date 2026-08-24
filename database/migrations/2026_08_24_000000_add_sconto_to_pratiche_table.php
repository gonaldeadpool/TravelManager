<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pratiche', function (Blueprint $table) {
            $table->decimal('sconto', 10, 2)->default(0)->after('totale');
        });
    }

    public function down(): void
    {
        Schema::table('pratiche', function (Blueprint $table) {
            $table->dropColumn('sconto');
        });
    }
};

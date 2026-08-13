<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('viaggi', function (Blueprint $table) {
            if (! Schema::hasColumn('viaggi', 'prezzi_cabine')) {
                $table->json('prezzi_cabine')->nullable()->after('trasporti');
            }
        });
    }

    public function down(): void
    {
        Schema::table('viaggi', function (Blueprint $table) {
            if (Schema::hasColumn('viaggi', 'prezzi_cabine')) {
                $table->dropColumn('prezzi_cabine');
            }
        });
    }
};

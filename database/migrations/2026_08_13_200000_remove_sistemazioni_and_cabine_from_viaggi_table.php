<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('viaggi', function ($table) {
            if (Schema::hasColumn('viaggi', 'sistemazioni')) {
                $table->dropColumn('sistemazioni');
            }
            if (Schema::hasColumn('viaggi', 'prezzi_cabine')) {
                $table->dropColumn('prezzi_cabine');
            }
        });
    }

    public function down(): void
    {
        Schema::table('viaggi', function ($table) {
            if (! Schema::hasColumn('viaggi', 'sistemazioni')) {
                $table->json('sistemazioni')->nullable();
            }
            if (! Schema::hasColumn('viaggi', 'prezzi_cabine')) {
                $table->json('prezzi_cabine')->nullable();
            }
        });
    }
};

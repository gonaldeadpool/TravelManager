<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('viaggi', function (Blueprint $table) {
            $table->json('prezzi_cabine')->nullable()->after('sistemazioni');
            $table->unsignedTinyInteger('eta_gratuita')->nullable()->after('prezzi_cabine');
        });
    }

    public function down(): void
    {
        Schema::table('viaggi', function (Blueprint $table) {
            $table->dropColumn(['prezzi_cabine', 'eta_gratuita']);
        });
    }
};

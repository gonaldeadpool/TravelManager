<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('viaggi', function (Blueprint $table) {
            $table->decimal('quota_fissa', 10, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('viaggi', function (Blueprint $table) {
            $table->dropColumn('quota_fissa');
        });
    }
};
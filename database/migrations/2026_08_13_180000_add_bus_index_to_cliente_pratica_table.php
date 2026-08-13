<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cliente_pratica', function (Blueprint $table) {
            $table->unsignedInteger('posto_bus')->nullable()->after('posto');
            $table->index(['pratica_id', 'posto_bus', 'posto']);
        });
    }

    public function down(): void
    {
        Schema::table('cliente_pratica', function (Blueprint $table) {
            $table->dropIndex(['pratica_id', 'posto_bus', 'posto']);
            $table->dropColumn('posto_bus');
        });
    }
};

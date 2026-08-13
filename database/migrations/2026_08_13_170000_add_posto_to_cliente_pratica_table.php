<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cliente_pratica', function (Blueprint $table) {
            $table->unsignedInteger('posto')->nullable()->after('cliente_id');
            $table->index(['pratica_id', 'posto']);
        });
    }

    public function down(): void
    {
        Schema::table('cliente_pratica', function (Blueprint $table) {
            $table->dropIndex(['pratica_id', 'posto']);
            $table->dropColumn('posto');
        });
    }
};

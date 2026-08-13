<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cliente_pratica', function (Blueprint $table) {
            $table->boolean('gratuito')->default(false)->after('cliente_id');
        });
    }

    public function down(): void
    {
        Schema::table('cliente_pratica', function (Blueprint $table) {
            $table->dropColumn('gratuito');
        });
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pratiche', function (Blueprint $table) {
            $table->string('cabina')->nullable();
            $table->decimal('totale_quote', 10, 2)->default(0);
            $table->decimal('assicurazione_annullamento', 10, 2)->default(0);
            $table->decimal('supplemento_singola', 10, 2)->default(0);
        });

        DB::table('pratiche')->select(['id', 'totale', 'sconto'])->orderBy('id')->chunkById(500, function ($pratiche) {
            foreach ($pratiche as $pratica) {
                DB::table('pratiche')->where('id', $pratica->id)->update([
                    'totale_quote' => $pratica->totale,
                    'totale' => max(0, (float) $pratica->totale - (float) $pratica->sconto),
                ]);
            }
        });
    }

    public function down(): void
    {
        DB::table('pratiche')->select(['id', 'totale_quote', 'assicurazione_annullamento', 'supplemento_singola'])->orderBy('id')->chunkById(500, function ($pratiche) {
            foreach ($pratiche as $pratica) {
                DB::table('pratiche')->where('id', $pratica->id)->update([
                    'totale' => (float) $pratica->totale_quote
                        + (float) $pratica->assicurazione_annullamento
                        + (float) $pratica->supplemento_singola,
                ]);
            }
        });

        Schema::table('pratiche', function (Blueprint $table) {
            $table->dropColumn(['cabina', 'totale_quote', 'assicurazione_annullamento', 'supplemento_singola']);
        });
    }
};
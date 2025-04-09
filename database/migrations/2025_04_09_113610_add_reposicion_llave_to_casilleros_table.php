<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration 
{
    public function up(): void
    {
        Schema::table('casilleros', function ($table) {
            $table->boolean('reposicion_llave')->default(false)->after('fecha_entrega_llave');
        });
    }

    public function down(): void
    {
        Schema::table('casilleros', function ($table) {
            $table->dropColumn('reposicion_llave');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration 
{
    public function up(): void
    {
        Schema::table('personals', function (Blueprint $table) {
            $table->index(['nombre', 'apellido_paterno', 'apellido_materno'], 'nombre_apellidos_index');
        });
    }

    public function down(): void
    {
        Schema::table('personals', function (Blueprint $table) {
            $table->dropIndex('nombre_apellidos_index');
        });
    }
};

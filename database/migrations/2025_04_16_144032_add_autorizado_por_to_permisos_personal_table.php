<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permisos_personal', function (Blueprint $table) {
            $table->foreignId('autorizado_por')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete(); // O ->cascadeOnDelete() según tu lógica
        });
    }

    public function down(): void
    {
        Schema::table('permisos_personal', function (Blueprint $table) {
            //
        });
    }
};

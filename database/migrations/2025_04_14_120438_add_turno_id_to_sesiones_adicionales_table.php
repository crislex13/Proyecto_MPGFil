<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sesiones_adicionales', function (Blueprint $table) {
            $table->foreignId('turno_id')->nullable()->constrained('turnos')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sesiones_adicionales', function (Blueprint $table) {
            //
        });
    }
};

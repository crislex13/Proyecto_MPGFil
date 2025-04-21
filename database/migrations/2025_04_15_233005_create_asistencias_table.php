<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asistencias', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('asistible_id');
            $table->string('asistible_type'); // cliente o personal
            $table->timestamp('hora_entrada')->nullable();
            $table->timestamp('hora_salida')->nullable();
            $table->enum('estado', ['puntual', 'atrasado', 'acceso_denegado'])->nullable();
            $table->text('observacion')->nullable();
            $table->timestamps();

            $table->index(['asistible_id', 'asistible_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asistencias');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('turnos', function (Blueprint $table) {
            // Quitamos solo el campo, ignorando la foreign
            $table->dropColumn('personal_id');
        });
    }

    public function down(): void
    {
        Schema::table('turnos', function (Blueprint $table) {
            $table->unsignedBigInteger('personal_id')->nullable();
            $table->foreign('personal_id')->references('id')->on('personals')->onDelete('cascade');
        });
    }
};

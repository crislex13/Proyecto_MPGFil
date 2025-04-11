<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration 
{
    public function up(): void
    {
        Schema::table('turnos', function (Blueprint $table) {
            $table->unsignedBigInteger('personal_id')->nullable()->after('estado');
            $table->foreign('personal_id')->references('id')->on('personals')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('turnos', function (Blueprint $table) {
            $table->dropForeign(['personal_id']);
            $table->dropColumn('personal_id');
        });
    }
};

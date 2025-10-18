<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('personals', function (Blueprint $table) {
            $table->string('telefono')->nullable()->change();
            $table->string('correo')->nullable()->change();
            $table->string('apellido_materno')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('personals', function (Blueprint $table) {
            $table->string('telefono')->nullable(false)->change();
            $table->string('correo')->nullable(false)->change();
            $table->string('apellido_materno')->nullable(false)->change();
        });
    }
};
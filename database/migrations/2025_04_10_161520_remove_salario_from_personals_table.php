<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personals', function (Blueprint $table) {
            $table->dropColumn('salario');
        });
    }

    public function down(): void
    {
        Schema::table('personals', function (Blueprint $table) {
            $table->decimal('salario', 10, 2)->default(0); // o los valores que tenía originalmente
        });
    }
};

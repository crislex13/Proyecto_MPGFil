<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('planes', function (Blueprint $table) {
            $table->boolean('ingresos_ilimitados')->default(false); // <- sin "after"
        });
    }

    public function down(): void
    {
        Schema::table('planes', function (Blueprint $table) {
            $table->dropColumn('ingresos_ilimitados');
        });
    }
};
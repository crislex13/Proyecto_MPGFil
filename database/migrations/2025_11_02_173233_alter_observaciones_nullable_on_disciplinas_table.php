<?php

// database/migrations/xxxx_xx_xx_xxxxxx_alter_observaciones_nullable_on_disciplinas_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('disciplinas', function (Blueprint $table) {
            $table->text('observaciones')->nullable()->change();
        });
    }
    public function down(): void
    {
        Schema::table('disciplinas', function (Blueprint $table) {
            $table->text('observaciones')->nullable(false)->change();
        });
    }
};
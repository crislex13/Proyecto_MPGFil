<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('sesiones_adicionales', function (Blueprint $table) {
            $table->unsignedBigInteger('plan_cliente_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('sesiones_adicionales', function (Blueprint $table) {
            //
        });
    }
};

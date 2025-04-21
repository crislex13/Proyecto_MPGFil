<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::table('clientes', function (Blueprint $table) {
        $table->unsignedBigInteger('modificado_por')->nullable()->after('registrado_por');
        $table->foreign('modificado_por')->references('id')->on('users')->nullOnDelete();
    });
}

public function down()
{
    Schema::table('clientes', function (Blueprint $table) {
        $table->dropForeign(['modificado_por']);
        $table->dropColumn('modificado_por');
    });
}
};

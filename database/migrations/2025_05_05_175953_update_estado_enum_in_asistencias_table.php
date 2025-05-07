<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateEstadoEnumInAsistenciasTable extends Migration
{
    public function up()
    {
        DB::statement("ALTER TABLE asistencias MODIFY estado ENUM('puntual', 'atrasado', 'permiso', 'acceso_denegado', 'falta')");
    }

    public function down()
    {
        DB::statement("ALTER TABLE asistencias MODIFY estado ENUM('puntual', 'atrasado', 'permiso', 'acceso_denegado')");
    }
}
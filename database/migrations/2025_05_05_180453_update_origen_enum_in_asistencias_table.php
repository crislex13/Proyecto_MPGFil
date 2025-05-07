<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class UpdateOrigenEnumInAsistenciasTable extends Migration
{
    public function up()
    {
        DB::statement("ALTER TABLE asistencias MODIFY origen ENUM('manual', 'biometrico', 'automatico')");
    }

    public function down()
    {
        DB::statement("ALTER TABLE asistencias MODIFY origen ENUM('manual', 'biometrico')");
    }
}

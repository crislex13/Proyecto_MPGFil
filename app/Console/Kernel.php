<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        \App\Console\Commands\BloquearPlanesPorDeuda::class,
        \App\Console\Commands\ProcesarAsistenciasBiometrico::class,
        \App\Console\Commands\RegistrarFaltasClientes::class,
        \App\Console\Commands\RegistrarFaltasSesionesAdicionales::class,
        \App\Console\Commands\RegistrarFaltasPersonal::class,
        \App\Console\Commands\RegistrarPermisosComoAsistencia::class,
    ];

    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('bloquear:planes-deuda')->dailyAt('06:00');
        $schedule->command('clientes:desbloquear-si-pagaron')->dailyAt('07:10');
        $schedule->command('app:procesar-asistencias-biometrico')->everyMinute();
        $schedule->command('asistencias:registrar-faltas')->dailyAt('23:59');
        $schedule->command('clientes:registrar-faltas')->dailyAt('23:59');
        $schedule->command('sesiones:registrar-faltas')->dailyAt('23:59');

    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }

}

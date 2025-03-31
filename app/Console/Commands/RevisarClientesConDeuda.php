<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Clientes;
use Illuminate\Support\Carbon;

class RevisarClientesConDeuda extends Command
{
    protected $signature = 'clientes:bloquear-si-deben';
    protected $description = 'Bloquea clientes con deuda después de 5 días hábiles de vencimiento';

    public function handle()
    {
        $clientes = Clientes::where('saldo', '>', 0)
            ->whereDate('fecha_final', '<=', now()->subWeekdays(5))
            ->where('bloqueado_por_deuda', false)
            ->get();

        foreach ($clientes as $cliente) {
            $cliente->update(['bloqueado_por_deuda' => true]);
            $this->info("Cliente {$cliente->nombre} bloqueado por deuda.");
        }

        $this->info('Revisión completa.');
    }
}

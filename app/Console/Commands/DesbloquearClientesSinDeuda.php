<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Clientes;
use App\Notifications\ClienteDesbloqueado;

class DesbloquearClientesSinDeuda extends Command
{
    protected $signature = 'clientes:desbloquear-si-pagaron';
    protected $description = 'Desbloquea automáticamente a los clientes que ya pagaron su deuda';

    public function handle()
    {
        $clientes = Clientes::where('bloqueado_por_deuda', true)
            ->where('saldo', '<=', 0)
            ->get();

            foreach ($clientes as $cliente) {
                $cliente->update(['bloqueado_por_deuda' => false]);
                
                // Notificar por correo
                if ($cliente->correo) {
                    $cliente->notify(new ClienteDesbloqueado());
                }
            
                $this->info("✅ Cliente {$cliente->nombre} ha sido desbloqueado y notificado.");
            }

        $this->info('Revisión de desbloqueo completada.');
    }
}


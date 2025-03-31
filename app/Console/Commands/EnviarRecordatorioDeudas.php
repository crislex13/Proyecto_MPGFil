<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Clientes;
use App\Notifications\RecordatorioPagoNotification;

class EnviarRecordatorioDeudas extends Command
{
    protected $signature = 'clientes:recordatorio-deudas';
    protected $description = 'Envía recordatorios de pago a clientes con deudas';

    public function handle()
    {
        $clientes = Clientes::where('saldo', '>', 0)->get();

        foreach ($clientes as $cliente) {
            if ($cliente->correo) {
                $cliente->notify(new RecordatorioPagoNotification());
                $this->info("Correo enviado a: {$cliente->correo}");
            }
        }
    }
}
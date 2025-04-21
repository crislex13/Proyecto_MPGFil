<?php

namespace App\Filament\Resources\ClientesResource\Pages;

use App\Filament\Resources\ClientesResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Clientes;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Filament\Notifications\Notification;

class CreateClientes extends CreateRecord
{
    protected static string $resource = ClientesResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (isset($data['foto'])) {
            $data['foto'] = str_replace('public/', '', $data['foto']);
        }

        $data['registrado_por'] = auth()->id();

        return $data;
    }

    protected function afterCreate(): void
    {
        $cliente = $this->record;

        $primerNombre = explode(' ', $cliente->nombre)[0];
        $usuario = $primerNombre . '_' . $cliente->ci;

        $existe = User::where('ci', $cliente->ci)->first();
        if ($existe) {
            $cliente->update(['user_id' => $existe->id]);
            return;
        }

        $passwordPlano = \Carbon\Carbon::parse($cliente->fecha_de_nacimiento)->format('d-m-Y');

        $user = User::create([
            'name' => $cliente->nombre . ' ' . $cliente->apellido_paterno,
            'ci' => $cliente->ci,
            'foto' => $cliente->foto,
            'telefono' => $cliente->telefono,
            'email' => $usuario . '@cliente.bo',
            'password' => bcrypt($passwordPlano),
            'estado' => 'activo',
        ]);

        $user->assignRole('cliente');

        $cliente->update(['user_id' => $user->id]);

        Notification::make()
            ->title('🆕 Usuario creado')
            ->body("Usuario: **{$usuario}**\nContraseña: **{$passwordPlano}**")
            ->success()
            ->send();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
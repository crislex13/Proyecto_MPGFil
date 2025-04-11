<?php

namespace App\Filament\Resources\CasilleroResource\Pages;

use App\Filament\Resources\CasilleroResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Carbon\Carbon;

class EditCasillero extends EditRecord
{
    protected static string $resource = CasilleroResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['fecha_entrega_llave'])) {
            $data['fecha_final_llave'] = Carbon::parse($data['fecha_entrega_llave'])->addDays(29)->toDateString();
        }

        return $data;
    }

}

<?php

namespace App\Filament\Resources\PlanClienteResource\Pages;

use App\Filament\Resources\PlanClienteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPlanCliente extends EditRecord
{
    protected static string $resource = PlanClienteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\PlanClienteResource\Pages;

use App\Filament\Resources\PlanClienteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Pages\Actions\CreateAction;
class ListPlanClientes extends ListRecords
{
    protected static string $resource = PlanClienteResource::class;

    
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Registrar Plan')
        ];
    }
}

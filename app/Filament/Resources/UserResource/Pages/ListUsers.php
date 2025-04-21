<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Pages\Actions\CreateAction;
class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Registrar Usuario')
        ];
    }
}

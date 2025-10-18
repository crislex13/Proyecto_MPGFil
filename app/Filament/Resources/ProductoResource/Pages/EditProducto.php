<?php

namespace App\Filament\Resources\ProductoResource\Pages;

use App\Filament\Resources\ProductoResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Pages\Actions\Action;
use Illuminate\Database\Eloquent\Model;

class EditProducto extends EditRecord
{
    protected static string $resource = ProductoResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('toggleActivo')
                ->label(fn(Model $record) => $record->activo ? 'Desactivar producto' : 'Activar producto')
                ->icon(fn(Model $record) => $record->activo ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                ->color(fn(Model $record) => $record->activo ? 'danger' : 'success')
                ->requiresConfirmation()
                ->visible(fn() => auth()->user()?->hasRole('admin'))
                ->action(function (Model $record) {
                    $record->activo = !$record->activo;
                    $record->save();

                    $this->notify(
                        'success',
                        $record->activo ? '✅ Producto activado correctamente.' : '⚠️ Producto desactivado correctamente.'
                    );
                }),
        ];
    }

    protected function hasDeleteAction(): bool
    {
        return false;
    }
}

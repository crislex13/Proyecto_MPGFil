<?php

namespace App\Filament\Resources\ProductosResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;

class LotesProductoRelationManager extends RelationManager
{
    protected static string $relationship = 'lotes';

    protected static ?string $title = 'Lotes del Producto';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('fecha_ingreso')->label('Ingreso')->date()->sortable(),
                TextColumn::make('fecha_vencimiento')->label('Vence')->date()->sortable(),
                TextColumn::make('stock_unidades')->label('Stock (u)')->sortable(),
                TextColumn::make('stock_paquetes')->label('Stock (paq)')->sortable(),
                TextColumn::make('precio_unitario')->label('Precio/U')->money('BOB'),
                TextColumn::make('precio_paquete')->label('Precio/Paq')->money('BOB'),
                BadgeColumn::make('es_perecedero')
                    ->label('Perecedero')
                    ->colors([
                        'primary' => fn ($state) => $state,
                        'gray' => fn ($state) => !$state,
                    ])
                    ->formatStateUsing(fn ($state) => $state ? 'Sí' : 'No'),
            ])
            ->emptyStateHeading('Sin lotes registrados')
            ->emptyStateDescription('Este producto aún no tiene lotes registrados.');
    }
}

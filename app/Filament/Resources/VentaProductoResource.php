<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VentaProductoResource\Pages;
use App\Models\VentaProducto;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\{
    Section,
    DatePicker,
    Select,
    Placeholder
};
use Filament\Tables\Columns\{
    TextColumn
};
use App\Filament\Resources\VentaProductoResource\RelationManagers\DetallesRelationManager;

class VentaProductoResource extends Resource
{
    protected static ?string $model = VentaProducto::class;

    public static function getNavigationLabel(): string
    {
        return 'Ventas de Productos';
    }

    public static function getNavigationGroup(): string
    {
        return 'Gestión de Productos';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-shopping-cart';
    }

    public static function getModelLabel(): string
    {
        return 'Venta de Producto';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Ventas de Productos';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->can('view_any_venta::producto');
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view_any_venta::producto');
    }

    public static function canView($record): bool
    {
        return auth()->user()?->can('view_venta::producto');
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create_venta::producto');
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('update_venta::producto');
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->can('delete_venta::producto');
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->can('delete_any_venta::producto');
    }


    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Información de la Venta')
                ->description('Complete los datos generales de la venta.')
                ->icon('heroicon-o-currency-dollar')
                ->columns(2)
                ->schema([
                    DatePicker::make('fecha')
                        ->label('Fecha de Venta')
                        ->default(now())
                        ->required()
                        ->placeholder('Seleccione la fecha de la venta'),

                    Select::make('usuario_id')
                        ->label('Responsable')
                        ->relationship('usuario', 'name')
                        ->default(Auth::id())
                        ->disabled()
                        ->dehydrated()
                        ->required()
                        ->placeholder('Usuario actual'),

                    Select::make('metodo_pago')
                        ->label('Forma de Pago')
                        ->options([
                            'efectivo' => 'Efectivo',
                            'qr' => 'QR',
                        ])
                        ->required()
                        ->placeholder('Seleccione el método de pago'),

                    Placeholder::make('total')
                        ->label('Total (Bs.)')
                        ->content(fn($record) => $record ? number_format($record->total, 2, ',', '.') . ' Bs' : '0.00 Bs'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('fecha')
                    ->label('Fecha')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->icon('heroicon-o-calendar'),

                TextColumn::make('usuario.name')
                    ->label('Responsable')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-user'),

                TextColumn::make('metodo_pago')
                    ->label('Método de Pago')
                    ->sortable()
                    ->formatStateUsing(fn($state) => ucfirst($state))
                    ->icon('heroicon-o-credit-card'),

                TextColumn::make('total')
                    ->label('Total (Bs.)')
                    ->money('BOB')
                    ->alignRight()
                    ->sortable()
                    ->icon('heroicon-o-banknotes'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('usuario_id')
                    ->label('Responsable')
                    ->relationship('usuario', 'name'),

                Tables\Filters\SelectFilter::make('metodo_pago')
                    ->label('Método de Pago')
                    ->options([
                        'efectivo' => 'Efectivo',
                        'qr' => 'QR',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->tooltip('Editar esta venta'),
                Tables\Actions\DeleteAction::make()
                    ->tooltip('Eliminar esta venta'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVentaProductos::route('/'),
            'create' => Pages\CreateVentaProducto::route('/create'),
            'edit' => Pages\EditVentaProducto::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            DetallesRelationManager::class,
        ];
    }
}
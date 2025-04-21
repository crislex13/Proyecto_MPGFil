<?php

namespace App\Filament\Resources\VentaProductoResource\RelationManagers;

use App\Models\Productos;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Filament\Forms\Get;
use App\Models\ConversionPaquete;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;


class DetallesRelationManager extends RelationManager
{
    protected static string $relationship = 'detalles';

    protected static ?string $title = 'Detalles de la Venta';

    public function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([

            Select::make('producto_id')
                ->label('Producto')
                ->relationship('producto', 'nombre')
                ->searchable()
                ->getOptionLabelFromRecordUsing(
                    fn($record) =>
                    "{$record->nombre} | Stock: {$record->stock_unidades} u. | Bs {$record->precio_unitario}"
                )
                ->required()
                ->placeholder('Seleccione un producto')
                ->reactive()
                ->afterStateUpdated(function (callable $set, $state) {
                    $producto = Productos::find($state);
                    if ($producto) {
                        $set('precio_unitario', $producto->precio_unitario);
                    }
                }),

            TextInput::make('cantidad')
                ->label('Cantidad')
                ->numeric()
                ->minValue(1)
                ->required()
                ->reactive()
                ->afterStateUpdated(
                    fn($state, callable $set, Get $get) =>
                    $set('subtotal', $state * $get('precio_unitario'))
                ),

            TextInput::make('precio_unitario')
                ->label('Precio Unitario (Bs.)')
                ->numeric()
                ->minValue(0.01)
                ->required()
                ->placeholder('Ej: 10.00'),

            TextInput::make('subtotal')
                ->label('Subtotal (Bs.)')
                ->disabled()
                ->dehydrated()
                ->required()
                ->hint('Se calculará automáticamente al guardar.')
        ])->columns(2);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('producto.nombre')
                    ->label('Producto')
                    ->searchable(),

                TextColumn::make('cantidad')
                    ->label('Cantidad'),

                TextColumn::make('precio_unitario')
                    ->label('Precio Unitario')
                    ->money('BOB'),

                TextColumn::make('subtotal')
                    ->label('Subtotal')
                    ->money('BOB'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['subtotal'] = $data['cantidad'] * $data['precio_unitario'];

                        $producto = Productos::find($data['producto_id']);
                        if ($producto) {
                            $stockSuficiente = $producto->stock_unidades >= $data['cantidad'];

                            $puedeConvertir = (
                                $producto->stock_paquetes > 0 &&
                                $producto->unidades_por_paquete &&
                                ($producto->stock_unidades + $producto->stock_paquetes * $producto->unidades_por_paquete) >= $data['cantidad']
                            );

                            if ($stockSuficiente) {
                                $producto->decrement('stock_unidades', $data['cantidad']);
                            } elseif ($puedeConvertir) {
                                while ($producto->stock_unidades < $data['cantidad'] && $producto->stock_paquetes > 0) {
                                    $producto->decrement('stock_paquetes', 1);
                                    $producto->increment('stock_unidades', $producto->unidades_por_paquete);
                                }

                                $producto->decrement('stock_unidades', $data['cantidad']);
                            } else {
                                Notification::make()
                                    ->title('Stock insuficiente')
                                    ->danger()
                                    ->body('No hay stock suficiente ni en unidades ni en paquetes para este producto.')
                                    ->persistent() // Opción para que no desaparezca automáticamente
                                    ->send();

                                throw ValidationException::withMessages([
                                    'producto_id' => 'Stock insuficiente para el producto seleccionado.',
                                ]);
                            }
                        }

                        return $data;
                    })
                    ->after(function (Model $record) {
                        // Actualizar total de la venta
                        $venta = $record->venta;
                        if ($venta) {
                            $venta->update([
                                'total' => $venta->detalles->sum('subtotal')
                            ]);
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['subtotal'] = $data['cantidad'] * $data['precio_unitario'];
                        return $data;
                    })
                    ->after(function (Model $record) {
                        $venta = $record->venta;
                        if ($venta) {
                            $venta->update([
                                'total' => $venta->detalles->sum('subtotal')
                            ]);
                        }
                    }),

                Tables\Actions\DeleteAction::make()
                    ->before(function (Model $record) {
                        // Devolver stock al eliminar
                        $producto = $record->producto;
                        if ($producto) {
                            $producto->increment('stock_unidades', $record->cantidad);
                        }
                    })
                    ->after(function (Model $record) {
                        // Actualizar total de la venta
                        $venta = $record->venta;
                        if ($venta) {
                            $venta->update([
                                'total' => $venta->detalles->sum('subtotal')
                            ]);
                        }
                    }),
            ]);
    }
}
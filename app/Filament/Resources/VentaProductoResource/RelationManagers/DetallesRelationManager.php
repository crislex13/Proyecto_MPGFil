<?php

namespace App\Filament\Resources\VentaProductoResource\RelationManagers;
use App\Models\Productos;
use App\Models\LoteProducto;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Get;
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
                ->searchable()
                ->options(function () {
                    return Productos::activos()
                        ->get()
                        ->mapWithKeys(function ($producto) {
                            return [
                                $producto->id => "{$producto->nombre} | Stock: {$producto->stock_unidades} u. | Bs {$producto->precio_unitario}",
                            ];
                        });
                })
                ->required()
                ->placeholder('Seleccione un producto')
                ->reactive()
                ->afterStateUpdated(function ($state, callable $set) {
                    $producto = Productos::find($state);
                    if ($producto) {
                        // set precio y resetear subtotal
                        $set('precio_unitario', $producto->precio_unitario);
                        $set('subtotal', 0);
                    } else {
                        $set('precio_unitario', null);
                        $set('subtotal', 0);
                    }
                }),

            TextInput::make('cantidad')
                ->label('Cantidad')
                ->required()
                ->numeric()
                ->debounce(500)
                ->minValue(1)
                ->extraAttributes([
                    'onkeydown' => 'return false;',  // bloquea teclado manual
                    'inputmode' => 'numeric',        // muestra teclado numérico en móvil
                    'style' => 'text-align: center;',// opcional: mejora visual
                ])
                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                    $precioUnitario = $get('precio_unitario');
                    if ($precioUnitario !== null) {
                        $set('subtotal', $state * $precioUnitario);
                    }
                }),

            TextInput::make('precio_unitario')
                ->label('Precio Unitario (Bs.)')
                ->required()
                ->numeric()
                ->minValue(0.01)
                ->placeholder('Ej: 10.00')
                ->disabled()
                ->dehydrated()
                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                    $cantidad = $get('cantidad');
                    if ($cantidad !== null) {
                        $set('subtotal', $state * $cantidad);
                    }
                }),

            TextInput::make('subtotal')
                ->label('Subtotal (Bs.)')
                ->disabled() // para que no se edite manualmente
                ->numeric()
                ->dehydrated() // se guarda en la BD
                ->default(0),
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

                TextColumn::make('loteProducto.fecha_vencimiento')
                    ->label('Lote Usado')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->modalHeading('Adicionar producto a la venta')
                    ->modalButton('Registrar venta')
                    ->createAnother(true)
                    ->successNotificationTitle('Detalle de venta registrado correctamente')
                    ->mutateFormDataUsing(function (array $data): array {
                        $producto = Productos::find($data['producto_id']);
                        if (!$producto) {
                            return $data;
                        }

                        $cantidadTotal = $data['cantidad'];
                        $unidadesPorPaquete = $producto->unidades_por_paquete ?? 0;

                        $lotes = $producto->lotes()
                            ->where(function ($q) {
                                $q->where('stock_unidades', '>', 0)
                                    ->orWhere('stock_paquetes', '>', 0);
                            })
                            ->where(function ($q) {
                                // ✅ incluye lotes con fecha futura o sin fecha
                                $q->whereDate('fecha_vencimiento', '>=', now())
                                    ->orWhereNull('fecha_vencimiento');
                            })
                            ->orderByRaw("ISNULL(fecha_vencimiento), fecha_vencimiento ASC") // PEPS: primero con fecha, luego sin fecha
                            ->get();

                        // Si no hay stock disponible en lotes válidos, pero sí hay lotes vencidos → alerta
                        if ($lotes->isEmpty()) {
                            $hayVencidos = $producto->lotes()
                                ->whereDate('fecha_vencimiento', '<', now())
                                ->where(function ($q) {
                                    $q->where('stock_unidades', '>', 0)
                                        ->orWhere('stock_paquetes', '>', 0);
                                })
                                ->exists();

                            if ($hayVencidos) {
                                Notification::make()
                                    ->title('¡Stock vencido!')
                                    ->danger()
                                    ->body("Este producto tiene stock, pero todos los lotes están vencidos. No se puede vender.")
                                    ->send();
                            }
                        }


                        $cantidadPendiente = $cantidadTotal;
                        $cantidadConvertida = 0;
                        $loteOrigen = null;
                        $loteConversion = null;

                        foreach ($lotes as $lote) {
                            if ($cantidadPendiente <= 0) {
                                break;
                            }

                            // 1️⃣ Usar unidades disponibles
                            if ($lote->stock_unidades > 0) {
                                $usadas = min($cantidadPendiente, $lote->stock_unidades);
                                $lote->stock_unidades -= $usadas;
                                $cantidadPendiente -= $usadas;
                                if (!$loteOrigen) {
                                    $loteOrigen = $lote;
                                }
                                $lote->save();
                            }

                            // 2️⃣ Si todavía falta, convertir paquetes y usar de inmediato
                            while (
                                $cantidadPendiente > 0 &&
                                $lote->stock_paquetes > 0 &&
                                $unidadesPorPaquete > 0
                            ) {
                                // Convertir un paquete
                                $lote->stock_paquetes -= 1;
                                $lote->stock_unidades += $unidadesPorPaquete;
                                $cantidadConvertida += 1;
                                if (!$loteOrigen) {
                                    $loteOrigen = $lote;
                                }
                                $loteConversion = $lote;

                                // Usar de inmediato las unidades recién convertidas
                                $usadas = min($cantidadPendiente, $lote->stock_unidades);
                                $lote->stock_unidades -= $usadas;
                                $cantidadPendiente -= $usadas;

                                $lote->save();
                            }
                        }

                        // 3️⃣ Si no se pudo cubrir la cantidad → error
                        if ($cantidadPendiente > 0) {
                            Notification::make()
                                ->title('¡Stock insuficiente!')
                                ->danger()
                                ->body('No hay stock suficiente para completar la venta del producto seleccionado.')
                                ->send();

                            throw ValidationException::withMessages([
                                'producto_id' => 'No hay stock suficiente para completar la venta.',
                            ]);
                        }

                        $data['subtotal'] = $data['cantidad'] * $data['precio_unitario'];
                        $data['lote_origen_id'] = $loteOrigen?->id;
                        $data['lote_producto_id'] = $loteConversion?->id ?? $loteOrigen?->id;
                        $data['cantidad_convertida_desde_paquete'] = $cantidadConvertida;

                        return $data;
                    })

                    ->after(function (Model $record, Tables\Actions\CreateAction $action) {
                        $venta = $record->venta;
                        if ($venta) {
                            $venta->update([
                                'total' => $venta->detalles->sum('subtotal'),
                            ]);
                        }

                        Notification::make()
                            ->title('Venta registrada')
                            ->success()
                            ->body("Se registró correctamente la venta de {$record->cantidad} unidades.")
                            ->send();

                        // Resetea el formulario
                        $action->fillForm([
                            'producto_id' => null,
                            'cantidad' => null,
                            'precio_unitario' => null,
                            'subtotal' => null,
                        ]);
                    })

            ])
            ->actions([
            ]);
    }
}

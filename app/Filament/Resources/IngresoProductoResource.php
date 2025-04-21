<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IngresoProductoResource\Pages;
use App\Models\IngresoProducto;
use App\Models\Productos;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\DateColumn;
use Illuminate\Support\Facades\Auth;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Hidden;

class IngresoProductoResource extends Resource
{
    protected static ?string $model = IngresoProducto::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-tray';
    protected static ?string $navigationGroup = 'Productos';
    protected static ?string $navigationLabel = 'Ingresos De Productos';
    protected static ?string $pluralModelLabel = 'Ingresos De Productos';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Datos del Ingreso')
                    ->description('Registre aquí la entrada de productos al inventario.')
                    ->columns(2)
                    ->schema([
                        Select::make('producto_id')
                            ->label('Producto')
                            ->relationship('producto', 'nombre')
                            ->searchable()
                            ->required()
                            ->placeholder('Seleccione un producto'),

                        TextInput::make('cantidad_unidades')
                            ->label('Cantidad (unidades)')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->placeholder('Ej: 10'),

                        TextInput::make('cantidad_paquetes')
                            ->label('Cantidad (paquetes)')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->placeholder('Ej: 2'),

                        TextInput::make('precio_unitario')
                            ->label('Precio unitario (Bs.)')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->placeholder('Ej: 10.50'),

                        TextInput::make('precio_paquete')
                            ->label('Precio por paquete (Bs.)')
                            ->numeric()
                            ->minValue(0)
                            ->placeholder('Ej: 120.00'),

                        Select::make('usuario_id')
                            ->label('Registrado por')
                            ->relationship('usuario', 'name')
                            ->default(Auth::id())
                            ->disabled()
                            ->dehydrated()
                            ->required(),

                        Hidden::make('fecha')
                            ->default(now())
                            ->dehydrated(),

                        Textarea::make('observacion')
                            ->label('Observaciones')
                            ->placeholder('Notas adicionales del ingreso')
                            ->rows(3),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('producto.foto_url')
                    ->label('Imagen')
                    ->circular()
                    ->height(40)
                    ->width(40),

                TextColumn::make('producto.nombre')
                    ->label('Producto')
                    ->icon('heroicon-o-cube')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('cantidad_unidades')
                    ->label('Unidades')
                    ->icon('heroicon-o-bars-3-bottom-left')
                    ->sortable(),

                TextColumn::make('precio_unitario')
                    ->label('P. Unitario')
                    ->icon('heroicon-o-currency-dollar')
                    ->money('BOB')
                    ->sortable(),

                TextColumn::make('cantidad_paquetes')
                    ->label('Paquetes')
                    ->icon('heroicon-o-archive-box')
                    ->sortable(),

                TextColumn::make('precio_paquete')
                    ->label('P. Paquete')
                    ->icon('heroicon-o-banknotes')
                    ->money('BOB')
                    ->sortable(),

                TextColumn::make('usuario.name')
                    ->label('Registrado por')
                    ->icon('heroicon-o-user')
                    ->sortable(),

                TextColumn::make('fecha')
                    ->label('Fecha')
                    ->icon('heroicon-o-calendar-days')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('producto_id')
                    ->label('Producto')
                    ->relationship('producto', 'nombre'),

                Tables\Filters\SelectFilter::make('usuario_id')
                    ->label('Registrado por')
                    ->relationship('usuario', 'name'),

                Tables\Filters\Filter::make('fecha')
                    ->form([
                        DatePicker::make('desde')->label('Desde'),
                        DatePicker::make('hasta')->label('Hasta'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['desde'], fn($q) => $q->whereDate('fecha', '>=', $data['desde']))
                            ->when($data['hasta'], fn($q) => $q->whereDate('fecha', '<=', $data['hasta']));
                    })
                    ->label('Rango de fechas'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->tooltip('Editar este ingreso'),
                Tables\Actions\DeleteAction::make()->tooltip('Eliminar este ingreso'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIngresoProductos::route('/'),
            'create' => Pages\CreateIngresoProducto::route('/create'),
            'edit' => Pages\EditIngresoProducto::route('/{record}/edit'),
        ];
    }
}

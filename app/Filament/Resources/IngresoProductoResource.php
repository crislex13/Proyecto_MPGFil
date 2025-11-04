<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IngresoProductoResource\Pages;
use App\Models\IngresoProducto;
use App\Models\Productos;
use Filament\Resources\Resource;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms;

class IngresoProductoResource extends Resource
{
    protected static ?string $model = IngresoProducto::class;

    public static function getNavigationLabel(): string
    {
        return 'Ingresos de Productos';
    }

    public static function getNavigationGroup(): string
    {
        return 'Gestión de Productos';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-arrow-down-tray';
    }

    public static function getModelLabel(): string
    {
        return 'Ingreso de Producto';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Ingresos de Productos';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->can('view_any_ingreso::producto');
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view_any_ingreso::producto');
    }

    public static function canView($record): bool
    {
        return auth()->user()?->can('view_ingreso::producto');
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create_ingreso::producto');
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('update_ingreso::producto');
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasRole('admin') === true;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->hasRole('admin') === true;
    }
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
                            ->searchable()
                            ->required()
                            ->reactive()
                            ->options(Productos::all()->pluck('nombre', 'id'))
                            ->options(Productos::activos()->pluck('nombre', 'id'))
                            ->validationMessages([
                                'required' => 'Debe seleccionar un producto.',
                                'exists' => 'El producto seleccionado no es válido.',
                            ])
                            ->placeholder('Seleccione el producto'),

                        Textarea::make('observacion')
                            ->label('Observaciones')
                            ->placeholder('Notas adicionales del ingreso')
                            ->rows(3),

                        TextInput::make('cantidad_unidades')
                            ->label('Cantidad (unidades)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(100000)
                            ->placeholder('Ej: 10')
                            ->helperText('Debe ser mayor a 0 y razonable (máx. 100,000).')
                            ->validationMessages([
                                'required' => 'Debe ingresar la cantidad de unidades.',
                                'numeric' => 'La cantidad de unidades debe ser un número.',
                                'min' => 'Debe ingresar al menos 1 unidad.',
                                'max' => 'No puede ingresar más de 100,000 unidades.',
                            ]),

                        TextInput::make('cantidad_paquetes')
                            ->label('Cantidad (paquetes)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(10000)
                            ->default(0)
                            ->placeholder('Ej: 2')
                            ->helperText('Máximo permitido: 10,000 paquetes.')
                            ->validationMessages([
                                'numeric' => 'La cantidad de paquetes debe ser un número.',
                                'min' => 'No se permiten valores negativos.',
                                'max' => 'No puede ingresar más de 10,000 paquetes.',
                            ]),

                        TextInput::make('precio_unitario')
                            ->label('Precio de compra unitario (Bs.)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(10000)
                            ->placeholder('Ej: 10.50')
                            ->helperText('Debe ser mayor a 1 y no exceder 10,000 Bs.')
                            ->validationMessages([
                                'required' => 'Debe ingresar el precio unitario.',
                                'numeric' => 'El precio unitario debe ser un número.',
                                'min' => 'El precio unitario debe ser al menos 1 Bs.',
                                'max' => 'El precio unitario no puede exceder 10,000 Bs.',
                            ]),

                        TextInput::make('precio_paquete')
                            ->label('Precio de compra paquete (Bs.)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100000)
                            ->placeholder('Ej: 120.00')
                            ->helperText('Debe estar entre 1 y 100,000 Bs.')
                            ->validationMessages([
                                'numeric' => 'El precio por paquete debe ser un número.',
                                'min' => 'El precio del paquete debe ser al menos 1 Bs.',
                                'max' => 'El precio del paquete no puede exceder 100,000 Bs.',
                            ]),

                        DatePicker::make('fecha_vencimiento')
                            ->label('Fecha de vencimiento')
                            ->placeholder('Solo si el producto es perecedero')
                            ->visible(fn(callable $get) => optional(Productos::find($get('producto_id')))->es_perecedero)
                            ->required(fn(callable $get) => optional(Productos::find($get('producto_id')))->es_perecedero)
                            ->minDate(now())
                            ->hint('Requerido para productos perecederos')
                            ->reactive()
                            ->columnSpan(2),

                        Hidden::make('fecha')
                            ->default(now())
                            ->dehydrated(),
                    ]),

                Section::make('Control de cambios')
                    ->icon('heroicon-o-user-circle')
                    ->collapsible()
                    ->columns(1)
                    ->visible(fn() => auth()->user()?->hasRole('admin'))
                    ->schema([
                        Placeholder::make('registrado_por')
                            ->label('Registrado por')
                            ->content(fn($record) => optional($record?->registradoPor)->name ?? 'No registrado'),

                        Placeholder::make('modificado_por')
                            ->label('Modificado por')
                            ->content(fn($record) => optional($record?->modificadoPor)->name ?? 'Sin cambios'),
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

                TextColumn::make('fecha')
                    ->label('Fecha')
                    ->icon('heroicon-o-calendar-days')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('registradoPor.name')
                    ->label('Registrado por')
                    ->icon('heroicon-o-user-plus')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable()
                    ->visible(fn() => auth()->user()?->hasRole('admin')),

                TextColumn::make('modificadoPor.name')
                    ->label('Modificado por')
                    ->icon('heroicon-o-pencil-square')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable()
                    ->visible(fn() => auth()->user()?->hasRole('admin')),

            ])
            ->filters([
                Tables\Filters\SelectFilter::make('producto_id')
                    ->label('Producto')
                    ->relationship('producto', 'nombre'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->tooltip('Editar este ingreso'),
                Tables\Actions\DeleteAction::make()
                    ->tooltip('Eliminar este ingreso')
                    ->visible(fn() => auth()->user()?->hasRole('admin'))
                    ->authorize(fn() => auth()->user()?->hasRole('admin'))
                    ->requiresConfirmation()
                    ->successNotificationTitle('Ingreso eliminado'),
            ])
            ->headerActions([
                Tables\Actions\Action::make('reporte_diario')
                    ->label('PDF Diario')
                    ->icon('heroicon-o-document-arrow-down')
                    ->visible(fn() => auth()->user()?->hasRole('admin'))
                    ->form([
                        Forms\Components\DatePicker::make('date')->default(now())->required()->native(false),
                    ])
                    ->action(fn(array $data) => redirect()->route('reportes.ingresos.dia', ['date' => $data['date']])),

                Tables\Actions\Action::make('reporte_mensual')
                    ->label('PDF Mensual')
                    ->icon('heroicon-o-document-arrow-down')
                    ->visible(fn() => auth()->user()?->hasRole('admin'))
                    ->form([
                        Forms\Components\Select::make('year')
                            ->options(collect(range(now()->year, now()->year - 5))->mapWithKeys(fn($y) => [$y => $y])->toArray())
                            ->default(now()->year)->required(),
                        Forms\Components\Select::make('month')
                            ->options([
                                1 => 'Enero',
                                2 => 'Febrero',
                                3 => 'Marzo',
                                4 => 'Abril',
                                5 => 'Mayo',
                                6 => 'Junio',
                                7 => 'Julio',
                                8 => 'Agosto',
                                9 => 'Septiembre',
                                10 => 'Octubre',
                                11 => 'Noviembre',
                                12 => 'Diciembre',
                            ])->default(now()->month)->required(),
                    ])
                    ->action(fn(array $data) => redirect()->route('reportes.ingresos.mes', $data)),

                Tables\Actions\Action::make('reporte_anual')
                    ->label('PDF Anual')
                    ->icon('heroicon-o-document-arrow-down')
                    ->visible(fn() => auth()->user()?->hasRole('admin'))
                    ->form([
                        Forms\Components\Select::make('year')
                            ->options(collect(range(now()->year, now()->year - 5))->mapWithKeys(fn($y) => [$y => $y])->toArray())
                            ->default(now()->year)->required(),
                    ])
                    ->action(fn(array $data) => redirect()->route('reportes.ingresos.anio', $data)),

                Tables\Actions\Action::make('reporte_rango')
                    ->label('PDF por Rango')
                    ->icon('heroicon-o-document-arrow-down')
                    ->visible(fn() => auth()->user()?->hasRole('admin'))
                    ->form([
                        Forms\Components\DatePicker::make('start')->required()->native(false),
                        Forms\Components\DatePicker::make('end')->required()->native(false),
                    ])
                    ->action(fn(array $data) => redirect()->route('reportes.ingresos.rango', $data)),
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

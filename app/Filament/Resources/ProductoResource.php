<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductoResource\Pages;
use App\Models\Productos;
use App\Models\CategoriaProducto;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Placeholder;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Hidden;
use Filament\Tables\Actions\Action;

class ProductoResource extends Resource
{
    protected static ?string $model = Productos::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationGroup = 'Productos';
    protected static ?string $navigationLabel = 'Productos';
    protected static ?string $pluralModelLabel = 'Productos';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Información del producto')
                ->description('Complete todos los campos para registrar un nuevo producto en el inventario.')
                ->columns(2)
                ->schema([
                    TextInput::make('nombre')
                        ->label('Nombre del producto')
                        ->required()
                        ->placeholder('Ej: Agua 3L'),

                    Select::make('categoria_id')
                        ->label('Categoría')
                        ->options(CategoriaProducto::pluck('nombre', 'id'))
                        ->searchable()
                        ->required()
                        ->placeholder('Seleccione una categoría'),

                    TextInput::make('precio_unitario')
                        ->label('Precio unitario (Bs.)')
                        ->numeric()
                        ->minValue(0)
                        ->placeholder('Ej: 6.50')
                        ->required(),

                    TextInput::make('precio_paquete')
                        ->label('Precio por paquete (Bs.)')
                        ->numeric()
                        ->minValue(0)
                        ->placeholder('Ej: 65.00'),

                    TextInput::make('unidades_por_paquete')
                        ->label('Unidades por paquete')
                        ->numeric()
                        ->minValue(1)
                        ->placeholder('Ej: 12'),

                    TextInput::make('stock_unidades')
                        ->label('Stock en unidades')
                        ->numeric()
                        ->default(0)
                        ->minValue(0)
                        ->required()
                        ->disabled(fn(string $context) => $context === 'edit'),

                    TextInput::make('stock_paquetes')
                        ->label('Stock en paquetes')
                        ->numeric()
                        ->default(0)
                        ->minValue(0)
                        ->required()
                        ->disabled(fn(string $context) => $context === 'edit'),

                    FileUpload::make('imagen')
                        ->label('Imagen del producto')
                        ->image()
                        ->directory('productos')
                        ->disk('public')
                        ->imageEditor()
                        ->imagePreviewHeight('80'),

                    Textarea::make('descripcion')
                        ->label('Descripción')
                        ->rows(3)
                        ->placeholder('Breve descripción del producto...'),

                    Hidden::make('usuario_id')
                        ->default(fn() => Auth::id())
                        ->dehydrated(fn(string $context) => $context === 'create')
                        ->required(fn(string $context) => $context === 'create'),

                    Hidden::make('modificado_por')
                        ->default(fn() => Auth::id())
                        ->dehydrated(fn(string $context) => $context === 'edit')
                        ->required(fn(string $context) => $context === 'edit'),
                ]),

            Section::make('Control de cambios')
                ->columns(2)
                ->schema([
                    Placeholder::make('registrado_por')
                        ->label('Registrado por')
                        ->content(fn($record) => $record?->usuario?->name ?? '—'),

                    Placeholder::make('modificado_por_placeholder')
                        ->label('Modificado por')
                        ->content(fn($record) => $record?->modificadoPor?->name ?? 'Sin modificaciones'),

                    Hidden::make('usuario_id')
                        ->default(fn() => Auth::id())
                        ->dehydrated(fn($context) => $context === 'create'),

                    Hidden::make('modificado_por')
                        ->default(fn() => Auth::id())
                        ->dehydrated(fn($context) => $context === 'edit'),
                ]),
        ]);
    }

    public static function getRelations(): array
    {
        return [
            // RelationManagers si se usan en el futuro
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('foto_url')
                    ->label('Imagen')
                    ->circular()
                    ->height(50)
                    ->width(50),

                TextColumn::make('nombre')
                    ->label('Nombre')
                    ->icon('heroicon-o-cube')
                    ->searchable()
                    ->sortable()
                    ->tooltip(fn($record) => $record->descripcion),

                TextColumn::make('categoria.nombre')
                    ->label('Categoría')
                    ->icon('heroicon-o-tag')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('precio_unitario')
                    ->label('P. Unitario')
                    ->icon('heroicon-o-currency-dollar')
                    ->money('BOB')
                    ->sortable(),

                TextColumn::make('precio_paquete')
                    ->label('P. Paquete')
                    ->icon('heroicon-o-archive-box')
                    ->money('BOB')
                    ->sortable(),

                TextColumn::make('unidades_por_paquete')
                    ->label('Unid./Paquete')
                    ->icon('heroicon-o-bars-3-bottom-left')
                    ->sortable(),

                TextColumn::make('stock_unidades')
                    ->label('Stock (u)')
                    ->icon('heroicon-o-archive-box')
                    ->sortable(),

                TextColumn::make('stock_paquetes')
                    ->label('Stock (p)')
                    ->icon('heroicon-o-archive-box-arrow-down')
                    ->sortable(),

                TextColumn::make('registradoPor.name')
                    ->label('Registrado por')
                    ->icon('heroicon-o-user-circle')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('modificadoPor.name')
                    ->label('Modificado por')
                    ->icon('heroicon-o-pencil-square')
                    ->searchable()
                    ->sortable(),
            ])
            ->headerActions([
                Action::make('Reporte Diario')
                    ->label('Reporte Diario')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->url(route('reporte.productos.diario'))
                    ->openUrlInNewTab(),
                    //->visible(fn() => auth()->user()->can('ver_reporte_productos')),

                Action::make('Reporte Mensual')
                    ->label('Reporte Mensual')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('warning')
                    ->url(route('reporte.productos.mensual'))
                    ->openUrlInNewTab(),
                    //->visible(fn() => auth()->user()->can('ver_reporte_productos')),

                Action::make('Reporte Anual')
                    ->label('Reporte Anual')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('primary')
                    ->url(route('reporte.productos.anual'))
                    ->openUrlInNewTab(),
                    //->visible(fn() => auth()->user()->can('ver_reporte_productos')),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('categoria_id')
                    ->label('Categoría')
                    ->relationship('categoria', 'nombre'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductos::route('/'),
            'create' => Pages\CreateProducto::route('/create'),
            'edit' => Pages\EditProducto::route('/{record}/edit'),
        ];
    }
}

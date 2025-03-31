<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductoResource\Pages;
use App\Models\Productos;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\Select;

class ProductoResource extends Resource
{
    protected static ?string $model = Productos::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationGroup = 'Productos';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Select::make('producto_id')
                    ->label('Producto')
                    ->options(Productos::pluck('nombre', 'id'))  // Usamos pluck directamente
                    ->searchable()
                    ->required(),

                TextInput::make('nombre')
                    ->required()
                    ->maxLength(255),

                Textarea::make('descripcion')
                    ->maxLength(500),

                TextInput::make('precio')
                    ->required()
                    ->step(0.01)
                    ->type('number')
                    ->prefix('Bs ')
                    ->rules(['numeric', 'min:0']),

                TextInput::make('stock')
                    ->required()
                    ->type('number')
                    ->step(1)
                    ->rules(['numeric', 'min:0']),

                TextInput::make('categoria')
                    ->required()
                    ->maxLength(255),

                FileUpload::make('imagen')
                    ->image()  // Validación para imágenes
                    ->maxSize(1024)  // Tamaño máximo de 1MB
                    ->directory('productos_imagenes')  // Directorio donde se guardará la imagen
                    ->required(),  // Hacerlo obligatorio

                DatePicker::make('fecha_vencimiento')
                    ->required()
                    ->label('Fecha de Vencimiento'),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')->searchable()->sortable(),
                TextColumn::make('descripcion')->searchable()->limit(50),
                TextColumn::make('precio')->money('BOB')->sortable(),
                TextColumn::make('stock')->sortable(),
                TextColumn::make('categoria')->sortable(),
                TextColumn::make('imagen')
                    ->label('Imagen')
                    ->formatStateUsing(fn($state) => $state ? '<img src="' . asset('storage/' . $state) . '" width="100" height="100" />' : '')
                    ->html(),
                TextColumn::make('fecha_vencimiento')
                    ->label('Fecha de Vencimiento')
                    ->sortable()
                    ->date(),
            ])
            ->filters([
                SelectFilter::make('categoria')
                    ->options(Productos::pluck('categoria', 'categoria')->unique()->toArray()) // Usamos productos directamente
                    ->label('Categoría'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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

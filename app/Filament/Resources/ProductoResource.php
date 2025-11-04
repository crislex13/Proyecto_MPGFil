<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductoResource\Pages;
use App\Filament\Resources\ProductosResource\RelationManagers\LotesProductoRelationManager;
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
use Filament\Forms\Components\Toggle;

class ProductoResource extends Resource
{
    protected static ?string $model = Productos::class;

    public static function getNavigationLabel(): string
    {
        return 'Productos';
    }

    public static function getNavigationGroup(): string
    {
        return 'Gestión de Productos';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-cube';
    }

    public static function getModelLabel(): string
    {
        return 'Producto';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Productos';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->can('view_any_producto');
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view_any_producto');
    }

    public static function canView($record): bool
    {
        return auth()->user()?->can('view_producto');
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create_producto');
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('update_producto');
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
                        ->label('Precio de venta unitario (Bs.)')
                        ->numeric()
                        ->minValue(0)
                        ->placeholder('Ej: 6.50')
                        ->required(),

                    TextInput::make('precio_paquete')
                        ->label('Precio de venta por paquete (Bs.)')
                        ->numeric()
                        ->minValue(0)
                        ->placeholder('Ej: 65.00'),

                    TextInput::make('unidades_por_paquete')
                        ->label('Unidades por paquete')
                        ->numeric()
                        ->minValue(1)
                        ->placeholder('Ej: 12'),

                    Toggle::make('es_perecedero')
                        ->label('¿Es perecedero?')
                        ->helperText('Activa esta opción si el producto tiene fecha de vencimiento')
                        ->default(false)
                        ->inline(false)
                        ->columnSpanFull()
                        ->required(),

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
            LotesProductoRelationManager::class,
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
                    ->sortable(false),

                TextColumn::make('stock_paquetes')
                    ->label('Stock (p)')
                    ->icon('heroicon-o-archive-box-arrow-down')
                    ->sortable(false),

                TextColumn::make('activo')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn($state) => $state ? 'Activo' : 'Inactivo')
                    ->colors([
                        'success' => true,
                        'danger' => false,
                    ])
                    ->sortable(),

                TextColumn::make('registradoPor.name')
                    ->label('Registrado por')
                    ->icon('heroicon-o-user-circle')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),

                TextColumn::make('modificadoPor.name')
                    ->label('Modificado por')
                    ->icon('heroicon-o-pencil-square')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
            ])
            ->headerActions([
                Action::make('Reporte Diario')
                    ->label('Reporte Diario')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->url(route('reporte.productos.diario'))
                    ->openUrlInNewTab()
                    ->visible(fn () => auth()->user()?->hasRole('admin')),
                //->visible(fn() => auth()->user()->can('ver_reporte_productos')),

                Action::make('Reporte Mensual')
                    ->label('Reporte Mensual')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('warning')
                    ->url(route('reporte.productos.mensual'))
                    ->openUrlInNewTab()
                    ->visible(fn () => auth()->user()?->hasRole('admin')),
                //->visible(fn() => auth()->user()->can('ver_reporte_productos')),

                Action::make('Reporte Anual')
                    ->label('Reporte Anual')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('primary')
                    ->url(route('reporte.productos.anual'))
                    ->openUrlInNewTab()
                    ->visible(fn () => auth()->user()?->hasRole('admin')),
                //->visible(fn() => auth()->user()->can('ver_reporte_productos')),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('categoria_id')
                    ->label('Categoría')
                    ->relationship('categoria', 'nombre'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn() => auth()->user()?->can('update_producto'))
                    ->authorize(fn() => auth()->user()?->can('update_producto')),

                Tables\Actions\DeleteAction::make()
                    ->visible(fn() => auth()->user()?->hasRole('admin'))
                    ->authorize(fn() => auth()->user()?->hasRole('admin'))
                    ->requiresConfirmation()
                    ->successNotificationTitle('Producto eliminado'),

                Tables\Actions\Action::make('desactivar')
                    ->label('Desactivar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->action(fn($record) => $record->update(['activo' => false]))
                    ->visible(
                        fn($record) =>
                        auth()->user()?->hasRole('admin') === true
                        && $record->activo
                    ),

                Tables\Actions\Action::make('ficha_pdf')
                    ->label('Ficha PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('primary')
                    ->url(fn($record) => route('reporte.producto.ficha', ['producto' => $record]))
                    ->openUrlInNewTab()
                    ->visible(fn () => auth()->user()?->hasRole('admin')),

                Tables\Actions\Action::make('activar')
                    ->label('Activar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(fn($record) => $record->update(['activo' => true]))
                    ->visible(
                        fn($record) =>
                        auth()->user()?->hasRole('admin') === true
                        && !$record->activo
                    )
                    ->authorize(fn() => auth()->user()?->hasRole('admin') === true),
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

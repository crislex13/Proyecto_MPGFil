<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoriaProductoResource\Pages;
use App\Models\CategoriaProducto;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;

class CategoriaProductoResource extends Resource
{
    protected static ?string $model = CategoriaProducto::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationGroup = 'Productos';
    protected static ?string $navigationLabel = 'Categorías de Productos';
    protected static ?string $pluralModelLabel = 'Categorías de Productos';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Datos de la categoría')
                ->description('Defina el nombre y una descripción general de la categoría.')
                ->schema([
                    TextInput::make('nombre')
                        ->label('Nombre de la categoría')
                        ->required()
                        ->placeholder('Ej: Bebidas'),

                    Textarea::make('descripcion')
                        ->label('Descripción')
                        ->placeholder('Ej: Productos líquidos, refrigerantes, energizantes...')
                        ->rows(3),
                ])
                ->columns(1),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->label('Nombre')
                    ->icon('heroicon-o-tag')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('descripcion')
                    ->label('Descripción')
                    ->icon('heroicon-o-document-text')
                    ->limit(50)
                    ->tooltip(fn($record) => $record->descripcion),
            ])
            ->filters([
                SelectFilter::make('nombre')
                    ->label('Filtrar por nombre')
                    ->options(CategoriaProducto::pluck('nombre', 'nombre')),

                Filter::make('descripcion')
                    ->label('Contiene descripción')
                    ->form([
                        TextInput::make('descripcion')
                            ->placeholder('Buscar por descripción'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when($data['descripcion'], fn($q) => $q->where('descripcion', 'like', "%{$data['descripcion']}%"));
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->tooltip('Editar categoría'),
                Tables\Actions\DeleteAction::make()->tooltip('Eliminar categoría'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategoriaProductos::route('/'),
            'create' => Pages\CreateCategoriaProducto::route('/create'),
            'edit' => Pages\EditCategoriaProducto::route('/{record}/edit'),
        ];
    }
}

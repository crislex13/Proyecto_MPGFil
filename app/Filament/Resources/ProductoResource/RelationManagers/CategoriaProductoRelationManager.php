<?php

namespace App\Filament\Resources\ProductoResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Forms\Components\{TextInput, Textarea, Section};
use Filament\Tables\Columns\TextColumn;

class CategoriaProductoRelationManager extends RelationManager
{
    protected static string $relationship = 'categoria';

    protected static ?string $title = 'Categorías de Productos';

    public function form(Form $form): Form
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

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('descripcion')
                    ->label('Descripción')
                    ->limit(50)
                    ->tooltip(fn($record) => $record->descripcion),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}

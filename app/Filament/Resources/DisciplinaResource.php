<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DisciplinaResource\Pages;
use App\Models\Disciplina;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class DisciplinaResource extends Resource
{
    protected static ?string $model = Disciplina::class;
    protected static ?string $navigationIcon = 'heroicon-o-bolt';
    protected static ?string $navigationGroup = 'Catálogos';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Datos de la disciplina')
                ->schema([
                    TextInput::make('nombre')
                        ->label('Nombre')
                        ->required()
                        ->maxLength(255),

                    TextInput::make('descripcion')
                        ->label('Descripción')
                        ->required()
                        ->maxLength(255),

                    Textarea::make('observaciones')
                        ->label('Observaciones')
                        ->rows(3)
                        ->maxLength(500),
                ])
                ->columns(1),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('nombre')->sortable()->searchable(),
            TextColumn::make('descripcion')->limit(30)->label('Descripción'),
            TextColumn::make('observaciones')->limit(30),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDisciplinas::route('/'),
            'create' => Pages\CreateDisciplina::route('/create'),
            'edit' => Pages\EditDisciplina::route('/{record}/edit'),
        ];
    }
}

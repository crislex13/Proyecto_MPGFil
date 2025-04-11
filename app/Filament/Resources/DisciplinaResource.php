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
    protected static ?string $navigationLabel = 'Disciplinas';
    protected static ?string $pluralModelLabel = 'Disciplinas';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('💪 Datos de la Disciplina')
                ->description('Completa la información para registrar una disciplina del gimnasio.')
                ->schema([
                    TextInput::make('nombre')
                        ->label('Nombre de la disciplina')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Ej: Crossfit, Zumba, Spinning'),

                    TextInput::make('descripcion')
                        ->label('Descripción breve')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Ej: Clase intensa de resistencia y fuerza'),

                    Textarea::make('observaciones')
                        ->label('Observaciones adicionales')
                        ->rows(3)
                        ->maxLength(500)
                        ->placeholder('Notas, recomendaciones o detalles logísticos...'),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('nombre')
                ->label('⚡ Disciplina')
                ->searchable()
                ->sortable(),

            TextColumn::make('descripcion')
                ->label('📝 Descripción')
                ->limit(30),

            TextColumn::make('observaciones')
                ->label('🔍 Observaciones')
                ->limit(30),
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
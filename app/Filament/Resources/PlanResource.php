<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlanResource\Pages;
use App\Models\Plan;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Section;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class PlanResource extends Resource
{
    protected static ?string $model = Plan::class;
    protected static ?string $pluralModelLabel = 'Planes';
    protected static ?string $navigationLabel = 'Planes';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Catálogos';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('📋 Información del Plan')
                ->description('Define los datos básicos del plan disponible en el gimnasio.')
                ->schema([
                    TextInput::make('nombre')
                        ->label('Nombre del plan')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Ej: Plan Mensual'),

                    TextInput::make('duracion_dias')
                        ->label('Duración del plan (en días)')
                        ->numeric()
                        ->required()
                        ->minValue(1)
                        ->placeholder('Ej: 30'),
                ])->columns(2)
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('nombre')
                ->label('📦 Nombre del plan')
                ->searchable()
                ->sortable(),

            TextColumn::make('duracion_dias')
                ->label('⏳ Duración (días)')
                ->sortable(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlans::route('/'),
            'create' => Pages\CreatePlan::route('/create'),
            'edit' => Pages\EditPlan::route('/{record}/edit'),
        ];
    }
}
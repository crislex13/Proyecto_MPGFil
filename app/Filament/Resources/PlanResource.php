<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlanResource\Pages;
use App\Models\Plan;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables;

class PlanResource extends Resource
{
    protected static ?string $model = Plan::class;
    protected static ?string $pluralModelLabel = 'Planes';
    protected static ?string $navigationLabel = 'Planes';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Catálogo de Planes';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Información del Plan')
                ->description('Define los datos básicos del plan disponible en el gimnasio.')
                ->columns(2)
                ->schema([
                    TextInput::make('nombre')
                        ->label('Nombre del plan')
                        ->placeholder('Ej: Plan Mensual, Plan Trimestral')
                        ->required()
                        ->maxLength(255),

                    TextInput::make('duracion_dias')
                        ->label('Duración (días)')
                        ->numeric()
                        ->minValue(1)
                        ->placeholder('Ej: 30')
                        ->required(),
                ]),

            Section::make('Opciones de acceso')
                ->columns(1)
                ->schema([
                    Toggle::make('ingresos_ilimitados')
                        ->label('¿Permite ingresos ilimitados por día?')
                        ->helperText('Actívalo si el cliente puede ingresar varias veces en el mismo día.')
                        ->default(false),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->label('Nombre del plan')
                    ->icon('heroicon-o-rectangle-stack')
                    ->sortable()
                    ->searchable()
                    ->tooltip(fn($record) => $record->nombre),

                TextColumn::make('duracion_dias')
                    ->label('Duración (días)')
                    ->icon('heroicon-o-clock')
                    ->sortable(),

                IconColumn::make('ingresos_ilimitados')
                    ->label('Ilimitado')
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
            ])
            ->filters([
                Tables\Filters\Filter::make('ingresos_ilimitados')
                    ->label('Permite múltiples ingresos')
                    ->query(fn($query) => $query->where('ingresos_ilimitados', true)),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->tooltip('Editar este plan'),
                Tables\Actions\DeleteAction::make()->tooltip('Eliminar este plan'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
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
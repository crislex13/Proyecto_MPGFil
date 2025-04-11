<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlanDisciplinaResource\Pages;
use App\Models\Plan;
use App\Models\Disciplina;
use App\Models\PlanDisciplina;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class PlanDisciplinaResource extends Resource
{
    protected static ?string $model = PlanDisciplina::class;

    protected static ?string $modelLabel = 'Precio de Plan';
    protected static ?string $pluralModelLabel = 'Precios de Plan';
    protected static ?string $navigationLabel = 'Precios por Disciplina';
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationGroup = 'Catálogos';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('💰 Asignación de Precio')
                ->description('Define el precio de un plan según la disciplina que lo utilice.')
                ->schema([

                    Select::make('plan_id')
                        ->label('Plan')
                        ->options(Plan::pluck('nombre', 'id'))
                        ->searchable()
                        ->required()
                        ->placeholder('Seleccione un plan'),

                    Select::make('disciplina_id')
                        ->label('Disciplina')
                        ->options(Disciplina::pluck('nombre', 'id'))
                        ->searchable()
                        ->required()
                        ->placeholder('Seleccione una disciplina'),

                    TextInput::make('precio')
                        ->label('Precio asignado (Bs.)')
                        ->numeric()
                        ->required()
                        ->minValue(0)
                        ->placeholder('Ej: 120.00'),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('plan.nombre')
                ->label('📦 Plan')
                ->searchable()
                ->sortable(),

            TextColumn::make('disciplina.nombre')
                ->label('⚡ Disciplina')
                ->searchable()
                ->sortable(),

            TextColumn::make('precio')
                ->label('💰 Precio (Bs.)')
                ->money('BOB')
                ->sortable(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlanDisciplinas::route('/'),
            'create' => Pages\CreatePlanDisciplina::route('/create'),
            'edit' => Pages\EditPlanDisciplina::route('/{record}/edit'),
        ];
    }
}
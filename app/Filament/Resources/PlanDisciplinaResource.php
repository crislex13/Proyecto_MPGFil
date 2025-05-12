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
use Filament\Tables;

class PlanDisciplinaResource extends Resource
{
    protected static ?string $model = PlanDisciplina::class;

    public static function getNavigationLabel(): string
    {
        return 'Precios de Planes';
    }

    public static function getNavigationGroup(): string
    {
        return 'Catálogo de Planes';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-chart-bar';
    }

    public static function getModelLabel(): string
    {
        return 'Precio de Plan';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Precios de Planes';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasRole('admin');
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasRole('admin');
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->hasRole('admin');
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasRole('admin');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Asignación de Precio')
                ->description('Define el precio de un plan según la disciplina que lo utilice.')
                ->icon('heroicon-o-currency-dollar')
                ->columns(2)
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
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('plan.nombre')
                    ->label('Plan')
                    ->icon('heroicon-o-clipboard-document')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('disciplina.nombre')
                    ->label('Disciplina')
                    ->icon('heroicon-o-bolt')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('precio')
                    ->label('Precio (Bs.)')
                    ->icon('heroicon-o-currency-dollar')
                    ->money('BOB')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('plan_id')
                    ->label('Plan')
                    ->relationship('plan', 'nombre'),

                Tables\Filters\SelectFilter::make('disciplina_id')
                    ->label('Disciplina')
                    ->relationship('disciplina', 'nombre'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->tooltip('Editar precio'),
                Tables\Actions\DeleteAction::make()->tooltip('Eliminar precio'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
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
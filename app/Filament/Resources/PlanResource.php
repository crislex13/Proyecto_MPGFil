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
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\CheckboxList;

class PlanResource extends Resource
{
    protected static ?string $model = Plan::class;

    public static function getNavigationLabel(): string
    {
        return 'Planes';
    }

    public static function getNavigationGroup(): string
    {
        return 'Catálogo de Planes';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-clipboard-document-list';
    }

    public static function getModelLabel(): string
    {
        return 'Plan';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Planes';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->can('view_any_plan');
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view_any_plan');
    }

    public static function canView($record): bool
    {
        return auth()->user()?->can('view_plan');
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create_plan');
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('update_plan');
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->can('delete_plan');
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->can('delete_any_plan');
    }

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
                ->columns(2)
                ->schema([

                    TimePicker::make('hora_inicio')
                        ->label('Hora de ingreso permitida')
                        ->nullable(),

                    TimePicker::make('hora_fin')
                        ->label('Hora de salida permitida')
                        ->nullable(),

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

                TextColumn::make('hora_inicio')
                    ->label('Hora Inicio')
                    ->icon('heroicon-o-clock')
                    ->formatStateUsing(fn($state) => $state ? \Carbon\Carbon::parse($state)->format('H:i') : '-')
                    ->sortable(),

                TextColumn::make('hora_fin')
                    ->label('Hora Fin')
                    ->icon('heroicon-o-clock')
                    ->formatStateUsing(fn($state) => $state ? \Carbon\Carbon::parse($state)->format('H:i') : '-')
                    ->sortable(),
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
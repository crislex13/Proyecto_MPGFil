<?php

namespace App\Filament\Resources\PlanClienteResource\RelationManagers;

use App\Models\Personal;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Section;

class SesionesAdicionalesRelationManager extends RelationManager
{
    protected static string $relationship = 'sesionesAdicionales';
    protected static ?string $title = 'Sesiones Adicionales';

    public function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Section::make('Datos de la sesión')
                ->schema([
                    Select::make('instructor_id')
                        ->label('Instructor')
                        ->relationship('instructor', 'nombre')
                        ->searchable()
                        ->required(),

                    TextInput::make('tipo_sesion')
                        ->label('Tipo de sesión')
                        ->required()
                        ->placeholder('Ej: Yoga, Boxeo, CrossFit'),

                    DatePicker::make('fecha')
                        ->required()
                        ->label('Fecha de la sesión'),

                    TextInput::make('precio')
                        ->label('Precio (Bs.)')
                        ->numeric()
                        ->required()
                        ->minValue(0),
                ])->columns(2),
        ]);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table->columns([
            TextColumn::make('instructor.nombre')
                ->label('Instructor')
                ->searchable(),

            TextColumn::make('tipo_sesion')
                ->label('Sesión')
                ->searchable(),

            TextColumn::make('fecha')
                ->label('Fecha')
                ->date(),

            TextColumn::make('precio')
                ->label('Precio')
                ->money('BOB'),
        ])
        ->headerActions([
            Tables\Actions\CreateAction::make(),
        ])
        ->actions([
            EditAction::make(),
            DeleteAction::make(),
        ]);
    }
}

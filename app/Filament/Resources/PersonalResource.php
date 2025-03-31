<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PersonalResource\Pages;
use App\Models\Personal;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;

class PersonalResource extends Resource
{
    protected static ?string $model = Personal::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    
    protected static ?string $navigationLabel = 'Personal';
    protected static ?string $navigationGroup = 'Gestion de Personal';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                TextInput::make('nombre')
                    ->required()
                    ->maxLength(255),
                TextInput::make('apellido_paterno')
                    ->required()
                    ->maxLength(255),
                TextInput::make('apellido_materno')
                    ->maxLength(255),
                TextInput::make('ci')
                    ->maxLength(20),
                TextInput::make('telefono')
                    ->maxLength(20),
                TextInput::make('direccion')
                    ->maxLength(255),
                DatePicker::make('fecha_de_nacimiento')
                    ->required()
                    ->label('Fecha de Nacimiento'),
                TextInput::make('correo')
                    ->email()
                    ->maxLength(255),
                TextInput::make('cargo')
                    ->maxLength(255)
                    ->required(),
                TextInput::make('horario')
                    ->maxLength(255)
                    ->required(),
                TextInput::make('salario')
                    ->required()
                    ->numeric()
                    ->minValue(0),
                DatePicker::make('fecha_contratacion')
                    ->required()
                    ->label('Fecha de Contratación'),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('apellido_paterno')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('apellido_materno')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('ci')
                    ->sortable(),
                TextColumn::make('telefono')
                    ->sortable(),
                TextColumn::make('direccion')
                    ->sortable(),
                TextColumn::make('correo')
                    ->sortable(),
                TextColumn::make('cargo')
                    ->sortable(),
                TextColumn::make('horario')
                    ->sortable(),
                TextColumn::make('salario')
                    ->sortable()
                    ->money('BOB'),  // Mostrar en formato monetario (Boliviano)
                TextColumn::make('fecha_contratacion')
                    ->sortable()
                    ->date(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPersonals::route('/'),
            'create' => Pages\CreatePersonal::route('/create'),
            'edit' => Pages\EditPersonal::route('/{record}/edit'),
        ];
    }
}

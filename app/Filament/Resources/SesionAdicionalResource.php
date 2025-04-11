<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SesionAdicionalResource\Pages;
use App\Models\SesionAdicional;
use App\Models\Personal;
use App\Models\Turno;
use App\Models\PlanCliente;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;

class SesionAdicionalResource extends Resource
{
    protected static ?string $model = SesionAdicional::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationGroup = 'Operaciones';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Datos de la sesión')
                ->schema([
                    Select::make('plan_cliente_id')
                        ->label('Cliente')
                        ->options(
                            PlanCliente::with('cliente')
                                ->get()
                                ->pluck('cliente_display_name', 'id')
                        )
                        ->searchable()
                        ->required()
                        ->placeholder('Seleccione el cliente'),

                        Select::make('personal_id')
                        ->label('Instructor')
                        ->options(Personal::all()
                                ->pluck('nombre_completo', 'id')
                        )
                        ->searchable()
                        ->required()
                        ->placeholder('Seleccione el instructor'),

                    Select::make('turno_id')
                        ->label('Turno')
                        ->relationship('turno', 'nombre')
                        ->searchable()
                        ->required()
                        ->placeholder('Seleccione el turno'),

                    DatePicker::make('fecha')
                        ->label('Fecha de la sesión')
                        ->required()
                        ->placeholder('Seleccione la fecha'),

                    TextInput::make('precio')
                        ->label('Precio de la sesión (Bs.)')
                        ->numeric()
                        ->required()
                        ->minValue(1)
                        ->placeholder('Ej: 25'),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('planCliente.cliente.nombre')
                ->label('Cliente')
                ->searchable(),

            TextColumn::make('instructor.nombre')
                ->label('Instructor')
                ->searchable(),

            TextColumn::make('turno.nombre')
                ->label('Turno')
                ->searchable(),

            TextColumn::make('fecha')
                ->label('Fecha')
                ->date()
                ->sortable(),

            TextColumn::make('precio')
                ->label('Precio')
                ->money('BOB')
                ->sortable(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSesionAdicionals::route('/'),
            'create' => Pages\CreateSesionAdicional::route('/create'),
            'edit' => Pages\EditSesionAdicional::route('/{record}/edit'),
        ];
    }
}

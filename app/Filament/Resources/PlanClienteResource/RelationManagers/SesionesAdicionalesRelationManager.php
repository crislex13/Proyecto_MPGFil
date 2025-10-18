<?php

namespace App\Filament\Resources\PlanClienteResource\RelationManagers;

use App\Models\Personal;
use App\Models\Turno;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Section;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Carbon\Carbon;

class SesionesAdicionalesRelationManager extends RelationManager
{
    protected static string $relationship = 'sesionesAdicionales';
    protected static ?string $title = 'Sesiones Adicionales';

    public function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Section::make('Datos de la sesión')
                ->description('Complete cuidadosamente todos los datos de la sesión adicional')
                ->schema([
                    Select::make('instructor_id')
                        ->label('Instructor')
                        ->options(
                            Personal::all()
                                ->pluck('nombre_completo', 'id')
                        )
                        ->searchable()
                        ->required()
                        ->placeholder('Seleccione el instructor')
                        ->reactive(),

                    Select::make('turno_id')
                        ->label('Turno asignado')
                        ->options(function (callable $get) {
                            $instructorId = $get('instructor_id');
                            if (!$instructorId) {
                                return [];
                            }
                            return Turno::where('personal_id', $instructorId)
                                ->where('estado', 'activo')
                                ->pluck('nombre', 'id');
                        })
                        ->searchable()
                        ->required()
                        ->placeholder('Seleccione un turno activo')
                        ->hint(function (callable $get) {
                            $instructorId = $get('instructor_id');
                            if (!$instructorId)
                                return null;
                            $hayActivos = Turno::where('personal_id', $instructorId)
                                ->where('estado', 'activo')
                                ->exists();
                            return !$hayActivos ? '⚠️ El instructor no tiene turnos activos.' : null;
                        })
                        ->disabled(function (callable $get) {
                            $instructorId = $get('instructor_id');
                            if (!$instructorId)
                                return true;
                            return !Turno::where('personal_id', $instructorId)
                                ->where('estado', 'activo')
                                ->exists();
                        })
                        ->reactive(),

                    TextInput::make('tipo_sesion')
                        ->label('Tipo de sesión')
                        ->required()
                        ->placeholder('Ej: Yoga, Boxeo, CrossFit'),

                    DatePicker::make('fecha')
                        ->required()
                        ->label('Fecha de la sesión')
                        ->minDate(Carbon::create(2020, 1, 1))
                        ->maxDate(now())
                        ->validationMessages([
                            'required' => 'La fecha es obligatoria.',
                            'before_or_equal' => 'No puede ser una fecha futura.',
                            'after_or_equal' => 'No puede ser anterior a 2020.',
                        ]),

                    TextInput::make('precio')
                        ->label('Precio (Bs.)')
                        ->numeric()
                        ->required()
                        ->minValue(1)
                        ->maxValue(1000)
                        ->placeholder('Ej: 50.00 Bs')
                        ->validationMessages([
                            'required' => 'El precio es obligatorio.',
                            'numeric' => 'Debe ser un número válido.',
                            'min' => 'El precio mínimo es 1 Bs.',
                            'max' => 'El precio no puede exceder los 1000 Bs.',
                        ]),
                ])
                ->columns(2),
        ]);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                ImageColumn::make('instructor.foto_url')
                    ->label('Foto')
                    ->circular()
                    ->height(40)
                    ->width(40),

                TextColumn::make('instructor.nombre_completo')
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
                CreateAction::make()
                    ->label('Crear sesión adicional'),
            ])
            ->actions([
                EditAction::make()->label('Editar'),
                DeleteAction::make()->label('Borrar'),
            ]);
    }
}
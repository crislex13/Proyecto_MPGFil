<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TurnoResource\Pages;
use App\Models\Turno;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Notifications\Notification;
use Carbon\Carbon;
use Filament\Forms\Components\Placeholder;
use Illuminate\Database\Eloquent\Builder;

class TurnoResource extends Resource
{
    protected static ?string $model = Turno::class;

    public static function getNavigationLabel(): string
    {
        return 'Turnos del Personal';
    }

    public static function getNavigationGroup(): string
    {
        return 'Gestión de Personal';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-clock';
    }

    public static function getModelLabel(): string
    {
        return 'Turno del Personal';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Turnos del Personal';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->can('view_any_turno');
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view_any_turno');
    }

    public static function canView($record): bool
    {
        return auth()->user()?->can('view_turno');
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create_turno');
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('update_turno');
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }


    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Información del Turno')
                ->description('Defina los horarios y estado del turno de trabajo o atención')
                ->icon('heroicon-o-clock')
                ->schema([

                    Select::make('personal_id')
                        ->label('Personal')
                        ->relationship(
                            name: 'personal',
                            titleAttribute: 'nombre_completo',
                            modifyQueryUsing: fn(Builder $query, $search) => $query->where(function ($q) use ($search) {
                                $q->where('nombre', 'like', "%$search%")
                                    ->orWhere('apellido_paterno', 'like', "%$search%")
                                    ->orWhere('apellido_materno', 'like', "%$search%");
                            })
                        )
                        ->getOptionLabelFromRecordUsing(fn($record) => $record->nombre_completo)
                        ->searchable()
                        ->preload()
                        ->required()
                        ->placeholder('Seleccione al instructor'),


                    TextInput::make('nombre')
                        ->label('Nombre del Turno')
                        ->placeholder('Ejemplo: Mañana, Tarde, Noche')
                        ->required()
                        ->maxLength(50)
                        ->columnSpan(2),

                    Select::make('dia')
                        ->label('Día de la Semana')
                        ->placeholder('Seleccione un día')
                        ->options([
                            'lunes' => 'Lunes',
                            'martes' => 'Martes',
                            'miércoles' => 'Miércoles',
                            'jueves' => 'Jueves',
                            'viernes' => 'Viernes',
                            'sábado' => 'Sábado',
                            'domingo' => 'Domingo',
                        ])
                        ->required()
                        ->columnSpan(2),

                    TimePicker::make('hora_inicio')
                        ->label('Hora de Inicio')
                        ->placeholder('Seleccione la hora de inicio')
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(
                            fn($state, callable $set, callable $get) =>
                            self::validarHoras($set, $get)
                        ),

                    TimePicker::make('hora_fin')
                        ->label('Hora de Fin')
                        ->placeholder('Seleccione la hora de fin')
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(
                            fn($state, callable $set, callable $get) =>
                            self::validarHoras($set, $get)
                        ),

                    Placeholder::make('duracion_turno')
                        ->label('Duración estimada del turno')
                        ->content(fn($get) => self::calcularDuracion($get('hora_inicio'), $get('hora_fin')))
                        ->visible(fn($get) => $get('hora_inicio') && $get('hora_fin'))
                        ->columnSpan(2),

                    Select::make('estado')
                        ->label('Estado del Turno')
                        ->placeholder('Seleccione el estado')
                        ->options([
                            'activo' => 'Activo',
                            'inactivo' => 'Inactivo',
                        ])
                        ->default('activo')
                        ->required()
                        ->columnSpan(2),
                ])
                ->columns(2)
                ->collapsible(),
        ]);
    }

    protected static function validarHoras(callable $set, callable $get): void
    {
        $inicio = $get('hora_inicio');
        $fin = $get('hora_fin');

        if ($inicio && $fin && $inicio >= $fin) {
            $set('hora_fin', null); // Limpiar hora_fin si no es válida
            Notification::make()
                ->title('Error en el horario')
                ->body('La hora de fin debe ser posterior a la hora de inicio.')
                ->danger()
                ->send();
        }
    }
    protected static function calcularDuracion(?string $inicio, ?string $fin): string
    {
        if (!$inicio || !$fin)
            return '—';

        try {
            $inicio = Carbon::parse($inicio);
            $fin = Carbon::parse($fin);
        } catch (\Throwable $e) {
            return 'Formato inválido';
        }

        $duracion = $inicio->diff($fin);

        return $duracion->format('%h horas %i minutos');
    }
    protected static function booted(): void
    {
        static::saving(function ($turno) {
            if ($turno->hora_inicio && $turno->hora_fin) {
                $inicio = Carbon::createFromFormat('H:i:s', $turno->hora_inicio);
                $fin = Carbon::createFromFormat('H:i:s', $turno->hora_fin);

                if ($fin->greaterThan($inicio)) {
                    $turno->duracion_minutos = $inicio->diffInMinutes($fin);
                } else {
                    $turno->duracion_minutos = null;
                }
            }
        });
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('personal.nombre_completo')
                    ->label('Personal')
                    ->searchable(['personals.nombre', 'personals.apellido_paterno', 'personals.apellido_materno']) // <- ✅ tabla real
                    ->icon('heroicon-o-user-circle')
                    ->sortable(),

                TextColumn::make('nombre')
                    ->label('Nombre del Turno')
                    ->icon('heroicon-o-identification')
                    ->searchable()
                    ->sortable(),

                BadgeColumn::make('dia')
                    ->label('Día')
                    ->colors([
                        'gray' => fn($state) => in_array($state, ['lunes', 'martes', 'miércoles', 'jueves', 'viernes']),
                        'warning' => fn($state) => $state === 'sábado',
                        'danger' => fn($state) => $state === 'domingo',
                    ])
                    ->sortable(),

                TextColumn::make('hora_inicio')
                    ->label('Hora de Inicio')
                    ->icon('heroicon-o-clock')
                    ->sortable(),

                TextColumn::make('hora_fin')
                    ->label('Hora de Fin')
                    ->icon('heroicon-o-clock')
                    ->sortable(),

                TextColumn::make('duracion_minutos')
                    ->label('Duración')
                    ->icon('heroicon-o-clock')
                    ->formatStateUsing(fn($state) => $state ? floor($state / 60) . 'h ' . ($state % 60) . 'm' : '—')
                    ->sortable(),

                BadgeColumn::make('estado')
                    ->label('Estado')
                    ->colors([
                        'success' => fn($state): bool => $state === 'activo',
                        'danger' => fn($state): bool => $state === 'inactivo',
                    ])
                    ->formatStateUsing(fn($state) => $state === 'activo' ? 'Activo' : 'Inactivo')
                    ->sortable()
                    ->icon('heroicon-o-light-bulb'),

                TextColumn::make('registradoPor.name')
                    ->label('Registrado por')
                    ->icon('heroicon-o-user-plus')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visible(fn() => auth()->user()?->hasRole('admin')),

                TextColumn::make('modificadoPor.name')
                    ->label('Modificado por')
                    ->icon('heroicon-o-pencil-square')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visible(fn() => auth()->user()?->hasRole('admin')),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('estado')
                    ->label('Filtrar por Estado')
                    ->options([
                        'activo' => 'Activo',
                        'inactivo' => 'Inactivo',
                    ])
                    ->placeholder('Todos'),

                Tables\Filters\SelectFilter::make('dia')
                    ->label('Filtrar por Día')
                    ->options([
                        'lunes' => 'Lunes',
                        'martes' => 'Martes',
                        'miércoles' => 'Miércoles',
                        'jueves' => 'Jueves',
                        'viernes' => 'Viernes',
                        'sábado' => 'Sábado',
                        'domingo' => 'Domingo',
                    ])
                    ->placeholder('Todos'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),

                Tables\Actions\Action::make('ver')
                    ->label('Ver Detalles')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->modalHeading('Detalles del Turno')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Cerrar')
                    ->form(fn(Turno $record) => [
                        Section::make('Información del Turno')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                TextInput::make('nombre')
                                    ->label('Nombre del Turno')
                                    ->default($record->nombre)
                                    ->disabled(),

                                TextInput::make('hora_inicio')
                                    ->label('Hora de Inicio')
                                    ->default($record->hora_inicio)
                                    ->disabled(),

                                TextInput::make('hora_fin')
                                    ->label('Hora de Fin')
                                    ->default($record->hora_fin)
                                    ->disabled(),

                                TextInput::make('duracion')->label('Duración')->default(
                                    $record->duracion_minutos
                                    ? floor($record->duracion_minutos / 60) . 'h ' . ($record->duracion_minutos % 60) . 'm'
                                    : '—'
                                )->disabled(),

                                TextInput::make('estado')
                                    ->label('Estado')
                                    ->default(match ($record->estado) {
                                        'activo' => 'Activo',
                                        'inactivo' => 'Inactivo',
                                        default => 'Desconocido',
                                    })
                                    ->disabled(),
                            ])
                            ->columns(2),
                    ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTurnos::route('/'),
            'create' => Pages\CreateTurno::route('/create'),
            'edit' => Pages\EditTurno::route('/{record}/edit'),
        ];
    }

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        $data['registrado_por'] = auth()->id();
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['modificado_por'] = auth()->id();
        return $data;
    }
}

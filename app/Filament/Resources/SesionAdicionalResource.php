<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SesionAdicionalResource\Pages;
use App\Models\SesionAdicional;
use App\Models\Personal;
use App\Models\Turno;
use App\Models\Clientes;
use App\Models\PlanCliente;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\ImageColumn;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Carbon\Carbon;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Components\TimePicker;
use Illuminate\Support\Str;


class SesionAdicionalResource extends Resource
{
    protected static ?string $model = SesionAdicional::class;

    public static function getNavigationLabel(): string
    {
        return 'Sesiones de Cliente';
    }

    public static function getNavigationGroup(): string
    {
        return 'Administración de Clientes';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-calendar-days';
    }

    public static function getModelLabel(): string
    {
        return 'Sesión de Cliente';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Sesiones de Clientes';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->can('view_any_sesion::adicional');
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view_any_sesion::adicional');
    }

    public static function canView($record): bool
    {
        return auth()->user()?->can('view_sesion::adicional');
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create_sesion::adicional');
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('update_sesion::adicional');
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasRole('admin') === true;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->hasRole('admin') === true;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Datos de la sesión')
                ->description('Complete cuidadosamente todos los datos de la sesión adicional')
                ->columns(2)
                ->schema([
                    Select::make('cliente_id')
                        ->label('Cliente')
                        ->options(Clientes::all()->pluck('nombre_completo', 'id'))
                        ->searchable()
                        ->required()
                        ->placeholder('Seleccione al cliente para esta sesión'),

                    // NUEVO: Disciplina (reemplaza tipo_sesion)
                    Select::make('disciplina_id')
                        ->label('Disciplina')
                        ->relationship('disciplina', 'nombre')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->reactive(),

                    // MODIFICADO: Instructor filtrado por disciplina
                    Select::make('instructor_id')
                        ->label('Instructor')
                        ->options(function (Get $get) {
                            $disciplinaId = $get('disciplina_id');

                            return Personal::query()
                                // solo instructores (case-insensitive)
                                ->whereRaw('LOWER(cargo) = "instructor"')
                                // si hay disciplina, filtra por la relación
                                ->when(
                                    $disciplinaId,
                                    fn($q) =>
                                    $q->whereHas('disciplinas', fn($qq) => $qq->where('disciplinas.id', $disciplinaId))
                                )
                                ->orderBy('apellido_paterno')
                                ->get()
                                ->mapWithKeys(fn($p) => [$p->id => $p->nombre_completo]);
                        })
                        ->searchable()
                        ->required()
                        ->placeholder('Seleccione el instructor responsable')
                        ->preload()
                        ->reactive()
                        ->afterStateUpdated(fn(Set $set) => $set('turno_id', null)),

                    DatePicker::make('fecha')
                        ->label('Fecha de la sesión')
                        ->required()
                        // ventana de selección: -6 meses … +6 meses
                        ->minDate(now()->subMonthsNoOverflow(6)->startOfDay())
                        ->maxDate(now()->addMonthsNoOverflow(6)->endOfDay())
                        ->placeholder('Seleccione la fecha')
                        ->reactive()
                        ->afterStateUpdated(fn(Set $set) => $set('turno_id', null))
                        ->validationMessages([
                            'required' => 'La fecha es obligatoria.',
                            'after_or_equal' => 'La fecha no puede ser anterior a hace 6 meses.',
                            'before_or_equal' => 'La fecha no puede ser posterior a dentro de 6 meses.',
                        ]),


                    // MODIFICADO: Turno filtrado por instructor (+ día según fecha)
                    Select::make('turno_id')
                        ->label('Turno asignado')
                        ->options(function (Get $get) {
                            $instructorId = $get('instructor_id');
                            $fecha = $get('fecha');

                            if (!$instructorId) {
                                return [];
                            }

                            $q = Turno::query()
                                ->where('personal_id', $instructorId)
                                ->where('estado', 'activo');

                            if ($fecha) {
                                $dow = Carbon::parse($fecha)->isoWeekday(); // 1..7
                                $q->where('dia', (int) $dow);
                            }

                            // Usamos un display claro: "Lunes 08:00–09:00 (Mañana)"
                            $map = [1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'];

                            return $q->get()->mapWithKeys(function ($t) use ($map) {
                                $dia = $map[(int) $t->dia] ?? $t->dia;
                                $label = "{$dia} {$t->hora_inicio}–{$t->hora_fin} ({$t->nombre})";
                                return [$t->id => $label];
                            });
                        })
                        ->searchable()
                        ->required()
                        ->placeholder('Seleccione un turno activo')
                        ->disabled(fn(Get $get) => !$get('instructor_id'))
                        ->preload()
                        ->reactive()
                        ->afterStateUpdated(function ($state, Set $set) {
                            if (!$state)
                                return;
                            $turno = Turno::find($state);
                            if ($turno) {
                                $set('hora_inicio', $turno->hora_inicio);
                                $set('hora_fin', $turno->hora_fin);
                            }
                        }),

                    TimePicker::make('hora_inicio')
                        ->label('Hora inicio (opcional)')
                        ->seconds(false),

                    TimePicker::make('hora_fin')
                        ->label('Hora fin (opcional)')
                        ->seconds(false)
                        ->after('hora_inicio'),

                    TextInput::make('precio')
                        ->label('Precio (Bs.)')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(500)
                        ->required()
                        ->helperText('Debe estar entre 0 y 500 Bs.')
                        ->placeholder('Ej: 50.00 Bs'),

                    Section::make('Control de cambios')
                        ->icon('heroicon-o-user-circle')
                        ->collapsible()
                        ->columns(1)
                        ->visible(fn() => auth()->user()?->hasRole('admin'))
                        ->schema([
                            \Filament\Forms\Components\Placeholder::make('registrado_por')
                                ->label('Registrado por')
                                ->content(fn($record) => optional($record?->registradoPor)->name ?? 'No registrado'),

                            \Filament\Forms\Components\Placeholder::make('modificado_por')
                                ->label('Modificado por')
                                ->content(fn($record) => optional($record?->modificadoPor)->name ?? 'Sin cambios'),
                        ]),
                ]),
        ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['disciplina', 'turno', 'instructor', 'planCliente.cliente']);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('cliente.foto_url')
                    ->label('Foto Cliente')
                    ->circular()
                    ->height(40),

                TextColumn::make('cliente.nombre_completo')
                    ->label('Cliente')
                    ->icon('heroicon-o-user')
                    ->searchable(
                        query: fn($query, $search) =>
                        $query->whereHas(
                            'cliente',
                            fn($q) =>
                            $q->where('nombre', 'like', "%$search%")
                                ->orWhere('apellido_paterno', 'like', "%$search%")
                                ->orWhere('apellido_materno', 'like', "%$search%")
                        )
                    )
                    ->sortable(['nombre', 'apellido_paterno', 'apellido_materno']),

                ImageColumn::make('instructor.foto_url')
                    ->label('Foto Instructor')
                    ->circular()
                    ->height(40),

                TextColumn::make('instructor.nombre_completo')
                    ->label('Instructor')
                    ->icon('heroicon-o-user-group')
                    ->searchable(
                        query: fn($query, $search) =>
                        $query->whereHas(
                            'instructor',
                            fn($q) =>
                            $q->where('nombre', 'like', "%$search%")
                                ->orWhere('apellido_paterno', 'like', "%$search%")
                                ->orWhere('apellido_materno', 'like', "%$search%")
                        )
                    ),

                TextColumn::make('turno.display_horario')
                    ->label('Turno')
                    ->icon('heroicon-o-clock')
                    ->sortable(),

                TextColumn::make('disciplina.nombre')
                    ->label('Disciplina')
                    ->icon('heroicon-o-rectangle-group')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('fecha')
                    ->label('Fecha')
                    ->icon('heroicon-o-calendar-days')
                    ->date()
                    ->sortable(),

                TextColumn::make('precio')
                    ->label('Precio')
                    ->icon('heroicon-o-currency-dollar')
                    ->money('BOB')
                    ->sortable(),

                TextColumn::make('registradoPor.name')
                    ->label('Registrado por')
                    ->icon('heroicon-o-user-plus')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable()
                    ->visible(fn() => auth()->user()?->hasRole('admin')),

                TextColumn::make('modificadoPor.name')
                    ->label('Modificado por')
                    ->icon('heroicon-o-pencil-square')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable()
                    ->visible(fn() => auth()->user()?->hasRole('admin')),
            ])
            ->filters([

                Tables\Filters\SelectFilter::make('disciplina_id')
                    ->label('Disciplina')
                    ->relationship('disciplina', 'nombre')
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn() => auth()->user()?->hasRole('admin'))
                    ->authorize(fn() => auth()->user()?->hasRole('admin'))
                    ->requiresConfirmation()
                    ->successNotificationTitle('Sesión eliminada'),
            ])
            ->headerActions([
                Tables\Actions\Action::make('reporteSesionesDia')
                    ->label('PDF Diario (hoy)')
                    ->icon('heroicon-o-document-arrow-down')
                    ->url(fn() => route('reportes.sesiones.dia', ['date' => now()->toDateString()]))
                    ->openUrlInNewTab()
                    ->visible(fn() => auth()->user()?->hasRole('admin')),

                Tables\Actions\Action::make('reporteSesionesMes')
                    ->label('PDF Mensual (actual)')
                    ->icon('heroicon-o-document-arrow-down')
                    ->url(fn() => route('reportes.sesiones.mes', ['year' => now()->year, 'month' => now()->month]))
                    ->openUrlInNewTab()
                    ->visible(fn() => auth()->user()?->hasRole('admin')),

                Tables\Actions\Action::make('reporteSesionesAnio')
                    ->label('PDF Anual (actual)')
                    ->icon('heroicon-o-document-arrow-down')
                    ->url(fn() => route('reportes.sesiones.anio', ['year' => now()->year]))
                    ->openUrlInNewTab()
                    ->visible(fn() => auth()->user()?->hasRole('admin')),
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

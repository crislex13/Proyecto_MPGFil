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
        return auth()->user()?->hasAnyRole(['admin', 'recepcionista', 'supervisor']);
    }

    public static function canCreate(): bool
    {
        return self::shouldRegisterNavigation();
    }

    public static function canEdit($record): bool
    {
        return self::shouldRegisterNavigation();
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasRole('admin');
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

                    Select::make('instructor_id')
                        ->label('Instructor')
                        ->options(Personal::all()->pluck('nombre_completo', 'id'))
                        ->searchable()
                        ->required()
                        ->placeholder('Seleccione el instructor responsable')
                        ->reactive(),

                    Select::make('turno_id')
                        ->label('Turno asignado')
                        ->options(function (callable $get) {
                            $instructorId = $get('instructor_id');

                            if (!$instructorId)
                                return [];

                            return Turno::where('personal_id', $instructorId)
                                ->where('estado', 'activo')
                                ->get()
                                ->pluck('display_horario', 'id');
                        })
                        ->searchable()
                        ->required()
                        ->placeholder('Seleccione un turno activo')
                        ->disabled(fn(callable $get) => !$get('instructor_id'))
                        ->reactive(),

                    TextInput::make('tipo_sesion')
                        ->label('Tipo de sesión')
                        ->required()
                        ->placeholder('Ej: Zumba, Crossfit, Yoga'),

                    DatePicker::make('fecha')
                        ->label('Fecha de la sesión')
                        ->required()
                        ->placeholder('Seleccione la fecha'),

                    TextInput::make('precio')
                        ->label('Precio (Bs.)')
                        ->numeric()
                        ->minValue(1)
                        ->required()
                        ->placeholder('Ej: 50.00 Bs'),
                ]),
        ]);
    }

    public static function getEloquentQuery(): EloquentBuilder
    {
        return parent::getEloquentQuery()
            ->with(['turno', 'instructor', 'planCliente.cliente']);
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
                    ->sortable(),

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

                TextColumn::make('tipo_sesion')
                    ->label('Tipo')
                    ->icon('heroicon-o-rectangle-group')
                    ->sortable(),

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
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('cliente_id')
                    ->label('Cliente')
                    ->relationship('cliente', 'nombre')
                    ->searchable(),

                Tables\Filters\SelectFilter::make('instructor_id')
                    ->label('Instructor')
                    ->relationship('instructor', 'nombre')
                    ->searchable(),

                Tables\Filters\Filter::make('fecha')
                    ->form([
                        DatePicker::make('desde')->label('Desde'),
                        DatePicker::make('hasta')->label('Hasta'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['desde'], fn($q) => $q->whereDate('fecha', '>=', $data['desde']))
                            ->when($data['hasta'], fn($q) => $q->whereDate('fecha', '<=', $data['hasta']));
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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

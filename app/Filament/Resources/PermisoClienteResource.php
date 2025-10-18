<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PermisoClienteResource\Pages;
use App\Models\PermisoCliente;
use App\Models\Clientes;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Forms\Components\{
    Section,
    Select,
    DatePicker,
    Textarea,
    Placeholder
};
use Filament\Tables\Columns\{
    TextColumn,
    BadgeColumn
};
use Filament\Tables;
use Illuminate\Support\Carbon;
use Filament\Notifications\Notification;

class PermisoClienteResource extends Resource
{
    protected static ?string $model = PermisoCliente::class;

    public static function getNavigationLabel(): string
    {
        return 'Permisos de Clientes';
    }

    public static function getNavigationGroup(): string
    {
        return 'Control de Accesos';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-calendar';
    }

    public static function getModelLabel(): string
    {
        return 'Permiso de Cliente';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Permisos de Clientes';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->can('view_any_permiso::cliente');
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view_any_permiso::cliente');
    }

    public static function canView($record): bool
    {
        return auth()->user()?->can('view_permiso::cliente');
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create_permiso::cliente');
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('update_permiso::cliente');
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
            Section::make('Solicitud de Permiso')
                ->icon('heroicon-o-document-text')
                ->columns(2)
                ->schema([
                    Select::make('cliente_id')
                        ->label('Cliente')
                        ->options(Clientes::all()->pluck('nombre_completo', 'id'))
                        ->searchable()
                        ->required()
                        ->placeholder('Selecciona un cliente'),

                    DatePicker::make('fecha')
                        ->label('Fecha del permiso')
                        ->required()
                        ->minDate(fn(callable $get) => optional(
                            \App\Models\PlanCliente::where('cliente_id', $get('cliente_id'))
                                ->where('estado', 'vigente')
                                ->orderByDesc('fecha_inicio')
                                ->first()
                        )?->fecha_inicio)
                        ->maxDate(fn(callable $get) => optional(
                            \App\Models\PlanCliente::where('cliente_id', $get('cliente_id'))
                                ->where('estado', 'vigente')
                                ->orderByDesc('fecha_inicio')
                                ->first()
                        )?->fecha_final)
                        ->placeholder('Selecciona la fecha')
                        ->afterStateUpdated(function ($state, callable $get) {
                            $plan = \App\Models\PlanCliente::where('cliente_id', $get('cliente_id'))
                                ->where('estado', 'vigente')
                                ->orderByDesc('fecha_inicio')
                                ->first();

                            if ($plan) {
                                $fecha = \Carbon\Carbon::parse($state);
                                if ($fecha->lt($plan->fecha_inicio) || $fecha->gt($plan->fecha_final)) {
                                    Notification::make()
                                        ->title('⚠️ Fecha fuera del rango del plan')
                                        ->body("La fecha seleccionada no está dentro de las fechas del plan vigente del cliente ({$plan->fecha_inicio->format('d/m/Y')} al {$plan->fecha_final->format('d/m/Y')}).")
                                        ->danger()
                                        ->send();
                                }
                            }
                        }),

                    Textarea::make('motivo')
                        ->label('Motivo del permiso')
                        ->placeholder('Ej: Viaje familiar, enfermedad...')
                        ->rows(3),

                    Select::make('estado')
                        ->label('Estado')
                        ->options([
                            'pendiente' => 'Pendiente',
                            'aprobado' => 'Aprobado',
                            'rechazado' => 'Rechazado',
                        ])
                        ->default('pendiente')
                        ->required(),
                ]),

            Section::make('Autorización')
                ->icon('heroicon-o-user')
                ->columns(1)
                ->schema([
                    Placeholder::make('autorizado_por')
                        ->label('Autorizado por')
                        ->content(fn($record) => optional($record?->autorizadoPor)->name ?? 'Aún no autorizado'),
                ]),
        ]);
    }


    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('cliente.nombre_completo')
                ->label('Cliente')
                ->icon('heroicon-o-user')
                ->searchable(['nombre', 'apellido_paterno', 'apellido_materno'])
                ->sortable(['nombre', 'apellido_paterno', 'apellido_materno']),

            TextColumn::make('fecha')
                ->label('Fecha')
                ->icon('heroicon-o-calendar')
                ->date()
                ->sortable(),

            BadgeColumn::make('estado')
                ->label('Estado')
                ->colors([
                    'gray' => 'pendiente',
                    'success' => 'aprobado',
                    'danger' => 'rechazado',
                ])
                ->sortable(),

            TextColumn::make('motivo')
                ->label('Motivo')
                ->limit(50),
        ])->filters([
                    Tables\Filters\SelectFilter::make('estado')
                        ->label('Estado')
                        ->options([
                            'pendiente' => 'Pendiente',
                            'aprobado' => 'Aprobado',
                            'rechazado' => 'Rechazado',
                        ]),

                    Tables\Filters\SelectFilter::make('cliente_id')
                        ->label('Cliente')
                        ->relationship('cliente', 'nombre_completo')
                        ->searchable(),
                ])->actions([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPermisoClientes::route('/'),
            'create' => Pages\CreatePermisoCliente::route('/create'),
            'edit' => Pages\EditPermisoCliente::route('/{record}/edit'),
        ];
    }
}

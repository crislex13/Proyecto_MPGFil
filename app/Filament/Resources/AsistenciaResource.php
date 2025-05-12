<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AsistenciaResource\Pages;
use App\Models\Asistencia;
use App\Models\Clientes;
use App\Models\Personal;
use Filament\Resources\Resource;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action as TableAction;

use Filament\Tables\Actions\Action;
use Filament\Actions\Action as FormAction;
use Filament\Tables\Filters\DateFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;


class AsistenciaResource extends Resource
{
    protected static ?string $model = Asistencia::class;

    public static function getNavigationLabel(): string
    {
        return 'Asistencias';
    }

    public static function getNavigationGroup(): string
    {
        return 'Control de Accesos';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-check-circle';
    }
    public static function getModelLabel(): string
    {
        return 'Asistencia';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Asistencias';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasAnyRole(['admin', 'supervisor', 'recepcionista']);
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
            TextInput::make('ci')
                ->label('CI')
                ->required()
                ->placeholder('Ingrese C.I. para registrar asistencia'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table

            ->persistSearchInSession()
            ->searchPlaceholder('Buscar por nombre o CI')
            ->columns([
                ImageColumn::make('foto_url')
                    ->label('Foto')
                    ->circular()
                    ->height(40),

                TextColumn::make('nombre_completo')
                    ->label('Nombre')
                    ->getStateUsing(
                        fn($record) =>
                        $record->cliente?->nombre_completo ??
                        $record->personal?->nombre_completo ??
                        '—'
                    ),

                TextColumn::make('ci')
                    ->label('CI')
                    ->icon('heroicon-o-identification')
                    ->getStateUsing(
                        fn($record) =>
                        $record->asistible?->ci ?? '—'
                    )
                    ->extraAttributes(['style' => 'width: 100px; white-space: nowrap']),

                TextColumn::make('rol')
                    ->label('Rol')
                    ->icon('heroicon-o-user-circle'),

                TextColumn::make('tipo_asistencia')
                    ->label('Tipo')
                    ->icon('heroicon-o-finger-print'),

                TextColumn::make('hora_entrada')
                    ->label('Entrada')
                    ->icon('heroicon-o-clock')
                    ->dateTime()
                    ->getStateUsing(fn($record) => $record?->hora_entrada),

                TextColumn::make('hora_salida')
                    ->label('Salida')
                    ->icon('heroicon-o-clock')
                    ->dateTime()
                    ->placeholder('—'),

                TextColumn::make('estado')
                    ->label('Estado')
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->badge()
                    ->color(fn(?string $state): string => match ($state) {
                        'puntual' => 'success',
                        'atrasado' => 'warning',
                        'permiso' => 'info',
                        'acceso_denegado' => 'danger',
                        'falta' => 'gray',
                        default => 'secondary',
                    }),

                TextColumn::make('observacion')
                    ->label('Observación')
                    ->icon('heroicon-o-information-circle')
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('origen')
                    ->label('Origen')
                    ->icon('heroicon-o-finger-print'),

                TextColumn::make('usuarioRegistro.name')
                    ->label('Registrado por')
                    ->icon('heroicon-o-user-circle'),

                TextColumn::make('asistible_type')
                    ->label('Buscador')
                    ->searchable()
                    ->visible(false), // Oculta esta columna
            ])

            ->defaultSort('hora_entrada', 'desc')

            ->filters([
                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options([
                        'puntual' => 'Puntual',
                        'atrasado' => 'Atrasado',
                        'permiso' => 'Permiso',
                        'acceso_denegado' => 'Acceso Denegado',
                        'falta' => 'Falta',
                    ]),

                SelectFilter::make('tipo_asistencia')
                    ->label('Tipo')
                    ->options([
                        'plan' => 'Plan',
                        'personal' => 'Personal',
                        'sesion' => 'Sesión adicional',
                    ]),

                SelectFilter::make('origen')
                    ->label('Origen')
                    ->options([
                        'manual' => 'Manual',
                        'biometrico' => 'Biométrico',
                        'automatico' => 'Automático',
                    ]),

                Filter::make('fecha')
                    ->form([
                        DatePicker::make('fecha')
                            ->label('Selecciona una fecha'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['fecha'], fn($query, $date) =>
                                $query->whereDate('fecha', $date));
                    })
                    ->label('Filtrar por Fecha'),
            ])

            ->actions([
                TableAction::make('registrar_salida')
                    ->label('Registrar salida')
                    ->color('success')
                    ->icon('heroicon-o-arrow-right-circle')
                    ->visible(
                        fn($record) =>
                        $record->tipo_asistencia === 'personal' &&
                        $record->hora_salida === null
                    )
                    ->requiresConfirmation()
                    ->action(fn($record) => $record->update([
                        'hora_salida' => now(),
                    ])),
            ])


            ->headerActions([
                Action::make('registrarAsistencia')
                    ->label('Registrar Asistencia por CI')
                    ->icon('heroicon-o-identification')
                    ->form([
                        TextInput::make('ci')
                            ->label('C.I.')
                            ->required()
                            ->placeholder('Ingresa el CI')
                    ])
                    ->action(function (array $data) {
                        $ci = trim($data['ci']);
                        $cliente = Clientes::where('ci', $ci)->first();
                        $personal = Personal::where('ci', $ci)->first();

                        if ($cliente && !$personal) {
                            app(\App\Filament\Resources\AsistenciaResource\Pages\ListAsistencias::class)
                                ->registrarComoClienteManual($cliente);
                        } elseif (!$cliente && $personal) {
                            app(\App\Filament\Resources\AsistenciaResource\Pages\ListAsistencias::class)
                                ->registrarComoPersonalManual($personal);
                        } elseif ($cliente && $personal) {
                            session()->flash('ci_preseleccionado', $ci);
                            redirect('/admin/asistencias');
                        } else {
                            Notification::make()
                                ->title('❌ CI no registrado')
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->modifyQueryUsing(function (Builder $query, $livewire) {
                $search = $livewire->getTableSearch();

                if ($search) {
                    $query->whereHas('asistible', function ($sub) use ($search) {
                        $sub->where(function ($s) use ($search) {
                            $s->where('nombre', 'like', "%{$search}%")
                                ->orWhere('apellido_paterno', 'like', "%{$search}%")
                                ->orWhere('apellido_materno', 'like', "%{$search}%")
                                ->orWhere('ci', 'like', "%{$search}%");
                        });
                    });
                }

                return $query;
            });
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAsistencias::route('/'),
        ];
    }
}
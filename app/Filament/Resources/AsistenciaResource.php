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
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

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
        return auth()->user()?->can('view_any_asistencia');
    }
    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view_any_asistencia');
    }
    public static function canView($record): bool
    {
        return auth()->user()?->can('view_asistencia');
    }
    public static function canCreate(): bool
    {
        return auth()->user()?->can('create_asistencia');
    }
    public static function canEdit($record): bool
    {
        return auth()->user()?->can('update_asistencia');
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
        // El form aquí no hace marcación; queda como placeholder si lo necesitas luego.
        return $form->schema([
            TextInput::make('ci')
                ->label('CI')
                ->placeholder('Este formulario no marca asistencias. Usa el botón del listado.')
                ->disabled(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->persistSearchInSession()
            ->searchPlaceholder('Buscar por nombre o CI')
            ->defaultSort('hora_entrada', 'desc')
            ->columns([
                ImageColumn::make('foto_url')
                    ->label('Foto')
                    ->circular()
                    ->height(40)
                    ->getStateUsing(fn($record) => $record->asistible?->foto_url ?? null),

                TextColumn::make('nombre_completo')
                    ->label('Nombre')
                    ->getStateUsing(
                        fn($record) =>
                        $record->asistible?->nombre_completo ?? '—'
                    )
                    ->searchable(isIndividual: false),

                TextColumn::make('ci')
                    ->label('CI')
                    ->icon('heroicon-o-identification')
                    ->getStateUsing(fn($record) => $record->asistible?->ci ?? '—')
                    ->extraAttributes(['style' => 'width: 110px; white-space: nowrap']),

                TextColumn::make('rol')
                    ->label('Rol')
                    ->icon('heroicon-o-user-circle')
                    ->state(function ($record) {
                        return match ($record->asistible_type) {
                            Clientes::class => 'Cliente',
                            Personal::class => 'Personal',
                            default => '—',
                        };
                    }),

                TextColumn::make('tipo_asistencia')
                    ->label('Tipo')
                    ->icon('heroicon-o-finger-print'),

                TextColumn::make('hora_entrada')
                    ->label('Entrada')
                    ->icon('heroicon-o-clock')
                    ->formatStateUsing(fn($state) => $state ? Carbon::parse($state)->format('Y-m-d H:i') : '—'),

                TextColumn::make('hora_salida')
                    ->label('Salida')
                    ->icon('heroicon-o-clock')
                    ->formatStateUsing(fn($state) => $state ? Carbon::parse($state)->format('Y-m-d H:i') : '—')
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

                TextColumn::make('min_restantes')
                    ->label('Restante')
                    ->formatStateUsing(
                        fn(?int $state): string =>
                        is_null($state) ? '—' : ($state <= 0 ? 'Finalizado' : $state . ' min')
                    )
                    ->badge()
                    ->color(
                        fn(?int $state): string =>
                        is_null($state) ? 'gray' : ($state <= 5 ? 'danger' : ($state <= 15 ? 'warning' : 'secondary'))
                    ),

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
                    ->icon('heroicon-o-user-circle')
                    ->toggleable(isToggledHiddenByDefault: true),

                // Para facilitar búsquedas por tipo sin mostrarlo
                TextColumn::make('asistible_type')
                    ->label('Buscador')
                    ->searchable()
                    ->visible(false),
            ])
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
                    ->form([DatePicker::make('fecha')->label('Selecciona una fecha')])
                    ->query(
                        fn($query, array $data) =>
                        $query->when($data['fecha'] ?? null, fn($q, $date) => $q->whereDate('fecha', $date))
                    )
                    ->label('Filtrar por Fecha'),
            ])
            ->actions([
                TableAction::make('registrar_salida')
                    ->label('Registrar salida')
                    ->color('success')
                    ->icon('heroicon-o-arrow-right-circle')
                    ->visible(fn($record) => is_null($record->hora_salida))
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        if ($record->hora_salida) {
                            Notification::make()->title('Ya tiene salida')->warning()->send();
                            return;
                        }
                        $record->update(['hora_salida' => now()]);
                        Notification::make()->title('Salida registrada')->success()->send();
                    }),
            ])
            ->modifyQueryUsing(function (Builder $query, $livewire) {
                // Búsqueda por nombre/apellidos/CI del asistible
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

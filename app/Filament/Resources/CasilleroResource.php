<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CasilleroResource\Pages;
use App\Models\Casillero;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Set;
use App\Models\Clientes;
use Filament\Forms\Components\Section;



class CasilleroResource extends Resource
{
    protected static ?string $model = Casillero::class;

    public static function getNavigationLabel(): string
    {
        return 'Casilleros';
    }

    public static function getNavigationGroup(): string
    {
        return 'Administración de Clientes';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-lock-closed';
    }
    public static function getModelLabel(): string
    {
        return 'Casillero';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Casilleros';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->can('view_any_casillero');
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view_any_casillero');
    }

    public static function canView($record): bool
    {
        return auth()->user()?->can('view_casillero');
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create_casillero');
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('update_casillero');
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->can('delete_casillero');
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->can('delete_any_casillero');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Datos del Casillero')->schema([
                Select::make('cliente_id')
                    ->label('Cliente asignado')
                    ->options(Clientes::all()->pluck('nombre_completo', 'id'))
                    ->searchable()
                    ->placeholder('Seleccione un cliente')
                    ->nullable(),

                TextInput::make('numero')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->placeholder('Ej: C-001')
                    ->label('Número de casillero'),

                Select::make('estado')
                    ->label('Estado')
                    ->options([
                        'disponible' => 'Disponible',
                        'ocupado' => 'Ocupado',
                        'mantenimiento' => 'Mantenimiento',
                    ])
                    ->required()
                    ->placeholder('Seleccione el estado actual'),

                TextInput::make('costo_mensual')
                    ->label('Costo mensual (Bs.)')
                    ->numeric()
                    ->default(40)
                    ->disabled()
                    ->dehydrated()
                    ->placeholder('Costo fijo por 30 días'),

                TextInput::make('total_reposiciones')
                    ->label('Reposiciones realizadas')
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->live(onBlur: true)
                    ->dehydrated()
                    ->placeholder('Cantidad de llaves perdidas')
                    ->afterStateUpdated(function ($state, Set $set) {
                        $costoReposicion = 10;
                        $set('monto_reposiciones', $state * $costoReposicion);
                    }),

                TextInput::make('monto_reposiciones')
                    ->label('Monto por reposiciones (Bs.)')
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->disabled()
                    ->dehydrated()
                    ->placeholder('Se calcula automáticamente'),

                DatePicker::make('fecha_entrega_llave')
                    ->label('Fecha de inicio de uso')
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, Set $set) {
                        if ($state) {
                            $fechaFinal = Carbon::parse($state)->addDays(29);
                            $set('fecha_final_llave', $fechaFinal->toDateString());
                        }
                    })
                    ->placeholder('Seleccionar fecha de entrega'),

                DatePicker::make('fecha_final_llave')
                    ->label('Fecha de vencimiento')
                    ->disabled()
                    ->dehydrated()
                    ->placeholder('Se calculará automáticamente'),
            ])->columns(2)
                ->description('Complete los datos del alquiler de casillero')
                ->collapsible()
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                ImageColumn::make('cliente.foto_url')
                    ->label('Foto')
                    ->circular()
                    ->height(40)
                    ->width(40)
                    ->getStateUsing(fn($record) => $record->cliente?->foto
                        ? asset('storage/' . $record->cliente->foto)
                        : asset('images/default-locker.png')),

                TextColumn::make('nombre_cliente')
                    ->label('Cliente')
                    ->icon('heroicon-o-user')
                    ->searchable(query: function ($query, $search) {
                        return $query->whereHas('cliente', function ($q) use ($search) {
                            $q->where('nombre', 'like', "%$search%")
                                ->orWhere('apellido_paterno', 'like', "%$search%")
                                ->orWhere('apellido_materno', 'like', "%$search%");
                        });
                    })
                    ->sortable(),

                TextColumn::make('numero')
                    ->label('N° Casillero')
                    ->icon('heroicon-o-lock-closed')
                    ->searchable()
                    ->sortable(),

                BadgeColumn::make('estado')
                    ->label('Estado')
                    ->colors([
                        'success' => 'disponible',
                        'warning' => 'ocupado',
                        'danger' => 'mantenimiento',
                    ])
                    ->icons([
                        'heroicon-o-check-circle',
                        'heroicon-o-exclamation-triangle',
                        'heroicon-o-wrench-screwdriver',
                    ])
                    ->sortable(),

                TextColumn::make('costo_mensual')
                    ->label('Costo mensual')
                    ->icon('heroicon-o-banknotes')
                    ->money('BOB')
                    ->sortable(),

                TextColumn::make('total_reposiciones')
                    ->label('Reposiciones')
                    ->icon('heroicon-o-arrow-path') // reemplazo válido de "refresh"
                    ->sortable(),

                TextColumn::make('monto_reposiciones')
                    ->label('Bs Reposición')
                    ->icon('heroicon-o-currency-dollar')
                    ->money('BOB')
                    ->sortable(),

                TextColumn::make('fecha_entrega_llave')
                    ->label('Entrega')
                    ->icon('heroicon-o-calendar')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('fecha_final_llave')
                    ->label('Vence')
                    ->icon('heroicon-o-calendar-days')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('dias_restantes')
                    ->label('Días restantes')
                    ->icon('heroicon-o-clock')
                    ->getStateUsing(function ($record) {
                        if (!$record->fecha_final_llave)
                            return '—';

                        $dias = \Carbon\Carbon::now()->diffInDays(\Carbon\Carbon::parse($record->fecha_final_llave), false);
                        return $dias < 0
                            ? "Venció hace " . abs(intval($dias)) . " días"
                            : intval($dias) . ' días';
                    })
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('cliente_id')
                    ->label('Cliente')
                    ->relationship('cliente', 'nombre')
                    ->searchable(),

                Tables\Filters\SelectFilter::make('estado')
                    ->label('Estado del casillero')
                    ->options([
                        'disponible' => 'Disponible',
                        'ocupado' => 'Ocupado',
                        'mantenimiento' => 'Mantenimiento',
                    ]),

                Tables\Filters\Filter::make('rango_fecha_entrega')
                    ->label('Rango de entrega')
                    ->form([
                        DatePicker::make('desde')->label('Desde'),
                        DatePicker::make('hasta')->label('Hasta'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['desde'], fn($q) => $q->whereDate('fecha_entrega_llave', '>=', $data['desde']))
                            ->when($data['hasta'], fn($q) => $q->whereDate('fecha_entrega_llave', '<=', $data['hasta']));
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('desocupar')
                    ->label('Desocupar')
                    ->color('warning')
                    ->icon('heroicon-o-archive-box-x-mark')
                    ->requiresConfirmation()
                    ->visible(fn($record) => $record->cliente_id !== null)
                    ->action(function ($record) {
                        $record->update([
                            'cliente_id' => null,
                            'fecha_entrega_llave' => null,
                            'fecha_final_llave' => null,
                            'reposicion_llave' => 0,
                            'total_reposiciones' => 0,
                            'monto_reposiciones' => 0.00,
                            'estado' => 'disponible',
                        ]);
                    }),
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
            'index' => Pages\ListCasilleros::route('/'),
            'create' => Pages\CreateCasillero::route('/create'),
            'edit' => Pages\EditCasillero::route('/{record}/edit'),
        ];
    }
}

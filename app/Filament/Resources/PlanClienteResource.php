<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlanClienteResource\Pages;
use App\Filament\Resources\PlanClienteResource\RelationManagers;
use App\Models\PlanCliente;
use App\Models\PlanDisciplina;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Carbon\Carbon;
use App\Models\Clientes;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Placeholder;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\HtmlString;
use App\Filament\Resources\PlanClienteResource\RelationManagers\SesionesAdicionalesRelationManager;
use Filament\Notifications\Notification;

class PlanClienteResource extends Resource
{
    protected static ?string $model = PlanCliente::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Planes por Cliente';

    protected static ?string $navigationGroup = 'Administración';


    protected static ?string $modelLabel = 'Planes por Cliente';

    protected static ?string $pluralModelLabel = 'Planes por Cliente';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Información del Cliente')
                ->icon('heroicon-o-user')
                ->columns(2)
                ->schema([
                    Select::make('cliente_id')
                        ->label('Cliente')
                        ->options(Clientes::all()->pluck('nombre_completo', 'id'))
                        ->searchable()
                        ->required()
                        ->placeholder('Seleccione el cliente'),
                ]),

            Section::make('Detalles del Plan')
                ->icon('heroicon-o-clipboard-document-check')
                ->columns(2)
                ->schema([
                    Select::make('plan_id')
                        ->label('Plan')
                        ->options(\App\Models\Plan::pluck('nombre', 'id'))
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $get, callable $set) {
                            $planId = $state;
                            $disciplinaId = $get('disciplina_id');

                            if ($planId && $disciplinaId) {
                                $precio = PlanDisciplina::where('plan_id', $planId)
                                    ->where('disciplina_id', $disciplinaId)
                                    ->value('precio');

                                if ($precio !== null) {
                                    $set('precio_plan', $precio);
                                } else {
                                    $set('precio_plan', 0);
                                    Notification::make()
                                        ->title('⚠️ Precio no asignado')
                                        ->body('No se encontró un precio para este Plan y Disciplina.')
                                        ->warning()
                                        ->send();
                                }
                            }
                        }),

                    Select::make('disciplina_id')
                        ->label('Disciplina')
                        ->options(\App\Models\Disciplina::pluck('nombre', 'id'))
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $get, callable $set) {
                            $disciplinaId = $state;
                            $planId = $get('plan_id');

                            if ($planId && $disciplinaId) {
                                $precio = PlanDisciplina::where('plan_id', $planId)
                                    ->where('disciplina_id', $disciplinaId)
                                    ->value('precio');

                                if ($precio !== null) {
                                    $set('precio_plan', $precio);
                                } else {
                                    $set('precio_plan', 0);
                                    Notification::make()
                                        ->title('⚠️ Precio no asignado')
                                        ->body('No se encontró un precio para este Plan y Disciplina.')
                                        ->warning()
                                        ->send();
                                }
                            }
                        }),

                    Select::make('estado')
                        ->label('Estado del plan')
                        ->options([
                            'vigente' => 'Vigente',
                            'vencido' => 'Vencido',
                            'bloqueado' => 'Bloqueado por deuda',
                        ])
                        ->default('vigente')
                        ->required()
                        ->native(false)
                        ->columnSpanFull(),

                    DatePicker::make('fecha_inicio')
                        ->label('Fecha inicio')
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                            $plan = \App\Models\Plan::find($get('plan_id'));
                            if ($plan && $state) {
                                $fechaFinal = Carbon::parse($state)->addDays($plan->duracion_dias)->subDay();
                                $set('fecha_final', $fechaFinal->toDateString());
                            }
                        }),

                    DatePicker::make('fecha_final')
                        ->label('Fecha final')
                        ->disabled()
                        ->dehydrated(true),
                ]),

            Section::make('Costos del Plan')
                ->icon('heroicon-o-banknotes')
                ->columns(3)
                ->schema([
                    TextInput::make('precio_plan')
                        ->label('Precio del Plan')
                        ->readOnly()
                        ->numeric()
                        ->default(0),

                    TextInput::make('a_cuenta')
                        ->label('A cuenta')
                        ->numeric()
                        ->minValue(0)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                            $precioPlan = floatval($get('precio_plan'));
                            $aCuenta = floatval($state);

                            if ($precioPlan <= 0) {
                                Notification::make()
                                    ->title('⚠️ Precio inválido')
                                    ->body('El precio del plan debe ser mayor a 0.')
                                    ->warning()
                                    ->send();
                                return;
                            }

                            if ($aCuenta > $precioPlan) {
                                Notification::make()
                                    ->title('⚠️ Monto inválido')
                                    ->body('El monto "a cuenta" no puede ser mayor al precio del plan.')
                                    ->danger()
                                    ->send();

                                $set('a_cuenta', $precioPlan);
                                $set('saldo', 0);
                                $set('total', $precioPlan);
                                return;
                            }

                            $saldo = $precioPlan - $aCuenta;

                            $set('total', $precioPlan);
                            $set('saldo', max($saldo, 0));
                        }),

                    TextInput::make('total')
                        ->label('Total')
                        ->readOnly(),

                    TextInput::make('saldo')
                        ->label('Saldo')
                        ->readOnly(),
                ]),

            Section::make('Pago')
                ->icon('heroicon-o-currency-dollar')
                ->columns(2)
                ->schema([
                    Select::make('metodo_pago')
                        ->label('Método de pago')
                        ->options([
                            'efectivo' => 'Efectivo',
                            'qr' => 'QR',
                        ])
                        ->required(),

                    Select::make('comprobante')
                        ->label('Comprobante')
                        ->options([
                            'simple' => 'Simple',
                            'factura' => 'Factura',
                        ])
                        ->default('simple')
                        ->required(),
                ]),
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
                    ->width(40),

                TextColumn::make('cliente.nombre_completo')
                    ->label('Cliente')
                    ->icon('heroicon-o-user')
                    ->searchable(query: function ($query, $search) {
                        return $query->whereHas('cliente', function ($q) use ($search) {
                            $q->where('nombre', 'like', "%{$search}%")
                                ->orWhere('apellido_paterno', 'like', "%{$search}%")
                                ->orWhere('apellido_materno', 'like', "%{$search}%");
                        });
                    })
                    ->sortable(),

                TextColumn::make('plan.nombre')
                    ->label('Plan')
                    ->icon('heroicon-o-rectangle-stack'),

                TextColumn::make('disciplina.nombre')
                    ->label('Disciplina')
                    ->icon('heroicon-o-bolt'),

                TextColumn::make('fecha_inicio')
                    ->label('Inicio')
                    ->icon('heroicon-o-calendar-days')
                    ->date()
                    ->sortable(),

                TextColumn::make('fecha_final')
                    ->label('Final')
                    ->icon('heroicon-o-calendar')
                    ->date()
                    ->sortable(),

                TextColumn::make('estado')
                    ->label('Estado')
                    ->icon('heroicon-o-adjustments-vertical')
                    ->badge()
                    ->color(fn(?string $state): string => match ($state) {
                        'vigente' => 'success',      // Verde
                        'bloqueado' => 'danger',     // Rojo
                        'vencido' => 'warning',      // Naranja
                        default => 'gray',
                    })
                    ->sortable()
                    ->formatStateUsing(fn(?string $state) => ucfirst($state)),

                TextColumn::make('total')
                    ->label('Total')
                    ->icon('heroicon-o-calculator')
                    ->money('BOB')
                    ->sortable()
                    ->alignRight(),

                TextColumn::make('a_cuenta')
                    ->label('A cuenta')
                    ->icon('heroicon-o-currency-dollar')
                    ->money('BOB')
                    ->alignRight()
                    ->color(fn($state) => $state > 0 ? 'success' : null),

                TextColumn::make('saldo')
                    ->label('Saldo')
                    ->icon('heroicon-o-exclamation-circle')
                    ->money('BOB')
                    ->alignRight()
                    ->color(fn($state) => $state > 0 ? 'danger' : null),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('cliente_id')
                    ->label('Cliente')
                    ->relationship('cliente', 'nombre'),

                Tables\Filters\SelectFilter::make('plan_id')
                    ->label('Plan')
                    ->relationship('plan', 'nombre'),

                Tables\Filters\SelectFilter::make('disciplina_id')
                    ->label('Disciplina')
                    ->relationship('disciplina', 'nombre'),

                Tables\Filters\SelectFilter::make('estado')
                    ->label('Estado del plan')
                    ->options([
                        'vigente' => 'Vigente',
                        'vencido' => 'Vencido',
                        'bloqueado' => 'Bloqueado por deuda',
                    ]),

                Tables\Filters\Filter::make('rango_fecha')
                    ->label('Rango de fechas')
                    ->form([
                        DatePicker::make('desde')->label('Desde'),
                        DatePicker::make('hasta')->label('Hasta'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['desde'], fn($q) => $q->whereDate('fecha_inicio', '>=', $data['desde']))
                            ->when($data['hasta'], fn($q) => $q->whereDate('fecha_final', '<=', $data['hasta']));
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->tooltip('Editar este plan'),
                Tables\Actions\DeleteAction::make()->tooltip('Eliminar este plan'),
            ]);
    }


    public static function getRelations(): array
    {
        return [
            SesionesAdicionalesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlanClientes::route('/'),
            'create' => Pages\CreatePlanCliente::route('/create'),
            'edit' => Pages\EditPlanCliente::route('/{record}/edit'),
        ];
    }
}

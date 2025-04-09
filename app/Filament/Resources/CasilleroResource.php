<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CasilleroResource\Pages;
use App\Filament\Resources\CasilleroResource\RelationManagers;
use App\Models\Casillero;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\PlanCliente;
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
use Filament\Forms\Get;
use Filament\Forms\Set;



class CasilleroResource extends Resource
{
    protected static ?string $model = Casillero::class;

    protected static ?string $navigationIcon = 'heroicon-o-lock-closed';

    protected static ?string $navigationGroup = 'Administración';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('numero')
                ->required()
                ->unique(ignoreRecord: true) // <- ESTO evita que dispare la validación al editar el mismo casillero
                ->placeholder('Ej: C-001'),
            TextInput::make('ubicacion')->nullable(),
            Select::make('estado')->options([
                'disponible' => 'Disponible',
                'ocupado' => 'Ocupado',
                'mantenimiento' => 'Mantenimiento',
            ])->required(),

            Select::make('cliente_id')
                ->relationship('cliente', 'nombre')
                ->searchable()
                ->label('Cliente asignado')
                ->nullable(),

            DatePicker::make('fecha_entrega_llave')
                ->label('Fecha de inicio de uso')
                ->required()
                ->reactive()
                ->afterStateUpdated(function ($state, Set $set) {
                    if ($state) {
                        $fechaFinal = Carbon::parse($state)->addDays(29);
                        $set('fecha_final_llave', $fechaFinal->toDateString());
                    }
                }),

            DatePicker::make('fecha_final_llave')
                ->label('Fecha final de uso')
                ->disabled()
                ->dehydrated()
                ->placeholder('Se calculará automáticamente'),

            TextInput::make('costo_mensual')
                ->label('Costo mensual')
                ->numeric()
                ->default(40)
                ->disabled()
                ->dehydrated(),

            TextInput::make('total_reposiciones')
                ->label('Reposiciones realizadas')
                ->numeric()
                ->default(0)
                ->minValue(0)
                ->live(onBlur: true)
                ->afterStateUpdated(function ($state, Set $set) {
                    $costoReposicion = 20; // ← puedes parametrizarlo
                    $set('monto_reposiciones', $state * $costoReposicion);
                }),

            TextInput::make('monto_reposiciones')
                ->label('Monto total por reposiciones')
                ->numeric()
                ->default(0)
                ->disabled()
                ->dehydrated(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([

            ImageColumn::make('cliente.foto_url')
                ->label('Foto')
                ->circular()
                ->height(40)
                ->width(40)
                ->getStateUsing(function ($record) {
                    return $record->cliente?->foto
                        ? asset('storage/' . $record->cliente->foto)
                        : asset('images/default-locker.png');
                }),

            TextColumn::make('nombre_cliente')
                ->label('Cliente')
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
                ->searchable()
                ->sortable(),

            BadgeColumn::make('estado')
                ->colors([
                    'success' => 'disponible',
                    'warning' => 'ocupado',
                    'danger' => 'mantenimiento',
                ])
                ->sortable(),

            TextColumn::make('fecha_entrega_llave')
                ->date()
                ->label('Entrega')
                ->sortable(),

            TextColumn::make('fecha_final_llave')
                ->label('Vence')
                ->date('M d, Y')
                ->sortable(),

            TextColumn::make('dias_restantes')
                ->label('Días restantes')
                ->formatStateUsing(function ($state, $record) {
                    if (!$record->fecha_final_llave) {
                        return '—';
                    }

                    $dias = Carbon::parse(now())->diffInDays(Carbon::parse($record->fecha_final_llave), false);

                    if ($dias < 0) {
                        return "Venció hace " . abs($dias) . " días";
                    }

                    return $dias . ' días';
                }),

            TextColumn::make('costo_mensual')
                ->label('Costo')
                ->money('BOB')
                ->sortable(),

            TextColumn::make('total_reposiciones')
                ->label('Reposiciones')
                ->sortable(),

            TextColumn::make('monto_reposiciones')
                ->label('Bs Reposición')
                ->money('BOB')
                ->sortable(),

        ])->actions([
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

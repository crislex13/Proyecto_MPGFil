<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientesResource\Pages;
use App\Models\Clientes;
use App\Models\PlanDisciplina;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;


class ClientesResource extends Resource
{
    protected static ?string $model = Clientes::class;
    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static ?string $navigationLabel = 'Clientes';
    protected static ?string $navigationGroup = 'Administración';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Datos personales')->schema([
                TextInput::make('nombre')->required(),
                TextInput::make('apellido_paterno')->required(),
                TextInput::make('apellido_materno'),
                DatePicker::make('fecha_de_nacimiento')->required(),
                TextInput::make('ci')->label('C.I.')->required(),
                TextInput::make('telefono'),
                TextInput::make('correo')->email(),
                Select::make('sexo')->options([
                    'masculino' => 'Masculino',
                    'femenino' => 'Femenino',
                ]),
                FileUpload::make('foto')
                    ->label('Foto del cliente')
                    ->image()
                    ->directory('fotos/clientes')
                    ->disk('public')
                    ->getUploadedFileNameForStorageUsing(fn($file) => time() . '-' . str_replace(' ', '_', $file->getClientOriginalName()))
                    ->default(fn($record) => $record?->foto ? asset('storage/' . $record->foto) : null)
                    ->required()
                    ->previewable()
                    ->dehydrated(),


                TextInput::make('biometrico_id')->label('ID del biométrico')->required(),
            ])->columns(2),

            Section::make('Salud y contacto de emergencia')->schema([
                Textarea::make('antecedentes_medicos'),
                TextInput::make('contacto_emergencia_nombre')->label('Nombre'),
                TextInput::make('contacto_emergencia_parentesco'),
                TextInput::make('contacto_emergencia_celular'),
            ])->columns(2),

            Section::make('Plan')->schema([
                Select::make('plan_id')
                    ->label('Plan')
                    ->relationship('plan', 'nombre')
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(fn(Set $set) => $set('precio_plan', null)),

                Select::make('disciplina_id')
                    ->label('Disciplina')
                    ->relationship('disciplina', 'nombre')
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function (Get $get, Set $set) {
                        $planId = $get('plan_id');
                        $disciplinaId = $get('disciplina_id');

                        if ($planId && $disciplinaId) {
                            $registro = PlanDisciplina::where('plan_id', $planId)
                                ->where('disciplina_id', $disciplinaId)
                                ->first();

                            $set('precio_plan', $registro?->precio ?? 0);

                            // También actualizamos saldo y total
                            $aCuenta = $get('a_cuenta') ?? 0;
                            $set('total', $registro?->precio ?? 0);
                            $set('saldo', ($registro?->precio ?? 0) - $aCuenta);
                        }
                    }),

                    DatePicker::make('fecha_inicio')
                    ->required()
                    ->afterOrEqual(Carbon::today()->toDateString()), // Validación exacta con la fecha actual

                    DatePicker::make('fecha_final')
                    ->required()
                    ->after(fn (Get $get) => $get('fecha_inicio') ?? today()->format('Y-m-d')),
                
                TextInput::make('precio_plan')
                    ->label('Precio del Plan (Bs.)')
                    ->numeric()
                    ->disabled()
                    ->dehydrated(),

                TextInput::make('a_cuenta')
                    ->numeric()
                    ->default(0)
                    ->reactive()
                    ->afterStateUpdated(function (Get $get, Set $set, $state) {
                        $precio = $get('precio_plan') ?? 0;
                        $set('saldo', $precio - $state);
                        $set('total', $precio);
                    }),

                TextInput::make('saldo')
                    ->numeric()
                    ->default(0)
                    ->disabled()
                    ->dehydrated(),

                TextInput::make('total')
                    ->numeric()
                    ->default(0)
                    ->disabled()
                    ->dehydrated(),

                Select::make('metodo_pago')->options([
                    'efectivo' => 'Efectivo',
                    'qr' => 'QR',
                ])->required(),

                Select::make('comprobante')->options([
                    'simple' => 'Simple',
                    'factura' => 'Factura',
                ])->default('simple')->required(),
            ])->columns(2),

            Section::make('Estado del cliente')->schema([
                Select::make('estado')->options([
                    'activo' => 'Activo',
                    'inactivo' => 'Inactivo',
                ])->default('activo')->required(),
            ]),

            Section::make('Resumen')->schema([
                Placeholder::make('resumen_total')
                    ->label('Total a pagar')
                    ->content(fn(Get $get) => 'Bs. ' . number_format($get('total') ?? 0, 2)),
                Placeholder::make('resumen_saldo')
                    ->label('Saldo restante')
                    ->content(fn(Get $get) => 'Bs. ' . number_format($get('saldo') ?? 0, 2)),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            ImageColumn::make('foto_url')
                ->label('Foto')
                ->circular()
                ->height(40)
                ->width(40),


            TextColumn::make('nombre')
                ->label('Nombre')
                ->formatStateUsing(function ($state, $record) {
                    return $record->saldo > 0
                        ? "⚠️ $state"
                        : $state;
                })
                ->searchable(),
            TextColumn::make('apellido_paterno')->searchable(),
            TextColumn::make('apellido_materno')->searchable(),
            TextColumn::make('correo')->searchable(),
            TextColumn::make('telefono'),
            TextColumn::make('plan.nombre')->label('Plan'),
            TextColumn::make('disciplina.nombre')->label('Disciplina'),
            TextColumn::make('precio_plan')->money('BOB'),
            TextColumn::make('a_cuenta')->money('BOB'),
            TextColumn::make('saldo')->money('BOB'),
            BadgeColumn::make('deuda')
                ->label('Deuda')
                ->colors([
                    'danger' => fn($record) => $record->saldo > 0,
                    'success' => fn($record) => $record->saldo == 0,
                ])
                ->formatStateUsing(fn($record) => $record->saldo > 0 ? 'Pendiente' : 'Sin deuda'),

            TextColumn::make('total')->money('BOB'),
            TextColumn::make('fecha_inicio')->label('Inicio')->sortable(),
            TextColumn::make('fecha_final')->label('Final')->sortable(),
            BadgeColumn::make('estado')->label('Estado de Cliente')->colors([
                'success' => 'activo',
                'danger' => 'inactivo',
            ])->sortable(),
        ])->filters([
                    \Filament\Tables\Filters\Filter::make('Con deuda')
                        ->query(fn($query) => $query->where('saldo', '>', 0)),
                ])->actions([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\Action::make('ver')
                        ->label('Ver')
                        ->icon('heroicon-o-eye')
                        ->modalHeading('Detalles del Cliente')
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Cerrar')
                        ->color('info')
                        ->form(fn(Clientes $record) => [
                            Placeholder::make('Foto')
                                ->label('Foto del cliente')
                                ->content(fn($record) => new HtmlString(
                                    '<img src="' . asset('storage/' . $record->foto) . '" alt="Foto del cliente" style="width: 120px; height: 120px; object-fit: cover; border-radius: 100px;" />'
                                ))
                                ->columnSpanFull()
                                ->disableLabel()
                                ->helperText('Foto del cliente')
                                ->extraAttributes(['style' => 'text-align: center'])
                                ->reactive(),

                            // Puedes agregar aquí más campos si deseas
                            TextInput::make('nombre')->default($record->nombre)->disabled(),
                            TextInput::make('apellido_paterno')->default($record->apellido_paterno)->disabled(),
                            // ...
                        ])
                ]);

    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClientes::route('/'),
            'create' => Pages\CreateClientes::route('/create'),
            'edit' => Pages\EditClientes::route('/{record}/edit'),
        ];
    }
}

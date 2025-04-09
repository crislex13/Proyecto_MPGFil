<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlanClienteResource\Pages;
use App\Filament\Resources\PlanClienteResource\RelationManagers;
use App\Models\PlanCliente;
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

class PlanClienteResource extends Resource
{
    protected static ?string $model = PlanCliente::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Administración';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('cliente_id')
                ->relationship('cliente', 'nombre')
                ->searchable()
                ->required(),

            Select::make('plan_id')
                ->relationship('plan', 'nombre')
                ->required(),

            Select::make('disciplina_id')
                ->relationship('disciplina', 'nombre')
                ->required(),

            DatePicker::make('fecha_inicio')
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
                ->disabled(),

            TextInput::make('precio_plan')->numeric()->default(0),
            TextInput::make('a_cuenta')->numeric()->default(0),
            TextInput::make('casillero_monto')->numeric()->default(0),
            TextInput::make('total')->numeric()->default(0),
            TextInput::make('saldo')->numeric()->default(0),

            Select::make('metodo_pago')
                ->options(['efectivo' => 'Efectivo', 'qr' => 'QR'])
                ->required(),

            Select::make('comprobante')
                ->options(['simple' => 'Simple', 'factura' => 'Factura'])
                ->default('simple')
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            ImageColumn::make('cliente.foto_url')
                ->label('Foto')
                ->circular()
                ->height(40)
                ->width(40),

            TextColumn::make('cliente.nombre')
                ->label('Cliente')
                ->searchable()
                ->sortable(),

            TextColumn::make('plan.nombre'),

            TextColumn::make('disciplina.nombre'),

            TextColumn::make('fecha_inicio')
            ->date(),

            TextColumn::make('fecha_final')
            ->date(),

            TextColumn::make('total')
            ->money('BOB'),

            TextColumn::make('saldo')
            ->money('BOB'),

            TextColumn::make('metodo_pago'),

        ])->filters([])->actions([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListPlanClientes::route('/'),
            'create' => Pages\CreatePlanCliente::route('/create'),
            'edit' => Pages\EditPlanCliente::route('/{record}/edit'),
        ];
    }
}

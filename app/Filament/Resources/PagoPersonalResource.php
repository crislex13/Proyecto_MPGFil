<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PagoPersonalResource\Pages;
use App\Models\PagoPersonal;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Placeholder;
use function Filament\Support\view;
use Filament\Forms\Components\ViewField;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Database\Eloquent\Builder;

class PagoPersonalResource extends Resource
{
    protected static ?string $model = PagoPersonal::class;

    protected static ?string $modelLabel = 'pago de personal';

    protected static ?string $pluralModelLabel = 'Pagos de personal';

    protected static ?string $navigationLabel = 'Pagos de personal';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Operaciones';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Registro de Pago al Personal')
                ->description('Complete los datos del pago realizado al trabajador')
                ->icon('heroicon-o-currency-dollar')
                ->schema([
                    Select::make('personal_id')
                        ->label('Instructor')
                        ->relationship(
                            name: 'personal',
                            titleAttribute: 'nombre_completo',
                            modifyQueryUsing: fn(Builder $query, $search) => $query->where(function ($q) use ($search) {
                                $q->where('nombre', 'like', "%$search%")
                                    ->orWhere('apellido_paterno', 'like', "%$search%")
                                    ->orWhere('apellido_materno', 'like', "%$search%");
                            })
                        )
                        ->getOptionLabelFromRecordUsing(fn($record) => $record->nombre_completo)
                        ->searchable()
                        ->preload() // <-- Esto hace que cargue como en clientes
                        ->required()
                        ->placeholder('Seleccione al instructor'),

                    ViewField::make('personal_foto')
                        ->view('components.personal-foto') // ✅ Esto SÍ funciona, sin envolverlo
                        ->visible(fn($get) => $get('personal_id'))
                        ->columnSpan(2),

                    TextInput::make('monto')
                        ->label('Monto (Bs.)')
                        ->placeholder('Ejemplo: 50.00')
                        ->required()
                        ->numeric()
                        ->minValue(0),

                    Select::make('turno_id')
                        ->label('Turno')
                        ->placeholder('Seleccione el turno trabajado')
                        ->options(
                            fn(Get $get) =>
                            $get('personal_id')
                            ? \App\Models\Turno::where('personal_id', $get('personal_id'))->pluck('nombre', 'id')
                            : []
                        )
                        ->searchable()
                        ->required()
                        ->reactive() // escucha cambios
                        //->disabled(fn(Get $get) => !$get('personal_id')) 
                        ->hint('Seleccione el turno que trabajó el instructor')
                        ->hintColor('gray'),

                    Select::make('sala_id')
                        ->label('Sala')
                        ->relationship(
                            name: 'sala',
                            titleAttribute: 'nombre',
                            modifyQueryUsing: fn($query) => $query->where('estado', 'activo')
                        )
                        ->placeholder('Seleccione la sala'),

                    Toggle::make('pagado')
                        ->label('¿Pago realizado?')
                        ->default(true)
                        ->dehydrated(true)
                        ->reactive()
                        ->hint(
                            fn(Get $get) =>
                            $get('pagado')
                            ? '✅ Este pago ha sido marcado como realizado.'
                            : '⚠️ Marque si ya se realizó el pago.'
                        ),

                    DatePicker::make('fecha')
                        ->label('Fecha del Pago')
                        ->required()
                        ->placeholder('Seleccione la fecha del pago'),

                    Textarea::make('descripcion')
                        ->label('Observaciones')
                        ->placeholder('Observaciones del pago, turno, rendimiento, etc.')
                        ->rows(3)
                        ->columnSpan(2),
                ])
                ->columns(2)
                ->collapsible(),
        ]);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                ImageColumn::make('personal.foto')
                    ->label('Foto')
                    ->circular()
                    ->size(40)
                    ->url(fn($record) => \Storage::url($record->personal->foto ?? ''))
                    ->defaultImageUrl('/default-user.png'),

                TextColumn::make('personal.nombre_completo')
                    ->label('Nombre del Personal')
                    ->searchable(['personals.nombre', 'personals.apellido_paterno', 'personals.apellido_materno']) // <- ✅ tabla real
                    ->icon('heroicon-o-user-circle')
                    ->sortable(),

                TextColumn::make('fecha')
                    ->label('Fecha del Pago')
                    ->icon('heroicon-o-calendar')
                    ->date()
                    ->sortable(),

                TextColumn::make('monto')
                    ->label('Monto por sesión')
                    ->money('BOB')
                    ->icon('heroicon-o-currency-dollar')
                    ->sortable(),

                TextColumn::make('turno.nombre')
                    ->label('Turno')
                    ->icon('heroicon-o-clock')
                    ->sortable(),

                TextColumn::make('sala.nombre')
                    ->label('Sala')
                    ->icon('heroicon-o-home-modern')
                    ->sortable(),

                BadgeColumn::make('pagado')
                    ->label('Estado de Pago')
                    ->colors([
                        'success' => fn($state) => (bool) $state === true,
                        'danger' => fn($state) => (bool) $state === false,
                    ])
                    ->formatStateUsing(fn($state) => $state ? 'Pagado' : 'Pendiente')
                    ->icon(fn($state) => $state ? 'heroicon-o-check-circle' : 'heroicon-o-exclamation-circle')
                    ->sortable(),

                TextColumn::make('descripcion')
                    ->label('Descripción')
                    ->limit(30)
                    ->wrap()
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('pagado')
                    ->label('Estado de Pago')
                    ->options([
                        true => 'Pagado',
                        false => 'Pendiente',
                    ])
                    ->placeholder('Todos'),

                Tables\Filters\Filter::make('fecha')
                    ->label('Fecha del Pago')
                    ->form([
                        DatePicker::make('fecha'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['fecha'], fn($q, $date) => $q->whereDate('fecha', $date));
                    }),

                Tables\Filters\SelectFilter::make('turno_id')
                    ->label('Turno')
                    ->relationship(
                        name: 'turno',
                        titleAttribute: 'nombre',
                        modifyQueryUsing: fn($query) => $query->where('estado', 'activo')
                    )
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('sala_id')
                    ->label('Sala')
                    ->relationship(
                        name: 'sala',
                        titleAttribute: 'nombre',
                        modifyQueryUsing: fn($query) => $query->where('estado', 'activo')
                    )
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
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
            'index' => Pages\ListPagoPersonals::route('/'),
            'create' => Pages\CreatePagoPersonal::route('/create'),
            'edit' => Pages\EditPagoPersonal::route('/{record}/edit'),
        ];
    }
}

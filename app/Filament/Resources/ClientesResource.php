<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientesResource\Pages;
use App\Models\Clientes;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class ClientesResource extends Resource
{
    protected static ?string $model = Clientes::class;
    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static ?string $navigationLabel = 'Clientes';
    protected static ?string $navigationGroup = 'Administración';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Datos personales')
                ->description('Completa esta sección con los datos del cliente.')
                ->icon('heroicon-o-identification')
                ->collapsible()
                ->schema([
                    TextInput::make('nombre')->required()->placeholder('Ingrese el nombre'),
                    TextInput::make('apellido_paterno')->required()->placeholder('Ingrese el apellido paterno'),
                    TextInput::make('apellido_materno')->placeholder('Ingrese el apellido materno (opcional)'),
                    DatePicker::make('fecha_de_nacimiento')->required()->placeholder('Seleccione la fecha de nacimiento'),
                    TextInput::make('ci')
                        ->label('Carnet de identidad')
                        ->required()
                        ->placeholder('Ingrese el C.I. (Carnet de Identidad)'),
                    TextInput::make('telefono')
                        ->label('Teléfono o Celular')
                        ->tel()
                        ->maxLength(15)
                        ->placeholder('Ingrese su número de teléfono o celular'),
                    TextInput::make('correo')->email()->placeholder('Ingrese su correo electrónico (opcional)'),
                    Select::make('sexo')->options([
                        'masculino' => 'Masculino',
                        'femenino' => 'Femenino',
                    ])->placeholder('Seleccione el sexo'),
                    FileUpload::make('foto')
                        ->label('Foto del cliente')
                        ->image()
                        ->directory('fotos/clientes')
                        ->disk('public')
                        ->getUploadedFileNameForStorageUsing(fn($file) => time() . '-' . str_replace(' ', '_', $file->getClientOriginalName()))
                        ->previewable()
                        ->dehydrated(),
                    TextInput::make('biometrico_id')
                        ->label('ID del biométrico')
                        ->disabled()
                        ->dehydrated()
                        ->placeholder('Se llenará automáticamente con el C.I.'),
                ])->columns(2),

            Section::make('Salud y contacto de emergencia')
                ->description('Completa esta sección con los datos de emergencia del cliente.')
                ->icon('heroicon-o-identification')
                ->collapsible()
                ->schema([
                    Textarea::make('antecedentes_medicos')->placeholder('Describa antecedentes médicos relevantes (opcional)'),
                    TextInput::make('contacto_emergencia_nombre')->label('Nombre')->placeholder('Ingrese el nombre del contacto de emergencia'),
                    TextInput::make('contacto_emergencia_parentesco')->placeholder('Ingrese el parentesco con el contacto de emergencia'),
                    TextInput::make('contacto_emergencia_celular')->placeholder('Ingrese el número de celular del contacto de emergencia'),
                ])->columns(2),

            Section::make('Estado del cliente')
                ->description('Selecciona el estado del cliente.')
                ->icon('heroicon-o-identification')
                ->collapsible()
                ->schema([
                    Select::make('estado')->options([
                        'activo' => 'Activo',
                        'inactivo' => 'Inactivo',
                    ])->default('activo')->required()->placeholder('Seleccione el estado del cliente'),
                ]),
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
                ->icon('heroicon-o-user')
                ->searchable()
                ->sortable(),

            TextColumn::make('apellido_paterno')->searchable(),
            TextColumn::make('apellido_materno')->searchable(),
            TextColumn::make('correo')->searchable(),
            TextColumn::make('telefono'),

            BadgeColumn::make('estado')
                ->label('Estado del Cliente')
                ->colors([
                    'success' => 'activo',
                    'danger' => 'inactivo',
                ])
                ->icon(fn($state) => $state === 'activo' ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
                ->sortable(),
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

                            Section::make('📸 Foto del Cliente')->schema([
                                Placeholder::make('foto')
                                    ->label('Foto')
                                    ->content(fn() => new HtmlString(
                                        '<div style="display: flex; justify-content: center;">
                                    <img src="' . asset('storage/' . $record->foto) . '" alt="Foto del cliente" style="width: 140px; height: 140px; object-fit: cover; border-radius: 100px; border: 2px solid #ccc;" />
                                </div>'
                                    ))
                                    ->columnSpanFull(),
                            ]),

                            Section::make('🧍 Datos Personales')->schema([
                                TextInput::make('nombre')->default($record->nombre)->disabled(),
                                TextInput::make('apellido_paterno')->default($record->apellido_paterno)->disabled(),
                                TextInput::make('apellido_materno')->default($record->apellido_materno)->disabled(),
                                TextInput::make('ci')->default($record->ci)->disabled(),
                                DatePicker::make('fecha_de_nacimiento')->default($record->fecha_de_nacimiento)->disabled(),
                                TextInput::make('telefono')->default($record->telefono)->disabled(),
                                TextInput::make('correo')->default($record->correo)->disabled(),
                                Placeholder::make('sexo')->label('Sexo')->content($record->sexo),
                                Placeholder::make('biometrico_id')->label('ID Biométrico')->content($record->biometrico_id),
                            ])->columns(2),

                            Section::make('🚨 Emergencias y Salud')->schema([
                                Placeholder::make('antecedentes_medicos')->label('Antecedentes Médicos')->content($record->antecedentes_medicos ?? 'Ninguno'),
                                Placeholder::make('contacto_emergencia_nombre')->label('Nombre de Emergencia')->content($record->contacto_emergencia_nombre ?? '-'),
                                Placeholder::make('contacto_emergencia_parentesco')->label('Parentesco')->content($record->contacto_emergencia_parentesco ?? '-'),
                                Placeholder::make('contacto_emergencia_celular')->label('Celular')->content($record->contacto_emergencia_celular ?? '-'),
                            ])->columns(2),

                            Section::make('💼 Estado del Cliente')->schema([
                                Placeholder::make('estado')
                                    ->label('Estado')
                                    ->content(fn() => new HtmlString(
                                        $record->estado === 'activo'
                                        ? '<span style="color: green; font-weight: bold;">🟢 Activo</span>'
                                        : '<span style="color: red; font-weight: bold;">🔴 Inactivo</span>'
                                    )),
                                Placeholder::make('bloqueado_por_deuda')
                                    ->label('Bloqueado por Deuda')
                                    ->content(fn() => new HtmlString(
                                        $record->bloqueado_por_deuda
                                        ? '<span style="color: red;">Sí</span>'
                                        : '<span style="color: green;">No</span>'
                                    )),
                            ])->columns(2),
                        ]),
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
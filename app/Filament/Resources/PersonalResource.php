<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PersonalResource\Pages;
use App\Models\Personal;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Get;
use Filament\Forms\Set;

class PersonalResource extends Resource
{
    protected static ?string $model = Personal::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Personal';
    protected static ?string $navigationGroup = 'Administración';
    protected static ?string $modelLabel = 'Personal';
    protected static ?string $pluralModelLabel = 'Personal';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('🧍 Datos Personales')
                ->description('Información básica del personal')
                ->schema([
                    TextInput::make('nombre')
                        ->required()
                        ->label('Nombre')
                        ->placeholder('Ej: Carlos'),
                    TextInput::make('apellido_paterno')
                        ->required()
                        ->label('Apellido Paterno')
                        ->placeholder('Ej: Pérez'),
                    TextInput::make('apellido_materno')
                        ->required()
                        ->label('Apellido Materno')
                        ->placeholder('Ej: Gutiérrez'),
                    DatePicker::make('fecha_de_nacimiento')
                        ->required()
                        ->label('Fecha de nacimiento'),
                    TextInput::make('ci')
                        ->label('C.I.')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn(Get $get, Set $set, $state) => $set('biometrico_id', $state)),
                    TextInput::make('telefono')
                        ->label('Teléfono')
                        ->tel()
                        ->placeholder('Ej: 76543210'),
                    TextInput::make('direccion')
                        ->label('Dirección')
                        ->placeholder('Ej: Av. América Oeste'),
                    TextInput::make('correo')
                        ->email()
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->label('Correo'),
                    FileUpload::make('foto')
                        ->label('Foto del personal')
                        ->image()
                        ->directory('fotos/personal')
                        ->disk('public')
                        ->getUploadedFileNameForStorageUsing(fn($file) => time() . '-' . str_replace(' ', '_', $file->getClientOriginalName()))
                        ->previewable(),
                ])->columns(2),

            Section::make('🏢 Datos Laborales')
                ->description('Información laboral y administrativa')
                ->schema([
                    TextInput::make('cargo')
                        ->required()->label('Cargo')
                        ->placeholder('Ej: Instructor, Recepcionista'),
                    TextInput::make('biometrico_id')
                        ->label('ID Biométrico')
                        ->disabled()
                        ->required(),
                    DatePicker::make('fecha_contratacion')
                        ->required()
                        ->label('Fecha de contratación'),
                    Select::make('estado')
                        ->label('Estado')
                        ->options([
                            'activo' => 'Activo',
                            'inactivo' => 'Inactivo',
                            'baja' => 'De baja',
                        ])
                        ->default('activo')
                        ->required(),
                    Textarea::make('observaciones')
                        ->label('Observaciones')
                        ->placeholder('Notas adicionales...')
                        ->rows(3),
                ])->columns(2),
        ]);
    }


    public static function table(Table $table): Table
    {
        return $table->columns([
            ImageColumn::make('foto')
                ->label('Foto')
                ->circular()
                ->height(50),

            TextColumn::make('nombre')
                ->searchable()
                ->sortable(),

            TextColumn::make('apellido_paterno')
                ->searchable()
                ->sortable(),

            TextColumn::make('apellido_materno')
                ->searchable()
                ->sortable(),

            TextColumn::make('cargo')
                ->sortable(),

            TextColumn::make('ci')
                ->label('C.I.')
                ->searchable(),

            TextColumn::make('correo')
                ->searchable(),

            TextColumn::make('telefono'),

            BadgeColumn::make('estado')
                ->label('Estado')
                ->colors([
                    'success' => 'activo',
                    'warning' => 'inactivo',
                    'danger' => 'baja',
                ])
                ->sortable(),
        ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('ver')
                    ->label('Ver')
                    ->icon('heroicon-o-eye')
                    ->modalHeading('Detalles del Personal')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Cerrar')
                    ->color('info')
                    ->form(fn(Personal $record) => [
                        Section::make('📸 Foto de Perfil')->schema([
                            Placeholder::make('foto')
                                ->label('Foto del Personal')
                                ->content(fn() => new HtmlString(
                                    '<div style="display: flex; justify-content: center;">
                        <img src="' . asset('storage/' . $record->foto) . '" alt="Foto" style="width: 140px; height: 140px; object-fit: cover; border-radius: 100px; border: 2px solid #ccc;" />
                    </div>'
                                ))
                                ->columnSpanFull(),
                        ]),

                        Section::make('👤 Información Personal')->schema([
                            TextInput::make('nombre')->label('Nombre')->default($record->nombre)->disabled()->placeholder('Nombre completo'),
                            TextInput::make('apellido_paterno')->label('Apellido Paterno')->default($record->apellido_paterno)->disabled()->placeholder('Primer apellido'),
                            TextInput::make('apellido_materno')->label('Apellido Materno')->default($record->apellido_materno)->disabled()->placeholder('Segundo apellido'),
                            TextInput::make('ci')->label('Carnet de Identidad')->default($record->ci)->disabled()->placeholder('Número de C.I.'),
                            DatePicker::make('fecha_de_nacimiento')->label('Fecha de Nacimiento')->default($record->fecha_de_nacimiento)->disabled()->placeholder('Fecha de nacimiento'),
                            TextInput::make('telefono')->label('Teléfono')->default($record->telefono)->disabled()->placeholder('Número celular'),
                            TextInput::make('correo')->label('Correo')->default($record->correo)->disabled()->placeholder('Correo electrónico'),
                            TextInput::make('direccion')->label('Dirección')->default($record->direccion)->disabled()->placeholder('Dirección de domicilio'),
                        ])->columns(2),

                        Section::make('💼 Información Laboral')->schema([
                            TextInput::make('cargo')->label('Cargo')->default($record->cargo)->disabled()->placeholder('Puesto actual'),
                            TextInput::make('biometrico_id')->label('ID Biométrico')->default($record->biometrico_id)->disabled()->placeholder('ID para marcación'),
                            DatePicker::make('fecha_contratacion')->label('Fecha de Contratación')->default($record->fecha_contratacion)->disabled()->placeholder('Fecha de ingreso'),
                            TextInput::make('salario')->label('Salario (Bs.)')->default($record->salario)->disabled()->placeholder('Salario mensual'),
                            Placeholder::make('estado')
                                ->label('Estado Laboral')
                                ->content(fn() => new HtmlString(
                                    match ($record->estado) {
                                        'activo' => '<span style="color: green; font-weight: bold;">🟢 Activo</span>',
                                        'inactivo' => '<span style="color: orange; font-weight: bold;">🟡 Inactivo</span>',
                                        'baja' => '<span style="color: red; font-weight: bold;">🔴 De Baja</span>',
                                        default => $record->estado,
                                    }
                                )),
                        ])->columns(2),

                        Section::make('📝 Observaciones')->schema([
                            Textarea::make('observaciones')
                                ->label('Notas')
                                ->default($record->observaciones)
                                ->disabled()
                                ->placeholder('Información adicional del personal')
                        ]),
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
            'index' => Pages\ListPersonals::route('/'),
            'create' => Pages\CreatePersonal::route('/create'),
            'edit' => Pages\EditPersonal::route('/{record}/edit'),
        ];
    }
}
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
use Spatie\Permission\Models\Role;
use Filament\Tables\Actions\Action;

class PersonalResource extends Resource
{
    protected static ?string $model = Personal::class;

    public static function getNavigationLabel(): string
    {
        return 'Personal';
    }

    public static function getNavigationGroup(): string
    {
        return 'Gestión de Personal';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-users';
    }

    public static function getModelLabel(): string
    {
        return 'Personal';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Personal';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->can('view_any_personal');
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view_any_personal');
    }

    public static function canView($record): bool
    {
        return auth()->user()?->can('view_personal');
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create_personal');
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('update_personal');
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->can('delete_personal');
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->can('delete_any_personal');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Datos Personales')
                ->description('Información básica del personal')
                ->schema([

                    TextInput::make('nombre')
                        ->required()
                        ->label('Nombre')
                        ->placeholder('Ej: Carlos')
                        ->helperText('Se usará para generar el usuario del sistema.'),

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
                        ->label('Fecha de nacimiento')
                        ->placeholder('Seleccionar fecha')
                        ->helperText('Se utilizará como contraseña inicial del usuario.'),

                    TextInput::make('ci')
                        ->label('C.I.')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn(Get $get, Set $set, $state) => $set('biometrico_id', $state))
                        ->placeholder('Carnet de identidad')
                        ->helperText('Identificador único del personal.'),

                    TextInput::make('telefono')
                        ->label('Teléfono (WhatsApp)')
                        ->tel()
                        ->maxLength(13)
                        ->minLength(8)
                        ->required()
                        ->default('+591')
                        ->placeholder('+59171234567')
                        ->prefixIcon('heroicon-o-device-phone-mobile')
                        ->helperText('Incluye el código de país. El número debe comenzar con +591.')
                        ->afterStateHydrated(function (callable $set, $state) {
                            if (empty($state)) {
                                $set('telefono', '+591');
                            }
                        })
                        ->afterStateUpdated(function ($state, callable $set) {
                            if (preg_match('/^\d{8}$/', $state)) {
                                $set('telefono', '+591' . $state);
                            }
                        }),

                    TextInput::make('direccion')
                        ->label('Dirección')
                        ->placeholder('Ej: Av. América Oeste'),

                    TextInput::make('correo')
                        ->email()
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->label('Correo')
                        ->placeholder('correo@ejemplo.com'),

                    FileUpload::make('foto')
                        ->label('Foto del personal')
                        ->image()
                        ->directory('fotos/personal')
                        ->disk('public')
                        ->imageEditor()
                        ->getUploadedFileNameForStorageUsing(fn($file) => time() . '-' . str_replace(' ', '_', $file->getClientOriginalName()))
                        ->previewable(),

                ])->columns(2),

            Section::make('Datos Laborales')
                ->description('Información laboral y administrativa')
                ->schema([
                    Select::make('cargo')
                        ->label('Cargo')
                        ->options(
                            Role::whereIn('name', ['recepcionista', 'instructor'])
                                ->pluck('name', 'name')
                        )
                        ->searchable()
                        ->required()
                        ->helperText('Este campo también define el rol del usuario generado.'),

                    TextInput::make('biometrico_id')
                        ->label('ID Biométrico')
                        ->disabled()
                        ->required()
                        ->dehydrated()
                        ->placeholder('Se autollenará con el C.I.'),

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
            Section::make('Control de cambios')
                ->icon('heroicon-o-user-circle')
                ->collapsible()
                ->columns(1)
                ->dehydrated()
                ->schema([
                    Placeholder::make('registrado_por')
                        ->label('Registrado por')
                        ->content(fn($record) => optional($record?->registradoPor)->name ?? 'Aún no registrado'),

                    Placeholder::make('modificado_por')
                        ->label('Modificado por')
                        ->content(fn($record) => optional($record?->modificadoPor)->name ?? 'Sin modificaciones'),
                ]),
        ]);
    }


    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['telefono']) && preg_match('/^\d{8}$/', $data['telefono'])) {
            $data['telefono'] = '+591' . $data['telefono'];
        }
        $data['modificado_por'] = auth()->id();
        return $data;
    }

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        $data['registrado_por'] = auth()->id();
        return $data;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('foto')
                    ->label('Foto')
                    ->circular()
                    ->height(50),

                TextColumn::make('nombre')
                    ->label('Nombre')
                    ->icon('heroicon-o-user')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('apellido_paterno')
                    ->label('Apellido paterno')
                    ->icon('heroicon-o-user-circle')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('apellido_materno')
                    ->label('Apellido materno')
                    ->icon('heroicon-o-user-circle')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('cargo')
                    ->label('Cargo')
                    ->icon('heroicon-o-briefcase')
                    ->sortable(),

                TextColumn::make('ci')
                    ->label('C.I.')
                    ->icon('heroicon-o-identification')
                    ->searchable(),

                TextColumn::make('telefono')
                    ->label('Teléfono')
                    ->icon('heroicon-o-device-phone-mobile'),

                BadgeColumn::make('estado')
                    ->label('Estado')
                    ->colors([
                        'activo' => 'success',
                        'inactivo' => 'warning',
                        'baja' => 'danger',
                    ])
                    ->icons([
                        'activo' => 'heroicon-o-check-circle',
                        'inactivo' => 'heroicon-o-exclamation-circle',
                        'baja' => 'heroicon-o-x-circle',
                    ])
                    ->formatStateUsing(fn($state) => ucfirst($state))
                    ->sortable(),

                TextColumn::make('registradoPor.name')
                    ->label('Registrado por')
                    ->icon('heroicon-o-user-plus')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('modificadoPor.name')
                    ->label('Modificado por')
                    ->icon('heroicon-o-pencil-square')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('cargo')
                    ->label('Cargo')
                    ->options([
                        'instructor' => 'Instructor',
                        'recepcionista' => 'Recepcionista',
                    ])
                    ->placeholder('Todos'),

                Tables\Filters\SelectFilter::make('estado')
                    ->label('Estado')
                    ->options([
                        'activo' => 'Activo',
                        'inactivo' => 'Inactivo',
                        'baja' => 'De baja',
                    ])
                    ->placeholder('Todos'),

                Tables\Filters\Filter::make('fecha_contratacion')
                    ->label('Fecha de contratación')
                    ->form([
                        DatePicker::make('desde')->label('Desde'),
                        DatePicker::make('hasta')->label('Hasta'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['desde'], fn($q) => $q->whereDate('fecha_contratacion', '>=', $data['desde']))
                            ->when($data['hasta'], fn($q) => $q->whereDate('fecha_contratacion', '<=', $data['hasta']));
                    }),
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
                        Section::make('Foto de Perfil')->schema([
                            Placeholder::make('foto')
                                ->label('Foto del Personal')
                                ->content(fn() => new HtmlString(
                                    '<div style="display: flex; justify-content: center;">
                        <img src="' . asset('storage/' . $record->foto) . '" alt="Foto" style="width: 140px; height: 140px; object-fit: cover; border-radius: 100px; border: 2px solid #ccc;" />
                    </div>'
                                ))
                                ->columnSpanFull(),
                        ]),

                        Section::make('Información Personal')->schema([

                            TextInput::make('nombre')
                                ->label('Nombre')
                                ->default($record->nombre)
                                ->disabled()
                                ->placeholder('Nombre completo'),

                            TextInput::make('apellido_paterno')
                                ->label('Apellido Paterno')
                                ->default($record->apellido_paterno)
                                ->disabled()
                                ->placeholder('Primer apellido'),

                            TextInput::make('apellido_materno')
                                ->label('Apellido Materno')
                                ->default($record->apellido_materno)
                                ->disabled()
                                ->placeholder('Segundo apellido'),

                            TextInput::make('ci')
                                ->label('Carnet de Identidad')
                                ->default($record->ci)
                                ->disabled()
                                ->placeholder('Número de C.I.'),

                            DatePicker::make('fecha_de_nacimiento')
                                ->label('Fecha de Nacimiento')
                                ->default($record->fecha_de_nacimiento)
                                ->disabled()->placeholder('Fecha de nacimiento'),

                            TextInput::make('telefono')
                                ->label('Teléfono')
                                ->default($record->telefono)
                                ->disabled()
                                ->placeholder('Número celular'),

                            TextInput::make('correo')
                                ->label('Correo')
                                ->default($record->correo)->disabled()
                                ->placeholder('Correo electrónico'),

                            TextInput::make('direccion')
                                ->label('Dirección')
                                ->default($record->direccion)
                                ->disabled()
                                ->placeholder('Dirección de domicilio'),

                        ])->columns(2),

                        Section::make('Información Laboral')->schema([

                            TextInput::make('cargo')
                                ->label('Cargo')
                                ->default($record->cargo)
                                ->disabled()
                                ->placeholder('Puesto actual'),

                            TextInput::make('biometrico_id')
                                ->label('ID Biométrico')
                                ->default($record->biometrico_id)
                                ->disabled()
                                ->placeholder('ID para marcación'),

                            DatePicker::make('fecha_contratacion')
                                ->label('Fecha de Contratación')
                                ->default($record->fecha_contratacion)
                                ->disabled()
                                ->placeholder('Fecha de ingreso'),

                            TextInput::make('salario')
                                ->label('Salario (Bs.)')
                                ->default($record->salario)
                                ->disabled()
                                ->placeholder('Salario mensual'),

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

                        Section::make('Observaciones')->schema([

                            Textarea::make('observaciones')
                                ->label('Notas')
                                ->default($record->observaciones)
                                ->disabled()
                                ->placeholder('Información adicional del personal')

                        ]),

                        Section::make('Credenciales del sistema')
                            ->columns(2)
                            ->schema([
                                Placeholder::make('username')
                                    ->label('Usuario')
                                    ->content(fn($record) => optional($record->user)->username ?? 'No generado'),

                                Placeholder::make('password_inicial')
                                    ->label('Contraseña inicial')
                                    ->content(
                                        fn($record) =>
                                        optional($record->fecha_de_nacimiento)
                                        ? \Carbon\Carbon::parse($record->fecha_de_nacimiento)->format('d-m-Y')
                                        : 'No disponible'
                                    ),
                            ]),
                    ]),
                Action::make('Descargar PDF')
                    ->label('Descargar PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->url(fn($record) => route('reporte.personal.ficha', ['id' => $record->id]))
                    ->openUrlInNewTab(),

                Action::make('reporteMensual')
                    ->label('Ficha Mensual')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->url(fn($record) => route('personal.reporte.mensual', ['id' => $record->id]))
                    ->openUrlInNewTab()
                //->visible(fn() => auth()->user()->can('ver_ficha_personal')),

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
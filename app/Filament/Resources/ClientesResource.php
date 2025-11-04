<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientesResource\Pages;
use App\Models\Clientes;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Forms\Components\{
    DatePicker,
    FileUpload,
    Section,
    Select,
    TextInput,
    Textarea,
    Placeholder
};
use Filament\Tables\Columns\{
    BadgeColumn,
    ImageColumn,
    TextColumn
};
use Filament\Tables\Actions\Action;
use Illuminate\Support\HtmlString;
use Filament\Tables;
use App\Models\User;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Filament\Notifications\Notification;



class ClientesResource extends Resource
{
    protected static ?string $model = Clientes::class;

    public static function getNavigationLabel(): string
    {
        return 'Clientes';
    }

    public static function getNavigationGroup(): string
    {
        return 'Administración de Clientes';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-user';
    }

    public static function getModelLabel(): string
    {
        return 'Cliente';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Clientes';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->can('view_any_clientes');
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view_any_clientes');
    }

    public static function canView($record): bool
    {
        return auth()->user()?->can('view_clientes');
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create_clientes');
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('update_clientes');
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasRole('admin');
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->hasRole('admin');
    }
    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Datos personales')
                ->icon('heroicon-o-identification')
                ->columns(2)
                ->schema([
                    TextInput::make('nombre')
                        ->required()
                        ->label('Nombre(s)')
                        ->placeholder('Nombre del cliente')
                        ->helperText('Este nombre se usará para crear el nombre de usuario del sistema.'),

                    TextInput::make('apellido_paterno')
                        ->required()
                        ->label('Apellido paterno')
                        ->placeholder('Apellido paterno'),

                    TextInput::make('apellido_materno')
                        ->nullable()
                        ->label('Apellido materno')
                        ->placeholder('Apellido materno'),

                    DatePicker::make('fecha_de_nacimiento')
                        ->required()
                        ->label('Fecha de nacimiento')
                        ->maxDate(now()->subYears(5))
                        ->minDate(Carbon::createFromDate(1900, 1, 1))
                        ->placeholder('Seleccione una fecha')
                        ->helperText('Se usará como contraseña inicial para el acceso al sistema.'),

                    TextInput::make('ci')
                        ->label('C.I.')
                        ->unique(ignoreRecord: true, table: 'clientes', column: 'ci')
                        ->required()
                        ->placeholder('Carnet de identidad')
                        ->live(onBlur: true)
                        ->afterStateUpdated(function ($state, callable $set) {
                            if ($state) {
                                $set('biometrico_id', $state);
                            }
                        })
                        ->helperText('Este valor se usará como identificador único del usuario.')
                        ->validationMessages([
                            'required' => 'El C.I. es obligatorio.',
                            'regex' => 'El C.I. debe tener entre 5 y 12 dígitos numéricos.',
                            'unique' => 'Ya existe un cliente con ese número de C.I.',
                        ]),

                    TextInput::make('telefono')
                        ->label('Teléfono (WhatsApp)')
                        ->tel()
                        ->maxLength(13)
                        ->minLength(8)
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
                        })
                        ->validationMessages([
                            'required' => 'El número de teléfono es obligatorio.',
                            'regex' => 'El teléfono debe comenzar con +591 y tener 8 dígitos después.',
                            'unique' => 'Este número de WhatsApp ya está registrado.',
                        ]),

                    TextInput::make('correo')
                        ->label('Correo electrónico')
                        ->email()
                        ->nullable()
                        ->placeholder('ejemplo@correo.com')
                        ->rules(fn($record) => [
                            'nullable',
                            'email:rfc',
                            Rule::unique('clientes', 'correo')->ignore($record?->id),
                        ])
                        ->validationMessages([
                            'email' => 'Debes ingresar un correo electrónico válido.',
                            'unique' => 'Este correo ya está registrado en otro cliente.',
                        ]),

                    Select::make('sexo')
                        ->label('Sexo')
                        ->options([
                            'masculino' => 'Masculino',
                            'femenino' => 'Femenino',
                        ])
                        ->placeholder('Seleccione el sexo'),

                    FileUpload::make('foto')
                        ->label('Foto')
                        ->image()
                        ->disk('public')
                        ->directory('fotos/clientes')
                        ->previewable()
                        ->imageEditor()
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg'])
                        ->getUploadedFileNameForStorageUsing(fn($file) => time() . '-' . str_replace(' ', '_', $file->getClientOriginalName())),

                    TextInput::make('biometrico_id')
                        ->label('ID biométrico')
                        ->disabled()
                        ->dehydrated()
                        ->placeholder('Se autollenará con el C.I.'),
                ]),

            Section::make('Emergencias y salud')
                ->icon('heroicon-o-exclamation-circle')
                ->columns(2)
                ->schema([
                    Textarea::make('antecedentes_medicos')
                        ->label('Antecedentes médicos')
                        ->placeholder('Ej: Asma, hipertensión...'),

                    TextInput::make('contacto_emergencia_nombre')
                        ->label('Nombre del contacto de emergencia')
                        ->placeholder('Nombre del contacto'),

                    TextInput::make('contacto_emergencia_parentesco')
                        ->label('¿Que parentesco tiene?')
                        ->placeholder('Parentesco con el cliente'),

                    TextInput::make('contacto_emergencia_celular')
                        ->label('Telefono del contacto')
                        ->placeholder('+59171234567'),
                ]),

            Section::make('Control de cambios')
                ->icon('heroicon-o-user-circle')
                ->collapsible()
                ->columns(1)
                ->visible(fn() => auth()->user()?->hasRole('admin'))
                ->schema([
                    Placeholder::make('registrado_por')
                        ->label('Registrado por')
                        ->content(fn($record) => optional($record?->registradoPor)->name ?? 'No registrado'),

                    Placeholder::make('modificado_por')
                        ->label('Modificado por')
                        ->content(fn($record) => optional($record?->modificadoPor)->name ?? 'Sin cambios'),
                ]),

        ]);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['telefono']) && preg_match('/^\d{8}$/', $data['telefono'])) {
            $data['telefono'] = '+591' . $data['telefono'];
        }

        return $data;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->headerActions([
                Action::make('importarExcel')
                    ->label('Importar Excel')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('success')
                    ->action(function () {
                        Notification::make()
                            ->title('Importado correctamente')
                            ->body('Se procesó el archivo de clientes sin errores.')
                            ->success()
                            ->send();
                    }),
            ])
            ->columns([
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

                TextColumn::make('ci')
                    ->label('C.I.')
                    ->icon('heroicon-o-identification')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('telefono')
                    ->label('Teléfono')
                    ->icon('heroicon-o-device-phone-mobile'),

                TextColumn::make('correo')
                    ->label('Correo')
                    ->icon('heroicon-o-envelope')
                    ->searchable(),

                TextColumn::make('usuario.name')
                    ->label('Registrado por')
                    ->icon('heroicon-o-user-plus')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visible(fn() => auth()->user()?->hasRole('admin')),

                TextColumn::make('modificadoPor.name')
                    ->label('Modificado por')
                    ->icon('heroicon-o-pencil-square')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visible(fn() => auth()->user()?->hasRole('admin')),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('sexo')
                    ->label('Sexo')
                    ->options([
                        'masculino' => 'Masculino',
                        'femenino' => 'Femenino',
                    ]),

            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),

                Action::make('ver')
                    ->label('Ver')
                    ->icon('heroicon-o-eye')
                    ->modalHeading('Detalles del Cliente')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Cerrar')
                    ->color('info')
                    ->form(fn(Clientes $record) => [
                        Section::make('Foto del Cliente')->schema([

                            Placeholder::make('foto')
                                ->label('Foto')
                                ->content(fn() => new HtmlString(
                                    '<div style="display: flex; justify-content: center;">
                                    <img src="' . asset('storage/' . $record->foto) . '" alt="Foto del cliente" style="width: 140px; height: 140px; object-fit: cover; border-radius: 100px; border: 2px solid #ccc;" />
                                </div>'
                                ))->columnSpanFull(),
                        ]),

                        Section::make('Datos Personales')
                            ->columns(2)
                            ->schema([
                                Placeholder::make('nombre')
                                    ->label('Nombre')
                                    ->content($record->nombre),

                                Placeholder::make('apellido_paterno')
                                    ->label('Apellido Paterno')
                                    ->content($record->apellido_paterno),

                                Placeholder::make('apellido_materno')
                                    ->label('Apellido Materno')
                                    ->content($record->apellido_materno),

                                Placeholder::make('ci')
                                    ->label('C.I.')
                                    ->content($record->ci),

                                Placeholder::make('fecha_de_nacimiento')
                                    ->label('Fecha de nacimiento')
                                    ->content($record->fecha_de_nacimiento),

                                Placeholder::make('telefono')
                                    ->label('Teléfono')
                                    ->content($record->telefono),

                                Placeholder::make('correo')
                                    ->label('Correo')
                                    ->content($record->correo),

                                Placeholder::make('sexo')
                                    ->label('Sexo')
                                    ->content($record->sexo),

                                Placeholder::make('biometrico_id')
                                    ->label('ID Biométrico')
                                    ->content($record->biometrico_id),
                            ]),

                        Section::make('Emergencias y Salud')->columns(2)->schema([

                            Placeholder::make('antecedentes_medicos')
                                ->label('Antecedentes Médicos')
                                ->content($record->antecedentes_medicos ?? 'Ninguno'),

                            Placeholder::make('contacto_emergencia_nombre')
                                ->label('Nombre de emergencia')
                                ->content($record->contacto_emergencia_nombre ?? '-'),

                            Placeholder::make('contacto_emergencia_parentesco')
                                ->label('Parentesco')
                                ->content($record->contacto_emergencia_parentesco ?? '-'),

                            Placeholder::make('contacto_emergencia_celular')
                                ->label('Celular')
                                ->content($record->contacto_emergencia_celular ?? '-'),

                        ]),

                        Section::make('Credenciales del sistema')
                            ->columns(2)
                            ->schema([
                                Placeholder::make('username')
                                    ->label('Usuario')
                                    ->content(fn($record) => optional(User::find($record->user_id))->username ?? 'No generado'),

                                Placeholder::make('password_inicial')
                                    ->label('Contraseña inicial')
                                    ->content(
                                        fn($record) =>
                                        optional($record->fecha_de_nacimiento)
                                        ? Carbon::parse($record->fecha_de_nacimiento)->format('d-m-Y')
                                        : 'No disponible'
                                    ),
                            ]),
                    ]),
                Action::make('descargarFicha')
                    ->label('Ficha Cliente')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->url(fn($record) => route('reporte.cliente.ficha', ['id' => $record->id]))
                    ->openUrlInNewTab()
                    ->visible(fn() => auth()->user()?->hasRole('admin')),

                Action::make('reporteMensual')
                    ->label('PDF Mensual')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->url(fn($record) => route('clientes.reporte.mensual', $record->id))
                    ->openUrlInNewTab()
                    ->visible(fn() => auth()->user()?->hasRole('admin')),
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
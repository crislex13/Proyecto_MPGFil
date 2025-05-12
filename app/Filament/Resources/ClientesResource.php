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
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Hidden;
use Filament\Facades\Filament;
use App\Models\User;


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
        return auth()->user()?->hasAnyRole(['admin', 'recepcionista', 'supervisor']);
    }

    public static function canCreate(): bool
    {
        return self::shouldRegisterNavigation();
    }

    public static function canEdit($record): bool
    {
        return self::shouldRegisterNavigation();
    }

    public static function canDelete($record): bool
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
                        ->placeholder('Nombre del cliente')
                        ->helperText('Este nombre se usará para crear el nombre de usuario del sistema.'),

                    TextInput::make('apellido_paterno')
                        ->required()
                        ->placeholder('Apellido paterno'),

                    TextInput::make('apellido_materno')
                        ->placeholder('Apellido materno'),

                    DatePicker::make('fecha_de_nacimiento')
                        ->required()
                        ->label('Fecha de nacimiento')
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
                        ->helperText('Este valor se usará como identificador único del usuario.'),

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

                    TextInput::make('correo')
                        ->label('Correo')
                        ->email()
                        ->placeholder('correo@ejemplo.com'),

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
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg']) // <- Agrega esto
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
                        ->label('Nombre de emergencia')
                        ->placeholder('Nombre del contacto'),

                    TextInput::make('contacto_emergencia_parentesco')
                        ->label('Parentesco')
                        ->placeholder('Parentesco con el cliente'),

                    TextInput::make('contacto_emergencia_celular')
                        ->label('Celular del contacto')
                        ->placeholder('+59171234567'),
                ]),

            Section::make('Control de cambios')
                ->icon('heroicon-o-user-circle')
                ->collapsible()
                ->columns(1)
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

        return $data;
    }

    public static function table(Table $table): Table
    {
        return $table
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
                    ->sortable(),

                TextColumn::make('modificadoPor.name')
                    ->label('Modificado por')
                    ->icon('heroicon-o-pencil-square')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('sexo')
                    ->label('Sexo')
                    ->options([
                        'masculino' => 'Masculino',
                        'femenino' => 'Femenino',
                    ]),

                Tables\Filters\Filter::make('rango_edad')
                    ->label('Edad')
                    ->form([
                        TextInput::make('min')
                            ->label('Edad mínima')
                            ->numeric()
                            ->placeholder('Ej: 18'),
                        TextInput::make('max')
                            ->label('Edad máxima')
                            ->numeric()
                            ->placeholder('Ej: 50'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['min'],
                                fn($q) =>
                                $q->whereDate('fecha_de_nacimiento', '<=', now()->subYears($data['min']))
                            )
                            ->when(
                                $data['max'],
                                fn($q) =>
                                $q->whereDate('fecha_de_nacimiento', '>=', now()->subYears($data['max']))
                            );
                    }),

                Tables\Filters\SelectFilter::make('registrado_por')
                    ->label('Registrado por')
                    ->relationship('registradoPor', 'name')
                    ->searchable(),
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
                                        ? \Carbon\Carbon::parse($record->fecha_de_nacimiento)->format('d-m-Y')
                                        : 'No disponible'
                                    ),
                            ]),
                    ]),
                Action::make('descargarFicha')
                    ->label('Ficha Cliente')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->url(fn($record) => route('reporte.cliente.ficha', ['id' => $record->id]))
                    ->openUrlInNewTab(),

                Action::make('reporteMensual')
                    ->label('PDF Mensual')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->url(fn($record) => route('clientes.reporte.mensual', $record->id))
                    ->openUrlInNewTab()
                //->visible(fn() => auth()->user()->can('ver_ficha_cliente')),
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
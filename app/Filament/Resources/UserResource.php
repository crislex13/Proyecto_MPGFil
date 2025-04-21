<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\Placeholder;
use Illuminate\Support\HtmlString;
use Filament\Tables\Filters\Filter;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $label = 'Usuario';
    protected static ?string $pluralLabel = 'Usuarios';
    protected static ?string $navigationLabel = 'Usuarios';
    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static ?string $navigationGroup = 'Administración';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Información Personal')
                ->icon('heroicon-o-user-circle')
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->label('Nombre completo')
                        ->placeholder('Ej: María Pérez')
                        ->required(),

                    TextInput::make('ci')
                        ->label('C.I.')
                        ->placeholder('Número de carnet de identidad')
                        ->unique(ignoreRecord: true)
                        ->required(),

                    TextInput::make('telefono')
                        ->label('Teléfono (WhatsApp)')
                        ->tel()
                        ->placeholder('+59171234567')
                        ->prefix('+591')
                        ->helperText('Incluye el código de país.')
                        ->maxLength(13),

                    FileUpload::make('foto')
                        ->label('Foto de perfil')
                        ->image()
                        ->directory('fotos/usuarios')
                        ->visibility('public')
                        ->previewable()
                        ->getUploadedFileNameForStorageUsing(
                            fn($file) => time() . '-' . str_replace(' ', '_', $file->getClientOriginalName())
                        ),
                ]),

            Section::make('Acceso al sistema')
                ->icon('heroicon-o-lock-closed')
                ->columns(2)
                ->schema([
                    TextInput::make('email')
                        ->label('Correo electrónico')
                        ->placeholder('usuario@sistema.com')
                        ->email()
                        ->required()
                        ->unique(ignoreRecord: true),

                    TextInput::make('password')
                        ->label('Contraseña')
                        ->placeholder('Contraseña segura')
                        ->password()
                        ->required(fn($context) => $context === 'create')
                        ->dehydrateStateUsing(fn($state) => bcrypt($state))
                        ->visibleOn('create'),

                    Select::make('estado')
                        ->label('Estado')
                        ->options([
                            'activo' => 'Activo',
                            'inactivo' => 'Inactivo',
                        ])
                        ->default('activo')
                        ->required(),

                    Select::make('roles')
                        ->label('Roles')
                        ->relationship('roles', 'name')
                        ->multiple()
                        ->searchable()
                        ->preload()
                        ->required()
                        ->hint('Selecciona uno o más roles.'),
                ]),
        ]);
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

                TextColumn::make('name')
                    ->label('Nombre completo')
                    ->icon('heroicon-o-user')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('ci')
                    ->label('C.I.')
                    ->icon('heroicon-o-identification')
                    ->searchable(),

                TextColumn::make('telefono')
                    ->label('Teléfono')
                    ->icon('heroicon-o-device-phone-mobile'),

                TextColumn::make('email')
                    ->label('Correo')
                    ->icon('heroicon-o-envelope'),

                BadgeColumn::make('estado')
                    ->label('Estado')
                    ->colors([
                        'success' => 'activo',
                        'danger' => 'inactivo',
                    ]),

                TextColumn::make('roles.name')
                    ->label('Rol')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'admin' => 'danger',
                        'cliente' => 'primary',
                        'instructor' => 'success',
                        'recepcionista' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('last_login_at')
                    ->label('Último acceso')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                Filter::make('Conectados hoy')
                    ->query(fn ($query) => $query->whereDate('last_login_at', now()->toDateString())),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                ViewAction::make()
                    ->label('Ver')
                    ->icon('heroicon-o-eye')
                    ->modalHeading('👤 Detalles del Usuario')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Cerrar')
                    ->color('info')
                    ->form(function (User $record) {
                        return [
                            Section::make('Foto de perfil')->schema([
                                Placeholder::make('foto')
                                    ->label('Foto')
                                    ->content(fn() => new HtmlString(
                                        '<div style="display: flex; justify-content: center;">
                            <img src="' . asset('storage/' . $record->foto) . '" alt="Foto del usuario" style="width: 140px; height: 140px; object-fit: cover; border-radius: 100px; border: 2px solid #ccc;" />
                        </div>'
                                    ))->columnSpanFull(),
                            ]),

                            Section::make('Información básica')
                                ->columns(2)
                                ->schema([
                                    Placeholder::make('name')
                                        ->label('Nombre')
                                        ->content($record->name),

                                    Placeholder::make('ci')
                                        ->label('C.I.')
                                        ->content($record->ci),

                                    Placeholder::make('telefono')
                                        ->label('Teléfono')
                                        ->content($record->telefono ?? 'No registrado'),

                                    Placeholder::make('email')
                                        ->label('Correo electrónico')
                                        ->content($record->email),
                                ]),

                            Section::make('Acceso y estado')
                                ->columns(2)
                                ->schema([
                                    Placeholder::make('estado')
                                        ->label('Estado')
                                        ->content(fn() => $record->estado === 'activo' ? '🟢 Activo' : '🔴 Inactivo'),

                                    Placeholder::make('roles')
                                        ->label('Rol(es)')
                                        ->content(function () use ($record) {
                                            return $record->roles->pluck('name')->implode(', ') ?: 'Sin rol asignado';
                                        }),
                                ]),
                        ];
                    }),
            ])
            ->defaultSort('name');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}

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
            Section::make('Información básica')
                ->schema([
                    TextInput::make('name')
                        ->label('Nombre Completo')
                        ->required()
                        ->maxLength(255),

                    TextInput::make('email')
                        ->label('Correo Electrónico')
                        ->email()
                        ->unique(ignoreRecord: true)
                        ->required(),

                    TextInput::make('password')
                        ->label('Contraseña')
                        ->password()
                        ->required(fn ($context) => $context === 'create')
                        ->dehydrateStateUsing(fn ($state) => bcrypt($state)),
                ])->columns(1),

            Section::make('Opciones adicionales')
                ->schema([
                    FileUpload::make('foto')
                        ->label('Foto de Perfil')
                        ->image()
                        ->directory('fotos/usuarios')
                        ->visibility('public')
                        ->imageEditor()
                        ->nullable(),

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
                        ->preload()
                        ->searchable()
                        ->required(),
                ])->columns(1),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('email')
                    ->label('Correo Electrónico')
                    ->sortable()
                    ->searchable(),

                BadgeColumn::make('estado')
                    ->label('Estado')
                    ->colors([
                        'success' => 'activo',
                        'danger' => 'inactivo',
                    ])
                    ->sortable(),

                TextColumn::make('roles.name')
                    ->label('Roles')
                    ->badge()
                    ->color('primary'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
                ViewAction::make(),
            ]);
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

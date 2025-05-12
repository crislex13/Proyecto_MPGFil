<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ConfiguracionResource\Pages;
use App\Filament\Resources\ConfiguracionResource\RelationManagers;
use App\Models\Configuracion;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;

class ConfiguracionResource extends Resource
{
    protected static ?string $model = Configuracion::class;

    public static function getNavigationLabel(): string
    {
        return 'Configuración';
    }

    public static function getNavigationGroup(): string
    {
        return 'Administración del Sistema';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-cog-6-tooth';
    }

    public static function getModelLabel(): string
    {
        return 'Configuración';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Configuraciones';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasRole('admin');
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasRole('admin');
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->hasRole('admin');
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasRole('admin');
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Control de acceso global')
                    ->icon('heroicon-o-lock-closed')
                    ->description('Habilita o bloquea el acceso de clientes e instructores con un solo clic.')
                    ->schema([
                        Toggle::make('clientes_pueden_acceder')
                            ->label('Permitir acceso a clientes'),

                        Toggle::make('instructores_pueden_acceder')
                            ->label('Permitir acceso a instructores'),
                    ])
                    ->columns(2),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\IconColumn::make('clientes_pueden_acceder')
                    ->label('Acceso Clientes')
                    ->boolean(),

                Tables\Columns\IconColumn::make('instructores_pueden_acceder')
                    ->label('Acceso Instructores')
                    ->boolean(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ManageConfiguraciones::route('/'),
            'edit' => Pages\EditConfiguracion::route('/{record}/edit'),
        ];
    }
}

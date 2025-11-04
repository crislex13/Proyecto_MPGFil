<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SalaResource\Pages;
use App\Models\Sala;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;

class SalaResource extends Resource
{
    protected static ?string $model = Sala::class;

    public static function getNavigationLabel(): string
    {
        return 'Salas de Sesiones';
    }

    public static function getNavigationGroup(): string
    {
        return 'Gestión de Personal';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-building-office';
    }

    public static function getModelLabel(): string
    {
        return 'Sala de Sesión';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Salas de Sesión';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->can('view_any_sala');
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view_any_sala');
    }

    public static function canView($record): bool
    {
        return auth()->user()?->can('view_sala');
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create_sala');
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('update_sala');
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasRole('admin') === true;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->hasRole('admin') === true;
    }
    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Información de la Sala')
                ->description('Complete los datos básicos sobre esta sala de entrenamiento')
                ->icon('heroicon-o-home-modern')
                ->schema([
                    TextInput::make('nombre')
                        ->label('Nombre de la Sala')
                        ->placeholder('Ejemplo: Sala A, Zona Funcional, Área de Cardio')
                        ->required()
                        ->maxLength(50)
                        ->columnSpanFull(),

                    Textarea::make('descripcion')
                        ->label('Descripción')
                        ->placeholder('Detalles adicionales como tamaño, tipo de equipamiento o ubicación dentro del gimnasio')
                        ->rows(3)
                        ->maxLength(255)
                        ->columnSpanFull(),

                    Select::make('estado')
                        ->label('Estado de la Sala')
                        ->placeholder('Seleccione el estado actual')
                        ->options([
                            'activo' => 'Activa',
                            'inactivo' => 'Inactiva',
                        ])
                        ->default('activo')
                        ->required()
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

                ImageColumn::make('icono')
                    ->getStateUsing(fn() => asset('images/sala-icon.png'))
                    ->circular()
                    ->height(25)
                    ->width(25)
                    ->label('Sala'),

                TextColumn::make('nombre')
                    ->label('Nombre de la Sala')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('descripcion')
                    ->label('Descripción')
                    ->limit(50)
                    ->wrap()
                    ->icon('heroicon-o-document-text'),

                BadgeColumn::make('estado')
                    ->label('Estado')
                    ->colors([
                        'success' => fn($state): bool => $state === 'activo',
                        'danger' => fn($state): bool => $state === 'inactivo',
                    ])
                    ->formatStateUsing(fn($state) => $state === 'activo' ? 'Activa' : 'Inactiva')
                    ->sortable()
                    ->icon('heroicon-o-light-bulb'),

                TextColumn::make('registradoPor.name')
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
                Tables\Filters\SelectFilter::make('estado')
                    ->label('Estado de la Sala')
                    ->options([
                        'activo' => 'Activa',
                        'inactivo' => 'Inactiva',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                
                Tables\Actions\EditAction::make(),

                Tables\Actions\DeleteAction::make()
                    ->visible(fn() => auth()->user()?->hasRole('admin'))
                    ->authorize(fn() => auth()->user()?->hasRole('admin'))
                    ->requiresConfirmation()
                    ->successNotificationTitle('Sala eliminada'),

                Tables\Actions\Action::make('ver')
                    ->label('Ver Detalles')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->modalHeading('Detalles de la Sala')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Cerrar')
                    ->form(fn(Sala $record) => [
                        Section::make('Información General')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                TextInput::make('nombre')
                                    ->default($record->nombre)
                                    ->disabled(),

                                Textarea::make('descripcion')
                                    ->default($record->descripcion)
                                    ->disabled()
                                    ->rows(3),

                                TextInput::make('estado')
                                    ->default(match ($record->estado) {
                                        'activo' => 'Activa',
                                        'inactivo' => 'Inactiva',
                                        default => 'Desconocido',
                                    })
                                    ->disabled(),
                            ]),
                    ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListSalas::route('/'),
            'create' => Pages\CreateSala::route('/create'),
            'edit' => Pages\EditSala::route('/{record}/edit'),
        ];
    }

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        $data['registrado_por'] = auth()->id();
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['modificado_por'] = auth()->id();
        return $data;
    }
}

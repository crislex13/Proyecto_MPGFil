<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DisciplinaResource\Pages;
use App\Models\Disciplina;
use Filament\Forms\Form;
use Filament\Forms\Components\{
    Section,
    TextInput,
    Textarea
};
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;

class DisciplinaResource extends Resource
{
    protected static ?string $model = Disciplina::class;

    public static function getNavigationLabel(): string
    {
        return 'Disciplinas';
    }

    public static function getNavigationGroup(): string
    {
        return 'Catálogo de Planes';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-bolt';
    }

    public static function getModelLabel(): string
    {
        return 'Disciplina';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Disciplinas';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->can('view_any_disciplina');
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view_any_disciplina');
    }

    public static function canView($record): bool
    {
        return auth()->user()?->can('view_disciplina');
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create_disciplina');
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('update_disciplina');
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
            Section::make('Datos de la Disciplina')
                ->description('Completa la información para registrar una disciplina del gimnasio.')
                ->icon('heroicon-o-clipboard-document-check')
                ->columns(2)
                ->schema([
                    TextInput::make('nombre')
                        ->label('Nombre de la disciplina')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Ej: Crossfit, Zumba, Spinning'),

                    TextInput::make('descripcion')
                        ->label('Descripción breve')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Ej: Clase intensa de resistencia y fuerza'),

                    Textarea::make('observaciones')
                        ->label('Observaciones adicionales')
                        ->rows(3)
                        ->maxLength(500)
                        ->placeholder('Notas, recomendaciones o detalles logísticos...'),
                ]),
        ]);
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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->label('Disciplina')
                    ->icon('heroicon-o-bolt')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('descripcion')
                    ->label('Descripción')
                    ->icon('heroicon-o-document-text')
                    ->limit(40)
                    ->tooltip(fn($record) => $record->descripcion),

                TextColumn::make('observaciones')
                    ->label('Observaciones')
                    ->icon('heroicon-o-eye')
                    ->limit(40)
                    ->tooltip(fn($record) => $record->observaciones),

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
                Tables\Filters\Filter::make('con_observaciones')
                    ->label('Solo con observaciones')
                    ->query(fn($query) => $query->whereNotNull('observaciones')->where('observaciones', '!=', '')),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->tooltip('Editar disciplina'),

                Tables\Actions\DeleteAction::make()
                    ->visible(fn() => auth()->user()?->hasRole('admin'))
                    ->authorize(fn() => auth()->user()?->hasRole('admin'))
                    ->requiresConfirmation()
                    ->successNotificationTitle('Disciplina eliminada'),
                    
                Tables\Actions\Action::make('ver')
                    ->label('Ver Detalles')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->modalHeading('Detalles de la Disciplina')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Cerrar')
                    ->form(fn(Disciplina $record) => [
                        Section::make('Detalles de la Disciplina')
                            ->icon('heroicon-o-bolt')
                            ->schema([
                                TextInput::make('nombre')
                                    ->label('Nombre')
                                    ->default($record->nombre)
                                    ->disabled(),

                                TextInput::make('descripcion')
                                    ->label('Descripción')
                                    ->default($record->descripcion)
                                    ->disabled(),

                                Textarea::make('observaciones')
                                    ->label('Observaciones')
                                    ->default($record->observaciones)
                                    ->disabled()
                                    ->rows(3),
                            ]),
                    ]),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDisciplinas::route('/'),
            'create' => Pages\CreateDisciplina::route('/create'),
            'edit' => Pages\EditDisciplina::route('/{record}/edit'),
        ];
    }
}
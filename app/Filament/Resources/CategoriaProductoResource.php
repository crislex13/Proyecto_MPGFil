<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoriaProductoResource\Pages;
use App\Models\CategoriaProducto;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\Placeholder;

class CategoriaProductoResource extends Resource
{
    protected static ?string $model = CategoriaProducto::class;

    public static function getNavigationLabel(): string
    {
        return 'Categorías de Productos';
    }

    public static function getNavigationGroup(): string
    {
        return 'Gestión de Productos';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-tag';
    }

    public static function getModelLabel(): string
    {
        return 'Categoría de Producto';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Categorías de Productos';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->can('view_any_categoria::producto');
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view_any_categoria::producto');
    }

    public static function canView($record): bool
    {
        return auth()->user()?->can('view_categoria::producto');
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create_categoria::producto');
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('update_categoria::producto');
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
            Section::make('Datos de la categoría')
                ->description('Defina el nombre y una descripción general de la categoría.')
                ->schema([
                    TextInput::make('nombre')
                        ->label('Nombre de la categoría')
                        ->required()
                        ->placeholder('Ej: Bebidas'),

                    Textarea::make('descripcion')
                        ->label('Descripción')
                        ->placeholder('Ej: Productos líquidos, refrigerantes, energizantes...')
                        ->rows(3),
                ])
                ->columns(1),
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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->label('Nombre')
                    ->icon('heroicon-o-tag')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('descripcion')
                    ->label('Descripción')
                    ->icon('heroicon-o-document-text')
                    ->limit(50)
                    ->tooltip(fn($record) => $record->descripcion),

                TextColumn::make('registradoPor.name')
                    ->label('Registrado por')
                    ->icon('heroicon-o-user-plus')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable()
                    ->visible(fn() => auth()->user()?->hasRole('admin')),

                TextColumn::make('modificadoPor.name')
                    ->label('Modificado por')
                    ->icon('heroicon-o-pencil-square')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable()
                    ->visible(fn() => auth()->user()?->hasRole('admin')),

            ])
            ->filters([
                SelectFilter::make('nombre')
                    ->label('Filtrar por nombre')
                    ->options(CategoriaProducto::pluck('nombre', 'nombre')),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->tooltip('Editar categoría'),
                Tables\Actions\DeleteAction::make()
                    ->tooltip('Eliminar categoría')
                    ->visible(fn() => auth()->user()?->hasRole('admin'))
                    ->authorize(fn() => auth()->user()?->hasRole('admin'))
                    ->requiresConfirmation()
                    ->successNotificationTitle('Categoría eliminada'),
            ])
            ->headerActions([
                Tables\Actions\Action::make('pdfCategoriasGeneral')
                    ->label('PDF Categorías (General)')
                    ->icon('heroicon-o-document-arrow-down')
                    ->url(fn() => route('reportes.categorias.general'))
                    ->openUrlInNewTab()
                    ->visible(fn() => auth()->user()?->hasRole('admin')),

                Tables\Actions\Action::make('pdfCategoriasMes')
                    ->label('PDF Categorías (Mes actual)')
                    ->icon('heroicon-o-document-arrow-down')
                    ->url(fn() => route('reportes.categorias.mes'))
                    ->openUrlInNewTab()
                    ->visible(fn() => auth()->user()?->hasRole('admin')),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategoriaProductos::route('/'),
            'create' => Pages\CreateCategoriaProducto::route('/create'),
            'edit' => Pages\EditCategoriaProducto::route('/{record}/edit'),
        ];
    }
}

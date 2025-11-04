<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VentaProductoResource\Pages;
use App\Models\VentaProducto;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\{
    Section,
    DatePicker,
    Select,
    Placeholder
};
use Filament\Tables\Columns\{
    TextColumn
};
use App\Filament\Resources\VentaProductoResource\RelationManagers\DetallesRelationManager;
use Illuminate\Database\Eloquent\Builder;

class VentaProductoResource extends Resource
{
    protected static ?string $model = VentaProducto::class;

    public static function getNavigationLabel(): string
    {
        return 'Ventas de Productos';
    }

    public static function getNavigationGroup(): string
    {
        return 'Gestión de Productos';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-shopping-cart';
    }

    public static function getModelLabel(): string
    {
        return 'Venta de Producto';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Ventas de Productos';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->can('view_any_venta::producto');
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view_any_venta::producto');
    }

    public static function canView($record): bool
    {
        return auth()->user()?->can('view_venta::producto');
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create_venta::producto');
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('update_venta::producto');
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
            Section::make('Información de la Venta')
                ->description('Complete los datos generales de la venta.')
                ->icon('heroicon-o-currency-dollar')
                ->columns(2)
                ->schema([
                    DatePicker::make('fecha')
                        ->label('Fecha de Venta')
                        ->default(now())
                        ->required()
                        ->placeholder('Seleccione la fecha de la venta'),

                    Select::make('usuario_id')
                        ->label('Responsable')
                        ->relationship('usuario', 'name')
                        ->default(Auth::id())
                        ->disabled()
                        ->dehydrated()
                        ->required()
                        ->placeholder('Usuario actual'),

                    Select::make('metodo_pago')
                        ->label('Forma de Pago')
                        ->options([
                            'efectivo' => 'Efectivo',
                            'qr' => 'QR',
                        ])
                        ->required()
                        ->placeholder('Seleccione el método de pago'),

                    Placeholder::make('total')
                        ->label('Total (Bs.)')
                        ->content(fn($record) => $record ? number_format($record->total, 2, ',', '.') . ' Bs' : '0.00 Bs'),

                    Section::make('Control de cambios')
                        ->icon('heroicon-o-user-circle')
                        ->collapsible()
                        ->columns(2)
                        ->schema([
                            Placeholder::make('registrado_por')
                                ->label('Registrado por')
                                ->content(fn($record) => optional($record?->registradoPor)->name ?? '—'),
                            Placeholder::make('modificado_por')
                                ->label('Modificado por')
                                ->content(fn($record) => optional($record?->modificadoPor)->name ?? '—'),
                        ]),
                ]),
        ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();

        return parent::getEloquentQuery()
            ->with(['usuario', 'registradoPor', 'modificadoPor'])
            ->when(
                !$user?->hasRole('admin'),
                fn($q) =>
                $q->where('usuario_id', $user?->id)
            );
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('fecha')
                    ->label('Fecha')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->icon('heroicon-o-calendar'),

                TextColumn::make('usuario.name')
                    ->label('Responsable')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-user'),

                TextColumn::make('metodo_pago')
                    ->label('Método de Pago')
                    ->sortable()
                    ->formatStateUsing(fn($state) => ucfirst($state))
                    ->icon('heroicon-o-credit-card'),

                TextColumn::make('total')
                    ->label('Total (Bs.)')
                    ->money('BOB')
                    ->alignRight()
                    ->sortable()
                    ->icon('heroicon-o-banknotes'),

                TextColumn::make('registradoPor.name')
                    ->label('Registrado por')
                    ->icon('heroicon-o-user-plus')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable()
                    ->searchable(),

                TextColumn::make('modificadoPor.name')
                    ->label('Modificado por')
                    ->icon('heroicon-o-pencil-square')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([

                Tables\Filters\SelectFilter::make('metodo_pago')
                    ->label('Método de Pago')
                    ->options([
                        'efectivo' => 'Efectivo',
                        'qr' => 'QR',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Adicionar venta')
                    ->tooltip('Adicionar productos a esta venta')
                    ->icon('heroicon-o-plus-circle'),

                Tables\Actions\DeleteAction::make()
                    ->visible(fn() => auth()->user()?->hasRole('admin'))
                    ->authorize(fn() => auth()->user()?->hasRole('admin'))
                    ->requiresConfirmation()
                    ->successNotificationTitle('Venta eliminada'),
            ])
            ->headerActions([
                // ==== Para TODOS (personales) ====
                Tables\Actions\Action::make('rep_dia_mias')
                    ->label('Mi reporte diario')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->url(route('reporte.ventas.dia.mias'))
                    ->openUrlInNewTab(),

                Tables\Actions\Action::make('rep_mes_mias')
                    ->label('Mi reporte mensual')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('warning')
                    ->url(fn() => route('reporte.ventas.mes.mias', [
                        'year' => now()->year,
                        'month' => now()->month,
                    ]))
                    ->openUrlInNewTab(),

                // ==== Solo ADMIN (globales) ====
                Tables\Actions\Action::make('rep_dia_global')
                    ->label('Reporte diario (global)')
                    ->icon('heroicon-o-document-chart-bar')
                    ->color('primary')
                    ->url(route('reporte.ventas.dia.global'))
                    ->openUrlInNewTab()
                    ->visible(fn() => auth()->user()?->hasRole('admin')),

                Tables\Actions\Action::make('rep_mes_global')
                    ->label('Reporte mensual (global)')
                    ->icon('heroicon-o-document-chart-bar')
                    ->color('primary')
                    ->url(fn() => route('reporte.ventas.mes.global', [
                        'year' => now()->year,
                        'month' => now()->month,
                    ]))
                    ->openUrlInNewTab()
                    ->visible(fn() => auth()->user()?->hasRole('admin')),

                Tables\Actions\Action::make('rep_anio_global')
                    ->label('Reporte anual (global)')
                    ->icon('heroicon-o-document-chart-bar')
                    ->color('primary')
                    ->url(fn() => route('reporte.ventas.anio.global', [
                        'year' => now()->year,
                    ]))
                    ->openUrlInNewTab()
                    ->visible(fn() => auth()->user()?->hasRole('admin')),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVentaProductos::route('/'),
            'create' => Pages\CreateVentaProducto::route('/create'),
            'edit' => Pages\EditVentaProducto::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            DetallesRelationManager::class,
        ];
    }


}
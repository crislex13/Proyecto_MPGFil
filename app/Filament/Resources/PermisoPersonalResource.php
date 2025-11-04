<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PermisoPersonalResource\Pages;
use App\Models\PermisoPersonal;
use App\Models\Personal;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Section;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\Hidden;


class PermisoPersonalResource extends Resource
{
    protected static ?string $model = PermisoPersonal::class;

    public static function getNavigationLabel(): string
    {
        return 'Permisos del Personal';
    }

    public static function getNavigationGroup(): string
    {
        return 'Control de Accesos';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-calendar-days';
    }

    public static function getModelLabel(): string
    {
        return 'Permiso del Personal';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Permisos del Personal';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->can('view_any_permiso::personal');
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view_any_permiso::personal');
    }

    public static function canView($record): bool
    {
        return auth()->user()?->can('view_permiso::personal');
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create_permiso::personal');
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('update_permiso::personal');
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasRole('admin');
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->hasRole('admin');
    }
    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Section::make('Solicitud de Permiso')
                ->description('Complete los detalles del permiso solicitado')
                ->columns(2)
                ->schema([
                    Select::make('personal_id')
                        ->label('Instructor')
                        ->relationship(
                            name: 'personal',
                            titleAttribute: 'id',
                            modifyQueryUsing: fn($query, $search) =>
                            $query->where('nombre', 'like', "%{$search}%")
                                ->orWhere('apellido_paterno', 'like', "%{$search}%")
                                ->orWhere('apellido_materno', 'like', "%{$search}%")
                        )
                        ->getOptionLabelFromRecordUsing(fn($record) => $record->nombre_completo)
                        ->searchable()
                        ->preload()
                        ->required()
                        ->placeholder('Seleccione al instructor responsable'),

                    DatePicker::make('fecha_inicio')
                        ->label('Fecha de inicio')
                        ->required()
                        ->native(false)
                        ->placeholder('Seleccione la fecha de inicio'),

                    DatePicker::make('fecha_fin')
                        ->label('Fecha de fin')
                        ->required()
                        ->native(false)
                        ->minDate(fn(callable $get) => $get('fecha_inicio'))
                        ->placeholder('Seleccione la fecha de fin'),

                    Select::make('tipo')
                        ->label('Tipo de permiso')
                        ->options([
                            'completo' => 'Día completo',
                            'parcial' => 'Parcial',
                        ])
                        ->required()
                        ->native(false)
                        ->placeholder('Seleccione el tipo de permiso'),

                    Select::make('estado')
                        ->label('Estado')
                        ->options([
                            'pendiente' => 'Pendiente',
                            'aprobado' => 'Aprobado',
                            'rechazado' => 'Rechazado',
                        ])
                        ->required()
                        ->default('pendiente')
                        ->native(false)
                        ->placeholder('Seleccione el estado actual del permiso'),

                    Hidden::make('autorizado_por')
                        ->default(auth()->id())
                        ->dehydrated(),

                    Textarea::make('motivo')
                        ->label('Motivo del permiso')
                        ->placeholder('Describa brevemente el motivo del permiso')
                        ->rows(3),

                    Section::make('Control de cambios')
                        ->icon('heroicon-o-user-circle')
                        ->collapsible()
                        ->columns(1)
                        ->visible(fn() => auth()->user()?->hasRole('admin'))
                        ->schema([
                            \Filament\Forms\Components\Placeholder::make('registrado_por')
                                ->label('Registrado por')
                                ->content(fn($record) => optional($record?->registradoPor)->name ?? 'No registrado'),

                            \Filament\Forms\Components\Placeholder::make('modificado_por')
                                ->label('Modificado por')
                                ->content(fn($record) => optional($record?->modificadoPor)->name ?? 'Sin cambios'),
                        ]),
                ]),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                ImageColumn::make('personal.foto_url')
                    ->label('Foto')
                    ->circular()
                    ->height(40)
                    ->width(40),

                TextColumn::make('personal.nombre_completo')
                    ->label('Instructor')
                    ->icon('heroicon-o-user')
                    ->sortable(query: function (Builder $query, string $direction) {
                        $query->leftJoin('personals', 'permisos_personal.personal_id', '=', 'personals.id')
                            ->orderByRaw("CONCAT(personals.nombre,' ',personals.apellido_paterno,' ',COALESCE(personals.apellido_materno,'')) {$direction}")
                            ->select('permisos_personal.*');
                    })
                    ->searchable(['nombre', 'apellido_paterno', 'apellido_materno']),

                TextColumn::make('fecha_inicio')
                    ->label('Inicio')
                    ->icon('heroicon-o-calendar')
                    ->date()
                    ->sortable(),

                TextColumn::make('fecha_fin')
                    ->label('Fin')
                    ->icon('heroicon-o-calendar-days')
                    ->date()
                    ->sortable(),

                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->icon('heroicon-o-clipboard-document')
                    ->formatStateUsing(fn($state) => ucfirst($state))
                    ->sortable(),

                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->color(fn(?string $state): string => match ($state) {
                        'pendiente' => 'warning',
                        'aprobado' => 'success',
                        'rechazado' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('autorizadoPor.name')
                    ->label('Autorizado por')
                    ->icon('heroicon-o-user-circle')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('motivo')
                    ->label('Motivo')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->limit(40)
                    ->tooltip(fn($record) => $record->motivo)
                    ->searchable(),

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
            ->defaultSort('fecha_inicio', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('estado')
                    ->label('Estado del permiso')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'aprobado' => 'Aprobado',
                        'rechazado' => 'Rechazado',
                    ]),

                Tables\Filters\SelectFilter::make('tipo')
                    ->label('Tipo de permiso')
                    ->options([
                        'completo' => 'Día completo',
                        'parcial' => 'Parcial',
                    ]),

                Tables\Filters\Filter::make('rango_fecha')
                    ->form([
                        Forms\Components\DatePicker::make('desde')->label('Desde'),
                        Forms\Components\DatePicker::make('hasta')->label('Hasta'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['desde'], fn($q) => $q->whereDate('fecha_inicio', '>=', $data['desde']))
                            ->when($data['hasta'], fn($q) => $q->whereDate('fecha_fin', '<=', $data['hasta']));
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn() => auth()->user()?->hasRole('admin'))
                    ->authorize(fn() => auth()->user()?->hasRole('admin'))
                    ->requiresConfirmation()
                    ->successNotificationTitle('Permiso eliminado'),
            ])
            ->bulkActions([
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPermisoPersonals::route('/'),
            'create' => Pages\CreatePermisoPersonal::route('/create'),
            'edit' => Pages\EditPermisoPersonal::route('/{record}/edit'),
        ];
    }
}
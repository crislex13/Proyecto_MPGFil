<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AsistenciaResource\Pages;
use App\Models\Asistencia;
use App\Models\Clientes;
use App\Models\Personal;
use Filament\Resources\Resource;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action as TableAction;

use Filament\Tables\Actions\Action;
use Filament\Actions\Action as FormAction;



class AsistenciaResource extends Resource
{
    protected static ?string $model = Asistencia::class;

    protected static ?string $navigationIcon = 'heroicon-o-check-circle';
    protected static ?string $navigationGroup = 'Control de Accesos';
    protected static ?string $modelLabel = 'Asistencia';
    protected static ?string $pluralModelLabel = 'Asistencias';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('ci')
                ->label('CI')
                ->required()
                ->placeholder('Ingrese C.I. para registrar asistencia'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('foto_url')
                    ->label('Foto')
                    ->circular()
                    ->height(40),

                TextColumn::make('nombre_completo')
                    ->label('Nombre')
                    ->icon('heroicon-o-user')
                    ->getStateUsing(fn($record) => $record?->nombre_completo ?? '—'),

                TextColumn::make('rol')
                    ->label('Rol')
                    ->icon('heroicon-o-user-circle'),

                TextColumn::make('tipo_asistencia')
                    ->label('Tipo')
                    ->icon('heroicon-o-finger-print'),

                TextColumn::make('hora_entrada')
                    ->label('Entrada')
                    ->icon('heroicon-o-clock')
                    ->dateTime()
                    ->getStateUsing(fn($record) => $record?->hora_entrada),

                TextColumn::make('hora_salida')
                    ->label('Salida')
                    ->icon('heroicon-o-clock')
                    ->dateTime()
                    ->placeholder('—'),

                TextColumn::make('estado')
                    ->label('Estado')
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->badge()
                    ->color(fn(?string $state): string => match ($state) {
                        'puntual' => 'success',
                        'atrasado' => 'warning',
                        'permiso' => 'info',
                        'acceso_denegado' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('origen')
                    ->label('Origen')
                    ->icon('heroicon-o-finger-print')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('usuarioRegistro.name')
                    ->label('Registrado por')
                    ->icon('heroicon-o-user-circle')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->defaultSort('hora_entrada', 'desc')

            ->actions([
                TableAction::make('registrar_salida')
                    ->label('Registrar salida')
                    ->color('success')
                    ->icon('heroicon-o-arrow-right-circle')
                    ->visible(
                        fn($record) =>
                        $record->tipo_asistencia === 'personal' &&
                        $record->hora_salida === null
                    )
                    ->requiresConfirmation()
                    ->action(fn($record) => $record->update([
                        'hora_salida' => now(),
                    ])),
            ])

            ->headerActions([
                Action::make('registrarAsistencia')
                    ->label('Registrar Asistencia por CI')
                    ->icon('heroicon-o-identification')
                    ->form([
                        TextInput::make('ci')
                            ->label('C.I.')
                            ->required()
                            ->placeholder('Ingresa el CI')
                    ])
                    ->action(function (array $data) {
                        $ci = trim($data['ci']);
                        $cliente = Clientes::where('ci', $ci)->first();
                        $personal = Personal::where('ci', $ci)->first();

                        if ($cliente && !$personal) {
                            app(\App\Filament\Resources\AsistenciaResource\Pages\ListAsistencias::class)
                                ->registrarComoClienteManual($cliente);
                        } elseif (!$cliente && $personal) {
                            app(\App\Filament\Resources\AsistenciaResource\Pages\ListAsistencias::class)
                                ->registrarComoPersonalManual($personal);
                        } elseif ($cliente && $personal) {
                            session()->flash('ci_preseleccionado', $ci);
                            redirect('/admin/asistencias');
                        } else {
                            Notification::make()
                                ->title('❌ CI no registrado')
                                ->danger()
                                ->send();
                        }
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAsistencias::route('/'),
        ];
    }
}
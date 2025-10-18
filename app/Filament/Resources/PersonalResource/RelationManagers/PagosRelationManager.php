<?php

namespace App\Filament\Resources\PersonalResource\RelationManagers;

use Filament\Tables;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Illuminate\View\View;

class PagosRelationManager extends RelationManager
{
    protected static string $relationship = 'pagos';
    protected static ?string $title = 'Historial de Pagos';
    protected static ?string $recordTitleAttribute = 'fecha';

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('fecha')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('monto')
                    ->label('Monto (Bs)')
                    ->money('BOB', true)
                    ->sortable(),

                TextColumn::make('descripcion')
                    ->label('Descripción')
                    ->wrap(),

                BadgeColumn::make('estado')
                    ->label('Estado')
                    ->colors([
                        'success' => 'pagado',
                        'warning' => 'pendiente',
                        'danger' => 'observado',
                    ])
                    ->formatStateUsing(fn($state) => ucfirst($state)),
            ])

            ->defaultSort('fecha', 'desc')
            ->headerActions([])
            ->actions([])
            ->bulkActions([])

            // ✅ Footer: devuelve una vista inline que Filament sí entiende
            ->contentFooter(function (): View {
                $personal = $this->getOwnerRecord();
                $total = $personal->pagos()->sum('monto');
                $ultimo = $personal->pagos()->max('fecha');

                return view('components.footer-pagos-inline', [
                    'total' => $total,
                    'ultimo' => $ultimo,
                ]);
            })

            ->emptyStateHeading('Sin pagos registrados')
            ->emptyStateDescription('Este personal aún no tiene pagos registrados.');
    }
}

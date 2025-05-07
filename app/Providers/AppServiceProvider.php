<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Storage;
use App\Models\PermisoCliente;
use App\Observers\PermisoClienteObserver;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\Blade::directive('storage_url', function ($expression) {
            return "<?php echo \$expression ? Storage::disk('public')->url(\$expression) : null; ?>";
        });

        // ✅ Registro manual de widgets Livewire
        \Livewire\Livewire::component('clientes-activos', \App\Filament\Widgets\ClientesActivos::class);
        \Livewire\Livewire::component('inscripciones-por-dia', \App\Filament\Widgets\InscripcionesPorDia::class);
        \Livewire\Livewire::component('inscripciones-anio', \App\Filament\Widgets\InscripcionesAnio::class);
        \Livewire\Livewire::component('inscripciones-del-mes', \App\Filament\Widgets\InscripcionesDelMes::class);
        \Livewire\Livewire::component('inscripciones-hoy', \App\Filament\Widgets\InscripcionesHoy::class);


        PermisoCliente::observe(PermisoClienteObserver::class);

        setlocale(LC_TIME, 'es_ES.UTF-8');
        Carbon::setLocale('es');

    }

}

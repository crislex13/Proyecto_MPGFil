<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Storage;
use App\Models\PermisoCliente;
use App\Observers\PermisoClienteObserver;
use Carbon\Carbon;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use App\Actions\Auth\CustomLoginResponse;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Route;
use Filament\Http\Responses\Auth\Contracts\LogoutResponse;
use App\Actions\Auth\CustomLogoutResponse;
use Livewire\Livewire;
use Illuminate\Support\Facades\Gate;
use App\Models\Productos;
use App\Policies\ProductoPolicy;
use App\Models\Asistencia;
use App\Models\Casillero;
use App\Models\CategoriaProducto;
use App\Models\Clientes;
use App\Models\Configuracion;
use App\Models\Disciplina;
use App\Models\IngresoProducto;
use App\Models\PagoPersonal;
use App\Models\PermisoPersonal;
use App\Models\Personal;
use App\Models\PlanCliente;
use App\Models\PlanDisciplina;
use App\Models\Plan;
use App\Models\Sala;
use App\Models\SesionAdicional;
use App\Models\Turno;
use App\Models\User;
use App\Models\VentaProducto;
use App\Policies\AsistenciaPolicy;
use App\Policies\CasilleroPolicy;
use App\Policies\CategoriaProductoPolicy;
use App\Policies\ClientesPolicy;
use App\Policies\ConfiguracionPolicy;
use App\Policies\DisciplinaPolicy;
use App\Policies\IngresoProductoPolicy;
use App\Policies\PagoPersonalPolicy;
use App\Policies\PermisoClientePolicy;
use App\Policies\PermisoPersonalPolicy;
use App\Policies\PersonalPolicy;
use App\Policies\PlanClientePolicy;
use App\Policies\PlanDisciplinaPolicy;
use App\Policies\PlanPolicy;
use App\Policies\SalaPolicy;
use App\Policies\SesionAdicionalPolicy;
use App\Policies\TurnoPolicy;
use App\Policies\UserPolicy;
use App\Policies\VentaProductoPolicy;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Logout;
use App\Models\ActivityLog;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
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
        Livewire::component('resumen-estadistico', \App\Filament\Widgets\ResumenEstadistico::class);
        Livewire::component('instructor-top-widget', \App\Filament\Widgets\InstructorTopWidget::class);
        Livewire::component('producto-top-widget', \App\Filament\Widgets\ProductoTopWidget::class);
        Livewire::component('flujo-caja-dia-widget', \App\Filament\Widgets\FlujoCajaDiaWidget::class);
        Livewire::component('flujo-caja-semana', \App\Filament\Widgets\FlujoCajaSemana::class);
        Livewire::component('inscripciones-por-dia', \App\Filament\Widgets\InscripcionesPorDia::class);
        Livewire::component('sesiones-totales-widget', \App\Filament\Widgets\SesionesTotalesWidget::class);

        PermisoCliente::observe(PermisoClienteObserver::class);

        setlocale(LC_TIME, 'es_ES.UTF-8');
        Carbon::setLocale('es');

        Gate::policy(Asistencia::class, AsistenciaPolicy::class);
        Gate::policy(Casillero::class, CasilleroPolicy::class);
        Gate::policy(CategoriaProducto::class, CategoriaProductoPolicy::class);
        Gate::policy(Clientes::class, ClientesPolicy::class);
        Gate::policy(Configuracion::class, ConfiguracionPolicy::class);
        Gate::policy(Disciplina::class, DisciplinaPolicy::class);
        Gate::policy(IngresoProducto::class, IngresoProductoPolicy::class);
        Gate::policy(PagoPersonal::class, PagoPersonalPolicy::class);
        Gate::policy(PermisoCliente::class, PermisoClientePolicy::class);
        Gate::policy(PermisoPersonal::class, PermisoPersonalPolicy::class);
        Gate::policy(Personal::class, PersonalPolicy::class);
        Gate::policy(PlanCliente::class, PlanClientePolicy::class);
        Gate::policy(PlanDisciplina::class, PlanDisciplinaPolicy::class);
        Gate::policy(Plan::class, PlanPolicy::class);
        Gate::policy(Productos::class, ProductoPolicy::class);
        Gate::policy(Sala::class, SalaPolicy::class);
        Gate::policy(SesionAdicional::class, SesionAdicionalPolicy::class);
        Gate::policy(Turno::class, TurnoPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(VentaProducto::class, VentaProductoPolicy::class);
        

        // ✅ Listener de logout (bitácora)
        Event::listen(Logout::class, function (Logout $event) {
            ActivityLog::create([
                'log_name'    => 'auth',
                'event'       => 'logout',
                'description' => 'Cierre de sesión',
                'causer_type' => $event->user ? get_class($event->user) : null,
                'causer_id'   => $event->user->id ?? null,
                'ip_address'  => request()->ip(),
                'user_agent'  => (string) request()->userAgent(),
            ]);
        });


    }

}

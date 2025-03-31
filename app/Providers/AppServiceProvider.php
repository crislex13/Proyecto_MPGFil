<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Storage;

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
    public function boot()
    {
        \Illuminate\Support\Facades\Blade::directive('storage_url', function ($expression) {
            return "<?php echo \$expression ? Storage::disk('public')->url(\$expression) : null; ?>";
        });
    }
}

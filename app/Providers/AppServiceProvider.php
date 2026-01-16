<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Registrar helper
        $this->app->bind('multi-comparendo', function() {
            return new \App\Helpers\MultiComparendoHelper();
        });
    }
    
    public function boot()
    {
        // Compartir variable global con todas las vistas
        view()->composer('*', function($view) {
            $view->with('is_multi_comparendo', config('app.multi_comparendo', true));
        });
    }
}
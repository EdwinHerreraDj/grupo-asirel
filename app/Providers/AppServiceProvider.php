<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Empresa;
use App\Models\GastoInicialPartida;
use App\Observers\GastoInicialPartidaObserver;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useTailwind();

        GastoInicialPartida::observe(GastoInicialPartidaObserver::class);

        if (Schema::hasTable('empresa')) {
            $empresa = Empresa::first();
            View::share('empresa', $empresa);
        }
    }
}
